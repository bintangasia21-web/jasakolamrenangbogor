<?php
/**
 * Skrip sekali-jalan: menerbitkan artikel "Cara Menghitung Volume
 * Air Kolam Renang (dan Kenapa Penting)" -- ditautkan dengan
 * seed-artikel-ph-ideal.php yang sudah live (url_path dikonfirmasi:
 * /artikel/cara-menjaga-ph-air-kolam-tetap-ideal/).
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

$urlPath = '/artikel/cara-menghitung-volume-air-kolam-renang/';
$title = 'Cara Menghitung Volume Air Kolam Renang (dan Kenapa Penting)';
$metaTitle = 'Cara Menghitung Volume Air Kolam Renang (dan Kenapa Penting) | Jasa Kolam Renang Bogor';
$metaDescription = 'Dosis bahan kimia kolam yang tepat tergantung volume air, bukan cuma luas permukaan. Simak cara menghitung volume air kolam renang Anda.';
$intro = 'Banyak pemilik kolam menakar bahan kimia hanya berdasarkan perkiraan kasar, padahal dosis yang tepat sebenarnya tergantung volume air, bukan luas permukaan kolam saja. Berikut cara menghitungnya.';

// Belum ada foto yang diambil khusus untuk artikel ini -- pakai foto
// proyek "Perawatan Kolam Renang di Cijeruk" dari galeri Portofolio
// yang sudah ada (tema perawatan air, paling relevan) sebagai sampul
// sementara. Admin bisa menggantinya kapan saja lewat tab Artikel di
// panel admin.
$coverImage = 'photo.php?id=12';

$content = '<h2>Rumus Dasar untuk Kolam Bentuk Persegi</h2>'
    . '<p>Untuk kolam berbentuk persegi atau persegi panjang dengan kedalaman rata, volume air (dalam liter) bisa dihitung dengan rumus: panjang (meter) x lebar (meter) x kedalaman rata-rata (meter) x 1.000. Misalnya kolam 8 x 4 meter dengan kedalaman rata-rata 1,5 meter punya volume sekitar 48.000 liter.</p>'
    . '<h2>Kolam dengan Bentuk Tidak Standar</h2>'
    . '<p>Untuk kolam dengan bentuk tidak beraturan atau kedalaman yang bervariasi (misalnya landai di satu sisi dan dalam di sisi lain), cara paling praktis adalah mengestimasi kedalaman rata-rata (jumlah kedalaman terendah dan tertinggi dibagi dua) sebelum memasukkannya ke rumus yang sama.</p>'
    . '<h2>Kenapa Volume Air Penting Diketahui</h2>'
    . '<p>Dosis bahan kimia seperti klorin dan penyeimbang pH dihitung berdasarkan volume air, bukan luas permukaan kolam. Dua kolam dengan luas permukaan sama tapi kedalaman berbeda akan butuh dosis yang berbeda pula -- kalau volume air tidak diketahui dengan benar, risiko overdosis atau underdosis jadi lebih besar, yang pada akhirnya memengaruhi kestabilan pH air (baca juga <a href="/artikel/cara-menjaga-ph-air-kolam-tetap-ideal/">cara menjaga pH air kolam tetap ideal</a>).</p>'
    . '<h2>Yang Bisa Anda Lakukan Sekarang</h2>'
    . '<p>Kalau Anda belum yakin cara menghitung volume kolam Anda atau dosis kimia yang sesuai, tim kami bisa bantu cek langsung dan sesuaikan jadwal perawatan yang tepat.</p>';

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
