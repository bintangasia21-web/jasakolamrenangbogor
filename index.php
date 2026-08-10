<?php
/**
 * Beranda — server-rendered penuh dari database (bukan lagi file statis
 * yang diisi lewat JS). Konsisten dengan seluruh halaman lain di situs
 * ini (lihat page-router.php + inc/templates/*.php) supaya SEO andal
 * (HTML lengkap langsung dari server) dan tidak ada lagi dua sumber
 * kebenaran yang harus disinkronkan manual.
 */
require_once __DIR__ . '/inc/db.php';
require_once __DIR__ . '/inc/render-partials.php';

try {
    $pdo = get_db();
    $business = $pdo->query('SELECT * FROM business WHERE id = 1')->fetch();
    if (!$business) {
        http_response_code(500);
        exit('Konfigurasi bisnis belum ada.');
    }
    $areasRaw = $pdo->query('SELECT * FROM areas ORDER BY sort_order, id')->fetchAll();
    $faq = array_map(function ($r) {
        return ['q' => $r['question'], 'a' => $r['answer']];
    }, $pdo->query('SELECT * FROM faq ORDER BY sort_order, id')->fetchAll());
    $portfolio = $pdo->query('SELECT * FROM portfolio ORDER BY sort_order, id')->fetchAll();
} catch (Exception $e) {
    http_response_code(500);
    exit('Terjadi kesalahan server.');
}

// Layanan dinamis & testimoni dibungkus try/catch TERPISAH dari blok di atas:
// kalau setup-schema.php belum sempat dijalankan ulang (tabel/kolom baru
// belum ada), beranda tetap tampil normal (fallback ke array kosong) alih-
// alih ikut menampilkan error 500 total untuk seluruh halaman.
try {
    $services = $pdo->query("SELECT * FROM pages WHERE type = 'service' AND status = 'published' ORDER BY sort_order, title")->fetchAll();
} catch (Exception $e) {
    $services = [];
}
try {
    $testimonials = $pdo->query("SELECT * FROM testimonials WHERE status = 'published' ORDER BY sort_order, id")->fetchAll();
} catch (Exception $e) {
    $testimonials = [];
}
try {
    $s = get_page_sections('home');
} catch (Exception $e) {
    $s = [];
}
try {
    $articles = $pdo->query("SELECT * FROM pages WHERE type = 'article' AND status = 'published' ORDER BY sort_order, id LIMIT 3")->fetchAll();
} catch (Exception $e) {
    $articles = [];
}

$areas = array_map(function ($r) {
    return [
        'name' => $r['name'],
        'link' => $r['link'],
        'desc' => $r['description'],
        'lat' => $r['lat'] !== null ? (float) $r['lat'] : null,
        'lng' => $r['lng'] !== null ? (float) $r['lng'] : null,
        'priority' => (bool) $r['priority']
    ];
}, $areasRaw);

$waHref = 'https://wa.me/' . $business['whatsapp'];

