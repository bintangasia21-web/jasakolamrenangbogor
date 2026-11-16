<?php
/**
 * Skrip sekali-jalan: menerbitkan artikel "Air Kolam Berbusa: Kenali
 * Sebab dan Cara Mengatasinya".
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

$urlPath = '/artikel/air-kolam-berbusa-sebab-dan-cara-mengatasi/';
$title = 'Air Kolam Berbusa: Kenali Sebab dan Cara Mengatasinya';
$metaTitle = 'Air Kolam Berbusa: Kenali Sebab dan Cara Mengatasinya | Jasa Kolam Renang Bogor';
$metaDescription = 'Air kolam renang Anda tiba-tiba berbusa? Kenali penyebab umumnya, mulai dari residu losion hingga produk kimia yang kurang tepat, dan cara mengatasinya.';
$intro = 'Busa yang muncul di permukaan air kolam sering membuat pemilik kolam khawatir, padahal penyebabnya biasanya cukup sederhana dan bisa dikenali sendiri sebelum memutuskan langkah penanganannya.';

$coverImage = 'photo.php?id=16';

$content = '<h2>Penyebab Umum Air Kolam Berbusa</h2>'
    . '<ul>'
    . '<li><strong>Residu losion, sabun, atau kosmetik:</strong> Produk yang menempel di tubuh perenang ikut larut ke air dan bisa memicu busa, terutama di kolam yang dipakai banyak orang sekaligus.</li>'
    . '<li><strong>Produk kimia kolam berkualitas rendah:</strong> Beberapa produk algaecide atau clarifier murah mengandung bahan yang cenderung menimbulkan busa dibanding produk sejenis yang lebih stabil.</li>'
    . '<li><strong>Deterjen atau pembersih yang salah dipakai:</strong> Membersihkan area sekitar kolam dengan deterjen biasa yang kemudian terbawa air hujan atau cipratan ke dalam kolam juga bisa jadi sumbernya.</li>'
    . '</ul>'
    . '<h2>Cara Mengatasi dan Mencegahnya</h2>'
    . '<p>Busa ringan biasanya mereda sendiri setelah sirkulasi dan filtrasi bekerja beberapa waktu, tapi kalau terus muncul berulang, sumbernya perlu ditelusuri lebih dulu -- apakah dari kebiasaan pemakaian kolam atau dari produk kimia yang dipakai. Mengganti ke produk perawatan yang lebih stabil dan membersihkan area sekitar kolam dengan produk yang aman untuk kolam renang biasanya cukup untuk mencegah busa muncul lagi.</p>'
    . '<h2>Yang Bisa Anda Lakukan Sekarang</h2>'
    . '<p>Kalau busa di kolam Anda terus muncul dan tidak jelas sebabnya, tim <a href="/layanan/jasa-perawatan-kolam-renang/">jasa perawatan kolam renang</a> kami bisa bantu cek kondisi air dan produk yang dipakai secara langsung.</p>';

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
