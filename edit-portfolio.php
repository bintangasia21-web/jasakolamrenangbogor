<?php
/**
 * Kelola Portfolio — halaman PHP tradisional (bukan single-page app).
 * Form HTML biasa, submit via POST, render sepenuhnya di server. Setiap
 * aksi (simpan/hapus/pindah urutan) adalah SATU request lengkap lalu
 * redirect (pola Post-Redirect-Get) -- tidak ada state JavaScript,
 * tidak ada fetch()/JSON, tidak ada array "hapus-semua-lalu-insert-
 * ulang". Dilindungi Basic Auth lewat .htaccess, sama seperti
 * admin.html.
 */
require_once __DIR__ . '/inc/db.php';
require_once __DIR__ . '/inc/render-partials.php';
require_once __DIR__ . '/inc/photo-helpers.php';
require_once __DIR__ . '/inc/portfolio-helpers.php';

$pdo = get_db();

function redirect_with($params) {
    header('Location: edit-portfolio.php?' . http_build_query($params));
    exit;
}

/**
 * Baca hasil paste dari ChatGPT (format "JUDUL: ..." / "DESKRIPSI: ...",
 * lihat prompt yang dibuat di bagian form) dan pisahkan jadi field
 * terpisah. Kalau formatnya tidak cocok, kembalikan null untuk field
 * itu -- pemanggil tetap pakai nilai yang diketik manual di form.
 */
