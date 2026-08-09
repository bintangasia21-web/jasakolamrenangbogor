<?php
/** @var array $page */
/** @var array $business */
render_breadcrumbs([['Beranda', '/'], ['Area Layanan', '/area-layanan/'], [$page['title'], null]], $business);
$areaName = $page['area_ref'] ?: $page['title'];
?>
<section class="hero">
  <div class="container">
    <div style="max-width:720px">
      <span class="hero-badge">Area Layanan: <?= h($areaName) ?></span>
      <h1><?= h($page['h1'] ?: $page['title']) ?></h1>
      <p class="lead"><?= h($page['intro']) ?></p>
      <div class="hero-actions">
        <a href="https://wa.me/<?= h($business['whatsapp']) ?>" class="btn btn-white">Konsultasi Gratis via WhatsApp</a>
        <a href="/area-layanan/" class="btn btn-outline">Lihat Area Lain</a>
      </div>
    </div>
  </div>
</section>

<section>
  <div class="container">
    <?= $page['content'] ?>
  </div>
</section>

<?php
// Galeri foto khusus halaman area ini (bukan galeri global) — dibungkus
// try/catch sendiri supaya kalau tabel area_photos belum ada (mis. skema
// belum dijalankan ulang), halaman area tetap tampil normal tanpa galeri.
try {
    $stmt = $pdo->prepare('SELECT * FROM area_photos WHERE area_link = :link ORDER BY sort_order, id');
    $stmt->execute([':link' => $page['url_path']]);
    $areaPhotos = $stmt->fetchAll();
} catch (Exception $e) {
    $areaPhotos = [];
}
?>
<?php if (!empty($areaPhotos)): ?>
<section class="section-alt">
  <div class="container">
    <div class="section-head">
      <span class="eyebrow">Galeri</span>
      <h2>Foto Pekerjaan di <?= h($areaName) ?></h2>
    </div>
    <div class="portfolio-grid">
      <?php foreach ($areaPhotos as $ap): ?>
      <div class="portfolio-card">
        <div class="portfolio-thumb"><img src="/<?= h(ltrim($ap['photo'], '/')) ?>" alt="<?= h($ap['caption'] ?: $areaName) ?>" loading="lazy"></div>
        <?php if (!empty($ap['caption'])): ?>
        <div class="portfolio-body"><p><?= h($ap['caption']) ?></p></div>
        <?php endif; ?>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<?php render_faq_block($page['faq'], $business, $page['url_path']); ?>
<?php render_cta_band('Konsultasikan Kebutuhan Kolam Renang Anda di ' . $areaName, 'Tim kami siap survei lokasi dan memberikan estimasi biaya tanpa biaya awal.', $business); ?>

<script type="application/ld+json">
<?= json_encode([
    '@context' => 'https://schema.org',
    '@type' => 'Service',
    'name' => 'Perawatan Kolam Renang ' . $areaName,
    'serviceType' => 'Perawatan Kolam Renang',
    'description' => $page['intro'],
    'provider' => ['@type' => 'LocalBusiness', 'name' => $business['name']],
    'areaServed' => $areaName
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?>
</script>
