<?php
/**
 * Skrip sekali-jalan: tambahkan 1 link internal LANGSUNG dari 6
 * halaman area ke /layanan/pembuatan-kolam-renang-baru/ (bukan ke
 * combo page /area/{area}/pembuatan-kolam-renang-baru/ yang sudah ada
 * lewat blok "Detail Layanan Kami di [Area]" dari fase sebelumnya).
 *
 * Dikonfirmasi lewat fetch live sebelum implementasi: 4 dari 10 area
 * (Sentul, Bogor Kota, Cibinong, Puncak) SUDAH punya link langsung ke
 * halaman ini. 6 area berikut BELUM: Ciawi, Yasmin, Cijeruk,
 * Rancamaya, Bogor Raya, Karadenan -- link baru disisipkan sebagai
 * paragraf baru tepat sebelum blok "Detail Layanan Kami di [Area]"
 * yang sudah ada, memakai konteks (vila/rumah tinggal/kompleks) yang
 * SUDAH dipublikasikan di halaman itu sendiri, bukan fakta baru.
 *
 * Content di-fetch & dicek strpos() dulu sebelum str_replace (bukan
 * overwrite). Aman dijalankan berkali-kali -- ALREADY_LINKED kalau
 * link sudah ada, SKIPPED kalau anchor "Detail Layanan Kami di
 * [Area]" tidak ditemukan persis (berarti sudah berubah sejak script
 * ini dibuat, tidak dipaksa diubah).
 */
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/inc/db.php';

function respond($success, $message, $extra = []) {
    echo json_encode(array_merge(['success' => $success, 'message' => $message], $extra), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}

$svcUrl = '/layanan/pembuatan-kolam-renang-baru/';

// url_path => [nama tampilan untuk heading "Detail Layanan Kami di X", kalimat baru yang disisipkan]
$AREAS = [
    '/area/ciawi/' => [
        'Ciawi',
        '<p style="color:var(--gray-600)">Untuk vila atau tempat peristirahatan yang ingin membangun kolam baru di jalur Ciawi menuju Puncak, kami juga menyediakan layanan <a href="' . $svcUrl . '" style="color:var(--blue-600);font-weight:600">pembuatan kolam renang baru</a>.</p>',
    ],
    '/area/yasmin/' => [
        'Yasmin',
        '<p style="color:var(--gray-600)">Untuk rumah tinggal di Yasmin yang ingin membangun kolam baru, kami juga menyediakan layanan <a href="' . $svcUrl . '" style="color:var(--blue-600);font-weight:600">pembuatan kolam renang baru</a>.</p>',
    ],
    '/area/cijeruk/' => [
        'Cijeruk',
        '<p style="color:var(--gray-600)">Untuk vila atau tempat peristirahatan yang ingin membangun kolam baru di kawasan kaki Gunung Salak ini, kami juga menyediakan layanan <a href="' . $svcUrl . '" style="color:var(--blue-600);font-weight:600">pembuatan kolam renang baru</a>.</p>',
    ],
    '/area/rancamaya/' => [
        'Rancamaya',
        '<p style="color:var(--gray-600)">Untuk rumah di kompleks Rancamaya yang ingin membangun kolam privat baru, kami juga menyediakan layanan <a href="' . $svcUrl . '" style="color:var(--blue-600);font-weight:600">pembuatan kolam renang baru</a>.</p>',
    ],
    '/area/bogor-raya/' => [
        'Bogor Raya',
        '<p style="color:var(--gray-600)">Untuk rumah di kawasan residensial Bogor Raya yang ingin membangun kolam privat baru, kami juga menyediakan layanan <a href="' . $svcUrl . '" style="color:var(--blue-600);font-weight:600">pembuatan kolam renang baru</a>.</p>',
    ],
    '/area/karadenan/' => [
        'Karadenan',
        '<p style="color:var(--gray-600)">Untuk rumah tinggal baru atau properti komersial di Karadenan yang ingin membangun kolam baru, kami juga menyediakan layanan <a href="' . $svcUrl . '" style="color:var(--blue-600);font-weight:600">pembuatan kolam renang baru</a>.</p>',
    ],
];

try {
    $pdo = get_db();
    $results = [];

    foreach ($AREAS as $areaUrl => [$areaName, $newParagraph]) {
        $sel = $pdo->prepare("SELECT id, content FROM pages WHERE url_path = :u AND type = 'area'");
        $sel->execute([':u' => $areaUrl]);
        $row = $sel->fetch();

        if (!$row) {
            $results[$areaUrl] = ['status' => 'SKIPPED', 'reason' => 'baris tidak ditemukan'];
            continue;
        }

        $oldContent = $row['content'];
        $backup = $oldContent;

        if (strpos($oldContent, $svcUrl) !== false) {
            $results[$areaUrl] = ['status' => 'ALREADY_LINKED', 'backup_content' => $backup];
            continue;
        }

        $needle = '</p><h2 style="margin-top:32px">Detail Layanan Kami di ' . $areaName . '</h2>';
        if (strpos($oldContent, $needle) === false) {
            $results[$areaUrl] = [
                'status' => 'SKIPPED',
                'reason' => 'anchor "Detail Layanan Kami di ' . $areaName . '" tidak ditemukan persis -- kemungkinan sudah berubah sejak script ini dibuat, tidak dipaksa diubah',
                'backup_content' => $backup,
            ];
            continue;
        }

        $replacement = '</p>' . $newParagraph . '<h2 style="margin-top:32px">Detail Layanan Kami di ' . $areaName . '</h2>';
        $newContent = str_replace($needle, $replacement, $oldContent);

        $upd = $pdo->prepare("UPDATE pages SET content = :content WHERE id = :id AND url_path = :u AND type = 'area'");
        $upd->execute([':content' => $newContent, ':id' => $row['id'], ':u' => $areaUrl]);

        $results[$areaUrl] = [
            'status' => 'ADDED',
            'id' => $row['id'],
            'rows_affected' => $upd->rowCount(),
            'verify_present_in_new_content' => strpos($newContent, $svcUrl) !== false,
            'backup_content' => $backup,
        ];
    }

    respond(true, 'Selesai menambah link ke halaman pembuatan kolam renang baru.', ['results' => $results]);
} catch (Exception $e) {
    respond(false, 'Gagal: ' . $e->getMessage());
}