function parse_ai_response($text) {
    $result = ['title' => null, 'desc' => null];
    if (preg_match('/JUDUL\s*:\s*(.+)/i', $text, $m)) {
        $result['title'] = trim($m[1]);
    }
    if (preg_match('/DESKRIPSI\s*:\s*(.+)/is', $text, $m)) {
        $result['desc'] = trim($m[1]);
    }
    return $result;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'save') {
        $id = (!empty($_POST['id'])) ? (int) $_POST['id'] : null;
        $title = trim($_POST['title'] ?? '');
        $area = trim($_POST['area'] ?? '');
        $desc = trim($_POST['desc'] ?? '');
        $backParams = $id ? ['id' => $id] : ['new' => 1];

        // Kalau admin menempel balasan ChatGPT, hasil parsingnya
        // menggantikan Judul/Deskripsi yang diketik manual -- itu memang
        // tujuan alur ini (isi lewat AI, bukan pelengkap).
        $aiPaste = trim($_POST['ai_paste'] ?? '');
        if ($aiPaste !== '') {
            $parsed = parse_ai_response($aiPaste);
            if ($parsed['title'] === null && $parsed['desc'] === null) {
                // Teks yang ditempel tidak mengandung label "JUDUL:"/
                // "DESKRIPSI:" sama sekali -- kemungkinan salah tempel
                // (bukan balasan ChatGPT yang sesuai format prompt).
                // Beri tahu jelas alih-alih diam-diam mengabaikannya.
                redirect_with($backParams + ['error' => 'Teks di kotak "Tempel balasan ChatGPT" tidak dikenali (harus ada baris "JUDUL:" dan/atau "DESKRIPSI:"). Cek kembali hasil dari ChatGPT, atau kosongkan kotak itu kalau tidak ingin memakainya.']);
            }
            if ($parsed['title'] !== null) $title = $parsed['title'];
            if ($parsed['desc'] !== null) $desc = $parsed['desc'];
        }

        // Jaring pengaman terakhir: kalau teks placeholder prompt entah
        // bagaimana lolos sampai ke ChatGPT & kepasang di sini (mis. dari
        // prompt lama yang sudah disalin sebelum perbaikan ini), tolak
        // dengan pesan jelas alih-alih menyimpan teks yang rusak.
        $leakedPlaceholders = ['(area belum dipilih)', '(judul proyek belum diisi)'];
        foreach ($leakedPlaceholders as $placeholder) {
            if (stripos($title, $placeholder) !== false || stripos($desc, $placeholder) !== false) {
                redirect_with($backParams + ['error' => 'Judul/Deskripsi mengandung teks placeholder ("' . $placeholder . '") dari prompt yang belum lengkap. Isi ulang Judul & Area, buat prompt baru, lalu coba lagi.']);
            }
        }

        if ($title === '') {
            redirect_with($backParams + ['error' => 'Judul wajib diisi.']);
        }
        // Batas aman kolom portfolio.title (VARCHAR 191) -- prompt AI
        // sudah minta maksimal 60 karakter, tapi ChatGPT tidak selalu
        // patuh, jadi dipotong paksa di sini supaya tidak pernah gagal
        // simpan gara-gara judul kepanjangan (pelajaran dari kasus
        // deskripsi yang ternyata jauh lebih panjang dari target).
        if (mb_strlen($title, 'UTF-8') > 191) {
            $title = mb_substr($title, 0, 188, 'UTF-8') . '...';
        }

        // Foto: pertahankan yang lama kalau tidak ada file baru diunggah.
        $image = null;
        if ($id) {
            $existing = $pdo->prepare('SELECT image FROM portfolio WHERE id = :id');
            $existing->execute([':id' => $id]);
            $row = $existing->fetch();
            $image = $row ? $row['image'] : null;
        }
        if (!empty($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
            $uploadResult = store_photo_upload($_FILES['photo']);
            if (!$uploadResult['success']) {
                redirect_with($backParams + ['error' => $uploadResult['message']]);
            }
            $image = $uploadResult['url'];
        }

        try {
            $pdo->beginTransaction();
            if ($id) {
                $stmt = $pdo->prepare('UPDATE portfolio SET title=:title, area=:area, description=:desc, image=:image WHERE id=:id');
                $stmt->execute([':title' => $title, ':area' => $area, ':desc' => $desc, ':image' => $image, ':id' => $id]);
            } else {
                $maxOrder = $pdo->query('SELECT COALESCE(MAX(sort_order), -1) FROM portfolio')->fetchColumn();
                $stmt = $pdo->prepare('INSERT INTO portfolio (title, area, description, image, sort_order) VALUES (:title, :area, :desc, :image, :sort_order)');
                $stmt->execute([':title' => $title, ':area' => $area, ':desc' => $desc, ':image' => $image, ':sort_order' => (int) $maxOrder + 1]);
                $id = (int) $pdo->lastInsertId();
            }

            // Halaman detail SEO otomatis, dalam transaksi yang sama.
            $detailLink = portfolio_sync_seo_page($pdo, $id, $title, $area, $desc, $image);
            $pdo->prepare('UPDATE portfolio SET detail_link = :detail_link WHERE id = :id')
                ->execute([':detail_link' => $detailLink, ':id' => $id]);

            $pdo->commit();
        } catch (Exception $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            redirect_with($backParams + ['error' => 'Gagal menyimpan: ' . $e->getMessage()]);
        }

        redirect_with(['saved' => 1]);
    }

    if ($action === 'delete') {
        $id = (int) ($_POST['id'] ?? 0);
        if ($id) {
            $pdo->prepare('DELETE FROM portfolio WHERE id = :id')->execute([':id' => $id]);
        }
        redirect_with(['deleted' => 1]);
    }

    if ($action === 'move') {
        $id = (int) ($_POST['id'] ?? 0);
        $direction = $_POST['direction'] ?? '';
        $rows = $pdo->query('SELECT id, sort_order FROM portfolio ORDER BY sort_order, id')->fetchAll();
        $idx = null;
        foreach ($rows as $i => $r) {
            if ((int) $r['id'] === $id) { $idx = $i; break; }
        }
        if ($idx !== null) {
            $swapWith = $direction === 'up' ? $idx - 1 : $idx + 1;
            if ($swapWith >= 0 && $swapWith < count($rows)) {
                $a = $rows[$idx];
                $b = $rows[$swapWith];
                $pdo->prepare('UPDATE portfolio SET sort_order = :so WHERE id = :id')->execute([':so' => $b['sort_order'], ':id' => $a['id']]);
                $pdo->prepare('UPDATE portfolio SET sort_order = :so WHERE id = :id')->execute([':so' => $a['sort_order'], ':id' => $b['id']]);
            }
        }
        redirect_with([]);
    }

    redirect_with([]);
}

// ---- GET: render daftar atau form tambah/edit ----
$editId = isset($_GET['id']) ? (int) $_GET['id'] : null;
$isNew = isset($_GET['new']);
$editItem = null;
if ($editId) {
    $stmt = $pdo->prepare('SELECT * FROM portfolio WHERE id = :id');
    $stmt->execute([':id' => $editId]);
    $editItem = $stmt->fetch();
    if (!$editItem) { $editId = null; }
}

