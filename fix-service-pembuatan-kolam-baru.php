<?php
/**
 * Skrip sekali-jalan (Phase 3, 2026-08-11): implementasi perbaikan SEO &
 * kedalaman konten untuk SATU baris "pages" saja --
 * url_path='/layanan/pembuatan-kolam-renang-baru/' -- berdasarkan
 * SERVICE-PEMBUATAN-KOLAM-RENANG-AUDIT.md. Hanya menyentuh kolom
 * meta_description, content, dan faq_json milik baris ini. title/h1/
 * intro TIDAK diubah (audit menilainya sudah baik). Tidak menyentuh
 * baris/tabel lain, tidak menyentuh inc/templates/service.php atau
 * render_cta_band()/render_faq_block() (shared, di luar scope).
 *
 * Heading H4 lama ("Tahapan Pengerjaan" di dalam .info-box) diganti H2
 * biasa di dalam konten -- h1..h4 berbagi style dasar yang sama persis
 * di style.css (line 56: "h1, h2, h3, h4 { ... }"), jadi tidak perlu
 * perubahan CSS apa pun untuk heading baru ini tampil konsisten.
 *
 * Gambar yang disisipkan (photo.php?id=11, "Kolam Renang Villa Modern
 * di Sentul") adalah foto portofolio ASLI yang sudah live publik --
 * dimensi width/height diverifikasi dari getimagesize() foto tsb
 * (649x472), bukan ditebak.
 *
 * Dijalankan lewat browser (Basic Auth). Aman dijalankan berkali-kali
 * (UPDATE berdasarkan url_path, idempoten).
 */
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/inc/db.php';

