<?php
/**
 * Skrip sekali-jalan: menerbitkan halaman layanan baru "Pemasangan
 * Pagar Pengaman Kolam" (type='service'). Mengisi gap layanan yang
 * belum punya halaman sendiri, sekaligus jadi target link dari artikel
 * "Keamanan Kolam Renang untuk Rumah dengan Anak Kecil" yang sudah live.
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

$urlPath = '/layanan/pemasangan-pagar-pengaman-kolam/';
$title = 'Pemasangan Pagar Pengaman Kolam';
$metaTitle = 'Pemasangan Pagar Pengaman Kolam Renang di Bogor | Jasa Kolam Renang Bogor';
$metaDescription = 'Pemasangan pagar dan pengaman akses kolam renang untuk mengurangi risiko bagi anak kecil di rumah dengan kolam pribadi. Survei & konsultasi gratis.';
$intro = 'Kolam renang pribadi di rumah dengan anak kecil butuh perhatian ekstra soal keamanan akses. Pagar pengaman membantu membatasi akses mandiri anak ke area kolam saat tidak ada pengawasan langsung.';
$coverImage = 'photo.php?id=14';

$content = '<h2>Pemasangan Pagar Pengaman Kolam di Bogor</h2>'
    . '<p style="color:var(--gray-600)">Kolam renang pribadi di rumah dengan anak kecil butuh perhatian ekstra soal keamanan akses. Pengawasan orang dewasa tetap jadi lapisan pertama yang paling penting, dan pagar atau pembatas akses membantu mengurangi risiko anak masuk ke area kolam tanpa sepengetahuan orang dewasa.</p>'
    . '<h2 style="margin-top:32px">Apa yang Kami Kerjakan</h2>'
    . '<p style="color:var(--gray-600)">Survei area sekitar kolam, lalu pemasangan pagar atau pembatas akses yang disesuaikan dengan kondisi lahan dan desain rumah, termasuk pintu akses dengan pengunci agar hanya bisa dibuka orang dewasa.</p>'
    . '<h2 style="margin-top:32px">Cocok untuk Kolam Baru Maupun Kolam Lama</h2>'
    . '<p style="color:var(--gray-600)">Pengaman kolam bisa direncanakan sejak tahap <a href="/layanan/pembuatan-kolam-renang-baru/" style="color:var(--blue-600);font-weight:600">pembuatan kolam renang baru</a>, atau ditambahkan pada kolam yang sudah berdiri lewat <a href="/layanan/renovasi-perbaikan-kolam/" style="color:var(--blue-600);font-weight:600">renovasi dan perbaikan kolam</a>, tanpa mengganggu struktur utama kolam. Contoh kolam keluarga yang kami kerjakan bisa dilihat di <a href="/portofolio/kolam-renang-keluarga/" style="color:var(--blue-600);font-weight:600">Kolam Renang Keluarga</a>.</p>'
    . '<h2 style="margin-top:32px">Konsultasi Pemasangan Pagar Pengaman</h2>'
    . '<p style="color:var(--gray-600)">Ceritakan kondisi kolam dan kebutuhan keluarga Anda, kami bantu rencanakan pengaman yang sesuai — tanpa komitmen apa pun.</p>'
    . '<p><a href="https://wa.me/6282216623388" style="color:var(--blue-600);font-weight:600">Chat via WhatsApp untuk Konsultasi &rarr;</a></p>';

$faq = [
    ['q' => 'Apakah pagar pengaman bisa dipasang di kolam yang sudah lama ada?', 'a' => 'Bisa, pemasangan disesuaikan dengan kondisi area sekitar kolam yang sudah berdiri.'],
    ['q' => 'Apakah desain pagar bisa disesuaikan dengan tampilan rumah?', 'a' => 'Ya, material dan desain pagar kami sesuaikan dengan karakter rumah atau villa Anda.'],
    ['q' => 'Apakah survei untuk pemasangan pagar pengaman dikenakan biaya?', 'a' => 'Tidak, survei dan konsultasi awal kami berikan gratis tanpa biaya.'],
];

try {
    $pdo = get_db();
    $stmt = $pdo->prepare(
        "INSERT INTO pages (type, url_path, title, meta_title, meta_description, h1, intro, content, cover_image, faq_json, status)
         VALUES ('service', :url_path, :title, :meta_title, :meta_description, :h1, :intro, :content, :cover_image, :faq_json, 'published')
         ON DUPLICATE KEY UPDATE title=VALUES(title), meta_title=VALUES(meta_title),
           meta_description=VALUES(meta_description), h1=VALUES(h1), intro=VALUES(intro),
           content=VALUES(content), cover_image=VALUES(cover_image), faq_json=VALUES(faq_json), status='published'"
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
        ':faq_json' => json_encode($faq, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
    ]);

    respond(true, 'Halaman layanan berhasil diterbitkan.', ['url_path' => $urlPath]);
} catch (Exception $e) {
    respond(false, 'Gagal menerbitkan halaman: ' . $e->getMessage());
}
