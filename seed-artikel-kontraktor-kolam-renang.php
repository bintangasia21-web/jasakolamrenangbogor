<?php
/**
 * Skrip sekali-jalan: menerbitkan artikel "Kontraktor Kolam Renang di
 * Bogor: Apa yang Perlu Diperhatikan Sebelum Memilih?" -- menyasar
 * istilah "kontraktor kolam renang" yang belum muncul sama sekali di
 * halaman /layanan/pembuatan-kolam-renang-baru/ (dikonfirmasi 0 match
 * lewat fetch live sebelum membuat file ini). Artikel ini link ke
 * halaman tersebut sebagai layanan utama.
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

$urlPath = '/artikel/kontraktor-kolam-renang-bogor-apa-yang-perlu-diperhatikan/';
$title = 'Kontraktor Kolam Renang di Bogor: Apa yang Perlu Diperhatikan Sebelum Memilih?';
$metaTitle = 'Kontraktor Kolam Renang di Bogor: Apa yang Perlu Diperhatikan? | Jasa Kolam Renang Bogor';
$metaDescription = 'Memilih kontraktor kolam renang yang tepat di Bogor menentukan hasil akhir dan daya tahan kolam Anda. Simak hal yang perlu diperhatikan sebelum memutuskan.';
$intro = 'Membangun kolam renang bukan pekerjaan tukang bangunan biasa -- dibutuhkan kontraktor kolam renang yang memahami struktur, waterproofing, dan sistem sirkulasi air secara terintegrasi. Berikut hal-hal yang perlu Anda perhatikan sebelum memilih kontraktor kolam renang untuk proyek Anda.';

// Belum ada foto yang diambil khusus untuk artikel ini -- pakai foto
// proyek "Kolam Renang Villa Modern di Sentul" dari galeri Portofolio
// yang sudah ada (tema pembuatan/konstruksi, paling relevan) sebagai
// sampul sementara. Admin bisa menggantinya kapan saja lewat tab
// Artikel di panel admin.
$coverImage = 'photo.php?id=11';

$content = '<h2>Apa Itu Kontraktor Kolam Renang</h2>'
    . '<p style="color:var(--gray-600)">Kontraktor kolam renang adalah pihak yang menangani seluruh proses pembuatan kolam dari awal sampai akhir -- mulai dari desain, penggalian, konstruksi struktur, pelapisan waterproofing, hingga instalasi sistem filtrasi dan sirkulasi air. Ini berbeda dari tukang bangunan umum yang biasanya hanya terbiasa mengerjakan struktur dasar tanpa pengalaman khusus menangani tekanan air dan kebutuhan teknis kolam renang.</p>'
    . '<h2 style="margin-top:32px">Kenapa Memilih Kontraktor Berpengalaman, Bukan Tukang Biasa</h2>'
    . '<p style="color:var(--gray-600)">Kolam renang punya kebutuhan teknis yang jauh berbeda dari bangunan biasa -- mulai dari kemiringan dasar kolam, ketebalan dan kualitas waterproofing, hingga perhitungan sistem sirkulasi yang sesuai volume air. Kontraktor kolam renang yang berpengalaman tahu cara menghindari masalah umum seperti kebocoran, retak struktur, atau sistem filtrasi yang tidak bekerja optimal -- masalah yang sering muncul kalau pengerjaannya ditangani tukang tanpa pengalaman spesifik di bidang ini.</p>'
    . '<h2 style="margin-top:32px">Hal yang Perlu Diperhatikan Saat Memilih Kontraktor Kolam Renang</h2>'
    . '<ul style="color:var(--gray-600)">'
    . '<li><strong>Portofolio proyek nyata:</strong> Minta untuk melihat proyek-proyek yang sudah pernah dikerjakan, bukan cuma foto hasil akhir tanpa konteks.</li>'
    . '<li><strong>Pengalaman dan lama berdiri:</strong> Kontraktor yang sudah lama menangani kolam renang biasanya lebih siap menghadapi kondisi lahan yang beragam.</li>'
    . '<li><strong>Garansi pekerjaan:</strong> Pastikan ada kejelasan garansi tertulis untuk pekerjaan struktur dan sistem yang dipasang.</li>'
    . '<li><strong>Survei lokasi sebelum penawaran harga:</strong> Kontraktor yang serius akan survei langsung sebelum memberi estimasi, bukan menyebut harga tanpa melihat kondisi lahan.</li>'
    . '<li><strong>Komunikasi yang jelas:</strong> Tahapan pengerjaan dan perkiraan waktu penyelesaian sebaiknya dijelaskan di awal, bukan baru diketahui di tengah proyek.</li>'
    . '</ul>'
    . '<h2 style="margin-top:32px">Jasa Kolam Renang Bogor sebagai Kontraktor Kolam Renang Anda</h2>'
    . '<p style="color:var(--gray-600)">Kami menangani <a href="/layanan/pembuatan-kolam-renang-baru/" style="color:var(--blue-600);font-weight:600">pembuatan kolam renang baru</a> sebagai kontraktor kolam renang di wilayah Bogor dan sekitarnya, mulai dari survei lokasi, desain, hingga serah terima bergaransi. Salah satu contoh proyek yang kami kerjakan bisa dilihat di <a href="/portofolio/kolam-renang-villa-modern-di-sentul/" style="color:var(--blue-600);font-weight:600">Kolam Renang Villa Modern di Sentul</a>.</p>'
    . '<h2 style="margin-top:32px">Yang Bisa Anda Lakukan Sekarang</h2>'
    . '<p style="color:var(--gray-600)">Ceritakan rencana kolam renang Anda, kami bantu survei dan rencanakan sebagai kontraktor kolam renang Anda -- tanpa komitmen apa pun.</p>';

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
