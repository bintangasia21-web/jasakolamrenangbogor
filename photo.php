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

// ETag dari hash isi data -- "immutable" sebelumnya salah karena foto
// id yang sama BISA berubah isinya (admin ganti foto, atau script
// optimasi re-encode ke WebP), jadi CDN/browser yang menyimpan cache
// 1 tahun tanpa revalidasi akan terus menyajikan versi basi. ETag
// memastikan cache hanya dipakai kalau isinya benar-benar belum
// berubah, tanpa mengorbankan keuntungan caching untuk kasus normal.
$etag = '"' . md5($row['data']) . '"';
header('ETag: ' . $etag);
header('Cache-Control: public, max-age=86400, must-revalidate');

$clientEtag = $_SERVER['HTTP_IF_NONE_MATCH'] ?? null;
if ($clientEtag !== null && trim($clientEtag) === $etag) {
    http_response_code(304);
    exit;
}

header('Content-Type: ' . $row['mime_type']);
header('Content-Length: ' . strlen($row['data']));
echo $row['data'];
