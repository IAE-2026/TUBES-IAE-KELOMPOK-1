# 📝 Resume Kontribusi Git Individu - Tugas Besar IAE 2026

* **Nama**: Naufan Rae Rasyad Torick
* **NIM**: 102022400283
* **Kelompok**: Kelompok 1 (E-Commerce)
* **Total Commit**: 7 Commit

Dokumen ini merangkum seluruh kontribusi individu saya yang tercatat pada log commit Git di repositori utama untuk pengembangan dan integrasi sistem microservices E-Commerce.

---

## 📈 Rangkuman Log Commit

Berikut adalah daftar 7 commit yang saya lakukan sepanjang pengerjaan tugas besar ini:

| No | Commit Hash | Pesan Commit | Kontribusi & Detail Teknis |
|----|-------------|--------------|-----------------------------|
| 1 | `b9ecd8e` | `docs: add team contribution resume and AI prompting log markdown files` | Menambahkan berkas dokumentasi luaran tugas besar (`RESUME_KONTRIBUSI.md` & `LOG_PROMPTING_AI.md`) ke repositori utama. |
| 2 | `d8e1bdb` | `Fix: checkout validation, promo apply fields, and endpoint routing updates` | Memperbaiki validasi `cart_id` agar menerima integer di Service B, parameter `total_price` di Service C, serta merapikan routing ulasan pelanggan di Nginx Gateway. |
| 3 | `4d760b9` | `feat: setup docker compose gabungan & api gateway` | Mengintegrasikan seluruh microservice ke dalam satu arsitektur `docker-compose.yml` terpadu beserta setup awal konfigurasi Nginx API Gateway. |
| 4 | `ba300fa` | `feat: integrate cart-promo-service to group repository` | Melakukan integrasi dan penggabungan berkas kode program Service C (Keranjang & Promo) ke dalam folder proyek repositori utama kelompok. |
| 5 | `9296c52` | `Create .gitkeep` | Membuat file placeholder `.gitkeep` agar Git dapat melacak dan melestarikan struktur folder kosong pada Service C sebelum pemindahan file. |
| 6 | `42d6312` | `feat: tambah cart-promo-service dengan integrasi SSO, SOAP, RabbitMQ` | Mengembangkan fitur inti Service C (Keranjang & Promo) terintegrasi dengan otentikasi JWT SSO, log SOAP Audit, dan antrean pesan RabbitMQ. |
| 7 | `d5b71c4` | `Create .gitkeep` | Setup awal file placeholder `.gitkeep` untuk melacak struktur direktori pengembangan Service C. |

---

## 🛠️ Detail Kontribusi Teknis per Commit

### 1. Inisialisasi Struktur & Placeholder Service C (Commit `d5b71c4` & `9296c52`)
* **Kontribusi**: Menyiapkan struktur folder Git awal untuk Service C (`CART PROMO`) dan menempatkan file `.gitkeep` agar Git melacak folder kosong sebelum kode program mulai diintegrasikan.

### 2. Pengembangan Fitur Inti Service C (Commit `42d6312`)
* **Kontribusi**: 
  * Mengembangkan modul Keranjang & Promo lengkap dengan validasi kode promo, batas kadaluwarsa, minimum transaksi, dan limit kuota.
  * Mengintegrasikan `SsoService` untuk otentikasi pelanggan menggunakan token JWT.
  * Menghubungkan servis ke server audit pusat menggunakan protokol SOAP XML serta mempublikasikan event promo ke sistem antrean pesan RabbitMQ.

### 3. Pemindahan dan Integrasi Kode ke Repositori Utama (Commit `ba300fa`)
* **Kontribusi**: Mengonsolidasikan seluruh folder pengerjaan Service C ke dalam repositori utama agar menjadi satu kesatuan kode dengan microservices milik anggota kelompok lainnya.

### 4. Setup Gabungan Arsitektur Docker & API Gateway (Commit `4d760b9`)
* **Kontribusi**: 
  * Merancang konfigurasi Docker Compose gabungan (`docker-compose.yml`) yang menyatukan Service A, B, C, D beserta databasenya masing-masing ke dalam satu *virtual network* (`iae-network`).
  * Mengonfigurasi Nginx API Gateway (`iae-gateway`) di port 80 untuk mengatur pintu masuk tunggal rute API `/api/v1/` ke masing-masing container.
  * Menambahkan DNS resolver publik (`8.8.8.8`) untuk mengatasi kendala koneksi internet container Docker ke domain SSO Dosen di Windows WSL2.

### 5. Pengujian Akhir & Perbaikan Bug Sistem (Commit `d8e1bdb`)
* **Kontribusi**: 
  * Menyesuaikan validasi di Service B (`CheckoutController.php`) agar field `cart_id` dapat menerima tipe data angka (integer) maupun string dari body Postman.
  * Merapikan routing Nginx Gateway untuk memisahkan pencocokan regex rute `/api/v1/reviews` milik Service D dan Service C agar tidak bertabrakan rute.
  * Memastikan parameter `total_price` dihitung secara benar dalam penentuan syarat minimum klaim kode promo di Service C.

### 6. Dokumentasi Akhir Proyek (Commit `b9ecd8e`)
* **Kontribusi**: Menyusun berkas luaran laporan tugas besar berupa log commit individu ini dan berkas log prompting AI guna pelaporan administrasi akademik.
