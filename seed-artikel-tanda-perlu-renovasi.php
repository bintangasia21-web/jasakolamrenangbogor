<?php
/**
 * Skrip sekali-jalan: menerbitkan artikel "Tanda-tanda Kolam Renang
 * Anda Sudah Waktunya Direnovasi" -- mengisi gap konten untuk layanan
 * Renovasi, yang sebelumnya belum punya satupun artikel pendukung.
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

$urlPath = '/artikel/tanda-tanda-kolam-renang-perlu-direnovasi/';
$title = 'Tanda-tanda Kolam Renang Anda Sudah Waktunya Direnovasi';
$metaTitle = 'Tanda-tanda Kolam Renang Anda Sudah Waktunya Direnovasi | Jasa Kolam Renang Bogor';
$metaDescription = 'Kolam retak, sering bocor, atau airnya tetap keruh meski rutin dirawat? Kenali tanda-tanda kolam renang Anda sudah waktunya direnovasi.';
$intro = 'Tidak semua masalah kolam renang bisa diselesaikan dengan perawatan rutin saja. Ada titik di mana kerusakan sudah masuk ke struktur atau sistem kolam, dan di situlah renovasi jadi pilihan yang lebih tepat dibanding terus menambal masalah yang sama berulang kali. Berikut tanda-tanda yang bisa Anda kenali sendiri.';

// Belum ada foto yang diambil khusus untuk artikel ini -- pakai foto
// proyek "Renovasi Kolam Renang di Puncak" dari galeri Portofolio yang
// sudah ada (tema renovasi, paling relevan) sebagai sampul sementara.
// Admin bisa menggantinya kapan saja lewat tab Artikel di panel admin.
$coverImage = 'photo.php?id=13';

$content = '<h2>Tanda Fisik yang Mudah Terlihat</h2>'
    . '<ul>'
    . '<li><strong>Retak pada dinding atau lantai kolam:</strong> Retakan kecil bisa melebar seiring waktu dan berisiko menjadi jalan masuk air ke struktur kolam. Kalau sudah muncul retak yang terlihat jelas, menambal sebagian biasanya tidak menyelesaikan akar masalahnya.</li>'
    . '<li><strong>Keramik atau lapisan finishing lepas dan pecah:</strong> Selain mengganggu tampilan, keramik yang lepas bisa jadi titik lemah baru untuk rembesan air ke lapisan di bawahnya.</li>'
    . '<li><strong>Warna cat atau liner memudar drastis:</strong> Lapisan yang sudah terlalu lama terpapar bahan kimia dan sinar matahari kehilangan fungsi pelindungnya, bukan cuma soal tampilan.</li>'
    . '</ul>'
    . '<h2>Tanda dari Kualitas Air</h2>'
    . '<ul>'
    . '<li><strong>Air tetap keruh meski sudah rutin dirawat:</strong> Kalau jadwal perawatan dan dosis kimia sudah benar tapi air tetap bermasalah, kemungkinan sumbernya bukan lagi soal perawatan, melainkan kondisi struktur atau sistem kolam itu sendiri.</li>'
    . '<li><strong>Level air terus turun lebih cepat dari wajar:</strong> Penurunan air yang jauh lebih cepat dibanding penguapan normal adalah indikasi kebocoran struktural, bukan sekadar masalah kecil yang bisa ditambal sendiri.</li>'
    . '</ul>'
    . '<h2>Tanda dari Sistem Kolam</h2>'
    . '<p>Pompa atau filter yang terus-menerus rusak meski sudah sering diperbaiki biasanya menandakan sistem sirkulasi sudah tidak lagi sesuai dengan kebutuhan kolam -- entah karena usia komponen atau karena memang sudah tidak sebanding dengan volume air yang harus diproses setiap hari.</p>'
    . '<h2>Renovasi vs Perawatan Rutin: Kapan Harus yang Mana</h2>'
    . '<p>Perawatan rutin tetap jadi langkah pertama untuk menjaga kualitas air sehari-hari. Tapi begitu masalahnya sudah menyangkut struktur kolam (retak, kebocoran, lapisan rusak) atau sistem yang sudah tidak efektif lagi, <a href="/layanan/renovasi-perbaikan-kolam/">renovasi dan perbaikan kolam</a> adalah langkah yang lebih tepat dibanding terus menambal gejalanya satu per satu. Salah satu contoh pengerjaan kami bisa dilihat di proyek <a href="/portofolio/renovasi-kolam-renang-di-puncak-bogor/">Renovasi Kolam Renang di Puncak</a>.</p>'
    . '<h2>Yang Bisa Anda Lakukan Sekarang</h2>'
    . '<p>Kalau kolam Anda menunjukkan salah satu tanda di atas, hubungi kami untuk survei langsung -- kami bantu menilai apakah kolam Anda cukup diperbaiki sebagian atau sudah perlu renovasi menyeluruh.</p>';

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
