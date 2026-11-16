<?php
/**
 * Skrip sekali-jalan: menerbitkan artikel "Waktu Terbaik Membangun
 * Kolam Renang: Musim Kemarau atau Hujan?".
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

$urlPath = '/artikel/waktu-terbaik-membangun-kolam-renang/';
$title = 'Waktu Terbaik Membangun Kolam Renang: Musim Kemarau atau Hujan?';
$metaTitle = 'Waktu Terbaik Membangun Kolam Renang: Musim Kemarau atau Hujan? | Jasa Kolam Renang Bogor';
$metaDescription = 'Musim kemarau umumnya lebih ideal untuk pembangunan struktur kolam renang. Simak pertimbangan waktu membangun kolam, terutama untuk wilayah Bogor.';
$intro = 'Waktu memulai pembangunan kolam renang bisa memengaruhi kelancaran proyek, terutama di wilayah seperti Bogor yang curah hujannya cukup tinggi sepanjang tahun.';

$coverImage = 'photo.php?id=11';

$content = '<h2>Kelebihan Membangun di Musim Kemarau</h2>'
    . '<p>Pekerjaan struktur seperti penggalian dan pengecoran beton berjalan lebih stabil saat cuaca kering -- risiko tertunda akibat hujan jauh lebih kecil, dan proses pengeringan material seperti beton atau waterproofing juga lebih optimal tanpa gangguan air hujan yang masuk ke area kerja.</p>'
    . '<h2>Membangun di Musim Hujan, Apakah Bisa?</h2>'
    . '<p>Musim hujan tidak sepenuhnya menghalangi proyek pembuatan kolam, tapi butuh penjadwalan yang lebih hati-hati -- terutama untuk tahapan yang sensitif terhadap air seperti pengecoran dan pelapisan waterproofing. Di Bogor yang curah hujannya tinggi, terutama di kawasan seperti Puncak dan Ciawi, fleksibilitas jadwal kerja jadi lebih penting dibanding di daerah dengan cuaca lebih kering.</p>'
    . '<h2>Saran Perencanaan dari Sisi Waktu</h2>'
    . '<p>Kalau memungkinkan, memulai proyek di awal musim kemarau memberi ruang lebih besar untuk tahapan struktur selesai sebelum musim hujan tiba. Tapi ini bukan syarat mutlak -- tim <a href="/layanan/pembuatan-kolam-renang-baru/">pembuatan kolam renang baru</a> kami tetap bisa menyesuaikan jadwal kerja mengikuti kondisi cuaca aktual di lokasi Anda. Untuk gambaran total waktu pengerjaan, baca juga <a href="/artikel/berapa-lama-waktu-pembuatan-kolam-renang-baru/">artikel kami soal berapa lama waktu pembuatan kolam renang baru</a>.</p>'
    . '<h2>Yang Bisa Anda Lakukan Sekarang</h2>'
    . '<p>Hubungi kami untuk konsultasi jadwal proyek yang paling sesuai dengan kondisi lahan dan waktu yang Anda inginkan, tanpa komitmen apa pun.</p>';

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
