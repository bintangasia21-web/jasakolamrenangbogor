<?php
/**
 * Skrip sekali-jalan: menerbitkan artikel pertama untuk fitur /artikel/
 * yang baru dibangun (brief 2026-08-10) -- "Berapa Biaya Perawatan Kolam
 * Renang di Bogor? Ini yang Menentukan".
 *
 * Dijalankan lewat browser (Basic Auth), sama seperti seed-perawatan-
 * content.php. Aman dijalankan berkali-kali -- INSERT ... ON DUPLICATE
 * KEY UPDATE berdasarkan url_path, tidak membuat baris duplikat.
 */
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/inc/db.php';

function respond($success, $message, $extra = []) {
    echo json_encode(array_merge(['success' => $success, 'message' => $message], $extra), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}

$urlPath = '/artikel/berapa-biaya-perawatan-kolam-renang-di-bogor/';
$title = 'Berapa Biaya Perawatan Kolam Renang di Bogor? Ini yang Menentukan';
$metaTitle = 'Berapa Biaya Perawatan Kolam Renang di Bogor? Ini yang Menentukan | Jasa Kolam Renang Bogor';
$metaDescription = 'Biaya perawatan kolam renang di Bogor dipengaruhi ukuran kolam, frekuensi kunjungan, dan kondisi sistem filtrasi. Simak faktor-faktor yang menentukan harga sebelum memilih jasa perawatan.';
$intro = 'Salah satu pertanyaan yang paling sering kami terima dari pemilik kolam di Bogor adalah soal biaya perawatan rutin. Jawaban jujurnya: tidak ada satu angka pasti yang berlaku untuk semua kolam, karena beberapa faktor berikut ikut menentukan.';

// Belum ada foto yang diambil khusus untuk artikel ini -- pakai foto
// proyek "Perawatan Kolam Rutin" dari galeri Portofolio yang sudah ada
// (tema paling relevan) sebagai sampul sementara. Admin bisa menggantinya
// kapan saja lewat tab Artikel di panel admin.
$coverImage = 'photo.php?id=16';

$content = '<h2>Faktor yang Menentukan Biaya Perawatan Kolam Renang</h2>'
    . '<ul>'
    . '<li><strong>Ukuran dan volume kolam:</strong> Semakin besar kolam, semakin banyak air yang perlu diseimbangkan kimianya dan semakin lama waktu pembersihan fisik. Kolam minimalis rumah tinggal jelas berbeda kebutuhannya dibanding kolam villa atau resort.</li>'
    . '<li><strong>Frekuensi kunjungan:</strong> Perawatan mingguan tentu berbeda total biayanya dengan perawatan bulanan. Kolam yang dipakai setiap hari umumnya butuh kunjungan lebih sering dibanding kolam vila yang hanya ramai di akhir pekan, seperti banyak kami temui di kawasan Sentul dan Puncak.</li>'
    . '<li><strong>Kondisi sistem filtrasi yang ada:</strong> Kolam dengan sistem filtrasi yang sudah lama tidak dirawat biasanya butuh penanganan awal lebih intensif (backwash, penggantian pasir filter, atau pembersihan mendalam) sebelum masuk ke jadwal rutin biasa.</li>'
    . '<li><strong>Karakteristik cuaca Bogor:</strong> Curah hujan tinggi khas Bogor, terutama di area seperti Puncak dan Cisarua, bisa mempercepat perubahan kualitas air, sehingga sebagian pelanggan kami memilih frekuensi kunjungan sedikit lebih sering di musim hujan.</li>'
    . '<li><strong>Aksesibilitas lokasi:</strong> Meski seluruh area layanan kami (Sentul, Ciawi, Cibinong, Bogor Kota, dan sekitarnya) sudah masuk cakupan rutin tim, lokasi yang lebih jauh dari rute normal kadang memengaruhi penjadwalan.</li>'
    . '</ul>'
    . '<h2>Kenapa Kami Tidak Mencantumkan Harga Pasti di Sini</h2>'
    . '<p>Menyebut satu angka tanpa tahu kondisi kolam Anda justru berisiko menyesatkan. Karena itu, kami selalu memberi estimasi biaya setelah survei singkat — langsung ke lokasi atau lewat foto/video via WhatsApp — tanpa biaya survei awal.</p>'
    . '<h2>Yang Bisa Anda Lakukan Sekarang</h2>'
    . '<p>Hubungi kami dan sebutkan ukuran kolam serta kondisinya saat ini, kami bantu hitungkan tanpa komitmen apa pun.</p>';

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
