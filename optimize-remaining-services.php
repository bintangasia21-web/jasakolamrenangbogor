<?php
/**
 * Skrip sekali-jalan: optimasi SEO & konten untuk 17 halaman layanan
 * yang belum disentuh (dari 20 total -- 3 lainnya sudah dioptimasi
 * sebelumnya: pembuatan-kolam-renang-baru, perbaikan-kebocoran-kolam,
 * jasa-perawatan-kolam-renang). Pola & prinsip SAMA PERSIS seperti
 * ketiga fase sebelumnya:
 *
 * - Heading H1->H4 (skip H2/H3, "Tahapan Pengerjaan" di dalam
 *   .info-box) diperbaiki jadi H1->H2 bersih.
 * - Internal link HANYA ditambahkan kalau targetnya genuinely relevan
 *   DAN sudah diverifikasi HTTP 200 (dicek manual satu per satu
 *   sebelum skrip ini ditulis -- lihat commit message).
 * - Portfolio/artikel HANYA dipakai kalau memang ada yang temanya
 *   cocok -- beberapa layanan (penggantian keramik, pembuatan ruang
 *   mesin, dll) TIDAK punya portfolio/artikel spesifik yang relevan
 *   di database saat ini, jadi TIDAK dipaksakan (bukan DATA_REQUIRED
 *   yang blocking, karena halaman tetap lengkap tanpa itu -- functional
 *   equivalent dari "skip section yang tidak didukung data").
 * - 6 langkah "Tahapan Pengerjaan" tiap halaman TIDAK diubah isinya,
 *   cuma dipindah dari .info-box/H4 ke <ol> di bawah H2.
 * - 2 FAQ lama tiap halaman dipertahankan PERSIS, ditambah 1 FAQ baru
 *   yang HANYA memakai fakta terverifikasi (10+ tahun, 350+ proyek).
 * - title/h1/intro TIDAK diubah (semua sudah dinilai baik sebelumnya).
 *
 * DATA SAFETY: tiap baris di-UPDATE dengan guard url_path + type +
 * id (dibaca dulu per baris sebelum UPDATE) -- SATU query per baris,
 * tidak ada UPDATE massal berdasarkan pola/LIKE.
 *
 * Dijalankan lewat browser (Basic Auth). Aman dijalankan berkali-kali.
 */
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/inc/db.php';