// Daftar masalah kolam renang yang paling sering dikeluhkan pelanggan,
// tiap item ditautkan ke halaman layanan terkait yang sudah live (dicek
// manual agar tidak ada tautan mati).
$masalahList = [
    ['title' => 'Kolam Bocor / Rembes', 'desc' => 'Air kolam terus berkurang meski tidak digunakan — tanda kebocoran struktur atau pipa yang perlu segera diperiksa.', 'link' => '/layanan/perbaikan-kebocoran-kolam/'],
    ['title' => 'Air Kolam Keruh & Tidak Jernih', 'desc' => 'Kualitas air menurun akibat kadar kimia tidak seimbang, membuat kolam terlihat kusam dan kurang nyaman dipakai.', 'link' => '/layanan/penyeimbangan-kimia-air-kolam/'],
    ['title' => 'Keramik Kolam Pecah / Lepas', 'desc' => 'Keramik retak atau terlepas dari dinding maupun dasar kolam, mengganggu tampilan sekaligus berisiko melukai pengguna.', 'link' => '/layanan/penggantian-keramik-kolam/'],
    ['title' => 'Filter & Pompa Tidak Optimal', 'desc' => 'Pompa melemah atau filter kotor membuat sirkulasi air tidak maksimal dan air lebih cepat kotor kembali.', 'link' => '/layanan/instalasi-filter-pompa/'],
    ['title' => 'Pasir Filter Sudah Lama Tidak Diganti', 'desc' => 'Pasir filter yang jenuh menurunkan efektivitas penyaringan, meski pompa masih menyala normal.', 'link' => '/layanan/penggantian-pasir-filter/'],
    ['title' => 'Sirkulasi Air Tidak Lancar', 'desc' => 'Aliran air terasa lemah di beberapa titik kolam — tanda ada masalah pada sistem pipa atau katup sirkulasi.', 'link' => '/layanan/perbaikan-sistem-sirkulasi-air/'],
    ['title' => 'Dasar Kolam Kotor & Berlumut', 'desc' => 'Endapan kotoran dan lumut menumpuk di dasar kolam sehingga permukaan menjadi licin dan tidak higienis.', 'link' => '/layanan/vacuum-pembersihan-dasar-kolam/'],
    ['title' => 'Lampu Kolam Mati / Redup', 'desc' => 'Pencahayaan kolam bermasalah membuat kolam kurang aman dan kurang nyaman digunakan pada malam hari.', 'link' => '/layanan/perbaikan-lampu-kolam-renang/'],
    ['title' => 'Rembesan pada Struktur Kolam', 'desc' => 'Lapisan waterproofing yang sudah menurun menyebabkan rembesan pada dinding atau area sekitar kolam.', 'link' => '/layanan/waterproofing-kolam-renang/'],
    ['title' => 'Kolam Cepat Kotor Saat Musim Hujan', 'desc' => 'Air hujan membawa kotoran dan menurunkan kualitas air kolam secara drastis dalam waktu singkat.', 'link' => '/layanan/perawatan-kolam-musim-hujan/']
];

