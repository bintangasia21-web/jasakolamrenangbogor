<?php
/** @var array $page */
/** @var array $business */
render_breadcrumbs([['Beranda', '/index.html'], ['Layanan', '/index.html#layanan'], [$page['title'], null]], $business);
?>
<section class="hero">
  <div class="container">
    <div style="max-width:720px">
      <span class="hero-badge">Layanan Kolam Renang</span>
      <h1><?= h($page['h1'] ?: $page['title']) ?></h1>
      <p class="lead"><?= h($page['intro']) ?></p>
      <div class="hero-actions">
        <a href="https://wa.me/<?= h($business['whatsapp']) ?>" class="btn btn-white">Konsultasi Gratis via WhatsApp</a>
      </div>
    </div>
  </div>
</section>

<section>
  <div class="container">
    <?= $page['content'] ?>
  </div>
</section>

<?php render_faq_block($page['faq'], $business, $page['url_path']); ?>
<?php render_cta_band($page['title'], 'Tim kami siap survei lokasi dan memberikan estimasi biaya tanpa biaya awal.', $business); ?>

<script type="application/ld+json">
<?= json_encode([
    '@context' => 'https://schema.org',
    '@type' => 'Service',
    'name' => $page['title'],
    'description' => $page['intro'],
    'provider' => ['@type' => 'LocalBusiness', 'name' => $business['name']],
    'areaServed' => 'Bogor'
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?>
</script>
