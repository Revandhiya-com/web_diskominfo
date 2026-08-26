-- ============================================================
-- Portal PKL & Magang Diskominfo Provinsi Banten
-- Schema Database (MySQL / MariaDB)
-- Engine: InnoDB | Charset: utf8mb4_unicode_ci
-- ============================================================

CREATE DATABASE IF NOT EXISTS diskominfo_pkl
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE diskominfo_pkl;

-- ------------------------------------------------------------
-- USERS — pengguna admin/editor
-- ------------------------------------------------------------
CREATE TABLE users (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    username VARCHAR(50) NOT NULL,
    email VARCHAR(100) NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin','editor') NOT NULL DEFAULT 'editor',
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    last_login_at DATETIME NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_users_username (username)
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- NEWS — berita, artikel, kegiatan, prestasi
-- ------------------------------------------------------------
CREATE TABLE news_categories (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    name VARCHAR(50) NOT NULL,
    slug VARCHAR(60) NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_news_cat_slug (slug)
) ENGINE=InnoDB;

CREATE TABLE news (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    category_id INT UNSIGNED NULL,
    title VARCHAR(200) NOT NULL,
    slug VARCHAR(220) NOT NULL,
    excerpt VARCHAR(500) NULL,
    content LONGTEXT NULL,
    thumbnail VARCHAR(255) NULL,
    source_url VARCHAR(500) NULL,
    author_id INT UNSIGNED NULL,
    status ENUM('draft','published') NOT NULL DEFAULT 'draft',
    views INT UNSIGNED NOT NULL DEFAULT 0,
    published_at DATETIME NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_news_slug (slug),
    KEY idx_news_category (category_id),
    KEY idx_news_status_pub (status, published_at),
    CONSTRAINT fk_news_category FOREIGN KEY (category_id)
        REFERENCES news_categories (id) ON DELETE SET NULL,
    CONSTRAINT fk_news_author FOREIGN KEY (author_id)
        REFERENCES users (id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- Foto/video dokumentasi per berita/kegiatan (galeri di halaman detail),
-- group_name dipakai untuk mengelompokkan media per jenis lomba.
CREATE TABLE news_photos (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    news_id INT UNSIGNED NOT NULL,
    group_name VARCHAR(120) NULL,
    caption VARCHAR(255) NULL,
    image VARCHAR(255) NOT NULL,
    thumbnail VARCHAR(255) NULL,
    sort_order INT NOT NULL DEFAULT 0,
    media_type ENUM('image','video') NOT NULL DEFAULT 'image',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_np_news (news_id),
    CONSTRAINT fk_np_news FOREIGN KEY (news_id)
        REFERENCES news (id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- ALUMNI — alumni PKL & Magang (pencarian publik + CRUD admin)
-- ------------------------------------------------------------
CREATE TABLE alumni (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    name VARCHAR(150) NOT NULL,
    slug VARCHAR(170) NOT NULL,
    photo VARCHAR(255) NULL,
    type ENUM('pkl','magang') NOT NULL DEFAULT 'pkl',
    institution VARCHAR(200) NULL,
    major VARCHAR(150) NULL,
    start_date DATE NULL,
    end_date DATE NULL,
    year INT UNSIGNED NULL,
    division VARCHAR(150) NULL,
    description TEXT NULL,
    status ENUM('draft','published') NOT NULL DEFAULT 'draft',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_alumni_slug (slug),
    KEY idx_alumni_name (name),
    KEY idx_alumni_status (status)
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- ALBUMS & PHOTOS — dokumentasi kegiatan (fitur utama)
-- ------------------------------------------------------------
CREATE TABLE album_categories (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    name VARCHAR(50) NOT NULL,
    slug VARCHAR(60) NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_album_cat_slug (slug)
) ENGINE=InnoDB;

CREATE TABLE albums (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    category_id INT UNSIGNED NULL,
    title VARCHAR(200) NOT NULL,
    slug VARCHAR(220) NOT NULL,
    description TEXT NULL,
    event_date DATE NULL,
    location VARCHAR(255) NULL,
    participants VARCHAR(255) NULL,
    cover_image VARCHAR(255) NULL,
    status ENUM('draft','published') NOT NULL DEFAULT 'draft',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_albums_slug (slug),
    KEY idx_albums_category (category_id),
    KEY idx_albums_status_created (status, created_at),
    KEY idx_albums_event_date (event_date),
    CONSTRAINT fk_albums_category FOREIGN KEY (category_id)
        REFERENCES album_categories (id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE photos (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    album_id INT UNSIGNED NOT NULL,
    image VARCHAR(255) NOT NULL,
    media_type ENUM('image','video') NOT NULL DEFAULT 'image',
    thumbnail VARCHAR(255) NULL,
    caption VARCHAR(255) NULL,
    sort_order INT NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_photos_album (album_id),
    CONSTRAINT fk_photos_album FOREIGN KEY (album_id)
        REFERENCES albums (id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- AGENDAS
-- ------------------------------------------------------------
CREATE TABLE agendas (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    title VARCHAR(200) NOT NULL,
    description TEXT NULL,
    location VARCHAR(255) NULL,
    source_url VARCHAR(500) NULL,
    start_datetime DATETIME NOT NULL,
    end_datetime DATETIME NULL,
    status ENUM('draft','published') NOT NULL DEFAULT 'draft',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_agendas_start (start_datetime),
    KEY idx_agendas_status (status)
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- ANNOUNCEMENTS — pengumuman
-- ------------------------------------------------------------
CREATE TABLE announcements (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    title VARCHAR(200) NOT NULL,
    content TEXT NULL,
    source_url VARCHAR(500) NULL,
    is_pinned TINYINT(1) NOT NULL DEFAULT 0,
    status ENUM('draft','published') NOT NULL DEFAULT 'draft',
    published_at DATETIME NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_ann_status_pub (status, published_at)
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- DOCUMENTS — dokumen unduhan
-- ------------------------------------------------------------
CREATE TABLE document_categories (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    name VARCHAR(50) NOT NULL,
    slug VARCHAR(60) NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_doc_cat_slug (slug)
) ENGINE=InnoDB;

CREATE TABLE documents (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    category_id INT UNSIGNED NULL,
    name VARCHAR(200) NOT NULL,
    file VARCHAR(255) NOT NULL,
    size INT UNSIGNED NOT NULL DEFAULT 0,
    mime VARCHAR(100) NULL,
    downloads INT UNSIGNED NOT NULL DEFAULT 0,
    uploaded_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_documents_category (category_id),
    CONSTRAINT fk_documents_category FOREIGN KEY (category_id)
        REFERENCES document_categories (id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- FAQS
-- ------------------------------------------------------------
CREATE TABLE faqs (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    question VARCHAR(255) NOT NULL,
    answer TEXT NOT NULL,
    category VARCHAR(60) NULL,
    sort_order INT NOT NULL DEFAULT 0,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_faqs_active_order (is_active, sort_order)
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- PAGES — halaman statis yang dapat dikelola admin
-- ------------------------------------------------------------
CREATE TABLE pages (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    title VARCHAR(200) NOT NULL,
    slug VARCHAR(220) NOT NULL,
    content LONGTEXT NULL,
    source_url VARCHAR(500) NULL,
    status ENUM('draft','published') NOT NULL DEFAULT 'published',
    updated_at DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_pages_slug (slug),
    KEY idx_pages_status (status)
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- CONTACT_MESSAGES — pesan masuk dari form kontak publik
-- ------------------------------------------------------------
CREATE TABLE contact_messages (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL,
    subject VARCHAR(200) NULL,
    message TEXT NOT NULL,
    ip VARCHAR(45) NULL,
    is_read TINYINT(1) NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_contact_created (created_at)
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- SETTINGS — pengaturan situs (identitas, kontak, teks hero, dsb.)
-- ------------------------------------------------------------
CREATE TABLE settings (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `key` VARCHAR(100) NOT NULL,
    `value` TEXT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_settings_key (`key`)
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- VISITORS — statistik pengunjung (dipakai fase berikutnya)
-- ------------------------------------------------------------
CREATE TABLE visitors (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    ip VARCHAR(45) NULL,
    user_agent VARCHAR(255) NULL,
    page VARCHAR(120) NULL,
    visited_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_visitors_visited (visited_at)
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- VIDEOS — modul Video & Media (upload langsung foto/video ke server;
-- file fisik di uploads/videos/, database hanya menyimpan metadata)
-- ------------------------------------------------------------
CREATE TABLE videos (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    title VARCHAR(200) NOT NULL,
    description TEXT NULL,
    media_type ENUM('image','video') NOT NULL DEFAULT 'video',
    file_path VARCHAR(255) NOT NULL,
    mime_type VARCHAR(100) NOT NULL,
    file_size INT UNSIGNED NOT NULL DEFAULT 0,
    thumbnail VARCHAR(255) NULL,
    status ENUM('draft','published') NOT NULL DEFAULT 'draft',
    source ENUM('library','karya') NOT NULL DEFAULT 'library',
    created_by INT UNSIGNED NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NULL,
    PRIMARY KEY (id),
    KEY idx_videos_status (status),
    KEY idx_videos_created (created_at)
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- Karya peserta (galeri hasil karya PKL & Magang: infografis, video,
-- foto, lainnya). Video TIDAK diduplikasi — file video selalu milik
-- tabel `videos` dan direferensikan lewat video_id (source='karya'
-- berarti baris videos dibuat otomatis dari form Karya, sehingga
-- status/hapusnya mengikuti karya induknya).
-- ------------------------------------------------------------
CREATE TABLE karya (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    title VARCHAR(200) NOT NULL,
    description TEXT NULL,
    jenis ENUM('infografis','video','foto','lainnya') NOT NULL DEFAULT 'lainnya',
    media_type ENUM('image','video') NOT NULL DEFAULT 'image',
    file_path VARCHAR(255) NOT NULL DEFAULT '',
    mime_type VARCHAR(100) NULL,
    file_size INT UNSIGNED NULL,
    thumbnail VARCHAR(255) NULL,
    team VARCHAR(60) NULL,
    week_number TINYINT UNSIGNED NULL,
    video_id INT UNSIGNED NULL,
    is_edukasi TINYINT(1) NOT NULL DEFAULT 0,
    status ENUM('draft','published') NOT NULL DEFAULT 'draft',
    published_at DATETIME NULL,
    created_by INT UNSIGNED NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NULL,
    PRIMARY KEY (id),
    KEY idx_karya_status (status),
    KEY idx_karya_team (team),
    KEY idx_karya_week (week_number),
    KEY idx_karya_jenis (jenis),
    CONSTRAINT fk_karya_video FOREIGN KEY (video_id)
        REFERENCES videos (id) ON DELETE SET NULL
) ENGINE=InnoDB;