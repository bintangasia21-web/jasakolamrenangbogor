<?php
/**
 * Simpan teks/copy satu halaman (page_key) — upsert per field lewat
 * INSERT ... ON DUPLICATE KEY UPDATE (bukan delete-all-lalu-insert-ulang
 * seperti save-data.php, karena di sini tidak ada urutan/sort_order yang
 * perlu dijaga, cuma pasangan field_key => field_value). Dilindungi
 * Basic Auth.
 */
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/inc/db.php';

function respond($success, $message) {
    echo json_encode(['success' => $success, 'message' => $message]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    respond(false, 'Metode tidak diizinkan.');
}

$pageKey = $_POST['page_key'] ?? '';
$payloadRaw = $_POST['payload'] ?? '';
$payload = json_decode($payloadRaw, true);

if ($pageKey === '' || !is_array($payload)) {
    respond(false, 'Data tidak valid.');
}

try {
    $pdo = get_db();
    $stmt = $pdo->prepare(
        'INSERT INTO page_sections (page_key, field_key, field_value) VALUES (:page_key, :field_key, :field_value)
         ON DUPLICATE KEY UPDATE field_value = VALUES(field_value)'
    );
    foreach ($payload as $fieldKey => $fieldValue) {
        $stmt->execute([
            ':page_key' => $pageKey,
            ':field_key' => $fieldKey,
            ':field_value' => (string) $fieldValue
        ]);
    }
    respond(true, 'Perubahan berhasil disimpan dan langsung tampil di situs.');
} catch (Exception $e) {
    respond(false, 'Gagal menyimpan: ' . $e->getMessage());
}
