<?php
/**
 * Skrip sekali-jalan: menerbitkan artikel "Jenis Material Kolam
 * Renang: Keramik, Mosaic, atau Liner?" -- mendukung layanan
 * Pembuatan.
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

$urlPath = '/artikel/jenis-material-kolam-renang-keramik-mosaic-liner/';
$title = 'Jenis Material Kolam Renang: Keramik, Mosaic, atau Liner?';
$metaTitle = 'Jenis Material Kolam Renang: Keramik, Mosaic, atau Liner? | Jasa Kolam Renang Bogor';
$metaDescription = 'Keramik, mosaic, dan liner masing-masing punya kelebihan berbeda untuk lapisan kolam renang. Simak perbandingannya sebelum menentukan pilihan.';
$intro = 'Material lapisan kolam renang bukan cuma soal tampilan -- pilihan ini juga memengaruhi daya tahan, kemudahan perawatan, dan tentu saja biaya. Berikut tiga jenis material yang paling umum dipakai.';

// Belum ada foto yang diambil khusus untuk artikel ini -- pakai foto
// proyek "Kolam Renang Villa Modern di Sentul" dari galeri Portofolio
// yang sudah ada (tema pembuatan/finishing, paling relevan) sebagai
// sampul sementara. Admin bisa menggantinya kapan saja lewat tab
// Artikel di panel admin.
$coverImage = 'photo.php?id=11';

$content = '<h2>Keramik</h2>'
    . '<p>Material paling umum dipakai untuk kolam renang rumahan maupun villa karena pilihan desainnya luas, daya tahannya baik, dan harganya relatif lebih terjangkau dibanding mosaic. Perawatannya juga tidak rumit -- cukup dibersihkan rutin seperti bagian kolam lainnya.</p>'
    . '<h2>Mosaic</h2>'
    . '<p>Terdiri dari potongan kecil (sering berbahan kaca atau keramik halus) yang disusun membentuk pola lebih detail dan estetik. Hasilnya lebih mewah secara tampilan, tapi proses pemasangan lebih memakan waktu dan harganya umumnya lebih tinggi dibanding keramik biasa.</p>'
    . '<h2>Liner</h2>'
    . '<p>Lapisan vinyl yang dipasang menutupi seluruh permukaan kolam tanpa perlu proses keramik satu per satu, sehingga waktu pemasangan jauh lebih cepat. Pilihan ini sering dipakai untuk proyek renovasi yang ingin tampilan baru tanpa membongkar struktur lama, meski umur pakainya perlu diganti ulang setelah beberapa tahun.</p>'
    . '<h2>Faktor yang Perlu Dipertimbangkan</h2>'
    . '<p>Pemilihan material sebaiknya mempertimbangkan tiga hal sekaligus: budget yang tersedia, tampilan akhir yang diinginkan, dan daya tahan jangka panjang yang dibutuhkan. Tim <a href="/layanan/pembuatan-kolam-renang-baru/">pembuatan kolam renang baru</a> kami membantu menyesuaikan pilihan material dengan kebutuhan dan budget Anda, seperti pada proyek <a href="/portofolio/kolam-renang-villa-modern-di-sentul/">Kolam Renang Villa Modern di Sentul</a>.</p>'
    . '<h2>Yang Bisa Anda Lakukan Sekarang</h2>'
    . '<p>Ceritakan gambaran tampilan dan budget yang Anda punya, kami bantu rekomendasikan material yang paling sesuai lewat konsultasi gratis.</p>';

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
