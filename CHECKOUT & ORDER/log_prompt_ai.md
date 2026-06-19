# Log Prompt AI - Tugas Besar Kelompok

Dokumen ini mencatat penggunaan AI dalam pengerjaan luaran kelompok Tugas Besar Integrasi Aplikasi Enterprise. Log dimulai dari tahap pengerjaan bagian luar repository gabungan, yaitu API Gateway, Docker Compose, dokumentasi arsitektur, dan struktur pengujian.

## Identitas Konteks

- Mata kuliah: Integrasi Aplikasi Enterprise
- Kelompok: TEAM-01
- Service individu: Checkout & Order Service
- Repository kelompok: `TUBES-IAE-KELOMPOK-1`
- Fokus kontribusi: bagian luar sistem kelompok, API Gateway, Docker Compose gabungan, dokumentasi arsitektur, dan penyesuaian request SSO M2M.

## Prompt 1

**Prompt:**
jadi disini saya dengan tim saya ingin mengerjakan tugas besar dengan service saya yaitu checkout-order dan sudah membuat repository yang berisi seluruh service kelompok saya lalu kerjakan bagian luarnya terlebih dahulu

**Tujuan prompt:**

Meminta bantuan untuk mengerjakan bagian luar repository kelompok terlebih dahulu, bukan logika internal service individu. Bagian luar yang dimaksud adalah struktur integrasi yang membungkus seluruh service kelompok.

**Tindakan AI:**

- Membaca konteks tugas dari file PDF Tugas Besar.
- Mengidentifikasi luaran kelompok yang diminta:
  - API Gateway & Routing Hub.
  - End-to-End Core Business Flow.
  - Central Infrastructure Compliance.
  - Repository gabungan berisi arsitektur container Docker dan konfigurasi API Gateway.
- Mengecek struktur repository lokal.
- Meng-clone repository kelompok ke:

- Mengecek folder service yang tersedia:
  - `CART PROMO`
  - `CHECKOUT & ORDER`
  - `CUSTOMER-REVIEW`
  - `PRODUK & STOK`
- Mengecek `Dockerfile`, `docker-compose.yml`, route API, dan README masing-masing service.

**Hasil dari prompt:**

AI membuat file awal bagian luar repository kelompok:

- `docker-compose.yml`
- `gateway/nginx.conf`
- `gateway/proxy-headers.inc`
- `README.md`
- `docs/arsitektur-luar.md`

**Ringkasan hasil teknis:**

- Menyediakan satu API Gateway berbasis Nginx.
- Membungkus seluruh service di network Docker `iae-network`.
- Mengatur routing endpoint gateway ke service yang sesuai.
- Menyediakan dokumentasi flow end-to-end.
- Menjelaskan endpoint kritis `POST /api/v1/orders`.

## Prompt 2

**Prompt pengguna:**

Dear all, untuk menyesuaikan beberapa hal, saya menambahkan kewajiban penambahan “nim” pada request body untuk request ke SSO M2M ya.

curl -s -X POST http://localhost:8080/api/v1/auth/token \
  -H "Content-Type: application/json" \
  -d '{"api_key":"KEY-MHS-45","nim":"102022400033"}'

tolong disesuaikan agar bisa hit dan mendapatkan token baik yang sebelumnya sudah berhasil atau belum

**Tujuan prompt:**
Menyesuaikan integrasi SSO Machine-to-Machine karena aturan baru mewajibkan field `nim` pada body request token.

**Tindakan AI:**
- Mencari seluruh pemanggilan endpoint token SSO.
- Menemukan client token di `IaeCentralTokenClient`.
- Menambahkan konfigurasi:

env
IAE_CENTRAL_NIM=102022400268

Mengubah body request token M2M dari:

```json
{
  "api_key": "KEY-MHS-343"
}
```

menjadi:

```json
{
  "api_key": "KEY-MHS-343",
  "nim": "102022400268"
}
```

- Menyesuaikan Postman collection dan environment.
- Menambahkan request Postman khusus:

01B - IAE Central - Get M2M Token

- Menyesuaikan repository kelompok agar `docker-compose.yml` juga mengirim `IAE_CENTRAL_NIM`.

**Hasil dari prompt:**

File yang disesuaikan di service Checkout & Order:

- `.env`
- `.env.example`
- `config/services.php`
- `app/Services/IaeCentralTokenClient.php`
- `docs/postman/checkout-order-tugas-3.postman_collection.json`
- `docs/postman/checkout-order-tugas-3.postman_environment.json`
- `docs/postman/README.md`
- `README.md`
- `tests/Feature/CheckoutOrderApiTest.php`

File yang disesuaikan di repository kelompok:

