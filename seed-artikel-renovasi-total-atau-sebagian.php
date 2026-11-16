<?php
/**
 * Skrip sekali-jalan: menerbitkan artikel "Renovasi Kolam: Ganti
 * Total atau Perbaikan Sebagian, Mana yang Tepat?" -- mendukung
 * layanan Renovasi, ditautkan dengan seed-artikel-tanda-perlu-
 * renovasi.php yang sudah live.
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

$urlPath = '/artikel/renovasi-ganti-total-atau-perbaikan-sebagian/';
$title = 'Renovasi Kolam: Ganti Total atau Perbaikan Sebagian, Mana yang Tepat?';
$metaTitle = 'Renovasi Kolam: Ganti Total atau Perbaikan Sebagian? | Jasa Kolam Renang Bogor';
$metaDescription = 'Tidak semua kolam renang bermasalah perlu direnovasi total. Simak kapan perbaikan sebagian sudah cukup, dan kapan harus ganti total.';
$intro = 'Setelah menyadari kolam perlu direnovasi, pertanyaan berikutnya adalah seberapa luas renovasi yang dibutuhkan -- apakah cukup memperbaiki bagian yang rusak saja, atau harus mengganti total. Berikut pertimbangannya.';

// Belum ada foto yang diambil khusus untuk artikel ini -- pakai foto
// proyek "Renovasi Kolam Renang di Puncak" dari galeri Portofolio yang
// sudah ada (tema renovasi, paling relevan) sebagai sampul sementara.
// Admin bisa menggantinya kapan saja lewat tab Artikel di panel admin.
$coverImage = 'photo.php?id=13';

$content = '<h2>Kapan Perbaikan Sebagian Sudah Cukup</h2>'
    . '<p>Kalau kerusakan masih bersifat lokal -- misalnya keramik lepas di satu titik, retak kecil yang belum menyebar, atau kebocoran di satu area saja -- sementara struktur utama kolam masih sehat, perbaikan sebagian biasanya sudah cukup menyelesaikan masalah tanpa perlu membongkar keseluruhan kolam.</p>'
    . '<h2>Kapan Harus Ganti Total</h2>'
    . '<p>Ganti total lebih tepat ketika kerusakan sudah menyebar ke banyak titik, usia kolam sudah sangat tua sehingga struktur dasarnya mulai menurun, atau ketika pemilik ingin mengubah desain secara signifikan (ukuran, bentuk, atau material) yang tidak bisa dilakukan hanya dengan tambal sebagian.</p>'
    . '<h2>Pertimbangan Biaya Jangka Panjang</h2>'
    . '<p>Perbaikan sebagian memang lebih murah di awal, tapi kalau kerusakan ternyata berakar dari masalah struktural yang lebih dalam, perbaikan kecil berulang bisa berakhir lebih mahal dibanding langsung menyelesaikannya secara menyeluruh sejak awal. Kami membantu menilai mana yang paling masuk akal lewat survei langsung -- kalau Anda belum yakin apakah kolam Anda sudah menunjukkan tanda-tanda ini, baca juga <a href="/artikel/tanda-tanda-kolam-renang-perlu-direnovasi/">artikel kami soal tanda-tanda kolam perlu direnovasi</a>.</p>'
    . '<h2>Yang Bisa Anda Lakukan Sekarang</h2>'
    . '<p>Hubungi kami untuk survei langsung, tim <a href="/layanan/renovasi-perbaikan-kolam/">renovasi dan perbaikan kolam</a> kami akan menilai kondisi kolam Anda dan merekomendasikan opsi yang paling sesuai, tanpa komitmen apa pun.</p>';

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
