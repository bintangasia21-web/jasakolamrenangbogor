<?php
/** @var array $page */
/** @var array $business */
render_breadcrumbs([['Beranda', '/index.html'], ['Artikel', '/artikel/'], [$page['title'], null]], $business);
?>
<section class="hero">
  <div class="container">
    <div style="max-width:720px">
      <span class="hero-badge">Artikel Edukasi</span>
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
<?php render_cta_band('Butuh Bantuan Langsung dari Ahlinya?', 'Tim kami siap konsultasi gratis untuk kebutuhan kolam renang Anda.', $business); ?>

<script type="application/ld+json">
<?= json_encode([
    '@context' => 'https://schema.org',
    '@type' => 'Article',
    'headline' => $page['title'],
    'description' => $page['intro'],
    'author' => ['@type' => 'Organization', 'name' => $business['name']],
    'publisher' => ['@type' => 'Organization', 'name' => $business['name']],
    'dateModified' => $page['updated_at']
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?>
</script>
