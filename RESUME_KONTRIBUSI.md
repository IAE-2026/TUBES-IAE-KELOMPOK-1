# 📝 Resume Kontribusi Kelompok - Tugas Besar IAE 2026

Dokumen ini berisi resume kontribusi dari masing-masing anggota Kelompok 1 dalam perancangan, pengembangan, dan integrasi sistem E-Commerce berbasis Microservices.

---

## 👥 Profil Kelompok & Distribusi Peran

### 1. Naufan Torick (NIM: 102022400283) - *System Integrator & Developer Service C*
* **Peran Utama**: Penanggung jawab integrasi arsitektur sistem (Docker & Nginx API Gateway) serta pengembangan Service C (Keranjang & Promo).
* **Kontribusi Teknis**:
  * **Arsitektur Gabungan (Docker Compose)**: Membuat dan menyatukan konfigurasi `docker-compose.yml` untuk ke-4 microservices beserta database MySQL masing-masing agar dapat berjalan dalam satu jaringan internal (`iae-network`).
  * **API Gateway (Nginx)**: Mengonfigurasi Nginx API Gateway (`iae-gateway`) di port 80 sebagai pintu masuk utama rute API. Mengatur aturan routing regex untuk memisahkan rute `/api/v1/` ke masing-masing container.
  * **Service C (Keranjang & Promo)**: 
    * Mengembangkan rute dan controller untuk manajemen keranjang belanja (`GET /api/v1/carts`, `POST /api/v1/carts`, `DELETE /api/v1/carts/{id}`).
    * Mengimplementasikan fitur kode promo (`POST /api/v1/promos/apply`) dengan validasi batas kadaluwarsa, minimum transaksi, kuota, serta integrasi audit SOAP dan RabbitMQ.
  * **Otentikasi & Keamanan**: Mengintegrasikan otentikasi login pelanggan berbasis JWT dari SSO Dosen di Service C.

### 2. Asep & Dappa (NIM: 102022400191) - *Developer Service A (Produk & Stok)*
* **Peran Utama**: Pengembangan Service A (Produk & Stok) untuk mengelola data katalog barang dan ketersediaan stok.
* **Kontribusi Teknis**:
  * **Katalog Produk**: Mengembangkan fitur CRUD produk (`POST /api/v1/products`, `GET /api/v1/products`, `GET /api/v1/products/{id}`).
  * **Manajemen Stok**: Membuat endpoint pencarian produk, pengecekan stok produk (`GET /api/v1/products/{id}/stock`), dan mekanisme update stok (`PUT /api/v1/products/stock/update`).
  * **Audit SOAP & AMQP**: Menambahkan `CentralSSOService` untuk melakukan registrasi log transaksi stok ke server audit pusat menggunakan protokol SOAP XML serta mempublikasikan event perubahan stok menggunakan RabbitMQ.

### 3. Lutfi (NIM: 102022400268) - *Developer Service B (Checkout & Order)*
* **Peran Utama**: Pengembangan Service B (Checkout & Order) yang menangani transaksi pembelian, pembayaran, dan pembuatan invoice pesanan.
* **Kontribusi Teknis**:
  * **Proses Checkout**: Mengembangkan alur checkout belanja (`POST /api/checkout`) dengan mengambil rincian keranjang belanja secara inter-service dari Service C.
  * **Mekanisme Pembayaran**: Membuat modul tagihan pembayaran (`POST /api/payment`) dan konfirmasi pelunasan pembayaran (`POST /api/payment/confirm`).
  * **Pembuatan Order & Pengurangan Stok**: Mengimplementasikan rute konversi checkout menjadi order resmi (`POST /api/orders`) yang otomatis memicu pengurangan stok produk di Service A secara machine-to-machine (M2M) menggunakan token SSO yang sah.

### 4. Jara (NIM: 102022400054) - *Developer Service D (Customer Review)*
* **Peran Utama**: Pengembangan Service D (Customer Review) untuk menampung ulasan pelanggan setelah transaksi selesai.
* **Kontribusi Teknis**:
  * **Ulasan Produk**: Mengembangkan fitur pengiriman review produk (`POST /api/v1/reviews`) dan pengambilan daftar review per produk (`GET /api/v1/reviews/product/{id}`).
  * **SSO Login & Audit**: Mengintegrasikan `SsoService` untuk memverifikasi token JWT warga sebelum mereka diperbolehkan menulis ulasan produk, serta mengirimkan SOAP audit ke server pusat.

---

## 📈 Log Commit & Sinkronisasi Repositori

Kontribusi kode dan integrasi di dalam repositori Git dilakukan secara kolaboratif melalui branch koordinasi integrasi:
* **Commit `feat: setup docker compose gabungan & api gateway`**: Inisialisasi awal arsitektur multi-container dan penyusunan gateway.
* **Commit `feat: configure api gateway routes and fix docker configurations`**: Penyempurnaan rute API Gateway dan penambahan konfigurasi DNS untuk mengatasi kendala resolusi SSO.
* **Commit `Fix: checkout validation, promo apply fields, and endpoint routing updates`**: Perbaikan validasi data checkout, perbaikan parameter promo, dan perbaikan routing ulasan pelanggan agar selaras dengan spesifikasi dosen.