function respond($success, $message, $extra = []) {
    echo json_encode(array_merge(['success' => $success, 'message' => $message], $extra), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}

$urlPath = '/layanan/pembuatan-kolam-renang-baru/';

$metaDescription = 'Jasa pembuatan kolam renang di Bogor — desain dan konstruksi dari nol untuk rumah, villa, dan resort. 10+ tahun pengalaman, 350+ proyek selesai. Survei gratis.';

$content = '<h2>Pembuatan Kolam Renang di Bogor</h2>'
    . '<p style="color:var(--gray-600)">Pembuatan kolam renang baru mencakup seluruh tahapan mulai dari konsultasi desain, survei kondisi tanah, penggalian, konstruksi struktur, pemasangan lapisan waterproofing dan keramik, hingga instalasi sistem sirkulasi air. Kami menyesuaikan desain dengan kondisi lahan — termasuk lahan berkontur di kawasan perbukitan seperti Sentul, maupun lahan terbatas di kawasan padat seperti Kota Bogor.</p>'
    . '<p style="color:var(--gray-600)">Layanan ini cocok untuk rumah tinggal yang ingin memiliki kolam pribadi, villa yang membutuhkan kolam sebagai fasilitas utama, maupun resort yang memerlukan kolam berskala lebih besar untuk tamu. Setiap proyek diawali survei lokasi gratis untuk memastikan estimasi biaya dan waktu pengerjaan akurat sebelum pekerjaan dimulai.</p>'

    . '<h2 style="margin-top:32px">Apa yang Kami Kerjakan</h2>'
    . '<p style="color:var(--gray-600)">Pekerjaan pembuatan kolam renang baru kami mencakup konstruksi struktur kolam, pelapisan waterproofing, hingga pemasangan sistem yang menunjang kualitas air jangka panjang. Dua bagian pekerjaan ini juga kami tangani sebagai layanan tersendiri bagi pelanggan yang sudah punya kolam dan hanya membutuhkan perbaikan atau upgrade: <a href="/layanan/waterproofing-kolam-renang/" style="color:var(--blue-600);font-weight:600">waterproofing kolam renang</a> untuk mencegah rembesan, dan <a href="/layanan/instalasi-filter-pompa/" style="color:var(--blue-600);font-weight:600">instalasi sistem filtrasi &amp; pompa</a> untuk menjaga sirkulasi air tetap optimal.</p>'

    . '<h2 style="margin-top:32px">Tahapan Pekerjaan</h2>'
    . '<ol style="color:var(--gray-600);padding-left:20px;margin:0"><li>Konsultasi kebutuhan &amp; survei lokasi</li><li>Desain &amp; penawaran rinci biaya</li><li>Penggalian &amp; pembangunan struktur</li><li>Waterproofing &amp; pemasangan finishing</li><li>Instalasi sistem filtrasi &amp; sirkulasi</li><li>Pengisian air &amp; serah terima bergaransi</li></ol>'
    . '<p style="color:var(--gray-600);margin-top:16px">Survei lokasi di tahap awal kami berikan tanpa biaya, sehingga Anda mendapat gambaran biaya dan waktu pengerjaan yang akurat sebelum memutuskan untuk melanjutkan.</p>'

    . '<h2 style="margin-top:32px">Pengalaman dan Proyek</h2>'
    . '<p style="color:var(--gray-600)">Jasa Kolam Renang Bogor sudah menangani pembuatan kolam renang selama lebih dari 10 tahun, dengan lebih dari 350 proyek yang telah kami selesaikan di wilayah Bogor dan sekitarnya. Sebagian proyek pembuatan kolam yang pernah kami kerjakan dapat dilihat di halaman portofolio kami, misalnya <a href="/portofolio/kolam-renang-villa-modern-di-sentul/" style="color:var(--blue-600);font-weight:600">Kolam Renang Villa Modern di Sentul</a> dan <a href="/portofolio/kolam-renang-keluarga/" style="color:var(--blue-600);font-weight:600">Kolam Renang Keluarga</a>.</p>'
    . '<img src="/photo.php?id=11" alt="Kolam renang villa modern hasil pembuatan kami di Sentul, Bogor" width="649" height="472" loading="lazy" style="width:100%;max-width:520px;height:auto;display:block;margin-top:16px;border-radius:var(--radius-md);box-shadow:var(--shadow-sm)">'

    . '<h2 style="margin-top:32px">Area yang Dilayani</h2>'
    . '<p style="color:var(--gray-600)">Kami mengerjakan pembuatan kolam renang di berbagai karakteristik lahan Bogor — mulai dari lahan berkontur perbukitan seperti <a href="/area/sentul/" style="color:var(--blue-600);font-weight:600">Sentul</a>, hingga lahan terbatas di kawasan padat seperti <a href="/area/bogor-kota/" style="color:var(--blue-600);font-weight:600">Kota Bogor</a>. Untuk daftar lengkap area yang kami layani, lihat <a href="/area-layanan/" style="color:var(--blue-600);font-weight:600">halaman Area Layanan</a>.</p>'

    . '<h2 style="margin-top:32px">Konsultasi Pembuatan Kolam Renang</h2>'
    . '<p style="color:var(--gray-600)">Ingin membangun kolam renang di rumah, villa, atau resort Anda? Hubungi kami untuk survei lokasi gratis dan penawaran biaya yang sesuai dengan kondisi lahan Anda — tanpa komitmen apa pun.</p>'
    . '<p><a href="https://wa.me/6282216623388" style="color:var(--blue-600);font-weight:600">Chat via WhatsApp untuk Konsultasi &rarr;</a></p>';

$faq = [
    ['q' => 'Berapa lama waktu pembuatan kolam renang baru?', 'a' => 'Rata-rata 3–6 minggu untuk kolam berukuran standar rumah tinggal, tergantung ukuran, desain, dan kondisi lahan. Kami sampaikan estimasi pasti setelah survei lokasi.'],
    ['q' => 'Apakah survei lokasi dikenakan biaya?', 'a' => 'Tidak, survei lokasi dan konsultasi awal kami berikan gratis tanpa biaya.'],
    ['q' => 'Sudah berapa lama Jasa Kolam Renang Bogor menangani pembuatan kolam renang?', 'a' => 'Kami sudah menangani pembuatan kolam renang selama lebih dari 10 tahun, dengan lebih dari 350 proyek yang telah kami selesaikan di wilayah Bogor dan sekitarnya.'],
];

try {
    $pdo = get_db();
    $stmt = $pdo->prepare(
        'UPDATE pages SET meta_description = :meta_description, content = :content, faq_json = :faq_json
         WHERE url_path = :url_path AND type = \'service\''
    );
    $stmt->execute([
        ':meta_description' => $metaDescription,
        ':content' => $content,
        ':faq_json' => json_encode($faq, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
        ':url_path' => $urlPath,
    ]);

    if ($stmt->rowCount() === 0) {
        $check = $pdo->prepare('SELECT id FROM pages WHERE url_path = :u');
        $check->execute([':u' => $urlPath]);
        if (!$check->fetch()) {
            respond(false, 'Baris tidak ditemukan -- tidak ada yang diubah.');
        }
        respond(true, 'Tidak ada perubahan (isi sudah sama persis, sudah pernah dijalankan sebelumnya).');
    }

    respond(true, 'Halaman "Pembuatan Kolam Renang Baru" berhasil diperbarui.', ['url_path' => $urlPath]);
} catch (Exception $e) {
    respond(false, 'Gagal memperbarui: ' . $e->getMessage());
}
