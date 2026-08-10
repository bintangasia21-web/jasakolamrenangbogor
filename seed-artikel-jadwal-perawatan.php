<?php
/**
 * Skrip sekali-jalan: menerbitkan artikel kedua untuk fitur /artikel/
 * (brief 2026-08-10) -- "Jadwal Ideal Perawatan Kolam Renang Rumah,
 * Berapa Kali Sebulan?". Pola sama persis dengan
 * seed-artikel-biaya-perawatan.php.
 *
 * Dijalankan lewat browser (Basic Auth). Aman dijalankan berkali-kali --
 * INSERT ... ON DUPLICATE KEY UPDATE berdasarkan url_path, tidak
 * membuat baris duplikat.
 */
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/inc/db.php';

function respond($success, $message, $extra = []) {
    echo json_encode(array_merge(['success' => $success, 'message' => $message], $extra), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}

$urlPath = '/artikel/jadwal-ideal-perawatan-kolam-renang-rumah/';
$title = 'Jadwal Ideal Perawatan Kolam Renang Rumah, Berapa Kali Sebulan?';
$metaTitle = 'Jadwal Ideal Perawatan Kolam Renang Rumah, Berapa Kali Sebulan? | Jasa Kolam Renang Bogor';
$metaDescription = 'Frekuensi ideal perawatan kolam renang tergantung intensitas pemakaian dan cuaca. Simak panduan jadwal perawatan yang tepat untuk kolam rumah di Bogor.';
$intro = 'Salah satu kesalahan paling umum pemilik kolam renang adalah menunggu air terlihat kotor baru memanggil jasa perawatan. Padahal, kolam yang dirawat sesuai jadwal justru lebih hemat dalam jangka panjang — mencegah masalah besar sebelum terjadi.';

$content = '<h2>Frekuensi Ideal Berdasarkan Pola Pemakaian</h2>'
    . '<ul>'
    . '<li><strong>Kolam yang dipakai setiap hari:</strong> idealnya dicek 1 kali seminggu. Pemakaian harian mempercepat perubahan kadar klorin dan pH, terutama kalau sering dipakai anak-anak atau banyak orang sekaligus.</li>'
    . '<li><strong>Kolam yang dipakai akhir pekan saja</strong> (banyak kami temui di vila kawasan Sentul dan Puncak): 2 minggu sekali umumnya cukup, tapi perlu penyesuaian dosis kimia karena air "didiamkan" lebih lama dibanding kolam harian.</li>'
    . '<li><strong>Musim hujan vs kemarau:</strong> curah hujan tinggi khas Bogor bisa mengubah keseimbangan air lebih cepat dari biasanya — kami sering menyarankan pelanggan menambah 1 kunjungan ekstra di bulan-bulan hujan deras, terutama untuk kolam outdoor tanpa penutup.</li>'
    . '</ul>'
    . '<h2>Tanda Kolam Butuh Perawatan di Luar Jadwal</h2>'
    . '<p>Jangan tunggu jadwal rutin kalau ini terjadi: air mulai berubah warna kehijauan, muncul bau klorin menyengat, atau permukaan lantai kolam terasa licin berlumut.</p>'
    . '<h2>Yang Termasuk dalam Kunjungan Rutin Kami</h2>'
    . '<p>Pengecekan pH dan klorin, pembersihan permukaan dan dasar kolam, pengecekan filter, serta laporan singkat kondisi kolam setiap kunjungan.</p>'
    . '<p>Kalau Anda belum yakin jadwal mana yang cocok untuk kolam Anda, kami bisa bantu tentukan berdasarkan ukuran, lokasi, dan pola pemakaian — tanpa biaya konsultasi awal.</p>';

try {
    $pdo = get_db();
    $stmt = $pdo->prepare(
        "INSERT INTO pages (type, url_path, title, meta_title, meta_description, h1, intro, content, status)
         VALUES ('article', :url_path, :title, :meta_title, :meta_description, :h1, :intro, :content, 'published')
         ON DUPLICATE KEY UPDATE title=VALUES(title), meta_title=VALUES(meta_title),
           meta_description=VALUES(meta_description), h1=VALUES(h1), intro=VALUES(intro),
           content=VALUES(content), status='published'"
    );
    $stmt->execute([
        ':url_path' => $urlPath,
        ':title' => $title,
        ':meta_title' => $metaTitle,
        ':meta_description' => $metaDescription,
        ':h1' => $title,
        ':intro' => $intro,
        ':content' => $content,
    ]);

    respond(true, 'Artikel berhasil diterbitkan.', ['url_path' => $urlPath]);
} catch (Exception $e) {
    respond(false, 'Gagal menerbitkan artikel: ' . $e->getMessage());
}
