<?php
/**
 * Skrip sekali-jalan: menerbitkan artikel "Tips Merawat Kolam Renang
 * Vila yang Jarang Dipakai" (brief 2026-08-10, batch 8 artikel). Pola
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

$urlPath = '/artikel/tips-merawat-kolam-renang-vila-jarang-dipakai/';
$title = 'Tips Merawat Kolam Renang Vila yang Jarang Dipakai';
$metaTitle = 'Tips Merawat Kolam Renang Vila yang Jarang Dipakai | Jasa Kolam Renang Bogor';
$metaDescription = 'Kolam renang vila di Sentul, Rancamaya, dan Bogor Raya sering kosong di hari kerja. Simak cara merawatnya agar tetap jernih meski jarang dipakai.';
$intro = 'Banyak pemilik vila di kawasan seperti Sentul, Rancamaya, dan Bogor Raya hanya menggunakan propertinya di akhir pekan atau musim liburan, sementara kolam renangnya dibiarkan kosong sepanjang hari kerja. Pola pemakaian musiman seperti ini punya tantangan perawatan tersendiri dibanding kolam rumah yang dipakai setiap hari.';

// Belum ada foto khusus untuk artikel ini -- pakai foto proyek "Kolam
// Renang Villa Modern di Sentul" dari galeri Portofolio yang sudah ada
// (tema vila Sentul paling relevan). Admin bisa menggantinya lewat tab
// Artikel.
$coverImage = 'photo.php?id=11';

$content = '<h2>Risiko Air yang Didiamkan Terlalu Lama</h2>'
    . '<p>Air kolam yang tidak dipakai bukan berarti aman dari masalah — justru sebaliknya. Tanpa sirkulasi dan pemakaian aktif, kadar klorin perlahan menurun karena terurai oleh sinar matahari, sementara nutrisi organik dari debu dan dedaunan yang jatuh tetap terus masuk. Kombinasi ini membuat kolam yang jarang dipakai justru lebih rentan berlumut dan airnya tidak seimbang dibanding kolam yang dipakai rutin dan diawasi setiap hari. Dalam beberapa kasus yang kami tangani, kolam vila yang dibiarkan lebih dari dua minggu tanpa pengecekan sama sekali sudah cukup berubah warna kehijauan begitu pemilik datang kembali.</p>'
    . '<h2>Sirkulasi Pompa Tetap Harus Berjalan</h2>'
    . '<p>Salah satu kesalahan yang sering kami temui adalah mematikan total sistem sirkulasi saat vila sedang kosong, dengan asumsi menghemat listrik karena tidak ada yang berenang. Padahal, sirkulasi air yang berhenti total justru mempercepat pertumbuhan alga karena air jadi diam dan hangat. Idealnya, pompa tetap dijalankan minimal beberapa jam sehari meski kolam tidak dipakai, supaya air tetap bersirkulasi dan bahan kimia tersebar merata di seluruh kolam. Timer otomatis pada pompa bisa jadi solusi praktis untuk memastikan sirkulasi tetap berjalan sesuai jadwal tanpa perlu ada yang mengoperasikan secara manual dari jauh.</p>'
    . '<h2>Kontrak Perawatan Terjadwal Meski Kolam Kosong</h2>'
    . '<p>Untuk vila dengan pola hunian musiman, kami biasanya menyarankan kontrak perawatan terjadwal yang tetap berjalan meski pemilik sedang tidak berada di lokasi — bukan hanya perawatan menjelang kedatangan tamu. Kunjungan rutin ini mencakup pengecekan kimia air, pembersihan permukaan, dan memastikan sistem sirkulasi tetap berfungsi, supaya begitu pemilik atau tamu datang, kolam sudah dalam kondisi siap pakai tanpa perlu penanganan darurat mendadak.</p>'
    . '<h2>Koordinasi dengan Pengelola atau Penjaga Vila</h2>'
    . '<p>Banyak vila di kawasan Sentul, Rancamaya, dan Bogor Raya punya penjaga atau pengelola yang tinggal di lokasi meski pemilik sedang tidak ada. Melibatkan mereka dalam koordinasi jadwal kunjungan tim perawatan sangat membantu — mulai dari memastikan akses masuk saat kunjungan rutin, sampai melaporkan kondisi kolam di antara jadwal kunjungan kalau ada perubahan yang mencolok, seperti daun menumpuk setelah hujan deras.</p>'
    . '<h2>Persiapan Sebelum Kedatangan Tamu</h2>'
    . '<p>Selain perawatan rutin, layanan pembersihan menjelang akhir pekan atau musim liburan juga penting untuk vila dengan pola pemakaian musiman — memastikan air benar-benar jernih dan seimbang tepat saat dibutuhkan, bukan cuma "cukup bersih" menurut jadwal rutin biasa.</p>'
    . '<p>Kalau vila Anda di kawasan Sentul, Rancamaya, Bogor Raya, atau sekitarnya sering kosong di hari kerja, kami bisa bantu susun jadwal perawatan yang sesuai dengan pola hunian musiman tersebut — supaya kolam selalu siap pakai kapan pun Anda atau tamu datang.</p>';

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
