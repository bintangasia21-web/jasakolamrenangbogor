<?php
/**
 * Skrip sekali-jalan: menerbitkan artikel "Kolam Garam vs Kolam
 * Klorin, Mana yang Lebih Cocok untuk Rumah Anda?" -- mendukung
 * money page jasa perawatan kolam renang (url_path dikonfirmasi:
 * /layanan/jasa-perawatan-kolam-renang/).
 *
 * Dijalankan lewat browser (Basic Auth), sama seperti seed-artikel-
 * lainnya. Aman dijalankan berkali-kali -- INSERT ... ON DUPLICATE KEY
 * UPDATE berdasarkan url_path, tidak membuat baris duplikat.
 */
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/inc/db.php';

function respond($success, $message, $extra = []) {
    echo json_encode(array_merge(['success' => $success, 'message' => $message], $extra), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}

$urlPath = '/artikel/kolam-garam-vs-kolam-klorin/';
$title = 'Kolam Garam vs Kolam Klorin, Mana yang Lebih Cocok untuk Rumah Anda?';
$metaTitle = 'Kolam Garam vs Kolam Klorin, Mana yang Lebih Cocok? | Jasa Kolam Renang Bogor';
$metaDescription = 'Kolam garam dan kolam klorin punya cara kerja dan kelebihan berbeda. Simak perbandingannya sebelum menentukan sistem sanitasi kolam Anda.';
$intro = 'Selain soal desain dan material, pemilik kolam juga sering dihadapkan pada pilihan sistem sanitasi air: pakai klorin manual seperti biasa, atau beralih ke sistem kolam garam. Berikut perbandingannya.';

// Belum ada foto yang diambil khusus untuk artikel ini -- pakai foto
// proyek "Perawatan Kolam Rutin" dari galeri Portofolio yang sudah
// ada (tema perawatan air, paling relevan) sebagai sampul sementara.
// Admin bisa menggantinya kapan saja lewat tab Artikel di panel admin.
$coverImage = 'photo.php?id=16';

$content = '<h2>Cara Kerja Kolam Garam</h2>'
    . '<p>Kolam garam memakai alat yang disebut chlorine generator -- garam yang dilarutkan ke air diubah secara otomatis menjadi klorin lewat proses elektrolisis. Jadi kolam garam tetap memakai klorin untuk sanitasi airnya, hanya saja prosesnya otomatis dan tidak perlu menambahkan klorin secara manual setiap saat.</p>'
    . '<h2>Cara Kerja Kolam Klorin Manual</h2>'
    . '<p>Pada sistem klorin konvensional, bahan kimia ditambahkan langsung ke air secara manual sesuai dosis dan jadwal yang ditentukan. Sistem ini lebih umum dipakai karena biaya awal pemasangannya lebih rendah dibanding memasang chlorine generator.</p>'
    . '<h2>Kelebihan Masing-masing</h2>'
    . '<ul>'
    . '<li><strong>Kolam garam:</strong> terasa lebih lembut di kulit dan mata karena kadar klorin yang dihasilkan lebih stabil dan merata, serta mengurangi pekerjaan menambah klorin manual sehari-hari.</li>'
    . '<li><strong>Kolam klorin manual:</strong> biaya pemasangan awal lebih rendah dan komponennya lebih sederhana, serta lebih umum dipakai sehingga perawatannya lebih mudah ditemukan tekniknya.</li>'
    . '</ul>'
    . '<h2>Faktor Pemilihan</h2>'
    . '<p>Pilihan antara keduanya biasanya kembali ke prioritas Anda: kenyamanan harian dan pengurangan pekerjaan manual (kolam garam), atau biaya awal yang lebih ringan (klorin manual). Tim <a href="/layanan/jasa-perawatan-kolam-renang/">jasa perawatan kolam renang</a> kami bisa membantu menjelaskan opsi mana yang paling sesuai dengan kondisi kolam dan kebiasaan pemakaian Anda.</p>'
    . '<h2>Yang Bisa Anda Lakukan Sekarang</h2>'
    . '<p>Ceritakan kondisi kolam dan preferensi Anda, kami bantu jelaskan opsi yang paling sesuai tanpa komitmen apa pun.</p>';

try {
    $pdo = get_db();
    $stmt = $pdo->prepare(
        "INSERT INTO pages (type, url_path, title, meta_title, meta_description, h1, intro, content, cover_image, status)
         VALUES ('article', :url_path, :title, :meta_title, :meta_description, :h1, :intro, :content, :cover_image, 'published')
         ON DUPLICATE KEY UPDATE title=VALUES(title), meta_title=VALUES(meta_title),
           meta_description=VALUES(meta_description), h1=VALUES(h1), intro=VALUES(intro),
           content=VALUES(content), cover_image=VALUES(cover_image), status='published'"
    );
    $stmt->execute([
        ':url_path' => $urlPath,
        ':title' => $title,
        ':meta_title' => $metaTitle,
        ':meta_description' => $metaDescription,
        ':h1' => $title,
        ':intro' => $intro,
        ':content' => $content,
        ':cover_image' => $coverImage,
    ]);

    respond(true, 'Artikel berhasil diterbitkan.', ['url_path' => $urlPath]);
} catch (Exception $e) {
    respond(false, 'Gagal menerbitkan artikel: ' . $e->getMessage());
}
