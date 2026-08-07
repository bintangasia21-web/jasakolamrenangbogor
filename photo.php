<?php
/**
 * Endpoint publik yang menampilkan foto portofolio langsung dari
 * database (tabel photos), lewat ?id=<id>. Disimpan di database (bukan
 * file di server) supaya kebal terhadap proses deploy yang menghapus
 * file di luar Git.
 */
require_once __DIR__ . '/inc/db.php';

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
if ($id <= 0) {
    http_response_code(400);
    exit('ID tidak valid.');
}

try {
    $pdo = get_db();
    $stmt = $pdo->prepare('SELECT mime_type, data FROM photos WHERE id = :id');
    $stmt->execute([':id' => $id]);
    $row = $stmt->fetch();
} catch (Exception $e) {
    http_response_code(500);
    exit('Database error.');
}

if (!$row) {
    http_response_code(404);
    exit('Foto tidak ditemukan.');
}

header('Content-Type: ' . $row['mime_type']);
header('Cache-Control: public, max-age=31536000, immutable');
header('Content-Length: ' . strlen($row['data']));
echo $row['data'];
