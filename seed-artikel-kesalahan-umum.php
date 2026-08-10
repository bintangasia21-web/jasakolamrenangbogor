<?php
/**
 * Skrip sekali-jalan: menerbitkan artikel "Kesalahan Umum Pemilik Kolam
 * Renang Rumahan" (brief 2026-08-10, batch 8 artikel). Pola sama persis
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

$urlPath = '/artikel/kesalahan-umum-pemilik-kolam-renang-rumahan/';
$title = 'Kesalahan Umum Pemilik Kolam Renang Rumahan';
$metaTitle = 'Kesalahan Umum Pemilik Kolam Renang Rumahan | Jasa Kolam Renang Bogor';
$metaDescription = 'Beberapa kebiasaan pemilik kolam renang rumahan justru mempercepat kerusakan dan menambah biaya perawatan. Kenali kesalahan yang paling sering terjadi.';
$intro = 'Setelah bertahun-tahun menangani berbagai kolam renang rumahan di Bogor, kami melihat pola kesalahan yang berulang dari satu pemilik ke pemilik lain — bukan karena kurang peduli, tapi karena memang tidak selalu tahu dampaknya sampai masalahnya sudah terlanjur besar. Beberapa di antaranya sudah kami bahas satu per satu di artikel lain, tapi ada baiknya dirangkum di sini supaya lebih mudah dijadikan daftar periksa untuk kolam Anda sendiri.';

// Belum ada foto khusus untuk artikel ini -- pakai foto proyek
// perawatan kolam dari galeri Portofolio yang sudah ada (tema umum
// perawatan kolam rumahan paling relevan). Admin bisa menggantinya
// lewat tab Artikel.
$coverImage = 'photo.php?id=12';

$content = '<h2>Menunda Perawatan Sampai Air Terlihat Kotor</h2>'
    . '<p>Ini kesalahan paling umum yang sudah kami singgung di artikel sebelumnya: menunggu air terlihat keruh atau berubah warna baru bertindak. Padahal, air yang terlihat jernih secara visual belum tentu seimbang secara kimia — kerusakan atau ketidakseimbangan sering sudah terjadi jauh sebelum terlihat kasat mata. Menunggu sampai masalah terlihat biasanya berarti penanganannya juga jadi lebih rumit dan mahal dibanding kalau ditangani sejak tanda-tanda awal.</p>'
    . '<h2>Overdosis Bahan Kimia Tanpa Tes Dulu</h2>'
    . '<p>Sebagian pemilik kolam menambahkan klorin atau bahan kimia lain berdasarkan perkiraan atau kebiasaan, tanpa mengecek kondisi air terlebih dahulu. Overdosis klorin bisa menyebabkan iritasi kulit dan mata, merusak lapisan permukaan kolam, bahkan memucatkan warna liner atau ubin dalam jangka panjang. Selalu tes dulu sebelum menambahkan bahan kimia, jangan menebak-nebak berdasarkan pengalaman menambahkan dosis di kolam lain yang ukuran atau kondisinya berbeda.</p>'
    . '<h2>Lupa Backwash Filter Secara Rutin</h2>'
    . '<p>Seperti dibahas di artikel kami soal backwash, banyak pemilik kolam tidak sadar filter mereka sudah lama tidak dibersihkan sampai performa sirkulasi menurun drastis. Filter yang tersumbat bukan cuma bikin air kurang jernih, tapi juga membebani kerja pompa lebih berat dari seharusnya, yang dalam jangka panjang bisa mempercepat kerusakan pompa itu sendiri.</p>'
    . '<h2>Mengabaikan Kebocoran Kecil</h2>'
    . '<p>Rembesan air yang tidak signifikan sering dianggap wajar atau "nanti juga berhenti sendiri". Padahal kebocoran kecil yang dibiarkan bisa membesar seiring waktu, merusak struktur di sekitarnya, dan pada akhirnya butuh perbaikan yang jauh lebih mahal dibanding kalau ditangani sejak awal terdeteksi. Tanda sederhana seperti permukaan air yang turun lebih cepat dari biasanya (di luar penguapan wajar) sering jadi indikasi awal yang terlewat.</p>'
    . '<h2>Mencoba Perbaikan Struktural Sendiri</h2>'
    . '<p>Menambal keramik pecah atau memperbaiki retakan struktur kolam sendiri tanpa keahlian yang tepat sering berakhir dengan hasil yang tidak tahan lama, bahkan bisa memperparah kerusakan aslinya. Perbaikan struktural kolam butuh pemahaman soal waterproofing dan material yang sesuai — kesalahan penanganan justru bisa membuat kebocoran atau kerusakan menyebar lebih luas, dan akhirnya perbaikan ulang oleh profesional jadi lebih rumit karena harus membenahi hasil perbaikan sebelumnya juga.</p>'
    . '<p>Kalau Anda mengenali salah satu kebiasaan di atas pada kolam Anda sendiri, belum terlambat untuk memperbaikinya. Hubungi kami untuk evaluasi kondisi kolam Anda secara menyeluruh, supaya kesalahan-kesalahan kecil tidak berkembang jadi masalah besar yang lebih mahal untuk diperbaiki.</p>';

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
