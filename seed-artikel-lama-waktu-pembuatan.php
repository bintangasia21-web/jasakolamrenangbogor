<?php
/**
 * Skrip sekali-jalan: menerbitkan artikel "Berapa Lama Waktu
 * Pembuatan Kolam Renang Baru?" -- mendukung layanan Pembuatan,
 * pasangan dari seed-artikel-biaya-pembuatan-kolam.php yang sudah
 * live (saling ditautkan).
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

$urlPath = '/artikel/berapa-lama-waktu-pembuatan-kolam-renang-baru/';
$title = 'Berapa Lama Waktu Pembuatan Kolam Renang Baru?';
$metaTitle = 'Berapa Lama Waktu Pembuatan Kolam Renang Baru? | Jasa Kolam Renang Bogor';
$metaDescription = 'Waktu pembuatan kolam renang baru dipengaruhi ukuran, desain, dan kondisi lahan. Simak tahapan dan estimasi waktu pengerjaannya di sini.';
$intro = 'Selain biaya, pertanyaan yang sama seringnya ditanyakan calon pelanggan adalah soal berapa lama proses pembuatan kolam renang berlangsung. Jawabannya tergantung beberapa tahapan dan faktor berikut.';

// Belum ada foto yang diambil khusus untuk artikel ini -- pakai foto
// proyek "Kolam Renang Villa Modern di Sentul" dari galeri Portofolio
// yang sudah ada (tema pembuatan/konstruksi, paling relevan) sebagai
// sampul sementara. Admin bisa menggantinya kapan saja lewat tab
// Artikel di panel admin.
$coverImage = 'photo.php?id=11';

$content = '<h2>Tahapan Pembuatan Kolam Renang</h2>'
    . '<ol>'
    . '<li><strong>Survei lokasi dan konsultasi desain:</strong> Menentukan ukuran, bentuk, dan spesifikasi kolam sesuai kondisi lahan.</li>'
    . '<li><strong>Penggalian dan pembangunan struktur:</strong> Bagian yang paling memakan waktu, tergantung ukuran dan kondisi tanah.</li>'
    . '<li><strong>Waterproofing dan pemasangan finishing:</strong> Pelapisan anti rembes, diikuti pemasangan keramik/mosaic/liner sesuai pilihan.</li>'
    . '<li><strong>Instalasi sistem filtrasi dan sirkulasi:</strong> Pemasangan pompa, filter, dan jalur pipa.</li>'
    . '<li><strong>Pengisian air dan serah terima:</strong> Tahap akhir sebelum kolam siap dipakai.</li>'
    . '</ol>'
    . '<h2>Faktor yang Memperlambat Pengerjaan</h2>'
    . '<p>Cuaca hujan khas Bogor, terutama di kawasan seperti Puncak dan Cijeruk, bisa memperlambat tahap penggalian dan struktur. Kondisi lahan yang berkontur atau sulit diakses alat berat juga menambah waktu dibanding lahan yang sudah rata. Desain custom atau ukuran besar (seperti kolam villa/resort) secara alami butuh waktu pengerjaan lebih panjang dibanding kolam minimalis rumah tinggal.</p>'
    . '<h2>Estimasi Waktu yang Wajar</h2>'
    . '<p>Untuk kolam berukuran standar rumah tinggal, proses pembuatan umumnya berlangsung sekitar 3-6 minggu dari penggalian hingga siap dipakai -- di luar waktu survei dan persetujuan desain di awal. Tim <a href="/layanan/pembuatan-kolam-renang-baru/">pembuatan kolam renang baru</a> kami memberi estimasi waktu yang lebih pasti setelah survei langsung ke lokasi. Kalau Anda juga ingin tahu gambaran biayanya, baca juga <a href="/artikel/berapa-biaya-membuat-kolam-renang-baru-di-bogor/">artikel kami soal faktor biaya pembuatan kolam renang baru</a>.</p>'
    . '<h2>Yang Bisa Anda Lakukan Sekarang</h2>'
    . '<p>Hubungi kami untuk survei lokasi gratis, kami bantu hitungkan estimasi waktu pengerjaan yang sesuai dengan kondisi lahan dan desain yang Anda inginkan.</p>';

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
