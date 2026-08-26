-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Win64 (AMD64)
--
-- Host: localhost    Database: diskominfo_pkl
-- ------------------------------------------------------
-- Server version	10.4.32-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `activity_logs`
--

DROP TABLE IF EXISTS `activity_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `activity_logs` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned DEFAULT NULL,
  `action` varchar(50) NOT NULL,
  `entity` varchar(50) NOT NULL,
  `entity_id` int(10) unsigned DEFAULT NULL,
  `detail` varchar(255) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_activity_created` (`created_at`),
  KEY `fk_activity_user` (`user_id`),
  CONSTRAINT `fk_activity_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=344 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `activity_logs`
--

LOCK TABLES `activity_logs` WRITE;
/*!40000 ALTER TABLE `activity_logs` DISABLE KEYS */;
INSERT INTO `activity_logs` VALUES (195,1,'login','auth',1,'Login berhasil','2026-08-17 17:21:50'),(196,1,'create','news',172,'Buat berita: Berita Uji F12 9tbgi','2026-08-17 17:21:53'),(197,1,'update','news',172,'Perbarui berita: Berita Uji F12 9tbgi (edit)','2026-08-17 17:21:53'),(198,1,'update','news',172,'Tarik ke draf berita: Berita Uji F12 9tbgi (edit)','2026-08-17 17:21:53'),(199,1,'delete','news',172,'Hapus berita: Berita Uji F12 9tbgi (edit)','2026-08-17 17:21:54'),(200,1,'create','agenda',36,'Buat agenda: Agenda Uji F12 ro8at','2026-08-17 17:21:54'),(201,1,'delete','agenda',36,'Hapus agenda: Agenda Uji F12 ro8at','2026-08-17 17:21:54'),(202,1,'create','announcement',31,'Buat pengumuman: Pengumuman Uji F12 hw8lu','2026-08-17 17:21:54'),(203,1,'delete','announcement',31,'Hapus pengumuman: Pengumuman Uji F12 hw8lu','2026-08-17 17:21:55'),(204,1,'create','faq',31,'Buat FAQ: Pertanyaan uji F12 5xgvi','2026-08-17 17:21:55'),(205,1,'delete','faq',31,'Hapus FAQ: Pertanyaan uji F12 5xgvi','2026-08-17 17:21:55'),(206,1,'create','album',63,'Buat album: Album Uji F12 6l3qc','2026-08-17 17:21:56'),(207,1,'upload','photo',63,'Unggah 2 foto ke album: Album Uji F12 6l3qc','2026-08-17 17:21:56'),(208,1,'update','photo',63,'Perbarui caption foto: Album Uji F12 6l3qc','2026-08-17 17:21:56'),(209,1,'delete','photo',63,'Hapus foto dari album: Album Uji F12 6l3qc','2026-08-17 17:21:56'),(210,1,'delete','album',63,'Hapus album: Album Uji F12 6l3qc','2026-08-17 17:21:57'),(211,1,'create','document',24,'Unggah dokumen: Dokumen Uji F12 dz37e','2026-08-17 17:21:57'),(212,1,'delete','document',24,'Hapus dokumen: Dokumen Uji F12 dz37e','2026-08-17 17:21:57'),(213,1,'logout','auth',1,'Logout','2026-08-17 17:21:58'),(214,1,'login','auth',1,'Login berhasil','2026-08-17 17:22:07'),(215,1,'login','auth',1,'Login berhasil','2026-08-17 17:22:08'),(216,1,'login','auth',1,'Login berhasil','2026-08-17 17:22:22'),(217,1,'create','news',173,'Buat berita: Perf Uji Resize','2026-08-17 17:22:23'),(218,1,'login','auth',1,'Login berhasil','2026-08-17 17:34:55'),(219,1,'create','news',179,'Buat berita: Berita Uji F12 pxm71','2026-08-17 17:34:58'),(220,1,'update','news',179,'Perbarui berita: Berita Uji F12 pxm71 (edit)','2026-08-17 17:34:59'),(221,1,'update','news',179,'Tarik ke draf berita: Berita Uji F12 pxm71 (edit)','2026-08-17 17:34:59'),(222,1,'delete','news',179,'Hapus berita: Berita Uji F12 pxm71 (edit)','2026-08-17 17:34:59'),(223,1,'create','agenda',39,'Buat agenda: Agenda Uji F12 rmp2w','2026-08-17 17:34:59'),(224,1,'delete','agenda',39,'Hapus agenda: Agenda Uji F12 rmp2w','2026-08-17 17:34:59'),(225,1,'create','announcement',32,'Buat pengumuman: Pengumuman Uji F12 s7v4r','2026-08-17 17:35:00'),(226,1,'delete','announcement',32,'Hapus pengumuman: Pengumuman Uji F12 s7v4r','2026-08-17 17:35:00'),(227,1,'create','faq',32,'Buat FAQ: Pertanyaan uji F12 7lw4k','2026-08-17 17:35:00'),(228,1,'delete','faq',32,'Hapus FAQ: Pertanyaan uji F12 7lw4k','2026-08-17 17:35:00'),(229,1,'create','album',64,'Buat album: Album Uji F12 4ou2y','2026-08-17 17:35:01'),(230,1,'upload','photo',64,'Unggah 2 foto ke album: Album Uji F12 4ou2y','2026-08-17 17:35:01'),(231,1,'update','photo',64,'Perbarui caption foto: Album Uji F12 4ou2y','2026-08-17 17:35:01'),(232,1,'delete','photo',64,'Hapus foto dari album: Album Uji F12 4ou2y','2026-08-17 17:35:01'),(233,1,'delete','album',64,'Hapus album: Album Uji F12 4ou2y','2026-08-17 17:35:01'),(234,1,'create','document',25,'Unggah dokumen: Dokumen Uji F12 xj3ed','2026-08-17 17:35:02'),(235,1,'delete','document',25,'Hapus dokumen: Dokumen Uji F12 xj3ed','2026-08-17 17:35:02'),(236,1,'logout','auth',1,'Logout','2026-08-17 17:35:02'),(237,1,'login','auth',1,'Login berhasil','2026-08-17 19:13:32'),(238,1,'create','news',195,'Buat berita: Berita Uji F12 tl069','2026-08-17 19:13:37'),(239,1,'update','news',195,'Perbarui berita: Berita Uji F12 tl069 (edit)','2026-08-17 19:13:37'),(240,1,'update','news',195,'Tarik ke draf berita: Berita Uji F12 tl069 (edit)','2026-08-17 19:13:38'),(241,1,'delete','news',195,'Hapus berita: Berita Uji F12 tl069 (edit)','2026-08-17 19:13:38'),(242,1,'create','agenda',46,'Buat agenda: Agenda Uji F12 sy8t4','2026-08-17 19:13:39'),(243,1,'delete','agenda',46,'Hapus agenda: Agenda Uji F12 sy8t4','2026-08-17 19:13:39'),(244,1,'create','announcement',33,'Buat pengumuman: Pengumuman Uji F12 u9j1z','2026-08-17 19:13:39'),(245,1,'delete','announcement',33,'Hapus pengumuman: Pengumuman Uji F12 u9j1z','2026-08-17 19:13:40'),(246,1,'create','faq',39,'Buat FAQ: Pertanyaan uji F12 0ru3v','2026-08-17 19:13:40'),(247,1,'delete','faq',39,'Hapus FAQ: Pertanyaan uji F12 0ru3v','2026-08-17 19:13:41'),(248,1,'create','album',65,'Buat album: Album Uji F12 walmk','2026-08-17 19:13:41'),(249,1,'upload','photo',65,'Unggah 2 foto ke album: Album Uji F12 walmk','2026-08-17 19:13:42'),(250,1,'update','photo',65,'Perbarui caption foto: Album Uji F12 walmk','2026-08-17 19:13:42'),(251,1,'delete','photo',65,'Hapus foto dari album: Album Uji F12 walmk','2026-08-17 19:13:42'),(252,1,'delete','album',65,'Hapus album: Album Uji F12 walmk','2026-08-17 19:13:43'),(253,1,'create','document',26,'Unggah dokumen: Dokumen Uji F12 n3wfk','2026-08-17 19:13:43'),(254,1,'delete','document',26,'Hapus dokumen: Dokumen Uji F12 n3wfk','2026-08-17 19:13:44'),(255,1,'logout','auth',1,'Logout','2026-08-17 19:13:44'),(256,1,'login','auth',1,'Login berhasil','2026-08-17 19:13:56'),(257,1,'login','auth',1,'Login berhasil','2026-08-17 19:13:57'),(258,1,'login','auth',1,'Login berhasil','2026-08-17 19:14:10'),(259,1,'create','news',196,'Buat berita: Perf Uji Resize','2026-08-17 19:14:12'),(260,1,'login','auth',1,'Login berhasil','2026-08-17 21:09:21'),(261,1,'create','news',197,'Buat berita: Berita Uji F12 pgo1a','2026-08-17 21:09:23'),(262,1,'update','news',197,'Perbarui berita: Berita Uji F12 pgo1a (edit)','2026-08-17 21:09:24'),(263,1,'update','news',197,'Tarik ke draf berita: Berita Uji F12 pgo1a (edit)','2026-08-17 21:09:24'),(264,1,'delete','news',197,'Hapus berita: Berita Uji F12 pgo1a (edit)','2026-08-17 21:09:24'),(265,1,'create','agenda',47,'Buat agenda: Agenda Uji F12 cmknh','2026-08-17 21:09:24'),(266,1,'delete','agenda',47,'Hapus agenda: Agenda Uji F12 cmknh','2026-08-17 21:09:25'),(267,1,'create','announcement',34,'Buat pengumuman: Pengumuman Uji F12 yuwsp','2026-08-17 21:09:25'),(268,1,'delete','announcement',34,'Hapus pengumuman: Pengumuman Uji F12 yuwsp','2026-08-17 21:09:25'),(269,1,'create','faq',40,'Buat FAQ: Pertanyaan uji F12 izkaj','2026-08-17 21:09:25'),(270,1,'delete','faq',40,'Hapus FAQ: Pertanyaan uji F12 izkaj','2026-08-17 21:09:26'),(271,1,'create','album',66,'Buat album: Album Uji F12 57vu4','2026-08-17 21:09:26'),(272,1,'upload','photo',66,'Unggah 2 foto ke album: Album Uji F12 57vu4','2026-08-17 21:09:26'),(273,1,'update','photo',66,'Perbarui caption foto: Album Uji F12 57vu4','2026-08-17 21:09:26'),(274,1,'delete','photo',66,'Hapus foto dari album: Album Uji F12 57vu4','2026-08-17 21:09:26'),(275,1,'delete','album',66,'Hapus album: Album Uji F12 57vu4','2026-08-17 21:09:27'),(276,1,'create','document',27,'Unggah dokumen: Dokumen Uji F12 fzcv8','2026-08-17 21:09:27'),(277,1,'delete','document',27,'Hapus dokumen: Dokumen Uji F12 fzcv8','2026-08-17 21:09:27'),(278,1,'logout','auth',1,'Logout','2026-08-17 21:09:27'),(279,1,'login','auth',1,'Login berhasil','2026-08-17 21:09:33'),(280,1,'login','auth',1,'Login berhasil','2026-08-17 21:09:34'),(281,1,'login','auth',1,'Login berhasil','2026-08-17 21:09:48'),(282,1,'create','news',198,'Buat berita: Perf Uji Resize','2026-08-17 21:09:49'),(283,NULL,'login','auth',18,'Login berhasil','2026-08-18 09:28:45'),(284,NULL,'logout','auth',18,'Logout','2026-08-18 09:28:46'),(285,NULL,'login','auth',18,'Login berhasil','2026-08-18 09:30:03'),(286,NULL,'logout','auth',18,'Logout','2026-08-18 09:30:05'),(287,NULL,'login','auth',18,'Login berhasil','2026-08-18 09:30:46'),(288,NULL,'login','auth',18,'Login berhasil','2026-08-18 09:31:18'),(289,NULL,'update','news',199,'Perbarui berita: Semarak 17 Agustus Bersama Anak PKL & Magang','2026-08-18 09:31:20'),(290,NULL,'create','news_photos',1,'Tambah foto dokumentasi berita #199','2026-08-18 09:31:20'),(291,NULL,'create','news_photos',2,'Tambah foto dokumentasi berita #199','2026-08-18 09:31:20'),(292,NULL,'create','news_photos',3,'Tambah foto dokumentasi berita #199','2026-08-18 09:31:20'),(293,NULL,'update','news',199,'Perbarui berita: Semarak 17 Agustus Bersama Anak PKL & Magang','2026-08-18 09:31:23'),(294,NULL,'create','news_photos',4,'Tambah foto dokumentasi berita #199','2026-08-18 09:31:23'),(295,NULL,'create','news_photos',5,'Tambah foto dokumentasi berita #199','2026-08-18 09:31:23'),(296,NULL,'update','news',199,'Perbarui berita: Semarak 17 Agustus Bersama Anak PKL & Magang','2026-08-18 09:31:24'),(297,1,'login','auth',1,'Login berhasil','2026-08-18 09:48:25'),(298,1,'login','auth',1,'Login berhasil','2026-08-18 10:02:51'),(299,1,'login','auth',1,'Login berhasil','2026-08-18 14:28:52'),(300,1,'logout','auth',1,'Logout','2026-08-18 14:28:56'),(304,1,'logout','auth',1,'Logout','2026-08-18 14:30:50'),(313,1,'update','news',199,'Perbarui berita: Semarak 17 Agustus Bersama Anak PKL & Magang','2026-08-18 14:33:25'),(318,1,'logout','auth',1,'Logout','2026-08-18 15:08:29'),(331,1,'login','auth',1,'Login berhasil','2026-08-19 11:56:10'),(332,1,'login','auth',1,'Login berhasil','2026-08-20 09:09:24'),(333,1,'create','alumni',8,'Tambah alumni: Ghita Naila Novitasari','2026-08-20 09:15:17'),(334,1,'logout','auth',1,'Logout','2026-08-20 09:25:36'),(335,1,'login','auth',1,'Login berhasil','2026-08-20 09:34:24'),(336,1,'login','auth',1,'Login berhasil','2026-08-20 09:35:16'),(337,1,'login','auth',1,'Login berhasil','2026-08-20 09:36:10'),(338,1,'login','auth',1,'Login berhasil','2026-08-21 10:28:56'),(339,1,'login','auth',1,'Login berhasil','2026-08-22 10:04:21'),(340,1,'update','alumni',8,'Perbarui alumni: Ghita Naila Novitasari','2026-08-22 11:05:52'),(341,1,'login','auth',1,'Login berhasil','2026-08-23 14:00:06'),(342,NULL,'login','auth',19,'Login berhasil','2026-08-23 14:38:59'),(343,NULL,'upload','media',52,'Unggah 1 media ke album: Workshop Tata Kelola Data dan Sistem Informasi','2026-08-23 14:38:59');
/*!40000 ALTER TABLE `activity_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `agendas`
--

