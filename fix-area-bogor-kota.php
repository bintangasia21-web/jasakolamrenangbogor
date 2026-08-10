<?php
/**
 * Skrip sekali-jalan (Phase 5, 2026-08-11): implementasi perbaikan SEO
 * untuk SATU baris "pages" saja -- url_path='/area/bogor-kota/' --
 * berdasarkan AREA-BOGOR-KOTA-SEO-AUDIT.md. Hanya menyentuh kolom
 * meta_description dan content milik baris ini. title/h1/intro/faq_json
 * TIDAK diubah. Tidak menyentuh inc/templates/area.php atau baris
 * "pages" lain (area lain, layanan, artikel, portofolio).
 *
 * Perbaikan heading H4->H3 ("Cakupan Wilayah Bogor Kota") aman
 * dilakukan di sini karena HTML section itu tersimpan literal di
 * kolom "content" baris ini (area.php cuma me-render $page['content']
 * apa adanya) -- BUKAN dihasilkan oleh template.
 *
 * Gambar yang disisipkan (photo.php?id=16 & id=15) adalah foto
 * portofolio ASLI proyek "Perawatan Kolam Rutin" & "Perbaikan
 * Kebocoran Kolam" yang sudah ber-tag area="Bogor Kota" di database
 * dan sudah live publik -- dimensi diverifikasi dari getimagesize()
 * nyata (738x414 dan 427x299), bukan ditebak. Deskripsi kartu diambil
 * dari kalimat pertama deskripsi asli proyek tsb (tidak dikarang).
 *
 * Dijalankan lewat browser (Basic Auth). Aman dijalankan berkali-kali.
 */
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/inc/db.php';

