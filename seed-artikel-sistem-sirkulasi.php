<?php
/**
 * Skrip sekali-jalan: menerbitkan artikel "Apa Itu Sistem Sirkulasi
 * Kolam Renang dan Kenapa Penting?" -- mendukung layanan Instalasi
 * Filter & Pompa, ditautkan dengan seed-artikel-jenis-filter-
 * kolam.php yang sudah live.
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

$urlPath = '/artikel/apa-itu-sistem-sirkulasi-kolam-renang/';
$title = 'Apa Itu Sistem Sirkulasi Kolam Renang dan Kenapa Penting?';
$metaTitle = 'Apa Itu Sistem Sirkulasi Kolam Renang dan Kenapa Penting? | Jasa Kolam Renang Bogor';
$metaDescription = 'Sistem sirkulasi menentukan apakah air kolam tetap bersih dan sehat. Kenali cara kerja dan komponennya sebelum memilih sistem untuk kolam Anda.';
$intro = 'Air kolam yang terlihat jernih sebenarnya bergantung pada satu hal yang jarang disadari pemiliknya: sistem sirkulasi yang bekerja terus-menerus di belakang layar. Berikut penjelasan singkatnya.';

// Belum ada foto yang diambil khusus untuk artikel ini -- pakai foto
// proyek "Instalasi Sistem Filtrasi" dari galeri Portofolio yang
// sudah ada (tema instalasi/sirkulasi, paling relevan) sebagai
// sampul sementara. Admin bisa menggantinya kapan saja lewat tab
// Artikel di panel admin.
$coverImage = 'photo.php?id=17';

$content = '<h2>Definisi Sistem Sirkulasi</h2>'
    . '<p>Sistem sirkulasi adalah proses air kolam yang terus-menerus disedot, disaring lewat filter, diberi bahan kimia penyeimbang, lalu dialirkan kembali ke kolam lewat pompa -- berulang terus selama kolam menyala. Proses ini yang menjaga air tetap bersih dan aman, bukan cuma sekali pembersihan lalu diam.</p>'
    . '<h2>Kenapa Sirkulasi Penting</h2>'
    . '<p>Tanpa sirkulasi yang baik, bahan kimia seperti klorin tidak tersebar merata ke seluruh bagian kolam, sehingga sebagian area bisa tetap jadi tempat tumbuhnya alga meski sisanya terlihat bersih. Air yang diam atau mengalir terlalu lambat juga lebih mudah menumpuk kotoran dan mikroorganisme dibanding air yang terus bersirkulasi.</p>'
    . '<h2>Komponen yang Terlibat</h2>'
    . '<ul>'
    . '<li><strong>Skimmer:</strong> menangkap kotoran di permukaan air sebelum masuk ke sistem.</li>'
    . '<li><strong>Pompa:</strong> menggerakkan aliran air ke seluruh sistem.</li>'
    . '<li><strong>Filter:</strong> menyaring partikel kotoran dari air (lihat juga <a href="/artikel/jenis-sistem-filtrasi-kolam-renang/">jenis-jenis filter kolam renang</a>).</li>'
    . '<li><strong>Pipa inlet/outlet:</strong> jalur air masuk dan keluar yang menentukan seberapa merata sirkulasi di seluruh kolam.</li>'
    . '</ul>'
    . '<p>Tim <a href="/layanan/instalasi-filter-pompa/">instalasi filter &amp; pompa</a> kami merancang seluruh komponen ini agar bekerja sesuai ukuran dan bentuk kolam Anda, bukan sekadar memasang unit standar tanpa penyesuaian.</p>'
    . '<h2>Yang Bisa Anda Lakukan Sekarang</h2>'
    . '<p>Kalau Anda belum yakin apakah sistem sirkulasi kolam Anda sudah berjalan optimal, hubungi kami untuk pengecekan langsung tanpa komitmen apa pun.</p>';

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
