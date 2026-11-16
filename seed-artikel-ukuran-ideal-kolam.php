<?php
/**
 * Skrip sekali-jalan: menerbitkan artikel "Ukuran Ideal Kolam Renang
 * untuk Rumah, Sebelum Mulai Membangun" -- mendukung layanan
 * Pembuatan, ditulis untuk calon pelanggan di tahap riset awal
 * sebelum menghubungi kami.
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

$urlPath = '/artikel/ukuran-ideal-kolam-renang-rumah/';
$title = 'Ukuran Ideal Kolam Renang untuk Rumah, Sebelum Mulai Membangun';
$metaTitle = 'Ukuran Ideal Kolam Renang untuk Rumah, Sebelum Mulai Membangun | Jasa Kolam Renang Bogor';
$metaDescription = 'Ukuran kolam renang rumah tergantung luas lahan dan kebutuhan penghuni. Simak panduan ukuran dan kedalaman ideal sebelum mulai membangun.';
$intro = 'Salah satu pertanyaan pertama yang muncul sebelum membangun kolam renang adalah soal ukuran -- terlalu kecil terasa sempit, terlalu besar bisa memboroskan lahan dan biaya perawatan. Berikut gambaran ukuran yang umum dipakai sebagai acuan awal.';

// Belum ada foto yang diambil khusus untuk artikel ini -- pakai foto
// proyek "Kolam Renang Keluarga" dari galeri Portofolio yang sudah ada
// (tema kolam rumah tinggal, paling relevan) sebagai sampul sementara.
// Admin bisa menggantinya kapan saja lewat tab Artikel di panel admin.
$coverImage = 'photo.php?id=14';

$content = '<h2>Ukuran Umum untuk Rumah Tinggal</h2>'
    . '<p>Kolam renang minimalis rumah tinggal biasanya berkisar di panjang 4-8 meter dengan lebar 2-4 meter, cukup untuk berendam dan berenang santai tanpa memakan terlalu banyak lahan. Ukuran ini paling umum kami temui di kawasan perumahan seperti Cibinong dan Yasmin, di mana lahan yang tersedia untuk kolam memang terbatas.</p>'
    . '<h2>Ukuran untuk Villa atau Resort</h2>'
    . '<p>Kolam villa atau resort umumnya dibangun lebih panjang (8 meter ke atas) dan kadang dengan bentuk custom seperti infinity edge, karena kolam di sini sering menjadi bagian utama dari pengalaman properti itu sendiri, bukan sekadar fasilitas tambahan -- seperti beberapa proyek yang kami kerjakan di kawasan Sentul.</p>'
    . '<h2>Kedalaman Ideal: Kolam Keluarga vs Kolam Renang Serius</h2>'
    . '<p>Untuk kolam keluarga yang dipakai bersantai dan oleh anak-anak, kedalaman 1-1,2 meter umumnya sudah cukup dan lebih aman. Kalau tujuannya untuk berenang serius atau olahraga, kedalaman minimal sekitar 1,5 meter lebih disarankan agar gerakan renang tidak terbatas.</p>'
    . '<h2>Ukuran Kolam dan Kebutuhan Sistem Filtrasi</h2>'
    . '<p>Semakin besar volume air, semakin besar pula kapasitas pompa dan filter yang dibutuhkan agar sirkulasi tetap efektif -- ini sebabnya ukuran kolam sebaiknya ditentukan lebih dulu sebelum memilih spesifikasi sistem filtrasi, bukan sebaliknya. Tim <a href="/layanan/pembuatan-kolam-renang-baru/">pembuatan kolam renang baru</a> kami membantu menghitung kebutuhan ini sekaligus saat survei, seperti pada proyek <a href="/portofolio/kolam-renang-keluarga/">Kolam Renang Keluarga</a> yang pernah kami kerjakan di Cibinong.</p>'
    . '<h2>Yang Bisa Anda Lakukan Sekarang</h2>'
    . '<p>Ceritakan luas lahan yang Anda punya dan kebutuhan penghuni rumah, kami bantu rekomendasikan ukuran dan desain yang paling sesuai lewat survei gratis.</p>';

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
