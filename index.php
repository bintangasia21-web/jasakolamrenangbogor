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
        Terpercaya di Bogor & Sekitarnya
      </span>
      <h1>Wujudkan Kolam Renang Impian Anda di Bogor</h1>
      <p class="lead">Kami melayani pembuatan kolam renang baru, perawatan rutin, renovasi & perbaikan, hingga instalasi sistem air — untuk rumah tinggal, villa, dan resort di Sentul, Puncak, Ciawi, Bogor Kota, Cibinong, Yasmin, dan area Bogor lainnya.</p>
      <div class="hero-actions">
        <a href="<?= h($waHref) ?>" class="btn btn-white">
          <svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 2a10 10 0 0 0-8.6 15L2 22l5.2-1.4A10 10 0 1 0 12 2Zm0 18a8 8 0 0 1-4.1-1.1l-.3-.2-3.1.8.8-3-.2-.3A8 8 0 1 1 12 20Zm4.4-5.9c-.2-.1-1.4-.7-1.6-.8-.2-.1-.4-.1-.5.1s-.6.8-.7.9-.3.1-.5 0a6.6 6.6 0 0 1-1.9-1.2 7.2 7.2 0 0 1-1.3-1.6c-.1-.2 0-.3.1-.4l.4-.4.2-.4v-.3c0-.1-.5-1.3-.7-1.7s-.4-.4-.5-.4h-.4a.9.9 0 0 0-.6.3 2.7 2.7 0 0 0-.8 2 4.7 4.7 0 0 0 1 2.5 10.8 10.8 0 0 0 4.1 3.6c.6.2 1 .4 1.4.5a3.3 3.3 0 0 0 1.5.1 2.5 2.5 0 0 0 1.6-1.1 1.9 1.9 0 0 0 .1-1.1c-.1-.1-.2-.2-.4-.3Z"/></svg>
          Konsultasi Gratis via WhatsApp
        </a>
        <a href="/layanan/" class="btn btn-outline">Lihat Layanan Kami</a>
      </div>
      <div class="hero-stats">
        <div><strong><?= (int) $business['yearsExperience'] ?>+</strong><span>Tahun Pengalaman</span></div>
        <div><strong><?= (int) $business['projectsDone'] ?>+</strong><span>Proyek Selesai</span></div>
        <div><strong><?= count($areas) ?>+</strong><span>Area Layanan Utama</span></div>
      </div>
    </div>
    <div class="hero-visual">
      <svg viewBox="0 0 420 320" xmlns="http://www.w3.org/2000/svg">
        <rect x="20" y="30" width="380" height="220" rx="18" fill="#ffffff" fill-opacity="0.12"/>
        <rect x="40" y="50" width="340" height="150" rx="10" fill="#ffffff" fill-opacity="0.85"/>
        <path d="M40 170 Q 80 155 120 170 T 200 170 T 280 170 T 360 170 T 380 170 V200 H40 Z" fill="#4fadea"/>
        <path d="M40 185 Q 80 172 120 185 T 200 185 T 280 185 T 360 185 T 380 185 V200 H40 Z" fill="#1e90e0"/>
        <circle cx="330" cy="90" r="26" fill="#ffdf6b"/>
        <rect x="60" y="215" width="300" height="14" rx="7" fill="#ffffff" fill-opacity="0.5"/>
        <rect x="60" y="240" width="200" height="10" rx="5" fill="#ffffff" fill-opacity="0.35"/>
      </svg>
    </div>
  </div>
</section>

