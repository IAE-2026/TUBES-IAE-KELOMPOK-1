# 📝 Resume Kontribusi Git Individu - Tugas Besar IAE 2026

* **Nama**: Naufan Rae Rasyad Torick
* **NIM**: 102022400283
* **Kelompok**: Kelompok 1 (E-Commerce)
* **Total Commit**: 7 Commit

Dokumen ini berisi rangkuman kontribusi saya dalam pembuatan tugas besar e-commerce berbasis microservices ini, berdasarkan riwayat commit Git di repositori kelompok.

---

## 📈 Rangkuman Log Commit

Berikut adalah daftar 7 commit yang saya lakukan selama pengerjaan tugas besar:

| No | Commit Hash | Pesan Commit | Detail Pekerjaan |
|----|-------------|--------------|------------------|
| 1 | `b9ecd8e` | `docs: add team contribution resume and AI prompting log markdown files` | Membuat file laporan tugas besar (`RESUME_KONTRIBUSI.md` dan `LOG_PROMPTING_AI.md`) di dalam repositori. |
| 2 | `d8e1bdb` | `Fix: checkout validation, promo apply fields, and endpoint routing updates` | Memperbaiki validasi `cart_id` di Service B, parameter `total_price` di Service C, dan merapikan rute ulasan produk di Nginx Gateway. |
| 3 | `4d760b9` | `feat: setup docker compose gabungan & api gateway` | Menyatukan semua service ke dalam file `docker-compose.yml` dan mengatur settingan awal Nginx API Gateway. |
| 4 | `ba300fa` | `feat: integrate cart-promo-service to group repository` | Memindahkan folder Service C (Keranjang & Promo) ke dalam repositori utama kelompok. |
| 5 | `9296c52` | `Create .gitkeep` | Membuat file `.gitkeep` di folder Service C agar Git bisa mendeteksi foldernya. |
| 6 | `42d6312` | `feat: tambah cart-promo-service dengan integrasi SSO, SOAP, RabbitMQ` | Membuat fitur utama Service C (Keranjang & Promo) lengkap dengan login SSO, log SOAP Audit, dan antrean pesan RabbitMQ. |
| 7 | `d5b71c4` | `Create .gitkeep` | Membuat folder awal Service C dengan file `.gitkeep`. |

---

## 🛠️ Detail Pekerjaan per Commit

### 1. Membuat Folder Awal Service C (Commit `d5b71c4` & `9296c52`)
* **Penjelasan**: Menyiapkan folder awal untuk Service C (`CART PROMO`) dan menambahkan file `.gitkeep` agar foldernya terdeteksi oleh Git sebelum diisi kode program.

### 2. Membuat Fitur Utama Service C (Commit `42d6312`)
* **Penjelasan**: 
  * Membuat fitur Keranjang dan Promo belanja, lengkap dengan validasi kode promo (seperti minimal belanja dan kuota promo).
  * Menghubungkan login dengan token JWT dari SSO.
  * Menghubungkan log audit menggunakan protokol SOAP XML serta mengirimkan event promo ke RabbitMQ.

### 3. Menggabungkan Kode ke Repositori Kelompok (Commit `ba300fa`)
* **Penjelasan**: Memindahkan seluruh file kode program Service C ke dalam repositori kelompok agar menyatu dengan pekerjaan anggota kelompok lainnya.

### 4. Setting Docker Compose & API Gateway (Commit `4d760b9`)
* **Penjelasan**: 
  * Membuat file `docker-compose.yml` untuk menggabungkan Service A, B, C, D dan databasenya agar bisa dijalankan bersama-sama dengan mudah.
  * Mengatur Nginx API Gateway (di port 80) sebagai pintu utama untuk mengarahkan request API ke service yang tepat.
  * Menambahkan settingan DNS (`8.8.8.8`) di Docker untuk mengatasi kendala internet container saat menghubungi SSO di Windows.

### 5. Perbaikan Bug & Uji Coba Akhir (Commit `d8e1bdb`)
* **Penjelasan**: 
  * Memperbaiki validasi checkout agar `cart_id` bisa menerima angka biasa (integer) atau tulisan (string) dari Postman.
  * Merapikan rute Nginx Gateway agar rute ulasan produk tidak bertabrakan dengan rute service lainnya.
  * Memperbaiki perhitungan minimal belanja untuk klaim promo di Service C.

### 6. Membuat Dokumentasi Tugas (Commit `b9ecd8e`)
* **Penjelasan**: Menyusun file laporan (resume kontribusi ini dan log prompting AI) untuk pengumpulan tugas besar.
