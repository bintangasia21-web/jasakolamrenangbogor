<?php
/**
 * Skrip sekali-jalan: menerbitkan artikel "Kolam Berbau Kaporit
 * Menyengat? Ini Sebabnya dan Cara Mengatasinya" -- meluruskan mitos
 * umum bahwa bau menyengat berarti klorin berlebih (sebenarnya
 * sebaliknya).
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

$urlPath = '/artikel/kolam-berbau-kaporit-menyengat/';
$title = 'Kolam Berbau Kaporit Menyengat? Ini Sebabnya dan Cara Mengatasinya';
$metaTitle = 'Kolam Berbau Kaporit Menyengat? Ini Sebabnya dan Cara Mengatasinya | Jasa Kolam Renang Bogor';
$metaDescription = 'Bau kaporit menyengat sebenarnya tanda klorin kurang, bukan berlebih. Kenali sebab sesungguhnya dan cara mengatasi bau menyengat di kolam Anda.';
$intro = 'Banyak orang mengira bau kaporit yang menyengat berarti air kolam kelebihan klorin, padahal faktanya justru kebalikannya. Memahami sebab sebenarnya penting supaya penanganannya tidak salah arah.';

$coverImage = 'photo.php?id=16';

$content = '<h2>Mitos yang Perlu Diluruskan</h2>'
    . '<p>Bau kaporit yang menyengat bukan tanda klorin terlalu banyak, melainkan tanda klorin <em>bebas</em> (yang aktif membasmi bakteri) sudah terlalu rendah. Bau menyengat sebenarnya berasal dari kloramin -- senyawa yang terbentuk saat klorin bereaksi dengan kotoran organik seperti keringat, urine, atau losion dari tubuh perenang. Semakin banyak kloramin terbentuk, semakin menyengat baunya, dan ini terjadi justru karena klorin aktif sudah terlalu sedikit untuk membasmi kotoran organik secara tuntas.</p>'
    . '<h2>Penyebab Umum</h2>'
    . '<ul>'
    . '<li><strong>Kadar klorin bebas rendah:</strong> Dosis klorin yang tidak mencukupi kebutuhan kolam, terutama setelah dipakai banyak orang.</li>'
    . '<li><strong>pH tidak seimbang:</strong> pH di luar rentang ideal membuat klorin bekerja kurang efektif meski dosisnya sudah sesuai takaran.</li>'
    . '<li><strong>Jarang dilakukan shock treatment:</strong> Tanpa penambahan dosis klorin tinggi secara berkala untuk "mengejar" penumpukan kloramin, bau menyengat akan terus muncul berulang.</li>'
    . '</ul>'
    . '<h2>Cara Mengatasinya</h2>'
    . '<p>Langkah pertama adalah mengecek kadar klorin bebas dan pH, lalu menyesuaikan dosis sesuai hasil pengukuran -- bukan langsung mengurangi klorin seperti asumsi umum. Kalau bau sudah cukup mengganggu, shock treatment (dosis klorin tinggi dalam waktu singkat) biasanya dibutuhkan untuk memecah kloramin yang menumpuk. Untuk menjaga pH tetap stabil sebagai pendukung kerja klorin, Anda bisa baca <a href="/artikel/cara-menjaga-ph-air-kolam-tetap-ideal/">artikel kami soal cara menjaga pH air kolam tetap ideal</a>.</p>'
    . '<h2>Yang Bisa Anda Lakukan Sekarang</h2>'
    . '<p>Kalau kolam Anda sering berbau menyengat meski rutin diberi klorin, tim <a href="/layanan/jasa-perawatan-kolam-renang/">jasa perawatan kolam renang</a> kami bisa bantu cek kondisi air dan menyesuaikan jadwal perawatannya.</p>';

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
