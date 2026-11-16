<?php
/**
 * Skrip sekali-jalan: menerbitkan artikel "Apa Itu Alkalinitas dan
 * Kesadahan Air Kolam? Ini Bedanya dengan pH".
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

$urlPath = '/artikel/apa-itu-alkalinitas-dan-kesadahan-air-kolam/';
$title = 'Apa Itu Alkalinitas dan Kesadahan Air Kolam? Ini Bedanya dengan pH';
$metaTitle = 'Apa Itu Alkalinitas dan Kesadahan Air Kolam? Ini Bedanya dengan pH | Jasa Kolam Renang Bogor';
$metaDescription = 'pH saja tidak cukup untuk menilai kualitas air kolam. Kenali apa itu alkalinitas dan kesadahan kalsium, dan kenapa keduanya sama penting dipantau.';
$intro = 'Banyak pemilik kolam hanya memantau pH air, padahal ada dua indikator lain yang sama pentingnya: alkalinitas dan kesadahan kalsium. Ketiganya saling berkaitan dan memengaruhi kualitas air secara keseluruhan.';

$coverImage = 'photo.php?id=12';

$content = '<h2>Alkalinitas: "Penyangga" yang Menstabilkan pH</h2>'
    . '<p>Alkalinitas total (total alkalinity) berfungsi sebagai penyangga yang membuat pH air tidak mudah berubah drastis akibat faktor luar seperti air hujan, penambahan bahan kimia, atau aktivitas perenang. Kalau alkalinitas terlalu rendah, pH jadi mudah melonjak naik-turun meski sudah disesuaikan -- membuat penyesuaian pH jadi terasa tidak pernah stabil.</p>'
    . '<h2>Kesadahan Kalsium: Risiko Korosif atau Berkerak</h2>'
    . '<p>Kesadahan kalsium (calcium hardness) mengukur jumlah mineral kalsium terlarut dalam air. Kesadahan yang terlalu rendah membuat air bersifat lebih korosif, berisiko mengikis permukaan kolam dan komponen logam. Sebaliknya, kesadahan yang terlalu tinggi memicu kerak (scaling) menumpuk di dinding kolam dan komponen filter.</p>'
    . '<h2>Kenapa pH Saja Tidak Cukup Dipantau</h2>'
    . '<p>pH yang terlihat normal pada satu waktu pengukuran bisa jadi tidak stabil kalau alkalinitasnya rendah -- artinya bisa berubah cepat sebelum pengukuran berikutnya. Begitu juga, air dengan pH ideal tapi kesadahan yang tidak seimbang tetap berisiko merusak struktur kolam dalam jangka panjang. Memantau ketiganya bersamaan memberi gambaran yang jauh lebih akurat dibanding hanya mengandalkan satu indikator. Untuk dasar soal menjaga pH itu sendiri, baca <a href="/artikel/cara-menjaga-ph-air-kolam-tetap-ideal/">artikel kami soal cara menjaga pH air kolam tetap ideal</a>.</p>'
    . '<h2>Yang Bisa Anda Lakukan Sekarang</h2>'
    . '<p>Kalau Anda belum pernah mengecek alkalinitas dan kesadahan kolam Anda, tim <a href="/layanan/jasa-perawatan-kolam-renang/">jasa perawatan kolam renang</a> kami bisa bantu pengukuran lengkap dan penyesuaian yang diperlukan.</p>';

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
