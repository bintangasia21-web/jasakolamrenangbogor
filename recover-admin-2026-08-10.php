<?php
/**
 * Halaman pemulihan akses admin sekali-pakai. Dipakai karena admin lupa
 * username & password login (admin.html) dan percobaan edit .htpasswd
 * manual lewat File Manager hPanel berkali-kali gagal menyentuh file
 * yang benar (lihat debug-auth-path.php).
 *
 * SENGAJA tidak dilindungi Basic Auth (memang untuk dipakai justru saat
 * admin tidak bisa login sama sekali) -- sebagai gantinya, halaman ini
 * MENGUNCI DIRI SENDIRI (self-disable) setelah berhasil dipakai satu
 * kali lewat file penanda "recover-admin.lock", supaya tidak bisa
 * dipakai ulang oleh siapa pun setelah admin berhasil masuk. File ini
 * dan lock-nya dihapus dari server setelah login dikonfirmasi berhasil.
 *
 * Alur: GET menampilkan form sederhana (username & password baru boleh
 * ditentukan sendiri oleh admin), POST menulis ulang .htpasswd langsung
 * di path yang sudah dipastikan benar lewat debug-auth-path.php.
 */
$lockFile = __DIR__ . '/recover-admin.lock';
$htpasswdFile = __DIR__ . '/.htpasswd';

function render_page($message = '', $success = false) {
    global $lockFile;
    $used = file_exists($lockFile);
    ?>
    <!DOCTYPE html>
    <html lang="id">
    <head>
    <meta charset="UTF-8">
    <title>Pemulihan Akses Admin</title>
    <style>
      body{font-family:system-ui,sans-serif;max-width:480px;margin:60px auto;padding:0 20px;color:#222}
      label{display:block;margin-top:16px;font-weight:600}
      input{width:100%;padding:10px;margin-top:6px;box-sizing:border-box;border:1px solid #ccc;border-radius:6px;font-size:16px}
      button{margin-top:24px;padding:12px 20px;background:#1478c8;color:#fff;border:none;border-radius:6px;font-size:16px;cursor:pointer}
      .msg{padding:12px;border-radius:6px;margin-top:16px}
      .ok{background:#e6f7ec;color:#146c34}
      .err{background:#fdecec;color:#a3231b}
    </style>
    </head>
    <body>
    <h2>Pemulihan Akses Admin</h2>
    <?php if ($message): ?>
      <p class="msg <?= $success ? 'ok' : 'err' ?>"><?= htmlspecialchars($message) ?></p>
    <?php endif; ?>
    <?php if ($used && !$success): ?>
      <p>Halaman ini sudah pernah dipakai dan terkunci demi keamanan. Kalau masih butuh reset lagi, hubungi developer.</p>
    <?php elseif (!$success): ?>
      <form method="post">
        <label>Username admin baru
          <input type="text" name="new_username" required value="bintangasia21@gmail.com">
        </label>
        <label>Password admin baru (minimal 8 karakter)
          <input type="password" name="new_password" required minlength="8">
        </label>
        <button type="submit">Simpan & Buka Akses</button>
      </form>
    <?php endif; ?>
    </body>
    </html>
    <?php
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (file_exists($lockFile)) {
        render_page('Halaman ini sudah pernah dipakai sebelumnya dan terkunci demi keamanan.', false);
        exit;
    }

    $username = trim($_POST['new_username'] ?? '');
    $password = $_POST['new_password'] ?? '';

    if ($username === '' || strpos($username, ':') !== false) {
        render_page('Username tidak valid (tidak boleh kosong atau mengandung ":").', false);
        exit;
    }
    if (strlen($password) < 8) {
        render_page('Password minimal 8 karakter.', false);
        exit;
    }

    $hash = '{SHA}' . base64_encode(sha1($password, true));
    $line = $username . ':' . $hash . "\n";

    if (file_put_contents($htpasswdFile, $line, LOCK_EX) === false) {
        render_page('Gagal menulis file .htpasswd. Cek izin file di server.', false);
        exit;
    }

    file_put_contents($lockFile, 'used at ' . date('c') . "\n");

    render_page('Berhasil! Username & password admin sudah diperbarui. Silakan login di /admin.html dengan kredensial baru Anda (gunakan mode private/incognito baru).', true);
    exit;
}

render_page();
