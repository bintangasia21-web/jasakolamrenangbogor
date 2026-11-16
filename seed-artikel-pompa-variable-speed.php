<?php
/**
 * Skrip sekali-jalan: menerbitkan artikel "Pompa Variable Speed vs
 * Single Speed, Mana yang Lebih Hemat Energi?".
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

$urlPath = '/artikel/pompa-variable-speed-vs-single-speed/';
$title = 'Pompa Variable Speed vs Single Speed, Mana yang Lebih Hemat Energi?';
$metaTitle = 'Pompa Variable Speed vs Single Speed, Mana yang Lebih Hemat Energi? | Jasa Kolam Renang Bogor';
$metaDescription = 'Pompa variable speed lebih hemat listrik dibanding single speed, tapi harga awalnya lebih tinggi. Simak perbandingannya sebelum memilih.';
$intro = 'Jenis pompa yang dipilih memengaruhi tagihan listrik bulanan dalam jangka panjang, bukan cuma performa sirkulasi air. Berikut perbandingan dua jenis pompa yang paling umum dipakai.';

$coverImage = 'photo.php?id=17';

$content = '<h2>Pompa Single Speed</h2>'
    . '<p>Bekerja pada satu kecepatan tetap, tanpa opsi pengaturan. Harga awalnya lebih murah dan mekanismenya sederhana, tapi karena selalu bekerja di kecepatan penuh setiap kali menyala, konsumsi listriknya cenderung lebih tinggi dibanding yang sebenarnya dibutuhkan kolam pada kondisi normal.</p>'
    . '<h2>Pompa Variable Speed</h2>'
    . '<p>Kecepatannya bisa diatur sesuai kebutuhan -- misalnya kecepatan rendah untuk sirkulasi rutin harian, dan kecepatan lebih tinggi saat dibutuhkan (seperti setelah kolam dipakai banyak orang). Karena bisa bekerja di kecepatan lebih rendah sebagian besar waktu, konsumsi listriknya jauh lebih hemat dalam jangka panjang, meski harga pembelian awalnya lebih tinggi dibanding single speed.</p>'
    . '<h2>Kapan Masing-masing Cocok Dipilih</h2>'
    . '<p>Single speed lebih cocok untuk kolam dengan anggaran awal terbatas dan pemakaian yang relatif sederhana. Variable speed lebih sesuai untuk kolam yang dipakai dalam jangka panjang, karena penghematan listrik dari waktu ke waktu bisa menutupi selisih harga awal yang lebih tinggi. Tim <a href="/layanan/instalasi-filter-pompa/">instalasi filter &amp; pompa</a> kami membantu menyesuaikan pilihan dengan kebutuhan dan anggaran Anda. Kalau pompa lama Anda sudah mulai bermasalah, baca juga <a href="/artikel/kapan-harus-upgrade-pompa-kolam-renang/">artikel kami soal kapan harus upgrade pompa kolam renang</a>.</p>'
    . '<h2>Yang Bisa Anda Lakukan Sekarang</h2>'
    . '<p>Ceritakan kondisi pompa dan kebutuhan kolam Anda saat ini, kami bantu rekomendasikan jenis pompa yang paling sesuai tanpa komitmen apa pun.</p>';

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
