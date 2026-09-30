-- ============================================================
-- Portal PKL & Magang Diskominfo Provinsi Banten
-- Schema Database (PostgreSQL / Supabase)
-- Jalankan di Supabase SQL Editor (Dashboard -> SQL -> New query).
-- ============================================================

-- Reset tabel jika sudah ada sebelumnya
DROP TABLE IF EXISTS activity_logs, login_attempts, karya, videos, settings, announcements, agendas, faqs, messages, pages, documents, document_categories, photos, albums, album_categories, alumni, news_photos, news, news_categories, users CASCADE;

-- ------------------------------------------------------------
-- USERS — pengguna admin/editor
-- ------------------------------------------------------------
CREATE TABLE users (
    id SERIAL PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    username VARCHAR(50) NOT NULL,
    email VARCHAR(100) NULL,
    password VARCHAR(255) NOT NULL,
    role VARCHAR(20) NOT NULL DEFAULT 'editor',
    is_active SMALLINT NOT NULL DEFAULT 1,
    last_login_at TIMESTAMP NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL,
    CONSTRAINT uq_users_username UNIQUE (username)
);

-- ------------------------------------------------------------
-- NEWS — berita, artikel, kegiatan, prestasi
-- ------------------------------------------------------------
CREATE TABLE news_categories (
    id SERIAL PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    slug VARCHAR(60) NOT NULL,
    CONSTRAINT uq_news_cat_slug UNIQUE (slug)
);

CREATE TABLE news (
    id SERIAL PRIMARY KEY,
    category_id INT NULL,
    title VARCHAR(200) NOT NULL,
    slug VARCHAR(220) NOT NULL,
    excerpt VARCHAR(500) NULL,
    content TEXT NULL,
    thumbnail VARCHAR(255) NULL,
    source_url VARCHAR(500) NULL,
    author_id INT NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'draft',
    views INT NOT NULL DEFAULT 0,
    published_at TIMESTAMP NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL,
    CONSTRAINT uq_news_slug UNIQUE (slug),
    CONSTRAINT fk_news_category FOREIGN KEY (category_id)
        REFERENCES news_categories (id) ON DELETE SET NULL,
    CONSTRAINT fk_news_author FOREIGN KEY (author_id)
        REFERENCES users (id) ON DELETE SET NULL
);
CREATE INDEX idx_news_category ON news (category_id);
CREATE INDEX idx_news_status_pub ON news (status, published_at);

-- Foto/video dokumentasi per berita/kegiatan (galeri di halaman detail),
-- group_name dipakai untuk mengelompokkan media per jenis lomba.
CREATE TABLE news_photos (
    id SERIAL PRIMARY KEY,
    news_id INT NOT NULL,
    group_name VARCHAR(120) NULL,
    caption VARCHAR(255) NULL,
    image VARCHAR(255) NOT NULL,
    thumbnail VARCHAR(255) NULL,
    sort_order INT NOT NULL DEFAULT 0,
    media_type VARCHAR(20) NOT NULL DEFAULT 'image',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_np_news FOREIGN KEY (news_id)
        REFERENCES news (id) ON DELETE CASCADE
);
CREATE INDEX idx_np_news ON news_photos (news_id);

-- ------------------------------------------------------------
-- ALUMNI — alumni PKL & Magang (pencarian publik + CRUD admin)
-- ------------------------------------------------------------
CREATE TABLE alumni (
    id SERIAL PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    slug VARCHAR(170) NOT NULL,
    photo VARCHAR(255) NULL,
    type VARCHAR(20) NOT NULL DEFAULT 'pkl',
    institution VARCHAR(200) NULL,
    major VARCHAR(150) NULL,
    start_date DATE NULL,
    end_date DATE NULL,
    year INT NULL,
    division VARCHAR(150) NULL,
    description TEXT NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'draft',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL,
    CONSTRAINT uq_alumni_slug UNIQUE (slug)
);
CREATE INDEX idx_alumni_name ON alumni (name);
CREATE INDEX idx_alumni_status ON alumni (status);

-- ------------------------------------------------------------
-- ALBUMS & PHOTOS — dokumentasi kegiatan (fitur utama)
-- ------------------------------------------------------------
CREATE TABLE album_categories (
    id SERIAL PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    slug VARCHAR(60) NOT NULL,
    CONSTRAINT uq_album_cat_slug UNIQUE (slug)
);

CREATE TABLE albums (
    id SERIAL PRIMARY KEY,
    category_id INT NULL,
    title VARCHAR(200) NOT NULL,
    slug VARCHAR(220) NOT NULL,
    description TEXT NULL,
    event_date DATE NULL,
    location VARCHAR(255) NULL,
    participants VARCHAR(255) NULL,
    cover_image VARCHAR(255) NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'draft',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL,
    CONSTRAINT uq_albums_slug UNIQUE (slug),
    CONSTRAINT fk_albums_category FOREIGN KEY (category_id)
        REFERENCES album_categories (id) ON DELETE SET NULL
);
CREATE INDEX idx_albums_category ON albums (category_id);
CREATE INDEX idx_albums_status_created ON albums (status, created_at);
CREATE INDEX idx_albums_event_date ON albums (event_date);

