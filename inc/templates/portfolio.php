<?php
/** @var array $page */
/** @var array $business */
render_breadcrumbs([['Beranda', '/'], ['Portofolio', '/portofolio/'], [$page['title'], null]], $business);
?>
<section class="hero">
  <div class="container">
    <div style="max-width:720px">
      <span class="hero-badge">Portofolio<?= $page['area_ref'] ? ' — ' . h($page['area_ref']) : '' ?></span>
      <h1><?= h($page['h1'] ?: $page['title']) ?></h1>
      <p class="lead"><?= h($page['intro']) ?></p>
      <div class="hero-actions">
        <a href="https://wa.me/<?= h($business['whatsapp']) ?>" class="btn btn-white">Konsultasi Proyek Serupa</a>
      </div>
    </div>
  </div>
</section>

<?php if (!empty($page['cover_image'])): ?>
<section>
  <div class="container" style="max-width:800px">
    <img src="/<?= h(ltrim($page['cover_image'], '/')) ?>" alt="<?= h($page['title']) ?>" style="width:100%;border-radius:var(--radius-md);box-shadow:var(--shadow-sm)" loading="lazy">
  </div>
</section>
<?php endif; ?>

<section>
  <div class="container">
    <?= $page['content'] ?>
  </div>
</section>

<?php render_cta_band('Punya Proyek Kolam Renang Serupa?', 'Konsultasikan kebutuhan Anda, gratis tanpa biaya survei awal.', $business); ?>