<section id="layanan">
  <div class="container">
    <div class="section-head">
      <span class="eyebrow">Layanan Kami</span>
      <h2>Solusi Lengkap Kolam Renang Anda</h2>
      <p>Dari kolam baru hingga perawatan berkelanjutan, tim kami menangani setiap tahap dengan standar kerja rapi dan bahan berkualitas.</p>
    </div>
    <div class="services-grid">
      <div class="service-card">
        <div class="service-icon">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M3 21h18"/><path d="M5 21V9l7-5 7 5v12"/><path d="M9 21v-6h6v6"/></svg>
        </div>
        <h3><a href="/layanan/pembuatan-kolam-renang-baru/" style="color:inherit">Pembuatan Kolam Baru</a></h3>
        <p>Desain dan konstruksi kolam renang dari nol sesuai lahan dan kebutuhan Anda, mulai dari kolam minimalis rumah tinggal hingga kolam villa berukuran besar. <a href="/layanan/pembuatan-kolam-renang-baru/" style="color:var(--blue-600);font-weight:600">Selengkapnya &rarr;</a></p>
      </div>
      <div class="service-card">
        <div class="service-icon">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2v4"/><path d="m4.9 4.9 2.8 2.8"/><path d="M2 12h4"/><path d="M12 22c-3 0-5-2-5-4.5S9 12 12 8c3 4 5 6.5 5 9.5S15 22 12 22Z"/></svg>
        </div>
        <h3><a href="/layanan/perawatan-pembersihan-rutin/" style="color:inherit">Perawatan Rutin</a></h3>
        <p>Program perawatan berkala — pembersihan, pengecekan kualitas air, dan perawatan sistem filtrasi — agar kolam selalu jernih dan siap pakai kapan saja. <a href="/layanan/perawatan-pembersihan-rutin/" style="color:var(--blue-600);font-weight:600">Selengkapnya &rarr;</a></p>
      </div>
      <div class="service-card">
        <div class="service-icon">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="m14.5 2.5 7 7-9 9-7-1-1-7 9-9Z"/><path d="m14.5 2.5 7 7"/><path d="M3 21l3-1"/></svg>
        </div>
        <h3><a href="/layanan/renovasi-perbaikan-kolam/" style="color:inherit">Renovasi & Perbaikan</a></h3>
        <p>Perbaikan kebocoran, keramik pecah, waterproofing, hingga renovasi total kolam lama agar tampil dan berfungsi seperti baru kembali. <a href="/layanan/renovasi-perbaikan-kolam/" style="color:var(--blue-600);font-weight:600">Selengkapnya &rarr;</a></p>
      </div>
      <div class="service-card">
        <div class="service-icon">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="4"/><path d="M12 2v3M12 19v3M4.2 4.2l2.1 2.1M17.7 17.7l2.1 2.1M2 12h3M19 12h3M4.2 19.8l2.1-2.1M17.7 6.3l2.1-2.1"/></svg>
        </div>
        <h3><a href="/layanan/instalasi-filter-pompa/" style="color:inherit">Instalasi Sistem Air</a></h3>
        <p>Pemasangan pompa sirkulasi, filter, sistem sanitasi, dan plumbing kolam renang agar air tetap bersih dan perawatan lebih efisien. <a href="/layanan/instalasi-filter-pompa/" style="color:var(--blue-600);font-weight:600">Selengkapnya &rarr;</a></p>
      </div>
    </div>
    <div class="text-center mt-32">
      <a href="/layanan/" class="btn btn-outline" style="border-color:var(--blue-600);color:var(--blue-600)">Lihat Semua Layanan Kami &rarr;</a>
    </div>
  </div>
</section>

<section id="kenapa-kami" class="section-alt">
  <div class="container">
    <div class="two-col">
      <div>
        <span class="eyebrow">Kenapa Kami</span>
        <h2>Dipercaya Pemilik Rumah, Villa & Resort di Bogor</h2>
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
      </div>
    </div>
  </div>
</section>

<section id="cara-kerja">
  <div class="container">
    <div class="section-head">
      <span class="eyebrow">Cara Kerja</span>
      <h2>Proses Pengerjaan yang Jelas dan Terstruktur</h2>
      <p>Kami memastikan setiap tahap pekerjaan dikomunikasikan dengan jelas kepada Anda.</p>
    </div>
    <div class="steps">
      <div class="step">
        <div class="step-num">1</div>
        <h4>Konsultasi & Survei Lokasi</h4>
        <p>Diskusi kebutuhan Anda dan survei langsung ke lokasi untuk memastikan kondisi lahan.</p>
      </div>
      <div class="step">
        <div class="step-num">2</div>
        <h4>Penawaran & Kesepakatan</h4>
        <p>Kami sampaikan rincian biaya, waktu pengerjaan, dan spesifikasi material secara transparan.</p>
      </div>
      <div class="step">
        <div class="step-num">3</div>
        <h4>Pengerjaan di Lapangan</h4>
        <p>Tim teknisi mengerjakan proyek sesuai jadwal dengan pengawasan mutu di setiap tahap.</p>
      </div>
      <div class="step">
        <div class="step-num">4</div>
        <h4>Quality Check</h4>
        <p>Pengecekan kualitas air, kebocoran, dan fungsi sistem sebelum serah terima.</p>
      </div>
      <div class="step">
        <div class="step-num">5</div>
        <h4>Serah Terima & Garansi</h4>
        <p>Kolam siap digunakan dengan masa garansi dan opsi paket perawatan lanjutan.</p>
      </div>
    </div>
  </div>
</section>

<section id="area" class="section-alt">
  <div class="container">
    <div class="section-head">
      <span class="eyebrow">Area Layanan</span>
      <h2>Melayani Wilayah Bogor dan Sekitarnya</h2>
      <p>Klik area di bawah untuk melihat layanan yang disesuaikan dengan karakteristik masing-masing wilayah. Zona berwarna hijau pada peta menandai area prioritas layanan kami.</p>
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

