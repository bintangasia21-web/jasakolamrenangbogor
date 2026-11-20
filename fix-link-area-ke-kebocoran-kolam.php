<?php
/**
 * Skrip sekali-jalan: tambahkan 1 link internal LANGSUNG dari 9
 * halaman area ke /layanan/perbaikan-kebocoran-kolam/ -- bagian dari
 * rencana memperkuat halaman itu berdasarkan data GSC ("jasa
 * perbaikan kolam renang bocor" posisi 6, dekat halaman 1).
 *
 * Dikonfirmasi lewat fetch live sebelum implementasi: hanya Bogor
 * Kota yang SUDAH punya link langsung ke halaman ini. 9 area berikut
 * BELUM: Sentul, Ciawi, Cibinong, Yasmin, Cijeruk, Rancamaya, Bogor
 * Raya, Karadenan, Puncak -- link baru disisipkan sebagai paragraf
 * baru tepat sebelum blok "Detail Layanan Kami di [Area]" yang sudah
 * ada (Puncak beda struktur, tidak punya blok combo itu -- disisipkan
 * sebelum heading "Bacaan Terkait" yang sudah ada di halaman itu),
 * memakai konteks (kontur tanah, curah hujan, usia kolam, dll) yang
 * SUDAH dipublikasikan di halaman itu sendiri, bukan fakta baru.
 *
 * Content di-fetch & dicek strpos() dulu sebelum str_replace (bukan
 * overwrite). Aman dijalankan berkali-kali -- ALREADY_LINKED kalau
 * link sudah ada, SKIPPED kalau anchor heading tidak ditemukan persis
 * (berarti sudah berubah sejak script ini dibuat, tidak dipaksa
 * diubah).
 */
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/inc/db.php';

function respond($success, $message, $extra = []) {
    echo json_encode(array_merge(['success' => $success, 'message' => $message], $extra), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}

$svcUrl = '/layanan/perbaikan-kebocoran-kolam/';

// url_path => [needle (anchor heading yang sudah ada, persis), paragraf baru yang disisipkan sebelum heading itu]
$AREAS = [
    '/area/sentul/' => [
        '</p><h2 style="margin-top:32px">Detail Layanan Kami di Sentul</h2>',
        '<p style="color:var(--gray-600)">Kontur tanah berbukit di Sentul membuat struktur kolam lebih rentan bergeser seiring waktu -- kami juga menyediakan layanan <a href="' . $svcUrl . '" style="color:var(--blue-600);font-weight:600">perbaikan kebocoran kolam</a> kalau kolam Anda sudah menunjukkan tanda rembesan.</p>',
    ],
    '/area/ciawi/' => [
        '</p><h2 style="margin-top:32px">Detail Layanan Kami di Ciawi</h2>',
        '<p style="color:var(--gray-600)">Untuk kolam yang sudah lama berdiri di jalur Ciawi menuju Puncak dan mulai menunjukkan tanda rembesan, kami juga menyediakan layanan <a href="' . $svcUrl . '" style="color:var(--blue-600);font-weight:600">perbaikan kebocoran kolam renang</a>.</p>',
    ],
    '/area/cibinong/' => [
        '</p><h2 style="margin-top:32px">Detail Layanan Kami di Cibinong</h2>',
        '<p style="color:var(--gray-600)">Kolam renang yang sudah berumur di kawasan permukiman padat seperti Cibinong juga rawan mengalami kebocoran -- kami menyediakan layanan <a href="' . $svcUrl . '" style="color:var(--blue-600);font-weight:600">perbaikan kebocoran kolam</a> untuk menanganinya.</p>',
    ],
    '/area/yasmin/' => [
        '</p><h2 style="margin-top:32px">Detail Layanan Kami di Yasmin</h2>',
        '<p style="color:var(--gray-600)">Selain perawatan rutin, kami juga menyediakan layanan <a href="' . $svcUrl . '" style="color:var(--blue-600);font-weight:600">perbaikan kebocoran kolam</a> untuk kolam di Yasmin yang mulai menunjukkan tanda rembesan.</p>',
    ],
    '/area/cijeruk/' => [
        '</p><h2 style="margin-top:32px">Detail Layanan Kami di Cijeruk</h2>',
        '<p style="color:var(--gray-600)">Curah hujan tinggi di Cijeruk juga bisa mempercepat kerusakan struktur kolam dari waktu ke waktu -- kami menyediakan layanan <a href="' . $svcUrl . '" style="color:var(--blue-600);font-weight:600">perbaikan kebocoran kolam</a> kalau kolam Anda mulai rembes.</p>',
    ],
    '/area/rancamaya/' => [
        '</p><h2 style="margin-top:32px">Detail Layanan Kami di Rancamaya</h2>',
        '<p style="color:var(--gray-600)">Kolam privat berukuran besar di Rancamaya yang sudah lama berdiri juga berisiko mengalami kebocoran -- kami menyediakan layanan <a href="' . $svcUrl . '" style="color:var(--blue-600);font-weight:600">perbaikan kebocoran kolam</a> untuk menanganinya.</p>',
    ],
    '/area/bogor-raya/' => [
        '</p><h2 style="margin-top:32px">Detail Layanan Kami di Bogor Raya</h2>',
        '<p style="color:var(--gray-600)">Untuk kolam privat di Bogor Raya yang sudah mulai menunjukkan tanda rembesan, kami juga menyediakan layanan <a href="' . $svcUrl . '" style="color:var(--blue-600);font-weight:600">perbaikan kebocoran kolam</a>.</p>',
    ],
    '/area/karadenan/' => [
        '</p><h2 style="margin-top:32px">Detail Layanan Kami di Karadenan</h2>',
        '<p style="color:var(--gray-600)">Untuk kolam renang di Karadenan yang sudah lama berdiri dan mulai menunjukkan tanda rembesan, kami juga menyediakan layanan <a href="' . $svcUrl . '" style="color:var(--blue-600);font-weight:600">perbaikan kebocoran kolam</a>.</p>',
    ],
    '/area/puncak/' => [
        '</p><h2 style="margin-top:32px">Bacaan Terkait</h2>',
        '<p style="color:var(--gray-600)">Curah hujan tinggi di kawasan Puncak juga bisa mempercepat kerusakan struktur kolam lama -- kami menyediakan layanan <a href="' . $svcUrl . '" style="color:var(--blue-600);font-weight:600">perbaikan kebocoran kolam</a> untuk mengatasinya.</p>',
    ],
];

try {
    $pdo = get_db();
    $results = [];

    foreach ($AREAS as $areaUrl => [$needleTail, $newParagraph]) {
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

        if (strpos($oldContent, $needleTail) === false) {
            $results[$areaUrl] = [
                'status' => 'SKIPPED',
                'reason' => 'anchor heading tidak ditemukan persis -- kemungkinan sudah berubah sejak script ini dibuat, tidak dipaksa diubah',
                'backup_content' => $backup,
            ];
            continue;
        }

        $replacement = '</p>' . $newParagraph . substr($needleTail, 4); // substr untuk buang '</p>' di awal needle, sisanya heading tetap
        $newContent = str_replace($needleTail, $replacement, $oldContent);

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

    respond(true, 'Selesai menambah link ke halaman perbaikan kebocoran kolam.', ['results' => $results]);
} catch (Exception $e) {
    respond(false, 'Gagal: ' . $e->getMessage());
}
