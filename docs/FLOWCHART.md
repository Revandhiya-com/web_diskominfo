# Spesifikasi Alur Kerja & Diagram Flowchart Portal PKL & Magang Diskominfo Banten

Dokumen ini berisi spesifikasi sistem, logika bisnis, dan diagram flowchart lengkap (berbasis Mermaid) untuk **Portal PKL & Magang Diskominfo Provinsi Banten**. Dokumen ini dirancang khusus agar mudah dipahami oleh pengembang maupun dapat diolah kembali oleh AI lain (seperti Napkin AI, ChatGPT, Claude, Draw.io, atau Mermaid Editor) untuk menghasilkan visualisasi yang lebih artistik.

---

## 📌 Daftar Isi Flowchart
1. [Arsitektur Umum Sistem (System Architecture Overview)](#1-arsitektur-umum-sistem)
2. [Flowchart 1: Alur Navigasi & Interaksi Pengunjung Publik (Public Flow)](#2-flowchart-1-alur-navigasi--interaksi-pengunjung-publik)
3. [Flowchart 2: Alur Autentikasi & Keamanan Login Admin (Auth & Security Flow)](#3-flowchart-2-alur-autentikasi--keamanan-login-admin)
4. [Flowchart 3: Alur Pengelolaan Konten & CMS Admin (CRUD Management Flow)](#4-flowchart-3-alur-pengelolaan-konten--cms-admin)
5. [Flowchart 4: Logika Khusus Modul Karya Peserta & Integrasi Pustaka Video](#5-flowchart-4-logika-khusus-modul-karya-peserta--integrasi-pustaka-video)
6. [Flowchart 5: Alur Validasi & Upload File Media (Upload Security Flow)](#6-flowchart-5-alur-validasi--upload-file-media)

---

## 1. Arsitektur Umum Sistem

Sistem berbasis **PHP 8 Native (Tanpa Framework)** dengan arsitektur **Front Controller** (`index.php`) dan **Database MySQL/MariaDB**.

```mermaid
graph TD
    User["👥 Pengunjung / Publik"] -->|HTTP/HTTPS Request| FrontController["🌐 index.php (Front Controller)"]
    AdminUser["🔐 Pengelola / Admin"] -->|Akses /admin/| AdminBoot["🛡️ admin/includes/bootstrap.php"]
    
    subgraph CoreEngine ["PHP Core Engine & Helpers"]
        FrontController --> Router["Routing Logic (?page=...)"]
        Router --> SecurityHelper["security.php (CSRF, XSS, Session)"]
        Router --> AuthHelper["auth.php (Session & Role Check)"]
        Router --> DBHelper["functions.php (PDO Wrapper & Sanitasi)"]
    end
    
    subgraph DataStore ["Penyimpanan Data"]
        DBHelper -->|Prepared Query| MySQL[("🗄️ Database MySQL (diskominfo_pkl)")]
        AdminBoot -->|Simpan Asset| MediaStorage["📁 File Storage (/uploads)"]
    end

    subgraph Views ["Rendering Halaman"]
        Router -->|Render| PublicPages["📄 Pages (home, berita, karya, dll)"]
        AdminBoot -->|Render Layout| AdminViews["📊 Admin Panels (CRUD Views)"]
    end
```

---

## 2. Flowchart 1: Alur Navigasi & Interaksi Pengunjung Publik

Alur perjalanan pengguna umum saat mengakses portal publik dari beranda hingga pengiriman pesan kontak atau pencarian informasi.

```mermaid
flowchart TD
    Start(["🚀 Mulai: Pengunjung Membuka Situs"]) --> AccessHome["Buka URL Utama (index.php)"]
    AccessHome --> InitApp["Inisialisasi Session, Config & Database"]
    InitApp --> ShowHome["Tampilkan Beranda (13 Seksi Dinamis)"]
    
    ShowHome --> UserChoice{"Pilihan Tindakan Pengunjung?"}
    
    %% Cabang 1: Navigasi Halaman Statis / Berita / Informasi
    UserChoice -->|Klik Menu Profil/Layanan/FAQ| StaticNav["Pilih Halaman (?page=nama)"]
    StaticNav --> CheckPageExist{"Halaman .php Ada?"}
    CheckPageExist -->|Ya| LoadPageFile["Muat File pages/nama.php"]
    CheckPageExist -->|Tidak| CheckCMSDB{"Cek Slug di Tabel 'pages'"}
    CheckCMSDB -->|Ditemukan| LoadCMSPage["Muat Dynamic Page (pages/cms.php)"]
    CheckCMSDB -->|Tidak Ditemukan| Load404["Tampilkan Halaman Error 404"]

    %% Cabang 2: Fitur Pencarian
    UserChoice -->|Input Kata Kunci Pencarian| SearchInput["Submit Form Cari (?page=search&q=...)"]
    SearchInput --> SanitizeKeyword["Sanitasi String Pencarian"]
    SanitizeKeyword --> QuerySearch["Cari di Berita, Pengumuman, Agenda, Karya, Dokumen"]
    QuerySearch --> ShowSearchResults["Tampilkan Hasil Pencarian Dinamis"]

    %% Cabang 3: Fitur Kontak / Kirim Pesan
    UserChoice -->|Buka Halaman Kontak| OpenContactForm["Buka ?page=kontak"]
    OpenContactForm --> FillForm["Isi Nama, Email, Subjek, Pesan"]
    FillForm --> SubmitContact["Klik 'Kirim Pesan' (POST)"]
    SubmitContact --> VerifyCSRF{"Validasi Token CSRF?"}
    VerifyCSRF -->|Gagal| ErrCSRF["Tampilkan Error 403 Session Invalid"]
    VerifyCSRF -->|Berhasil| CheckRateLimit{"Cek Rate Limit (Maks Pesan/Waktu)"}
    CheckRateLimit -->|Exceeded| ErrRate["Tampilkan Pesan Batas Waktu Terlampaui"]
    CheckRateLimit -->|Aman| SaveMsgDB["Simpan ke Tabel 'contact_messages'"]
    SaveMsgDB --> SuccessAlert["Tampilkan Notifikasi Sukses ke Pengunjung"]

    %% Cabang 4: Unduh Dokumen
    UserChoice -->|Buka Menu Dokumen| OpenDocs["Buka ?page=dokumen"]
    OpenDocs --> FilterDocCategory["Pilih Kategori Dokumen"]
    FilterDocCategory --> ClickDownload["Klik Tombol Unduh File"]
    ClickDownload --> ServeFile["Stream File PDF/DOCX/ZIP dari /uploads/documents/"]

    LoadPageFile --> EndPublic(["Selesai / Pengunjung Melanjutkan Browsing"])
    LoadCMSPage --> EndPublic
    Load404 --> EndPublic
    ShowSearchResults --> EndPublic
    SuccessAlert --> EndPublic
    ServeFile --> EndPublic
```

---

## 3. Flowchart 2: Alur Autentikasi & Keamanan Login Admin

Alur autentikasi berlapis untuk pengelola/admin mencakup proteksi CSRF, pencatatan batas percobaan login (Rate Limiting), verifikasi hash BCRYPT, sesi aman, serta fungsi *Remember Me*.

```mermaid
flowchart TD
    StartAdmin(["🔐 Pengguna Buka /admin/login.php"]) --> CheckLogState{"Sudah Login Sebelumnya?"}
    
    CheckLogState -->|Ya| RedirectDash["Redirect Langsung ke /admin/index.php"]
    CheckLogState -->|Tidak| CheckRememberCookie{"Ada Cookie 'dp_remember'?"}
    
    CheckRememberCookie -->|Ada & Valid| AutoLogin["Verifikasi Token DB -> Auto Login"]
    AutoLogin --> RedirectDash
    CheckRememberCookie -->|Tidak/Expired| RenderLoginForm["Tampilkan Form Login Admin"]
    
    RenderLoginForm --> UserSubmittion["Input Username/Email & Password -> Submit"]
    UserSubmittion --> CheckCSRF{"Token CSRF Valid?"}
    
    CheckCSRF -->|Salah/Kosong| Deny403["Tolak Akses (HTTP 403 Forbidden)"]
    CheckCSRF -->|Valid| CheckLockout{"Jumlah Gagal Login IP/User >= 5x?"}
    
    CheckLockout -->|Ya| ShowLockoutMsg["Blokir Sementara (Tunggu 15 Menit)"]
    CheckLockout -->|Tidak| FetchUserDB["Cari User di DB (is_active = 1)"]
    
    FetchUserDB --> VerifyBCRYPT{"User Ada & Password Match (password_verify)?"}
    
    VerifyBCRYPT -->|Gagal| LogFailedAttempt["Catat ke Tabel 'login_attempts'"]
    LogFailedAttempt --> ShowErrCreds["Tampilkan Error 'Username/Password Salah'"]
    ShowErrCreds --> RenderLoginForm
    
    VerifyBCRYPT -->|Berhasil| RegenerateSess["Regenerate Session ID (Cegah Fixation)"]
    RegenerateSess --> ClearAttempts["Hapus Record 'login_attempts' Terkait"]
    ClearAttempts --> SetUserSession["Set Session user_id & role"]
    
    SetUserSession --> CheckRememberOption{"Opsi 'Ingat Saya' Dicentang?"}
    CheckRememberOption -->|Ya| GenRememberCookie["Buat Token Acak & Set Cookie 'dp_remember'"]
    CheckRememberOption -->|Tidak| ClearRememberToken["Kosongkan Token Remember di DB"]
    
    GenRememberCookie --> LogActivity["Catat Log Aktivitas ('login berhasil')"]
    ClearRememberToken --> LogActivity
    LogActivity --> RedirectDash
    
    RedirectDash --> EndAuth(["Selesai: Masuk ke Dashboard Admin"])
```

---

## 4. Flowchart 3: Alur Pengelolaan Konten & CMS Admin

Alur umum manajemen konten (Create, Read, Update, Delete) oleh Admin/Editor di dalam panel kontrol.

```mermaid
flowchart TD
    AdminStart(["📊 Admin di Dashboard Dashboard/Menu Admin"]) --> SelectModule{"Pilih Modul CMS"}
    
    SelectModule -->|Berita / Pengumuman| NewsMod["Modul Berita & Pengumuman"]
    SelectModule -->|Agenda / FAQ / Halaman| MiscMod["Modul Agenda, FAQ, Halaman Statis"]
    SelectModule -->|Dokumentasi Galeri| AlbumMod["Modul Album & Foto"]
    SelectModule -->|Pengaturan Situs| SettingsMod["Modul Settings (Kontak, Hero, Sosmed)"]
    SelectModule -->|Pengguna System| UserMod["Modul User (Hanya Role Admin)"]
    
    %% Alur Berita/Pengumuman
    NewsMod --> FormNews["Buka Form Tambah/Edit"]
    FormNews --> InputNewsData["Isi Judul, Slug, Kategori, Status (Draft/Published)"]
    InputNewsData --> UploadNewsCover{"Ada Upload Gambar Cover?"}
    UploadNewsCover -->|Ya| ProcessCover["Validasi & Resize Gambar Maks 1920px + Thumbnail"]
    UploadNewsCover -->|Tidak| SkipCover["Gunakan Gambar Lama / Placeholder"]
    ProcessCover --> SaveNewsDB["Simpan Record ke DB via Prepared Statement"]
    SkipCover --> SaveNewsDB
    
    %% Alur Galeri Album
    AlbumMod --> FormAlbum["Buat Album Foto/Video"]
    FormAlbum --> MultipleUpload["Upload Banyak Foto Sekaligus"]
    MultipleUpload --> LoopPhotos["Proses Iterasi Setiap File Foto"]
    LoopPhotos --> SavePhotoDB["Generate Nama Unik, Resize, Simpan ke uploads/albums/"]
    
    %% Alur Pengaturan Situs
    SettingsMod --> FormSettings["Ubah Nilai Key-Value (Kontak, Sosmed, Hero)"]
    FormSettings --> SaveSettingsDB["Update Tabel 'settings' Batch"]
    
    %% Selesai CRUD
    SaveNewsDB --> FlashMessage["Set Flash Message ('Data Berhasil Disimpan')"]
    SavePhotoDB --> FlashMessage
    SaveSettingsDB --> FlashMessage
    
    FlashMessage --> RedirectList["Redirect ke Halaman Tabel Modul"]
    RedirectList --> EndCRUD(["Selesai Manajemen Konten"])
```

---

## 5. Flowchart 4: Logika Khusus Modul Karya Peserta & Integrasi Pustaka Video

Modul Karya Peserta memiliki bisnis logika unik: menyatukan media Infografis/Foto/Video, mengintegrasikan Pustaka Video tunggal tanpa duplikasi file, serta fitur sinkronisasi **Konten Edukasi** ke halaman publik.

```mermaid
flowchart TD
    StartKarya(["🎨 Admin Buka Modul Karya Peserta"]) --> ChoiceKaryaAction{"Pilih Tindakan"}
    
    ChoiceKaryaAction -->|Tambah/Edit Karya| FillKaryaForm["Isi Judul, Tim (1-3), Minggu ke-N, Deskripsi"]
    FillKaryaForm --> SelectMediaType{"Pilih Jenis Media Karya"}
    
    %% Jenis Media: Infografis / Foto
    SelectMediaType -->|Infografis / Foto| UploadImgKarya["Upload File Gambar ke uploads/karya/"]
    UploadImgKarya --> SaveKaryaImgDB["Simpan Record Karya (media_type = image)"]
    
    %% Jenis Media: Video
    SelectMediaType -->|Video| VideoSourceChoice{"Pilih Sumber Video"}
    VideoSourceChoice -->|Pilih dari Pustaka| SelectFromLib["Pilih ID Video dari Tabel 'videos'"]
    VideoSourceChoice -->|Upload Video Baru| UploadNewVid["Upload File MP4/WebM ke uploads/videos/"]
    UploadNewVid --> CreateVideoLibRecord["Buat Record Baru di Tabel 'videos' (source='karya')"]
    
    SelectFromLib --> LinkVideoID["Hubungkan video_id ke Record Karya"]
    CreateVideoLibRecord --> LinkVideoID
    LinkVideoID --> SaveKaryaVidDB["Simpan Record Karya (media_type = video)"]
    
    %% Logic Konten Edukasi Flag
    SaveKaryaImgDB --> EduFlagCheck{"Centang 'Tampilkan di Konten Edukasi'?"}
    SaveKaryaVidDB --> EduFlagCheck
    
    EduFlagCheck -->|Ya & Status Published| SetEduSync["Set Record Video di 'videos' -> displays in ?page=video"]
    EduFlagCheck -->|Tidak / Draft| RemoveEduSync["Sembunyikan dari Halaman Konten Edukasi"]
    
    SetEduSync --> EndKaryaFlow(["Selesai: Karya Terbit & Tersinkronisasi"])
    RemoveEduSync --> EndKaryaFlow
```

---

## 6. Flowchart 5: Alur Validasi & Upload File Media

Logika keamanan komprehensif untuk menangani file upload guna mencegah peretasan (seperti upload script RCE/PHP).

```mermaid
flowchart TD
    UploadStart(["📁 Proses Upload File Diinisialisasi"]) --> CheckFileSelected{"File Dipilih & Tidak Error Code?"}
    
    CheckFileSelected -->|Kosong / Error| ReturnErrNoFile["Return Error: File Tidak Valid"]
    CheckFileSelected -->|Ada File| CheckSize{"Cek Ukuran File vs Limit (5MB Image / 100MB Video)"}
    
    CheckSize -->|Melebihi Limit| ReturnErrSize["Return Error: Ukuran File Terlalu Besar"]
    CheckSize -->|Aman| DetectMIME["Deteksi MIME Real via finfo_file() (Bukan Ekstensi)"]
    
    DetectMIME --> CheckMIMEAllowed{"MIME Type Diizinkan? (image/jpeg, video/mp4, pdf, dll)"}
    
    CheckMIMEAllowed -->|Ditolak/Script| ReturnErrType["Return Error: Format File Dilarang!"]
    CheckMIMEAllowed -->|Diterima| CheckExt{"Double Check Ekstensi File blacklist (.php, .exe, dll)"}
    
    CheckExt -->|Berbahaya| ReturnErrType
    CheckExt -->|Aman| GenerateUniqueName["Generate Nama File Unik (hash random string)"]
    
    GenerateUniqueName --> CheckIsImage{"Apakah Berkas Bertipe Gambar?"}
    
    CheckIsImage -->|Ya| AutoResize["Resize Gambar Maksimum 1920px & Buat Crop Thumbnail"]
    CheckIsImage -->|Tidak (Video/Doc)| SkipResize["Gunakan File Asli"]
    
    AutoResize --> MoveToFolder["Pindahkan File ke Target Folder (/uploads/...)"]
    SkipResize --> MoveToFolder
    
    MoveToFolder --> VerifyHtaccess["Proteksi Direktori Upload via .htaccess (Disable Script Execution)"]
    VerifyHtaccess --> ReturnSuccess["Return Path File Berhasil Disimpan"]
    
    ReturnErrNoFile --> EndUpload(["Proses Selesai (Gagal/Ganti Input)"])
    ReturnErrSize --> EndUpload
    ReturnErrType --> EndUpload
    ReturnSuccess --> EndUpload(["Proses Selesai (File Siap Digunakan)"])
```

---

## 📝 Ringkasan Ketentuan Logika Sistem

1. **Routing & URL Structure**:
   - Front-end Publik menggunakan `index.php?page={slug}`.
   - Admin Panel menggunakan berkas eksplisit seperti `/admin/{modul}.php` (misal `/admin/index.php` untuk Dashboard, `/admin/news.php` untuk Berita).

2. **Keamanan (Security Matrix)**:
   - **XSS & Injection**: Menggunakan PDO Prepared Statement untuk seluruh query database + fungsi sanitasi XSS `e()` saat render HTML.
   - **CSRF Protection**: Setiap form mutlak dilengkapi token hidden CSRF yang diverifikasi oleh `require_csrf()`.
   - **File Upload Security**: Pembatasan ekstensi, pemeriksaan MIME asli melalui `finfo_file()`, perusakan eksekusi skrip PHP di folder `/uploads/` menggunakan `.htaccess` (`SetHandler none`).
   - **Rate Limiting**: Pembatasan percobaan login (maksimal 5 kali gagal per 15 menit) dan pengiriman pesan kontak.

---
*Dokumen ini dibuat otomatis sebagai acuan teknis dan alur diagram untuk Portal PKL & Magang Diskominfo Banten.*
