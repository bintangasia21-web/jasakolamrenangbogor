<?php
/**
 * Daftar ringkas semua baris "pages" untuk tabel di tab "Halaman SEO"
 * panel admin. Dilindungi Basic Auth.
 */
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/inc/db.php';

try {
    $pdo = get_db();

    if (isset($_GET['id'])) {
        $stmt = $pdo->prepare('SELECT * FROM pages WHERE id = :id');
        $stmt->execute([':id' => (int) $_GET['id']]);
        $row = $stmt->fetch();
        if (!$row) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Halaman tidak ditemukan.']);
            exit;
        }
        $row['faq'] = $row['faq_json'] ? json_decode($row['faq_json'], true) : [];
        echo json_encode(['success' => true, 'page' => $row], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        exit;
    }

    $rows = $pdo->query('SELECT id, type, tier, title, url_path, target_keyword, status FROM pages ORDER BY id')->fetchAll();
    echo json_encode(['success' => true, 'pages' => $rows], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error']);
}