CREATE TABLE photos (
    id SERIAL PRIMARY KEY,
    album_id INT NOT NULL,
    image VARCHAR(255) NOT NULL,
    media_type VARCHAR(20) NOT NULL DEFAULT 'image',
    thumbnail VARCHAR(255) NULL,
    caption VARCHAR(255) NULL,
    sort_order INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_photos_album FOREIGN KEY (album_id)
        REFERENCES albums (id) ON DELETE CASCADE
);
CREATE INDEX idx_photos_album ON photos (album_id);

-- ------------------------------------------------------------
-- AGENDAS
-- ------------------------------------------------------------
CREATE TABLE agendas (
    id SERIAL PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    description TEXT NULL,
    location VARCHAR(255) NULL,
    source_url VARCHAR(500) NULL,
    start_datetime TIMESTAMP NOT NULL,
    end_datetime TIMESTAMP NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'draft',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);
CREATE INDEX idx_agendas_start ON agendas (start_datetime);
CREATE INDEX idx_agendas_status ON agendas (status);

-- ------------------------------------------------------------
-- ANNOUNCEMENTS — pengumuman
-- ------------------------------------------------------------
CREATE TABLE announcements (
    id SERIAL PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    content TEXT NULL,
    source_url VARCHAR(500) NULL,
    is_pinned SMALLINT NOT NULL DEFAULT 0,
    status VARCHAR(20) NOT NULL DEFAULT 'draft',
    published_at TIMESTAMP NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);
CREATE INDEX idx_ann_status_pub ON announcements (status, published_at);

-- ------------------------------------------------------------
-- DOCUMENTS — dokumen unduhan
-- ------------------------------------------------------------
CREATE TABLE document_categories (
    id SERIAL PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    slug VARCHAR(60) NOT NULL,
    CONSTRAINT uq_doc_cat_slug UNIQUE (slug)
);

CREATE TABLE documents (
    id SERIAL PRIMARY KEY,
    category_id INT NULL,
    name VARCHAR(200) NOT NULL,
    file VARCHAR(255) NOT NULL,
    size INT NOT NULL DEFAULT 0,
    mime VARCHAR(100) NULL,
    downloads INT NOT NULL DEFAULT 0,
    uploaded_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_documents_category FOREIGN KEY (category_id)
        REFERENCES document_categories (id) ON DELETE SET NULL
);
CREATE INDEX idx_documents_category ON documents (category_id);

-- ------------------------------------------------------------
-- FAQS
-- ------------------------------------------------------------
CREATE TABLE faqs (
    id SERIAL PRIMARY KEY,
    question VARCHAR(255) NOT NULL,
    answer TEXT NOT NULL,
    category VARCHAR(60) NULL,
    sort_order INT NOT NULL DEFAULT 0,
    is_active SMALLINT NOT NULL DEFAULT 1,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);
CREATE INDEX idx_faqs_active_order ON faqs (is_active, sort_order);

-- ------------------------------------------------------------
-- PAGES — halaman statis yang dapat dikelola admin
-- ------------------------------------------------------------
CREATE TABLE pages (
    id SERIAL PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    slug VARCHAR(220) NOT NULL,
    content TEXT NULL,
    source_url VARCHAR(500) NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'published',
    updated_at TIMESTAMP NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT uq_pages_slug UNIQUE (slug)
);
CREATE INDEX idx_pages_status ON pages (status);

-- ------------------------------------------------------------
-- CONTACT_MESSAGES — pesan masuk dari form kontak publik
-- ------------------------------------------------------------
CREATE TABLE contact_messages (
    id SERIAL PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL,
    subject VARCHAR(200) NULL,
    message TEXT NOT NULL,
    ip VARCHAR(45) NULL,
    is_read SMALLINT NOT NULL DEFAULT 0,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);
CREATE INDEX idx_contact_created ON contact_messages (created_at);

-- ------------------------------------------------------------
-- SETTINGS — pengaturan situs (identitas, kontak, teks hero, dsb.)
-- "key" adalah reserved word di PostgreSQL -> di-quote.
-- ------------------------------------------------------------
CREATE TABLE settings (
    id SERIAL PRIMARY KEY,
    "key" VARCHAR(100) NOT NULL,
    "value" TEXT NULL,
    CONSTRAINT uq_settings_key UNIQUE ("key")
);

-- ------------------------------------------------------------
-- VISITORS — statistik pengunjung (dipakai fase berikutnya)
-- ------------------------------------------------------------
CREATE TABLE visitors (
    id BIGSERIAL PRIMARY KEY,
    ip VARCHAR(45) NULL,
    user_agent VARCHAR(255) NULL,
    page VARCHAR(120) NULL,
    visited_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);