- `docker-compose.yml`
- `README.md`
- `docs/arsitektur-luar.md`

**Verifikasi:**
php artisan test
12 passed, 76 assertions
```

Postman JSON valid dan konfigurasi Docker Compose kelompok valid.

## Prompt 3
ini isi luaran dari kelompok yang sudah selesai

Pengguna memberikan gambar contoh repository kelompok lain yang sudah selesai. Struktur pada contoh berisi:

- folder service-service individu,
- `api-gateway`,
- `docs`,
- `postman`,
- `.gitignore`,
- `README.md`,
- `docker-compose.yml`.

**Tujuan prompt:**
Menjadikan contoh tersebut sebagai acuan struktur luaran repository kelompok.

**Tindakan AI:**
- Membandingkan struktur repository kelompok yang sedang dikerjakan dengan contoh pada gambar.
- Merapikan struktur folder gateway.
- Mengubah folder:
gateway

menjadi:
api-gateway

- Memperbarui `docker-compose.yml` agar mount konfigurasi Nginx dari `./api-gateway`.
- Menambahkan folder `postman`.
- Menambahkan file `.gitignore`.
- Membersihkan folder gateway lama.

**Hasil dari prompt:**

Struktur repository kelompok menjadi:
api-gateway/
CART PROMO/
CHECKOUT & ORDER/
CUSTOMER-REVIEW/
PRODUK & STOK/
docs/
postman/
.gitignore
README.md
docker-compose.yml
```

**Verifikasi:**
docker compose config --quiet

Konfigurasi Docker Compose valid.

## Prompt 4

**Prompt

**Tujuan prompt:**

Membuat file log prompt AI yang mencatat proses pengerjaan dengan AI, dimulai dari prompt pada gambar tentang pengerjaan bagian luar repository kelompok.

**Tindakan AI:**

- Membuat file:

```text
docs/prompt-log-ai.md
```

- Menulis log prompt mulai dari pengerjaan bagian luar repository kelompok.
- Merangkum prompt pengguna, tujuan, tindakan AI, hasil file, dan verifikasi.

**Hasil dari prompt:**

File log prompt AI ini dibuat sebagai bukti akuntabilitas penggunaan AI dalam pengerjaan luaran kelompok.

## Ringkasan Kontribusi AI

AI digunakan sebagai asisten teknis untuk:

- menafsirkan luaran Tugas Besar dari PDF,
- menyusun struktur repository kelompok,
- membuat API Gateway Nginx,
- membuat Docker Compose gabungan,
- membuat dokumentasi arsitektur luar,
- menyesuaikan token SSO M2M dengan field `nim`,
- menyiapkan dokumentasi Postman,
- memvalidasi konfigurasi dan test yang relevan.

## Riwayat Prompt

### [2026-06-20] Troubleshooting 502 Bad Gateway
**Konteks**: Saat mengakses endpoint `POST /api/checkout` lewat Postman (gateway: `http://localhost/api/checkout`), response yang didapat adalah `502 Bad Gateway`.

**Prompt**:
> "ini kenapa pas endpoint http://localhost/api/checkout gini ya" (dengan screenshot Postman)

**Analisis AI**:
1. Mengecek status container dengan `docker ps`, ditemukan container `checkout-order-app` berstatus `Restarting`.
2. Mengecek log container `docker logs checkout-order-app`, ditemukan error `Class "Laravel\Pail\PailServiceProvider" not found`.
3. Menemukan bahwa error ini adalah konflik Docker Volume. Container dijalankan dengan `composer install --no-dev`, namun volume mapping di `docker-compose.yml` (`- ./CHECKOUT & ORDER:/var/www/html`) memuat file cache `bootstrap/cache/packages.php` dari host (yang memiliki dev packages).
4. Solusi: Menghapus file `bootstrap/cache/packages.php` dan `bootstrap/cache/services.php` dari host, lalu merestart container `checkout-order-app`.

### [2026-06-20] Troubleshooting 404 Cart Service
**Konteks**: Setelah 502 resolved, request yang sama memunculkan error body dari Laravel: `Cart service returned HTTP 404` (Cart ID 2 tidak ditemukan).

**Prompt**:
> "skrg gini" (dengan screenshot Postman yang menunjukkan error message dari Cart Service)

**Analisis AI**:
1. Melakukan pencarian kode (grep) untuk pesan error `"Cart service returned HTTP"`.
2. Menemukan bahwa `CartPromoClient.php` melempar exception ini jika request HTTP ke service Cart (`cart-promo-app`) gagal.
3. Mencoba memverifikasi apakah `cart_id: 2` memang ada di database `cart-promo-db` dengan menjalankan query MySQL secara langsung.