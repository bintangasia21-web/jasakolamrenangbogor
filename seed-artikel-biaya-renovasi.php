<?php
/**
 * Skrip sekali-jalan: menerbitkan artikel "Berapa Biaya Renovasi
 * Kolam Renang di Bogor? Ini yang Menentukan" -- mendukung layanan
 * Renovasi, pasangan dari seed-artikel-tanda-perlu-renovasi.php yang
 * sudah live.
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

$urlPath = '/artikel/berapa-biaya-renovasi-kolam-renang-di-bogor/';
$title = 'Berapa Biaya Renovasi Kolam Renang di Bogor? Ini yang Menentukan';
$metaTitle = 'Berapa Biaya Renovasi Kolam Renang di Bogor? Ini yang Menentukan | Jasa Kolam Renang Bogor';
$metaDescription = 'Biaya renovasi kolam renang di Bogor dipengaruhi tingkat kerusakan, luas area, dan material pengganti. Simak faktor penentu harganya di sini.';
$intro = 'Sama seperti pembuatan kolam baru, biaya renovasi juga tidak bisa dipatok satu angka untuk semua kasus. Tingkat kerusakan setiap kolam berbeda-beda, dan itu yang paling menentukan besar kecilnya biaya renovasi.';

// Belum ada foto yang diambil khusus untuk artikel ini -- pakai foto
// proyek "Perbaikan Kebocoran Kolam" dari galeri Portofolio yang
// sudah ada (tema perbaikan/renovasi, paling relevan) sebagai sampul
// sementara. Admin bisa menggantinya kapan saja lewat tab Artikel di
// panel admin.
$coverImage = 'photo.php?id=15';

$content = '<h2>Faktor yang Menentukan Biaya Renovasi Kolam Renang</h2>'
    . '<ul>'
    . '<li><strong>Tingkat kerusakan:</strong> Kerusakan ringan seperti keramik lepas sebagian jauh lebih murah diperbaiki dibanding kerusakan struktural seperti retak besar atau kebocoran yang sudah merembes ke pondasi.</li>'
    . '<li><strong>Luas area yang direnovasi:</strong> Renovasi sebagian dinding atau lantai tentu berbeda biayanya dengan renovasi menyeluruh seluruh permukaan kolam.</li>'
    . '<li><strong>Jenis material pengganti:</strong> Pilihan mengganti dengan keramik, mosaic, atau liner memengaruhi total biaya, sama seperti pada pembuatan kolam baru.</li>'
    . '<li><strong>Kondisi sistem filtrasi lama:</strong> Kalau pompa atau filter lama juga perlu diganti bersamaan dengan renovasi struktur, ini menambah komponen biaya di luar pekerjaan struktural.</li>'
    . '</ul>'
    . '<h2>Kenapa Kami Tidak Mencantumkan Harga Pasti di Sini</h2>'
    . '<p>Tingkat kerusakan setiap kolam berbeda dan hanya bisa dinilai akurat lewat survei langsung. Karena itu, tim <a href="/layanan/renovasi-perbaikan-kolam/">renovasi dan perbaikan kolam</a> kami selalu memberi estimasi biaya setelah melihat kondisi kolam Anda -- langsung ke lokasi atau lewat foto/video via WhatsApp -- tanpa biaya survei awal. Anda bisa melihat salah satu contoh hasil pengerjaan kami di proyek <a href="/portofolio/renovasi-kolam-resort/">Renovasi Kolam Resort</a>.</p>'
    . '<h2>Yang Bisa Anda Lakukan Sekarang</h2>'
    . '<p>Ceritakan kondisi kolam Anda saat ini, kami bantu hitungkan estimasi biaya renovasi tanpa komitmen apa pun.</p>';

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
