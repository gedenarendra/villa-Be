# Villa Backend Context

Dokumen ini berisi rangkuman lengkap mengenai arsitektur, konfigurasi, dan fungsionalitas dari proyek `villa-backend` saat ini.

## 1. Stack Teknologi & Infrastruktur
- **Framework Utama:** Laravel v11.x (PHP ^8.3)
- **Database:** PostgreSQL (Lokal via Docker)
- **Web Server:** Nginx
- **Authentication:** Laravel Sanctum (Token-based Auth)
- **Containerization:** Docker & Docker Compose
  - `app` (PHP-FPM)
  - `db` (PostgreSQL Alpine, Port Internal: 5432, Host Port: 5433)
  - `webserver` (Nginx Alpine, Port: 8000)

## 2. Struktur Database & Model
Sistem memiliki beberapa entitas utama dengan relasinya:

*   **Admin (`admins`)**: Pengguna dengan hak akses manajemen (CRUD Villa).
*   **User (`users`)**: Entitas pengguna umum (tamu).
*   **Villa (`villas`)**: Menyimpan data villa.
    *   Kolom: `name`, `description`, `price_per_year`, `max_guests`, `status (enum: available, not available)`
*   **VillaImage (`villa_images`)**: Foto-foto untuk setiap villa.
    *   Relasi: `belongsTo(Villa)` (Setiap villa bisa memiliki banyak foto / *hasMany*).
    *   Kolom khusus: `image_url`, `is_primary`.
*   **Booking (`bookings`)**: Transaksi pemesanan villa.
    *   Relasi: Terhubung dengan `Villa`.

> [!NOTE]
> **Fitur Database Tambahan:**
> - Telah diaktifkan **Row Level Security (RLS)** pada seluruh tabel menggunakan migrasi `enable_rls_on_all_tables`.
> - Menggunakan driver database untuk tabel `cache` (`cache`, `cache_locks`) dan Antrean (`jobs`, `failed_jobs`, `job_batches`).

## 3. Daftar Endpoint API (`routes/api.php`)

### Area Publik (Tidak Butuh Token)
*   `GET /api/ping`: Health check endpoint untuk memastikan server aktif.
*   `GET /api/villas`: Menampilkan daftar semua villa (Katalog). **(Menggunakan Cache 60 Menit)**.
*   `GET /api/villas/{id}`: Menampilkan detail satu villa beserta relasi `images`.
*   `POST /api/login`: Endpoint untuk proses autentikasi admin dan mendapatkan token.

### Area Terlindungi (Membutuhkan Token Bearer Sanctum)
*   `POST /api/logout`: Menghapus token (Logout Admin).
*   `GET /api/admin/profile`: Melihat data profil admin yang sedang login.
*   `POST /api/villas`: Menambahkan data villa baru sekaligus menyimpan URL gambar utamanya.
*   `PUT /api/villas/{id}`: Mengubah data villa yang sudah ada.
*   `DELETE /api/villas/{id}`: Menghapus data villa (otomatis menghapus `villa_images` secara *cascade*).

## 4. Konfigurasi Sistem (`.env`)
*   **Database:** Terhubung ke service Docker `db` dengan host `db` (menggunakan user `admin` dan database `villa_db`).
*   **Cache & Session:** Keduanya saat ini menggunakan driver `database`. Sebelumnya sempat menggunakan Redis, namun distandarisasi ke database.
*   **Queue:** Menggunakan koneksi `database` untuk pemrosesan antrean latar belakang.
*   **Logging:** Menggunakan stack *single* level debug.

## 5. Logika Bisnis & Performa (`VillaController`)
*   **Caching:** Endpoint `GET /api/villas` memiliki mekanisme caching selama 3600 detik (1 jam) menggunakan kunci `villas_catalog` untuk mempercepat waktu muat bagi pengunjung.
*   **Cache Invalidation:** Setiap operasi manipulasi data (Create, Update, Delete) pada `VillaController` akan secara otomatis memanggil `Cache::forget('villas_catalog')` untuk memastikan pengunjung selalu mendapatkan data yang valid.
*   **Eager Loading:** Saat mengambil data villa, relasi `images` langsung dimuat menggunakan `.with('images')` untuk menghindari *N+1 Query Problem*.

## 6. Riwayat Pengembangan Terakhir
Berdasarkan log pengembangan terakhir:
1.  **Transisi Database:** Pemindahan database dari remote Supabase ke PostgreSQL lokal di dalam Docker untuk meningkatkan latensi dan performa.
2.  **Perbaikan Error:** Menyelesaikan isu tabel cache dan jobs yang hilang dengan membuat file migrasi eksplisit (`create_cache_table`, `create_jobs_table`).
3.  **Keamanan:** Menyesuaikan Row Level Security untuk melindungi data jika database terekspos.
