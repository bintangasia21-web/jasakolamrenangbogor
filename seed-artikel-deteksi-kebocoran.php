<?php
/**
 * Skrip sekali-jalan: menerbitkan artikel "Cara Mendeteksi Titik
 * Kebocoran Kolam Renang Sebelum Renovasi".
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

$urlPath = '/artikel/cara-mendeteksi-titik-kebocoran-kolam-renang/';
$title = 'Cara Mendeteksi Titik Kebocoran Kolam Renang Sebelum Renovasi';
$metaTitle = 'Cara Mendeteksi Titik Kebocoran Kolam Renang Sebelum Renovasi | Jasa Kolam Renang Bogor';
$metaDescription = 'Level air kolam terus turun tanpa sebab jelas? Kenali tanda awal dan cara sederhana mendeteksi titik kebocoran sebelum memutuskan renovasi.';
$intro = 'Sebelum memutuskan renovasi, penting untuk memastikan dulu bahwa kolam Anda memang mengalami kebocoran struktural, bukan sekadar penguapan normal. Berikut tanda awal dan cara sederhana untuk mengeceknya sendiri.';

$coverImage = 'photo.php?id=15';

$content = '<h2>Tanda Awal Kebocoran</h2>'
    . '<ul>'
    . '<li><strong>Level air turun lebih cepat dari evaporasi wajar:</strong> Penguapan normal biasanya hanya beberapa milimeter per hari, tergantung cuaca. Penurunan yang jauh lebih cepat dari itu patut dicurigai.</li>'
    . '<li><strong>Area rumput atau tanah di sekitar kolam lebih basah/hijau di satu titik:</strong> Ini bisa jadi tanda air merembes keluar lewat struktur kolam ke tanah di sekitarnya.</li>'
    . '<li><strong>Retak yang terlihat jelas di dinding atau lantai:</strong> Retakan yang sudah terlihat kasat mata hampir selalu berarti ada jalur rembesan, meski belum terasa signifikan dari volume air yang hilang.</li>'
    . '</ul>'
    . '<h2>Metode Deteksi Sederhana: Bucket Test</h2>'
    . '<p>Isi ember dengan air kolam, letakkan di tangga kolam sehingga sebagian terendam, lalu tandai permukaan air di dalam ember dan di kolam dengan spidol. Setelah 24 jam, bandingkan penurunan di ember (murni akibat evaporasi) dengan penurunan di kolam. Kalau penurunan di kolam jauh lebih banyak dibanding di ember, itu indikasi kuat ada kebocoran, bukan cuma penguapan biasa.</p>'
    . '<h2>Kapan Harus Memanggil Profesional</h2>'
    . '<p>Bucket test membantu memastikan ada-tidaknya kebocoran, tapi menemukan titik pastinya di struktur kolam butuh pemeriksaan lebih mendalam. Kalau hasil tes mengindikasikan kebocoran, langkah selanjutnya adalah <a href="/layanan/renovasi-perbaikan-kolam/">renovasi dan perbaikan kolam</a> untuk menemukan dan menutup titik rembesannya secara tuntas -- seperti salah satu proyek yang pernah kami kerjakan di <a href="/portofolio/perbaikan-kebocoran-kolam/">Perbaikan Kebocoran Kolam</a>.</p>'
    . '<h2>Yang Bisa Anda Lakukan Sekarang</h2>'
    . '<p>Kalau bucket test Anda menunjukkan tanda kebocoran, hubungi kami untuk survei langsung dan penilaian tingkat kerusakan sebelum memutuskan langkah perbaikan.</p>';

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
