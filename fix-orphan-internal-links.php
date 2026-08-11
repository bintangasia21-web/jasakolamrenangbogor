<?php
/**
 * Skrip sekali-jalan: perbaiki discovery 40 orphan page yang ditemukan
 * di INDEXING-CRAWL-AUDIT.md (36 combo area x layanan + 4 portofolio),
 * dengan menambahkan internal link dari halaman yang genuinely relevan.
 *
 * TIDAK membuat halaman baru, TIDAK mengubah URL/slug/canonical/sitemap/
 * robots.txt/schema, TIDAK menghapus apa pun, TIDAK redirect. Hanya
 * APPEND/str_replace presisi pada field `content` milik 9 halaman area
 * (type='area') dan 2 halaman layanan (type='service') yang SUDAH ADA.
 *
 * === 36 combo pages (9 area x 4 layanan; Puncak tidak punya combo,
 * dikonfirmasi 404 live, jadi TIDAK disentuh) ===
 * Tiap halaman area dapat 1 blok baru berisi 4 link ke combo page-nya
 * sendiri, dengan kalimat pembuka yang memakai fakta karakteristik area
 * yang SUDAH dipublikasikan di halaman itu sendiri (bukan karangan baru).
 *
 * === 4 portofolio orphan ===
 * - kolam-renang-villa-modern/  : area TIDAK disebut eksplisit ("villa
 *   di kawasan perbukitan" - bisa Sentul/Puncak/Ciawi, tidak pasti) ->
 *   DATA_REQUIRED untuk area. Service (pembuatan) eksplisit -> link
 *   ditambahkan dari /layanan/pembuatan-kolam-renang-baru/ saja.
 * - perawatan-kolam-renang-di-area-belum-dipilih/ : konten literally
 *   berisi placeholder "(area belum dipilih)" yang belum diisi admin ->
 *   DATA_REQUIRED, TIDAK ditambah link sama sekali (bukan proyek nyata
 *   yang bisa diverifikasi, sesuai instruksi "jangan ubah data proyek").
 * - perawatan-kolam-renang-di-cijeruk/ : konten berisi catatan internal
 *   admin yang ter-paste tidak sengaja (bukan deskripsi proyek) ->
 *   DATA_REQUIRED, TIDAK ditambah link sama sekali.
 * - renovasi-kolam-resort/ : deskripsi menyebut "tahan cuaca dingin" -
 *   cocok dengan karakteristik Puncak yang SUDAH dipublikasikan di
 *   /area/puncak/ ("suhu udara yang lebih dingin", "curah hujan
 *   tinggi"). Service (renovasi) eksplisit. Link ditambahkan dari
 *   /area/puncak/ DAN /layanan/renovasi-perbaikan-kolam/.
 *
 * DATA SAFETY: tiap UPDATE dikunci id (SELECT dulu) + url_path +
 * type. Sebelum UPDATE, isi `content` lama disertakan penuh di response
 * JSON (backup). Setelah UPDATE, script memverifikasi setiap link
 * target benar-benar ada di content baru dan rowCount() sesuai target.
 * Link yang SUDAH ada di content sebelumnya (dicek via strpos) TIDAK
 * diduplikasi -> ditandai ALREADY_LINKED di response.
 */
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/inc/db.php';

