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

<?php
  // Sebagian halaman lama tersimpan dengan body yang mengulang persis
  // teks intro di atas (bug lama di portfolio_sync_seo_page() saat
  // deskripsi cuma satu paragraf). Bandingkan versi teks-polos supaya
  // section ini disembunyikan untuk data lama yang masih duplikat,
  // tanpa perlu migrasi/re-save data di database.
  $bodyPlain = trim(preg_replace('/\s+/', ' ', strip_tags((string) $page['content'])));
  $introPlain = trim(preg_replace('/\s+/', ' ', (string) $page['intro']));
  $showBody = $bodyPlain !== '' && $bodyPlain !== $introPlain;
?>
<?php if ($showBody): ?>
<section>
  <div class="container">
    <?= $page['content'] ?>
  </div>
</section>
<?php endif; ?>

<?php
  // Proyek lain untuk internal linking -- exclude proyek yang sedang
  // dilihat lewat detail_link (sumber kebenaran kepemilikan halaman,
  // sama seperti dipakai portfolio_sync_seo_page()), bukan lewat title.
  $otherProjects = [];
  try {
      $stmt = $pdo->prepare(
          "SELECT title, area, description, image, color1, color2, detail_link FROM portfolio
           WHERE detail_link IS NOT NULL AND detail_link != '' AND detail_link != :current
           ORDER BY sort_order, id LIMIT 3"
      );
      $stmt->execute([':current' => $page['url_path']]);
      $otherProjects = $stmt->fetchAll();
  } catch (Exception $e) {
      $otherProjects = [];
  }
?>
<?php if (!empty($otherProjects)): ?>
<section class="section-alt">
  <div class="container">
    <div class="section-head">
      <span class="eyebrow">Proyek Lainnya</span>
      <h2>Lihat Proyek Kolam Renang Lain</h2>
    </div>
    <div class="portfolio-grid">
      <?php foreach ($otherProjects as $item): ?>
      <a class="portfolio-card" href="<?= h($item['detail_link']) ?>" style="display:block;color:inherit">
        <div class="portfolio-thumb">
          <?= !empty($item['image'])
              ? '<img src="' . h($item['image']) . '" alt="' . h($item['title']) . '" loading="lazy">'
              : placeholder_svg($item['title'], $item['color1'] ?: '#1478c8', $item['color2'] ?: '#00b8d9') ?>
        </div>
        <div class="portfolio-body">
          <span class="tag"><?= h($item['area']) ?></span>
          <h3><?= h($item['title']) ?></h3>
          <p><?= h(short_desc($item['description'])) ?></p>
          <span class="portfolio-link">Lihat Detail <span class="arrow">&rarr;</span></span>
        </div>
      </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<?php render_cta_band('Punya Proyek Kolam Renang Serupa?', 'Konsultasikan kebutuhan Anda, gratis tanpa biaya survei awal.', $business); ?>
