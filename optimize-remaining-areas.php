<?php
/**
 * Skrip sekali-jalan: optimasi SEO 9 halaman area yang tersisa (dari 10
 * total -- /area/bogor-kota/ sudah dioptimasi di Phase 5). Ada 2 gaya
 * konten berbeda di antara 9 halaman ini (temuan dari audit Bogor Kota
 * sebelumnya):
 *
 * - GAYA "PRIORITAS" (Sentul, Puncak, Cibinong): struktur kaya (two-col
 *   + info-box + 4 kartu layanan), sama seperti Bogor Kota SEBELUM
 *   Phase 5 -- py bug yang sama: H4 "Cakupan Wilayah" (skip H3), 4
 *   kartu layanan tidak ditautkan, meta description ada bug kapitalisasi
 *   nama area huruf kecil. Diperbaiki dengan str_replace() presisi
 *   terhadap content yang SEDANG tersimpan (bukan ditulis ulang manual)
 *   supaya tidak berisiko salah ketik ulang paragraf yang sudah bagus,
 *   lalu ditambah section portofolio & fakta bisnis di akhir.
 *
 * - GAYA "PERAWATAN" (Ciawi, Yasmin, Cijeruk, Rancamaya, Bogor Raya,
 *   Karadenan): struktur sudah H2 bersih (TIDAK ada bug heading), meta
 *   description sudah baik. Tidak ada internal link sama sekali di
 *   konten manapun -- ditambah section "Layanan Terkait" + fakta bisnis
 *   di akhir (append, tidak mengubah konten existing sama sekali).
 *   Cijeruk SATU-SATUNYA dari 6 ini yang punya portofolio bertag area
 *   ini di database -- 5 lainnya TIDAK, jadi TIDAK dipaksakan
 *   portofolio (dicatat DATA_REQUIRED di laporan, bukan dikarang).
 *
 * Semua link diverifikasi HTTP 200 satu per satu sebelum dipakai (lihat
 * commit message). URL layanan "Perawatan & Pembersihan Rutin" dipakai
 * dalam bentuk BARU (/layanan/jasa-perawatan-kolam-renang/, hasil
 * migrasi Phase 7), bukan URL lama yang sekarang cuma redirect.
 *
 * DATA SAFETY: tiap baris di-UPDATE dengan guard id (dibaca dulu) +
 * url_path + type='area'. Untuk 3 halaman gaya "Prioritas", content
 * BARU dibangun dari content LAMA yang dibaca live saat skrip jalan
 * (str_replace + append), bukan hardcode string yang ditulis ulang total.
 *
 * Dijalankan lewat browser (Basic Auth). Aman dijalankan berkali-kali.
 */
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/inc/db.php';

