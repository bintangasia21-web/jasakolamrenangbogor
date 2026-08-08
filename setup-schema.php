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
} catch (Exception $e) {
    respond(false, 'Gagal konek database: ' . $e->getMessage());
}

// Tiap statement dieksekusi & ditangkap errornya SENDIRI-SENDIRI (bukan
// satu try/catch besar) — supaya kalau satu ALTER/CREATE gagal (mis.
// kolom sudah ada di MySQL versi lama yang tidak dukung "IF NOT EXISTS"),
// statement lain di bawahnya tetap lanjut jalan, bukan berhenti total.
$ok = [];
$failed = [];
foreach ($statements as $stmt) {
    if ($stmt === '') continue;
    preg_match('/^(CREATE TABLE|ALTER TABLE)[^`\w]*`?(\w+)`?/i', $stmt, $m);
    $label = isset($m[2]) ? $m[1] . ' ' . $m[2] : substr($stmt, 0, 40);
    try {
        $pdo->exec($stmt);
        $ok[] = $label;
    } catch (Exception $e) {
        $failed[] = $label . ': ' . $e->getMessage();
    }
}

$tables = $pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
respond(empty($failed), empty($failed) ? 'Skema berhasil dijalankan.' : 'Sebagian statement gagal, lihat "failed".', [
    'tables_now' => $tables,
    'ok' => $ok,
    'failed' => $failed
]);