$jenisPelangganList = [
    ['title' => 'Rumah Tinggal', 'desc' => 'Kolam renang pribadi untuk kenyamanan keluarga sehari-hari.'],
    ['title' => 'Villa', 'desc' => 'Kolam renang villa yang tampil menarik dan terawat sepanjang waktu.'],
    ['title' => 'Hotel', 'desc' => 'Fasilitas kolam renang hotel dengan standar kebersihan dan keamanan tamu.'],
    ['title' => 'Penginapan', 'desc' => 'Kolam renang penginapan/guest house yang perlu perawatan rutin terjadwal.'],
    ['title' => 'Sekolah', 'desc' => 'Kolam renang fasilitas pendidikan yang aman untuk aktivitas siswa.'],
    ['title' => 'Fasilitas Komersial', 'desc' => 'Kolam renang gedung komersial, klub, atau fasilitas umum lainnya.']
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Jasa Kolam Renang Bogor | Pembuatan, Perawatan &amp; Renovasi Kolam Renang</title>
<meta name="description" content="<?= h($business['description']) ?>">
<link rel="canonical" href="<?= h(rtrim($business['domain'], '/')) ?>/">
<meta name="robots" content="index, follow">
<meta property="og:type" content="website">
<meta property="og:title" content="Jasa Kolam Renang Bogor | Pembuatan, Perawatan &amp; Renovasi Kolam Renang">
<meta property="og:description" content="<?= h($business['description']) ?>">
<meta property="og:url" content="<?= h(rtrim($business['domain'], '/')) ?>/">
<meta property="og:image" content="<?= h(rtrim($business['domain'], '/')) ?>/assets/img/og-image.svg">
<meta name="twitter:card" content="summary_large_image">
<link rel="icon" href="/assets/img/favicon.svg" type="image/svg+xml">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="/assets/css/style.css">
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
</head>
<body>

<?php render_header_nav($business); ?>

<?php render_local_business_ld($business, $areas); ?>

<section class="hero">
  <div class="container hero-inner">
    <div>
      <span class="hero-badge">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2"><path d="M12 2l3 7h7l-5.5 4.5L18.5 21 12 16.5 5.5 21l2-7.5L2 9h7z"/></svg>
        <?= h(page_text($s, 'hero_badge', 'Terpercaya di Bogor & Sekitarnya')) ?>
      </span>
      <h1><?= h(page_text($s, 'hero_h1', 'Jasa Kolam Renang Bogor untuk Pembangunan, Renovasi & Perawatan')) ?></h1>
      <p class="lead"><?= h(page_text($s, 'hero_lead', 'Kami melayani pembuatan kolam renang baru, perawatan rutin, renovasi & perbaikan, hingga instalasi sistem air — untuk rumah tinggal, villa, dan resort di Sentul, Puncak, Ciawi, Bogor Kota, Cibinong, Yasmin, dan area Bogor lainnya.')) ?></p>
      <div class="hero-actions">
        <a href="<?= h($waHref) ?>" class="btn btn-white">
          <svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 2a10 10 0 0 0-8.6 15L2 22l5.2-1.4A10 10 0 1 0 12 2Zm0 18a8 8 0 0 1-4.1-1.1l-.3-.2-3.1.8.8-3-.2-.3A8 8 0 1 1 12 20Zm4.4-5.9c-.2-.1-1.4-.7-1.6-.8-.2-.1-.4-.1-.5.1s-.6.8-.7.9-.3.1-.5 0a6.6 6.6 0 0 1-1.9-1.2 7.2 7.2 0 0 1-1.3-1.6c-.1-.2 0-.3.1-.4l.4-.4.2-.4v-.3c0-.1-.5-1.3-.7-1.7s-.4-.4-.5-.4h-.4a.9.9 0 0 0-.6.3 2.7 2.7 0 0 0-.8 2 4.7 4.7 0 0 0 1 2.5 10.8 10.8 0 0 0 4.1 3.6c.6.2 1 .4 1.4.5a3.3 3.3 0 0 0 1.5.1 2.5 2.5 0 0 0 1.6-1.1 1.9 1.9 0 0 0 .1-1.1c-.1-.1-.2-.2-.4-.3Z"/></svg>
          Konsultasi Sekarang
        </a>
        <a href="#proyek" class="btn btn-outline">Lihat Proyek</a>
      </div>
      <div class="hero-stats">
        <div><strong><?= (int) $business['yearsExperience'] ?>+</strong><span>Tahun Pengalaman</span></div>
        <div><strong><?= (int) $business['projectsDone'] ?>+</strong><span>Proyek Selesai</span></div>
        <div><strong><?= count($areas) ?>+</strong><span>Area Layanan Utama</span></div>
      </div>
    </div>
    <div class="hero-visual">
      <?php if (!empty($s['hero_image'])): ?>
      <img src="/<?= h(ltrim($s['hero_image'], '/')) ?>" alt="<?= h($business['name']) ?>">
      <?php else: ?>
      <svg viewBox="0 0 420 320" xmlns="http://www.w3.org/2000/svg">
        <rect x="20" y="30" width="380" height="220" rx="18" fill="#ffffff" fill-opacity="0.12"/>
        <rect x="40" y="50" width="340" height="150" rx="10" fill="#ffffff" fill-opacity="0.85"/>
        <path d="M40 170 Q 80 155 120 170 T 200 170 T 280 170 T 360 170 T 380 170 V200 H40 Z" fill="#4fadea"/>
        <path d="M40 185 Q 80 172 120 185 T 200 185 T 280 185 T 360 185 T 380 185 V200 H40 Z" fill="#1e90e0"/>
        <circle cx="330" cy="90" r="26" fill="#ffdf6b"/>
        <rect x="60" y="215" width="300" height="14" rx="7" fill="#ffffff" fill-opacity="0.5"/>
        <rect x="60" y="240" width="200" height="10" rx="5" fill="#ffffff" fill-opacity="0.35"/>
      </svg>
      <?php endif; ?>
    </div>
  </div>
</section>

<?php
$trustItems = [];
if (!empty($business['yearFounded'])) {
    $trustItems[] = ['value' => 'Sejak ' . (int) $business['yearFounded'], 'label' => 'Tahun Berdiri'];
}
if (!empty($business['activeCustomers'])) {
    $trustItems[] = ['value' => $business['activeCustomers'], 'label' => 'Pelanggan Aktif'];
}
if (!empty($business['employeeCount'])) {
    $trustItems[] = ['value' => '±' . (int) $business['employeeCount'], 'label' => 'Tim Profesional'];
}
$trustItems[] = ['value' => ($business['city'] ?: 'Bogor'), 'label' => 'Fokus Wilayah Layanan'];
?>
<section class="trust-bar">
  <div class="container trust-bar-inner">
    <?php foreach ($trustItems as $t): ?>
    <div class="trust-bar-item"><strong><?= h($t['value']) ?></strong><span><?= h($t['label']) ?></span></div>
    <?php endforeach; ?>
  </div>
</section>

<section id="tentang">
  <div class="container">
    <div class="section-head">
      <span class="eyebrow"><?= h(page_text($s, 'tentang_eyebrow', 'Tentang Kami')) ?></span>
      <h2><?= h(page_text($s, 'tentang_h2', 'Tentang Jasa Kolam Renang Bogor')) ?></h2>
      <p><?= h(page_text($s, 'tentang_lead', 'Dipercaya pemilik rumah, villa, dan resort di Bogor untuk pembangunan, renovasi, dan perawatan kolam renang.')) ?></p>
    </div>
    <div class="two-col">
      <div>
        <p style="color:var(--gray-600)">Kami memahami karakteristik cuaca dan kondisi tanah Bogor yang beragam — dari dataran tinggi Puncak hingga area perkotaan padat — sehingga setiap pengerjaan disesuaikan dengan kondisi lapangan.</p>
        <div class="why-grid mt-32">
          <div class="why-item">
            <div class="why-icon"><svg viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 15a4 4 0 1 0 0-8 4 4 0 0 0 0 8Z"/><path d="M12 2v2M12 20v2M4.9 4.9l1.4 1.4M17.7 17.7l1.4 1.4M2 12h2M20 12h2M4.9 19.1l1.4-1.4M17.7 6.3l1.4-1.4"/></svg></div>
            <div>
              <h4>Berpengalaman & Terpercaya</h4>
              <p>Puluhan tahun pengalaman menangani kolam renang berbagai skala di wilayah Bogor.</p>
            </div>
          </div>
          <div class="why-item">
            <div class="why-icon"><svg viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2 3 6v6c0 5 4 8.5 9 10 5-1.5 9-5 9-10V6l-9-4Z"/></svg></div>
            <div>
              <h4>Garansi Pekerjaan</h4>
              <p>Setiap pengerjaan renovasi dan pembuatan kolam disertai garansi tertulis.</p>
            </div>
          </div>
          <div class="why-item">
            <div class="why-icon"><svg viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="6" width="18" height="13" rx="2"/><path d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg></div>
            <div>
              <h4>Harga Transparan</h4>
              <p>Penawaran rinci disampaikan di awal tanpa biaya tersembunyi.</p>
            </div>
          </div>
          <div class="why-item">
            <div class="why-icon"><svg viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M13 2 3 14h7l-1 8 11-14h-8l1-6Z"/></svg></div>
            <div>
              <h4>Respon Cepat</h4>
              <p>Tim siap merespons konsultasi dan jadwal survei dengan cepat via WhatsApp.</p>
            </div>
          </div>
        </div>
      </div>
      <div class="info-box">
        <h4><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 12l2 2 4-4"/><circle cx="12" cy="12" r="10"/></svg>Material & Standar Kerja</h4>
        <p style="color:var(--gray-600)">Kami menggunakan material berkualitas (keramik, liner, sistem filtrasi) dari merek terpercaya dan menerapkan standar pengerjaan yang memperhatikan kekuatan struktur, kemiringan lahan, dan drainase — terutama penting untuk kondisi tanah di kawasan perbukitan Bogor.</p>
        <div class="badge-list">
          <span>Waterproofing Teruji</span>
          <span>Filtrasi Berkualitas</span>
          <span>Tim Berpengalaman</span>
        </div>
        <h4 style="margin-top:20px">Proses Kerja Singkat</h4>
        <div class="badge-list">
          <span>1. Konsultasi & Survei</span>
          <span>2. Penawaran Transparan</span>
          <span>3. Pengerjaan Terjadwal</span>
          <span>4. Quality Check & Garansi</span>
        </div>
      </div>
    </div>
  </div>
</section>

<section id="layanan" class="section-alt">
  <div class="container">
    <div class="section-head">
      <span class="eyebrow"><?= h(page_text($s, 'layanan_eyebrow', 'Layanan Kami')) ?></span>
      <h2><?= h(page_text($s, 'layanan_h2', 'Solusi Lengkap Kolam Renang Anda')) ?></h2>
      <p><?= h(page_text($s, 'layanan_lead', 'Dari kolam baru hingga perawatan berkelanjutan, tim kami menangani setiap tahap dengan standar kerja rapi dan bahan berkualitas.')) ?></p>
    </div>
    <div class="services-grid">
      <?php
      // Fallback ke 4 layanan inti kalau tabel "pages" belum berisi layanan
      // published (mis. sebelum setup-schema.php dijalankan ulang) — supaya
      // section ini tidak pernah tampak kosong.
      $fallbackServices = [
          ['title' => 'Pembuatan Kolam Baru', 'url_path' => '/layanan/pembuatan-kolam-renang-baru/', 'intro' => 'Desain dan konstruksi kolam renang dari nol sesuai lahan dan kebutuhan Anda, mulai dari kolam minimalis rumah tinggal hingga kolam villa berukuran besar.'],
          ['title' => 'Perawatan Rutin', 'url_path' => '/layanan/perawatan-pembersihan-rutin/', 'intro' => 'Program perawatan berkala — pembersihan, pengecekan kualitas air, dan perawatan sistem filtrasi — agar kolam selalu jernih dan siap pakai kapan saja.'],
          ['title' => 'Renovasi & Perbaikan', 'url_path' => '/layanan/renovasi-perbaikan-kolam/', 'intro' => 'Perbaikan kebocoran, keramik pecah, waterproofing, hingga renovasi total kolam lama agar tampil dan berfungsi seperti baru kembali.'],
          ['title' => 'Instalasi Sistem Air', 'url_path' => '/layanan/instalasi-filter-pompa/', 'intro' => 'Pemasangan pompa sirkulasi, filter, sistem sanitasi, dan plumbing kolam renang agar air tetap bersih dan perawatan lebih efisien.']
      ];
      $servicesToShow = !empty($services) ? $services : $fallbackServices;
      foreach ($servicesToShow as $svc):
      ?>
      <div class="service-card">
        <div class="service-icon">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M3 21h18"/><path d="M5 21V9l7-5 7 5v12"/><path d="M9 21v-6h6v6"/></svg>
        </div>
        <h3><a href="<?= h($svc['url_path']) ?>" style="color:inherit"><?= h($svc['title']) ?></a></h3>
        <p><?= h($svc['intro']) ?> <a href="<?= h($svc['url_path']) ?>" style="color:var(--blue-600);font-weight:600">Selengkapnya &rarr;</a></p>
      </div>
      <?php endforeach; ?>
    </div>
    <div class="text-center mt-32">
      <a href="/layanan/" class="btn btn-outline" style="border-color:var(--blue-600);color:var(--blue-600)">Lihat Semua Layanan Kami &rarr;</a>
    </div>
  </div>
</section>

<section id="area">
  <div class="container">
    <div class="section-head">
      <span class="eyebrow"><?= h(page_text($s, 'area_eyebrow', 'Area Layanan')) ?></span>
      <h2><?= h(page_text($s, 'area_h2', 'Melayani Kota Bogor & Kabupaten Bogor serta Sekitarnya')) ?></h2>
      <p><?= h(page_text($s, 'area_lead', 'Klik area di bawah untuk melihat layanan yang disesuaikan dengan karakteristik masing-masing wilayah yang benar-benar kami layani. Zona berwarna hijau pada peta menandai area prioritas layanan kami.')) ?></p>
    </div>
    <div class="area-map-wrap"><div id="area-map"></div></div>
    <div class="area-grid" id="area-grid">
      <?php foreach ($areas as $area): ?>
      <a class="area-card" href="<?= h($area['link'] ?: '/') ?>">
        <div class="area-chip-row"><span class="area-chip"><?= h($area['name']) ?></span><span class="arrow">&rarr;</span></div>
        <h3>Kolam Renang <?= h($area['name']) ?></h3>
        <p><?= h($area['desc']) ?></p>
      </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section id="masalah" class="section-alt">
  <div class="container">
    <div class="section-head">
      <span class="eyebrow"><?= h(page_text($s, 'masalah_eyebrow', 'Masalah Kolam Renang')) ?></span>
      <h2><?= h(page_text($s, 'masalah_h2', 'Masalah yang Paling Sering Kami Tangani')) ?></h2>
      <p><?= h(page_text($s, 'masalah_lead', 'Kenali masalah kolam Anda dan langsung lihat layanan yang paling sesuai untuk menanganinya.')) ?></p>
    </div>
    <div class="services-grid">
      <?php foreach ($masalahList as $m): ?>
      <div class="service-card">
        <div class="service-icon">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M10.3 3.9 1.9 18.2a1.8 1.8 0 0 0 1.5 2.8h17.2a1.8 1.8 0 0 0 1.5-2.8L13.7 3.9a1.8 1.8 0 0 0-3.4 0Z"/><path d="M12 9v4"/><path d="M12 17h.01"/></svg>
        </div>
        <h3><a href="<?= h($m['link']) ?>" style="color:inherit"><?= h($m['title']) ?></a></h3>
        <p><?= h($m['desc']) ?> <a href="<?= h($m['link']) ?>" style="color:var(--blue-600);font-weight:600">Lihat Solusinya &rarr;</a></p>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section id="proyek">
  <div class="container">
    <div class="section-head">
      <span class="eyebrow"><?= h(page_text($s, 'proyek_eyebrow', 'Proyek Nyata')) ?></span>
      <h2><?= h(page_text($s, 'proyek_h2', 'Contoh Pekerjaan Kami')) ?></h2>
      <p><?= h(page_text($s, 'proyek_lead', 'Gambaran jenis proyek yang telah kami kerjakan di berbagai area Bogor.')) ?></p>
    </div>
    <div class="portfolio-grid" id="portfolio-grid">
      <?php foreach ($portfolio as $item): ?>
      <?php
        $detailLink = $item['detail_link'] ?? null;
        $card = '<div class="portfolio-thumb">'
            . (!empty($item['image'])
                ? '<img src="' . h($item['image']) . '" alt="' . h($item['title']) . '" loading="lazy">'
                : placeholder_svg($item['title'], $item['color1'] ?: '#1478c8', $item['color2'] ?: '#00b8d9'))
            . '</div><div class="portfolio-body"><span class="tag">' . h($item['area']) . '</span>'
            . '<h3>' . h($item['title']) . '</h3><p>' . h($item['description']) . '</p></div>';
      ?>
      <?php if ($detailLink): ?>
      <a class="portfolio-card" href="<?= h($detailLink) ?>" style="display:block;color:inherit"><?= $card ?></a>
      <?php else: ?>
      <div class="portfolio-card"><?= $card ?></div>
      <?php endif; ?>
      <?php endforeach; ?>
    </div>
    <?php
      $hasPlaceholderPhoto = false;
      foreach ($portfolio as $item) {
          if (empty($item['image'])) { $hasPlaceholderPhoto = true; break; }
      }
    ?>
    <?php if ($hasPlaceholderPhoto): ?>
    <p class="portfolio-note">Sebagian gambar di atas adalah ilustrasi placeholder. Foto proyek asli dapat ditambahkan melalui panel admin.</p>
    <?php elseif (!empty($portfolio)): ?>
    <p class="portfolio-note">Klik salah satu kartu untuk melihat detail proyek.</p>
    <?php endif; ?>
  </div>
</section>

<section id="jenis-pelanggan" class="section-alt">
  <div class="container">
    <div class="section-head">
      <span class="eyebrow"><?= h(page_text($s, 'jenis_pelanggan_eyebrow', 'Jenis Pelanggan')) ?></span>
      <h2><?= h(page_text($s, 'jenis_pelanggan_h2', 'Melayani Berbagai Jenis Properti')) ?></h2>
      <p><?= h(page_text($s, 'jenis_pelanggan_lead', 'Dari rumah tinggal hingga fasilitas komersial, kami menyesuaikan layanan dengan kebutuhan tiap jenis properti.')) ?></p>
    </div>
    <div class="why-grid">
      <?php foreach ($jenisPelangganList as $jp): ?>
      <div class="why-item">
        <div class="why-icon"><svg viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 21h18"/><path d="M5 21V9l7-5 7 5v12"/><path d="M9 21v-6h6v6"/></svg></div>
        <div>
          <h4><?= h($jp['title']) ?></h4>
          <p><?= h($jp['desc']) ?></p>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section id="panduan">
  <div class="container">
    <div class="section-head">
      <span class="eyebrow"><?= h(page_text($s, 'panduan_eyebrow', 'Panduan Kolam Renang')) ?></span>
      <h2><?= h(page_text($s, 'panduan_h2', 'Tips & Panduan Perawatan Kolam Renang')) ?></h2>
      <p><?= h(page_text($s, 'panduan_lead', 'Artikel panduan seputar perawatan, renovasi, dan perbaikan kolam renang.')) ?></p>
    </div>
    <?php if (!empty($articles)): ?>
    <div class="portfolio-grid">
      <?php foreach ($articles as $item): ?>
      <a class="portfolio-card" href="<?= h($item['url_path']) ?>" style="display:block;color:inherit">
        <div class="portfolio-thumb">
          <?php if (!empty($item['cover_image'])): ?>
          <img src="/<?= h(ltrim($item['cover_image'], '/')) ?>" alt="<?= h($item['title']) ?>" loading="lazy">
          <?php else: ?>
          <?= placeholder_svg($item['title'], '#1478c8', '#00b8d9') ?>
          <?php endif; ?>
        </div>
        <div class="portfolio-body">
          <h3><?= h($item['title']) ?></h3>
          <p><?= h($item['intro']) ?></p>
        </div>
      </a>
      <?php endforeach; ?>
    </div>
    <div class="text-center mt-32">
      <a href="/artikel/" class="btn btn-outline" style="border-color:var(--blue-600);color:var(--blue-600)">Lihat Semua Artikel &rarr;</a>
    </div>
    <?php else: ?>
    <div class="empty-state">
      <h4 style="margin:0">Artikel Segera Hadir</h4>
      <p>Kami sedang menyiapkan kumpulan panduan kolam renang yang bermanfaat. Sementara menunggu, konsultasikan pertanyaan Anda langsung lewat WhatsApp.</p>
      <div class="hero-actions" style="justify-content:center;margin-top:18px">
        <a href="<?= h($waHref) ?>" class="btn btn-primary">Tanya via WhatsApp</a>
      </div>
    </div>
    <?php endif; ?>
  </div>
</section>

<section id="faq" class="section-alt">
  <div class="container">
    <div class="section-head">
      <span class="eyebrow"><?= h(page_text($s, 'faq_eyebrow', 'FAQ')) ?></span>
      <h2><?= h(page_text($s, 'faq_h2', 'Pertanyaan yang Sering Diajukan')) ?></h2>
      <p><?= h(page_text($s, 'faq_lead', 'Belum menemukan jawaban? Hubungi kami langsung melalui WhatsApp.')) ?></p>
    </div>
    <div class="faq-list" id="faq-list">
      <?php foreach ($faq as $i => $item): ?>
      <details class="faq-item"<?= $i === 0 ? ' open' : '' ?>>
        <summary><?= h($item['q']) ?></summary>
        <p><?= h($item['a']) ?></p>
      </details>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php
$faqLd = [
    '@context' => 'https://schema.org',
    '@type' => 'FAQPage',
    'mainEntity' => array_map(function ($item) {
        return ['@type' => 'Question', 'name' => $item['q'], 'acceptedAnswer' => ['@type' => 'Answer', 'text' => $item['a']]];
    }, $faq)
];
echo '<script type="application/ld+json">' . json_encode($faqLd, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . '</script>';
?>

<section id="testimonial">
  <div class="container">
    <div class="section-head">
      <span class="eyebrow"><?= h(page_text($s, 'testimonial_eyebrow', 'Testimonial')) ?></span>
      <h2><?= h(page_text($s, 'testimonial_h2', 'Kata Pelanggan Kami')) ?></h2>
      <p><?= h(page_text($s, 'testimonial_lead', 'Pengalaman nyata pelanggan yang sudah menggunakan jasa kami.')) ?></p>
    </div>
    <?php if (!empty($testimonials)): ?>
    <div class="testimonial-grid">
      <?php foreach ($testimonials as $t): ?>
      <div class="testimonial-card">
        <p class="testimonial-quote"><?= h($t['content']) ?></p>
        <div class="testimonial-person">
          <div class="testimonial-avatar">
            <?php if (!empty($t['photo'])): ?>
            <img src="<?= h($t['photo']) ?>" alt="<?= h($t['name']) ?>">
            <?php else: ?>
            <?= h(placeholder_initials($t['name'])) ?>
            <?php endif; ?>
          </div>
          <div>
            <strong><?= h($t['name']) ?></strong>
            <span><?= h(trim($t['area'] . ($t['service'] ? ' • ' . $t['service'] : ''), ' •')) ?></span>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    <?php else: ?>
    <div class="empty-state">
      <h4 style="margin:0">Testimoni Segera Hadir</h4>
      <p>Kami sedang mengumpulkan testimoni asli dari pelanggan. Nantikan cerita pengalaman mereka di sini.</p>
    </div>
    <?php endif; ?>
  </div>
</section>

<?php render_cta_band(page_text($s, 'cta_title', 'Butuh Jasa Kolam Renang di Bogor?'), page_text($s, 'cta_subtitle', 'Konsultasikan kebutuhan kolam renang Anda sekarang, gratis tanpa biaya survei awal.'), $business); ?>

<section id="kontak">
  <div class="container">
    <div class="section-head">
      <span class="eyebrow">Kontak</span>
      <h2>Hubungi Kami</h2>
      <p>Konsultasikan kebutuhan kolam renang Anda, gratis tanpa biaya survei awal.</p>
    </div>
    <?php render_contact_block($business); ?>
  </div>
</section>

<footer class="site-footer">
  <div class="container">
    <div class="footer-grid">
      <div>
        <div class="footer-brand">
          <span class="brand-mark">
            <svg viewBox="0 0 24 24" fill="none" stroke="#ffffff" stroke-width="2" stroke-linecap="round"><path d="M2 17c1.5 1.2 3 1.2 4.5 0s3-1.2 4.5 0 3 1.2 4.5 0 3-1.2 4.5 0"/><path d="M12 13V4l3 2"/></svg>
          </span>
          <span><?= h($business['name']) ?></span>
        </div>
        <p style="max-width:340px"><?= h($business['description']) ?></p>
      </div>
      <div>
        <h4>Navigasi</h4>
        <a href="/layanan/">Semua Layanan</a>
        <a href="/area-layanan/">Area Layanan</a>
        <a href="/portofolio/">Proyek</a>
        <a href="/artikel/">Artikel</a>
        <a href="/faq/">FAQ</a>
        <a href="/kontak/">Kontak</a>
      </div>
      <div>
        <h4>Area Layanan</h4>
        <?php foreach (array_slice($areas, 0, 4) as $area): ?>
        <a href="<?= h($area['link'] ?: '/') ?>"><?= h($area['name']) ?></a>
        <?php endforeach; ?>
        <a href="/area-layanan/">Lihat Semua Area &rarr;</a>
      </div>
    </div>
    <div class="footer-bottom">
      &copy; <span id="year"><?= date('Y') ?></span> <?= h($business['name']) ?>. Seluruh hak cipta dilindungi. — <?= h(preg_replace('#^https?://#', '', $business['domain'])) ?>
    </div>
  </div>
</footer>

<a href="<?= h($waHref) ?>" id="wa-float" class="wa-float" aria-label="Chat WhatsApp">
  <svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 2a10 10 0 0 0-8.6 15L2 22l5.2-1.4A10 10 0 1 0 12 2Zm0 18a8 8 0 0 1-4.1-1.1l-.3-.2-3.1.8.8-3-.2-.3A8 8 0 1 1 12 20Zm4.4-5.9c-.2-.1-1.4-.7-1.6-.8-.2-.1-.4-.1-.5.1s-.6.8-.7.9-.3.1-.5 0a6.6 6.6 0 0 1-1.9-1.2 7.2 7.2 0 0 1-1.3-1.6c-.1-.2 0-.3.1-.4l.4-.4.2-.4v-.3c0-.1-.5-1.3-.7-1.7s-.4-.4-.5-.4h-.4a.9.9 0 0 0-.6.3 2.7 2.7 0 0 0-.8 2 4.7 4.7 0 0 0 1 2.5 10.8 10.8 0 0 0 4.1 3.6c.6.2 1 .4 1.4.5a3.3 3.3 0 0 0 1.5.1 2.5 2.5 0 0 0 1.6-1.1 1.9 1.9 0 0 0 .1-1.1c-.1-.1-.2-.2-.4-.3Z"/></svg>
</a>

<script>window.AREA_MAP_DATA = <?= json_encode($areas, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?>;</script>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="assets/js/main.js"></script>
<script src="assets/js/reveal.js"></script>
</body>
</html>
