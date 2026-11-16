<?php
/**
 * Skrip sekali-jalan: menerbitkan artikel "Apakah Kolam Renang Perlu
 * Pemanas (Heater)? Ini Pertimbangannya untuk Bogor".
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

$urlPath = '/artikel/apakah-kolam-renang-perlu-pemanas-heater/';
$title = 'Apakah Kolam Renang Perlu Pemanas (Heater)? Ini Pertimbangannya untuk Bogor';
$metaTitle = 'Apakah Kolam Renang Perlu Pemanas (Heater)? Ini Pertimbangannya untuk Bogor | Jasa Kolam Renang Bogor';
$metaDescription = 'Suhu udara Bogor yang lebih sejuk membuat heater kolam renang relevan untuk sebagian pemilik. Simak pertimbangannya sebelum memutuskan pasang atau tidak.';
$intro = 'Pemanas air (heater) bukan komponen wajib di semua kolam renang, tapi relevansinya berbeda-beda tergantung lokasi dan pola pemakaian kolam -- termasuk untuk wilayah Bogor yang suhu udaranya cenderung lebih sejuk dibanding kota-kota dataran rendah.';

$coverImage = 'photo.php?id=11';

$content = '<h2>Fungsi Heater pada Kolam Renang</h2>'
    . '<p>Heater menjaga suhu air tetap nyaman untuk berenang, terutama di waktu-waktu yang biasanya terasa dingin seperti pagi hari, malam hari, atau saat musim hujan. Tanpa heater, suhu air sepenuhnya mengikuti suhu udara sekitar, yang bisa jadi kurang nyaman di jam-jam tertentu.</p>'
    . '<h2>Pertimbangan Khusus untuk Bogor</h2>'
    . '<p>Suhu udara di sebagian wilayah Bogor, terutama kawasan dataran lebih tinggi seperti Puncak dan Sentul, cenderung lebih sejuk dibanding Jakarta dan sekitarnya. Untuk kolam yang sering dipakai pagi atau malam hari, atau yang berada di kawasan bersuhu lebih dingin, heater jadi pertimbangan yang lebih relevan dibanding kolam di kawasan dataran rendah yang lebih hangat sepanjang hari.</p>'
    . '<h2>Bukan Kebutuhan Wajib, Tergantung Pola Pakai</h2>'
    . '<p>Kalau kolam Anda lebih sering dipakai siang hari saat matahari sedang terik, heater mungkin tidak terlalu diperlukan. Tapi untuk vila atau rumah yang kolamnya dipakai kapan saja termasuk pagi dan malam, heater bisa jadi investasi yang sepadan untuk kenyamanan jangka panjang. Keputusan ini sebaiknya dipertimbangkan sejak tahap <a href="/layanan/pembuatan-kolam-renang-baru/">pembuatan kolam renang baru</a>, supaya instalasinya terintegrasi dengan sistem kolam secara keseluruhan.</p>'
    . '<h2>Yang Bisa Anda Lakukan Sekarang</h2>'
    . '<p>Ceritakan pola pemakaian kolam yang Anda rencanakan, kami bantu pertimbangkan apakah heater relevan untuk kebutuhan Anda atau tidak.</p>';

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
