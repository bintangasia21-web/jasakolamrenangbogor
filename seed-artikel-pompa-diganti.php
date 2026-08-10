<?php
/**
 * Skrip sekali-jalan: menerbitkan artikel "Tanda-tanda Pompa Kolam
 * Renang Perlu Diganti" (brief 2026-08-10, batch 8 artikel). Pola sama
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

$urlPath = '/artikel/tanda-pompa-kolam-renang-perlu-diganti/';
$title = 'Tanda-tanda Pompa Kolam Renang Perlu Diganti';
$metaTitle = 'Tanda-tanda Pompa Kolam Renang Perlu Diganti | Jasa Kolam Renang Bogor';
$metaDescription = 'Pompa adalah jantung sistem sirkulasi kolam renang. Kenali tanda-tanda pompa mulai bermasalah sebelum berujung kerusakan lebih besar.';
$intro = 'Pompa adalah komponen yang bekerja hampir tanpa henti di sistem kolam renang, jadi wajar kalau lambat laun performanya menurun. Masalahnya, banyak pemilik kolam baru sadar ada yang salah setelah pompa benar-benar berhenti berfungsi — padahal biasanya ada tanda-tanda peringatan jauh sebelum itu terjadi.';

// Belum ada foto khusus untuk artikel ini -- pakai foto proyek
// "Perbaikan Kebocoran Kolam" dari galeri Portofolio yang sudah ada
// (tema perbaikan/masalah peralatan paling relevan). Admin bisa
// menggantinya lewat tab Artikel.
$coverImage = 'photo.php?id=15';

$content = '<h2>Suara Berisik yang Tidak Biasa</h2>'
    . '<p>Pompa yang sehat biasanya beroperasi dengan suara dengungan halus dan konsisten. Kalau Anda mulai mendengar suara berdecit, menggerinda, atau berisik yang tidak biasa, itu bisa jadi tanda bearing motor sudah aus atau ada komponen internal yang mulai bermasalah — sebaiknya jangan dibiarkan sampai pompa berhenti total. Suara yang muncul tiba-tiba dan semakin keras dari hari ke hari biasanya jadi sinyal paling jelas bahwa ada yang perlu diperiksa segera, bukan sekadar ditunggu sampai hilang sendiri.</p>'
    . '<h2>Aliran Air yang Melemah</h2>'
    . '<p>Kalau air yang keluar dari return jet terasa lebih lemah dibanding biasanya, padahal filter sudah dalam kondisi bersih dan baru di-backwash, kemungkinan besar penyebabnya ada di performa pompa itu sendiri, bukan di sistem filtrasi. Ini tanda impeller pompa mulai aus atau ada kebocoran udara yang mengganggu daya hisapnya. Aliran yang lemah juga membuat proses sirkulasi dan distribusi bahan kimia jadi kurang merata ke seluruh bagian kolam, yang lambat laun bisa memicu masalah air lain seperti pertumbuhan alga di sudut-sudut yang kurang tersirkulasi.</p>'
    . '<h2>Sering Mati Sendiri atau Overheat</h2>'
    . '<p>Pompa yang mati sendiri di tengah operasi, terutama setelah berjalan beberapa saat, sering menandakan motor mengalami overheat akibat kerja lebih berat dari seharusnya. Ini bisa disebabkan oleh berbagai hal mulai dari kapasitor yang lemah hingga motor yang memang sudah mendekati akhir usia pakainya. Kalau dibiarkan terus menyala-mati sendiri, beban berulang ini justru mempercepat kerusakan komponen lain di dalam motor.</p>'
    . '<h2>Usia Pakai dan Kebocoran di Sekitar Unit</h2>'
    . '<p>Secara umum, pompa kolam renang punya usia pakai sekitar 8-12 tahun tergantung merek, intensitas pemakaian, dan seberapa rutin perawatannya. Kalau pompa Anda sudah mendekati atau melewati usia tersebut, mulai pertimbangkan penggantian meski belum sepenuhnya rusak. Tanda lain yang perlu diwaspadai adalah rembesan air atau genangan di sekitar unit pompa — ini indikasi seal sudah aus dan berisiko merembet ke komponen elektrikal di dalamnya.</p>'
    . '<h2>Memperbaiki atau Mengganti, Mana yang Lebih Tepat?</h2>'
    . '<p>Tidak semua masalah pompa berarti harus ganti unit baru — kadang cukup mengganti komponen tertentu seperti seal, kapasitor, atau bearing kalau kerusakannya masih terbatas dan usia pompa belum terlalu tua. Tapi kalau pompa sudah sering bermasalah berulang kali, atau biaya perbaikan sudah mendekati harga unit baru, mengganti pompa biasanya jadi pilihan yang lebih hemat dalam jangka panjang dibanding terus-menerus memperbaiki komponen yang sama.</p>'
    . '<p>Kalau pompa kolam Anda menunjukkan salah satu tanda di atas, sebaiknya segera diperiksa sebelum berujung kerusakan total yang biaya penggantiannya jauh lebih besar. Hubungi kami untuk pengecekan kondisi pompa dan rekomendasi apakah masih bisa diperbaiki atau sudah waktunya diganti.</p>';

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
