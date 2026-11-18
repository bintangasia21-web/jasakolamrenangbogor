<?php
/**
 * Skrip sekali-jalan: memperkuat konten halaman
 * /layanan/pembuatan-kolam-renang-baru/ (saat ini rank #60 Google
 * menurut laporan admin) -- SATU baris saja, tidak menyentuh halaman
 * lain atau template bersama.
 *
 * 3 perubahan pada baris ini:
 * 1. Tambah section baru "Kisaran Biaya Pembuatan Kolam Renang" (link
 *    ke artikel biaya yang sudah ada, tidak mengarang angka).
 * 2. Tambah 1 foto lagi (photo.php?id=14, "Kolam Renang Keluarga" --
 *    satu-satunya foto bertema pembuatan selain id=11 yang sudah
 *    dipakai di halaman ini, dikonfirmasi lewat hub /portofolio/ live).
 * 3. Tambah 3 FAQ baru (biaya, desain custom, lahan terbatas) ke
 *    kolom faq_json -- FAQ di-render DINAMIS dari kolom ini oleh
 *    inc/templates/service.php (render_faq_block($page['faq'], ...)),
 *    jadi cukup update faq_json, tidak perlu sentuh HTML statis.
 *
 * Content di-fetch & dicek strpos()/needle dulu sebelum str_replace
 * (bukan overwrite penuh) -- konten halaman ini sudah berubah
 * beberapa kali sejak Phase 3. FAQ dicek per pertanyaan supaya aman
 * dijalankan berkali-kali (tidak menduplikasi).
 */
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/inc/db.php';

