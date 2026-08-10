<?php
/**
 * Dashboard admin sederhana (server-rendered, bukan single-page app).
 * Titik masuk baru untuk fitur-fitur yang dipindah dari admin.html ke
 * halaman PHP tradisional satu-per-satu (mulai dari Portfolio). Fitur
 * yang belum dipindah tetap dikelola lewat admin.html.
 */
require_once __DIR__ . '/inc/db.php';

try {
    $pdo = get_db();
    $portfolioCount = (int) $pdo->query('SELECT COUNT(*) FROM portfolio')->fetchColumn();
} catch (Exception $e) {
    $portfolioCount = 0;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dashboard — Panel Admin</title>
<meta name="robots" content="noindex, nofollow">
<link rel="icon" href="assets/img/favicon.svg" type="image/svg+xml">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/css/style.css">
<link rel="stylesheet" href="assets/css/admin.css">
</head>
<body class="admin-body">

<div class="admin-shell">
  <aside class="admin-sidebar">
    <div class="admin-brand">
      <span class="brand-mark"><svg viewBox="0 0 24 24" fill="none" stroke="#ffffff" stroke-width="2" stroke-linecap="round"><path d="M2 17c1.5 1.2 3 1.2 4.5 0s3-1.2 4.5 0 3 1.2 4.5 0 3-1.2 4.5 0"/><path d="M12 13V4l3 2"/></svg></span>
      Panel Admin
    </div>
    <nav class="admin-nav">
      <a href="admin.php" style="display:block;padding:11px 12px;border-radius:8px;background:var(--blue-600);color:#fff;text-decoration:none;font-size:.92rem;font-weight:600;margin-bottom:4px">Dashboard</a>
      <a href="edit-portfolio.php" style="display:block;padding:11px 12px;border-radius:8px;color:rgba(255,255,255,.75);text-decoration:none;font-size:.92rem;font-weight:600;margin-bottom:4px">Portfolio</a>
      <a href="admin.html" style="display:block;padding:11px 12px;border-radius:8px;color:rgba(255,255,255,.75);text-decoration:none;font-size:.92rem;font-weight:600;margin-bottom:4px">Fitur Lainnya (panel lama)</a>
    </nav>
  </aside>

  <main class="admin-main">
    <h2>Dashboard</h2>
    <p class="panel-desc">Panel admin sedang dipindah bertahap dari satu halaman besar (admin.html) ke halaman terpisah per fungsi — lebih sederhana &amp; lebih andal. Portfolio sudah pindah ke pola baru; fitur lain masih di panel lama untuk sekarang.</p>

    <div class="admin-card">
      <div class="admin-card-head"><h3>Portfolio</h3><span class="status-badge published">Pola Baru</span></div>
      <p style="color:var(--gray-600);font-size:.9rem;margin:0 0 14px"><?= $portfolioCount ?> proyek tersimpan. Form PHP biasa — satu kali simpan langsung mencakup foto &amp; halaman detail SEO.</p>
      <a href="edit-portfolio.php" class="btn btn-primary btn-sm">Kelola Portfolio &rarr;</a>
    </div>

    <div class="admin-card">
      <div class="admin-card-head"><h3>Fitur Lainnya</h3><span class="status-badge draft">Panel Lama</span></div>
      <p style="color:var(--gray-600);font-size:.9rem;margin:0 0 14px">Info Bisnis, Area Layanan, FAQ, Testimonial, Layanan, Artikel, Halaman Utama, dan Halaman Kombinasi &amp; Lainnya masih dikelola lewat panel admin lama.</p>
      <a href="admin.html" class="btn btn-secondary btn-sm">Buka Panel Lama &rarr;</a>
    </div>
  </main>
</div>
</body>
</html>
