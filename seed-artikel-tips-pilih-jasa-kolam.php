<?php
/**
 * Skrip sekali-jalan: menerbitkan artikel "Tips Memilih Jasa Kolam
 * Renang yang Terpercaya di Bogor" -- artikel umum (bukan spesifik 1
 * layanan), dimaksudkan untuk menangkap pencarian di tahap
 * perbandingan vendor. Fakta bisnis (10+ tahun, 350+ proyek) memakai
 * angka yang sama dengan yang sudah dipublikasikan di halaman lain,
 * tidak ada angka baru yang dikarang.
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

$urlPath = '/artikel/tips-memilih-jasa-kolam-renang-terpercaya-di-bogor/';
$title = 'Tips Memilih Jasa Kolam Renang yang Terpercaya di Bogor';
$metaTitle = 'Tips Memilih Jasa Kolam Renang yang Terpercaya di Bogor | Jasa Kolam Renang Bogor';
$metaDescription = 'Sebelum memilih jasa kolam renang di Bogor, perhatikan portofolio nyata, garansi pekerjaan, dan kejelasan estimasi biaya. Simak tipsnya di sini.';
$intro = 'Dengan banyaknya pilihan jasa kolam renang di Bogor, memilih yang benar-benar terpercaya kadang membingungkan. Berikut beberapa hal yang bisa Anda periksa sebelum memutuskan.';

// Belum ada foto yang diambil khusus untuk artikel ini -- pakai foto
// proyek "Perawatan Kolam Rutin" dari galeri Portofolio yang sudah
// ada sebagai sampul sementara. Admin bisa menggantinya kapan saja
// lewat tab Artikel di panel admin.
$coverImage = 'photo.php?id=16';

$content = '<h2>Cek Pengalaman dan Portofolio Nyata</h2>'
    . '<p>Testimoni teks saja mudah dibuat-buat, tapi portofolio proyek nyata dengan foto dan detail lokasi jauh lebih bisa diverifikasi. Lihat apakah jasa yang Anda pertimbangkan punya contoh pekerjaan nyata yang bisa dilihat langsung, misalnya lewat <a href="/portofolio/">halaman portofolio</a> yang menampilkan proyek-proyek yang pernah dikerjakan.</p>'
    . '<h2>Tanya Detail Garansi Pekerjaan</h2>'
    . '<p>Jasa yang bertanggung jawab biasanya bersedia menjelaskan garansi pekerjaan secara spesifik -- berapa lama, mencakup apa, dan bagaimana prosedurnya kalau ada masalah setelah pekerjaan selesai. Kalau jasa ragu atau tidak jelas soal ini, sebaiknya jadi pertimbangan tambahan sebelum memutuskan.</p>'
    . '<h2>Minta Estimasi Tertulis Setelah Survei</h2>'
    . '<p>Harga yang disebutkan tanpa melihat kondisi kolam Anda langsung berisiko tidak akurat -- baik terlalu rendah (lalu membengkak di tengah jalan) maupun asal tinggi. Estimasi yang baik biasanya diberikan setelah survei singkat, baik langsung ke lokasi maupun lewat foto/video.</p>'
    . '<h2>Perhatikan Respons Komunikasi</h2>'
    . '<p>Cara jasa merespons pertanyaan Anda di awal -- termasuk seberapa cepat dan jelas balasannya lewat WhatsApp -- sering jadi indikator sederhana namun cukup akurat soal bagaimana pelayanan mereka nantinya selama proyek berjalan.</p>'
    . '<h2>Yang Bisa Anda Lakukan Sekarang</h2>'
    . '<p>Kami sudah menangani pembuatan, perawatan, renovasi, dan instalasi kolam renang selama lebih dari 10 tahun, dengan lebih dari 350 proyek yang telah kami selesaikan di wilayah Bogor dan sekitarnya. Lihat seluruh <a href="/layanan/">layanan kami</a>, atau hubungi kami langsung untuk konsultasi dan survei gratis tanpa komitmen apa pun.</p>';

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