CREATE INDEX idx_visitors_visited ON visitors (visited_at);

-- ------------------------------------------------------------
-- VIDEOS — modul Video & Media
-- ------------------------------------------------------------
CREATE TABLE videos (
    id SERIAL PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    description TEXT NULL,
    media_type VARCHAR(20) NOT NULL DEFAULT 'video',
    file_path VARCHAR(255) NOT NULL,
    mime_type VARCHAR(100) NOT NULL,
    file_size INT NOT NULL DEFAULT 0,
    thumbnail VARCHAR(255) NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'draft',
    source VARCHAR(20) NOT NULL DEFAULT 'library',
    created_by INT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL
);
CREATE INDEX idx_videos_status ON videos (status);
CREATE INDEX idx_videos_created ON videos (created_at);

-- ------------------------------------------------------------
-- KARYA peserta
-- ------------------------------------------------------------
CREATE TABLE karya (
    id SERIAL PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    description TEXT NULL,
    jenis VARCHAR(20) NOT NULL DEFAULT 'lainnya',
    media_type VARCHAR(20) NOT NULL DEFAULT 'image',
    file_path VARCHAR(255) NOT NULL DEFAULT '',
    mime_type VARCHAR(100) NULL,
    file_size INT NULL,
    thumbnail VARCHAR(255) NULL,
    team VARCHAR(60) NULL,
    week_number SMALLINT NULL,
    video_id INT NULL,
    is_edukasi SMALLINT NOT NULL DEFAULT 0,
    status VARCHAR(20) NOT NULL DEFAULT 'draft',
    published_at TIMESTAMP NULL,
    created_by INT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL,
    CONSTRAINT fk_karya_video FOREIGN KEY (video_id)
        REFERENCES videos (id) ON DELETE SET NULL
);
CREATE INDEX idx_karya_status ON karya (status);
CREATE INDEX idx_karya_team ON karya (team);
CREATE INDEX idx_karya_week ON karya (week_number);
CREATE INDEX idx_karya_jenis ON karya (jenis);

-- ------------------------------------------------------------
-- LOGIN_ATTEMPTS — pelindung brute-force login admin
-- ------------------------------------------------------------
CREATE TABLE login_attempts (
    id SERIAL PRIMARY KEY,
    username VARCHAR(50) NOT NULL,
    ip VARCHAR(45) NOT NULL,
    attempted_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);
CREATE INDEX idx_login_attempts_user_ip ON login_attempts (username, ip);

-- ------------------------------------------------------------
-- ACTIVITY_LOGS — log aktivitas admin
-- ------------------------------------------------------------
CREATE TABLE activity_logs (
    id BIGSERIAL PRIMARY KEY,
    user_id INT NULL,
    action VARCHAR(50) NOT NULL,
    entity VARCHAR(50) NOT NULL,
    entity_id INT NULL,
    detail VARCHAR(255) NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);
CREATE INDEX idx_activity_user ON activity_logs (user_id);
CREATE INDEX idx_activity_created ON activity_logs (created_at);

-- ------------------------------------------------------------
-- TRIGGER updated_at — pengganti ON UPDATE CURRENT_TIMESTAMP
-- ------------------------------------------------------------
CREATE OR REPLACE FUNCTION set_updated_at()
RETURNS TRIGGER AS $$
BEGIN
    NEW.updated_at = NOW();
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER trg_users_updated_at BEFORE UPDATE ON users
    FOR EACH ROW EXECUTE FUNCTION set_updated_at();
CREATE TRIGGER trg_news_updated_at BEFORE UPDATE ON news
    FOR EACH ROW EXECUTE FUNCTION set_updated_at();
CREATE TRIGGER trg_alumni_updated_at BEFORE UPDATE ON alumni
    FOR EACH ROW EXECUTE FUNCTION set_updated_at();
CREATE TRIGGER trg_albums_updated_at BEFORE UPDATE ON albums
    FOR EACH ROW EXECUTE FUNCTION set_updated_at();
CREATE TRIGGER trg_pages_updated_at BEFORE UPDATE ON pages
    FOR EACH ROW EXECUTE FUNCTION set_updated_at();
CREATE TRIGGER trg_videos_updated_at BEFORE UPDATE ON videos
    FOR EACH ROW EXECUTE FUNCTION set_updated_at();
CREATE TRIGGER trg_karya_updated_at BEFORE UPDATE ON karya
    FOR EACH ROW EXECUTE FUNCTION set_updated_at();

-- ------------------------------------------------------------
-- SEED DATA AWAL — USER ADMIN (username: admin / password: demo1234)
-- ------------------------------------------------------------
INSERT INTO users (name, username, email, password, role, is_active)
VALUES ('Admin Portal', 'admin', 'admin@example.invalid', '$2y$10$pjNsXPHJ/45k.zafLr9OteNX9KqEsUwssAUiCQkUvxfmb21Rjunh6', 'admin', 1)
ON CONFLICT (username) DO NOTHING;
