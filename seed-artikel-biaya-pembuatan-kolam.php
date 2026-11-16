<?php
/**
 * Skrip sekali-jalan: menerbitkan artikel "Berapa Biaya Membuat Kolam
 * Renang Baru di Bogor? Ini yang Menentukan" -- pasangan dari
 * seed-artikel-biaya-perawatan.php, tapi untuk sisi Pembuatan (bukan
 * Perawatan). Ditambahkan karena dari 10 artikel yang sudah terbit,
 * hampir semua mendukung layanan Perawatan -- Pembuatan, Renovasi, dan
 * Instalasi Filter & Pompa belum punya artikel pendukung sama sekali.
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

$urlPath = '/artikel/berapa-biaya-membuat-kolam-renang-baru-di-bogor/';
$title = 'Berapa Biaya Membuat Kolam Renang Baru di Bogor? Ini yang Menentukan';
$metaTitle = 'Berapa Biaya Membuat Kolam Renang Baru di Bogor? Ini yang Menentukan | Jasa Kolam Renang Bogor';
$metaDescription = 'Biaya membuat kolam renang baru di Bogor dipengaruhi ukuran, desain, dan kondisi lahan. Simak faktor penentu harga sebelum mulai proyek.';
$intro = 'Setelah memutuskan ingin punya kolam renang sendiri, pertanyaan berikutnya yang paling sering muncul adalah soal biaya pembuatannya. Sama seperti perawatan, tidak ada satu angka pasti yang berlaku untuk semua kolam -- beberapa faktor berikut yang sebenarnya menentukan harga.';

// Belum ada foto yang diambil khusus untuk artikel ini -- pakai foto
// proyek "Kolam Renang Villa Modern di Sentul" dari galeri Portofolio
// yang sudah ada (tema pembuatan/konstruksi, paling relevan) sebagai
// sampul sementara. Admin bisa menggantinya kapan saja lewat tab
// Artikel di panel admin.
$coverImage = 'photo.php?id=11';

$content = '<h2>Faktor yang Menentukan Biaya Pembuatan Kolam Renang Baru</h2>'
    . '<ul>'
    . '<li><strong>Ukuran dan kedalaman kolam:</strong> Semakin besar volume kolam, semakin banyak material konstruksi, waktu pengerjaan, dan kapasitas sistem filtrasi yang dibutuhkan. Kolam minimalis rumah tinggal jelas berbeda kebutuhannya dibanding kolam villa atau resort berukuran besar.</li>'
    . '<li><strong>Desain dan bentuk kolam:</strong> Bentuk persegi standar umumnya lebih sederhana dikerjakan dibanding desain custom seperti infinity edge atau kolam dengan lekukan non-simetris, yang butuh perhitungan struktur dan pengerjaan lebih detail.</li>'
    . '<li><strong>Jenis material dan finishing:</strong> Pilihan lapisan kolam (keramik, mosaic, atau liner) dan finishing area sekitar kolam ikut memengaruhi total biaya, karena setiap material punya rentang harga dan kebutuhan pengerjaan yang berbeda.</li>'
    . '<li><strong>Kondisi lahan dan akses:</strong> Lahan yang sudah rata dan mudah diakses alat/material tentu lebih efisien dikerjakan dibanding lahan berkontur atau di kawasan yang aksesnya sempit, seperti sering kami temui di beberapa titik Sentul dan Puncak.</li>'
    . '<li><strong>Sistem filtrasi dan pompa yang dipilih:</strong> Kapasitas dan jenis sistem sirkulasi air yang dipasang menyesuaikan ukuran kolam -- ini bagian dari paket pembuatan, bukan biaya tambahan terpisah, tapi tetap memengaruhi total anggaran sesuai spesifikasi yang dipilih.</li>'
    . '<li><strong>Fitur tambahan:</strong> Elemen seperti lampu bawah air, water feature, atau sistem pemanas menambah nilai dan kenyamanan kolam, tapi juga menambah komponen yang perlu dihitung di luar struktur dasar kolam.</li>'
    . '</ul>'
    . '<h2>Kenapa Kami Tidak Mencantumkan Harga Pasti di Sini</h2>'
    . '<p>Menyebut satu angka tanpa tahu kondisi lahan dan keinginan desain Anda justru berisiko menyesatkan. Karena itu, tim <a href="/layanan/pembuatan-kolam-renang-baru/">pembuatan kolam renang baru</a> kami selalu memberi estimasi biaya setelah survei singkat -- langsung ke lokasi atau lewat foto/video via WhatsApp -- tanpa biaya survei awal. Anda bisa melihat salah satu contoh hasil pengerjaan kami di proyek <a href="/portofolio/kolam-renang-villa-modern-di-sentul/">Kolam Renang Villa Modern di Sentul</a>.</p>'
    . '<h2>Yang Bisa Anda Lakukan Sekarang</h2>'
    . '<p>Hubungi kami dan ceritakan ukuran lahan serta gambaran desain yang Anda inginkan, kami bantu hitungkan tanpa komitmen apa pun.</p>';

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
