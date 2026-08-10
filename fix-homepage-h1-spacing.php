<?php
/**
 * Skrip sekali-jalan (Phase 2 homepage SEO fix, 2026-08-10, FIX 4): H1
 * beranda tersimpan di page_sections (page_key='home', field_key=
 * 'hero_h1') dengan bug spasi ganda ("Perawatan  Rutin") -- nilai ini
 * konten database, bukan kode, jadi tidak bisa diperbaiki lewat edit
 * index.php. Skrip ini HANYA merapikan spasi berlebih pada NILAI YANG
 * SEDANG TERSIMPAN (bukan menimpa dengan teks baru), sama seperti pola
 * fix-content-2026-08-10.php sebelumnya.
 *
 * Dijalankan lewat browser (Basic Auth). Aman dijalankan berkali-kali.
 */
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/inc/db.php';

function respond($success, $message, $extra = []) {
    echo json_encode(array_merge(['success' => $success, 'message' => $message], $extra), JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}

try {
    $pdo = get_db();
    $stmt = $pdo->prepare('SELECT field_value FROM page_sections WHERE page_key = :page_key AND field_key = :field_key');
    $stmt->execute([':page_key' => 'home', ':field_key' => 'hero_h1']);
    $row = $stmt->fetch();

    if (!$row) {
        respond(true, 'Belum ada override hero_h1 tersimpan (halaman masih pakai teks default dari kode, yang sudah rapi) -- tidak ada yang perlu diperbaiki.');
    }

    $old = $row['field_value'];
    $new = trim(preg_replace('/\s+/', ' ', $old));

    if ($new === $old) {
        respond(true, 'Sudah rapi, tidak ada perubahan.', ['value' => $old]);
    }

    $update = $pdo->prepare('UPDATE page_sections SET field_value = :v WHERE page_key = :page_key AND field_key = :field_key');
    $update->execute([':v' => $new, ':page_key' => 'home', ':field_key' => 'hero_h1']);

    respond(true, 'Spasi ganda pada H1 beranda berhasil dirapikan.', ['before' => $old, 'after' => $new]);
} catch (Exception $e) {
    respond(false, 'Gagal memperbaiki: ' . $e->getMessage());
}
