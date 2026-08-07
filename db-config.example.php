<?php
/**
 * TEMPLATE konfigurasi database — aman untuk di-commit ke Git (tidak ada
 * kredensial asli di sini).
 *
 * CARA PAKAI:
 * 1. Salin file ini menjadi "db-config.php" (nama file harus persis itu),
 *    di folder yang sama (public_html), LEWAT File Manager Hostinger
 *    langsung di server — JANGAN lewat Git/commit.
 * 2. Isi nilai di bawah dengan kredensial asli dari hPanel
 *    (Databases -> MySQL Databases).
 * 3. db-config.php sudah otomatis masuk .gitignore, jadi tidak akan
 *    pernah ikut ter-commit walau Anda push perubahan lain nanti.
 */
define('DB_HOST', 'localhost');
define('DB_NAME', 'namadatabase_anda');
define('DB_USER', 'username_database_anda');
define('DB_PASS', 'password_database_anda');
