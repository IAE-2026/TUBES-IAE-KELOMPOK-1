# Resume Kontribusi Individu

## Informasi Mahasiswa
- **Nama:** Sepdaffa Raja
- **NIM:** 102022400191
- **Layanan (Service):** Service A - Produk & Stok Service (Kelompok 1)

---

## Log Commit Git
Berikut adalah daftar commit yang dilakukan oleh Sepdaffa Raja pada repository kelompok:

1. **`062d134` (2026-06-17)**
   - **Deskripsi:** `Create .gitkeep`
   - **Tujuan:** Inisialisasi struktur direktori awal untuk layanan Produk & Stok.
   
2. **`3024549` (2026-06-17)**
   - **Deskripsi:** `Create .gitkeep`
   - **Tujuan:** Menjaga struktur folder agar ter-track oleh Git.

3. **`fddc660` (2026-06-17)**
   - **Deskripsi:** `feat: add produk & stok service implementation`
   - **Tujuan:** Implementasi penuh Service A (Produk & Stok) mencakup REST API, GraphQL, dokumentasi Swagger, autentikasi SSO, integrasi database, Docker, dan unit testing.

---

## Detail Kontribusi Implementasi
Pada commit `fddc660`, berikut adalah komponen penting yang telah saya rancang dan buat sendiri:

### 1. Pengembangan REST API & Otorisasi
- **[ProductController.php](file:///c:/Users/M%20S%20I/Downloads/TUBES%20EAI%201/TUBES-IAE-KELOMPOK-1/PRODUK%20&%20STOK/app/Http/Controllers/Api/ProductController.php)**: Logika CRUD produk, pengurangan stok saat checkout, SOAP Audit logging, dan pengiriman event ke RabbitMQ.
- **[ApiKeyMiddleware.php](file:///c:/Users/M%20S%20I/Downloads/TUBES%20EAI%201/TUBES-IAE-KELOMPOK-1/PRODUK%20&%20STOK/app/Http/Middleware/ApiKeyMiddleware.php)**: Memvalidasi request menggunakan header `X-IAE-KEY` yang dicocokkan dengan NIM pemilik service.
- **[SsoAuthMiddleware.php](file:///c:/Users/M%20S%20I/Downloads/TUBES%20EAI%201/TUBES-IAE-KELOMPOK-1/PRODUK%20&%20STOK/app/Http/Middleware/SsoAuthMiddleware.php)**: Memvalidasi JWT token dari SSO Pusat untuk membatasi akses berdasarkan role (Otorisasi RBAC).

### 2. Integrasi GraphQL
- **[ProductsQuery.php](file:///c:/Users/M%20S%20I/Downloads/TUBES%20EAI%201/TUBES-IAE-KELOMPOK-1/PRODUK%20&%20STOK/app/GraphQL/Queries/ProductsQuery.php)**: Mengambil semua data produk atau filter per produk via query GraphQL.
- **[ProductType.php](file:///c:/Users/M%20S%20I/Downloads/TUBES%20EAI%201/TUBES-IAE-KELOMPOK-1/PRODUK%20&%20STOK/app/GraphQL/Types/ProductType.php)**: Menentukan schema tipe data GraphQL untuk model `Product`.

### 3. Integrasi Layanan SSO Pusat
- **[CentralSSOService.php](file:///c:/Users/M%20S%20I/Downloads/TUBES%20EAI%201/TUBES-IAE-KELOMPOK-1/PRODUK%20&%20STOK/app/Services/CentralSSOService.php) & [JwtVerifier.php](file:///c:/Users/M%20S%20I/Downloads/TUBES%20EAI%201/TUBES-IAE-KELOMPOK-1/PRODUK%20&%20STOK/app/Services/JwtVerifier.php)**: Modul untuk memverifikasi JWT token, mendecode payload JWT dari Cloud SSO Pusat (`https://iae-sso.virtualfri.id`), serta memetakan role ke database lokal.

### 4. Database & Migrasi
- **[2026_05_15_131039_create_products_table.php](file:///c:/Users/M%20S%20I/Downloads/TUBES%20EAI%201/TUBES-IAE-KELOMPOK-1/PRODUK%20&%20STOK/database/migrations/2026_05_15_131039_create_products_table.php)**: Skema tabel produk lokal yang menampung informasi produk dan kuantitas stok.
- **[2026_06_09_123605_create_roles_and_audit_logs_tables.php](file:///c:/Users/M%20S%20I/Downloads/TUBES%20EAI%201/TUBES-IAE-KELOMPOK-1/PRODUK%20&%20STOK/database/migrations/2026_06_09_124634_create_roles_and_audit_logs_tables.php)**: Skema tabel lokal untuk memetakan role user dan menyimpan ReceiptNumber dari SOAP Audit.

### 5. Dockerization & Konfigurasi Gateway
- **[Dockerfile](file:///c:/Users/M%20S%20I/Downloads/TUBES%20EAI%201/TUBES-IAE-KELOMPOK-1/PRODUK%20&%20STOK/Dockerfile) & [docker-compose.yml](file:///c:/Users/M%20S%20I/Downloads/TUBES%20EAI%201/TUBES-IAE-KELOMPOK-1/PRODUK%20&%20STOK/docker-compose.yml)**: Konfigurasi docker agar service dapat dijalankan secara terisolasi pada port internal `8000` dengan database MySQL.
- **[default.conf](file:///c:/Users/M%20S%20I/Downloads/TUBES%20EAI%201/TUBES-IAE-KELOMPOK-1/gateway/default.conf)**: Konfigurasi routing `/api/v1/products` di API Gateway Nginx kelompok.

### 6. Dokumentasi & Pengujian
- **[l5-swagger.php](file:///c:/Users/M%20S%20I/Downloads/TUBES%20EAI%201/TUBES-IAE-KELOMPOK-1/PRODUK%20&%20STOK/config/l5-swagger.php)**: Konfigurasi Swagger OpenAPI spec.
- **[SsoIntegrationTest.php](file:///c:/Users/M%20S%20I/Downloads/TUBES%20EAI%201/TUBES-IAE-KELOMPOK-1/PRODUK%20&%20STOK/tests/Feature/SsoIntegrationTest.php)**: Pengujian REST API dan middleware keamanan secara otomatis.
