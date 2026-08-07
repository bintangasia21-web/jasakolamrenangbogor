<?php
/**
 * Import massal baris ke tabel "pages" dari data terstruktur (JSON array
 * hasil parsing sheet: No/Prioritas/Kategori/Judul Halaman/URL/Target
 * Kata Kunci/Status). Dilindungi Basic Auth. Aman dijalankan berkali-kali
 * — url_path unik, baris yang sudah ada hanya diperbarui tier & keyword
 * (tidak menimpa konten yang sudah ditulis admin).
 */
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/inc/db.php';

function respond($success, $message, $extra = []) {
    echo json_encode(array_merge(['success' => $success, 'message' => $message], $extra));
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    respond(false, 'Metode tidak diizinkan.');
}

$payloadRaw = $_POST['payload'] ?? '';
$rows = json_decode($payloadRaw, true);
if (!is_array($rows)) {
    respond(false, 'Data tidak valid.');
}

$kategoriMap = [
    'Halaman Area' => 'area',
    'Kombinasi Layanan x Area' => 'combo',
    'Halaman Layanan' => 'service',
    'Artikel' => 'article',
    'Portofolio (menunggu proyek nyata)' => 'portfolio',
    'Halaman Pendukung' => 'page'
];

try {
    $pdo = get_db();
    $stmt = $pdo->prepare(
        'INSERT INTO pages (type, tier, url_path, title, meta_title, target_keyword, h1, area_ref, service_ref, intro, status, sort_order)
         VALUES (:type, :tier, :url_path, :title, :title2, :keyword, :title3, :area_ref, :service_ref, :intro, :status, :sort_order)
         ON DUPLICATE KEY UPDATE tier = VALUES(tier), target_keyword = VALUES(target_keyword)'
    );

    $inserted = 0;
    $skipped = 0;
    $order = 0;
    foreach ($rows as $row) {
        $kategori = trim($row['kategori'] ?? '');
        $type = $kategoriMap[$kategori] ?? null;
        $urlPath = trim($row['url'] ?? '');
        $title = trim($row['judul'] ?? '');
        if (!$type || $urlPath === '' || $title === '') {
            $skipped++;
            continue;
        }
        if ($urlPath[0] !== '/') $urlPath = '/' . $urlPath;
        if (substr($urlPath, -1) !== '/') $urlPath .= '/';

        $areaRef = null;
        $serviceRef = null;
        if (($type === 'combo' || $type === 'portfolio') && strpos($title, ' di ') !== false) {
            $parts = explode(' di ', $title, 2);
            $serviceRef = trim($parts[0]);
            $areaRef = trim(preg_replace('/\s*#\d+$/', '', $parts[1]));
        } elseif ($type === 'area') {
            $areaRef = trim(preg_replace('/^Jasa Kolam Renang\s*/i', '', $title));
        }

        $status = (trim($row['status'] ?? '') === 'Sudah dibuat') ? 'published' : 'draft';

        $stmt->execute([
            ':type' => $type,
            ':tier' => trim($row['prioritas'] ?? ''),
            ':url_path' => $urlPath,
            ':title' => $title,
            ':title2' => $title,
            ':keyword' => trim($row['keyword'] ?? ''),
            ':title3' => $title,
            ':area_ref' => $areaRef,
            ':service_ref' => $serviceRef,
            ':intro' => '',
            ':status' => $status,
            ':sort_order' => $order++
        ]);
        $inserted++;
    }
    respond(true, 'Import selesai.', ['inserted' => $inserted, 'skipped' => $skipped]);
} catch (Exception $e) {
    respond(false, 'Import gagal: ' . $e->getMessage());
}
