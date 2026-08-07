<?php
/**
 * Endpoint untuk menyimpan perubahan konten secara langsung live ke
 * assets/js/data.json (dibaca oleh main.js saat runtime). Saat ini
 * dipakai oleh panel admin khusus untuk bagian "portfolio" (foto),
 * supaya foto yang diunggah langsung tampil bagi semua pengunjung
 * tanpa perlu unduh/upload data.js manual.
 *
 * assets/js/data.json TIDAK dilacak Git (lihat .gitignore) supaya
 * perubahan lewat endpoint ini tidak tertimpa saat deploy berikutnya.
 */
header('Content-Type: application/json; charset=utf-8');

function respond($success, $message) {
    echo json_encode(['success' => $success, 'message' => $message]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    respond(false, 'Metode tidak diizinkan.');
}

$allowedSections = ['portfolio'];

$section = $_POST['section'] ?? '';
$payloadRaw = $_POST['payload'] ?? '';

if (!in_array($section, $allowedSections, true)) {
    respond(false, 'Bagian data tidak dikenali.');
}

$payload = json_decode($payloadRaw, true);
if (json_last_error() !== JSON_ERROR_NONE || !is_array($payload)) {
    respond(false, 'Data yang dikirim tidak valid.');
}

$dataFile = __DIR__ . '/assets/js/data.json';
$defaultFile = __DIR__ . '/assets/js/data.default.json';

// data.json sengaja tidak dilacak Git (lihat .gitignore) supaya bisa ditulis
// bebas oleh endpoint ini. Kalau proses deploy pernah menghapusnya, ATAU
// isinya rusak/bukan JSON valid (mis. tertimpa file lain secara tidak
// sengaja), pulihkan otomatis dari data.default.json (file baca-saja yang
// selalu ikut ter-deploy) sebelum menerapkan perubahan yang diminta.
$current = null;
if (file_exists($dataFile) && is_readable($dataFile)) {
    $current = json_decode(file_get_contents($dataFile), true);
}
if (!is_array($current)) {
    if (!file_exists($defaultFile) || !is_readable($defaultFile)) {
        respond(false, 'data.json rusak dan data.default.json tidak ada/tidak bisa dibaca. Hubungi pengembang.');
    }
    $current = json_decode(file_get_contents($defaultFile), true);
    if (!is_array($current)) {
        respond(false, 'data.default.json juga rusak/tidak valid. Hubungi pengembang.');
    }
}
if (file_exists($dataFile) && !is_writable($dataFile)) {
    respond(false, 'data.json tidak bisa ditulis. Cek izin file di server.');
}

$current[$section] = $payload;

$json = json_encode($current, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
if ($json === false || file_put_contents($dataFile, $json, LOCK_EX) === false) {
    respond(false, 'Gagal menyimpan data ke server.');
}

respond(true, 'Perubahan berhasil disimpan dan langsung tampil di situs.');
