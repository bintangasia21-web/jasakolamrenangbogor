-- Skema database Jasa Kolam Renang Bogor.
-- Jalankan lewat phpMyAdmin (Import atau tab SQL) pada database yang
-- sudah dibuat di hPanel. Aman dijalankan berkali-kali (IF NOT EXISTS).

CREATE TABLE IF NOT EXISTS business (
  id TINYINT UNSIGNED NOT NULL DEFAULT 1 PRIMARY KEY,
  name VARCHAR(191) NOT NULL DEFAULT '',
  legalName VARCHAR(191) NOT NULL DEFAULT '',
  tagline VARCHAR(255) NOT NULL DEFAULT '',
  description TEXT,
  phoneDisplay VARCHAR(50) NOT NULL DEFAULT '',
  phoneHref VARCHAR(50) NOT NULL DEFAULT '',
  whatsapp VARCHAR(50) NOT NULL DEFAULT '',
  whatsappMessage TEXT,
  email VARCHAR(191) NOT NULL DEFAULT '',
  addressLine VARCHAR(255) NOT NULL DEFAULT '',
  city VARCHAR(100) NOT NULL DEFAULT '',
  region VARCHAR(100) NOT NULL DEFAULT '',
  postalCode VARCHAR(20) NOT NULL DEFAULT '',
  country VARCHAR(10) NOT NULL DEFAULT 'ID',
  hoursWeekday VARCHAR(100) NOT NULL DEFAULT '',
  hoursWeekend VARCHAR(100) NOT NULL DEFAULT '',
  mapsQuery VARCHAR(255) NOT NULL DEFAULT '',
  mapsUrl TEXT,
  priceRange VARCHAR(10) NOT NULL DEFAULT '',
  yearsExperience INT NOT NULL DEFAULT 0,
  projectsDone INT NOT NULL DEFAULT 0,
  domain VARCHAR(255) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Kolom "Fakta Bisnis" tambahan (brief 2026-08). Tabel business sudah
-- berisi data hidup, jadi CREATE TABLE IF NOT EXISTS di atas tidak akan
-- menambah kolom ke tabel yang sudah ada -- perlu ALTER terpisah.
-- monthlyRevenue SENGAJA internal-only: hanya tersimpan & terlihat di
-- panel admin, tidak pernah dirender ke halaman publik manapun.
-- CATATAN: sengaja TANPA "IF NOT EXISTS" pada ADD COLUMN -- klausa itu
-- baru didukung MySQL 8.0.29+, dan versi MySQL/MariaDB di hosting ini
-- lebih lama sehingga menyebabkan seluruh statement gagal (dibuktikan
-- lewat setup-schema.php: CREATE TABLE IF NOT EXISTS berhasil, tapi
-- keempat ADD COLUMN IF NOT EXISTS ini gagal). setup-schema.php sudah
-- menjalankan tiap statement dengan try/catch sendiri-sendiri, jadi
-- aman dijalankan ulang -- kalau kolom sudah ada, statement ini akan
-- gagal dengan "Duplicate column name" (masuk ke daftar "failed") tanpa
-- mengganggu statement lain.
ALTER TABLE business ADD COLUMN yearFounded INT NULL;
ALTER TABLE business ADD COLUMN activeCustomers VARCHAR(100) NOT NULL DEFAULT '';
ALTER TABLE business ADD COLUMN employeeCount INT NULL;
ALTER TABLE business ADD COLUMN monthlyRevenue VARCHAR(100) NULL;

CREATE TABLE IF NOT EXISTS areas (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(191) NOT NULL,
  link VARCHAR(255) NOT NULL DEFAULT '',
  description TEXT,
  lat DECIMAL(10,6) DEFAULT NULL,
  lng DECIMAL(10,6) DEFAULT NULL,
  priority TINYINT(1) NOT NULL DEFAULT 1,
  sort_order INT NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS faq (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  question VARCHAR(500) NOT NULL,
  answer TEXT NOT NULL,
  sort_order INT NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS portfolio (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(191) NOT NULL,
  area VARCHAR(100) NOT NULL DEFAULT '',
  description TEXT,
  image VARCHAR(500) DEFAULT NULL,
  color1 VARCHAR(20) DEFAULT NULL,
  color2 VARCHAR(20) DEFAULT NULL,
  sort_order INT NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Foto disimpan sebagai data biner LANGSUNG di database (bukan file di
-- server) supaya kebal terhadap proses deploy yang bisa menghapus file
-- di luar Git. portfolio.image menyimpan referensi "photo.php?id=<id>"
-- yang menunjuk ke baris di tabel ini.
CREATE TABLE IF NOT EXISTS photos (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  mime_type VARCHAR(50) NOT NULL,
  data LONGBLOB NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Fondasi 300 halaman SEO/GEO (area, kombinasi layanan x area, layanan,
-- artikel, portofolio, halaman pendukung). url_path adalah kunci unik
-- yang dipakai page-router.php untuk mencari halaman mana yang harus
-- ditampilkan untuk sebuah URL.
-- Testimoni pelanggan asli (brief 2026-08). Hanya baris status=published
-- yang ditampilkan publik; foto opsional lewat photo.php?id=<id> (pola
-- sama seperti portfolio.image, reuse tabel "photos" & upload-photo.php).
CREATE TABLE IF NOT EXISTS testimonials (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(191) NOT NULL,
  area VARCHAR(100) NOT NULL DEFAULT '',
  service VARCHAR(191) NOT NULL DEFAULT '',
  content TEXT NOT NULL,
  photo VARCHAR(500) DEFAULT NULL,
  testimonial_date DATE NULL,
  status ENUM('draft','published') NOT NULL DEFAULT 'draft',
  sort_order INT NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Galeri foto PER AREA (bukan galeri global) — dikaitkan lewat area_link
-- (bukan area.id) karena tab "Area Layanan" di admin menyimpan area dengan
-- pola delete-all-lalu-insert-ulang setiap kali disimpan, jadi area.id
-- tidak stabil antar penyimpanan; area_link (url halaman area, mis.
-- "/area/sentul/") jauh lebih stabil sebagai kunci penghubung.
CREATE TABLE IF NOT EXISTS area_photos (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  area_link VARCHAR(255) NOT NULL DEFAULT '',
  photo VARCHAR(500) NOT NULL,
  caption VARCHAR(255) NOT NULL DEFAULT '',
  sort_order INT NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS pages (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  type ENUM('area','service','combo','article','portfolio','page') NOT NULL,
  tier VARCHAR(100) DEFAULT NULL,
  url_path VARCHAR(255) NOT NULL,
  title VARCHAR(255) NOT NULL,
  meta_title VARCHAR(255) DEFAULT NULL,
  meta_description VARCHAR(500) DEFAULT NULL,
  target_keyword VARCHAR(255) DEFAULT NULL,
  h1 VARCHAR(255) DEFAULT NULL,
  area_ref VARCHAR(191) DEFAULT NULL,
  service_ref VARCHAR(191) DEFAULT NULL,
  intro TEXT,
  content LONGTEXT,
  faq_json TEXT,
  status ENUM('draft','published') NOT NULL DEFAULT 'draft',
  sort_order INT NOT NULL DEFAULT 0,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uniq_url_path (url_path)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
