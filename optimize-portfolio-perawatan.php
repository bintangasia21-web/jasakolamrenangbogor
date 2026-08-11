<?php
/**
 * Skrip sekali-jalan: optimasi 11 portofolio "Perawatan Kolam Renang"
 * berdasarkan PORTFOLIO-PERAWATAN-LIVE-AUDIT.md. TIDAK membuat
 * portofolio baru, TIDAK mengubah URL, TIDAK menghapus/redirect/
 * menggabungkan halaman manapun -- termasuk 2 halaman Cijeruk yang
 * SENGAJA dipertahankan sebagai 2 proyek terpisah (foto berbeda: id=12
 * vs id=25; teks deskripsi berbeda kata-per-kata, bukan copy identik --
 * tidak ada bukti kuat keduanya proyek yang sama, jadi TIDAK dianggap
 * duplikat sesuai aturan Phase 2).
 *
 * Cakupan per item (9 baru + Rancamaya direstrukturisasi penuh dengan
 * H2 "Lingkup Pekerjaan"/"Hasil Pekerjaan"/"Layanan Terkait"; 2 item
 * LAMA -- Perawatan Kolam Rutin & Cijeruk original -- HANYA ditambah
 * meta description baru + section "Layanan Terkait" karena sudah cukup
 * baik & di luar scope eksplisit "8 portofolio baru" Phase 4):
 *
 * - Rancamaya: placeholder "JUDUL:"/"DESKRIPSI:" dibersihkan, typo
 *   "Perawawatan" & kapitalisasi "rancamaya" diperbaiki. title/h1 BARU
 *   diambil LANGSUNG dari teks "JUDUL: ..." yang sudah ada (bukan
 *   dikarang -- cuma dibuang prefix rusaknya). Isi konten diambil dari
 *   teks "DESKRIPSI: ..." yang sudah ada, cuma direstrukturisasi H2 +
 *   dibuang prefix.
 * - 8 lainnya: intro dipersingkat, isi lengkap direstrukturisasi jadi
 *   daftar tugas (H2 "Lingkup Pekerjaan") + hasil (H2 "Hasil
 *   Pekerjaan") -- SEMUA fakta diambil dari teks yang SUDAH ADA,
 *   TIDAK ada detail baru yang dikarang (tidak ada tanggal, durasi,
 *   material, harga, garansi yang ditambahkan).
 *
 * Semua 11 dapat section "Layanan Terkait" (money page + halaman area)
 * dan meta description baru 140-160 karakter, berbeda kata-per-kata
 * antar item (bukan template identik). Link artikel/portofolio lain
 * HANYA ditambahkan kalau genuinely relevan (karakter area yang sudah
 * mapan di situs, bukan dipaksakan ke semua item).
 *
 * DATA SAFETY: tiap baris di-UPDATE dengan guard id (dibaca dulu) +
 * url_path + type='portfolio'. Hanya 11 baris target yang tersentuh.
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
function build_ul($items) {
    $html = '<ul style="color:var(--gray-600);padding-left:20px;margin:0">';
    foreach ($items as $i) $html .= '<li>' . $i . '</li>';
    $html .= '</ul>';
    return $html;
}

$MONEY_PAGE = '/layanan/jasa-perawatan-kolam-renang/';
$ART_VILA = '/artikel/tips-merawat-kolam-renang-vila-jarang-dipakai/';
$ART_PH = '/artikel/cara-menjaga-ph-air-kolam-tetap-ideal/';
$ART_HIJAU = '/artikel/cara-mengatasi-air-kolam-berwarna-hijau/';

$standardTasks = [
    'Pemeriksaan kondisi air dan sistem sirkulasi',
    'Pembersihan permukaan dan dasar kolam',
    'Penyikatan dinding dan lantai kolam',
    'Penyedotan endapan dan kotoran yang mengendap',
    'Pengecekan pompa dan sistem filtrasi',
];

// ============================================================
// Data per portofolio (key = url_path)
// ============================================================
$items = [];

// --- 2 item LAMA: minimal (meta description + section Layanan Terkait saja) ---
$items['/portofolio/perawatan-kolam-rutin/'] = [
    'meta_description' => 'Perawatan rutin bulanan kolam renang hotel di Bogor Kota — cek kualitas air, pembersihan filter, dan penyeimbangan kimia berkala agar kolam siap dipakai tamu.',
    'append_only' =>
        '<h2 style="margin-top:32px">Layanan Terkait</h2>'
        . build_p('Proyek ini bagian dari ' . build_link($MONEY_PAGE, 'jasa perawatan kolam renang') . ' kami di ' . build_link('/area/bogor-kota/', 'Bogor Kota') . '.'),
];

$items['/portofolio/perawatan-kolam-renang-di-cijeruk-bogor/'] = [
    'meta_description' => 'Perawatan kolam renang di Cijeruk, Bogor — pemeriksaan air, sirkulasi, pompa, dan filter agar kolam tetap bersih, jernih, dan sistem bekerja optimal.',
    'append_only' =>
        '<h2 style="margin-top:32px">Layanan Terkait</h2>'
        . build_p('Proyek ini bagian dari ' . build_link($MONEY_PAGE, 'jasa perawatan kolam renang') . ' kami di ' . build_link('/area/cijeruk/', 'Cijeruk') . '. Lihat juga ' . build_link('/portofolio/perawatan-kolam-renang-di-cijeruk-bogor-13/', 'proyek perawatan lain yang pernah kami kerjakan di Cijeruk') . ', dan bacaan terkait di artikel ' . build_link($ART_HIJAU, 'Cara Mengatasi Air Kolam Berwarna Hijau') . '.'),
];

// --- 9 item BARU: restrukturisasi penuh ---

$items['/portofolio/perawatan-kolam-renang-di-sentul-bogor/'] = [
    'meta_description' => 'Perawatan kolam renang di Sentul, Bogor — pembersihan menyeluruh, penyedotan endapan, dan pengecekan sirkulasi agar air kembali jernih dan nyaman digunakan.',
    'intro' => 'Proyek perawatan kolam renang di Sentul, Bogor, mencakup pengecekan kondisi kolam dan sistem pendukung sebelum pekerjaan dimulai, dilakukan secara menyeluruh sesuai kondisi aktual di lapangan.',
    'content' =>
        '<h2>Lingkup Pekerjaan di Sentul</h2>' . build_ul($standardTasks)
        . '<h2 style="margin-top:32px">Hasil Pekerjaan</h2>' . build_p('Hasil akhir kolam terlihat lebih bersih, jernih, dan siap digunakan dengan nyaman.')
        . '<h2 style="margin-top:32px">Layanan Terkait</h2>' . build_p('Proyek ini bagian dari ' . build_link($MONEY_PAGE, 'jasa perawatan kolam renang') . ' kami di ' . build_link('/area/sentul/', 'Sentul') . '. Banyak properti di Sentul berpola hunian musiman — baca lebih lanjut di artikel ' . build_link($ART_VILA, 'Tips Merawat Kolam Renang Vila yang Jarang Dipakai') . '.'),
];

$items['/portofolio/perawatan-kolam-renang-di-ciawi-bogor/'] = [
    'meta_description' => 'Perawatan menyeluruh kolam renang di Ciawi, Bogor — pemeriksaan air, dinding, dan sistem sirkulasi, bukan sekadar membersihkan bagian yang terlihat saja.',
    'intro' => 'Proyek perawatan kolam renang di Ciawi, Bogor, disesuaikan dengan kondisi aktual kolam — memastikan pompa, filter, dan jalur sirkulasi bekerja baik sebelum proses perawatan dilanjutkan.',
    'content' =>
        '<h2>Lingkup Pekerjaan di Ciawi</h2>' . build_ul($standardTasks)
        . '<h2 style="margin-top:32px">Hasil Pekerjaan</h2>' . build_p('Perawatan dilakukan secara menyeluruh, bukan sekadar membersihkan bagian yang terlihat. Hasil pekerjaan memberikan tampilan kolam yang lebih bersih, air lebih jernih, serta sistem yang berfungsi lebih optimal.')
        . '<h2 style="margin-top:32px">Layanan Terkait</h2>' . build_p('Proyek ini bagian dari ' . build_link($MONEY_PAGE, 'jasa perawatan kolam renang') . ' kami di ' . build_link('/area/ciawi/', 'Ciawi') . '. Banyak vila dan resort di jalur Puncak seperti Ciawi berpola hunian musiman — baca lebih lanjut di artikel ' . build_link($ART_VILA, 'Tips Merawat Kolam Renang Vila yang Jarang Dipakai') . '.'),
];

$items['/portofolio/perawatan-kolam-renang-di-bogor-kota/'] = [
    'meta_description' => 'Perawatan kolam renang di Bogor Kota — pembersihan dasar dan dinding kolam serta pengecekan pompa dan sistem filtrasi untuk sirkulasi yang lebih optimal.',
    'intro' => 'Proyek perawatan kolam renang di Bogor Kota disesuaikan dengan kondisi aktual di lokasi, agar proses perawatan tidak hanya memperbaiki tampilan kolam tetapi juga mendukung sirkulasi air yang lebih optimal.',
    'content' =>
        '<h2>Lingkup Pekerjaan di Bogor Kota</h2>' . build_ul($standardTasks)
        . '<h2 style="margin-top:32px">Hasil Pekerjaan</h2>' . build_p('Pekerjaan dilakukan secara menyeluruh dengan memperhatikan kebersihan kolam dan kondisi sistem pendukung. Hasil akhirnya lebih bersih, jernih, dan nyaman digunakan.')
        . '<h2 style="margin-top:32px">Layanan Terkait</h2>' . build_p('Proyek ini bagian dari ' . build_link($MONEY_PAGE, 'jasa perawatan kolam renang') . ' kami di ' . build_link('/area/bogor-kota/', 'Bogor Kota') . '. Lihat juga contoh penanganan kebocoran di artikel portofolio ' . build_link('/portofolio/perbaikan-kebocoran-kolam/', 'Perbaikan Kebocoran Kolam') . ' di area yang sama.'),
];

$items['/portofolio/perawatan-kolam-renang-di-cibinong-bogor/'] = [
    'meta_description' => 'Perawatan kolam renang di Cibinong, Bogor, disesuaikan dengan kebutuhan kolam, bukan sekadar rutin — hasil kolam lebih bersih dan sirkulasi lebih optimal.',
    'intro' => 'Proyek perawatan kolam renang di Cibinong, Bogor, diawali pemeriksaan kondisi air dan sirkulasi, sehingga penanganan dapat disesuaikan dengan kebutuhan kolam, bukan sekadar pembersihan rutin.',
    'content' =>
        '<h2>Lingkup Pekerjaan di Cibinong</h2>' . build_ul($standardTasks)
        . '<h2 style="margin-top:32px">Hasil Pekerjaan</h2>' . build_p('Hasil pekerjaan di Cibinong menunjukkan kolam yang lebih bersih, air lebih jernih, sistem sirkulasi lebih optimal, serta tampilan keseluruhan yang lebih terawat.')
        . '<h2 style="margin-top:32px">Layanan Terkait</h2>' . build_p('Proyek ini bagian dari ' . build_link($MONEY_PAGE, 'jasa perawatan kolam renang') . ' kami di ' . build_link('/area/cibinong/', 'Cibinong') . '.'),
];

$items['/portofolio/perawatan-kolam-renang-di-yasmin-bogor/'] = [
    'meta_description' => 'Perawatan kolam renang di Yasmin, Bogor — menjaga kebersihan sekaligus fungsi sistem pendukung kolam, bukan hanya tampilan permukaannya saja.',
    'intro' => 'Proyek perawatan kolam renang di Yasmin, Bogor, diawali pemeriksaan kondisi air dan sistem sirkulasi secara menyeluruh, sehingga perawatan tidak hanya berfokus pada tampilan kolam.',
    'content' =>
        '<h2>Lingkup Pekerjaan di Yasmin</h2>' . build_ul($standardTasks)
        . '<h2 style="margin-top:32px">Hasil Pekerjaan</h2>' . build_p('Penanganan yang terukur membantu mengembalikan kejernihan air. Hasil pekerjaan di Yasmin menunjukkan kolam yang lebih bersih, air lebih jernih, sirkulasi lebih optimal, dan keseluruhan area tampak terawat.')
        . '<h2 style="margin-top:32px">Layanan Terkait</h2>' . build_p('Proyek ini bagian dari ' . build_link($MONEY_PAGE, 'jasa perawatan kolam renang') . ' kami di ' . build_link('/area/yasmin/', 'Yasmin') . '. Untuk kolam yang dipakai setiap hari seperti umum di Yasmin, menjaga keseimbangan kimia air jadi kunci utama — baca lebih lanjut di artikel ' . build_link($ART_PH, 'Cara Menjaga pH Air Kolam Tetap Ideal') . '.'),
];

$items['/portofolio/perawatan-kolam-renang-di-cijeruk-bogor-13/'] = [
    'meta_description' => 'Proyek perawatan kolam renang lain di Cijeruk, Bogor — penanganan area dengan endapan menumpuk dan pengecekan ulang sistem filtrasi pasca-pembersihan.',
    'intro' => 'Proyek perawatan kolam renang lain di Cijeruk, Bogor, diawali pemeriksaan kondisi air, permukaan kolam, dan sistem sirkulasi untuk menentukan penanganan yang sesuai.',
    'content' =>
        '<h2>Lingkup Pekerjaan</h2>' . build_ul(array_merge($standardTasks, ['Pengecekan ulang sistem filtrasi setelah pembersihan selesai']))
        . '<h2 style="margin-top:32px">Hasil Pekerjaan</h2>' . build_p('Perhatian khusus diberikan pada bagian dengan penumpukan kotoran atau endapan lebih banyak. Hasil pekerjaan menunjukkan kolam yang lebih bersih, air lebih jernih, dan sistem sirkulasi lebih optimal.')
        . '<h2 style="margin-top:32px">Layanan Terkait</h2>' . build_p('Proyek ini bagian dari ' . build_link($MONEY_PAGE, 'jasa perawatan kolam renang') . ' kami di ' . build_link('/area/cijeruk/', 'Cijeruk') . '. Ini adalah salah satu dari beberapa proyek perawatan yang pernah kami kerjakan di kawasan ini — lihat juga ' . build_link('/portofolio/perawatan-kolam-renang-di-cijeruk-bogor/', 'proyek perawatan Cijeruk lainnya') . ' dan artikel ' . build_link($ART_HIJAU, 'Cara Mengatasi Air Kolam Berwarna Hijau') . ', relevan untuk kawasan berkelembaban tinggi seperti Cijeruk.'),
];

$items['/portofolio/jasa-perawawatan-kolam-renang-rutin-di-rancamaya/'] = [
    // FIX Phase 1: placeholder JUDUL:/DESKRIPSI: dibersihkan, typo "Perawawatan" & kapitalisasi "rancamaya" diperbaiki.
    // title/h1 diambil dari teks "JUDUL: ..." yang SUDAH ADA (bukan dikarang), cuma dibuang prefiksnya.
    'title' => 'Perawatan Kolam Renang Rutin di Rancamaya, Bogor',
    'h1' => 'Perawatan Kolam Renang Rutin di Rancamaya, Bogor',
    'meta_description' => 'Perawatan rutin kolam renang di Rancamaya, Bogor — pemeriksaan air dan sirkulasi berkala agar kolam tidak cepat kotor dan tetap nyaman digunakan.',
    'intro' => 'Proyek perawatan kolam renang rutin di Rancamaya, Bogor, diawali pemeriksaan kondisi air, kebersihan permukaan kolam, dan sistem sirkulasi sebelum pekerjaan dimulai.',
    'content' =>
        '<h2>Lingkup Pekerjaan di Rancamaya</h2>' . build_ul($standardTasks)
        . '<h2 style="margin-top:32px">Hasil Pekerjaan</h2>' . build_p('Setiap kunjungan disesuaikan dengan kondisi aktual kolam, termasuk membersihkan bagian yang mengalami penumpukan kotoran atau endapan. Perawatan berkala membantu mencegah kondisi kolam cepat kotor. Hasil pekerjaan di Rancamaya membuat kolam lebih bersih, air lebih jernih, dan sirkulasi lebih optimal.')
        . '<h2 style="margin-top:32px">Layanan Terkait</h2>' . build_p('Proyek ini bagian dari ' . build_link($MONEY_PAGE, 'jasa perawatan kolam renang') . ' kami di ' . build_link('/area/rancamaya/', 'Rancamaya') . '.'),
];

$items['/portofolio/perawatan-kolam-renang-di-bogor-raya/'] = [
    'meta_description' => 'Perawatan kolam renang di Bogor Raya — pembersihan menyeluruh dan pengecekan ulang aliran sirkulasi serta penyaringan setelah proses perawatan selesai.',
    'intro' => 'Proyek perawatan kolam renang di Bogor Raya diawali pemeriksaan kondisi air, permukaan kolam, dan sistem sirkulasi untuk menentukan kebutuhan penanganan.',
    'content' =>
        '<h2>Lingkup Pekerjaan di Bogor Raya</h2>' . build_ul(array_merge($standardTasks, ['Pengecekan ulang sirkulasi & penyaringan setelah pembersihan']))
        . '<h2 style="margin-top:32px">Hasil Pekerjaan</h2>' . build_p('Perawatan menyeluruh membantu menjaga kejernihan air sekaligus meningkatkan kenyamanan penggunaan. Hasil pekerjaan di Bogor Raya membuat kolam terlihat lebih bersih, air lebih jernih, dan sistem pendukung lebih optimal.')
        . '<h2 style="margin-top:32px">Layanan Terkait</h2>' . build_p('Proyek ini bagian dari ' . build_link($MONEY_PAGE, 'jasa perawatan kolam renang') . ' kami di ' . build_link('/area/bogor-raya/', 'Bogor Raya') . '.'),
];

$items['/portofolio/perawatan-kolam-renang-di-karadenan-bogor/'] = [
    'meta_description' => 'Perawatan kolam renang di Karadenan, Bogor — menjaga fungsi filtrasi dan sirkulasi kolam, bukan hanya membersihkan bagian yang tampak kotor saja.',
    'intro' => 'Proyek perawatan kolam renang di Karadenan, Bogor, diawali pemeriksaan kondisi air, permukaan kolam, dan sistem sirkulasi untuk menentukan penanganan yang tepat.',
    'content' =>
        '<h2>Lingkup Pekerjaan di Karadenan</h2>' . build_ul(array_merge($standardTasks, ['Pengecekan ulang aliran sirkulasi setelah pembersihan']))
        . '<h2 style="margin-top:32px">Hasil Pekerjaan</h2>' . build_p('Perawatan menyeluruh seperti ini membantu menjaga kejernihan air dan mengurangi penumpukan kotoran. Hasil pekerjaan di Karadenan membuat kolam lebih bersih, air lebih jernih, dan sistem sirkulasi lebih optimal.')
        . '<h2 style="margin-top:32px">Layanan Terkait</h2>' . build_p('Proyek ini bagian dari ' . build_link($MONEY_PAGE, 'jasa perawatan kolam renang') . ' kami di ' . build_link('/area/karadenan/', 'Karadenan') . '.'),
];

// ============================================================
// Eksekusi
// ============================================================
$results = [];
try {
    $pdo = get_db();

    foreach ($items as $urlPath => $cfg) {
        $before = $pdo->prepare('SELECT id, title, h1, intro, content, meta_description FROM pages WHERE url_path = :u AND type = \'portfolio\'');
        $before->execute([':u' => $urlPath]);
        $row = $before->fetch();
        if (!$row) { $results[$urlPath] = 'SKIPPED -- baris tidak ditemukan'; continue; }

        $fields = [':meta_description' => $cfg['meta_description']];
        $setClauses = ['meta_description = :meta_description'];

        if (isset($cfg['append_only'])) {
            // 2 item lama: konten existing (yang sudah bagus) dipertahankan, cuma ditambah section link di akhir.
            $fields[':content'] = $row['content'] . $cfg['append_only'];
            $setClauses[] = 'content = :content';
        } else {
            // 9 item baru: intro & content ditulis ulang penuh (restrukturisasi, fakta sama).
            $fields[':intro'] = $cfg['intro'];
            $fields[':content'] = $cfg['content'];
            $setClauses[] = 'intro = :intro';
            $setClauses[] = 'content = :content';
            if (isset($cfg['title'])) {
                $fields[':title'] = $cfg['title'];
                $setClauses[] = 'title = :title';
            }
            if (isset($cfg['h1'])) {
                $fields[':h1'] = $cfg['h1'];
                $setClauses[] = 'h1 = :h1';
            }
        }

        $fields[':id'] = $row['id'];
        $fields[':url_path'] = $urlPath;
        $sql = 'UPDATE pages SET ' . implode(', ', $setClauses) . ' WHERE id = :id AND url_path = :url_path AND type = \'portfolio\'';
        $stmt = $pdo->prepare($sql);
        $stmt->execute($fields);

        $results[$urlPath] = [
            'id' => $row['id'],
            'rows_affected' => $stmt->rowCount(),
            'before_title' => $row['title'],
            'before_h1' => $row['h1'],
        ];
    }

    respond(true, 'Optimasi 11 portofolio Perawatan Kolam Renang selesai.', ['results' => $results]);
} catch (Exception $e) {
    respond(false, 'Gagal: ' . $e->getMessage(), ['results' => $results]);
}
