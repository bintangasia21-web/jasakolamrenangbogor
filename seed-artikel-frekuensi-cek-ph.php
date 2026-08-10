<?php
/**
 * Skrip sekali-jalan: menerbitkan artikel "Berapa Kali Idealnya Cek pH
 * Air Kolam per Minggu" (brief 2026-08-10, batch 8 artikel). Pola sama
 * persis dengan seed-artikel-biaya-perawatan.php.
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

$urlPath = '/artikel/berapa-kali-cek-ph-air-kolam-per-minggu/';
$title = 'Berapa Kali Idealnya Cek pH Air Kolam per Minggu';
$metaTitle = 'Berapa Kali Idealnya Cek pH Air Kolam per Minggu? | Jasa Kolam Renang Bogor';
$metaDescription = 'Frekuensi ideal cek pH air kolam renang tergantung intensitas pemakaian dan musim. Simak panduan praktisnya untuk kolam rumah Anda.';
$intro = 'Setelah tahu pentingnya menjaga pH air kolam tetap di rentang ideal, pertanyaan berikutnya yang sering kami dapat adalah: seberapa sering sebenarnya pH ini perlu dicek? Jawabannya tidak selalu sama untuk setiap kolam, tapi ada patokan umum yang bisa jadi acuan.';

// Belum ada foto khusus untuk artikel ini -- pakai foto proyek "Kolam
// Renang Keluarga" dari galeri Portofolio yang sudah ada (kolam
// pemakaian harian/keluarga relevan dengan tema frekuensi cek rutin).
// Admin bisa menggantinya lewat tab Artikel.
$coverImage = 'photo.php?id=14';

$content = '<h2>Rekomendasi Umum untuk Kolam Aktif</h2>'
    . '<p>Untuk kolam yang dipakai secara rutin, kami biasanya menyarankan pengecekan pH 2 hingga 3 kali seminggu. Frekuensi ini cukup untuk menangkap perubahan sebelum airnya benar-benar keluar dari rentang ideal, tanpa perlu pengecekan harian yang sebenarnya berlebihan untuk kebanyakan kolam rumah tinggal. Pengecekan yang konsisten di jam yang sama juga membantu melihat pola perubahan pH dari waktu ke waktu, bukan cuma angka sesaat.</p>'
    . '<h2>Faktor yang Mengubah Frekuensi Ideal</h2>'
    . '<p>Angka 2-3 kali seminggu itu bukan aturan kaku — ada beberapa faktor yang bisa membuat kolam Anda butuh pengecekan lebih sering atau justru lebih jarang. Musim hujan, misalnya, cenderung butuh pengecekan lebih sering karena air hujan yang masuk bisa mengubah keseimbangan kimia dengan cepat. Jumlah pemakai juga berpengaruh — kolam yang sering dipakai banyak orang sekaligus (misalnya saat acara keluarga atau kolam komunitas) mengalami perubahan pH lebih cepat dibanding kolam yang jarang dipakai. Ukuran kolam turut menentukan: kolam kecil dengan volume air terbatas biasanya lebih cepat berubah komposisi kimianya dibanding kolam besar yang volumenya lebih "menyerap" perubahan.</p>'
    . '<h2>Mencatat Hasil Pengecekan dari Waktu ke Waktu</h2>'
    . '<p>Sekadar mengecek pH tanpa mencatat hasilnya sering membuat pola perubahan sulit dikenali. Mencatat angka setiap kali pengecekan — meski sederhana di buku catatan atau catatan ponsel — membantu melihat apakah pH kolam Anda cenderung stabil atau sering bergeser drastis, sehingga penyesuaian dosis kimia bisa lebih terarah alih-alih menebak-nebak setiap kali.</p>'
    . '<h2>Kolam yang Jarang Dipakai Tetap Perlu Dicek</h2>'
    . '<p>Untuk kolam vila atau properti musiman yang hanya dipakai akhir pekan, pengecekan tetap perlu dilakukan meski frekuensinya bisa disesuaikan — bukan berarti dilewati sama sekali. Air yang didiamkan lama tanpa pengecekan justru berisiko keluar jauh dari rentang ideal tanpa disadari, dan baru ketahuan saat kolam mau dipakai.</p>'
    . '<h2>Kalau Tidak Sempat Cek Sendiri Secara Rutin</h2>'
    . '<p>Realitanya, tidak semua pemilik kolam punya waktu atau kebiasaan untuk rutin melakukan pengecekan sendiri di sela kesibukan sehari-hari — dan itu wajar. Untuk situasi seperti ini, paket perawatan terjadwal dari tim profesional jadi solusi paling praktis: pengecekan pH dan kimia lain tetap berjalan sesuai frekuensi yang dibutuhkan kolam Anda, tanpa Anda perlu mengingat-ingat jadwalnya sendiri atau membeli dan menyimpan test kit di rumah.</p>'
    . '<p>Kalau Anda tidak yakin seberapa sering kolam Anda perlu dicek berdasarkan pola pemakaiannya, kami bisa bantu tentukan jadwal yang paling sesuai — sekaligus menanganinya langsung lewat paket perawatan rutin supaya Anda tidak perlu memikirkannya sendiri.</p>';

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
