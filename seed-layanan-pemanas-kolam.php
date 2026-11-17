<?php
/**
 * Skrip sekali-jalan: menerbitkan halaman layanan baru "Instalasi
 * Pemanas Kolam Renang" (type='service'). Mengisi gap layanan yang
 * belum punya halaman sendiri, sekaligus jadi target link dari artikel
 * "Apakah Kolam Renang Perlu Pemanas (Heater)?" yang sudah live.
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

$urlPath = '/layanan/instalasi-pemanas-kolam-renang/';
$title = 'Instalasi Pemanas Kolam Renang';
$metaTitle = 'Instalasi Pemanas (Heater) Kolam Renang di Bogor | Jasa Kolam Renang Bogor';
$metaDescription = 'Pemasangan sistem pemanas (heater) kolam renang agar air tetap nyaman dipakai pagi, malam, atau musim hujan di Bogor. Survei & konsultasi gratis.';
$intro = 'Udara pagi dan malam di beberapa kawasan Bogor, terutama area perbukitan seperti Sentul dan Puncak, bisa membuat air kolam terasa dingin meski siang hari cukup hangat. Pemanas (heater) kolam renang membantu menjaga suhu air tetap nyaman kapan pun kolam ingin dipakai.';
$coverImage = 'photo.php?id=11';

$content = '<h2>Instalasi Pemanas Kolam Renang di Bogor</h2>'
    . '<p style="color:var(--gray-600)">Udara pagi dan malam di beberapa kawasan Bogor, terutama area perbukitan seperti Sentul dan Puncak, bisa membuat air kolam terasa dingin meski siang hari cukup hangat. Pemanas (heater) kolam renang membantu menjaga suhu air tetap nyaman kapan pun kolam ingin dipakai, tanpa harus menunggu matahari cukup terik.</p>'
    . '<h2 style="margin-top:32px">Kapan Pemanas Kolam Dibutuhkan</h2>'
    . '<p style="color:var(--gray-600)">Heater paling terasa manfaatnya untuk kolam yang sering dipakai pagi atau malam, kolam di kawasan dataran tinggi yang udaranya lebih sejuk, maupun vila dan resort yang ingin tamu bisa berenang nyaman kapan saja tanpa terbatas jam-jam tertentu.</p>'
    . '<h2 style="margin-top:32px">Tahapan Pemasangan</h2>'
    . '<ol style="color:var(--gray-600);padding-left:20px;margin:0"><li>Konsultasi kebutuhan &amp; survei lokasi</li><li>Pemilihan jenis dan kapasitas pemanas sesuai volume kolam</li><li>Instalasi &amp; integrasi ke sistem sirkulasi yang sudah ada</li><li>Pengujian &amp; serah terima</li></ol>'
    . '<h2 style="margin-top:32px">Cocok untuk Kolam Baru Maupun Kolam Lama</h2>'
    . '<p style="color:var(--gray-600)">Pemanas bisa dipasang sejak tahap <a href="/layanan/pembuatan-kolam-renang-baru/" style="color:var(--blue-600);font-weight:600">pembuatan kolam renang baru</a> agar terintegrasi penuh dengan sistem kolam, atau ditambahkan kemudian ke kolam yang sudah berdiri tanpa membongkar struktur utamanya.</p>'
    . '<h2 style="margin-top:32px">Konsultasi Instalasi Pemanas Kolam</h2>'
    . '<p style="color:var(--gray-600)">Ceritakan pola pemakaian kolam Anda, kami bantu tentukan jenis dan kapasitas pemanas yang sesuai — tanpa komitmen apa pun.</p>'
    . '<p><a href="https://wa.me/6282216623388" style="color:var(--blue-600);font-weight:600">Chat via WhatsApp untuk Konsultasi &rarr;</a></p>';

$faq = [
    ['q' => 'Apakah semua kolam bisa dipasang pemanas?', 'a' => 'Pada umumnya bisa, baik kolam baru maupun kolam yang sudah ada -- kami tentukan jenis dan kapasitas yang sesuai setelah survei langsung.'],
    ['q' => 'Apakah pemanas kolam boros listrik?', 'a' => 'Konsumsi energi tergantung jenis dan kapasitas pemanas yang dipilih. Kami jelaskan detail opsinya saat survei supaya sesuai kebutuhan dan anggaran Anda.'],
    ['q' => 'Apakah survei untuk instalasi pemanas dikenakan biaya?', 'a' => 'Tidak, survei dan konsultasi awal kami berikan gratis tanpa biaya.'],
];

try {
    $pdo = get_db();
    $stmt = $pdo->prepare(
        "INSERT INTO pages (type, url_path, title, meta_title, meta_description, h1, intro, content, cover_image, faq_json, status)
         VALUES ('service', :url_path, :title, :meta_title, :meta_description, :h1, :intro, :content, :cover_image, :faq_json, 'published')
         ON DUPLICATE KEY UPDATE title=VALUES(title), meta_title=VALUES(meta_title),
           meta_description=VALUES(meta_description), h1=VALUES(h1), intro=VALUES(intro),
           content=VALUES(content), cover_image=VALUES(cover_image), faq_json=VALUES(faq_json), status='published'"
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
        ':faq_json' => json_encode($faq, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
    ]);

    respond(true, 'Halaman layanan berhasil diterbitkan.', ['url_path' => $urlPath]);
} catch (Exception $e) {
    respond(false, 'Gagal menerbitkan halaman: ' . $e->getMessage());
}