DROP TABLE IF EXISTS `agendas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `agendas` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(200) NOT NULL,
  `description` text DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `source_url` varchar(500) DEFAULT NULL,
  `start_datetime` datetime NOT NULL,
  `end_datetime` datetime DEFAULT NULL,
  `status` enum('draft','published') NOT NULL DEFAULT 'draft',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_agendas_start` (`start_datetime`),
  KEY `idx_agendas_status` (`status`)
) ENGINE=InnoDB AUTO_INCREMENT=48 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `agendas`
--

LOCK TABLES `agendas` WRITE;
/*!40000 ALTER TABLE `agendas` DISABLE KEYS */;
INSERT INTO `agendas` VALUES (1,'Agenda Kegiatan: Jadwal resmi akan diumumkan','Detail agenda resmi akan diisi oleh tim pengelola Diskominfo.','Diskominfo Provinsi Banten',NULL,'2026-08-17 14:35:00','2026-08-17 17:35:00','draft','2026-08-10 14:35:00'),(21,'Sosialisasi Program PKL dan Magang Periode Baru','Sosialisasi mekanisme pendaftaran dan pelaksanaan PKL serta magang kepada sekolah dan perguruan tinggi mitra.','Aula Diskominfo Provinsi Banten',NULL,'2026-08-28 10:22:28','2026-08-28 12:22:28','draft','2026-08-14 15:22:28'),(22,'Pelatihan Pemanfaatan Aplikasi Layanan Digital','Pelatihan bagi peserta magang mengenai pemanfaatan aplikasi layanan digital milik Pemerintah Provinsi Banten.','Ruang Pelatihan Diskominfo Provinsi Banten',NULL,'2026-09-04 10:22:28','2026-09-04 14:22:28','draft','2026-08-14 15:22:28'),(23,'Rapat Evaluasi Pelaksanaan PKL Periode Berjalan','Evaluasi pelaksanaan PKL bersama pembimbing dan satuan kerja penerima.','Ruang Rapat Diskominfo Provinsi Banten',NULL,'2026-08-04 10:22:28','2026-08-04 12:22:28','draft','2026-08-14 15:22:28'),(24,'Pembekalan Peserta Magang Gelombang II','Pembekalan materi kode etik, tata tertib, dan pengenalan lingkungan kerja bagi peserta magang.','Aula Diskominfo Provinsi Banten',NULL,'2026-08-11 10:22:28','2026-08-11 13:22:28','draft','2026-08-14 15:22:28'),(25,'Penutupan dan Pelepasan Peserta PKL','Kegiatan penutupan dan pelepasan peserta PKL periode berjalan secara resmi.','Aula Diskominfo Provinsi Banten',NULL,'2026-07-25 10:22:28','2026-07-25 12:22:28','draft','2026-08-14 15:22:28'),(44,'Rapat Persiapan Kompetisi Inovasi Pelayanan Publik (KIPP) Tahun 2026','Unsur Dinas Kominfo Statistik dan Persandian Provinsi Banten mengikuti rapat persiapan Kompetisi Inovasi Pelayanan Publik (KIPP) Tahun 2026.','KP3B, Kota Serang','https://diskominfo.bantenprov.go.id/event/diskominfo-ikuti-rapat-persiapan-kipp-tahun-2026','2026-06-22 09:00:00','2026-06-22 12:00:00','published','2026-08-17 19:09:46'),(45,'Rapat Koordinasi Diskominfo Kab/Kota','Kepala Dinas Kominfo Statistik dan Persandian Provinsi Banten Beni Ismail membuka agenda Rapat Koordinasi Diskominfo Kab/Kota dalam rangka penyebarluasan informasi pembangunan.','Aula Kantor Dinas Kominfo Provinsi Banten, KP3B','https://diskominfo.bantenprov.go.id/event/rapat-koordinasi-diskominfo-kabkota','2026-04-13 09:00:00','2026-04-13 12:00:00','published','2026-08-17 19:09:46');
/*!40000 ALTER TABLE `agendas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `album_categories`
--

DROP TABLE IF EXISTS `album_categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `album_categories` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `slug` varchar(60) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_album_cat_slug` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `album_categories`
--

LOCK TABLES `album_categories` WRITE;
/*!40000 ALTER TABLE `album_categories` DISABLE KEYS */;
INSERT INTO `album_categories` VALUES (1,'Workshop','workshop'),(2,'Pelatihan','pelatihan'),(3,'Seminar','seminar'),(4,'Kegiatan PKL','kegiatan-pkl'),(5,'Kegiatan Magang','kegiatan-magang');
/*!40000 ALTER TABLE `album_categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `albums`
--

DROP TABLE IF EXISTS `albums`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `albums` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `category_id` int(10) unsigned DEFAULT NULL,
  `title` varchar(200) NOT NULL,
  `slug` varchar(220) NOT NULL,
  `description` text DEFAULT NULL,
  `event_date` date DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `participants` varchar(255) DEFAULT NULL,
  `cover_image` varchar(255) DEFAULT NULL,
  `status` enum('draft','published') NOT NULL DEFAULT 'draft',
  `created_by` int(10) unsigned DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_albums_slug` (`slug`),
  KEY `idx_albums_category` (`category_id`),
  KEY `idx_albums_status_created` (`status`,`created_at`),
  KEY `idx_albums_event_date` (`event_date`),
  KEY `fk_albums_created_by` (`created_by`),
  CONSTRAINT `fk_albums_category` FOREIGN KEY (`category_id`) REFERENCES `album_categories` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_albums_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=67 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `albums`
--

LOCK TABLES `albums` WRITE;
/*!40000 ALTER TABLE `albums` DISABLE KEYS */;
INSERT INTO `albums` VALUES (52,1,'Workshop Tata Kelola Data dan Sistem Informasi','workshop-tata-kelola-data-sistem-informasi','Dokumentasi kegiatan workshop tata kelola data dan sistem informasi bagi peserta PKL.','2026-08-13','Diskominfo Provinsi Banten',NULL,'02dae247b0c5aa056f9f65f8.jpg','draft',1,'2026-08-14 15:25:11','2026-08-17 16:25:12'),(53,2,'Pelatihan Dasar Penulisan Berita dan Publikasi','pelatihan-dasar-penulisan-berita-publikasi','Dokumentasi pelatihan dasar penulisan berita dan publikasi untuk peserta magang.','2026-08-08','Diskominfo Provinsi Banten',NULL,'533fffe6482b62894e2ad5f2.jpg','draft',1,'2026-08-14 15:25:12','2026-08-17 16:25:12'),(54,3,'Presentasi Akhir Peserta PKL Periode Berjalan','presentasi-akhir-peserta-pkl-periode-berjalan','Dokumentasi kegiatan presentasi akhir hasil pelaksanaan PKL oleh peserta.','2026-07-31','Diskominfo Provinsi Banten',NULL,'8a188d68ecc451fde71e2646.jpg','draft',1,'2026-08-14 15:25:13','2026-08-17 16:25:12');
/*!40000 ALTER TABLE `albums` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `alumni`
--

DROP TABLE IF EXISTS `alumni`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `alumni` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(150) NOT NULL,
  `slug` varchar(170) NOT NULL,
  `photo` varchar(255) DEFAULT NULL,
  `type` enum('pkl','magang') NOT NULL DEFAULT 'pkl',
  `institution` varchar(200) DEFAULT NULL,
  `major` varchar(150) DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `year` int(10) unsigned DEFAULT NULL,
  `division` varchar(150) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `status` enum('draft','published') NOT NULL DEFAULT 'draft',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_alumni_slug` (`slug`),
  KEY `idx_alumni_name` (`name`),
  KEY `idx_alumni_status` (`status`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `alumni`
--

LOCK TABLES `alumni` WRITE;
/*!40000 ALTER TABLE `alumni` DISABLE KEYS */;
INSERT INTO `alumni` VALUES (8,'Ghita Naila Novitasari','ghita-naila-novitasari','3f6493e4b5399a8223309eb7.jpg','magang','Universitas Pamulang','Sistem Informasi','2026-07-14','2026-08-31',2026,'Bidang Statistik, Persandian dan Keamanan Informasi','sangat seru','published','2026-08-20 09:15:17','2026-08-22 11:05:52');
/*!40000 ALTER TABLE `alumni` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `announcements`
--

DROP TABLE IF EXISTS `announcements`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `announcements` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(200) NOT NULL,
  `content` text DEFAULT NULL,
  `source_url` varchar(500) DEFAULT NULL,
  `is_pinned` tinyint(1) NOT NULL DEFAULT 0,
  `status` enum('draft','published') NOT NULL DEFAULT 'draft',
  `published_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_ann_status_pub` (`status`,`published_at`)
) ENGINE=InnoDB AUTO_INCREMENT=35 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `announcements`
--

LOCK TABLES `announcements` WRITE;
/*!40000 ALTER TABLE `announcements` DISABLE KEYS */;
INSERT INTO `announcements` VALUES (1,'Pengumuman: Informasi resmi menunggu data Diskominfo','Seluruh pengumuman di halaman ini bersifat placeholder dan akan diganti dengan pengumuman resmi tim pengelola.',NULL,1,'draft',NULL,'2026-08-10 14:35:00'),(2,'Jadwal Presentasi Peserta','Jadwal presentasi peserta PKL/Magang akan diumumkan melalui kanal resmi instansi.',NULL,0,'draft',NULL,'2026-08-10 14:35:00'),(20,'Penyesuaian Jam Layanan Administrasi','Mulai pekan depan, layanan administrasi penerimaan berkas PKL dan magang dibuka pukul 08.00-15.00 WIB pada hari kerja.',NULL,1,'draft',NULL,'2026-08-14 15:22:28'),(21,'Perpanjangan Batas Waktu Pendaftaran Periode Berjalan','Batas waktu pendaftaran diperpanjang. Detail jadwal akan diumumkan melalui kanal resmi.',NULL,0,'draft',NULL,'2026-08-14 15:22:28'),(22,'Pengumuman Jadwal Presentasi Akhir','Jadwal presentasi akhir peserta PKL telah diterbitkan. Peserta diharapkan menghubungi pembimbing masing-masing.',NULL,0,'draft',NULL,'2026-08-14 15:22:28');
/*!40000 ALTER TABLE `announcements` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `contact_messages`
--

DROP TABLE IF EXISTS `contact_messages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `contact_messages` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `subject` varchar(200) DEFAULT NULL,
  `message` text NOT NULL,
  `ip` varchar(45) DEFAULT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_contact_created` (`created_at`)
) ENGINE=InnoDB AUTO_INCREMENT=26 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `contact_messages`
--

LOCK TABLES `contact_messages` WRITE;
/*!40000 ALTER TABLE `contact_messages` DISABLE KEYS */;
/*!40000 ALTER TABLE `contact_messages` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `document_categories`
--

DROP TABLE IF EXISTS `document_categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `document_categories` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `slug` varchar(60) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_doc_cat_slug` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `document_categories`
--

LOCK TABLES `document_categories` WRITE;
/*!40000 ALTER TABLE `document_categories` DISABLE KEYS */;
INSERT INTO `document_categories` VALUES (1,'Panduan','panduan'),(2,'Formulir','formulir'),(3,'Surat','surat'),(4,'Template','template'),(5,'Laporan','laporan');
/*!40000 ALTER TABLE `document_categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `documents`
--

DROP TABLE IF EXISTS `documents`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `documents` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `category_id` int(10) unsigned DEFAULT NULL,
  `name` varchar(200) NOT NULL,
  `file` varchar(255) NOT NULL,
  `size` int(10) unsigned NOT NULL DEFAULT 0,
  `mime` varchar(100) DEFAULT NULL,
  `downloads` int(10) unsigned NOT NULL DEFAULT 0,
  `uploaded_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_documents_category` (`category_id`),
  CONSTRAINT `fk_documents_category` FOREIGN KEY (`category_id`) REFERENCES `document_categories` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=28 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `documents`
--

LOCK TABLES `documents` WRITE;
/*!40000 ALTER TABLE `documents` DISABLE KEYS */;
/*!40000 ALTER TABLE `documents` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `faqs`
--

DROP TABLE IF EXISTS `faqs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `faqs` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `question` varchar(255) NOT NULL,
  `answer` text NOT NULL,
  `category` varchar(60) DEFAULT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_faqs_active_order` (`is_active`,`sort_order`)
) ENGINE=InnoDB AUTO_INCREMENT=41 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `faqs`
--

LOCK TABLES `faqs` WRITE;
/*!40000 ALTER TABLE `faqs` DISABLE KEYS */;
INSERT INTO `faqs` VALUES (1,'Bagaimana cara mengajukan permohonan PKL?','Tata cara resmi pengajuan PKL akan ditampilkan setelah konfirmasi data dari Diskominfo Provinsi Banten.','Umum',1,1,'2026-08-10 14:35:00'),(2,'Dokumen apa saja yang perlu disiapkan?','Daftar dokumen resmi akan ditampilkan setelah konfirmasi data dari Diskominfo Provinsi Banten.','Umum',2,1,'2026-08-10 14:35:00'),(3,'Berapa lama pelaksanaan PKL/Magang?','Durasi pelaksanaan mengikuti ketentuan resmi instansi dan akan diumumkan kemudian.','Umum',3,1,'2026-08-10 14:35:00'),(4,'Apakah peserta memperoleh sertifikat?','Informasi mengenai sertifikat akan ditampilkan setelah konfirmasi data resmi.','Umum',4,1,'2026-08-10 14:35:00'),(36,'Apa tugas Diskominfo Provinsi Banten?','Dinas Komunikasi, Informatika, Statistik dan Persandian (Diskominfo) Provinsi Banten melaksanakan urusan pemerintahan di bidang komunikasi, informatika, statistik dan persandian sesuai tugas dan fungsinya.\nInformasi selengkapnya tersedia pada halaman Tugas dan Fungsi.','Tentang Diskominfo',1,1,'2026-08-17 19:09:46'),(37,'Di mana alamat Diskominfo Provinsi Banten?','Jl. Syech Nawawi Al-Bantani, KP3B (Kawasan Pusat Pemerintahan Provinsi Banten), Area Gedung Baru OPD Pemprov Banten, Kota Serang, Provinsi Banten.','Tentang Diskominfo',2,1,'2026-08-17 19:09:46'),(38,'Bagaimana cara menghubungi Diskominfo Provinsi Banten?','Email: pkl.diskominfo.banten@gmail.com\nHelp Desk: 08129382693','Tentang Diskominfo',3,1,'2026-08-17 19:09:46');
/*!40000 ALTER TABLE `faqs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `login_attempts`
--

DROP TABLE IF EXISTS `login_attempts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `login_attempts` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `ip` varchar(45) NOT NULL,
  `attempted_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_attempts_user_ip` (`username`,`ip`,`attempted_at`)
) ENGINE=InnoDB AUTO_INCREMENT=107 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `login_attempts`
--

LOCK TABLES `login_attempts` WRITE;
/*!40000 ALTER TABLE `login_attempts` DISABLE KEYS */;
INSERT INTO `login_attempts` VALUES (104,'admin','::1','2026-08-22 10:02:35'),(105,'Kominfo','::1','2026-08-22 10:02:58'),(106,'kominfo','::1','2026-08-22 10:03:09');
/*!40000 ALTER TABLE `login_attempts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `news`
--

DROP TABLE IF EXISTS `news`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `news` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `category_id` int(10) unsigned DEFAULT NULL,
  `title` varchar(200) NOT NULL,
  `slug` varchar(220) NOT NULL,
  `excerpt` varchar(500) DEFAULT NULL,
  `content` longtext DEFAULT NULL,
  `thumbnail` varchar(255) DEFAULT NULL,
  `source_url` varchar(500) DEFAULT NULL,
  `author_id` int(10) unsigned DEFAULT NULL,
  `status` enum('draft','published') NOT NULL DEFAULT 'draft',
  `views` int(10) unsigned NOT NULL DEFAULT 0,
  `published_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_news_slug` (`slug`),
  KEY `idx_news_category` (`category_id`),
  KEY `idx_news_status_pub` (`status`,`published_at`),
  KEY `fk_news_author` (`author_id`),
  CONSTRAINT `fk_news_author` FOREIGN KEY (`author_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_news_category` FOREIGN KEY (`category_id`) REFERENCES `news_categories` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=200 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `news`
--

LOCK TABLES `news` WRITE;
/*!40000 ALTER TABLE `news` DISABLE KEYS */;
INSERT INTO `news` VALUES (139,1,'Informasi Pendaftaran PKL Periode Berikutnya','informasi-pendaftaran-pkl-periode-berikutnya','Informasi resmi mengenai pendaftaran PKL periode berikutnya akan diumumkan oleh tim pengelola. Pantau terus halaman pengumuman.','<p>Pendaftaran Praktik Kerja Lapangan (PKL) di lingkungan Diskominfo Provinsi Banten dilaksanakan sesuai ketentuan yang berlaku.</p><p>Informasi lengkap mengenai jadwal, persyaratan, dan tata cara pendaftaran akan diumumkan melalui halaman pengumuman dan media resmi instansi.</p>','b37951de9d46de1b20ec3fc3.jpg',NULL,1,'draft',12,NULL,'2026-08-14 15:25:11','2026-08-17 16:25:12'),(140,2,'Kegiatan Pembekalan Peserta PKL','kegiatan-pembekalan-peserta-pkl','Kegiatan pembekalan peserta PKL dilaksanakan sebelum peserta ditempatkan. Dokumentasi kegiatan akan tersedia di galeri.','<p>Pembekalan merupakan bagian dari rangkaian kegiatan PKL agar peserta memahami tugas, kode etik, dan budaya kerja di lingkungan instansi.</p><p>Materi pembekalan mencakup pengenalan struktur organisasi, tata tertib, dan tupoksi satuan kerja tempat peserta ditempatkan.</p>','e4bd4bf586f3d568a54180d4.jpg',NULL,1,'draft',8,NULL,'2026-08-14 15:25:11','2026-08-17 16:25:12'),(141,2,'Workshop Tata Kelola Data dan Sistem Informasi','workshop-tata-kelola-data-dan-sistem-informasi','Peserta PKL mengikuti workshop tata kelola data dan sistem informasi sebagai bagian dari kegiatan pengembangan kompetensi.','<p>Workshop Tata Kelola Data dan Sistem Informasi dilaksanakan di lingkungan Diskominfo Provinsi Banten. Kegiatan ini bertujuan membekali peserta PKL dengan pemahaman dasar tentang pengelolaan data dan pemanfaatan sistem informasi.</p><p>Peserta memperoleh materi tentang siklus pengelolaan data, keamanan informasi, serta praktik langsung penggunaan aplikasi yang dikembangkan oleh Diskominfo.</p>','7dbb3d24812c27ebf02dace6.jpg',NULL,1,'draft',29,NULL,'2026-08-14 15:25:11','2026-08-17 16:25:12'),(142,1,'Perpanjangan Batas Waktu Pendaftaran Magang','perpanjangan-batas-waktu-pendaftaran-magang','Batas waktu pendaftaran magang diperpanjang untuk mengakomodasi permohonan yang masih dalam proses verifikasi berkas.','<p>Sehubungan dengan banyaknya permohonan yang masuk, batas waktu pendaftaran program magang diperpanjang hingga periode yang akan diumumkan kemudian.</p><p>Calon peserta yang telah mengirimkan berkas dapat memantau status permohonan melalui kontak resmi yang tertera pada halaman kontak.</p>','fbc7027e3b04a40cb116ce34.jpg',NULL,1,'draft',15,NULL,'2026-08-14 15:25:11','2026-08-17 16:25:12'),(143,3,'Mengenal Profesi Pengelola Sistem Informasi di Pemerintahan','mengenal-profesi-pengelola-sistem-informasi','Karya ringan mengenai peran pengelola sistem informasi di lingkungan pemerintahan, ditulis dari perspektif peserta magang.','<p>Pengelola sistem informasi di lingkungan pemerintahan memiliki peran penting dalam memastikan layanan digital berjalan dengan baik. Mulai dari pengelolaan jaringan, pengembangan aplikasi, hingga pengelolaan data dan statistik.</p><p>Melalui program magang, peserta dapat mengenal langsung praktik pengelolaan teknologi informasi di sektor publik.</p>','ce6237d2a3657e0cf3667a99.jpg',NULL,1,'draft',9,NULL,'2026-08-14 15:25:11','2026-08-23 13:51:31'),(145,1,'Kunjungan Sekolah ke Diskominfo Provinsi Banten','kunjungan-sekolah-ke-diskominfo-provinsi-banten','Siswa dari salah satu sekolah melakukan kunjungan untuk mengenal kegiatan PKL di Diskominfo Provinsi Banten.','<p>Kunjungan ini merupakan bagian dari upaya memperkenalkan program PKL kepada calon peserta dari tingkat SMA/SMK sederajat.</p><p>Dalam kunjungan tersebut, siswa menerima penjelasan mengenai alur pendaftaran, persyaratan, serta kegiatan yang akan dilaksanakan selama PKL.</p>','0ad51dfedc44e991a7ef4da1.jpg',NULL,1,'draft',4,NULL,'2026-08-14 15:25:11','2026-08-17 16:25:12'),(146,3,'Tips Menyusun Laporan Akhir Kegiatan PKL dan Magang','tips-menyusun-laporan-akhir-pkl-magang','Laporan akhir merupakan salah satu syarat penyelesaian kegiatan. Berikut tips menyusun laporan yang baik dan sistematis.','<p>Laporan akhir kegiatan PKL dan magang umumnya memuat pendahuluan, gambaran instansi, uraian kegiatan, hasil dan pembahasan, hingga kesimpulan dan saran.</p><p>Peserta disarankan mencatat kegiatan harian selama pelaksanaan agar bahan laporan lebih mudah disusun dan terdokumentasi dengan baik.</p>','c3367ce4aaf7bcf1c052f89e.jpg',NULL,1,'draft',3,NULL,'2026-08-14 15:25:11','2026-08-17 16:25:12'),(147,2,'Persiapan Penempatan Peserta Magang Periode Baru','persiapan-penempatan-peserta-magang-periode-baru','Tim pengelola melakukan persiapan penempatan peserta magang periode baru ke berbagai satuan kerja.','<p>Penempatan peserta dilakukan dengan mempertimbangkan kompetensi peserta dan kebutuhan satuan kerja penerima.</p><p>Informasi daftar peserta yang telah ditempatkan akan disampaikan melalui pengumuman resmi pada portal ini.</p>','6e22a126906e60f424b31d98.jpg',NULL,1,'draft',0,NULL,'2026-08-14 15:25:11',NULL),(167,2,'Kadis Kominfo Ikuti Presentasi Monev KIP','kadis-kominfo-ikuti-presentasi-monev-kip','Kepala Dinas Kominfo Statistik dan Persandian Provinsi Banten Beni Ismail mengikuti presentasi Monitoring dan Evaluasi (Monev) Keterbukaan Informasi Publik (KIP) yang dilaksanakan oleh Komisi Informasi Provinsi Banten.','<p>Kota Serang &ndash; Kepala Dinas Kominfo Statistik dan Persandian Provinsi Banten Beni Ismail mengikuti presentasi Monitoring dan Evaluasi (Monev) Keterbukaan Informasi Publik (KIP) yang dilaksanakan oleh Komisi Informasi Provinsi Banten, bertempat di KP3B, Kota Serang.</p>\n<p>Kegiatan tersebut merupakan bagian dari evaluasi penyelenggaraan keterbukaan informasi publik di lingkungan Pemerintah Provinsi Banten.</p>','84f897d38e0a44d3.jpg','https://diskominfo.bantenprov.go.id/berita/kadis-kominfo-ikuti-presentasi-monev-kip',1,'draft',3,'2026-07-28 09:00:00','2026-08-17 17:19:54','2026-08-18 09:18:21'),(168,2,'Kadis Kominfo Hadiri Agenda Banten Tech Festival','kadis-kominfo-hadiri-agenda-banten-tech-festival','Kepala Dinas Kominfo Statistik dan Persandian Provinsi Banten Beni Ismail menghadiri agenda Banten Tech Festival yang diselenggarakan di Universitas Tangerang Raya.','<p>Kota Serang &ndash; Kepala Dinas Kominfo Statistik dan Persandian Provinsi Banten Beni Ismail menghadiri agenda Banten Tech Festival yang diselenggarakan di Universitas Tangerang Raya.</p>\n<p>Kehadiran tersebut dalam rangka mendukung pengembangan ekosistem teknologi informasi dan komunikasi di Provinsi Banten.</p>','8c30ba1194f3447c.jpg','https://diskominfo.bantenprov.go.id/berita/kadis-kominfo-hadiri-agenda-banten-tech-festival',1,'draft',0,'2026-07-28 09:00:00','2026-08-17 17:19:54','2026-08-18 09:18:21'),(169,2,'Kadis Kominfo Hadiri Pelantikan Pemuda Muslimin','kadis-kominfo-hadiri-pelantikan-pemuda-muslimin','Kepala Dinas Kominfo Provinsi Banten Beni Ismail mendampingi Wakil Gubernur Banten Achmad Dimiyati Natakusumah pada Pelantikan Pimpinan Wilayah Pemuda Muslimin Indonesia Provinsi Banten Periode 2026-2030.','<p>Kota Serang &ndash; Kepala Dinas Kominfo Provinsi Banten Beni Ismail mendampingi Wakil Gubernur Banten Achmad Dimiyati Natakusumah dalam agenda Pelantikan Pimpinan Wilayah Pemuda Muslimin Indonesia Provinsi Banten Periode 2026-2030, bertempat di Lt. 3 Aula Kantor Dinas Kominfo Provinsi Banten, KP3B, Kota Serang, Sabtu (18/07/2026).</p>\n<p>Pemuda Muslimin Indonesia merupakan organisasi kepemudaan yang didirikan pada tanggal 25 November 1928 di Yogyakarta.</p>','5fe49e0925af4f53.jpg','https://diskominfo.bantenprov.go.id/berita/kadis-kominfo-hadiri-pelantikan-pemuda-muslimin',1,'draft',0,'2026-07-28 09:00:00','2026-08-17 17:19:54','2026-08-18 09:18:21'),(170,2,'Senam Semangat Dinas Kominfo','senam-semangat-dinas-kominfo','Kegiatan senam bersama di lingkungan Dinas Kominfo Provinsi Banten, KP3B, untuk menjaga kesehatan dan kebugaran pegawai.','<p>Kota Serang &ndash; Senam bersama rutin dilaksanakan oleh pegawai Dinas Kominfo Provinsi Banten di area kantor Dinas Kominfo Provinsi Banten, KP3B, Kota Serang, pada Kamis (16/07/2026).</p>\n<p>Kegiatan ini bertujuan menjaga kesehatan jasmani serta mempererat silaturahmi antar pegawai di lingkungan Diskominfo.</p>','adfef4a7371943a6.jpg','https://diskominfo.bantenprov.go.id/berita/senam-semangat-dinas-kominfo',1,'published',9,'2026-07-28 09:00:00','2026-08-17 17:19:54','2026-08-18 13:35:11'),(171,3,'Apa Itu Radikalisme dan Bahayanya?','apa-itu-radikalisme-dan-bahayanya','Karya edukasi tentang pengertian radikalisme, bahayanya bagi stabilitas sosial, regulasi terkait, serta kanal pengaduan konten radikal.','<p>Radikalisme adalah paham atau ideologi yang menghendaki perubahan sosial, politik, atau sistem secara drastis atau ekstrem, seringkali menggunakan cara-cara kekerasan. Di era digital, paham ini kerap disebarkan melalui platform media sosial.</p>\n<p>Bahaya radikalisme terletak pada potensinya merusak stabilitas sosial, memecah belah masyarakat, dan menimbulkan kerugian besar baik secara fisik maupun psikologis. Regulasi yang mengatur antara lain Undang-Undang Nomor 5 Tahun 2018 tentang Pemberantasan Tindak Pidana Terorisme, Peraturan BNPT Nomor 1 Tahun 2025 tentang Kontra Radikalisme, serta Undang-Undang Nomor 1 Tahun 2024 tentang Informasi dan Transaksi Elektronik.</p>\n<p>Masyarakat dapat melaporkan konten yang mengarah pada radikalisme melalui kanal pengaduan seperti aduankonten.id dan lapor.go.id.</p>','fe05bacd2b9647a7.jpg','https://diskominfo.bantenprov.go.id/berita/apa-itu-radikalisme-dan-bahayanya',1,'published',3,'2025-11-17 09:00:00','2026-08-17 17:19:54','2026-08-23 13:51:31'),(198,NULL,'Perf Uji Resize','perf-uji-resize',NULL,'x','152f08fc640a8cad3003bf8d.jpg',NULL,1,'published',0,NULL,'2026-08-17 21:09:49',NULL),(199,2,'Semarak 17 Agustus Bersama Anak PKL & Magang','semarak-17-agustus-bersama-anak-pkl-magang','Kegiatan perayaan Hari Kemerdekaan Republik Indonesia bersama anak PKL dan Magang di lingkungan Diskominfo Provinsi Banten. Kegiatan diisi dengan berbagai perlombaan dan kebersamaan untuk memeriahkan HUT Kemerdekaan RI.','<p>Kota Serang &ndash; Dalam rangka memeriahkan Hari Ulang Tahun Kemerdekaan Republik Indonesia, Dinas Komunikasi, Informatika, Persandian dan Statistik Provinsi Banten menggelar kegiatan Semarak 17 Agustus bersama anak PKL dan Magang di lingkungan kantor Dinas Kominfo Provinsi Banten, KP3B, Kota Serang.</p>\r\n<p>Kegiatan diisi dengan berbagai perlombaan dan kebersamaan untuk memeriahkan HUT Kemerdekaan RI. Seluruh anak PKL dan Magang turut serta dengan penuh semangat dalam rangkaian kegiatan tersebut.</p>',NULL,NULL,1,'published',4,'2026-08-17 09:00:00','2026-08-18 09:18:21','2026-08-22 10:59:23');
/*!40000 ALTER TABLE `news` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `news_categories`
--

DROP TABLE IF EXISTS `news_categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `news_categories` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `slug` varchar(60) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_news_cat_slug` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `news_categories`
--

LOCK TABLES `news_categories` WRITE;
/*!40000 ALTER TABLE `news_categories` DISABLE KEYS */;
INSERT INTO `news_categories` VALUES (1,'Berita','berita'),(2,'Kegiatan','kegiatan'),(3,'Karya','karya');
/*!40000 ALTER TABLE `news_categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `news_photos`
--

DROP TABLE IF EXISTS `news_photos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `news_photos` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `news_id` int(10) unsigned NOT NULL,
  `group_name` varchar(120) DEFAULT NULL,
  `caption` varchar(255) DEFAULT NULL,
  `image` varchar(255) NOT NULL,
  `thumbnail` varchar(255) DEFAULT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_np_news` (`news_id`),
  CONSTRAINT `fk_np_news` FOREIGN KEY (`news_id`) REFERENCES `news` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `news_photos`
--

LOCK TABLES `news_photos` WRITE;
/*!40000 ALTER TABLE `news_photos` DISABLE KEYS */;
/*!40000 ALTER TABLE `news_photos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pages`
--

DROP TABLE IF EXISTS `pages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pages` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(200) NOT NULL,
  `slug` varchar(220) NOT NULL,
  `content` longtext DEFAULT NULL,
  `source_url` varchar(500) DEFAULT NULL,
  `status` enum('draft','published') NOT NULL DEFAULT 'published',
  `updated_at` datetime DEFAULT NULL ON UPDATE current_timestamp(),
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_pages_slug` (`slug`),
  KEY `idx_pages_status` (`status`)
) ENGINE=InnoDB AUTO_INCREMENT=65 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pages`
--

LOCK TABLES `pages` WRITE;
/*!40000 ALTER TABLE `pages` DISABLE KEYS */;
INSERT INTO `pages` VALUES (1,'Profil Program','profil-program','<p>Portal ini merupakan pusat informasi resmi program <strong>Praktik Kerja Lapangan (PKL)</strong> dan <strong>Magang</strong> di lingkungan Dinas Komunikasi, Informatika, Statistik dan Persandian (Diskominfo) Provinsi Banten.</p><p>Diskominfo Provinsi Banten dibentuk berdasarkan Peraturan Daerah Nomor 8 Tahun 2016 dan sejak awal tahun 2017 menyelenggarakan urusan komunikasi, informatika, statistik, dan persandian serta menjadi <em>leading sector</em> penyelenggaraan pemerintahan berbasis elektronik (SPBE) di lingkungan Pemerintah Provinsi Banten.</p><p>Melalui program ini peserta memperoleh pengalaman kerja nyata di lingkungan pemerintahan dengan pendampingan pembimbing dari instansi:</p><ul><li><strong>PKL</strong> &mdash; bagi siswa aktif SMK/sederajat;</li><li><strong>Magang</strong> &mdash; bagi mahasiswa aktif perguruan tinggi.</li></ul><p>Persyaratan dan alur pendaftaran tersedia pada halaman <a href=\"?page=layanan\">Layanan</a>. Pertanyaan dapat disampaikan melalui Help Desk 08129382693 atau email pkl.diskominfo.banten@gmail.com.</p>','https://diskominfo.bantenprov.go.id/selayang-pandang','published','2026-08-23 13:18:57','2026-08-11 12:41:14'),(2,'Tujuan Program','tujuan','<p>Program PKL dan Magang di lingkungan Diskominfo Provinsi Banten diselenggarakan untuk:</p><ol><li>Menyediakan wadah pembelajaran dan pengembangan kompetensi bagi siswa SMK/sederajat dan mahasiswa;</li><li>Memberikan pengalaman kerja nyata di lingkungan pemerintahan, khususnya bidang komunikasi, informatika, statistik, dan persandian;</li><li>Menerapkan ilmu dan keterampilan yang diperoleh dari sekolah maupun perguruan tinggi dalam pekerjaan sesungguhnya;</li><li>Membentuk etos kerja, kedisiplinan, dan profesionalisme peserta;</li><li>Menjalin kemitraan antara instansi dengan sekolah dan perguruan tinggi.</li></ol>',NULL,'published','2026-08-23 13:09:16','2026-08-11 12:41:14'),(3,'Visi & Misi','visi-misi','\n<h3>Visi</h3>\n<p>&ldquo;BANTEN MAJU, ADIL MERATA TIDAK KORUPSI&rdquo;</p>\n<h3>Misi</h3>\n<ol>\n    <li>Mewujudkan masyarakat bermoral Pancasila dan demokratis berlandaskan iman dan taqwa melalui reformasi birokrasi yang berintegritas, adaptif dan tangguh;</li>\n    <li>Mendorong kemajuan ekonomi secara inklusif melalui penguatan sektor kreatif, unggulan dan potensial;</li>\n    <li>Mewujudkan sumber daya manusia yang berintegritas, berdaya saing, berkualitas, inovatif dan tidak diskriminatif;</li>\n    <li>Mewujudkan pemerataan pembangunan wilayah yang didukung infrastruktur berkualitas;</li>\n    <li>Mewujudkan pembangunan berkelanjutan secara holistik dan resiliensi terhadap bencana.</li>\n</ol>','https://diskominfo.bantenprov.go.id/visi-misi','published','2026-08-23 13:09:16','2026-08-11 12:41:14'),(6,'Informasi PKL','informasi-pkl','<h3>Pengertian PKL</h3>\n<p>Praktik Kerja Lapangan (PKL) adalah kegiatan pembelajaran yang dilaksanakan oleh siswa SMK/sederajat di dunia kerja atau instansi untuk menerapkan dan mengembangkan kompetensi keahlian yang telah dipelajari di sekolah. Melalui PKL, siswa mengenal lingkungan kerja yang sesungguhnya sekaligus mempersiapkan diri memasuki dunia kerja setelah lulus.</p>\n<h3>Sasaran Peserta PKL</h3>\n<p>Program PKL ditujukan bagi siswa aktif SMK/sederajat yang memiliki kompetensi keahlian yang relevan dengan bidang tugas dan kegiatan Diskominfo Provinsi Banten, seperti teknologi informasi dan komunikasi, multimedia, rekayasa perangkat lunak, administrasi perkantoran, dan bidang relevan lainnya.</p>\n<h3>Tujuan PKL</h3>\n<ul>\n    <li>Memberikan pengalaman kerja nyata kepada siswa sesuai dengan kompetensi keahliannya;</li>\n    <li>Mengembangkan keterampilan teknis dan nonteknis (soft skills) siswa;</li>\n    <li>Membentuk sikap kerja yang disiplin, bertanggung jawab, dan profesional;</li>\n    <li>Menjembatani dunia pendidikan dengan dunia kerja;</li>\n    <li>Menumbuhkan etos kerja dan kesiapan siswa memasuki dunia kerja.</li>\n</ul>\n<h3>Manfaat PKL</h3>\n<ul>\n    <li>Bagi siswa: memperoleh pengalaman kerja, wawasan, dan keterampilan yang tidak diperoleh sepenuhnya di sekolah;</li>\n    <li>Bagi sekolah: menjalin kemitraan dengan instansi serta menyesuaikan pembelajaran dengan kebutuhan dunia kerja;</li>\n    <li>Bagi instansi: memperoleh dukungan dalam pelaksanaan tugas serta turut menyiapkan sumber daya manusia yang kompeten.</li>\n</ul>\n<h3>Gambaran Kegiatan PKL</h3>\n<p>Selama PKL, siswa ditempatkan pada bidang atau unit kerja sesuai kompetensi dan ketersediaan program. Kegiatan dilaksanakan di bawah arahan pembimbing instansi dan mencakup pengenalan lingkungan kerja, pelaksanaan tugas yang relevan dengan bidang penempatan, serta penyusunan laporan PKL.</p>\n<h3>Hal yang Perlu Dipersiapkan Peserta</h3>\n<ul>\n    <li>Menguasai dasar-dasar kompetensi keahlian yang dipersiapkan oleh sekolah;</li>\n    <li>Menyiapkan perlengkapan pribadi dan dokumen administrasi yang dibutuhkan;</li>\n    <li>Mempelajari informasi mengenai instansi tempat PKL;</li>\n    <li>Menjaga kesehatan dan kesiapan fisik selama kegiatan berlangsung;</li>\n    <li>Menyiapkan laporan dan dokumentasi kegiatan.</li>\n</ul>\n<h3>Etika dan Kedisiplinan Peserta</h3>\n<ul>\n    <li>Hadir tepat waktu dan mematuhi jam kerja instansi;</li>\n    <li>Berpakaian rapi, sopan, dan sesuai ketentuan instansi;</li>\n    <li>Menghormati pegawai, pembimbing, dan sesama peserta;</li>\n    <li>Bersikap jujur, santun, dan bertanggung jawab;</li>\n    <li>Menjaga nama baik sekolah dan instansi.</li>\n</ul>\n<h3>Kewajiban Peserta Selama Kegiatan</h3>\n<ul>\n    <li>Mengikuti seluruh kegiatan sesuai jadwal dan arahan pembimbing;</li>\n    <li>Mematuhi tata tertib dan ketentuan yang berlaku di instansi;</li>\n    <li>Menjaga keamanan dan kerahasiaan informasi instansi;</li>\n    <li>Melaporkan kendala atau keperluan kepada pembimbing atau pengelola;</li>\n    <li>Menyelesaikan laporan PKL dan menyerahkannya sesuai ketentuan.</li>\n</ul>\n<h3>Catatan</h3>\n<p>Informasi pada halaman ini merupakan gambaran umum kegiatan PKL. Ketentuan pelaksanaan dapat disesuaikan dengan kebijakan dan ketersediaan program pada periode berjalan.</p>',NULL,'published','2026-08-20 09:50:44','2026-08-11 12:41:14'),(7,'Informasi Magang','informasi-magang','<h3>Pengertian Magang</h3>\n<p>Magang adalah kegiatan pembelajaran dan pengembangan diri yang dilaksanakan oleh mahasiswa di suatu instansi atau organisasi untuk memperoleh pengalaman kerja, mengembangkan kompetensi, serta menerapkan ilmu yang diperoleh di perguruan tinggi dalam lingkungan kerja yang sesungguhnya.</p>\n<h3>Sasaran Peserta Magang</h3>\n<p>Program magang ditujukan bagi mahasiswa aktif dari perguruan tinggi yang memiliki bidang atau kompetensi yang relevan dengan tugas dan fungsi Diskominfo Provinsi Banten, antara lain di bidang teknologi informasi, komunikasi, informatika, sistem informasi, statistika, administrasi publik, dan bidang relevan lainnya.</p>\n<h3>Tujuan Magang</h3>\n<ul>\n    <li>Memberikan pengalaman kerja nyata kepada mahasiswa;</li>\n    <li>Mengembangkan kompetensi teknis dan profesionalisme mahasiswa;</li>\n    <li>Menerapkan ilmu pengetahuan yang diperoleh di perguruan tinggi;</li>\n    <li>Menjembatani dunia akademik dengan dunia kerja;</li>\n    <li>Mempersiapkan mahasiswa menghadapi dunia kerja dan karier profesional.</li>\n</ul>\n<h3>Manfaat Magang</h3>\n<ul>\n    <li>Bagi mahasiswa: memperoleh pengalaman, jejaring, dan wawasan dunia kerja;</li>\n    <li>Bagi perguruan tinggi: memperkuat keterkaitan kurikulum dengan kebutuhan dunia kerja;</li>\n    <li>Bagi instansi: memperoleh dukungan terhadap pelaksanaan tugas serta turut mengembangkan sumber daya manusia yang kompeten.</li>\n</ul>\n<h3>Gambaran Kegiatan Magang</h3>\n<p>Selama magang, mahasiswa ditempatkan pada bidang atau unit kerja sesuai kompetensi dan ketersediaan program. Kegiatan dilaksanakan di bawah arahan pembimbing dan mencakup penyelesaian tugas yang relevan, dukungan terhadap kegiatan unit kerja, serta penyusunan laporan magang.</p>\n<h3>Pengembangan Kompetensi</h3>\n<p>Selama magang, mahasiswa berkesempatan mengembangkan kompetensi antara lain:</p>\n<ul>\n    <li>Kompetensi teknis sesuai bidang penempatan;</li>\n    <li>Kemampuan komunikasi dan kerja sama tim;</li>\n    <li>Kemampuan analisis dan pemecahan masalah;</li>\n    <li>Profesionalisme, etos kerja, dan manajemen waktu;</li>\n    <li>Pemahaman mengenai tata kelola dan layanan pemerintahan berbasis elektronik.</li>\n</ul>\n<h3>Hal yang Perlu Dipersiapkan Mahasiswa</h3>\n<ul>\n    <li>Menguasai bidang atau kompetensi yang relevan dengan penempatan;</li>\n    <li>Menyiapkan dokumen administrasi dan perlengkapan yang dibutuhkan;</li>\n    <li>Mempelajari profil dan tugas pokok instansi tempat magang;</li>\n    <li>Menyiapkan rencana dan target pembelajaran selama magang;</li>\n    <li>Menjaga kesehatan dan kesiapan fisik selama kegiatan.</li>\n</ul>\n<h3>Etika dan Profesionalitas Peserta</h3>\n<ul>\n    <li>Mematuhi jam kerja, tata tertib, dan budaya kerja instansi;</li>\n    <li>Berpakaian rapi dan profesional sesuai ketentuan;</li>\n    <li>Menjaga sikap, tutur kata, dan perilaku profesional;</li>\n    <li>Menghormati pegawai, pembimbing, dan sesama peserta;</li>\n    <li>Menjaga kerahasiaan dan keamanan informasi instansi.</li>\n</ul>\n<h3>Kewajiban Peserta Selama Kegiatan</h3>\n<ul>\n    <li>Mengikuti kegiatan sesuai jadwal dan arahan pembimbing;</li>\n    <li>Mematuhi seluruh ketentuan yang berlaku di instansi;</li>\n    <li>Menjaga keamanan dan kerahasiaan informasi instansi;</li>\n    <li>Melaporkan perkembangan dan kendala kepada pembimbing;</li>\n    <li>Menyelesaikan laporan magang dan menyerahkannya sesuai ketentuan.</li>\n</ul>\n<h3>Catatan</h3>\n<p>Informasi pada halaman ini merupakan gambaran umum kegiatan magang. Ketentuan pelaksanaan dapat disesuaikan dengan kebijakan dan ketersediaan program pada periode berjalan.</p>',NULL,'published','2026-08-20 09:50:44','2026-08-11 12:41:14'),(8,'Persyaratan PKL','persyaratan-pkl','<p>Berikut merupakan persyaratan umum pengajuan Praktik Kerja Lapangan (PKL) di lingkungan Diskominfo Provinsi Banten:</p>\n<ol>\n    <li>Siswa aktif SMK/sederajat;</li>\n    <li>Menyertakan surat pengantar atau permohonan dari sekolah;</li>\n    <li>Mendapatkan persetujuan dari sekolah;</li>\n    <li>Mengajukan permohonan PKL kepada Diskominfo Provinsi Banten;</li>\n    <li>Menentukan periode pelaksanaan PKL;</li>\n    <li>Mematuhi tata tertib instansi;</li>\n    <li>Menjaga etika dan kedisiplinan selama kegiatan;</li>\n    <li>Mengikuti arahan pembimbing;</li>\n    <li>Menjaga keamanan dan kerahasiaan informasi instansi;</li>\n    <li>Menyelesaikan laporan PKL sesuai ketentuan.</li>\n</ol>\n<h3>Catatan</h3>\n<p>Persyaratan tersebut merupakan panduan umum. Ketentuan, periode pelaksanaan, kuota, dan prosedur penerimaan dapat berubah sesuai kebijakan dan ketersediaan program pada periode berjalan. Informasi lebih lanjut dapat diperoleh dari tim pengelola program.</p>',NULL,'published','2026-08-20 09:50:44','2026-08-11 12:41:14'),(9,'Persyaratan Magang','persyaratan-magang','<p>Berikut merupakan persyaratan umum pengajuan Magang di lingkungan Diskominfo Provinsi Banten:</p>\n<ol>\n    <li>Mahasiswa aktif dari perguruan tinggi;</li>\n    <li>Menyertakan surat pengantar atau permohonan dari perguruan tinggi;</li>\n    <li>Mendapatkan persetujuan dari perguruan tinggi;</li>\n    <li>Mengajukan permohonan magang kepada Diskominfo Provinsi Banten;</li>\n    <li>Menentukan periode pelaksanaan magang;</li>\n    <li>Memiliki bidang atau kompetensi yang relevan dengan tugas dan fungsi instansi;</li>\n    <li>Mematuhi tata tertib instansi;</li>\n    <li>Menjaga etika dan profesionalitas selama kegiatan;</li>\n    <li>Mengikuti arahan pembimbing;</li>\n    <li>Menjaga keamanan dan kerahasiaan informasi instansi;</li>\n    <li>Menyelesaikan laporan magang sesuai ketentuan.</li>\n</ol>\n<h3>Catatan</h3>\n<p>Persyaratan tersebut merupakan panduan umum. Ketentuan, periode pelaksanaan, kuota, dan prosedur penerimaan dapat berubah sesuai kebijakan dan ketersediaan program pada periode berjalan. Informasi lebih lanjut dapat diperoleh dari tim pengelola program.</p>',NULL,'published','2026-08-20 09:50:44','2026-08-11 12:41:14'),(10,'Alur Pendaftaran','alur-pendaftaran','<h3>Alur Umum Pendaftaran PKL dan Magang</h3>\n<p>Berikut merupakan alur umum pendaftaran program Praktik Kerja Lapangan (PKL) dan Magang di lingkungan Diskominfo Provinsi Banten:</p>\n<ol>\n    <li><strong>Menyiapkan persyaratan.</strong> Calon peserta menyiapkan seluruh dokumen dan kelengkapan persyaratan sesuai ketentuan yang berlaku.</li>\n    <li><strong>Mengajukan permohonan.</strong> Permohonan diajukan oleh sekolah atau perguruan tinggi kepada Diskominfo Provinsi Banten.</li>\n    <li><strong>Verifikasi permohonan.</strong> Tim pengelola melakukan verifikasi terhadap kelengkapan dan kesesuaian permohonan yang diajukan.</li>\n    <li><strong>Menunggu informasi hasil.</strong> Calon peserta menunggu informasi hasil verifikasi dan keputusan penerimaan.</li>\n    <li><strong>Pelaksanaan PKL/Magang.</strong> Peserta melaksanakan kegiatan sesuai bidang penempatan, jadwal, dan ketentuan yang berlaku.</li>\n    <li><strong>Pembimbingan dan evaluasi.</strong> Peserta mengikuti pembimbingan dari pembimbing serta menjalani evaluasi selama kegiatan berlangsung.</li>\n    <li><strong>Penyelesaian kegiatan dan laporan.</strong> Peserta menyelesaikan kegiatan, menyusun laporan, dan menyerahkannya sesuai ketentuan yang berlaku.</li>\n</ol>\n<h3>Catatan</h3>\n<p>Alur tersebut merupakan gambaran umum dan dapat disesuaikan dengan ketentuan serta ketersediaan program pada periode berjalan. Informasi lebih lanjut dapat ditanyakan kepada tim pengelola program.</p>',NULL,'published','2026-08-20 09:50:44','2026-08-11 12:41:14'),(37,'Tugas dan Fungsi','tugas-dan-fungsi','\n<p>Dinas Komunikasi, Informatika, Statistik dan Persandian Provinsi Banten terbentuk berdasarkan Peraturan Daerah Nomor 8 Tahun 2016 tentang Pembentukan dan Susunan Perangkat Daerah Provinsi Banten.</p>\n<p>Dalam melaksanakan tugasnya, Dinas menyelenggarakan fungsi dan wewenang pengelolaan informasi dan komunikasi publik Pemerintah Daerah Provinsi, pengelolaan nama domain dan sub domain di lingkup Pemerintah Daerah provinsi, pengelolaan e-Government di lingkup Pemerintah Daerah provinsi, penyelenggaraan statistik sektoral, penetapan pola komunikasi sandi antar perangkat daerah provinsi, serta pelaksanaan tugas lain yang diberikan oleh Gubernur.</p>\n<p>Berdasarkan Peraturan Gubernur Banten Nomor 48 Tahun 2022 pasal 38 ayat (2), tugas Dinas Kominfo antara lain:</p>\n<ol>\n    <li>Pengelolaan informasi dan komunikasi publik;</li>\n    <li>Pengelolaan aplikasi informatika;</li>\n    <li>Penyelenggaraan persandian untuk pengamanan informasi;</li>\n    <li>Terselenggaranya statistik sektoral; dan</li>\n    <li>Pelaksanaan fungsi lain yang diberikan oleh Gubernur terkait dengan tugas dan fungsinya sesuai peraturan perundang-undangan.</li>\n</ol>\n<p>Ketentuan terbaru mengenai kedudukan, susunan organisasi, tugas pokok dan fungsi diatur melalui Peraturan Gubernur Banten Nomor 48 Tahun 2025.</p>','https://diskominfo.bantenprov.go.id/tugas-dan-fungsi','published',NULL,'2026-08-17 17:19:54'),(38,'Unit Kerja','unit-kerja','\n<p>Berdasarkan Peraturan Gubernur Nomor 48 Tahun 2025 tentang Perubahan atas Peraturan Gubernur Nomor 48 Tahun 2022 tentang Kedudukan, Susunan Organisasi, Tugas Pokok dan Fungsi, Uraian Tugas, dan Tata Kerja Dinas Daerah, susunan organisasi Dinas Komunikasi, Informatika, Statistik dan Persandian Provinsi Banten terdiri atas:</p>\n<ol>\n    <li>Kepala Dinas;</li>\n    <li>Sekretaris Dinas;</li>\n    <li>Kepala Sub Bagian Umum dan Kepegawaian;</li>\n    <li>Kepala Bidang Pengelolaan Informasi dan Kemitraan Komunikasi;</li>\n    <li>Kepala Bidang Ekosistem Pemerintahan Digital;</li>\n    <li>Kepala Bidang Statistik, Persandian dan Keamanan Informasi;</li>\n    <li>Kelompok Jabatan Fungsional;</li>\n    <li>Unit Pelaksana Teknis Daerah (UPTD).</li>\n</ol>','https://diskominfo.bantenprov.go.id/unit-kerja','published','2026-08-17 19:09:46','2026-08-17 17:19:54'),(39,'Informasi Pelayanan','informasi-pelayanan','<p>Dinas Komunikasi, Informatika, Statistik dan Persandian Provinsi Banten memberikan pelayanan melalui tiga bidang dan satu sekretariat sesuai tugas pokok dan fungsinya, yaitu:</p>\n<ol>\n    <li><strong>Bidang Ekosistem Pemerintahan Digital</strong> &mdash; menangani urusan terkait pengembangan dan pengelolaan ekosistem pemerintahan berbasis elektronik (e-Government) di lingkungan Pemerintah Provinsi Banten;</li>\n    <li><strong>Bidang Pengelolaan Informasi dan Kemitraan Komunikasi</strong> &mdash; menangani urusan pengelolaan informasi dan komunikasi publik serta kemitraan komunikasi;</li>\n    <li><strong>Bidang Statistik, Persandian dan Keamanan Informasi</strong> &mdash; menangani urusan statistik sektoral, persandian, dan keamanan informasi;</li>\n    <li><strong>Sekretariat</strong> &mdash; memberikan dukungan administratif, keuangan, kepegawaian, dan umum bagi penyelenggaraan tugas Dinas.</li>\n</ol>\n<p>Daftar lengkap jenis layanan Dinas Kominfo, Statistik dan Persandian Provinsi Banten dapat diakses pada tautan berikut:</p>\n<ul>\n    <li><a href=\"https://drive.google.com/file/d/1a65_8ac93UiGFqfEEE_1Mr-R9b09QGv7/view?usp=sharing\" target=\"_blank\" rel=\"noopener\">Jenis-Jenis Layanan Dinas Kominfo, Statistik dan Persandian Provinsi Banten</a></li>\n</ul>\n<p>Untuk pertanyaan seputar pelayanan, silakan menghubungi Help Desk <strong>08129382693</strong> atau email <strong>pkl.diskominfo.banten@gmail.com</strong>.</p>','https://diskominfo.bantenprov.go.id/informasi-pelayanan','published','2026-08-23 13:18:57','2026-08-17 17:19:54'),(40,'PPID ΓÇö Keterbukaan Informasi Publik','ppid','<h3>Pengertian PPID</h3>\n<p>Pejabat Pengelola Informasi dan Dokumentasi (PPID) adalah pejabat yang bertanggung jawab di bidang penyimpanan, pendokumentasian, penyediaan, dan/atau pelayanan informasi di badan publik. PPID Pelaksana Diskominfo Provinsi Banten memberikan layanan informasi publik sesuai Undang-Undang Nomor 14 Tahun 2008 tentang Keterbukaan Informasi Publik dan peraturan pelaksanaannya.</p>\n<h3>Fungsi PPID</h3>\n<p>Berdasarkan informasi resmi PPID Pelaksana Diskominfo Provinsi Banten, fungsi utama PPID antara lain:</p>\n<ul>\n    <li>Membantu PPID Utama melaksanakan tanggung jawab, tugas, dan kewenangannya;</li>\n    <li>Menyampaikan informasi dan dokumentasi kepada PPID Utama secara berkala;</li>\n    <li>Melaksanakan kebijakan teknis informasi dan dokumentasi sesuai tugas pokok dan fungsinya;</li>\n    <li>Menjamin ketersediaan dan akselerasi layanan informasi dan dokumentasi bagi pemohon informasi dengan mengedepankan prinsip pelayanan prima;</li>\n    <li>Mengumpulkan, mengolah, dan mengompilasi bahan serta data menjadi bahan informasi publik;</li>\n    <li>Mengelola website dan media sosial resmi perangkat daerah dalam penyediaan dan penyebaran informasi publik;</li>\n    <li>Menyampaikan laporan pelaksanaan kebijakan teknis dan pelayanan informasi dan dokumentasi kepada PPID Utama.</li>\n</ul>\n<h3>Keterbukaan Informasi Publik</h3>\n<p>Keterbukaan informasi publik dijamin oleh Undang-Undang Nomor 14 Tahun 2008 yang memberikan hak kepada setiap orang untuk memperoleh informasi publik dari badan publik, kecuali informasi yang dikecualikan berdasarkan ketentuan peraturan perundang-undangan.</p>\n<h3>Jenis Informasi Publik</h3>\n<p>Informasi publik yang disediakan PPID Pelaksana Diskominfo Provinsi Banten terdiri atas:</p>\n<ul>\n    <li><strong>Informasi berkala</strong> &mdash; informasi yang wajib diumumkan secara berkala;</li>\n    <li><strong>Informasi serta merta</strong> &mdash; informasi yang wajib diumumkan segera tanpa penundaan;</li>\n    <li><strong>Informasi setiap saat</strong> &mdash; informasi yang wajib tersedia setiap saat.</li>\n</ul>\n<h3>Dasar Hukum</h3>\n<ul>\n    <li>Undang-Undang Nomor 14 Tahun 2008 tentang Keterbukaan Informasi Publik;</li>\n    <li>Peraturan Pemerintah Nomor 61 Tahun 2010 tentang Pelaksanaan Undang-Undang Nomor 14 Tahun 2008 tentang Keterbukaan Informasi Publik;</li>\n    <li>Peraturan Komisi Informasi Nomor 1 Tahun 2021 tentang Standar Layanan Informasi Publik;</li>\n    <li>Peraturan Menteri Komunikasi dan Informatika Nomor 4 Tahun 2024;</li>\n    <li>Peraturan Gubernur Banten Nomor 67 Tahun 2021 tentang Pedoman Pelayanan Informasi Publik dan Dokumentasi di Lingkungan Pemerintah Provinsi Banten.</li>\n</ul>\n<h3>Cara Memperoleh Informasi Publik</h3>\n<p>Permohonan informasi publik dapat disampaikan kepada PPID Pelaksana Diskominfo Provinsi Banten melalui:</p>\n<ul>\n    <li>Alamat: Jl. Syech Nawawi Al-Bantani, KP3B (Kawasan Pusat Pemerintah Provinsi Banten), Area Gedung Baru OPD Provinsi Banten, Kecamatan Curug, Kota Serang, Provinsi Banten;</li>\n    \n    <li>Telepon: 08129382693;</li>\n    <li>Aplikasi SP4N LAPOR! melalui laman <a href=\"https://www.lapor.go.id/\" target=\"_blank\" rel=\"noopener\">lapor.go.id</a>;</li>\n    <li>Layanan permohonan informasi online melalui <a href=\"https://ppid.bantenprov.go.id/permohonan\" target=\"_blank\" rel=\"noopener\">PPID Provinsi Banten</a>.</li>\n</ul>\n<p>Detail dokumen informasi berkala dan setiap saat tersedia pada laman PPID di website resmi Diskominfo Provinsi Banten.</p>','https://diskominfo.bantenprov.go.id/PPID%20Pelaksana','published','2026-08-23 13:18:57','2026-08-17 17:19:54'),(41,'Profil Pejabat Diskominfo','profil-pejabat','\n<p>Berikut pejabat struktural Dinas Komunikasi, Informatika, Statistik dan Persandian Provinsi Banten sebagaimana tercantum pada laman Profil Pegawai Diskominfo:</p>\n<ol>\n    <li><strong>Beni Ismail, S.STP., M.Si.</strong> ΓÇö Kepala Dinas (Pembina Utama Muda, IV/C);</li>\n    <li><strong>H. Karna Wijaya, SH., MH.</strong> ΓÇö Sekretaris Dinas (Pembina Tk. I, IV/b);</li>\n    <li><strong>Trenggono Wahyu Pratomo, SE., M.Si.</strong> ΓÇö Kepala Sub Bagian Umum dan Kepegawaian (Pembina Tk. I, IV/b);</li>\n    <li><strong>Sofyan Nuryaman, SH</strong> ΓÇö Kepala Bidang Ekosistem Pemerintahan Digital (Penata Tk. I, III/d);</li>\n    <li><strong>Akhmad Subhan Syafaat, SH.</strong> ΓÇö Kepala Bidang Pengelolaan Informasi dan Kemitraan Komunikasi (Pembina Tk. I, IV/b);</li>\n    <li><strong>Chobir Sabbaha, S.Si</strong> ΓÇö Kepala Bidang Statistik, Persandian dan Keamanan Informasi (Penata Tk. I, III/d).</li>\n</ol>\n<p>Daftar lengkap pegawai (NIP, pangkat/golongan, dan jabatan fungsional) tersedia pada laman Profil Pegawai Diskominfo di website resmi Diskominfo Provinsi Banten (tautan pada bagian Sumber halaman ini).</p>','https://diskominfo.bantenprov.go.id/profil-pejabat-struktural','published','2026-08-20 09:52:00','2026-08-17 17:24:29'),(56,'Sejarah Dinas','sejarah-dinas','\n<p>Dinas Komunikasi, Informatika, Statistik dan Persandian Provinsi Banten terbentuk berdasarkan Peraturan Daerah Nomor 8 Tahun 2016 tentang Pembentukan dan Susunan Perangkat Daerah Provinsi Banten. Sebelumnya, urusan komunikasi dan informatika di Provinsi Banten dilaksanakan oleh Biro Humas dan Protokol Setda serta Bagian Kominfo pada Dinas Perhubungan, Komunikasi dan Informatika (Dishubkominfo).</p>\n<p>Dalam melaksanakan tugasnya, Dinas menyelenggarakan fungsi pengelolaan informasi dan komunikasi publik, pengelolaan nama domain yang ditetapkan Pemerintah Pusat dan sub domain di lingkup Pemerintah Daerah provinsi, pengelolaan e-Government, penyelenggaraan statistik sektoral, penetapan pola komunikasi sandi antar perangkat daerah provinsi, serta pelaksanaan tugas lain yang diberikan oleh Gubernur.</p>\n<p>Struktur organisasi Diskominfo Provinsi Banten telah mengalami beberapa penyesuaian, antara lain melalui Peraturan Gubernur Nomor 83 Tahun 2016 (klasifikasi Dinas tipe B), Peraturan Gubernur Nomor 58 Tahun 2020 (perubahan nomenklatur), Peraturan Gubernur Nomor 48 Tahun 2022, dan terakhir Peraturan Gubernur Banten Nomor 48 Tahun 2025.</p>\n<h3>Susunan Organisasi Terkini (Pergub Nomor 48 Tahun 2025)</h3>\n<ol>\n    <li>Kepala Dinas;</li>\n    <li>Sekretaris Dinas;</li>\n    <li>Kepala Sub Bagian Umum dan Kepegawaian;</li>\n    <li>Kepala Bidang Pengelolaan Informasi dan Kemitraan Komunikasi;</li>\n    <li>Kepala Bidang Ekosistem Pemerintahan Digital;</li>\n    <li>Kepala Bidang Statistik, Persandian dan Keamanan Informasi;</li>\n    <li>Kelompok Jabatan Fungsional;</li>\n    <li>Unit Pelaksana Teknis Daerah (UPTD).</li>\n</ol>\n<h3>Kepala Dinas Kominfo dari Masa ke Masa</h3>\n<ol>\n    <li>Komari, S.Pd., M.M. (Januari 2017 &ndash; 2 Maret 2020);</li>\n    <li>Ir. Hj. Eneng Nurcahyati (3 Maret 2020 &ndash; 31 Agustus 2022);</li>\n    <li>Plt. Didi Hadiyatna, SE., M.M. (6 September 2022 &ndash; 2 Mei 2023);</li>\n    <li>Plt. Nana Suryana, ST., M.Si. (2 Mei 2023 &ndash; 19 Februari 2025);</li>\n    <li>Plt. Arif Agus Rakhman, S.IP., M.Si. (19 Februari 2025 &ndash; 3 November 2025);</li>\n    <li>Beni Ismail, S.STP., M.Si. (3 November 2025 &ndash; saat ini).</li>\n</ol>','https://diskominfo.bantenprov.go.id/sejarah-dinas','published',NULL,'2026-08-17 19:09:39'),(57,'Informasi Setiap Saat','informasi-setiap-saat','\n<p>Pejabat Pengelola Informasi dan Dokumentasi (PPID) Pelaksana Dinas Komunikasi, Informatika, Statistik dan Persandian Provinsi Banten menyediakan informasi yang wajib disediakan dan diumumkan setiap saat sesuai Undang-Undang Nomor 14 Tahun 2008 tentang Keterbukaan Informasi Publik.</p>\n<h3>Program Prioritas dan Maklumat Pelayanan</h3>\n<ul>\n    <li><a href=\"https://drive.google.com/file/d/1rhOGwMncsvZ4ct_ba3FAXFHsj0ofg1mT/view?usp=sharing\" target=\"_blank\" rel=\"noopener\">Program Prioritas Pemerintah Provinsi Banten 2024-2025</a></li>\n    <li><a href=\"https://drive.google.com/file/d/1dBKTov6_S5MMLaTOI_mN1ju9eJZJvj9h/view?usp=sharing\" target=\"_blank\" rel=\"noopener\">Maklumat Pengaduan Pelayanan Publik</a></li>\n</ul>\n<h3>Daftar Informasi Publik</h3>\n<p>Daftar Informasi Publik (DIP) Diskominfo dikelola oleh Sekretariat, tersedia dalam bentuk online di Kantor Diskominfo Lt. 1, KP3B, dengan ringkasan sebagai berikut:</p>\n<table>\n    <thead>\n        <tr><th>No</th><th>Ringkasan Informasi</th><th>Jangka Waktu Retensi</th></tr>\n    </thead>\n    <tbody>\n        <tr><td>1</td><td>Profil badan publik</td><td>5 tahun</td></tr>\n        <tr><td>2</td><td>Ringkasan laporan keuangan</td><td>10 tahun</td></tr>\n        <tr><td>3</td><td>Ringkasan program dan kegiatan</td><td>1 tahun</td></tr>\n        <tr><td>4</td><td>Peraturan dan kebijakan terkait badan publik Diskominfo</td><td>5 tahun</td></tr>\n        <tr><td>5</td><td>Informasi jumlah SDM ASN dan Non ASN badan publik</td><td>5 tahun</td></tr>\n        <tr><td>6</td><td>Informasi peraturan organisasi badan publik</td><td>5 tahun</td></tr>\n        <tr><td>7</td><td>Dokumen LKIP</td><td>5 tahun</td></tr>\n        <tr><td>8</td><td>Dokumen rencana kerja badan publik</td><td>1 tahun</td></tr>\n        <tr><td>9</td><td>Dokumen rencana strategis badan publik</td><td>5 tahun</td></tr>\n        <tr><td>10</td><td>Profil pegawai Diskominfo</td><td>5 tahun</td></tr>\n        <tr><td>11</td><td>Sejarah badan publik</td><td>2 tahun</td></tr>\n        <tr><td>12</td><td>Tugas dan fungsi badan publik</td><td>5 tahun</td></tr>\n        <tr><td>13</td><td>Informasi kepegawaian badan publik</td><td>2 tahun</td></tr>\n        <tr><td>14</td><td>Ringkasan RKA dan DPA badan publik</td><td>1 tahun</td></tr>\n        <tr><td>15</td><td>Informasi terkait data statistik</td><td>1 tahun</td></tr>\n        <tr><td>16</td><td>LHKPN pejabat struktural</td><td>1 tahun</td></tr>\n        <tr><td>17</td><td>Surat keputusan kegiatan di Diskominfo</td><td>2 tahun</td></tr>\n        <tr><td>18</td><td>Informasi kanal-kanal resmi informasi di Provinsi Banten</td><td>2 tahun</td></tr>\n        <tr><td>19</td><td>SAKIP</td><td>1 tahun</td></tr>\n        <tr><td>20</td><td>Informasi layanan pembuatan tanda tangan elektronik</td><td>1 tahun</td></tr>\n    </tbody>\n</table>\n<ul>\n    <li><a href=\"https://drive.google.com/file/d/1a9GmBbEsp9qUG4bKNcZUuSpsNoLfBGuU/view?usp=drive_link\" target=\"_blank\" rel=\"noopener\">SK Daftar Informasi Publik (DIP) 2025</a></li>\n    <li><a href=\"https://dmsppid.bantenprov.go.id/upload/dms/20/sk-dip.pdf\" target=\"_blank\" rel=\"noopener\">SK Daftar Informasi Publik (DIP) 2024</a></li>\n</ul>\n<h3>Kelembagaan dan Alur PPID</h3>\n<ul>\n    <li><a href=\"https://dmsppid.bantenprov.go.id/upload/dms/20/struktur-tim-kerja-ppid-pembantu-dinas-komunikasi-informatika-statistik-dan-persandaian-rev.png\" target=\"_blank\" rel=\"noopener\">Visi Misi PPID Pelaksana Diskominfo</a></li>\n    <li><a href=\"https://dmsppid.bantenprov.go.id/upload/dms/20/22_2.png\" target=\"_blank\" rel=\"noopener\">Struktur Tim PPID Pelaksana Diskominfo</a></li>\n    <li><a href=\"https://dmsppid.bantenprov.go.id/upload/dms/47/info-grapis-ppid.jpeg\" target=\"_blank\" rel=\"noopener\">Infografis Alur Informasi Publik</a></li>\n    <li><a href=\"https://dmsppid.bantenprov.go.id/upload/dms/53/2021-form-permintaan-inf-publik.pdf\" target=\"_blank\" rel=\"noopener\">Form Permohonan Informasi Publik Diskominfo</a></li>\n    <li><a href=\"https://dmsppid.bantenprov.go.id/upload/dms/20/tanda-terima-ppid.pdf\" target=\"_blank\" rel=\"noopener\">Tanda Terima Permohonan Informasi Publik Diskominfo</a></li>\n</ul>\n<h3>Regulasi Terkait Keterbukaan Informasi</h3>\n<ul>\n    <li><a href=\"https://dmsppid.bantenprov.go.id/upload/dms/65/uu-nomor-14-tahun-2008-uu-nomor-14-tahun-2008.pdf\" target=\"_blank\" rel=\"noopener\">UU Nomor 14 Tahun 2008 tentang Keterbukaan Informasi Publik</a></li>\n    <li><a href=\"https://dmsppid.bantenprov.go.id/upload/dms/66/permendagri-3-2017-ttg-ppid.pdf\" target=\"_blank\" rel=\"noopener\">Permendagri Nomor 3 Tahun 2017 tentang PPID</a></li>\n    <li><a href=\"https://dmsppid.bantenprov.go.id/upload/dms/67/perpres-2018-95-sistem-pemerintahan-berbasis-elektronik-spbe.pdf\" target=\"_blank\" rel=\"noopener\">Perpres Nomor 95 Tahun 2018 tentang Sistem Pemerintahan Berbasis Elektronik</a></li>\n    <li><a href=\"https://dmsppid.bantenprov.go.id/upload/dms/69/24perda-no-6-tahun-2018-1.pdf\" target=\"_blank\" rel=\"noopener\">Perda Nomor 6 Tahun 2018 tentang Penyelenggaraan Komunikasi dan Informatika</a></li>\n    <li><a href=\"https://dmsppid.bantenprov.go.id/upload/dms/65/1482381374-uu_19_tahun_2016_publish.pdf\" target=\"_blank\" rel=\"noopener\">UU Nomor 19 Tahun 2016 tentang Informasi dan Transaksi Elektronik</a></li>\n    <li><a href=\"https://drive.google.com/file/d/10dHfbrOuqpqh5Rbd7vnWReJts79HiH4t/view?usp=sharing\" target=\"_blank\" rel=\"noopener\">UU Nomor 27 Tahun 2022 tentang Perlindungan Data Pribadi</a></li>\n</ul>\n<p>Permohonan informasi dapat disampaikan melalui aplikasi <a href=\"https://www.lapor.go.id/\" target=\"_blank\" rel=\"noopener\">SP4N LAPOR! (lapor.go.id)</a>. Daftar lengkap dokumen tersedia pada laman Informasi Setiap Saat di website resmi Diskominfo Provinsi Banten.</p>','https://diskominfo.bantenprov.go.id/informasi-setiap-saat','published',NULL,'2026-08-17 19:09:39'),(64,'Informasi Berkala','informasi-berkala','<p>Berikut merupakan informasi yang wajib diumumkan secara berkala oleh PPID Pelaksana Dinas Komunikasi, Informatika, Statistik dan Persandian Provinsi Banten sesuai Undang-Undang Nomor 14 Tahun 2008 tentang Keterbukaan Informasi Publik.</p>\n<h3>Kategori Informasi Berkala</h3>\n<ol>\n    <li><strong>Profil badan publik</strong> &mdash; tugas, visi dan misi, profil pejabat, profil pegawai, kepegawaian, sejarah, data statistik, dan dokumen terkait;</li>\n    <li><strong>Laporan Harta Kekayaan Pejabat Negara (LHKPN)</strong>;</li>\n    <li><strong>Renja dan Renstra</strong> &mdash; dokumen perencanaan tahunan dan strategis;</li>\n    <li><strong>RKA dan DPA</strong> &mdash; dokumen anggaran;</li>\n    <li><strong>Peraturan, keputusan, dan kebijakan</strong>;</li>\n    <li><strong>Ringkasan laporan keuangan</strong> &mdash; CALK, neraca saldo, LRA, dan data aset;</li>\n    <li><strong>Ringkasan laporan akses informasi publik</strong>;</li>\n    <li><strong>LKIP</strong> &mdash; Laporan Kinerja Instansi Pemerintah;</li>\n    <li><strong>Tata cara pengaduan</strong> (SP4N LAPOR!);</li>\n    <li><strong>Layanan permohonan dan keberatan informasi publik</strong>;</li>\n    <li><strong>Informasi pelanggaran internal dan gratifikasi</strong>;</li>\n    <li><strong>Daftar aplikasi, agenda, dan informasi lainnya</strong>.</li>\n</ol>\n<h3>Alamat PPID Pelaksana</h3>\n<p>Jl. Syech Nawawi Al-Bantani, KP3B (Kawasan Pusat Pemerintah Provinsi Banten), Area Gedung Baru OPD Provinsi Banten, Kecamatan Curug, Kota Serang, Provinsi Banten.</p>\n<ul>\n    \n    <li>Telepon: 08129382693.</li>\n</ul>\n<p>Dokumen lengkap informasi berkala dapat diakses pada laman Informasi Berkala di website resmi Diskominfo Provinsi Banten: <a href=\"https://diskominfo.bantenprov.go.id/informasi-berkala\" target=\"_blank\" rel=\"noopener\">https://diskominfo.bantenprov.go.id/informasi-berkala</a>.</p>','https://diskominfo.bantenprov.go.id/informasi-berkala','published','2026-08-23 13:18:57','2026-08-20 09:50:44');
/*!40000 ALTER TABLE `pages` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `photos`
--

DROP TABLE IF EXISTS `photos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `photos` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `album_id` int(10) unsigned NOT NULL,
  `image` varchar(255) NOT NULL,
  `media_type` enum('image','video') NOT NULL DEFAULT 'image',
  `thumbnail` varchar(255) DEFAULT NULL,
  `caption` varchar(255) DEFAULT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_photos_album` (`album_id`),
  CONSTRAINT `fk_photos_album` FOREIGN KEY (`album_id`) REFERENCES `albums` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=90 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `photos`
--

LOCK TABLES `photos` WRITE;
/*!40000 ALTER TABLE `photos` DISABLE KEYS */;
INSERT INTO `photos` VALUES (50,52,'beda86548a2c88ce6bb65678.jpg','image','beda86548a2c88ce6bb65678-thumb.jpg','Dokumentasi kegiatan ke-1',1,'2026-08-14 15:25:11'),(51,52,'9482796ab0f768d530ffd004.jpg','image','9482796ab0f768d530ffd004-thumb.jpg','Dokumentasi kegiatan ke-2',2,'2026-08-14 15:25:12'),(52,52,'f1614264e39d425598bdcdd0.jpg','image','f1614264e39d425598bdcdd0-thumb.jpg','Dokumentasi kegiatan ke-3',3,'2026-08-14 15:25:12'),(53,52,'8f646d46825aa3462e301704.jpg','image','8f646d46825aa3462e301704-thumb.jpg','Dokumentasi kegiatan ke-4',4,'2026-08-14 15:25:12'),(54,53,'ecc302bbe346fca8fb515e42.jpg','image','ecc302bbe346fca8fb515e42-thumb.jpg','Dokumentasi kegiatan ke-1',1,'2026-08-14 15:25:12'),(55,53,'1a57da6baf9c88b0c0a51bb1.jpg','image','1a57da6baf9c88b0c0a51bb1-thumb.jpg','Dokumentasi kegiatan ke-2',2,'2026-08-14 15:25:13'),(56,53,'6bea8f43d877880b82f50d16.jpg','image','6bea8f43d877880b82f50d16-thumb.jpg','Dokumentasi kegiatan ke-3',3,'2026-08-14 15:25:13'),(57,53,'6e8f7ab2526734fd3792147b.jpg','image','6e8f7ab2526734fd3792147b-thumb.jpg','Dokumentasi kegiatan ke-4',4,'2026-08-14 15:25:13'),(58,53,'cc96bbcf614846f243da8c64.jpg','image','cc96bbcf614846f243da8c64-thumb.jpg','Dokumentasi kegiatan ke-5',5,'2026-08-14 15:25:13'),(59,54,'5fe6a7045c39d940f4b56eeb.jpg','image','5fe6a7045c39d940f4b56eeb-thumb.jpg','Dokumentasi kegiatan ke-1',1,'2026-08-14 15:25:13'),(60,54,'20d1849fa172765f4bae3f1a.jpg','image','20d1849fa172765f4bae3f1a-thumb.jpg','Dokumentasi kegiatan ke-2',2,'2026-08-14 15:25:14'),(61,54,'f38508a02e9a4cff98389dae.jpg','image','f38508a02e9a4cff98389dae-thumb.jpg','Dokumentasi kegiatan ke-3',3,'2026-08-14 15:25:14'),(62,54,'3b927b009cfad89048cc1154.jpg','image','3b927b009cfad89048cc1154-thumb.jpg','Dokumentasi kegiatan ke-4',4,'2026-08-14 15:25:14'),(63,54,'fa7f0bca97bc400595bdca2d.jpg','image','fa7f0bca97bc400595bdca2d-thumb.jpg','Dokumentasi kegiatan ke-5',5,'2026-08-14 15:25:14'),(64,54,'4f7dbb5f21b904aafcad0208.jpg','image','4f7dbb5f21b904aafcad0208-thumb.jpg','Dokumentasi kegiatan ke-6',6,'2026-08-14 15:25:14');
/*!40000 ALTER TABLE `photos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `settings`
--

DROP TABLE IF EXISTS `settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `settings` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `key` varchar(100) NOT NULL,
  `value` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_settings_key` (`key`)
) ENGINE=InnoDB AUTO_INCREMENT=27 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `settings`
--

LOCK TABLES `settings` WRITE;
/*!40000 ALTER TABLE `settings` DISABLE KEYS */;
INSERT INTO `settings` VALUES (1,'site_tagline','Portal Informasi PKL & Magang'),(2,'hero_title','Portal PKL & Magang'),(3,'hero_subtitle','Diskominfo Provinsi Banten'),(4,'hero_description','Pusat informasi, layanan, berita, dan dokumentasi kegiatan Praktik Kerja Lapangan (PKL) dan Magang di lingkungan Diskominfo Provinsi Banten.'),(5,'hero_btn_primary','Informasi PKL'),(6,'hero_btn_primary_link','?page=layanan'),(7,'hero_btn_secondary','Informasi Magang'),(8,'hero_btn_secondary_link','?page=layanan'),(9,'about_title','Tentang Program'),(10,'about_content','Dinas Komunikasi, Informatika, Statistik dan Persandian (Diskominfo) Provinsi Banten dibentuk berdasarkan Peraturan Daerah Nomor 8 Tahun 2016 dan berperan sebagai leading sector penyelenggaraan pemerintahan berbasis elektronik (e-Government) di lingkungan Pemerintah Provinsi Banten. Portal ini menyediakan informasi resmi mengenai program Praktik Kerja Lapangan (PKL) dan Magang di lingkungan Diskominfo.'),(11,'about_btn','Pelajari Lebih Lanjut'),(12,'about_btn_link','?page=profil'),(13,'cta_title','Siap Memulai PKL atau Magang?'),(14,'cta_text','Pelajari persyaratan, alur pendaftaran, dan dokumen yang dibutuhkan sebelum mengajukan permohonan.'),(15,'cta_btn','Hubungi Kami'),(16,'cta_btn_link','?page=kontak'),(17,'contact_address','Jl. Syech Nawawi Al-Bantani KP3B, Area Gedung Baru OPD Pemprov Banten'),(18,'contact_email','pkl.diskominfo.banten@gmail.com'),(19,'contact_phone','08129382693'),(20,'contact_map_link','https://www.google.com/maps/search/?api=1&query=Jl.+Syech+Nawawi+Al-Bantani+KP3B%2C+Area+Gedung+Baru+OPD+Pemprov+Banten%2C+Kota+Serang%2C+Banten'),(21,'social_facebook','https://www.facebook.com/ppiddiskominfobanten'),(22,'social_instagram','https://www.instagram.com/diskominfospprovbanten'),(23,'social_twitter','#'),(24,'social_youtube','https://www.youtube.com/channel/UCtCKKGCncMz0BoLEkNYfRLQ'),(25,'footer_about','Portal informasi dan layanan Praktik Kerja Lapangan (PKL) serta Magang di lingkungan Dinas Komunikasi, Informatika, Statistik dan Persandian Provinsi Banten. Seluruh konten informatif bersumber dari website resmi Diskominfo Provinsi Banten dan dapat diperbarui oleh tim pengelola.'),(26,'privacy_note','Data yang diberikan pengguna melalui website digunakan untuk mendukung proses administrasi dan pelayanan PKL dan magang. Data tersebut dikelola sesuai dengan kebutuhan layanan dan administrasi yang berlaku. Jangan menampilkan atau menggunakan data pribadi peserta untuk kepentingan lain tanpa dasar dan izin yang sesuai.');
/*!40000 ALTER TABLE `settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','editor') NOT NULL DEFAULT 'editor',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `remember_token` varchar(64) DEFAULT NULL,
  `last_login_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_users_username` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'Admin Portal PKL & Magang','disKominfo','pkl.diskominfo.banten@gmail.com','$2y$10$/XuIgR0A0lRF9r9WN00Ga.QRtQWdZIAPQugWMLHR8vIsXuj/Js8aO','admin',1,NULL,'2026-08-23 14:00:06','2026-08-11 13:58:00','2026-08-23 14:00:06');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `visitors`
--

DROP TABLE IF EXISTS `visitors`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `visitors` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `ip` varchar(45) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `page` varchar(120) DEFAULT NULL,
  `visited_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_visitors_visited` (`visited_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `visitors`
--

LOCK TABLES `visitors` WRITE;
/*!40000 ALTER TABLE `visitors` DISABLE KEYS */;
/*!40000 ALTER TABLE `visitors` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-08-23 14:40:38
