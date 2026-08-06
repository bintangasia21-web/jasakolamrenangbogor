<?php
/**
 * Endpoint ganti password login admin.html.
 * Dilindungi Basic Auth yang sama lewat .htaccess (lihat <FilesMatch>),
 * jadi permintaan hanya sampai ke sini jika kredensial saat ini valid.
 * Menulis ulang .htpasswd langsung di server (bukan lewat Git) supaya
 * perubahan tidak tertimpa saat deploy berikutnya.
 */
header('Content-Type: application/json; charset=utf-8');

function get_basic_auth_credentials() {
    if (isset($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW'])) {
        return [$_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW']];
    }
    $header = $_SERVER['HTTP_AUTHORIZATION'] ?? null;
    if (!$header && function_exists('apache_request_headers')) {
        $headers = apache_request_headers();
        $header = $headers['Authorization'] ?? null;
    }
    if ($header && stripos($header, 'Basic ') === 0) {
        $decoded = base64_decode(substr($header, 6));
        if ($decoded !== false && strpos($decoded, ':') !== false) {
            return explode(':', $decoded, 2);
        }
    }
    return [null, null];
}

function respond($success, $message) {
    echo json_encode(['success' => $success, 'message' => $message]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    respond(false, 'Metode tidak diizinkan.');
}

[$authUser, $authPass] = get_basic_auth_credentials();
if (!$authUser) {
    http_response_code(401);
    respond(false, 'Belum terautentikasi.');
}

$currentPassword = $_POST['current_password'] ?? '';
$newPassword = $_POST['new_password'] ?? '';

if ($currentPassword !== $authPass) {
    respond(false, 'Password saat ini salah.');
}
if (strlen($newPassword) < 8) {
    respond(false, 'Password baru minimal 8 karakter.');
}

$htpasswdFile = __DIR__ . '/.htpasswd';
if (!is_writable($htpasswdFile)) {
    respond(false, 'File .htpasswd tidak bisa ditulis. Cek izin file di server.');
}

$newHash = '{SHA}' . base64_encode(sha1($newPassword, true));
$newLine = $authUser . ':' . $newHash . "\n";

if (file_put_contents($htpasswdFile, $newLine, LOCK_EX) === false) {
    respond(false, 'Gagal menyimpan password baru.');
}

respond(true, 'Password berhasil diganti. Tutup lalu buka kembali browser (atau gunakan mode private baru) dan login dengan password baru.');
