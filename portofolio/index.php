<?php
/**
 * Halaman indeks Portofolio — file fisik (bukan lewat tabel "pages" /
 * page-router.php), persis seperti index.php di root, supaya halaman
 * ini SELALU sinkron dengan tabel "portfolio" yang dikelola admin
 * (tab Foto/Portofolio) tanpa perlu "republish" manual tiap ada foto
 * baru diunggah atau dihapus.
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
    $portfolio = $pdo->query('SELECT * FROM portfolio ORDER BY sort_order, id')->fetchAll();
} catch (Exception $e) {
    http_response_code(500);
    exit('Terjadi kesalahan server.');
}

$areas = array_map(function ($r) {
    return ['name' => $r['name'], 'link' => $r['link']];
}, $areasRaw);
$areaLinkMap = array_column($areas, 'link', 'name');

$s = get_page_sections('portofolio');

$meta = [
    'title' => 'Portofolio Kami',
    'meta_title' => 'Portofolio Kolam Renang | Jasa Kolam Renang Bogor',
    'meta_description' => 'Galeri contoh pekerjaan pembuatan, perawatan, dan renovasi kolam renang kami di berbagai area Bogor.',
    'intro' => page_text($s, 'lead', 'Gambaran jenis proyek yang telah kami kerjakan di berbagai area Bogor.'),
    'url_path' => '/portofolio/'
];

render_head($meta, $business);
render_header_nav($business);
render_local_business_ld($business, $areas);
render_breadcrumbs([['Beranda', '/'], ['Portofolio', null]], $business);
?>

<section class="hero">
  <div class="container">
    <div style="max-width:720px">
      <span class="hero-badge">Portofolio</span>
      <h1><?= h(page_text($s, 'h1', 'Contoh Pekerjaan Kami')) ?></h1>
      <p class="lead"><?= h($meta['intro']) ?></p>
    </div>
  </div>
</section>

<section>
  <div class="container">
    <div class="portfolio-grid">
      <?php foreach ($portfolio as $item): ?>
      <?php
        // Prioritaskan halaman detail studi-kasus proyek (kalau sudah
        // dibuatkan lewat tab admin Portfolio) di atas tautan ke halaman
        // area generik -- lebih spesifik & lebih bermanfaat bagi pembaca.
        $detailLink = $item['detail_link'] ?? null;
        $targetLink = $detailLink ?: ($areaLinkMap[$item['area']] ?? null);
        $linkLabel = $detailLink ? 'Lihat Detail' : 'Lihat Layanan Area';
        $card = '<div class="portfolio-thumb">'
            . (!empty($item['image'])
                ? '<img src="/' . h(ltrim($item['image'], '/')) . '" alt="' . h($item['title']) . '" loading="lazy">'
                : placeholder_svg($item['title'], $item['color1'] ?: '#1478c8', $item['color2'] ?: '#00b8d9'))
            . '</div><div class="portfolio-body"><span class="tag">' . h($item['area']) . '</span>'
            . '<h3>' . h($item['title']) . '</h3><p>' . h($item['description']) . '</p>'
            . ($targetLink ? '<span class="portfolio-link">' . h($linkLabel) . ' <span class="arrow">&rarr;</span></span>' : '')
            . '</div>';
      ?>
      <?php if ($targetLink): ?>
      <a class="portfolio-card" href="<?= h($targetLink) ?>" style="display:block;color:inherit"><?= $card ?></a>
      <?php else: ?>
      <div class="portfolio-card"><?= $card ?></div>
      <?php endif; ?>
      <?php endforeach; ?>
    </div>
    <?php if (empty($portfolio)): ?>
    <p class="portfolio-note">Belum ada item portofolio. Tambahkan lewat panel admin (tab Portfolio).</p>
    <?php else: ?>
    <p class="portfolio-note">Kartu dengan halaman detail proyek atau area yang cocok dapat diklik untuk melihat selengkapnya.</p>
    <?php endif; ?>
  </div>
</section>

<?php
render_cta_band('Punya Proyek Kolam Renang Serupa?', 'Konsultasikan kebutuhan Anda, gratis tanpa biaya survei awal.', $business);
render_footer($business);
