<?php
/**
 * Skrip sekali-jalan: menerbitkan artikel "Lumut di Dinding Kolam
 * (Bukan Air Hijau): Beda dan Cara Membersihkannya" -- membedakan
 * masalah lumut permukaan dengan air hijau keruh menyeluruh yang sudah
 * dibahas di seed-artikel-air-hijau.php.
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

$urlPath = '/artikel/lumut-di-dinding-kolam-cara-membersihkan/';
$title = 'Lumut di Dinding Kolam (Bukan Air Hijau): Beda dan Cara Membersihkannya';
$metaTitle = 'Lumut di Dinding Kolam (Bukan Air Hijau): Beda dan Cara Membersihkannya | Jasa Kolam Renang Bogor';
$metaDescription = 'Lumut yang menempel di dinding kolam berbeda dengan air hijau keruh menyeluruh. Kenali bedanya dan cara membersihkan lumut di dinding kolam Anda.';
$intro = 'Tidak semua masalah warna di kolam renang sama. Lumut yang menempel di dinding atau lantai kolam adalah masalah yang berbeda dari air yang keruh hijau secara keseluruhan, dan cara menanganinya pun tidak sama.';

$coverImage = 'photo.php?id=16';

$content = '<h2>Beda Lumut di Dinding dengan Air Hijau</h2>'
    . '<p>Air hijau keruh berarti seluruh volume air sudah terkontaminasi alga, biasanya akibat kadar klorin yang terlalu rendah dalam waktu lama. Lumut di dinding atau lantai kolam berbeda -- ini masalah di permukaan tertentu saja, sementara air secara keseluruhan masih terlihat jernih. Keduanya butuh penanganan yang berbeda, jadi penting untuk tidak menyamakan keduanya sebelum memutuskan langkah perbaikan.</p>'
    . '<h2>Kenapa Lumut Muncul di Titik Tertentu</h2>'
    . '<ul>'
    . '<li><strong>Area dengan sirkulasi air lemah:</strong> Sudut-sudut kolam atau bagian yang jauh dari aliran sirkulasi utama lebih rentan jadi tempat lumut menempel dan berkembang.</li>'
    . '<li><strong>Jarang disikat secara manual:</strong> Klorin saja kadang tidak cukup menjangkau lumut yang sudah menempel kuat di permukaan kasar seperti keramik berpori atau sambungan nat.</li>'
    . '</ul>'
    . '<h2>Cara Membersihkannya</h2>'
    . '<p>Sikat kolam khusus (pool brush) dipakai untuk melepas lumut yang sudah menempel di dinding/lantai secara manual, lalu diikuti dengan shock klorin lokal di area tersebut untuk membasmi sisa spora yang masih menempel. Setelah dibersihkan, memperbaiki sirkulasi di titik-titik mati (misalnya dengan menyesuaikan arah nozzle inlet) membantu mencegah lumut tumbuh lagi di tempat yang sama. Kalau masalahnya justru air hijau secara keseluruhan, bukan cuma di dinding, baca <a href="/artikel/cara-mengatasi-air-kolam-berwarna-hijau/">artikel kami soal cara mengatasi air kolam berwarna hijau</a>.</p>'
    . '<h2>Yang Bisa Anda Lakukan Sekarang</h2>'
    . '<p>Kalau lumut di kolam Anda terus muncul berulang di area yang sama, tim <a href="/layanan/jasa-perawatan-kolam-renang/">jasa perawatan kolam renang</a> kami bisa bantu cek sirkulasi dan membersihkannya secara tuntas.</p>';

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
