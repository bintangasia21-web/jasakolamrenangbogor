<?php
/**
 * Skrip sekali-jalan: menerbitkan artikel "Kapan Harus Upgrade Pompa
 * Kolam Renang ke Kapasitas Lebih Besar?" -- mendukung layanan
 * Instalasi Filter & Pompa, ditautkan dengan artikel lama soal tanda
 * pompa perlu diganti (seed-artikel-pompa-diganti.php, url_path
 * dikonfirmasi: /artikel/tanda-pompa-kolam-renang-perlu-diganti/).
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

$urlPath = '/artikel/kapan-harus-upgrade-pompa-kolam-renang/';
$title = 'Kapan Harus Upgrade Pompa Kolam Renang ke Kapasitas Lebih Besar?';
$metaTitle = 'Kapan Harus Upgrade Pompa Kolam Renang? | Jasa Kolam Renang Bogor';
$metaDescription = 'Pompa yang tidak sebanding kapasitas kolam membuat sirkulasi air lambat dan air cepat keruh. Simak tanda-tanda pompa Anda perlu di-upgrade.';
$intro = 'Mengganti pompa yang rusak berbeda dengan upgrade ke kapasitas yang lebih besar -- yang kedua biasanya dibutuhkan ketika kolam berubah ukuran atau pompa lama sejak awal memang kurang sesuai. Berikut tanda-tandanya.';

// Belum ada foto yang diambil khusus untuk artikel ini -- pakai foto
// proyek "Instalasi Sistem Filtrasi" dari galeri Portofolio yang
// sudah ada (tema instalasi, paling relevan) sebagai sampul sementara.
// Admin bisa menggantinya kapan saja lewat tab Artikel di panel admin.
$coverImage = 'photo.php?id=17';

$content = '<h2>Tanda Pompa Sudah Tidak Sebanding Kapasitas Kolam</h2>'
    . '<ul>'
    . '<li><strong>Sirkulasi air terasa lambat:</strong> Air di sudut-sudut kolam terlihat lebih diam dibanding bagian dekat outlet, tanda pompa tidak lagi mampu mengalirkan seluruh volume air secara merata.</li>'
    . '<li><strong>Air cepat keruh meski sudah rutin dirawat:</strong> Kalau jadwal perawatan dan dosis kimia sudah benar tapi air tetap cepat keruh, sirkulasi yang kurang kuat bisa jadi sebabnya, bukan soal perawatan.</li>'
    . '</ul>'
    . '<h2>Kolam yang Baru Direnovasi atau Diperluas</h2>'
    . '<p>Kalau kolam Anda baru saja diperluas atau direnovasi dengan volume air yang lebih besar dari sebelumnya, pompa lama yang dirancang untuk ukuran kolam yang lebih kecil kemungkinan besar sudah tidak lagi mencukupi dan perlu di-upgrade bersamaan.</p>'
    . '<h2>Dampak Pompa Underpowered dalam Jangka Panjang</h2>'
    . '<p>Pompa yang terus-menerus bekerja melebihi kapasitas idealnya lebih cepat mengalami keausan, selain membuat kualitas air sulit terjaga optimal. Kalau pompa Anda sudah menunjukkan tanda-tanda rusak (bukan sekadar kurang kapasitas), baca juga <a href="/artikel/tanda-pompa-kolam-renang-perlu-diganti/">artikel kami soal tanda-tanda pompa perlu diganti</a>. Tim <a href="/layanan/instalasi-filter-pompa/">instalasi filter &amp; pompa</a> kami membantu menilai apakah pompa Anda cukup diganti unit baru dengan spesifikasi sama, atau memang perlu upgrade ke kapasitas lebih besar.</p>'
    . '<h2>Yang Bisa Anda Lakukan Sekarang</h2>'
    . '<p>Ceritakan ukuran kolam dan kondisi pompa Anda saat ini, kami bantu rekomendasikan kapasitas yang sesuai tanpa komitmen apa pun.</p>';

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