function respond($success, $message, $extra = []) {
    echo json_encode(array_merge(['success' => $success, 'message' => $message], $extra), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}

$SERVICES = [
    'pembuatan-kolam-renang-baru' => 'Pembuatan Kolam Renang Baru',
    'perawatan-pembersihan-rutin' => 'Perawatan & Pembersihan Rutin',
    'renovasi-perbaikan-kolam' => 'Renovasi & Perbaikan Kolam',
    'instalasi-filter-pompa' => 'Instalasi Filter & Pompa',
];

// url_path area => [nama tampilan, kalimat konteks (fakta yang sudah live di halaman itu)]
$AREAS = [
    '/area/sentul/' => ['Sentul', 'Setiap layanan berikut sudah kami sesuaikan dengan kontur lahan berbukit dan karakter villa & resort di Sentul:'],
    '/area/ciawi/' => ['Ciawi', 'Empat layanan berikut kami sesuaikan dengan kondisi akses dan jadwal kunjungan di jalur Ciawi menuju Puncak:'],
    '/area/bogor-kota/' => ['Bogor Kota', 'Layanan berikut kami sesuaikan dengan kepadatan permukiman dan kebutuhan properti di Bogor Kota:'],
    '/area/cibinong/' => ['Cibinong', 'Layanan berikut kami sesuaikan dengan karakter permukiman dan perkantoran yang padat di Cibinong:'],
    '/area/yasmin/' => ['Yasmin', 'Layanan berikut kami sesuaikan dengan kebutuhan kolam rumah tinggal berpemakaian harian di Yasmin:'],
    '/area/cijeruk/' => ['Cijeruk', 'Layanan berikut kami sesuaikan dengan kelembapan udara dan curah hujan tinggi khas Cijeruk:'],
    '/area/rancamaya/' => ['Rancamaya', 'Layanan berikut kami sesuaikan dengan karakter kolam privat berukuran besar di kawasan gated community Rancamaya:'],
    '/area/bogor-raya/' => ['Bogor Raya', 'Layanan berikut kami sesuaikan dengan karakter kolam privat di kawasan residensial modern Bogor Raya:'],
    '/area/karadenan/' => ['Karadenan', 'Layanan berikut kami sesuaikan dengan kondisi akses dan jadwal kunjungan di kawasan Karadenan yang terus berkembang:'],
];

function build_combo_block($areaName, $areaSlug, $contextSentence, $services) {
    $html = '<h2 style="margin-top:32px">Detail Layanan Kami di ' . $areaName . '</h2>';
    $html .= '<p style="color:var(--gray-600)">' . $contextSentence . '</p>';
    $html .= '<ul style="color:var(--gray-600)">';
    foreach ($services as $slug => $label) {
        $html .= '<li><a href="/area/' . $areaSlug . '/' . $slug . '/" style="color:var(--blue-600);font-weight:600">' . $label . ' di ' . $areaName . '</a></li>';
    }
    $html .= '</ul>';
    return $html;
}

$results = ['combo_areas' => [], 'orphan_portfolio' => [], 'backups' => []];

try {
    $pdo = get_db();

    // ============================================================
    // 1) 36 combo pages: 1 blok link baru per area (9 area)
    // ============================================================
    foreach ($AREAS as $urlPath => list($areaName, $contextSentence)) {
        $areaSlug = trim($urlPath, '/');
        $areaSlug = substr($areaSlug, strlen('area/'));

        $sel = $pdo->prepare("SELECT id, content FROM pages WHERE url_path = :u AND type = 'area'");
        $sel->execute([':u' => $urlPath]);
        $row = $sel->fetch();
        if (!$row) {
            $results['combo_areas'][$urlPath] = ['status' => 'SKIPPED', 'reason' => 'row not found'];
            continue;
        }

        $oldContent = $row['content'];
        $results['backups'][$urlPath] = $oldContent;

        // Duplicate-link safety: cek combo URL mana yang SUDAH ada di content.
        $linksToAdd = [];
        $alreadyLinked = [];
        foreach ($SERVICES as $slug => $label) {
            $target = '/area/' . $areaSlug . '/' . $slug . '/';
            if (strpos($oldContent, $target) !== false) {
                $alreadyLinked[] = $target;
            } else {
                $linksToAdd[$slug] = $label;
            }
        }

        if (empty($linksToAdd)) {
            $results['combo_areas'][$urlPath] = [
                'status' => 'ALREADY_LINKED',
                'already_linked' => $alreadyLinked,
            ];
            continue;
        }

        $block = build_combo_block($areaName, $areaSlug, $contextSentence, $linksToAdd);
        $newContent = $oldContent . $block;

        $upd = $pdo->prepare("UPDATE pages SET content = :content WHERE id = :id AND url_path = :u AND type = 'area'");
        $upd->execute([':content' => $newContent, ':id' => $row['id'], ':u' => $urlPath]);

        $verify = [];
        foreach ($linksToAdd as $slug => $label) {
            $target = '/area/' . $areaSlug . '/' . $slug . '/';
            $verify[$target] = strpos($newContent, $target) !== false;
        }

        $results['combo_areas'][$urlPath] = [
            'status' => 'ADDED',
            'id' => $row['id'],
            'rows_affected' => $upd->rowCount(),
            'links_added' => array_map(function ($slug) use ($areaSlug) { return '/area/' . $areaSlug . '/' . $slug . '/'; }, array_keys($linksToAdd)),
            'already_linked' => $alreadyLinked,
            'verify_present_in_new_content' => $verify,
        ];
    }

    // ============================================================
    // 2) Orphan portfolio: kolam-renang-villa-modern -> link dari
    //    /layanan/pembuatan-kolam-renang-baru/ (service jelas, area
    //    tidak disebut eksplisit -> tidak dilink dari area manapun)
    // ============================================================
    $pfUrl = '/portofolio/kolam-renang-villa-modern/';
    $svcUrl = '/layanan/pembuatan-kolam-renang-baru/';
    $sel = $pdo->prepare("SELECT id, content FROM pages WHERE url_path = :u AND type = 'service'");
    $sel->execute([':u' => $svcUrl]);
    $row = $sel->fetch();
    if ($row) {
        $oldContent = $row['content'];
        $results['backups'][$svcUrl] = $oldContent;
        if (strpos($oldContent, $pfUrl) !== false) {
            $results['orphan_portfolio'][$pfUrl] = ['status' => 'ALREADY_LINKED', 'source' => $svcUrl];
        } else {
            $needle = 'dan <a href="/portofolio/kolam-renang-keluarga/" style="color:var(--blue-600);font-weight:600">Kolam Renang Keluarga</a>.';
            $replacement = 'dan <a href="/portofolio/kolam-renang-keluarga/" style="color:var(--blue-600);font-weight:600">Kolam Renang Keluarga</a>, serta <a href="' . $pfUrl . '" style="color:var(--blue-600);font-weight:600">Kolam Renang Villa Modern</a> untuk villa di kawasan perbukitan.';
            if (strpos($oldContent, $needle) === false) {
                $results['orphan_portfolio'][$pfUrl] = ['status' => 'SKIPPED', 'reason' => 'anchor text tidak ditemukan di content service (kemungkinan sudah berubah sejak audit) - tidak melakukan perubahan supaya aman'];
            } else {
                $newContent = str_replace($needle, $replacement, $oldContent);
                $upd = $pdo->prepare("UPDATE pages SET content = :content WHERE id = :id AND url_path = :u AND type = 'service'");
                $upd->execute([':content' => $newContent, ':id' => $row['id'], ':u' => $svcUrl]);
                $results['orphan_portfolio'][$pfUrl] = [
                    'status' => 'ADDED',
                    'source' => $svcUrl,
                    'id' => $row['id'],
                    'rows_affected' => $upd->rowCount(),
                    'verify_present_in_new_content' => strpos($newContent, $pfUrl) !== false,
                ];
            }
        }
    } else {
        $results['orphan_portfolio'][$pfUrl] = ['status' => 'SKIPPED', 'reason' => 'service page row not found: ' . $svcUrl];
    }

    // DATA_REQUIRED - tidak ada perubahan untuk 2 portofolio ini
    $results['orphan_portfolio']['/portofolio/perawatan-kolam-renang-di-area-belum-dipilih/'] = [
        'status' => 'DATA_REQUIRED',
        'reason' => 'Konten literally berisi placeholder "(area belum dipilih)" yang belum diisi admin - bukan proyek nyata yang dapat diverifikasi. Tidak ada link ditambahkan.',
    ];
    $results['orphan_portfolio']['/portofolio/perawatan-kolam-renang-di-cijeruk/'] = [
        'status' => 'DATA_REQUIRED',
        'reason' => 'Konten berisi catatan internal admin (instruksi cara generate deskripsi via AI) yang ter-paste tidak sengaja, bukan deskripsi proyek nyata. Tidak ada link ditambahkan.',
    ];

    // ============================================================
    // 3) Orphan portfolio: renovasi-kolam-resort -> link dari
    //    /area/puncak/ (evidence: "tahan cuaca dingin" cocok dengan
    //    karakteristik Puncak yang sudah live) DAN
    //    /layanan/renovasi-perbaikan-kolam/ (service eksplisit)
    // ============================================================
    $pfUrl2 = '/portofolio/renovasi-kolam-resort/';
    $mention = '<p style="color:var(--gray-600)">Kami juga pernah menangani <a href="' . $pfUrl2 . '" style="color:var(--blue-600);font-weight:600">renovasi kolam renang resort dengan sistem tahan cuaca dingin</a> di kawasan pegunungan seperti Puncak.</p>';

    // 3a. /area/puncak/
    $sel = $pdo->prepare("SELECT id, content FROM pages WHERE url_path = '/area/puncak/' AND type = 'area'");
    $sel->execute();
    $row = $sel->fetch();
    if ($row) {
        $oldContent = $row['content'];
        $results['backups']['/area/puncak/'] = $oldContent;
        if (strpos($oldContent, $pfUrl2) !== false) {
            $results['orphan_portfolio'][$pfUrl2 . ' (via /area/puncak/)'] = ['status' => 'ALREADY_LINKED', 'source' => '/area/puncak/'];
        } else {
            $needle = '</div><h2 style="margin-top:32px">Bacaan Terkait</h2>';
            if (strpos($oldContent, $needle) === false) {
                $results['orphan_portfolio'][$pfUrl2 . ' (via /area/puncak/)'] = ['status' => 'SKIPPED', 'reason' => 'anchor structure "Bacaan Terkait" tidak ditemukan seperti diharapkan - tidak melakukan perubahan supaya aman'];
            } else {
                $newContent = str_replace($needle, '</div>' . $mention . '<h2 style="margin-top:32px">Bacaan Terkait</h2>', $oldContent);
                $upd = $pdo->prepare("UPDATE pages SET content = :content WHERE id = :id AND url_path = '/area/puncak/' AND type = 'area'");
                $upd->execute([':content' => $newContent, ':id' => $row['id']]);
                $results['orphan_portfolio'][$pfUrl2 . ' (via /area/puncak/)'] = [
                    'status' => 'ADDED',
                    'id' => $row['id'],
                    'rows_affected' => $upd->rowCount(),
                    'verify_present_in_new_content' => strpos($newContent, $pfUrl2) !== false,
                ];
            }
        }
    } else {
        $results['orphan_portfolio'][$pfUrl2 . ' (via /area/puncak/)'] = ['status' => 'SKIPPED', 'reason' => 'row not found: /area/puncak/'];
    }

    // 3b. /layanan/renovasi-perbaikan-kolam/
    $svcUrl2 = '/layanan/renovasi-perbaikan-kolam/';
    $sel = $pdo->prepare("SELECT id, content FROM pages WHERE url_path = :u AND type = 'service'");
    $sel->execute([':u' => $svcUrl2]);
    $row = $sel->fetch();
    if ($row) {
        $oldContent = $row['content'];
        $results['backups'][$svcUrl2] = $oldContent;
        if (strpos($oldContent, $pfUrl2) !== false) {
            $results['orphan_portfolio'][$pfUrl2 . ' (via ' . $svcUrl2 . ')'] = ['status' => 'ALREADY_LINKED', 'source' => $svcUrl2];
        } else {
            $needle2 = 'berikut salah satu contoh renovasi kolam yang pernah kami tangani.</p><div class="portfolio-grid">';
            if (strpos($oldContent, $needle2) === false) {
                $results['orphan_portfolio'][$pfUrl2 . ' (via ' . $svcUrl2 . ')'] = ['status' => 'SKIPPED', 'reason' => 'anchor text tidak ditemukan seperti diharapkan - tidak melakukan perubahan supaya aman'];
            } else {
                $step1 = str_replace(
                    'berikut salah satu contoh renovasi kolam yang pernah kami tangani.</p><div class="portfolio-grid">',
                    'berikut beberapa contoh renovasi kolam yang pernah kami tangani.</p><div class="portfolio-grid">',
                    $oldContent
                );
                // sisipkan mention teks setelah penutup </div> dari portfolio-grid pertama
                $needle3 = '</a></div><h2';
                if (strpos($step1, $needle3) === false) {
                    $results['orphan_portfolio'][$pfUrl2 . ' (via ' . $svcUrl2 . ')'] = ['status' => 'SKIPPED', 'reason' => 'struktur portfolio-grid tidak sesuai ekspektasi - tidak melakukan perubahan supaya aman'];
                } else {
                    $newContent = preg_replace('/(<\/a><\/div>)(<h2)/', '$1' . $mention . '$2', $step1, 1);
                    $upd = $pdo->prepare("UPDATE pages SET content = :content WHERE id = :id AND url_path = :u AND type = 'service'");
                    $upd->execute([':content' => $newContent, ':id' => $row['id'], ':u' => $svcUrl2]);
                    $results['orphan_portfolio'][$pfUrl2 . ' (via ' . $svcUrl2 . ')'] = [
                        'status' => 'ADDED',
                        'id' => $row['id'],
                        'rows_affected' => $upd->rowCount(),
                        'verify_present_in_new_content' => strpos($newContent, $pfUrl2) !== false,
                    ];
                }
            }
        }
    } else {
        $results['orphan_portfolio'][$pfUrl2 . ' (via ' . $svcUrl2 . ')'] = ['status' => 'SKIPPED', 'reason' => 'row not found: ' . $svcUrl2];
    }

    respond(true, 'Fase perbaikan orphan/internal link selesai.', $results);
} catch (Exception $e) {
    respond(false, 'Gagal: ' . $e->getMessage(), $results);
}
