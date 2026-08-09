<?php
/**
 * Ambil teks/copy tersimpan untuk satu halaman (page_key), dipakai admin
 * panel untuk mengisi form edit di tab "Halaman Utama". Dilindungi Basic
 * Auth (beda dari get-data.php yang publik, karena field_value di sini
 * bisa berisi draft copy yang belum tentu ingin ditampilkan admin lain).
 */
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/inc/db.php';

function respond($success, $message, $extra = []) {
    echo json_encode(array_merge(['success' => $success, 'message' => $message], $extra));
    exit;
}

$pageKey = $_GET['page_key'] ?? '';
if ($pageKey === '') {
    respond(false, 'page_key wajib diisi.');
}

try {
    $pdo = get_db();
    $stmt = $pdo->prepare('SELECT field_key, field_value FROM page_sections WHERE page_key = :page_key');
    $stmt->execute([':page_key' => $pageKey]);
    $sections = [];
    foreach ($stmt->fetchAll() as $row) {
        $sections[$row['field_key']] = $row['field_value'];
    }
    respond(true, 'OK', ['sections' => $sections]);
} catch (Exception $e) {
    // Tabel mungkin belum ada (setup-schema.php belum dijalankan) -- jangan
    // gagal total, kembalikan kosong supaya form admin tetap terbuka dengan
    // nilai default (placeholder) alih-alih error.
    respond(true, 'OK (kosong)', ['sections' => []]);
}
