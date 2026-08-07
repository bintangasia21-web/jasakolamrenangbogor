<?php
/**
 * Menjalankan schema.sql (buat tabel) langsung dari server, lewat PDO
 * yang sudah punya akses ke db-config.php — supaya tidak perlu membuka
 * phpMyAdmin secara manual. Dilindungi Basic Auth. Aman dijalankan
 * berkali-kali (semua statement di schema.sql pakai IF NOT EXISTS).
 */
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/inc/db.php';

function respond($success, $message, $extra = []) {
    echo json_encode(array_merge(['success' => $success, 'message' => $message], $extra));
    exit;
}

$sqlFile = __DIR__ . '/schema.sql';
if (!file_exists($sqlFile)) {
    respond(false, 'schema.sql tidak ditemukan di server.');
}

$sql = file_get_contents($sqlFile);
// Buang komentar baris "--" lalu pecah per statement berdasarkan ";"
$sql = preg_replace('/^--.*$/m', '', $sql);
$statements = array_filter(array_map('trim', explode(';', $sql)));

try {
    $pdo = get_db();
    $executed = [];
    foreach ($statements as $stmt) {
        if ($stmt === '') continue;
        $pdo->exec($stmt);
        preg_match('/CREATE TABLE[^`]*`?(\w+)`?/i', $stmt, $m);
        $executed[] = $m[1] ?? substr($stmt, 0, 30);
    }

    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    respond(true, 'Skema berhasil dijalankan.', ['tables_now' => $tables]);
} catch (Exception $e) {
    respond(false, 'Gagal menjalankan skema: ' . $e->getMessage());
}
