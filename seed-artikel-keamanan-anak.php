<?php
/**
 * Skrip sekali-jalan: menerbitkan artikel "Keamanan Kolam Renang untuk
 * Rumah dengan Anak Kecil, Apa yang Perlu Diperhatikan?".
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

$urlPath = '/artikel/keamanan-kolam-renang-untuk-rumah-dengan-anak/';
$title = 'Keamanan Kolam Renang untuk Rumah dengan Anak Kecil, Apa yang Perlu Diperhatikan?';
$metaTitle = 'Keamanan Kolam Renang untuk Rumah dengan Anak Kecil, Apa yang Perlu Diperhatikan? | Jasa Kolam Renang Bogor';
$metaDescription = 'Punya anak kecil di rumah dengan kolam renang? Simak hal-hal penting soal pagar pengaman, permukaan anti-slip, dan pengawasan yang perlu diperhatikan.';
$intro = 'Kolam renang di rumah dengan anak kecil butuh perhatian ekstra soal keselamatan, bukan cuma soal desain dan kenyamanan. Berikut hal-hal yang perlu dipertimbangkan.';

$coverImage = 'photo.php?id=14';

$content = '<h2>Pagar atau Pembatas Akses Mandiri</h2>'
    . '<p>Pagar pengaman di sekeliling area kolam mencegah anak kecil mengakses kolam tanpa pengawasan orang dewasa. Ini jadi lapisan keamanan tambahan, terutama untuk rumah dengan area kolam yang terhubung langsung ke ruang bermain atau taman tempat anak biasa beraktivitas.</p>'
    . '<h2>Permukaan Anti-Slip di Pinggir Kolam</h2>'
    . '<p>Area sekitar kolam yang basah berisiko licin, terutama untuk anak kecil yang belum terlalu stabil berjalan. Memilih material lantai dengan permukaan anti-slip di sekitar kolam membantu mengurangi risiko terpeleset.</p>'
    . '<h2>Pengawasan Tetap Nomor Satu</h2>'
    . '<p>Pagar dan alat bantu keselamatan lainnya mengurangi risiko, tapi tidak menggantikan pengawasan langsung orang dewasa saat anak berada di sekitar kolam. Alat bantu sebaiknya dipandang sebagai lapisan tambahan, bukan solusi utama.</p>'
    . '<h2>Pertimbangan Sejak Tahap Desain</h2>'
    . '<p>Kalau rumah Anda punya anak kecil, aspek keselamatan ini sebaiknya dipikirkan sejak tahap <a href="/layanan/pembuatan-kolam-renang-baru/">pembuatan kolam renang baru</a> -- termasuk posisi pagar, jenis material lantai, dan tata letak kolam terhadap area bermain anak. Contoh kolam keluarga yang kami kerjakan bisa dilihat di <a href="/portofolio/kolam-renang-keluarga/">Kolam Renang Keluarga</a>.</p>'
    . '<h2>Yang Bisa Anda Lakukan Sekarang</h2>'
    . '<p>Ceritakan kebutuhan keluarga Anda, kami bantu pertimbangkan desain kolam yang memperhatikan aspek keselamatan sejak awal perencanaan.</p>';

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
