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

<?php render_faq_block($page['faq'], $business, $page['url_path']); ?>
<?php render_cta_band('Konsultasikan Kebutuhan Kolam Renang Anda di ' . $areaName, 'Tim kami siap survei lokasi dan memberikan estimasi biaya tanpa biaya awal.', $business); ?>
