<?php
/** @var array $page */
/** @var array $business */
$areaName = $page['area_ref'] ?: '';
$serviceName = $page['service_ref'] ?: $page['title'];
// URL kombinasi berpola /area/{slug}/{layanan}/ — area induk = potong 1 segmen terakhir
$segments = array_values(array_filter(explode('/', $page['url_path'])));
array_pop($segments);
$areaUrl = '/' . implode('/', $segments) . '/';

render_breadcrumbs([
    ['Beranda', '/'],
    ['Area Layanan', '/area-layanan/'],
    [$areaName, $areaUrl],
    [$serviceName, null]
], $business);
?>
<section class="hero">
  <div class="container">
    <div style="max-width:720px">
      <span class="hero-badge">Layanan di <?= h($areaName) ?></span>
      <h1><?= h($page['h1'] ?: $page['title']) ?></h1>
      <p class="lead"><?= h($page['intro']) ?></p>
      <div class="hero-actions">
        <a href="https://wa.me/<?= h($business['whatsapp']) ?>" class="btn btn-white">Konsultasi Gratis via WhatsApp</a>
        <a href="<?= h($areaUrl) ?>" class="btn btn-outline">Lihat Semua Layanan di <?= h($areaName) ?></a>
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
<?php render_cta_band($serviceName . ' di ' . $areaName, 'Tim kami siap survei lokasi dan memberikan estimasi biaya tanpa biaya awal.', $business); ?>
