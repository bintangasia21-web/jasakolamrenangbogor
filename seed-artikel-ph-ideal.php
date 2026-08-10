<?php
/**
 * Skrip sekali-jalan: menerbitkan artikel "Cara Menjaga pH Air Kolam
 * Tetap Ideal" (brief 2026-08-10, batch 8 artikel). Pola sama persis
 * dengan seed-artikel-biaya-perawatan.php.
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

$urlPath = '/artikel/cara-menjaga-ph-air-kolam-tetap-ideal/';
$title = 'Cara Menjaga pH Air Kolam Tetap Ideal';
$metaTitle = 'Cara Menjaga pH Air Kolam Renang Tetap Ideal | Jasa Kolam Renang Bogor';
$metaDescription = 'pH air kolam yang tidak seimbang bisa merusak klorin hingga mengiritasi kulit. Simak rentang pH ideal dan cara menjaganya tetap stabil.';
$intro = 'pH air adalah salah satu indikator paling penting dalam perawatan kolam renang, tapi sering luput dari perhatian pemilik kolam yang lebih fokus ke kejernihan air secara visual. Padahal, air yang terlihat jernih pun bisa punya pH yang jauh dari ideal — dan itu memengaruhi banyak hal lain di kolam Anda.';

// Belum ada foto khusus untuk artikel ini -- pakai foto proyek
// perawatan kolam dari galeri Portofolio yang sudah ada (tema perawatan
// air paling relevan). Admin bisa menggantinya lewat tab Artikel.
$coverImage = 'photo.php?id=12';

$content = '<h2>Rentang pH Ideal untuk Kolam Renang</h2>'
    . '<p>Idealnya, pH air kolam renang berada di rentang 7,2 hingga 7,6 — mendekati pH alami air mata manusia, sehingga nyaman untuk kulit dan mata perenang. Rentang ini juga merupakan kondisi optimal bagi klorin untuk bekerja membunuh bakteri dan mikroorganisme secara efektif. Di luar rentang ini, performa klorin turun drastis meski jumlahnya sudah sesuai takaran — jadi menjaga pH sebenarnya sama pentingnya dengan menjaga kadar klorin itu sendiri.</p>'
    . '<h2>Kalau pH Terlalu Tinggi</h2>'
    . '<p>pH di atas 7,6 membuat klorin jadi kurang efektif membasmi bakteri dan alga, meski dosis klorin yang ditambahkan sudah sesuai takaran. Air cenderung terlihat keruh, permukaan kolam bisa berkerak (scaling), dan mata perenang lebih mudah perih meski kadar klorinnya sebenarnya normal. Kerak yang terbentuk akibat pH tinggi juga bisa menumpuk di pipa dan komponen filter dalam jangka panjang, mengurangi efisiensi sistem sirkulasi.</p>'
    . '<h2>Kalau pH Terlalu Rendah</h2>'
    . '<p>Sebaliknya, pH di bawah 7,2 membuat air bersifat lebih asam, yang berisiko mengikis lapisan permukaan kolam (terutama kolam berbahan marmer atau beton) dan mempercepat korosi pada komponen logam seperti tangga atau pipa. Kulit dan mata perenang juga lebih mudah iritasi akibat air yang terlalu asam, dan dalam kasus yang lebih parah, warna liner atau cat kolam bisa memudar lebih cepat dari seharusnya akibat paparan air asam yang berkepanjangan.</p>'
    . '<h2>Apa yang Memengaruhi Perubahan pH</h2>'
    . '<p>pH air kolam tidak diam di satu angka — ia bergerak karena berbagai faktor: air hujan yang masuk, jumlah dan aktivitas perenang, penambahan bahan kimia lain seperti klorin, bahkan penguapan air di hari yang panas. Karena itu, pH bukan sesuatu yang cukup diatur sekali lalu dibiarkan, melainkan perlu dipantau secara berkala supaya penyesuaian bisa dilakukan sebelum bergeser terlalu jauh dari rentang ideal.</p>'
    . '<h2>Cara Mengecek pH Sendiri</h2>'
    . '<p>Test kit pH sederhana (strip atau cairan reagen) bisa digunakan pemilik kolam untuk pengecekan rutin harian atau mingguan — cukup celupkan dan bandingkan warnanya dengan skala yang tersedia. Tapi untuk penyesuaian dosis kimia yang tepat, terutama kalau hasilnya jauh dari rentang ideal, sebaiknya diserahkan ke profesional supaya tidak overdosis atau underdosis yang justru bikin air makin tidak stabil.</p>'
    . '<p>Kalau Anda belum yakin cara membaca hasil test kit atau pH kolam Anda sering naik-turun tanpa sebab jelas, tim kami bisa bantu cek langsung dan sesuaikan jadwal perawatan supaya pH tetap stabil di rentang ideal.</p>';

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