$areas = $pdo->query('SELECT name FROM areas ORDER BY sort_order, id')->fetchAll();
$items = $pdo->query('SELECT * FROM portfolio ORDER BY sort_order, id')->fetchAll();
$errorMsg = $_GET['error'] ?? '';
$showForm = $editId || $isNew;
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Kelola Portfolio — Panel Admin</title>
<meta name="robots" content="noindex, nofollow">
<link rel="icon" href="assets/img/favicon.svg" type="image/svg+xml">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/css/style.css">
<link rel="stylesheet" href="assets/css/admin.css">
</head>
<body class="admin-body">

<div class="admin-shell">
  <aside class="admin-sidebar">
    <div class="admin-brand">
      <span class="brand-mark"><svg viewBox="0 0 24 24" fill="none" stroke="#ffffff" stroke-width="2" stroke-linecap="round"><path d="M2 17c1.5 1.2 3 1.2 4.5 0s3-1.2 4.5 0 3 1.2 4.5 0 3-1.2 4.5 0"/><path d="M12 13V4l3 2"/></svg></span>
      Panel Admin
    </div>
    <nav class="admin-nav">
      <a href="admin.php" style="display:block;padding:11px 12px;border-radius:8px;color:rgba(255,255,255,.75);text-decoration:none;font-size:.92rem;font-weight:600;margin-bottom:4px">&larr; Dashboard</a>
      <a href="edit-portfolio.php" style="display:block;padding:11px 12px;border-radius:8px;background:var(--blue-600);color:#fff;text-decoration:none;font-size:.92rem;font-weight:600;margin-bottom:4px">Portfolio</a>
      <a href="admin.html" style="display:block;padding:11px 12px;border-radius:8px;color:rgba(255,255,255,.75);text-decoration:none;font-size:.92rem;font-weight:600;margin-bottom:4px">Fitur Lainnya (panel lama)</a>
    </nav>
  </aside>

  <main class="admin-main">
    <h2>Portfolio</h2>
    <p class="panel-desc">Kelola galeri proyek di beranda &amp; /portofolio/. Setiap proyek otomatis dapat halaman detail sendiri untuk SEO — satu form, satu kali simpan, langsung live.</p>

    <?php if (isset($_GET['saved'])): ?>
    <div class="admin-notice" style="background:#d9f7e6;border-color:#8fdcae;color:#1a7a45">Perubahan berhasil disimpan &amp; sudah live di situs.</div>
    <?php endif; ?>
    <?php if (isset($_GET['deleted'])): ?>
    <div class="admin-notice" style="background:#d9f7e6;border-color:#8fdcae;color:#1a7a45">Proyek berhasil dihapus.</div>
    <?php endif; ?>
    <?php if ($errorMsg): ?>
    <div class="admin-notice" style="background:#fdecec;border-color:#f5b7b1;color:#c0392b"><?= h($errorMsg) ?></div>
    <?php endif; ?>

    <?php if ($showForm): ?>
      <?php
      $formTitle = $editItem['title'] ?? '';
      $formArea = $editItem['area'] ?? '';
      $formDesc = $editItem['description'] ?? '';
      $formImage = $editItem['image'] ?? null;
      ?>
      <div class="admin-card">
        <div class="admin-card-head"><h3><?= $editId ? 'Edit Proyek' : 'Tambah Proyek Baru' ?></h3>
          <a href="edit-portfolio.php" class="btn btn-sm btn-ghost">Batal</a>
        </div>
        <form method="POST" enctype="multipart/form-data">
          <input type="hidden" name="action" value="save">
          <?php if ($editId): ?><input type="hidden" name="id" value="<?= (int) $editId ?>"><?php endif; ?>
          <div class="form-grid">
            <div class="field">
              <label>Judul</label>
              <input type="text" name="title" value="<?= h($formTitle) ?>" required>
            </div>
            <div class="field">
              <label>Area / Lokasi</label>
              <select name="area">
                <option value="">— Pilih Area —</option>
                <?php foreach ($areas as $a): ?>
                <option value="<?= h($a['name']) ?>"<?= $a['name'] === $formArea ? ' selected' : '' ?>><?= h($a['name']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>
          <div class="admin-card" style="background:var(--blue-50);border-style:dashed;margin:16px 0">
            <div class="admin-card-head"><h4 style="margin:0">Bantuan Menulis dengan AI (opsional)</h4></div>
            <p style="color:var(--gray-600);font-size:.85rem;margin:0 0 10px">Isi Judul &amp; Area di atas dulu, prompt di bawah otomatis menyesuaikan. Salin, tempel ke ChatGPT, lalu tempel balasannya ke kotak kedua — Judul &amp; Deskripsi akan otomatis terisi saat disimpan.</p>
            <div class="field full">
              <label>1. Prompt (salin ke ChatGPT)</label>
              <textarea id="ai-prompt-box" readonly style="min-height:140px;font-size:.82rem" onclick="this.select()"></textarea>
            </div>
            <div class="field full">
              <label>2. Tempel balasan ChatGPT di sini</label>
              <textarea name="ai_paste" placeholder="Tempel balasan ChatGPT (format JUDUL: ... / DESKRIPSI: ...) di sini, lalu klik Simpan Proyek." style="min-height:100px"></textarea>
              <small>Kalau kotak ini diisi, Judul &amp; Deskripsi di atas akan otomatis diganti dengan hasil dari sini saat disimpan.</small>
            </div>
          </div>
          <div class="field full">
            <label>Deskripsi</label>
            <textarea name="desc"><?= h($formDesc) ?></textarea>
          </div>
          <div class="field full">
            <label>Foto</label>
            <?php if ($formImage): ?>
            <div class="thumb-preview" style="width:220px;height:150px"><img src="<?= h($formImage) ?>" alt=""></div>
            <?php endif; ?>
            <input type="file" name="photo" accept="image/jpeg,image/png,image/webp,image/gif">
            <small><?= $formImage ? 'Pilih file baru untuk mengganti foto di atas, atau biarkan kosong untuk tetap pakai foto yang sudah ada.' : 'Opsional — belum ada foto untuk proyek ini.' ?></small>
          </div>
          <?php if ($editItem && !empty($editItem['detail_link'])): ?>
          <p style="color:var(--gray-600);font-size:.85rem">Halaman detail SEO: <a href="<?= h($editItem['detail_link']) ?>" target="_blank" rel="noopener"><?= h($editItem['detail_link']) ?></a> (otomatis dibuat/diperbarui saat disimpan)</p>
          <?php endif; ?>
          <div class="toolbar" style="margin-top:16px">
            <button type="submit" class="btn btn-primary">Simpan Proyek</button>
          </div>
        </form>
      </div>
      <script>
      // Murni templating teks di browser (tanpa fetch/AJAX/state) --
      // hanya mengisi kotak prompt supaya bisa disalin ke ChatGPT.
      // Tidak berkomunikasi ke server sama sekali, jadi tidak membawa
      // balik risiko yang sama seperti panel admin lama.
      (function () {
        var titleInput = document.querySelector('form input[name="title"]');
        var areaSelect = document.querySelector('form select[name="area"]');
        var promptBox = document.getElementById('ai-prompt-box');
        if (!titleInput || !areaSelect || !promptBox) return;

        function updatePrompt() {
          var title = titleInput.value.trim();
          var area = areaSelect.value;
          // Kalau Judul/Area belum diisi, JANGAN buat prompt sama sekali
          // -- sebelumnya teks placeholder ("area belum dipilih") ikut
          // ke ChatGPT dan muncul apa adanya di hasilnya kalau admin
          // menyalin prompt sebelum mengisi field ini.
          if (!title || !area) {
            promptBox.value = 'Isi dulu "Judul" dan "Area / Lokasi" di atas -- prompt akan muncul di sini otomatis begitu keduanya terisi.';
            return;
          }
          promptBox.value =
            'Buatkan konten SEO & GEO (local SEO) untuk halaman portofolio proyek jasa kolam renang berikut:\n' +
            '- Judul proyek (draft): ' + title + '\n' +
            '- Area/lokasi proyek: ' + area + '\n' +
            '- Kota: Bogor, Jawa Barat, Indonesia\n\n' +
            'Konteks bisnis: Jasa Kolam Renang Bogor melayani pembuatan, renovasi, dan perawatan kolam renang di wilayah Bogor & sekitarnya (Sentul, Puncak, Cibinong, Bogor Kota, Ciawi, Cijeruk, Yasmin, Rancamaya, Bogor Raya, Karadenan).\n\n' +
            'Aturan SEO & GEO:\n' +
            '1. Sebutkan nama area "' + area + '" secara alami di JUDUL dan minimal 2x di DESKRIPSI (sinyal local SEO) -- tapi JANGAN diulang kaku/berlebihan (keyword stuffing).\n' +
            '2. Selipkan variasi istilah terkait secara alami (jasa kolam renang, perawatan kolam, renovasi kolam, dsb) sesuai konteks proyek -- bukan asal tempel semua istilah.\n' +
            '3. Tulisan harus terdengar seperti ditulis oleh tim berpengalaman yang benar-benar mengerjakan proyek ini (sebutkan detail teknis yang masuk akal, bukan generik/template kosong).\n' +
            '4. Target pembaca: calon pelanggan di area tersebut yang sedang mencari jasa kolam renang -- buat mereka yakin ini penyedia yang tepat.\n\n' +
            'Balas PERSIS dengan format berikut, tanpa tambahan teks lain:\n' +
            'JUDUL: [judul SEO-friendly, sebutkan area secara alami, maksimal 60 karakter, hindari clickbait]\n' +
            'DESKRIPSI: [deskripsi 60-90 kata, sebutkan lokasi & jenis layanan secara natural (bukan berulang kaku), tulisan meyakinkan & terasa berpengalaman, akhiri dengan kesan hasil kerja yang memuaskan]';
        }

        titleInput.addEventListener('input', updatePrompt);
        areaSelect.addEventListener('change', updatePrompt);
        updatePrompt();
      })();
      </script>
    <?php else: ?>
      <div class="toolbar">
        <a href="edit-portfolio.php?new=1" class="btn btn-secondary">+ Tambah Proyek</a>
      </div>
      <?php if (empty($items)): ?>
      <div class="admin-card"><p style="color:var(--gray-600);margin:0">Belum ada proyek. Klik "+ Tambah Proyek" untuk mulai.</p></div>
      <?php else: ?>
        <?php foreach ($items as $i => $item): ?>
        <div class="admin-card">
          <div class="admin-card-head">
            <h3><?= h($item['title']) ?></h3>
            <div>
              <form method="POST" style="display:inline">
                <input type="hidden" name="action" value="move">
                <input type="hidden" name="id" value="<?= (int) $item['id'] ?>">
                <input type="hidden" name="direction" value="up">
                <button type="submit" class="btn btn-sm btn-ghost"<?= $i === 0 ? ' disabled' : '' ?>>&uarr; Naik</button>
              </form>
              <form method="POST" style="display:inline">
                <input type="hidden" name="action" value="move">
                <input type="hidden" name="id" value="<?= (int) $item['id'] ?>">
                <input type="hidden" name="direction" value="down">
                <button type="submit" class="btn btn-sm btn-ghost"<?= $i === count($items) - 1 ? ' disabled' : '' ?>>&darr; Turun</button>
              </form>
              <a href="edit-portfolio.php?id=<?= (int) $item['id'] ?>" class="btn btn-sm btn-secondary">Edit</a>
              <form method="POST" style="display:inline" onsubmit="return confirm('Hapus proyek ini? Tindakan ini tidak bisa dibatalkan.');">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="id" value="<?= (int) $item['id'] ?>">
                <button type="submit" class="btn btn-sm btn-danger">Hapus</button>
              </form>
            </div>
          </div>
          <div style="display:flex;gap:16px;align-items:flex-start">
            <div class="thumb-preview" style="width:140px;height:100px;flex:none;margin-bottom:0">
              <?php if (!empty($item['image'])): ?>
              <img src="<?= h($item['image']) ?>" alt="">
              <?php else: ?>
              <div style="width:100%;height:100%;display:flex;align-items:center;justify-content:center;color:var(--blue-600);font-weight:700;background:var(--blue-100);font-size:.8rem">Belum ada foto</div>
              <?php endif; ?>
            </div>
            <div>
              <p style="color:var(--gray-600);font-size:.88rem;margin:0 0 6px"><strong><?= h($item['area']) ?></strong> — <?= h($item['description']) ?></p>
              <?php if (!empty($item['detail_link'])): ?>
              <a href="<?= h($item['detail_link']) ?>" target="_blank" rel="noopener" style="font-size:.85rem">Lihat halaman detail &rarr;</a>
              <?php else: ?>
              <small style="color:var(--gray-500)">Halaman detail akan dibuat otomatis saat disimpan.</small>
              <?php endif; ?>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
      <?php endif; ?>
    <?php endif; ?>
  </main>
</div>
</body>
</html>
