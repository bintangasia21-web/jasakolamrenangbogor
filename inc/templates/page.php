<?php
/** @var array $page */
/** @var array $business */
render_breadcrumbs([['Beranda', '/'], [$page['title'], null]], $business);
?>
<section class="hero">
  <div class="container">
    <div style="max-width:720px">
      <h1><?= h($page['h1'] ?: $page['title']) ?></h1>
      <p class="lead"><?= h($page['intro']) ?></p>
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
  <div class="container" style="max-width:800px">
    <?= $page['content'] ?>
  </div>
</section>

<?php render_faq_block($page['faq'], $business, $page['url_path']); ?>
<?php render_cta_band('Ada Pertanyaan Lain?', 'Hubungi kami langsung, kami siap membantu.', $business); ?>
