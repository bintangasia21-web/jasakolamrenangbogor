<?php
/**
 * Skrip sekali-jalan: menerbitkan artikel "Jenis Sistem Filtrasi Kolam
 * Renang: Pasir, Cartridge, atau DE?" -- mengisi gap konten untuk
 * layanan Instalasi Filter & Pompa, yang sebelumnya belum punya
 * satupun artikel pendukung.
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

$urlPath = '/artikel/jenis-sistem-filtrasi-kolam-renang/';
$title = 'Jenis Sistem Filtrasi Kolam Renang: Pasir, Cartridge, atau DE?';
$metaTitle = 'Jenis Sistem Filtrasi Kolam Renang: Pasir, Cartridge, atau DE? | Jasa Kolam Renang Bogor';
$metaDescription = 'Ada tiga jenis filter kolam renang yang umum dipakai: pasir, cartridge, dan DE. Kenali kelebihan masing-masing sebelum memilih sistem filtrasi kolam Anda.';
$intro = 'Sistem filtrasi adalah salah satu komponen paling penting dalam menjaga kualitas air kolam renang, tapi tidak semua kolam cocok dengan jenis filter yang sama. Berikut tiga jenis filter yang paling umum dipakai, beserta kelebihan dan kekurangannya.';

// Belum ada foto yang diambil khusus untuk artikel ini -- pakai foto
// proyek "Instalasi Sistem Filtrasi" dari galeri Portofolio yang sudah
// ada (tema paling relevan) sebagai sampul sementara. Admin bisa
// menggantinya kapan saja lewat tab Artikel di panel admin.
$coverImage = 'photo.php?id=17';

$content = '<h2>Filter Pasir (Sand Filter)</h2>'
    . '<p>Jenis filter yang paling umum dipakai untuk kolam renang rumahan karena perawatannya sederhana -- cukup di-backwash secara berkala untuk membersihkan kotoran yang tersaring. Media pasir di dalamnya biasanya tahan beberapa tahun sebelum perlu diganti. Cocok untuk pemilik kolam yang menginginkan sistem dengan perawatan rutin yang mudah dan biaya operasional terjangkau.</p>'
    . '<h2>Filter Cartridge</h2>'
    . '<p>Memakai elemen kertas lipat (cartridge) yang disaring secara fisik, tanpa proses backwash seperti filter pasir -- cartridge cukup dilepas dan disemprot air untuk dibersihkan. Kualitas penyaringannya umumnya lebih halus dibanding filter pasir, tapi cartridge perlu diganti secara berkala setelah penggunaan jangka panjang.</p>'
    . '<h2>Filter DE (Diatomaceous Earth)</h2>'
    . '<p>Memakai bubuk diatom yang melapisi elemen filter, menghasilkan kualitas penyaringan air paling halus di antara ketiga jenis ini. Cocok untuk kolam yang menuntut kejernihan air maksimal, tapi perawatannya sedikit lebih rumit karena melibatkan penambahan bubuk DE secara berkala dan penanganan limbah backwash yang lebih hati-hati.</p>'
    . '<h2>Jadi, Mana yang Paling Cocok untuk Kolam Anda?</h2>'
    . '<p>Tidak ada satu jenis yang otomatis paling baik untuk semua kolam -- pilihan terbaik tergantung ukuran kolam, frekuensi pemakaian, dan seberapa besar waktu yang Anda mau luangkan untuk perawatan sistem filtrasinya. Tim <a href="/layanan/instalasi-filter-pompa/">instalasi filter &amp; pompa</a> kami membantu menentukan jenis yang paling sesuai setelah melihat kondisi kolam Anda langsung, seperti pada proyek <a href="/portofolio/instalasi-sistem-filtrasi/">Instalasi Sistem Filtrasi</a> yang pernah kami kerjakan. Kalau filter yang sudah terpasang mulai menurun performanya, kami juga menyediakan layanan <a href="/layanan/penggantian-pasir-filter/">penggantian pasir filter</a> secara terpisah.</p>'
    . '<h2>Yang Bisa Anda Lakukan Sekarang</h2>'
    . '<p>Ceritakan ukuran kolam dan kondisi sistem filtrasi Anda saat ini, kami bantu rekomendasikan jenis filter yang paling sesuai tanpa komitmen apa pun.</p>';

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