<section id="portofolio">
  <div class="container">
    <div class="section-head">
      <span class="eyebrow">Portofolio</span>
      <h2>Contoh Pekerjaan Kami</h2>
      <p>Gambaran jenis proyek yang telah kami kerjakan di berbagai area Bogor.</p>
    </div>
    <div class="portfolio-grid" id="portfolio-grid">
      <?php foreach ($portfolio as $item): ?>
      <div class="portfolio-card">
        <div class="portfolio-thumb">
          <?php if (!empty($item['image'])): ?>
          <img src="<?= h($item['image']) ?>" alt="<?= h($item['title']) ?>" loading="lazy">
          <?php else: ?>
          <?= placeholder_svg($item['title'], $item['color1'] ?: '#1478c8', $item['color2'] ?: '#00b8d9') ?>
          <?php endif; ?>
        </div>
        <div class="portfolio-body">
          <span class="tag"><?= h($item['area']) ?></span>
          <h3><?= h($item['title']) ?></h3>
          <p><?= h($item['description']) ?></p>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    <p class="portfolio-note">Gambar di atas adalah ilustrasi placeholder. Foto proyek asli dapat ditambahkan melalui panel admin.</p>
  </div>
</section>

<section id="faq" class="section-alt">
  <div class="container">
    <div class="section-head">
      <span class="eyebrow">FAQ</span>
      <h2>Pertanyaan yang Sering Diajukan</h2>
      <p>Belum menemukan jawaban? Hubungi kami langsung melalui WhatsApp.</p>
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

<section id="kontak">
  <div class="container">
    <div class="section-head">
      <span class="eyebrow">Kontak</span>
      <h2>Hubungi Kami</h2>
      <p>Konsultasikan kebutuhan kolam renang Anda, gratis tanpa biaya survei awal.</p>
    </div>
    <div class="contact-grid">
      <div class="contact-card">
        <h3>Informasi Kontak</h3>
        <ul class="contact-list">
          <li>
            <span class="ci"><svg viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2"><path d="M22 16.9v3a2 2 0 0 1-2.2 2 19.8 19.8 0 0 1-8.6-3 19.5 19.5 0 0 1-6-6 19.8 19.8 0 0 1-3-8.7A2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.7c.1.9.3 1.8.6 2.7a2 2 0 0 1-.5 2.1L8 9.7a16 16 0 0 0 6 6l1.2-1.2a2 2 0 0 1 2.1-.5c.9.3 1.8.5 2.7.6a2 2 0 0 1 1.7 2Z"/></svg></span>
            <div><small>Telepon</small><a href="tel:<?= h($business['phoneHref']) ?>"><?= h($business['phoneDisplay']) ?></a></div>
          </li>
          <li>
            <span class="ci"><svg viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2"><path d="M4 4h16v16H4z"/><path d="m4 6 8 7 8-7"/></svg></span>
            <div><small>Email</small><a href="mailto:<?= h($business['email']) ?>"><?= h($business['email']) ?></a></div>
          </li>
          <li>
            <span class="ci"><svg viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/></svg></span>
            <div><small>Alamat</small><span><?= h($business['addressLine']) ?></span></div>
          </li>
          <li>
            <span class="ci"><svg viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg></span>
            <div><small>Jam Operasional</small><span>Senin–Jumat <?= h($business['hoursWeekday']) ?> • Sabtu–Minggu <?= h($business['hoursWeekend']) ?></span></div>
          </li>
        </ul>
        <a href="<?= h($waHref) ?>" class="btn btn-wa btn-block">
          <svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 2a10 10 0 0 0-8.6 15L2 22l5.2-1.4A10 10 0 1 0 12 2Zm0 18a8 8 0 0 1-4.1-1.1l-.3-.2-3.1.8.8-3-.2-.3A8 8 0 1 1 12 20Zm4.4-5.9c-.2-.1-1.4-.7-1.6-.8-.2-.1-.4-.1-.5.1s-.6.8-.7.9-.3.1-.5 0a6.6 6.6 0 0 1-1.9-1.2 7.2 7.2 0 0 1-1.3-1.6c-.1-.2 0-.3.1-.4l.4-.4.2-.4v-.3c0-.1-.5-1.3-.7-1.7s-.4-.4-.5-.4h-.4a.9.9 0 0 0-.6.3 2.7 2.7 0 0 0-.8 2 4.7 4.7 0 0 0 1 2.5 10.8 10.8 0 0 0 4.1 3.6c.6.2 1 .4 1.4.5a3.3 3.3 0 0 0 1.5.1 2.5 2.5 0 0 0 1.6-1.1 1.9 1.9 0 0 0 .1-1.1c-.1-.1-.2-.2-.4-.3Z"/></svg>
          Chat via WhatsApp Sekarang
        </a>
      </div>
      <div class="map-frame">
        <iframe src="<?= h($business['mapsUrl']) ?>" loading="lazy" referrerpolicy="no-referrer-when-downgrade" title="Peta Lokasi <?= h($business['name']) ?>"></iframe>
      </div>
    </div>
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
        <button type="button" data-scroll="portofolio">Portofolio</button>
        <button type="button" data-scroll="faq">FAQ</button>
        <button type="button" data-scroll="kontak">Kontak</button>
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
</body>
</html>