function respond($success, $message, $extra = []) {
    echo json_encode(array_merge(['success' => $success, 'message' => $message], $extra), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}

$urlPath = '/layanan/pembuatan-kolam-renang-baru/';

try {
    $pdo = get_db();
    $sel = $pdo->prepare("SELECT id, content, faq_json FROM pages WHERE url_path = :u AND type = 'service'");
    $sel->execute([':u' => $urlPath]);
    $row = $sel->fetch();

    if (!$row) {
        respond(false, 'Baris tidak ditemukan: ' . $urlPath);
    }

    $content = $row['content'];
    $report = ['backup_old_content' => $content, 'backup_old_faq_json' => $row['faq_json']];

    // ---- 1. Section "Kisaran Biaya" ----
    if (strpos($content, 'Kisaran Biaya Pembuatan Kolam Renang') !== false) {
        $report['section_biaya'] = ['status' => 'ALREADY_ADDED'];
    } else {
        $needle1 = 'baca <a href="/artikel/berapa-biaya-membuat-kolam-renang-baru-di-bogor/" style="color:var(--blue-600);font-weight:600">artikel kami soal biaya pembuatan kolam renang baru</a>.</p>';
        if (strpos($content, $needle1) === false) {
            $report['section_biaya'] = ['status' => 'SKIPPED', 'reason' => 'anchor teks Tahapan Pekerjaan tidak ditemukan persis -- kemungkinan sudah berubah, tidak dipaksa diubah'];
        } else {
            $newSection = '<h2 style="margin-top:32px">Kisaran Biaya Pembuatan Kolam Renang</h2>'
                . '<p style="color:var(--gray-600)">Setiap kolam berbeda kebutuhannya, jadi kami tidak mencantumkan satu angka pasti di halaman ini. Ukuran, desain, pilihan material, dan kondisi lahan semuanya memengaruhi hitungan akhir. Anda bisa baca rincian faktor-faktor tersebut di <a href="/artikel/berapa-biaya-membuat-kolam-renang-baru-di-bogor/" style="color:var(--blue-600);font-weight:600">artikel kisaran biaya pembuatan kolam renang baru</a>, atau langsung konsultasikan kondisi lahan Anda lewat WhatsApp untuk estimasi setelah survei gratis.</p>';
            $content = str_replace($needle1, $needle1 . $newSection, $content);
            $report['section_biaya'] = ['status' => 'ADDED'];
        }
    }

    // ---- 2. Foto tambahan (id=14) ----
    if (strpos($content, 'photo.php?id=14') !== false) {
        $report['foto_tambahan'] = ['status' => 'ALREADY_ADDED'];
    } else {
        $needle2 = '<img src="/photo.php?id=11" alt="Kolam renang villa modern hasil pembuatan kami di Sentul, Bogor" width="649" height="472" loading="lazy" style="width:100%;max-width:520px;height:auto;display:block;margin-top:16px;border-radius:var(--radius-md);box-shadow:var(--shadow-sm)">';
        if (strpos($content, $needle2) === false) {
            $report['foto_tambahan'] = ['status' => 'SKIPPED', 'reason' => 'tag img id=11 tidak ditemukan persis -- kemungkinan sudah berubah, tidak dipaksa diubah'];
        } else {
            $newImg = '<img src="/photo.php?id=14" alt="Kolam renang keluarga minimalis hasil pembuatan kami di Cibinong, Bogor" width="427" height="299" loading="lazy" style="width:100%;max-width:420px;height:auto;display:block;margin-top:16px;border-radius:var(--radius-md);box-shadow:var(--shadow-sm)">';
            $content = str_replace($needle2, $needle2 . $newImg, $content);
            $report['foto_tambahan'] = ['status' => 'ADDED'];
        }
    }

    // ---- 3. FAQ tambahan (lewat faq_json, bukan HTML statis) ----
    $faq = json_decode($row['faq_json'], true);
    if (!is_array($faq)) {
        $faq = [];
    }
    $existingQuestions = array_map(function ($f) { return $f['q']; }, $faq);

    $newFaqItems = [
        ['q' => 'Berapa biaya pembuatan kolam renang baru di Bogor?', 'a' => 'Biaya tergantung ukuran, desain, material, dan kondisi lahan -- kami berikan estimasi pasti setelah survei lokasi gratis.'],
        ['q' => 'Apakah bisa membuat kolam dengan desain custom seperti infinity edge?', 'a' => 'Bisa. Kami sesuaikan desain dengan keinginan dan kondisi lahan Anda, termasuk desain custom, setelah dikonsultasikan saat survei.'],
        ['q' => 'Apakah pembuatan kolam bisa dikerjakan di lahan terbatas atau berkontur?', 'a' => 'Bisa. Kami sudah menangani berbagai kondisi lahan di Bogor, dari lahan sempit di kawasan padat hingga lahan berkontur di kawasan perbukitan seperti Sentul.'],
    ];

    $addedFaq = [];
    foreach ($newFaqItems as $item) {
        if (in_array($item['q'], $existingQuestions, true)) {
            continue;
        }
        $faq[] = $item;
        $addedFaq[] = $item['q'];
    }
    $report['faq_tambahan'] = [
        'status' => empty($addedFaq) ? 'ALREADY_ADDED' : 'ADDED',
        'ditambahkan' => $addedFaq,
        'total_faq_setelah' => count($faq),
    ];

    $newFaqJson = json_encode($faq, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

    $upd = $pdo->prepare("UPDATE pages SET content = :content, faq_json = :faq_json WHERE id = :id AND url_path = :u AND type = 'service'");
    $upd->execute([
        ':content' => $content,
        ':faq_json' => $newFaqJson,
        ':id' => $row['id'],
        ':u' => $urlPath,
    ]);

    $report['rows_affected'] = $upd->rowCount();
    $report['verify_kisaran_biaya_present'] = strpos($content, 'Kisaran Biaya Pembuatan Kolam Renang') !== false;
    $report['verify_foto_present'] = strpos($content, 'photo.php?id=14') !== false;

    respond(true, 'Halaman pembuatan kolam renang baru berhasil diperkuat.', $report);
} catch (Exception $e) {
    respond(false, 'Gagal: ' . $e->getMessage());
}
