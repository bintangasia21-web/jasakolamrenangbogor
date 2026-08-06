<?php
/**
 * Endpoint upload foto portofolio (langsung live, tanpa perlu redeploy).
 * Dilindungi Basic Auth yang sama dengan admin.html lewat .htaccess.
 * File disimpan ke assets/img/portfolio/ dan URL-nya dikembalikan
 * sebagai JSON untuk disimpan ke data.json lewat save-data.php.
 */
header('Content-Type: application/json; charset=utf-8');

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

$allowedMimes = [
    'image/jpeg' => 'jpg',
    'image/png' => 'png',
    'image/webp' => 'webp',
    'image/gif' => 'gif'
];

$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mime = finfo_file($finfo, $file['tmp_name']);
finfo_close($finfo);

if (!isset($allowedMimes[$mime])) {
    respond(false, ['message' => 'Format file tidak didukung. Gunakan JPG, PNG, WEBP, atau GIF.']);
}

$ext = $allowedMimes[$mime];
$safeName = bin2hex(random_bytes(8)) . '-' . time() . '.' . $ext;

$targetDir = __DIR__ . '/assets/img/portfolio';
if (!is_dir($targetDir)) {
    mkdir($targetDir, 0755, true);
}

$targetPath = $targetDir . '/' . $safeName;
if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
    respond(false, ['message' => 'Gagal menyimpan file ke server.']);
}

respond(true, ['url' => 'assets/img/portfolio/' . $safeName]);
