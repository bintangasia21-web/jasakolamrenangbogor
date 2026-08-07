<?php
/**
 * Endpoint upload foto portofolio (langsung live, tanpa perlu redeploy).
 * Dilindungi Basic Auth yang sama dengan admin.html lewat .htaccess.
 * Foto disimpan sebagai data biner di tabel "photos" (BUKAN file di
 * server) supaya kebal terhadap proses deploy yang menghapus file di
 * luar Git — URL referensinya ("photo.php?id=<id>") dikembalikan
 * sebagai JSON untuk disimpan lewat save-data.php.
 */
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/inc/db.php';

function respond($success, $payload) {
    echo json_encode(array_merge(['success' => $success], $payload));
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    respond(false, ['message' => 'Metode tidak diizinkan.']);
}

if (!isset($_FILES['photo']) || $_FILES['photo']['error'] !== UPLOAD_ERR_OK) {
    respond(false, ['message' => 'Tidak ada file yang diterima atau upload gagal.']);
}

$file = $_FILES['photo'];
$maxBytes = 5 * 1024 * 1024; // 5MB
if ($file['size'] > $maxBytes) {
    respond(false, ['message' => 'Ukuran file maksimal 5MB.']);
}

$allowedMimes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];

$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mime = finfo_file($finfo, $file['tmp_name']);
finfo_close($finfo);

if (!in_array($mime, $allowedMimes, true)) {
    respond(false, ['message' => 'Format file tidak didukung. Gunakan JPG, PNG, WEBP, atau GIF.']);
}

$data = file_get_contents($file['tmp_name']);
if ($data === false) {
    respond(false, ['message' => 'Gagal membaca file yang diunggah.']);
}

try {
    $pdo = get_db();
    $stmt = $pdo->prepare('INSERT INTO photos (mime_type, data) VALUES (:mime, :data)');
    $stmt->bindValue(':mime', $mime);
    $stmt->bindValue(':data', $data, PDO::PARAM_LOB);
    $stmt->execute();
    $id = $pdo->lastInsertId();
} catch (Exception $e) {
    respond(false, ['message' => 'Gagal menyimpan foto ke database: ' . $e->getMessage()]);
}

respond(true, ['url' => 'photo.php?id=' . $id]);