function respond($success, $message, $extra = []) {
    echo json_encode(array_merge(['success' => $success, 'message' => $message], $extra), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}

$urlPath = '/area/bogor-kota/';

// FIX 1: kapitalisasi "bogor kota" -> "Bogor Kota".
$metaDescription = 'Jasa kolam renang Bogor Kota terpercaya — pembuatan kolam baru, perawatan rutin, renovasi, dan instalasi filter/pompa. Konsultasi gratis via WhatsApp.';

$content = '<div class="two-col"><div><span class="eyebrow">Karakteristik Area</span><h2>Kolam Renang untuk Kawasan Bogor Kota</h2>'
    . '<p style="color:var(--gray-600)">Kota Bogor, yang dikenal dengan julukan "Kota Hujan" dan terdiri dari enam kecamatan (Bogor Tengah, Bogor Utara, Bogor Selatan, Bogor Timur, Bogor Barat, dan Tanah Sareal), merupakan kawasan perkotaan padat dengan lahan permukiman yang relatif terbatas. Banyak rumah dan bangunan lama di kawasan ini memiliki kolam renang yang sudah berumur puluhan tahun dan membutuhkan renovasi.</p>'
    . '<p style="color:var(--gray-600)">Layanan kami di Kota Bogor berfokus pada renovasi kolam lama, <a href="/layanan/perbaikan-kebocoran-kolam/" style="color:var(--blue-600);font-weight:600">perbaikan kebocoran</a> akibat curah hujan tinggi yang terus-menerus, serta solusi kolam renang hemat lahan untuk hunian di kawasan padat penduduk, termasuk perawatan rutin untuk hotel dan guesthouse di area pusat kota.</p>'
    . '<p style="color:var(--gray-600)">Untuk penjelasan lebih lengkap soal penyebabnya, baca juga: <a href="/artikel/kenapa-air-kolam-keruh-saat-musim-hujan-bogor/" style="color:var(--blue-600);font-weight:600">Kenapa Air Kolam Cepat Keruh Saat Musim Hujan di Bogor</a>.</p>'
    . '<div class="badge-list"><span>Renovasi Kolam Lama</span><span>Perbaikan Kebocoran</span><span>Perawatan Hotel & Guesthouse</span></div></div>'
    . '<div class="info-box"><h3>Cakupan Wilayah Bogor Kota</h3><p style="color:var(--gray-600)">Bogor Tengah, Bogor Utara, Bogor Selatan, Bogor Timur, Bogor Barat, dan Tanah Sareal — mencakup kawasan sekitar Kebun Raya Bogor dan pusat kota.</p></div></div>'

    . '<div class="section-head" style="margin-top:48px"><span class="eyebrow">Layanan Unggulan di Bogor Kota</span><h2>Disesuaikan dengan Karakteristik Wilayah</h2></div>'
    . '<div class="services-grid">'
    . '<div class="service-card"><h3><a href="/layanan/pembuatan-kolam-renang-baru/" style="color:inherit">Pembuatan Kolam Renang Baru</a></h3><p style="color:var(--gray-600);font-size:.9rem">Dengan lahan yang relatif terbatas di kawasan padat Kota Bogor, kami merancang kolam renang minimalis yang memaksimalkan pekarangan yang tersedia tanpa mengorbankan kenyamanan, cocok untuk rumah tinggal maupun guesthouse di area perkotaan.</p></div>'
    . '<div class="service-card"><h3><a href="/layanan/perawatan-pembersihan-rutin/" style="color:inherit">Perawatan & Pembersihan Rutin</a></h3><p style="color:var(--gray-600);font-size:.9rem">Curah hujan tinggi yang konsisten sepanjang tahun di Kota Bogor mempercepat perubahan kualitas air kolam. Kami menyediakan jadwal perawatan lebih rapat khusus musim hujan agar air tetap jernih dan seimbang secara kimiawi.</p></div>'
    . '<div class="service-card"><h3><a href="/layanan/renovasi-perbaikan-kolam/" style="color:inherit">Renovasi & Perbaikan Kolam</a></h3><p style="color:var(--gray-600);font-size:.9rem">Banyak kolam renang di Kota Bogor sudah berusia puluhan tahun dengan struktur dan lapisan waterproofing yang mulai menurun. Kami menangani renovasi menyeluruh — mulai dari perbaikan retak struktur, penggantian keramik, hingga pembaruan sistem sirkulasi — tanpa selalu perlu bongkar total.</p></div>'
    . '<div class="service-card"><h3><a href="/layanan/instalasi-filter-pompa/" style="color:inherit">Instalasi Filter & Pompa</a></h3><p style="color:var(--gray-600);font-size:.9rem">Untuk kolam renang di kawasan padat kota, kami memasang sistem filter dan pompa yang efisien ruang serta hemat energi, sekaligus mempertimbangkan akses ruang mesin yang sering terbatas pada bangunan lama di Kota Bogor.</p></div>'
    . '</div>'

    . '<div class="section-head" style="margin-top:48px"><span class="eyebrow">Bukti Pengerjaan</span><h2>Proyek Kami di Bogor Kota</h2>'
    . '<p style="color:var(--gray-600)">Dengan lebih dari 10 tahun pengalaman dan 350+ proyek yang telah kami selesaikan di wilayah Bogor dan sekitarnya, berikut beberapa contoh pekerjaan kami khusus di kawasan Bogor Kota.</p></div>'
    . '<div class="portfolio-grid">'
    . '<a class="portfolio-card" href="/portofolio/perawatan-kolam-rutin/" style="display:block;color:inherit"><div class="portfolio-thumb"><img src="/photo.php?id=16" alt="Perawatan kolam renang rutin di Bogor Kota" width="738" height="414" loading="lazy"></div><div class="portfolio-body"><span class="tag">Bogor Kota</span><h3>Perawatan Kolam Rutin</h3><p style="color:var(--gray-600)">Program perawatan bulanan untuk kolam renang hotel di kawasan Bogor Kota, mencakup pengecekan kualitas air, pembersihan filter, dan penyeimbangan kimia secara berkala.</p><span class="portfolio-link">Lihat Detail <span class="arrow">&rarr;</span></span></div></a>'
    . '<a class="portfolio-card" href="/portofolio/perbaikan-kebocoran-kolam/" style="display:block;color:inherit"><div class="portfolio-thumb"><img src="/photo.php?id=15" alt="Perbaikan kebocoran dan waterproofing kolam di Bogor Kota" width="427" height="299" loading="lazy"></div><div class="portfolio-body"><span class="tag">Bogor Kota</span><h3>Perbaikan Kebocoran Kolam</h3><p style="color:var(--gray-600)">Perbaikan struktur dan pelapisan ulang waterproofing untuk kolam renang lama di kawasan Bogor Kota yang mengalami rembesan akibat curah hujan tinggi.</p><span class="portfolio-link">Lihat Detail <span class="arrow">&rarr;</span></span></div></a>'
    . '</div>';

try {
    $pdo = get_db();
    $stmt = $pdo->prepare(
        'UPDATE pages SET meta_description = :meta_description, content = :content
         WHERE url_path = :url_path AND type = \'area\''
    );
    $stmt->execute([
        ':meta_description' => $metaDescription,
        ':content' => $content,
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

    respond(true, 'Halaman Area Bogor Kota berhasil diperbarui.', ['url_path' => $urlPath]);
} catch (Exception $e) {
    respond(false, 'Gagal memperbarui: ' . $e->getMessage());
}
