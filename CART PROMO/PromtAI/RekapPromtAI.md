# Rekap Log Prompting dengan AI

## Tanggal: 16 Mei 2026

### 02:13 WIB
Melakukan pengecekan endpoint API menggunakan Laravel dan Postman.  
Ditemukan error:
- `Class "App\\Http\\Controllers\\Controller" not found`

Tindakan:
- Memperbaiki namespace dan struktur file `Controller.php`.

---

### 02:20 WIB
Endpoint `/api/v1/promo/apply` berhasil dijalankan melalui Postman.  
Response:
```json
{
  "status": "success",
  "message": "PROMO WORK"
}
```

---

### 02:23 WIB
Melakukan pengembangan fitur perhitungan promo otomatis berdasarkan:
- kode promo
- total transaksi

Tindakan:
- Menambahkan logika diskon pada `PromoController.php`.

---

### 02:29 WIB
Pengujian ulang endpoint promo berhasil.  
Response:
```json
{
  "status": "success",
  "promo_code": "DISKON10",
  "discount": 10000,
  "final_total": 90000
}
```

---

### 02:32 WIB
Membuat migration dan model untuk tabel promo menggunakan Laravel Artisan.

Command:
```bash
php artisan make:migration create_promos_table
php artisan make:model Promo
```

---

### 02:45 WIB
Melakukan validasi endpoint berdasarkan ketentuan tugas Service C:
- GET `/api/v1/products`
- POST `/api/v1/carts`
- DELETE `/api/v1/carts/{id}`
- GET `/api/v1/carts/{id}`
- POST `/api/v1/promo/apply`
- GET `/api/v1/promo`
- GET `/api/v1/promo/{id}`

Hasil:
- Endpoint berhasil dibuat dan diuji menggunakan Postman.

---

### 02:50 WIB
Memulai konfigurasi Swagger Documentation menggunakan package `L5 Swagger`.

Command:
```bash
php artisan l5-swagger:generate
```

Kendala:
- Error `Required @OA\Info() not found`

---

### 03:00 WIB
Melakukan debugging konfigurasi Swagger:
- Menambahkan annotation `@OA\Info`
- Membuat file dokumentasi OpenAPI
- Membersihkan cache Laravel

Command:
```bash
php artisan optimize:clear
composer dump-autoload
```

---

### 03:27 WIB
Swagger UI berhasil terbuka pada:
```text
http://127.0.0.1:8000/api/documentation
```

Namun ditemukan kendala:
- `Failed to load API definition`

---

### 03:40 WIB
Melakukan perbaikan dependency Swagger dan penyesuaian annotation agar kompatibel dengan versi package yang digunakan.

---

### 04:00 WIB
Melakukan final checking pada:
- Endpoint API
- Response JSON
- Routing Laravel
- Dokumentasi Swagger

Status:
- Sebagian besar fitur utama telah berjalan dengan baik.
- Swagger masih dalam tahap penyesuaian konfigurasi.

---

## Tanggal: 8 Juni 2026

### 15:00 WIB
Mulai ngerjain integrasi service ke Cloud Dosen (SSO, SOAP, RabbitMQ).  
Baca PDF tugas besar dan cari tau endpoint apa aja yang disediain dosen.

Referensi:
- SSO: `https://iae-sso.virtualfri.id/api/v1/auth/token`
- SOAP: `https://iae-sso.virtualfri.id/soap/v1/audit`
- RabbitMQ: `https://iae-sso.virtualfri.id/api/v1/messages/publish`

---

### 15:30 WIB
Setup config buat integrasi. Tambahin variabel di `.env` dan bikin `config/iae.php`.

Prompt ke AI:
> "gimana cara bikin config file Laravel buat nyimpen URL SSO, SOAP, sama RabbitMQ?"

Hasil:
- Bikin `config/iae.php` yang baca dari `.env`

---

### 16:00 WIB
Mulai bikin SSO login. Butuh library buat verify JWT (RS256).

Command:
```bash
composer require firebase/php-jwt
```

