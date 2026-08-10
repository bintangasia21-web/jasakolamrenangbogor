<?php
/**
 * Skrip sekali-jalan: menerbitkan artikel "Cara Mengatasi Air Kolam
 * Berwarna Hijau" (brief 2026-08-10, batch 8 artikel). Pola sama persis
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

$urlPath = '/artikel/cara-mengatasi-air-kolam-berwarna-hijau/';
$title = 'Cara Mengatasi Air Kolam Berwarna Hijau';
$metaTitle = 'Cara Mengatasi Air Kolam Renang Berwarna Hijau | Jasa Kolam Renang Bogor';
$metaDescription = 'Air kolam berubah hijau tanda pertumbuhan alga tidak terkendali. Kenali penyebabnya, langkah penanganannya, dan cara mencegahnya terulang.';
$intro = 'Air kolam yang tiba-tiba berubah warna hijau adalah salah satu masalah yang paling sering membuat pemilik kolam panik — dan wajar, karena perubahannya bisa terjadi cukup cepat, kadang hanya dalam hitungan hari. Kabar baiknya, ini masalah yang bisa diatasi, asal ditangani dengan cara yang tepat.';

// Belum ada foto khusus untuk artikel ini -- pakai foto proyek
// "Perawatan Kolam Rutin" dari galeri Portofolio yang sudah ada (tema
// perawatan/kualitas air paling relevan). Admin bisa menggantinya
// lewat tab Artikel.
$coverImage = 'photo.php?id=16';

$content = '<h2>Penyebab Utama Air Kolam Menghijau</h2>'
    . '<p>Warna hijau pada air kolam hampir selalu disebabkan oleh pertumbuhan alga yang tidak terkendali. Ada beberapa pemicu umum: kadar klorin yang terlalu rendah sehingga tidak lagi efektif membunuh alga, paparan sinar matahari langsung yang intens (mempercepat fotosintesis alga), dan nutrisi organik berlebih dari daun, debu, atau keringat perenang yang jadi "makanan" bagi alga untuk berkembang biak. pH yang tidak seimbang juga sering jadi faktor tersembunyi — klorin yang sebenarnya cukup dosisnya tetap kurang efektif kalau pH air terlalu tinggi.</p>'
    . '<h2>Langkah Shock Treatment</h2>'
    . '<p>Untuk mengatasi air yang sudah hijau, langkah standar yang biasa dilakukan adalah shock treatment — menambahkan dosis klorin dalam jumlah besar sekaligus (jauh di atas dosis perawatan rutin) untuk membunuh alga dan bakteri secara cepat. Setelah shock treatment, filter perlu dijalankan terus-menerus selama beberapa jam hingga air kembali jernih, dan biasanya diikuti dengan penyikatan permukaan kolam untuk melepaskan alga yang menempel di dinding dan dasar kolam. Air baru boleh dipakai kembali untuk berenang setelah kadar klorin turun ke rentang aman normal, bukan langsung setelah air terlihat jernih secara visual.</p>'
    . '<h2>Kapan Bisa Ditangani Sendiri, Kapan Perlu Profesional</h2>'
    . '<p>Kalau warna hijau masih ringan (air agak kehijauan tapi dasar kolam masih terlihat), penyesuaian dosis klorin dan penyikatan rutin oleh pemilik kolam biasanya cukup. Tapi kalau air sudah hijau pekat sampai dasar kolam tidak terlihat sama sekali, sebaiknya diserahkan ke profesional — dosis shock treatment yang tidak tepat justru berisiko merusak keseimbangan kimia air atau membahayakan kalau ada yang berenang sebelum kadar klorin kembali aman. Alga yang sudah menempel lama di dinding kolam juga kadang butuh perlakuan algaecide khusus di luar klorin biasa, yang penentuan jenis dan dosisnya sebaiknya dilakukan oleh yang berpengalaman.</p>'
    . '<h2>Mencegah Air Hijau Terulang</h2>'
    . '<p>Setelah air kembali jernih, kuncinya menjaga kadar klorin tetap stabil di rentang perawatan rutin dan tidak menunggu terlalu lama antar jadwal pengecekan, terutama saat cuaca panas terik atau musim hujan yang sama-sama mempercepat pertumbuhan alga. Menjaga pH tetap di rentang ideal dan rutin membersihkan filter juga membantu mencegah kondisi yang memicu alga tumbuh kembali dengan cepat.</p>'
    . '<p>Kalau kolam Anda sudah terlanjur hijau atau sering berulang meski sudah dirawat, kami bisa bantu tangani langsung sekaligus evaluasi penyebab akarnya supaya masalah yang sama tidak terus berulang.</p>';

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
