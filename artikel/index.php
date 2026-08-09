<?php
/**
 * Halaman indeks Artikel — file fisik (bukan lewat tabel "pages" /
 * page-router.php), persis seperti /portofolio/, /faq/, /kontak/,
 * supaya selalu sinkron dengan artikel yang dikelola lewat tab admin
 * "Artikel" tanpa perlu "republish" manual.
 */
require_once __DIR__ . '/../inc/db.php';
require_once __DIR__ . '/../inc/render-partials.php';

try {
    $pdo = get_db();
    $business = $pdo->query('SELECT * FROM business WHERE id = 1')->fetch();
    if (!$business) {
        http_response_code(500);
        exit('Konfigurasi bisnis belum ada.');
    }
    $areasRaw = $pdo->query('SELECT * FROM areas ORDER BY sort_order, id')->fetchAll();
} catch (Exception $e) {
    http_response_code(500);
    exit('Terjadi kesalahan server.');
}

// Artikel dibungkus try/catch terpisah: kolom cover_image atau tabel
// pages mungkin belum sepenuhnya termigrasi -- kalau gagal, halaman
// tetap tampil dengan status kosong alih-alih error total.
try {
    $articles = $pdo->query("SELECT * FROM pages WHERE type = 'article' AND status = 'published' ORDER BY sort_order, id")->fetchAll();
} catch (Exception $e) {
    $articles = [];
}

$areas = array_map(function ($r) {
    return ['name' => $r['name'], 'link' => $r['link']];
}, $areasRaw);

$meta = [
    'title' => 'Panduan Kolam Renang',
    'meta_title' => 'Panduan & Artikel Kolam Renang | Jasa Kolam Renang Bogor',
    'meta_description' => 'Kumpulan artikel panduan seputar perawatan, renovasi, dan perbaikan kolam renang dari tim Jasa Kolam Renang Bogor.',
    'intro' => 'Tips dan panduan praktis seputar perawatan, renovasi, dan perbaikan kolam renang.',
    'url_path' => '/artikel/'
];

render_head($meta, $business);
render_header_nav($business);
render_local_business_ld($business, $areas);
render_breadcrumbs([['Beranda', '/'], ['Artikel', null]], $business);
?>

<section class="hero">
  <div class="container">
    <div style="max-width:720px">
      <span class="hero-badge">Panduan Kolam Renang</span>
      <h1><?= h($meta['title']) ?></h1>
      <p class="lead"><?= h($meta['intro']) ?></p>
    </div>
  </div>
</section>

<section>
  <div class="container">
    <?php if (!empty($articles)): ?>
    <div class="portfolio-grid">
      <?php foreach ($articles as $item): ?>
      <a class="portfolio-card" href="<?= h($item['url_path']) ?>" style="display:block;color:inherit">
        <div class="portfolio-thumb">
          <?php if (!empty($item['cover_image'])): ?>
          <img src="/<?= h(ltrim($item['cover_image'], '/')) ?>" alt="<?= h($item['title']) ?>" loading="lazy">
          <?php else: ?>
          <?= placeholder_svg($item['title'], '#1478c8', '#00b8d9') ?>
          <?php endif; ?>
        </div>
        <div class="portfolio-body">
          <h3><?= h($item['title']) ?></h3>
          <p><?= h($item['intro']) ?></p>
        </div>
      </a>
      <?php endforeach; ?>
    </div>
    <?php else: ?>
    <div class="empty-state">
      <h4 style="margin:0">Artikel Segera Hadir</h4>
      <p>Kami sedang menyiapkan kumpulan panduan kolam renang yang bermanfaat.</p>
    </div>
    <?php endif; ?>
  </div>
</section>

<?php
render_cta_band('Butuh Bantuan Langsung dari Ahlinya?', 'Konsultasikan kebutuhan kolam renang Anda, gratis tanpa biaya survei awal.', $business);
render_footer($business);
