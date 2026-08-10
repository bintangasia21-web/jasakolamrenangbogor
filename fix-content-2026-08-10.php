<?php
/**
 * Skrip sekali-jalan: memperbaiki 2 hal di konten live (bukan kode) yang
 * cuma bisa diubah lewat database, sesuai brief 2026-08-10:
 *
 * 1. Typo teks hero Beranda (page_sections, page_key='home'): "sd"
 *    dipakai sebagai pengganti "&" (mis. "Renovasi sd Perawatan"), dan
 *    "Installasi" (dobel L) harusnya "Instalasi". Dicari & diganti di
 *    NILAI YANG SEDANG TERSIMPAN (bukan string tebakan/hardcode) supaya
 *    tetap tepat berapa pun teks lengkapnya saat ini.
 * 2. Deskripsi 5 item portofolio placeholder diganti dengan versi yang
 *    lebih spesifik & SEO-friendly. Dicocokkan berdasarkan judul yang
 *    SUDAH ADA (judul TIDAK diubah -- supaya slug/URL halaman detail
 *    SEO tidak berubah, lihat portfolio_sync_seo_page()). Setelah
 *    UPDATE, halaman detail SEO proyek terkait (tabel "pages") ikut
 *    disinkronkan ulang lewat portfolio_sync_seo_page() yang sama
 *    dipakai edit-portfolio.php, supaya kontennya konsisten dan bebas
 *    dari bug paragraf dobel yang sudah diperbaiki di helper tsb.
 *
 * Dijalankan lewat browser (Basic Auth), sama seperti seed-perawatan-
 * content.php. Aman dijalankan berkali-kali -- UPDATE berdasarkan nilai
 * yang cocok, tidak melakukan apa-apa lagi begitu sudah diperbaiki.
 */
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/inc/db.php';
require_once __DIR__ . '/inc/portfolio-helpers.php';

function respond($success, $message, $extra = []) {
    echo json_encode(array_merge(['success' => $success, 'message' => $message], $extra), JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}

function fix_typo_text($value) {
    $fixed = preg_replace('/\s+sd\s+/u', ' & ', (string) $value);
    $fixed = preg_replace('/\bInstallasi\b/u', 'Instalasi', $fixed);
    $fixed = preg_replace('/\binstallasi\b/u', 'instalasi', $fixed);
    return $fixed;
}

$portfolioFixes = [
    'Kolam Renang Villa Modern' => 'Proyek pembuatan kolam renang infinity edge untuk villa di kawasan perbukitan Sentul, Bogor. Desain disesuaikan dengan kontur lahan berbukit agar tampilan kolam menyatu dengan pemandangan sekitar, dilengkapi sistem sirkulasi yang stabil meski sering ditinggal kosong di hari kerja. Cocok untuk pemilik villa yang menginginkan kolam estetis sekaligus mudah dirawat.',
    'Kolam Renang Keluarga' => 'Proyek pembuatan kolam renang minimalis untuk hunian keluarga di kawasan perumahan Cibinong, Bogor. Ukuran dan desain disesuaikan dengan lahan rumah yang terbatas tanpa mengurangi kenyamanan penggunaan sehari-hari, lengkap dengan sistem filtrasi yang mudah dirawat. Pilihan tepat bagi keluarga yang ingin kolam renang pribadi di lahan perumahan.',
    'Perawatan Kolam Rutin' => 'Program perawatan bulanan untuk kolam renang hotel di kawasan Bogor Kota, mencakup pengecekan kualitas air, pembersihan filter, dan penyeimbangan kimia secara berkala. Jadwal kunjungan disesuaikan agar tidak mengganggu operasional hotel, menjaga kolam selalu jernih dan siap digunakan tamu kapan saja. Cocok untuk properti komersial yang membutuhkan standar kebersihan konsisten.',
    'Instalasi Sistem Filtrasi' => 'Pemasangan sistem sirkulasi dan sanitasi air otomatis untuk kolam renang di kawasan Sentul, Bogor. Sistem dirancang untuk menjaga kualitas air tetap stabil meski kolam tidak digunakan setiap hari, sesuai karakteristik banyak vila di area ini yang dipakai musiman. Mengurangi kebutuhan perawatan manual sekaligus menekan risiko air keruh saat kolam kembali dipakai.',
    'Perbaikan Kebocoran Kolam' => 'Perbaikan struktur dan pelapisan ulang waterproofing untuk kolam renang lama di kawasan Bogor Kota yang mengalami rembesan akibat curah hujan tinggi. Pekerjaan mencakup deteksi titik kebocoran, perbaikan struktur beton, dan pelapisan waterproofing baru agar air tidak lagi merembes ke bangunan sekitar. Solusi tepat untuk kolam lama yang mulai bermasalah karena usia dan cuaca.',
];

try {
    $pdo = get_db();

    // 1. Typo teks hero Beranda.
    $heroChanges = [];
    $heroFields = ['hero_h1', 'hero_lead', 'hero_badge'];
    $stmt = $pdo->prepare('SELECT field_value FROM page_sections WHERE page_key = :page_key AND field_key = :field_key');
    $update = $pdo->prepare('UPDATE page_sections SET field_value = :v WHERE page_key = :page_key AND field_key = :field_key');
    foreach ($heroFields as $field) {
        $stmt->execute([':page_key' => 'home', ':field_key' => $field]);
        $row = $stmt->fetch();
        if (!$row) continue;
        $old = $row['field_value'];
        $new = fix_typo_text($old);
        if ($new !== $old) {
            $update->execute([':v' => $new, ':page_key' => 'home', ':field_key' => $field]);
            $heroChanges[$field] = ['before' => $old, 'after' => $new];
        }
    }

    // 2. Deskripsi 5 proyek portofolio (judul tidak diubah).
    $portfolioChanges = [];
    $portfolioNotFound = [];
    $findByTitle = $pdo->prepare('SELECT * FROM portfolio WHERE title = :title LIMIT 1');
    $updateDesc = $pdo->prepare('UPDATE portfolio SET description = :desc WHERE id = :id');
    foreach ($portfolioFixes as $title => $newDesc) {
        $findByTitle->execute([':title' => $title]);
        $item = $findByTitle->fetch();
        if (!$item) {
            $portfolioNotFound[] = $title;
            continue;
        }
        if ($item['description'] === $newDesc) {
            $portfolioChanges[$title] = 'tidak berubah, sudah sama';
            continue;
        }
        $updateDesc->execute([':desc' => $newDesc, ':id' => $item['id']]);
        $detailLink = portfolio_sync_seo_page($pdo, $item['id'], $item['title'], $item['area'], $newDesc, $item['image']);
        if ($detailLink) {
            $pdo->prepare('UPDATE portfolio SET detail_link = :detail_link WHERE id = :id')
                ->execute([':detail_link' => $detailLink, ':id' => $item['id']]);
        }
        $portfolioChanges[$title] = 'deskripsi diperbarui' . ($detailLink ? " (halaman detail: $detailLink)" : '');
    }

    respond(empty($portfolioNotFound), 'Selesai.', [
        'hero_changes' => $heroChanges,
        'portfolio_changes' => $portfolioChanges,
        'portfolio_not_found' => $portfolioNotFound,
    ]);
} catch (Exception $e) {
    respond(false, 'Gagal memperbarui konten: ' . $e->getMessage());
}
