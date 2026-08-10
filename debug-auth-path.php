<?php
/**
 * Skrip diagnostik sementara: mencari tahu kenapa login admin gagal
 * setelah reset .htpasswd, dengan menampilkan lokasi folder situs yang
 * sebenarnya di server dan status file .htpasswd yang dibaca .htaccess
 * -- TANPA menampilkan isi hash/password. SENGAJA tidak dilindungi
 * Basic Auth (memang untuk dipakai saat admin belum bisa login sama
 * sekali). Hapus file ini setelah masalah login selesai ditelusuri.
 */
header('Content-Type: application/json; charset=utf-8');

$expectedAuthUserFile = '/home/u740832685/domains/jasakolamrenangbogor.com/public_html/.htpasswd';

function htpasswd_info($path) {
    if (!file_exists($path)) {
        return ['exists' => false];
    }
    $lines = array_filter(array_map('trim', file($path)));
    $usernames = [];
    foreach ($lines as $line) {
        $parts = explode(':', $line, 2);
        $usernames[] = $parts[0] ?? '(baris tidak valid)';
    }
    return [
        'exists' => true,
        'readable' => is_readable($path),
        'modified_at' => date('Y-m-d H:i:s', filemtime($path)),
        'line_count' => count($lines),
        'usernames_found' => $usernames,
    ];
}

$candidates = [
    $expectedAuthUserFile,
    __DIR__ . '/.htpasswd',
];

$result = [
    'script_actual_folder' => __DIR__,
    'document_root' => $_SERVER['DOCUMENT_ROOT'] ?? null,
    'expected_authuserfile_per_htaccess' => $expectedAuthUserFile,
    'checks' => [],
];

foreach (array_unique($candidates) as $path) {
    $result['checks'][$path] = htpasswd_info($path);
}

echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
