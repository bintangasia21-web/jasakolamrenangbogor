<?php
/**
 * Hapus satu baris di tabel "pages" berdasar id. Dilindungi Basic Auth.
 * Dipakai bersama oleh semua tab admin yang mengelola tabel pages
 * (Layanan, Artikel, Area Layanan, Portfolio, Halaman Utama) — bukan
 * spesifik satu tipe halaman.
 */
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/inc/db.php';

function respond($success, $message) {
    echo json_encode(['success' => $success, 'message' => $message]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    respond(false, 'Metode tidak diizinkan.');
}

$id = isset($_POST['id']) ? (int) $_POST['id'] : 0;
if ($id <= 0) {
    respond(false, 'ID halaman tidak valid.');
}

// Halaman inti (5 hub utama) tidak boleh dihapus lewat endpoint ini —
// nav header/footer menautkan ke url_path-nya secara hardcode di
// inc/render-partials.php, jadi menghapusnya akan mematahkan navigasi
// situs tanpa fallback apa pun.
$protectedUrlPaths = ['/layanan/', '/area-layanan/'];

try {
    $pdo = get_db();
    $stmt = $pdo->prepare('SELECT url_path FROM pages WHERE id = :id');
    $stmt->execute([':id' => $id]);
    $row = $stmt->fetch();
    if (!$row) {
        respond(false, 'Halaman tidak ditemukan.');
    }
    if (in_array($row['url_path'], $protectedUrlPaths, true)) {
        respond(false, 'Halaman inti ini tidak bisa dihapus karena masih ditautkan dari menu navigasi situs.');
    }

    $del = $pdo->prepare('DELETE FROM pages WHERE id = :id');
    $del->execute([':id' => $id]);
    respond(true, 'Halaman berhasil dihapus.');
} catch (Exception $e) {
    respond(false, 'Gagal menghapus: ' . $e->getMessage());
}