Prompt ke AI:
> "cara verify JWT pakai JWKS di Laravel gimana?"

Hasil:
- Bikin `SsoService.php` — method `login()`, `getJwks()`, `verifyToken()`
- JWKS di-cache 1 jam biar ga fetch tiap request

---

### 16:30 WIB
Bikin middleware buat proteksi endpoint pakai JWT.

Prompt ke AI:
> "bikin middleware Laravel yang cek Bearer token dan verify pakai JWKS"

Hasil:
- Bikin `JwtAuthMiddleware.php`
- Kalau token invalid/expired, return 401 JSON
- Kalau valid, attach decoded user ke request

Kendala:
- Bingung cara register middleware di Laravel 12 (beda sama versi lama)
- Ternyata di `bootstrap/app.php` pakai `->withMiddleware()`

---

### 17:00 WIB
Bikin AuthController dan tabel local_roles.

Prompt ke AI:
> "bikin controller login yang forward ke SSO, terus bikin tabel roles buat mapping user SSO ke role lokal"

Hasil:
- `AuthController.php` — endpoint `POST /auth/login` dan `GET /auth/me`
- Migration `local_roles` — mapping email SSO ke role (admin/customer)
- Seeder buat set `warga28@ktp.iae.id` sebagai admin

---

### 17:30 WIB
Lanjut bikin SOAP Audit Service.

Prompt ke AI:
> "cara bikin SOAP XML envelope manual tanpa PHP SOAP extension gimana? terus kirim pakai HTTP client Laravel"

Hasil:
- Bikin `SoapAuditService.php`
- XML envelope dibikin manual pakai string concatenation
- Tag wajib: `<TeamID>`, `<ActivityName>`, `<LogContent>` (CDATA JSON)
- Parse response pakai regex buat ambil ReceiptNumber
- ReceiptNumber disimpen ke tabel `audit_receipts`

---

### 18:00 WIB
Bikin RabbitMQ Publisher.

Prompt ke AI:
> "cara publish event ke RabbitMQ dosen lewat HTTP API, bukan AMQP protocol"

Hasil:
- Bikin `RabbitMqPublisher.php`
- Kirim JSON event ke `/api/v1/messages/publish` pakai Bearer token
- Pola fire-and-forget (ga ganggu flow utama kalau gagal)

---

### 18:30 WIB
Modifikasi controller-controller yang sudah ada.

Perubahan:
- `PromoController.php` — tambahin SOAP audit + RabbitMQ di `apply()`
- `CartController.php` — tambahin RabbitMQ di `store()` dan `destroy()`
- `ApplyPromo.php` (GraphQL) — tambahin SOAP audit + RabbitMQ

---

## Tanggal: 11 Juni 2026

### 05:30 WIB
Setup API Gateway pakai Nginx.

Prompt ke AI:
> "bikin Nginx reverse proxy buat bungkus Laravel, port 8000 ga boleh diakses langsung"

Hasil:
- Bikin `nginx/default.conf` — reverse proxy ke app:8000
- Update `docker-compose.yml` — tambahin service Nginx, ganti `ports` ke `expose` di app

---

### 06:00 WIB
Build dan deploy Docker.

Command:
```bash
docker compose up -d --build
docker compose exec app php artisan migrate --force
docker compose exec app php artisan db:seed --class=LocalRoleSeeder
```

Kendala:
- `firebase/php-jwt` belum ke-install di dalam container Docker
- Fix: `docker compose exec app composer require firebase/php-jwt`

---

### 06:30 WIB
Testing semua endpoint.

Hasil testing:
- ✅ SSO Login — dapat JWT token, profile: Bayu Setiawan
- ✅ JWT Auth `/me` — decoded user + role admin
- ✅ Protected route tanpa token — 401 Unauthorized
- ✅ Apply Promo — SOAP audit terkirim, ReceiptNumber: `IAE-LOG-2026-DBB6301B`
- ✅ Create Cart — event `cart.created` terpublish ke RabbitMQ
- ✅ Gateway blocking — port 8000 ga bisa diakses langsung

Semua integrasi berjalan.