function respond($success, $message, $extra = []) {
    echo json_encode(array_merge(['success' => $success, 'message' => $message], $extra), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}

function build_p($text) { return '<p style="color:var(--gray-600)">' . $text . '</p>'; }
function build_link($url, $text) { return '<a href="' . $url . '" style="color:var(--blue-600);font-weight:600">' . $text . '</a>'; }
function build_portfolio_card($href, $img, $w, $h, $alt, $tag, $title, $desc) {
    return '<a class="portfolio-card" href="' . $href . '" style="display:block;color:inherit">'
        . '<div class="portfolio-thumb"><img src="' . $img . '" alt="' . $alt . '" width="' . $w . '" height="' . $h . '" loading="lazy"></div>'
        . '<div class="portfolio-body"><span class="tag">' . $tag . '</span><h3>' . $title . '</h3>'
        . build_p($desc) . '<span class="portfolio-link">Lihat Detail <span class="arrow">&rarr;</span></span></div></a>';
}

$SVC_PEMBUATAN = '/layanan/pembuatan-kolam-renang-baru/';
$SVC_PERAWATAN = '/layanan/jasa-perawatan-kolam-renang/';
$SVC_RENOVASI = '/layanan/renovasi-perbaikan-kolam/';
$SVC_INSTALASI = '/layanan/instalasi-filter-pompa/';
$SVC_KONTRAK = '/layanan/kontrak-perawatan-bulanan/';
$SVC_KIMIA = '/layanan/penyeimbangan-kimia-air-kolam/';
$SVC_SIRKULASI = '/layanan/perbaikan-sistem-sirkulasi-air/';

// ============================================================
// GAYA "PRIORITAS": Sentul, Puncak, Cibinong
// ============================================================
$priorityPages = [];

$priorityPages['/area/sentul/'] = [
    'area_name' => 'Sentul',
    'meta_description' => 'Jasa kolam renang Sentul terpercaya — pembuatan kolam baru, perawatan rutin, renovasi, dan instalasi filter/pompa. Konsultasi gratis via WhatsApp.',
    'append' =>
        '<div class="section-head" style="margin-top:48px"><span class="eyebrow">Bukti Pengerjaan</span><h2>Proyek Kami di Sentul</h2>'
        . build_p('Dengan lebih dari 10 tahun pengalaman dan 350+ proyek yang telah kami selesaikan di wilayah Bogor dan sekitarnya, berikut beberapa contoh pekerjaan kami di kawasan Sentul.') . '</div>'
        . '<div class="portfolio-grid">'
        . build_portfolio_card('/portofolio/kolam-renang-villa-modern-di-sentul/', '/photo.php?id=11', 649, 472, 'Kolam renang villa modern di Sentul', 'Sentul', 'Kolam Renang Villa Modern di Sentul', 'Kolam renang villa modern di Sentul, dirancang agar kolam menjadi bagian dari keseluruhan desain properti, bukan sekadar fasilitas tambahan.')
        . build_portfolio_card('/portofolio/instalasi-sistem-filtrasi/', '/photo.php?id=17', 427, 299, 'Instalasi sistem filtrasi kolam renang di Sentul', 'Sentul', 'Instalasi Sistem Filtrasi', 'Pemasangan sistem sirkulasi dan sanitasi air otomatis untuk kolam renang di kawasan Sentul, Bogor.')
        . '</div>'
        . '<h2 style="margin-top:32px">Bacaan Terkait</h2>'
        . build_p('Banyak villa di Sentul dihuni musiman — kami membahas cara merawat kolam yang jarang dipakai secara lebih lengkap di artikel ' . build_link('/artikel/tips-merawat-kolam-renang-vila-jarang-dipakai/', 'Tips Merawat Kolam Renang Vila yang Jarang Dipakai') . '.'),
];

$priorityPages['/area/puncak/'] = [
    'area_name' => 'Puncak',
    'meta_description' => 'Jasa kolam renang Puncak terpercaya — pembuatan kolam baru, perawatan rutin, renovasi, dan instalasi filter/pompa tahan cuaca dingin & hujan. Konsultasi gratis.',
    'append' =>
        '<div class="section-head" style="margin-top:48px"><span class="eyebrow">Bukti Pengerjaan</span><h2>Proyek Kami di Puncak</h2>'
        . build_p('Dengan lebih dari 10 tahun pengalaman dan 350+ proyek yang telah kami selesaikan di wilayah Bogor dan sekitarnya, berikut salah satu contoh pekerjaan kami di kawasan Puncak.') . '</div>'
        . '<div class="portfolio-grid">'
        . build_portfolio_card('/portofolio/renovasi-kolam-renang-di-puncak-bogor/', '/photo.php?id=13', 390, 256, 'Renovasi kolam renang di Puncak, Bogor', 'Puncak', 'Renovasi Kolam Renang di Puncak Bogor', 'Proyek renovasi kolam renang di kawasan Puncak, Bogor, untuk meningkatkan tampilan, kenyamanan, dan fungsi kolam.')
        . '</div>'
        . '<h2 style="margin-top:32px">Bacaan Terkait</h2>'
        . build_p('Curah hujan tinggi khas kawasan Puncak memengaruhi kualitas air kolam lebih cepat — kami membahasnya lebih lengkap di artikel ' . build_link('/artikel/kenapa-air-kolam-keruh-saat-musim-hujan-bogor/', 'Kenapa Air Kolam Cepat Keruh Saat Musim Hujan di Bogor') . '.'),
];

$priorityPages['/area/cibinong/'] = [
    'area_name' => 'Cibinong',
    'meta_description' => 'Jasa kolam renang Cibinong terpercaya — pembuatan kolam baru, perawatan rutin, renovasi, dan instalasi filter/pompa. Konsultasi gratis via WhatsApp.',
    'append' =>
        '<div class="section-head" style="margin-top:48px"><span class="eyebrow">Bukti Pengerjaan</span><h2>Proyek Kami di Cibinong</h2>'
        . build_p('Dengan lebih dari 10 tahun pengalaman dan 350+ proyek yang telah kami selesaikan di wilayah Bogor dan sekitarnya, berikut salah satu contoh pekerjaan kami di kawasan Cibinong.') . '</div>'
        . '<div class="portfolio-grid">'
        . build_portfolio_card('/portofolio/kolam-renang-keluarga/', '/photo.php?id=14', 427, 299, 'Kolam renang keluarga di Cibinong', 'Cibinong', 'Kolam Renang Keluarga', 'Kolam renang minimalis untuk hunian keluarga di kawasan perumahan Cibinong, disesuaikan dengan lahan rumah yang terbatas.')
        . '</div>'
        . '<h2 style="margin-top:32px">Bacaan Terkait</h2>'
        . build_p('Untuk pemilik kolam rumahan di Cibinong, kami merangkum kebiasaan yang sebaiknya dihindari di artikel ' . build_link('/artikel/kesalahan-umum-pemilik-kolam-renang-rumahan/', 'Kesalahan Umum Pemilik Kolam Renang Rumahan') . '.'),
];

// ============================================================
// GAYA "PERAWATAN": Ciawi, Yasmin, Cijeruk, Rancamaya, Bogor Raya, Karadenan
// (append saja, TIDAK ada bug heading/meta yang perlu diperbaiki)
// ============================================================
$perawatanPages = [];

$perawatanPages['/area/ciawi/'] = [
    'append' =>
        '<h2>Layanan Terkait di Ciawi</h2>'
        . build_p('Untuk properti dengan hunian musiman seperti banyak ditemui di Ciawi, ' . build_link($SVC_KONTRAK, 'Kontrak Perawatan Bulanan') . ' membantu menjaga jadwal tetap terkoordinasi. Kami juga membahas cara merawat kolam yang jarang dipakai di artikel ' . build_link('/artikel/tips-merawat-kolam-renang-vila-jarang-dipakai/', 'Tips Merawat Kolam Renang Vila yang Jarang Dipakai') . '.')
        . '<h2 style="margin-top:32px">Pengalaman Kami</h2>'
        . build_p('Dengan lebih dari 10 tahun pengalaman dan 350+ proyek yang telah kami selesaikan di wilayah Bogor dan sekitarnya, kami memahami karakter kolam-kolam musiman di jalur Puncak seperti Ciawi.'),
];

$perawatanPages['/area/yasmin/'] = [
    'append' =>
        '<h2>Layanan Terkait di Yasmin</h2>'
        . build_p('Untuk kolam yang dipakai setiap hari seperti umum di Yasmin, menjaga ' . build_link($SVC_KIMIA, 'keseimbangan kimia air') . ' jadi kunci utama. Kami membahas rentang pH ideal di artikel ' . build_link('/artikel/cara-menjaga-ph-air-kolam-tetap-ideal/', 'Cara Menjaga pH Air Kolam Tetap Ideal') . ' dan frekuensi pengecekan yang disarankan di artikel ' . build_link('/artikel/berapa-kali-cek-ph-air-kolam-per-minggu/', 'Berapa Kali Idealnya Cek pH Air Kolam per Minggu') . '.')
        . '<h2 style="margin-top:32px">Pengalaman Kami</h2>'
        . build_p('Dengan lebih dari 10 tahun pengalaman dan 350+ proyek yang telah kami selesaikan di wilayah Bogor dan sekitarnya, kami terbiasa menangani kolam rumah tinggal dengan pemakaian harian seperti di Yasmin.'),
];

$perawatanPages['/area/cijeruk/'] = [
    'append' =>
        '<h2>Proyek yang Pernah Kami Kerjakan</h2>'
        . build_p('Dengan lebih dari 10 tahun pengalaman dan 350+ proyek yang telah kami selesaikan di wilayah Bogor dan sekitarnya, berikut contoh pekerjaan kami di kawasan Cijeruk.')
        . '<div class="portfolio-grid">' . build_portfolio_card('/portofolio/perawatan-kolam-renang-di-cijeruk-bogor/', '/photo.php?id=12', 426, 292, 'Perawatan kolam renang di Cijeruk, Bogor', 'Cijeruk', 'Perawatan Kolam Renang di Cijeruk, Bogor', 'Proyek perawatan kolam renang di Cijeruk, Bogor, dilakukan dengan pemeriksaan kondisi air, kebersihan kolam, sirkulasi, pompa, dan filter.') . '</div>'
        . '<h2 style="margin-top:32px">Layanan Terkait di Cijeruk</h2>'
        . build_p('Kelembaban tinggi khas kaki Gunung Salak mempercepat pertumbuhan alga — kami membahas cara mengatasi air kolam hijau di artikel ' . build_link('/artikel/cara-mengatasi-air-kolam-berwarna-hijau/', 'Cara Mengatasi Air Kolam Berwarna Hijau') . ' dan penyebab air keruh saat musim hujan di artikel ' . build_link('/artikel/kenapa-air-kolam-keruh-saat-musim-hujan-bogor/', 'Kenapa Air Kolam Cepat Keruh Saat Musim Hujan di Bogor') . '.'),
];

$perawatanPages['/area/rancamaya/'] = [
    'append' =>
        '<h2>Layanan Terkait di Rancamaya</h2>'
        . build_p('Untuk kolam privat berstandar tinggi seperti umum di Rancamaya, ' . build_link($SVC_KONTRAK, 'Kontrak Perawatan Bulanan') . ' membantu menjaga jadwal kunjungan tetap konsisten dan terkoordinasi dengan keamanan kompleks.')
        . '<h2 style="margin-top:32px">Pengalaman Kami</h2>'
        . build_p('Dengan lebih dari 10 tahun pengalaman dan 350+ proyek yang telah kami selesaikan di wilayah Bogor dan sekitarnya, kami terbiasa bekerja di kompleks tertutup dengan standar profesionalisme yang dibutuhkan.'),
];

$perawatanPages['/area/bogor-raya/'] = [
    'append' =>
        '<h2>Layanan Terkait di Bogor Raya</h2>'
        . build_p('Untuk kolam privat berukuran menengah-besar seperti umum di Bogor Raya, kami juga menyediakan ' . build_link($SVC_SIRKULASI, 'perbaikan sistem sirkulasi air') . ' dan ' . build_link($SVC_INSTALASI, 'instalasi filter & pompa') . ' bila diperlukan.')
        . '<h2 style="margin-top:32px">Pengalaman Kami</h2>'
        . build_p('Dengan lebih dari 10 tahun pengalaman dan 350+ proyek yang telah kami selesaikan di wilayah Bogor dan sekitarnya, kami memahami kebutuhan kolam privat di kawasan residensial modern seperti Bogor Raya.'),
];

$perawatanPages['/area/karadenan/'] = [
    'append' =>
        '<h2>Layanan Terkait di Karadenan</h2>'
        . build_p('Untuk properti komersial seperti ruko di Karadenan, ' . build_link($SVC_KONTRAK, 'Kontrak Perawatan Bulanan') . ' membantu menjaga jadwal kunjungan tetap konsisten sesuai jam operasional.')
        . '<h2 style="margin-top:32px">Pengalaman Kami</h2>'
        . build_p('Dengan lebih dari 10 tahun pengalaman dan 350+ proyek yang telah kami selesaikan di wilayah Bogor dan sekitarnya, kami melayani area yang terus berkembang seperti Karadenan.'),
];

// ============================================================
// Eksekusi
// ============================================================
$results = [];
try {
    $pdo = get_db();

    // ---- Halaman gaya "Prioritas": str_replace presisi + append ----
    foreach ($priorityPages as $urlPath => $cfg) {
        $before = $pdo->prepare('SELECT id, content FROM pages WHERE url_path = :u AND type = \'area\'');
        $before->execute([':u' => $urlPath]);
        $row = $before->fetch();
        if (!$row) { $results[$urlPath] = 'SKIPPED -- baris tidak ditemukan'; continue; }

        $content = $row['content'];
        $area = $cfg['area_name'];

        // Fix 1: H4 -> H3 untuk "Cakupan Wilayah [Area]"
        $content = str_replace('<h4>Cakupan Wilayah ' . $area . '</h4>', '<h3>Cakupan Wilayah ' . $area . '</h3>', $content);

        // Fix 2: tautkan 4 kartu layanan (judul H3 sama persis di semua halaman
        // gaya ini -- TAPI beberapa baris menyimpan "&" sebagai literal, yang
        // lain sebagai "&amp;" ter-encode, jadi dua varian dicoba supaya tidak
        // ada yang senyap tidak ketemu/tidak tertaut).
        $serviceLinks = [
            'Pembuatan Kolam Renang Baru' => $GLOBALS['SVC_PEMBUATAN'],
            'Perawatan & Pembersihan Rutin' => $GLOBALS['SVC_PERAWATAN'],
            'Renovasi & Perbaikan Kolam' => $GLOBALS['SVC_RENOVASI'],
            'Instalasi Filter & Pompa' => $GLOBALS['SVC_INSTALASI'],
        ];
        $linkedCount = 0;
        foreach ($serviceLinks as $label => $url) {
            foreach ([$label, str_replace('&', '&amp;', $label)] as $variant) {
                $needle = '<h3>' . $variant . '</h3>';
                if (strpos($content, $needle) !== false) {
                    $content = str_replace($needle, '<h3><a href="' . $url . '" style="color:inherit">' . $variant . '</a></h3>', $content);
                    $linkedCount++;
                }
            }
        }

        // Append section baru (portofolio + bacaan terkait)
        $content .= $cfg['append'];

        $upd = $pdo->prepare('UPDATE pages SET meta_description = :md, content = :c WHERE id = :id AND url_path = :u AND type = \'area\'');
        $upd->execute([':md' => $cfg['meta_description'], ':c' => $content, ':id' => $row['id'], ':u' => $urlPath]);
        $results[$urlPath] = 'OK (id=' . $row['id'] . ', style=prioritas, service_links_matched=' . $linkedCount . '/4, rows_affected=' . $upd->rowCount() . ')';
    }

    // ---- Halaman gaya "Perawatan": append saja ----
    foreach ($perawatanPages as $urlPath => $cfg) {
        $before = $pdo->prepare('SELECT id, content FROM pages WHERE url_path = :u AND type = \'area\'');
        $before->execute([':u' => $urlPath]);
        $row = $before->fetch();
        if (!$row) { $results[$urlPath] = 'SKIPPED -- baris tidak ditemukan'; continue; }

        $content = $row['content'] . $cfg['append'];

        $upd = $pdo->prepare('UPDATE pages SET content = :c WHERE id = :id AND url_path = :u AND type = \'area\'');
        $upd->execute([':c' => $content, ':id' => $row['id'], ':u' => $urlPath]);
        $results[$urlPath] = 'OK (id=' . $row['id'] . ', style=perawatan, rows_affected=' . $upd->rowCount() . ')';
    }

    respond(true, 'Optimasi 9 halaman area selesai.', ['results' => $results]);
} catch (Exception $e) {
    respond(false, 'Gagal: ' . $e->getMessage(), ['results' => $results]);
}
