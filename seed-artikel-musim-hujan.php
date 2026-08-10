<?php
/**
 * Skrip sekali-jalan: menerbitkan artikel "Kenapa Air Kolam Cepat Keruh
 * Saat Musim Hujan di Bogor" (brief 2026-08-10, batch 8 artikel). Pola
 * sama persis dengan seed-artikel-biaya-perawatan.php.
 *
 * Dijalankan lewat browser (Basic Auth). Aman dijalankan berkali-kali --
 * INSERT ... ON DUPLICATE KEY UPDATE berdasarkan url_path.
 */
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/inc/db.php';

function respond($success, $message, $extra = []) {
    echo json_encode(array_merge(['success' => $success, 'message' => $message], $extra), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}

$urlPath = '/artikel/kenapa-air-kolam-keruh-saat-musim-hujan-bogor/';
$title = 'Kenapa Air Kolam Cepat Keruh Saat Musim Hujan di Bogor';
$metaTitle = 'Kenapa Air Kolam Cepat Keruh Saat Musim Hujan di Bogor | Jasa Kolam Renang Bogor';
$metaDescription = 'Curah hujan tinggi di Bogor membuat air kolam renang lebih cepat keruh. Kenali penyebabnya dan cara mencegahnya sebelum musim hujan berikutnya tiba.';
$intro = 'Bogor dikenal dengan curah hujan yang tinggi sepanjang tahun, dan ini jadi tantangan tersendiri bagi pemilik kolam renang outdoor. Kalau Anda merasa air kolam lebih cepat keruh di musim hujan dibanding musim kemarau, itu bukan kebetulan — ada beberapa faktor spesifik yang menyebabkannya.';

// Belum ada foto khusus untuk artikel ini -- pakai foto proyek "Renovasi
// Kolam Renang di Puncak Bogor" dari galeri Portofolio yang sudah ada
// (Puncak dikenal sebagai kawasan dengan curah hujan tinggi, relevan
// dengan tema artikel). Admin bisa menggantinya lewat tab Artikel.
$coverImage = 'photo.php?id=13';

$content = '<h2>Air Hujan Mengencerkan Kadar Klorin</h2>'
    . '<p>Setiap kali hujan turun ke kolam, volume air bertambah sementara kadar klorin di dalamnya tidak ikut bertambah — hasilnya, konsentrasi klorin jadi lebih encer dari seharusnya. Kolam yang tadinya sudah seimbang bisa jadi kurang terlindungi begitu hujan deras mengguyur, apalagi kalau terjadi berhari-hari berturut-turut seperti yang sering terjadi di Bogor pada bulan-bulan puncak musim hujan. Kolam ukuran standar rumah tinggal yang kehujanan semalaman saja sudah cukup untuk menggeser kadar klorin keluar dari rentang aman, terutama kalau sebelumnya sudah mepet batas bawah.</p>'
    . '<h2>Kotoran Organik yang Terbawa Air Hujan</h2>'
    . '<p>Air hujan tidak datang sendirian — debu, serbuk daun, dan partikel organik dari atap serta lingkungan sekitar ikut terbawa masuk ke kolam. Material organik ini menjadi "makanan" bagi alga dan bakteri, mempercepat pertumbuhan lumut yang membuat air terlihat keruh atau kehijauan meski baru beberapa hari sejak perawatan terakhir. Kolam yang berada di bawah pepohonan atau dekat atap tanpa talang yang baik biasanya mengalami masalah ini lebih parah dibanding kolam di area terbuka.</p>'
    . '<h2>Daun dan Serangga yang Berguguran</h2>'
    . '<p>Musim hujan biasanya berbarengan dengan angin kencang yang menerbangkan daun dan ranting ke permukaan kolam. Kalau tidak segera diangkat, sampah organik ini tenggelam dan membusuk di dasar kolam, jadi sumber nutrisi tambahan untuk alga sekaligus mengganggu kejernihan air. Serangga yang mencari tempat berlindung dari hujan juga sering berakhir di permukaan kolam, menambah beban kerja skimmer dan filter dari yang seharusnya.</p>'
    . '<h2>Kolam yang Rutin Dirawat Lebih Tahan Terhadap Musim Hujan</h2>'
    . '<p>Kabar baiknya, dampak musim hujan ini jauh lebih ringan pada kolam yang memang sudah dirawat rutin sepanjang tahun dibanding kolam yang perawatannya sudah telat sebelum hujan turun. Kolam dengan kadar klorin dan pH yang sudah stabil di rentang ideal punya "ruang toleransi" lebih besar sebelum keluar dari batas aman akibat pengenceran air hujan, sementara kolam yang kondisinya sudah pas-pasan akan langsung terasa dampaknya begitu hujan deras datang.</p>'
    . '<h2>Yang Bisa Dilakukan Selama Musim Hujan</h2>'
    . '<p>Beberapa penyesuaian sederhana bisa membantu: pertama, tingkatkan frekuensi pengecekan kimia air selama musim hujan deras, jangan menunggu jadwal rutin bulanan seperti biasa. Kedua, pertimbangkan memasang cover/penutup kolam saat tidak digunakan, terutama untuk kolam yang sering ditinggal beberapa hari. Ketiga, segera angkat daun dan kotoran yang mengambang setelah hujan reda, jangan dibiarkan menumpuk. Keempat, pastikan sistem drainase di sekitar kolam berfungsi baik supaya air hujan dari halaman tidak ikut mengalir masuk ke kolam dan menambah beban pengenceran.</p>'
    . '<p>Kalau kolam Anda sering keruh setiap musim hujan tiba, kami bisa bantu evaluasi penyebab spesifiknya dan sesuaikan jadwal perawatan supaya air tetap jernih sepanjang tahun — termasuk di bulan-bulan hujan terberat.</p>';

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
