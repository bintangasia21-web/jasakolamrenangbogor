<?php
/**
 * Skrip diagnostik SEMENTARA (bukan bagian permanen situs), dilindungi
 * Basic Auth sama seperti skrip admin lainnya. Menjalankan ULANG logic
 * auto-pembuatan halaman detail SEO Portfolio (persis seperti di
 * save-data.php) di dalam transaksi yang di-ROLLBACK di akhir -- jadi
 * TIDAK mengubah data apa pun, murni untuk melihat pesan error asli
 * per-item kalau ada yang gagal. DIHAPUS setelah masalah ditemukan.
 */
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/inc/db.php';

function diag_slugify($text) {
    $text = strtolower(trim((string) $text));
    $text = preg_replace('/[^a-z0-9\s-]/', '', $text);
    $text = preg_replace('/[\s-]+/', '-', $text);
    return trim($text, '-');
}

$results = [];

try {
    $pdo = get_db();
} catch (Exception $e) {
    echo json_encode(['stage' => 'connect', 'error' => $e->getMessage()]);
    exit;
}

try {
    $rows = $pdo->query('SELECT * FROM portfolio ORDER BY sort_order, id')->fetchAll();
} catch (Exception $e) {
    echo json_encode(['stage' => 'select_portfolio', 'error' => $e->getMessage()]);
    exit;
}

$pdo->beginTransaction();

try {
    $pageStmt = $pdo->prepare(
        "INSERT INTO pages (type, url_path, title, h1, area_ref, intro, content, cover_image, status)
         VALUES ('portfolio', :url_path, :title, :h1, :area_ref, :intro, :content, :cover_image, 'published')
         ON DUPLICATE KEY UPDATE title=VALUES(title), h1=VALUES(h1), area_ref=VALUES(area_ref),
           intro=VALUES(intro), content=VALUES(content), status='published'"
    );
    $updateLinkStmt = $pdo->prepare('UPDATE portfolio SET detail_link = :detail_link WHERE id = :id');

    $usedSlugs = [];
    $order = 0;
    foreach ($rows as $row) {
        $title = trim($row['title'] ?? '');
        $desc = $row['description'] ?? '';
        $area = $row['area'] ?? '';
        $entry = ['id' => $row['id'], 'title' => $title];

        if ($title === '') {
            $entry['skipped'] = 'judul kosong';
            $results[] = $entry;
            $order++;
            continue;
        }

        $slug = diag_slugify($title);
        if ($slug === '') $slug = 'proyek-' . ($order + 1);
        if (isset($usedSlugs[$slug])) {
            $usedSlugs[$slug]++;
            $slug .= '-' . $usedSlugs[$slug];
        } else {
            $usedSlugs[$slug] = 1;
        }
        $detailLink = '/portofolio/' . $slug . '/';
        $entry['computed_detail_link'] = $detailLink;

        $pageStmt->execute([
            ':url_path' => $detailLink,
            ':title' => $title,
            ':h1' => $title,
            ':area_ref' => $area,
            ':intro' => $desc,
            ':content' => '<p>' . htmlspecialchars($desc, ENT_QUOTES, 'UTF-8') . '</p>',
            ':cover_image' => $row['image'] ?? null
        ]);
        $entry['page_upsert'] = 'OK';

        $updateLinkStmt->execute([':detail_link' => $detailLink, ':id' => $row['id']]);
        $entry['link_update'] = 'OK';

        $results[] = $entry;
        $order++;
    }

    $pdo->rollBack();
    echo json_encode(['note' => 'DRY RUN -- semua perubahan di atas di-ROLLBACK, tidak benar-benar tersimpan.', 'results' => $results], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    echo json_encode(['stage' => 'processing', 'error' => $e->getMessage(), 'partial_results' => $results], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
}