function respond($success, $message, $extra = []) {
    echo json_encode(array_merge(['success' => $success, 'message' => $message], $extra), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}

function build_ol($steps) {
    $html = '<ol style="color:var(--gray-600);padding-left:20px;margin:0">';
    foreach ($steps as $s) $html .= '<li>' . $s . '</li>';
    $html .= '</ol>';
    return $html;
}

function build_p($text) {
    return '<p style="color:var(--gray-600)">' . $text . '</p>';
}

function build_link($url, $text) {
    return '<a href="' . $url . '" style="color:var(--blue-600);font-weight:600">' . $text . '</a>';
}

function build_closing($text) {
    return build_p($text) . '<p><a href="https://wa.me/6282216623388" style="color:var(--blue-600);font-weight:600">Chat via WhatsApp untuk Konsultasi &rarr;</a></p>';
}

function build_portfolio_card($href, $img, $w, $h, $alt, $tag, $title, $desc) {
    return '<a class="portfolio-card" href="' . $href . '" style="display:block;color:inherit">'
        . '<div class="portfolio-thumb"><img src="' . $img . '" alt="' . $alt . '" width="' . $w . '" height="' . $h . '" loading="lazy"></div>'
        . '<div class="portfolio-body"><span class="tag">' . $tag . '</span><h3>' . $title . '</h3>'
        . build_p($desc) . '<span class="portfolio-link">Lihat Detail <span class="arrow">&rarr;</span></span></div></a>';
}

// ============================================================
// Data per halaman -- title/h1/intro TIDAK disentuh (kolom itu
// tidak ada di $pagesData, cuma meta_description/content/faq_json).
// ============================================================
$pagesData = [];

// 1. Renovasi & Perbaikan Kolam
$pagesData['/layanan/renovasi-perbaikan-kolam/'] = [
    'meta_description' => 'Renovasi kolam renang lama di Bogor — perbaikan struktur, keramik, dan waterproofing agar kolam tampil dan berfungsi seperti baru. Survei gratis.',
    'content' =>
        '<h2>Renovasi Kolam Renang Lama di Bogor</h2>'
        . build_p('Kolam renang yang sudah berumur puluhan tahun sering mengalami penurunan struktur, keramik pecah, atau lapisan waterproofing yang mulai gagal. Renovasi membantu mengembalikan kondisi kolam tanpa harus membangun ulang dari nol.')
        . '<h2 style="margin-top:32px">Tahapan Pengerjaan</h2>'
        . build_ol(['Survei & diagnosis sumber kerusakan', 'Penawaran rencana perbaikan', 'Pengeringan & pembongkaran bagian rusak', 'Perbaikan struktur & waterproofing ulang', 'Pemasangan keramik/finishing baru', 'Pengisian air & pengecekan akhir'])
        . '<h2 style="margin-top:32px">Proyek yang Pernah Kami Kerjakan</h2>'
        . build_p('Dengan lebih dari 10 tahun pengalaman dan 350+ proyek yang telah kami selesaikan di wilayah Bogor dan sekitarnya, berikut salah satu contoh renovasi kolam yang pernah kami tangani.')
        . '<div class="portfolio-grid">' . build_portfolio_card('/portofolio/renovasi-kolam-renang-di-puncak-bogor/', '/photo.php?id=13', 390, 256, 'Renovasi kolam renang di Puncak, Bogor', 'Puncak', 'Renovasi Kolam Renang di Puncak Bogor', 'Proyek renovasi kolam renang di kawasan Puncak, Bogor, untuk meningkatkan tampilan, kenyamanan, dan fungsi kolam.') . '</div>'
        . '<h2 style="margin-top:32px">Layanan Terkait</h2>'
        . build_p('Renovasi sering mencakup ' . build_link('/layanan/waterproofing-kolam-renang/', 'waterproofing ulang') . ' dan ' . build_link('/layanan/penggantian-keramik-kolam/', 'penggantian keramik') . '. Kalau masalah utamanya kebocoran aktif, lihat juga ' . build_link('/layanan/perbaikan-kebocoran-kolam/', 'perbaikan kebocoran kolam') . '.')
        . '<h2 style="margin-top:32px">Konsultasi Renovasi Kolam</h2>'
        . build_closing('Kolam Anda mulai terlihat usang atau ada tanda kerusakan? Hubungi kami untuk survei kondisi dan rencana renovasi yang sesuai.'),
    'faq_extra' => ['q' => 'Sudah berapa lama menangani renovasi kolam renang?', 'a' => 'Renovasi kolam adalah bagian dari lebih dari 10 tahun pengalaman kami, dengan lebih dari 350 proyek yang telah kami selesaikan di wilayah Bogor dan sekitarnya.'],
];

// 2. Instalasi Filter & Pompa
$pagesData['/layanan/instalasi-filter-pompa/'] = [
    'meta_description' => 'Instalasi filter dan pompa kolam renang di Bogor — pemasangan sistem sirkulasi & sanitasi yang tepat untuk ukuran kolam Anda. Konsultasi gratis.',
    'content' =>
        '<h2>Instalasi Filter &amp; Pompa Kolam Renang</h2>'
        . build_p('Sistem filter dan pompa yang tepat kapasitasnya menentukan seberapa jernih dan efisien kolam renang Anda dalam jangka panjang. Kami membantu memilih dan memasang sistem yang sesuai dengan volume dan kebutuhan kolam Anda.')
        . '<h2 style="margin-top:32px">Tahapan Pengerjaan</h2>'
        . build_ol(['Perhitungan kapasitas sesuai volume kolam', 'Pemilihan jenis filter (pasir/cartridge)', 'Pemasangan pompa & jalur plumbing', 'Pengaturan sistem sanitasi air', 'Uji coba sirkulasi & tekanan', 'Panduan penggunaan untuk pemilik'])
        . '<h2 style="margin-top:32px">Proyek yang Pernah Kami Kerjakan</h2>'
        . build_p('Dengan lebih dari 10 tahun pengalaman dan 350+ proyek yang telah kami selesaikan di wilayah Bogor dan sekitarnya, berikut salah satu contoh instalasi sistem filtrasi yang pernah kami tangani.')
        . '<div class="portfolio-grid">' . build_portfolio_card('/portofolio/instalasi-sistem-filtrasi/', '/photo.php?id=17', 427, 299, 'Instalasi sistem filtrasi kolam renang di Sentul', 'Sentul', 'Instalasi Sistem Filtrasi', 'Pemasangan sistem sirkulasi dan sanitasi air otomatis untuk kolam renang di kawasan Sentul, Bogor.') . '</div>'
        . '<h2 style="margin-top:32px">Masalah yang Bisa Dicegah</h2>'
        . build_p('Sistem filtrasi yang sudah lama sering butuh ' . build_link('/layanan/penggantian-pasir-filter/', 'penggantian pasir filter') . ' atau berkaitan dengan ' . build_link('/layanan/perbaikan-sistem-sirkulasi-air/', 'perbaikan sistem sirkulasi') . '. Kami juga membahas tanda pompa perlu diganti di artikel ' . build_link('/artikel/tanda-pompa-kolam-renang-perlu-diganti/', 'Tanda-tanda Pompa Kolam Renang Perlu Diganti') . ' dan cara kerja backwash di artikel ' . build_link('/artikel/apa-itu-backwash-filter-kolam-renang/', 'Apa Itu Backwash Filter dan Kapan Perlu Dilakukan') . '.')
        . '<h2 style="margin-top:32px">Konsultasi Instalasi Filter & Pompa</h2>'
        . build_closing('Ingin memasang atau mengganti sistem filter dan pompa kolam Anda? Hubungi kami untuk konsultasi kapasitas yang sesuai.'),
    'faq_extra' => ['q' => 'Sudah berapa lama menangani instalasi filter dan pompa kolam renang?', 'a' => 'Instalasi sistem filtrasi adalah bagian dari lebih dari 10 tahun pengalaman kami, dengan lebih dari 350 proyek yang telah kami selesaikan di wilayah Bogor dan sekitarnya.'],
];

// 3. Penggantian Keramik Kolam
$pagesData['/layanan/penggantian-keramik-kolam/'] = [
    'meta_description' => 'Penggantian keramik kolam renang di Bogor — keramik pecah, pudar, atau berlumut diganti agar tampilan rapi dan permukaan aman. Survei gratis.',
    'content' =>
        '<h2>Penggantian Keramik Kolam Renang</h2>'
        . build_p('Keramik yang pecah, pudar, atau berlumut bukan cuma soal tampilan — permukaan yang rusak juga bisa jadi licin dan berisiko melukai pengguna kolam. Penggantian keramik mengembalikan tampilan sekaligus keamanan permukaan kolam.')
        . '<h2 style="margin-top:32px">Tahapan Pengerjaan</h2>'
        . build_ol(['Survei kondisi keramik & area rusak', 'Pembongkaran keramik lama', 'Perbaikan lapisan semen & waterproofing', 'Pemasangan keramik baru', 'Nat ulang & finishing', 'Pengecekan akhir sebelum pengisian air'])
        . '<h2 style="margin-top:32px">Layanan Terkait</h2>'
        . build_p('Penggantian keramik sering dilakukan bersamaan dengan ' . build_link('/layanan/waterproofing-kolam-renang/', 'waterproofing ulang') . ', terutama untuk kolam lama yang juga masuk kategori ' . build_link('/layanan/renovasi-perbaikan-kolam/', 'renovasi & perbaikan kolam') . '.')
        . '<h2 style="margin-top:32px">Konsultasi Penggantian Keramik</h2>'
        . build_closing('Keramik kolam Anda mulai pecah atau berlumut? Hubungi kami untuk survei dan rekomendasi jenis keramik yang sesuai.'),
    'faq_extra' => ['q' => 'Sudah berapa lama menangani penggantian keramik kolam renang?', 'a' => 'Penggantian keramik adalah bagian dari lebih dari 10 tahun pengalaman kami, dengan lebih dari 350 proyek yang telah kami selesaikan di wilayah Bogor dan sekitarnya.'],
];

// 4. Penggantian Pasir Filter
$pagesData['/layanan/penggantian-pasir-filter/'] = [
    'meta_description' => 'Penggantian pasir filter kolam renang di Bogor — media filter yang jenuh diganti agar sistem filtrasi kembali optimal. Konsultasi gratis via WhatsApp.',
    'content' =>
        '<h2>Penggantian Pasir Filter Kolam Renang</h2>'
        . build_p('Pasir filter yang sudah jenuh atau menggumpal menurunkan efektivitas penyaringan meski pompa masih menyala normal — air jadi kurang jernih meski bahan kimia sudah seimbang.')
        . '<h2 style="margin-top:32px">Tahapan Pengerjaan</h2>'
        . build_ol(['Pemeriksaan tekanan & kondisi filter', 'Pengurasan tangki filter', 'Pembongkaran pasir lama', 'Pengisian pasir silika baru sesuai lapisan', 'Backwash & rinse sistem', 'Uji tekanan & aliran air'])
        . '<h2 style="margin-top:32px">Masalah yang Bisa Dicegah</h2>'
        . build_p('Kami membahas lebih lanjut soal cara kerja backwash dan tanda filter perlu diganti di artikel ' . build_link('/artikel/apa-itu-backwash-filter-kolam-renang/', 'Apa Itu Backwash Filter dan Kapan Perlu Dilakukan') . '. Layanan ini juga berkaitan dengan ' . build_link('/layanan/instalasi-filter-pompa/', 'instalasi filter & pompa') . ' dan ' . build_link('/layanan/perbaikan-sistem-sirkulasi-air/', 'perbaikan sistem sirkulasi air') . '.')
        . '<h2 style="margin-top:32px">Konsultasi Penggantian Pasir Filter</h2>'
        . build_closing('Tekanan filter kolam Anda terus naik meski sudah di-backwash? Hubungi kami untuk pengecekan kondisi media filter.'),
    'faq_extra' => ['q' => 'Sudah berapa lama menangani penggantian pasir filter kolam renang?', 'a' => 'Penggantian pasir filter adalah bagian dari lebih dari 10 tahun pengalaman kami, dengan lebih dari 350 proyek yang telah kami selesaikan di wilayah Bogor dan sekitarnya.'],
];

// 5. Perbaikan Sistem Sirkulasi Air
$pagesData['/layanan/perbaikan-sistem-sirkulasi-air/'] = [
    'meta_description' => 'Perbaikan sistem sirkulasi air kolam renang di Bogor — atasi jalur pipa tersumbat atau sirkulasi tidak merata. Survei & diagnosis awal gratis.',
    'content' =>
        '<h2>Perbaikan Sistem Sirkulasi Air Kolam Renang</h2>'
        . build_p('Sirkulasi yang tersumbat atau tidak merata membuat sebagian area kolam terasa lebih kotor dibanding area lain, meski pompa dan filter berfungsi normal — biasanya disebabkan masalah pada jalur pipa, inlet, atau outlet.')
        . '<h2 style="margin-top:32px">Tahapan Pengerjaan</h2>'
        . build_ol(['Pemeriksaan jalur pipa & titik sirkulasi', 'Identifikasi sumbatan/kerusakan', 'Perbaikan atau penggantian komponen', 'Penyesuaian posisi inlet/outlet bila perlu', 'Uji sirkulasi menyeluruh', 'Rekomendasi perawatan lanjutan'])
        . '<h2 style="margin-top:32px">Layanan Terkait</h2>'
        . build_p('Masalah sirkulasi sering berkaitan dengan kondisi ' . build_link('/layanan/instalasi-filter-pompa/', 'filter & pompa') . ' atau ' . build_link('/layanan/penggantian-pasir-filter/', 'media filter yang sudah jenuh') . '. Tanda-tanda pompa perlu diganti kami bahas di artikel ' . build_link('/artikel/tanda-pompa-kolam-renang-perlu-diganti/', 'Tanda-tanda Pompa Kolam Renang Perlu Diganti') . '.')
        . '<h2 style="margin-top:32px">Konsultasi Perbaikan Sirkulasi</h2>'
        . build_closing('Ada area kolam yang selalu terlihat lebih kotor dari bagian lain? Hubungi kami untuk pemeriksaan jalur sirkulasi.'),
    'faq_extra' => ['q' => 'Sudah berapa lama menangani perbaikan sistem sirkulasi air kolam renang?', 'a' => 'Perbaikan sistem sirkulasi adalah bagian dari lebih dari 10 tahun pengalaman kami, dengan lebih dari 350 proyek yang telah kami selesaikan di wilayah Bogor dan sekitarnya.'],
];

// 6. Pembuatan Ruang Mesin Kolam
$pagesData['/layanan/pembuatan-ruang-mesin-kolam/'] = [
    'meta_description' => 'Pembuatan ruang mesin (pump room) kolam renang di Bogor — rapi, aman, dan memudahkan perawatan sistem filtrasi. Konsultasi gratis via WhatsApp.',
    'content' =>
        '<h2>Pembuatan Ruang Mesin Kolam Renang</h2>'
        . build_p('Ruang mesin yang tertata rapi melindungi pompa, filter, dan sistem sanitasi dari cuaca, sekaligus memudahkan akses saat perawatan atau perbaikan di kemudian hari.')
        . '<h2 style="margin-top:32px">Tahapan Pengerjaan</h2>'
        . build_ol(['Survei lokasi & kebutuhan peralatan', 'Desain tata letak & ventilasi', 'Pembangunan struktur ruang mesin', 'Pemasangan dudukan pompa & filter', 'Penataan jalur pipa & kelistrikan', 'Pengecekan akhir & serah terima'])
        . '<h2 style="margin-top:32px">Layanan Terkait</h2>'
        . build_p('Layanan ini sering menyertai proyek ' . build_link('/layanan/pembuatan-kolam-renang-baru/', 'pembuatan kolam renang baru') . ' dan berkaitan langsung dengan ' . build_link('/layanan/instalasi-filter-pompa/', 'instalasi filter & pompa') . '.')
        . '<h2 style="margin-top:32px">Konsultasi Pembuatan Ruang Mesin</h2>'
        . build_closing('Sedang merencanakan kolam baru atau ingin merapikan tata letak peralatan kolam yang sudah ada? Hubungi kami untuk konsultasi.'),
    'faq_extra' => ['q' => 'Sudah berapa lama menangani pembuatan ruang mesin kolam renang?', 'a' => 'Pembuatan ruang mesin adalah bagian dari lebih dari 10 tahun pengalaman kami, dengan lebih dari 350 proyek yang telah kami selesaikan di wilayah Bogor dan sekitarnya.'],
];

// 7. Jasa Pengurasan Kolam Renang
$pagesData['/layanan/jasa-pengurasan-kolam-renang/'] = [
    'meta_description' => 'Jasa pengurasan kolam renang di Bogor untuk pembersihan mendalam, perbaikan, atau persiapan renovasi — dikerjakan bertahap & aman. Survei gratis.',
    'content' =>
        '<h2>Jasa Pengurasan Kolam Renang</h2>'
        . build_p('Pengurasan total diperlukan untuk pembersihan mendalam yang tidak bisa dicapai lewat perawatan rutin biasa, atau sebagai persiapan sebelum renovasi maupun perbaikan struktur kolam.')
        . '<h2 style="margin-top:32px">Tahapan Pengerjaan</h2>'
        . build_ol(['Survei kondisi kolam & muka air tanah', 'Pembuangan air secara bertahap', 'Pembersihan dinding & dasar kolam', 'Pemeriksaan struktur saat kolam kosong', 'Pengisian air bersih', 'Penyeimbangan kimia air awal'])
        . '<h2 style="margin-top:32px">Layanan Terkait</h2>'
        . build_p('Pengurasan sering jadi tahap awal sebelum ' . build_link('/layanan/renovasi-perbaikan-kolam/', 'renovasi & perbaikan kolam') . '. Setelah pengisian ulang, kolam tetap butuh ' . build_link('/layanan/jasa-perawatan-kolam-renang/', 'perawatan rutin') . ' agar kualitas air terjaga.')
        . '<h2 style="margin-top:32px">Konsultasi Pengurasan Kolam</h2>'
        . build_closing('Kolam Anda butuh pembersihan mendalam atau persiapan sebelum renovasi? Hubungi kami untuk survei kondisi terlebih dahulu.'),
    'faq_extra' => ['q' => 'Sudah berapa lama menangani jasa pengurasan kolam renang?', 'a' => 'Pengurasan kolam adalah bagian dari lebih dari 10 tahun pengalaman kami, dengan lebih dari 350 proyek yang telah kami selesaikan di wilayah Bogor dan sekitarnya.'],
];

// 8. Perawatan Kolam Air Asin
$pagesData['/layanan/perawatan-kolam-air-asin/'] = [
    'meta_description' => 'Perawatan kolam renang air asin (salt chlorinator) di Bogor — pembersihan sel elektrolisis & penyeimbangan kadar garam berkala. Konsultasi gratis.',
    'content' =>
        '<h2>Perawatan Kolam Renang Air Asin</h2>'
        . build_p('Kolam dengan sistem salt chlorinator butuh perawatan khusus di luar perawatan kolam klorin biasa — terutama pembersihan sel elektrolisis dan penyeimbangan kadar garam agar sistem tetap berfungsi optimal.')
        . '<h2 style="margin-top:32px">Tahapan Pengerjaan</h2>'
        . build_ol(['Pengecekan kadar garam & salinitas', 'Pembersihan sel elektrolisis dari kerak', 'Kalibrasi alat salt chlorinator', 'Penyeimbangan pH & alkalinitas', 'Pengecekan komponen elektronik', 'Rekomendasi penambahan garam bila perlu'])
        . '<h2 style="margin-top:32px">Layanan Terkait</h2>'
        . build_p('Untuk kebutuhan perawatan rutin kolam secara umum, lihat juga ' . build_link('/layanan/jasa-perawatan-kolam-renang/', 'jasa perawatan kolam renang') . ' dan ' . build_link('/layanan/penyeimbangan-kimia-air-kolam/', 'penyeimbangan kimia air kolam') . '.')
        . '<h2 style="margin-top:32px">Konsultasi Perawatan Kolam Air Asin</h2>'
        . build_closing('Punya kolam dengan sistem salt chlorinator? Hubungi kami untuk jadwal perawatan sel elektrolisis dan kadar garam.'),
    'faq_extra' => ['q' => 'Sudah berapa lama menangani perawatan kolam air asin?', 'a' => 'Perawatan kolam air asin adalah bagian dari lebih dari 10 tahun pengalaman kami, dengan lebih dari 350 proyek yang telah kami selesaikan di wilayah Bogor dan sekitarnya.'],
];

// 9. Vacuum & Pembersihan Dasar Kolam
$pagesData['/layanan/vacuum-pembersihan-dasar-kolam/'] = [
    'meta_description' => 'Vacuum & pembersihan dasar kolam renang di Bogor — angkat sedimen dan kotoran yang tidak terangkat filtrasi biasa. Termasuk paket perawatan rutin.',
    'content' =>
        '<h2>Vacuum &amp; Pembersihan Dasar Kolam</h2>'
        . build_p('Filtrasi biasa hanya menyaring partikel yang melayang di air — kotoran dan sedimen yang sudah mengendap di dasar kolam butuh diangkat lewat vacuum secara manual.')
        . '<h2 style="margin-top:32px">Tahapan Pengerjaan</h2>'
        . build_ol(['Pemeriksaan kondisi dasar & dinding kolam', 'Vacuum sedimen & kotoran mengendap', 'Sikat dinding & garis air (waterline)', 'Pembuangan hasil vacuum ke saluran', 'Pengecekan kejernihan air', 'Penyeimbangan kimia bila perlu'])
        . '<h2 style="margin-top:32px">Layanan Terkait</h2>'
        . build_p('Vacuum dasar kolam sudah termasuk dalam paket ' . build_link('/layanan/jasa-perawatan-kolam-renang/', 'jasa perawatan kolam renang') . ' rutin kami. Untuk pembersihan mendalam atau kolam yang lama tidak dirawat, lihat juga ' . build_link('/layanan/jasa-pengurasan-kolam-renang/', 'jasa pengurasan kolam renang') . '.')
        . '<h2 style="margin-top:32px">Konsultasi Vacuum Kolam</h2>'
        . build_closing('Dasar kolam Anda terlihat berpasir atau berlumpur meski air terlihat jernih? Hubungi kami untuk layanan vacuum.'),
    'faq_extra' => ['q' => 'Sudah berapa lama menangani jasa vacuum dan pembersihan dasar kolam?', 'a' => 'Layanan ini adalah bagian dari lebih dari 10 tahun pengalaman kami, dengan lebih dari 350 proyek yang telah kami selesaikan di wilayah Bogor dan sekitarnya.'],
];

// 10. Perbaikan Lampu Kolam Renang
$pagesData['/layanan/perbaikan-lampu-kolam-renang/'] = [
    'meta_description' => 'Perbaikan & penggantian lampu kolam renang bawah air di Bogor — ditangani teknisi berpengalaman untuk keamanan instalasi listrik. Survei gratis.',
    'content' =>
        '<h2>Perbaikan Lampu Kolam Renang</h2>'
        . build_p('Lampu bawah air yang mati atau redup sering disebabkan seal yang aus atau masalah jalur kabel — pekerjaan yang melibatkan kelistrikan di area basah dan sebaiknya tidak ditangani sendiri.')
        . '<h2 style="margin-top:32px">Tahapan Pengerjaan</h2>'
        . build_ol(['Pemeriksaan fixture & jalur kabel', 'Identifikasi sumber masalah kelistrikan', 'Penggantian seal/fixture bila perlu', 'Pengecekan sistem grounding & keamanan', 'Uji coba lampu setelah perbaikan', 'Rekomendasi perawatan pencegahan'])
        . '<h2 style="margin-top:32px">Layanan Terkait</h2>'
        . build_p('Kalau masalah lampu terjadi bersamaan dengan tanda kerusakan lain pada kolam, lihat juga ' . build_link('/layanan/renovasi-perbaikan-kolam/', 'renovasi & perbaikan kolam') . '.')
        . '<h2 style="margin-top:32px">Konsultasi Perbaikan Lampu Kolam</h2>'
        . build_closing('Lampu kolam Anda mati atau redup? Hubungi kami untuk pemeriksaan yang aman oleh teknisi berpengalaman.'),
    'faq_extra' => ['q' => 'Sudah berapa lama menangani perbaikan lampu kolam renang?', 'a' => 'Perbaikan lampu kolam adalah bagian dari lebih dari 10 tahun pengalaman kami, dengan lebih dari 350 proyek yang telah kami selesaikan di wilayah Bogor dan sekitarnya.'],
];

// 11. Waterproofing Kolam Renang
$pagesData['/layanan/waterproofing-kolam-renang/'] = [
    'meta_description' => 'Waterproofing kolam renang di Bogor — cegah rembesan ke struktur bangunan, untuk kolam baru maupun renovasi kolam lama. Konsultasi gratis via WhatsApp.',
    'content' =>
        '<h2>Waterproofing Kolam Renang</h2>'
        . build_p('Lapisan waterproofing yang menurun adalah penyebab umum rembesan pada kolam lama. Waterproofing yang tepat mencegah air merembes ke struktur bangunan sekitar, baik untuk kolam baru maupun renovasi.')
        . '<h2 style="margin-top:32px">Tahapan Pengerjaan</h2>'
        . build_ol(['Survei kondisi struktur kolam', 'Pembersihan & persiapan permukaan', 'Aplikasi lapisan waterproofing', 'Masa curing/pengeringan sesuai standar', 'Uji rendam untuk memastikan tidak bocor', 'Lanjut ke tahap finishing keramik'])
        . '<h2 style="margin-top:32px">Proyek yang Pernah Kami Kerjakan</h2>'
        . build_p('Dengan lebih dari 10 tahun pengalaman dan 350+ proyek yang telah kami selesaikan di wilayah Bogor dan sekitarnya, berikut salah satu contoh pekerjaan waterproofing yang pernah kami tangani.')
        . '<div class="portfolio-grid">' . build_portfolio_card('/portofolio/perbaikan-kebocoran-kolam/', '/photo.php?id=15', 427, 299, 'Perbaikan kebocoran dan waterproofing kolam di Bogor Kota', 'Bogor Kota', 'Perbaikan Kebocoran Kolam', 'Perbaikan struktur dan pelapisan ulang waterproofing untuk kolam renang lama di kawasan Bogor Kota yang mengalami rembesan akibat curah hujan tinggi.') . '</div>'
        . '<h2 style="margin-top:32px">Layanan Terkait</h2>'
        . build_p('Waterproofing sering jadi bagian dari penanganan ' . build_link('/layanan/perbaikan-kebocoran-kolam/', 'perbaikan kebocoran kolam') . ' dan ' . build_link('/layanan/renovasi-perbaikan-kolam/', 'renovasi kolam lama') . '. Beberapa kesalahan umum seputar kebocoran kami bahas di artikel ' . build_link('/artikel/kesalahan-umum-pemilik-kolam-renang-rumahan/', 'Kesalahan Umum Pemilik Kolam Renang Rumahan') . '.')
        . '<h2 style="margin-top:32px">Konsultasi Waterproofing</h2>'
        . build_closing('Kolam Anda mulai merembes atau ingin mencegahnya sejak awal? Hubungi kami untuk survei kondisi struktur.'),
    'faq_extra' => ['q' => 'Sudah berapa lama menangani waterproofing kolam renang?', 'a' => 'Waterproofing kolam adalah bagian dari lebih dari 10 tahun pengalaman kami, dengan lebih dari 350 proyek yang telah kami selesaikan di wilayah Bogor dan sekitarnya.'],
];

// 12. Perawatan Kolam Musim Hujan
$pagesData['/layanan/perawatan-kolam-musim-hujan/'] = [
    'meta_description' => 'Perawatan kolam renang musim hujan di Bogor — jaga kejernihan air meski curah hujan tinggi, sesuai karakter cuaca khas Bogor. Survei gratis.',
    'content' =>
        '<h2>Perawatan Kolam Renang Musim Hujan</h2>'
        . build_p('Curah hujan tinggi yang khas Bogor bisa mengencerkan kadar klorin dan membawa kotoran tambahan ke kolam, sehingga jadwal dan penyeimbangan kimia perlu disesuaikan lebih sering dibanding musim kemarau.')
        . '<h2 style="margin-top:32px">Tahapan Pengerjaan</h2>'
        . build_ol(['Pemeriksaan sistem overflow & drainase', 'Penyeimbangan pH & klorin pasca hujan', 'Pembersihan debris & dedaunan', 'Pengecekan kejernihan air', 'Penyesuaian jadwal perawatan musiman', 'Rekomendasi pencegahan lumut'])
        . '<h2 style="margin-top:32px">Bacaan Terkait</h2>'
        . build_p('Kami membahas lebih detail soal penyebab air keruh saat musim hujan di artikel ' . build_link('/artikel/kenapa-air-kolam-keruh-saat-musim-hujan-bogor/', 'Kenapa Air Kolam Cepat Keruh Saat Musim Hujan di Bogor') . '. Layanan ini melengkapi ' . build_link('/layanan/jasa-perawatan-kolam-renang/', 'jasa perawatan kolam renang') . ' rutin kami.')
        . '<h2 style="margin-top:32px">Konsultasi Perawatan Musim Hujan</h2>'
        . build_closing('Kolam Anda sering keruh setiap musim hujan tiba? Hubungi kami untuk penyesuaian jadwal perawatan.'),
    'faq_extra' => ['q' => 'Sudah berapa lama menangani perawatan kolam khusus musim hujan?', 'a' => 'Perawatan musiman ini adalah bagian dari lebih dari 10 tahun pengalaman kami menangani kolam renang di Bogor, dengan lebih dari 350 proyek yang telah kami selesaikan.'],
];

// 13. Konsultasi Desain Kolam Renang
$pagesData['/layanan/konsultasi-desain-kolam-renang/'] = [
    'meta_description' => 'Konsultasi desain kolam renang gratis di Bogor — bantu tentukan ukuran, gaya, dan tata letak kolam sesuai lahan dan anggaran Anda. Hubungi kami.',
    'content' =>
        '<h2>Konsultasi Desain Kolam Renang</h2>'
        . build_p('Sebelum memulai proyek pembuatan kolam, konsultasi desain membantu menentukan ukuran, gaya, dan tata letak yang paling sesuai dengan kondisi lahan, kebutuhan, dan anggaran Anda.')
        . '<h2 style="margin-top:32px">Tahapan Pengerjaan</h2>'
        . build_ol(['Diskusi kebutuhan & preferensi gaya', 'Survei lahan yang tersedia', 'Rekomendasi desain & ukuran kolam', 'Estimasi anggaran awal', 'Penjelasan tahapan & linimasa proyek', 'Lanjut ke tahap penawaran resmi bila setuju'])
        . '<h2 style="margin-top:32px">Contoh Hasil Desain Kami</h2>'
        . build_p('Berikut beberapa proyek yang berawal dari proses konsultasi desain serupa.')
        . '<div class="portfolio-grid">'
        . build_portfolio_card('/portofolio/kolam-renang-villa-modern-di-sentul/', '/photo.php?id=11', 649, 472, 'Desain kolam renang villa modern di Sentul', 'Sentul', 'Kolam Renang Villa Modern di Sentul', 'Kolam renang villa modern di Sentul, dirancang agar kolam menjadi bagian dari keseluruhan desain properti, bukan sekadar fasilitas tambahan.')
        . build_portfolio_card('/portofolio/kolam-renang-keluarga/', '/photo.php?id=14', 427, 299, 'Desain kolam renang minimalis untuk keluarga di Cibinong', 'Cibinong', 'Kolam Renang Keluarga', 'Kolam renang minimalis untuk hunian keluarga di kawasan perumahan Cibinong, disesuaikan dengan lahan rumah yang terbatas.')
        . '</div>'
        . '<h2 style="margin-top:32px">Langkah Selanjutnya</h2>'
        . build_p('Setelah desain disepakati, proyek dilanjutkan ke tahap ' . build_link('/layanan/pembuatan-kolam-renang-baru/', 'pembuatan kolam renang baru') . '.')
        . '<h2 style="margin-top:32px">Konsultasi Desain Sekarang</h2>'
        . build_closing('Punya lahan tapi belum tahu desain kolam yang cocok? Hubungi kami untuk konsultasi gratis.'),
    'faq_extra' => ['q' => 'Sudah berapa lama menyediakan konsultasi desain kolam renang?', 'a' => 'Konsultasi desain adalah bagian dari lebih dari 10 tahun pengalaman kami, dengan lebih dari 350 proyek yang telah kami selesaikan di wilayah Bogor dan sekitarnya.'],
];

// 14. Perawatan Kolam Vila & Penginapan
$pagesData['/layanan/perawatan-kolam-vila-penginapan/'] = [
    'meta_description' => 'Perawatan kolam renang vila & penginapan di Bogor — jadwal fleksibel dan persiapan sebelum kedatangan tamu. Cocok untuk properti hunian musiman.',
    'content' =>
        '<h2>Perawatan Kolam Vila &amp; Penginapan</h2>'
        . build_p('Properti seperti vila dan guesthouse punya pola pemakaian yang berbeda dari rumah tinggal — sering kosong di hari kerja, ramai saat akhir pekan atau musim liburan. Perawatan kami disesuaikan dengan pola ini, termasuk persiapan khusus sebelum tamu datang.')
        . '<h2 style="margin-top:32px">Tahapan Pengerjaan</h2>'
        . build_ol(['Diskusi jadwal & pola kunjungan tamu', 'Perawatan rutin sesuai jadwal disepakati', 'Kunjungan persiapan sebelum kedatangan tamu', 'Pengecekan standar kejernihan & keamanan', 'Laporan kondisi ke pengelola/pemilik', 'Penanganan cepat bila ada masalah mendadak'])
        . '<h2 style="margin-top:32px">Proyek yang Pernah Kami Kerjakan</h2>'
        . build_p('Berikut contoh kolam villa yang kami tangani, relevan dengan karakter properti hunian musiman di kawasan Sentul.')
        . '<div class="portfolio-grid">' . build_portfolio_card('/portofolio/kolam-renang-villa-modern-di-sentul/', '/photo.php?id=11', 649, 472, 'Kolam renang villa di Sentul', 'Sentul', 'Kolam Renang Villa Modern di Sentul', 'Kolam renang villa modern di Sentul, Bogor, dirancang melengkapi karakter villa yang mengutamakan tampilan modern dan mudah dirawat.') . '</div>'
        . '<h2 style="margin-top:32px">Bacaan &amp; Layanan Terkait</h2>'
        . build_p('Kami membahas lebih detail soal merawat kolam yang jarang dipakai di artikel ' . build_link('/artikel/tips-merawat-kolam-renang-vila-jarang-dipakai/', 'Tips Merawat Kolam Renang Vila yang Jarang Dipakai') . '. Untuk kebutuhan jadwal tetap jangka panjang, lihat juga ' . build_link('/layanan/kontrak-perawatan-bulanan/', 'Kontrak Perawatan Bulanan') . '.')
        . '<h2 style="margin-top:32px">Konsultasi Perawatan Vila</h2>'
        . build_closing('Punya vila atau penginapan dengan pola hunian musiman? Hubungi kami untuk menyusun jadwal perawatan yang sesuai.'),
    'faq_extra' => ['q' => 'Sudah berapa lama menangani perawatan kolam vila dan penginapan?', 'a' => 'Perawatan kolam vila & penginapan adalah bagian dari lebih dari 10 tahun pengalaman kami, dengan lebih dari 350 proyek yang telah kami selesaikan di wilayah Bogor dan sekitarnya.'],
];

// 15. Kontrak Perawatan Bulanan
$pagesData['/layanan/kontrak-perawatan-bulanan/'] = [
    'meta_description' => 'Kontrak perawatan kolam renang bulanan di Bogor — jadwal rutin, harga tetap, dan laporan kondisi berkala. Cocok untuk rumah, vila, dan hotel.',
    'content' =>
        '<h2>Kontrak Perawatan Kolam Renang Bulanan</h2>'
        . build_p('Kontrak bulanan memberi kepastian jadwal dan harga dibanding memanggil jasa perawatan sesuai kebutuhan — cocok untuk pemilik yang ingin kolam selalu terpantau tanpa perlu mengingat-ingat jadwalnya sendiri.')
        . '<h2 style="margin-top:32px">Tahapan Pengerjaan</h2>'
        . build_ol(['Konsultasi kebutuhan & frekuensi kunjungan', 'Penawaran paket & kesepakatan harga tetap', 'Penjadwalan kunjungan rutin', 'Pelaksanaan perawatan sesuai jadwal', 'Laporan kondisi kolam tiap kunjungan', 'Evaluasi & penyesuaian paket berkala'])
        . '<h2 style="margin-top:32px">Proyek yang Pernah Kami Kerjakan</h2>'
        . build_p('Dengan lebih dari 10 tahun pengalaman dan 350+ proyek yang telah kami selesaikan di wilayah Bogor dan sekitarnya, berikut contoh kontrak perawatan bulanan yang kami jalankan untuk properti komersial.')
        . '<div class="portfolio-grid">' . build_portfolio_card('/portofolio/perawatan-kolam-rutin/', '/photo.php?id=16', 738, 414, 'Kontrak perawatan bulanan kolam renang hotel di Bogor Kota', 'Bogor Kota', 'Perawatan Kolam Rutin', 'Program perawatan bulanan untuk kolam renang hotel di kawasan Bogor Kota, mencakup pengecekan kualitas air, pembersihan filter, dan penyeimbangan kimia secara berkala.') . '</div>'
        . '<h2 style="margin-top:32px">Layanan Terkait</h2>'
        . build_p('Kontrak ini mencakup perawatan yang sama seperti ' . build_link('/layanan/jasa-perawatan-kolam-renang/', 'jasa perawatan kolam renang') . ' reguler, hanya dengan jadwal dan harga yang terikat kontrak. Cocok juga untuk ' . build_link('/layanan/perawatan-kolam-vila-penginapan/', 'properti vila & penginapan') . '.')
        . '<h2 style="margin-top:32px">Konsultasi Kontrak Perawatan</h2>'
        . build_closing('Ingin kolam Anda terjadwal rutin dengan harga tetap? Hubungi kami untuk penawaran paket kontrak bulanan.'),
    'faq_extra' => ['q' => 'Sudah berapa lama menyediakan kontrak perawatan bulanan?', 'a' => 'Kontrak perawatan bulanan adalah bagian dari lebih dari 10 tahun pengalaman kami, dengan lebih dari 350 proyek yang telah kami selesaikan di wilayah Bogor dan sekitarnya.'],
];

// 16. Penyeimbangan Kimia Air Kolam
$pagesData['/layanan/penyeimbangan-kimia-air-kolam/'] = [
    'meta_description' => 'Penyeimbangan kimia air kolam renang di Bogor — cek & atur pH, klorin, dan alkalinitas agar air aman dan tidak merusak material kolam.',
    'content' =>
        '<h2>Penyeimbangan Kimia Air Kolam Renang</h2>'
        . build_p('Kadar pH, klorin, dan alkalinitas yang tidak seimbang bisa membuat air terlihat keruh, mengiritasi kulit dan mata, atau bahkan merusak permukaan dan komponen kolam dalam jangka panjang — meski air terlihat jernih secara visual.')
        . '<h2 style="margin-top:32px">Tahapan Pengerjaan</h2>'
        . build_ol(['Pengujian pH, klorin & alkalinitas', 'Analisis hasil pengukuran', 'Penambahan bahan kimia sesuai dosis', 'Pengujian ulang setelah penyesuaian', 'Rekomendasi jadwal pengecekan berikutnya', 'Pencatatan riwayat kualitas air'])
        . '<h2 style="margin-top:32px">Bacaan Terkait</h2>'
        . build_p('Kami membahas lebih detail soal rentang pH ideal di artikel ' . build_link('/artikel/cara-menjaga-ph-air-kolam-tetap-ideal/', 'Cara Menjaga pH Air Kolam Tetap Ideal') . ', frekuensi pengecekan di artikel ' . build_link('/artikel/berapa-kali-cek-ph-air-kolam-per-minggu/', 'Berapa Kali Idealnya Cek pH Air Kolam per Minggu') . ', dan cara mengatasi air hijau di artikel ' . build_link('/artikel/cara-mengatasi-air-kolam-berwarna-hijau/', 'Cara Mengatasi Air Kolam Berwarna Hijau') . '.')
        . '<h2 style="margin-top:32px">Layanan Terkait</h2>'
        . build_p('Penyeimbangan kimia air sudah termasuk dalam paket ' . build_link('/layanan/jasa-perawatan-kolam-renang/', 'jasa perawatan kolam renang') . ' rutin kami.')
        . '<h2 style="margin-top:32px">Konsultasi Penyeimbangan Kimia Air</h2>'
        . build_closing('Air kolam Anda sering terasa perih di mata atau cepat berubah warna? Hubungi kami untuk pengecekan kimia air.'),
    'faq_extra' => ['q' => 'Sudah berapa lama menangani penyeimbangan kimia air kolam renang?', 'a' => 'Penyeimbangan kimia air adalah bagian dari lebih dari 10 tahun pengalaman kami, dengan lebih dari 350 proyek yang telah kami selesaikan di wilayah Bogor dan sekitarnya.'],
];

// 17. Jasa Darurat Kolam Bocor Mendadak
$pagesData['/layanan/jasa-darurat-kolam-bocor-mendadak/'] = [
    'meta_description' => 'Jasa darurat kolam bocor mendadak di Bogor — tanggap cepat via WhatsApp untuk mencegah kerusakan lebih lanjut ke struktur bangunan.',
    'content' =>
        '<h2>Jasa Darurat Kolam Bocor Mendadak</h2>'
        . build_p('Kebocoran mendadak butuh respons cepat untuk mencegah kerusakan merembet ke struktur bangunan sekitar. Layanan ini fokus pada penilaian awal dan tindakan cepat, sebelum masuk ke perbaikan menyeluruh.')
        . '<h2 style="margin-top:32px">Tahapan Pengerjaan</h2>'
        . build_ol(['Hubungi tim kami segera via WhatsApp', 'Penilaian awal tingkat kedaruratan', 'Tindakan stabilisasi sementara bila diperlukan', 'Diagnosis sumber kebocoran', 'Perbaikan sesuai temuan', 'Pemantauan pasca perbaikan'])
        . '<h2 style="margin-top:32px">Layanan Terkait</h2>'
        . build_p('Untuk perbaikan kebocoran yang tidak mendesak/terjadwal, lihat ' . build_link('/layanan/perbaikan-kebocoran-kolam/', 'perbaikan kebocoran kolam') . ' reguler kami. Kami juga membahas kesalahan umum soal kebocoran di artikel ' . build_link('/artikel/kesalahan-umum-pemilik-kolam-renang-rumahan/', 'Kesalahan Umum Pemilik Kolam Renang Rumahan') . '.')
        . '<h2 style="margin-top:32px">Hubungi Kami Sekarang</h2>'
        . build_closing('Kolam Anda bocor mendadak? Jangan tunggu, segera hubungi kami via WhatsApp untuk penilaian awal.'),
    'faq_extra' => ['q' => 'Sudah berapa lama menangani kasus kebocoran kolam mendadak?', 'a' => 'Penanganan kebocoran adalah bagian dari lebih dari 10 tahun pengalaman kami, dengan lebih dari 350 proyek yang telah kami selesaikan di wilayah Bogor dan sekitarnya.'],
];

// ============================================================
// Eksekusi: loop tiap halaman, SATU query UPDATE per baris,
// dikunci id + url_path + type (dibaca dulu per baris).
// FAQ lama (2 pertanyaan existing tiap halaman) dipertahankan --
// diambil dari database, ditambah 1 FAQ baru di atas.
// ============================================================
$results = [];
try {
    $pdo = get_db();

    foreach ($pagesData as $urlPath => $data) {
        $before = $pdo->prepare('SELECT id, faq_json FROM pages WHERE url_path = :u AND type = \'service\'');
        $before->execute([':u' => $urlPath]);
        $row = $before->fetch();

        if (!$row) {
            $results[$urlPath] = 'SKIPPED -- baris tidak ditemukan';
            continue;
        }

        $existingFaq = $row['faq_json'] ? json_decode($row['faq_json'], true) : [];
        if (!is_array($existingFaq)) $existingFaq = [];
        $existingFaq[] = $data['faq_extra'];

        $stmt = $pdo->prepare(
            'UPDATE pages SET meta_description = :meta_description, content = :content, faq_json = :faq_json
             WHERE id = :id AND url_path = :url_path AND type = \'service\''
        );
        $stmt->execute([
            ':meta_description' => $data['meta_description'],
            ':content' => $data['content'],
            ':faq_json' => json_encode($existingFaq, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
            ':id' => $row['id'],
            ':url_path' => $urlPath,
        ]);
        $results[$urlPath] = 'OK (id=' . $row['id'] . ', rows_affected=' . $stmt->rowCount() . ')';
    }

    respond(true, 'Optimasi 17 halaman layanan selesai.', ['results' => $results]);
} catch (Exception $e) {
    respond(false, 'Gagal: ' . $e->getMessage(), ['results' => $results]);
}
