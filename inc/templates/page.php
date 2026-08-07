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

<section>
  <div class="container" style="max-width:800px">
    <?= $page['content'] ?>
  </div>
</section>

<?php render_faq_block($page['faq'], $business, $page['url_path']); ?>
<?php render_cta_band('Ada Pertanyaan Lain?', 'Hubungi kami langsung, kami siap membantu.', $business); ?>
