<?php
/**
 * Skrip sekali-jalan: menerbitkan artikel "Pentingnya Skimmer Kolam
 * Renang dan Cara Kerjanya".
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

$urlPath = '/artikel/pentingnya-skimmer-kolam-renang/';
$title = 'Pentingnya Skimmer Kolam Renang dan Cara Kerjanya';
$metaTitle = 'Pentingnya Skimmer Kolam Renang dan Cara Kerjanya | Jasa Kolam Renang Bogor';
$metaDescription = 'Skimmer sering diabaikan padahal perannya besar menjaga kebersihan air. Kenali cara kerja skimmer kolam renang dan kenapa perawatannya penting.';
$intro = 'Skimmer adalah salah satu komponen yang sering kurang diperhatikan pemilik kolam, padahal perannya cukup besar dalam menjaga air tetap bersih sebelum masalah kotoran membesar.';

$coverImage = 'photo.php?id=17';

$content = '<h2>Apa Itu Skimmer dan Cara Kerjanya</h2>'
    . '<p>Skimmer adalah komponen yang menangkap kotoran mengambang di permukaan air -- seperti daun, serangga, atau rambut -- sebelum sempat tenggelam ke dasar kolam atau masuk ke sistem filtrasi utama. Air permukaan ditarik masuk ke skimmer, kotorannya tertahan di keranjang (skimmer basket), lalu air yang lebih bersih diteruskan ke sistem sirkulasi.</p>'
    . '<h2>Kenapa Skimmer Penting</h2>'
    . '<ul>'
    . '<li><strong>Mengurangi beban kerja filter utama:</strong> Kotoran besar yang sudah tertangkap skimmer tidak perlu lagi disaring oleh filter, membuat filter bekerja lebih efisien dan lebih awet.</li>'
    . '<li><strong>Mencegah kotoran mengendap ke dasar:</strong> Kotoran yang dibiarkan tenggelam lebih sulit dibersihkan dibanding saat masih mengambang dan bisa ditangkap skimmer lebih dulu.</li>'
    . '</ul>'
    . '<h2>Perawatan Skimmer Basket Secara Rutin</h2>'
    . '<p>Keranjang skimmer perlu dikosongkan secara rutin -- kalau dibiarkan penuh, aliran air ke sistem sirkulasi jadi terhambat dan kinerja skimmer menurun. Ini salah satu bagian dari menjaga keseluruhan <a href="/artikel/apa-itu-sistem-sirkulasi-kolam-renang/">sistem sirkulasi kolam renang</a> tetap optimal.</p>'
    . '<h2>Yang Bisa Anda Lakukan Sekarang</h2>'
    . '<p>Kalau skimmer di kolam Anda belum berfungsi optimal atau butuh pemasangan baru, tim <a href="/layanan/instalasi-filter-pompa/">instalasi filter &amp; pompa</a> kami bisa bantu cek dan sesuaikan dengan kebutuhan kolam Anda.</p>';

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
