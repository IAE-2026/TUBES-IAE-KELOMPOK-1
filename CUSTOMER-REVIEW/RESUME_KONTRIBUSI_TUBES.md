# Resume Kontribusi Tugas Besar IAE 2026

**Nama:** Azzahra Afidah Yusfa  
**Username GitHub:** azzahraayy  
**Service / Modul Utama:** Customer Review Service  

---

## 🚀 Ringkasan Kontribusi
Sebagai pengembang utama untuk **Customer Review Service**, saya bertanggung jawab membangun sistem ulasan pelanggan yang terhubung secara *end-to-end* dengan arsitektur *microservices* kelompok. Selain itu, saya juga berkontribusi pada proses *troubleshooting* Docker dan *testing* integrasi API Gateway gabungan kelompok.

## 🛠️ Detail Pekerjaan & Fitur yang Diimplementasikan

### 1. Pengembangan Customer Review Service (Backend)
- **Inisialisasi Service**: Membangun service mandiri untuk fitur *Customer Review* menggunakan arsitektur microservices.
- **Pembuatan RESTful API**: 
  - Endpoint `GET /api/v1/reviews` untuk mengambil semua ulasan.
  - Endpoint `GET /api/v1/reviews/product/{product_id}` untuk melihat ulasan berdasarkan ID produk.
  - Endpoint `POST /api/v1/reviews` untuk membuat ulasan baru.
- **Integrasi Wajib Dosen**:
  - **SSO Dosen**: Mengimplementasikan validasi token dan komunikasi Single Sign-On (SSO) Dosen untuk otentikasi *Machine-to-Machine* (M2M).
  - **SOAP Audit**: Menghubungkan service dengan sistem SOAP Audit eksternal untuk mencatat log transaksi secara terpusat.
  - **RabbitMQ**: Menerapkan sistem *message broker* menggunakan RabbitMQ untuk menembakkan *event notification*.

### 2. Infrastruktur & Integrasi Kelompok
- **Penyelesaian Isu Docker-Compose**: Membantu mengidentifikasi dan memperbaiki *error* koneksi MySQL (`Host is not allowed to connect`) pada container `checkout-order-db` dan `cart-promo-db` dengan memodifikasi konfigurasi `MYSQL_ROOT_HOST: '%'` di file `docker-compose.yml`.
- **End-to-End Testing via API Gateway**: Melakukan inisialisasi *Docker environment* di komputer lokal dan melakukan uji coba interaksi antar-service melewati rute Nginx Gateway (`localhost:80`).
- **Manajemen Repositori (Git)**: Merapikan struktur folder *root* repositori (membersihkan direktori `app`, `public`, `config` yang salah lokasi), mengamankan folder kosong dengan `.gitkeep`, dan menyelesaikan proses *merge* dari branch `main` ke branch pribadi.

---

## 📊 Daftar Commit Utama (Git History)

Berikut adalah daftar detail *commit* yang saya buat pada repositori kelompok sebagai bukti pengerjaan tugas, beserta penjelasan dari masing-masing perubahannya:

| No. | Pesan Commit (*Commit Message*) | Hash / SHA | Kategori Pekerjaan | Deskripsi Detail |
|:---:|:---|:---:|:---|:---|
| 1 | `Feature : Customer Review` | `c49d5dc` | **Feature / Backend** | Melakukan inisialisasi awal dan *push* struktur dasar untuk service **Customer Review**. |
| 2 | `Delete .vscode directory` | `ab77978` | **Cleanup** | Menghapus direktori `.vscode` bawaan lokal yang tidak perlu masuk ke repositori. |
| 3 | `feat: add customer review service` | `a09d567` | **Feature / Backend** | Menambahkan implementasi kode untuk layanan ulasan pelanggan (*Customer Review*). |
| 4 | `Delete CUSTOMER REVIEW directory` | `f49848d` | **Refactoring** | Menghapus folder `CUSTOMER REVIEW` yang salah (karena memakai spasi pada penamaan foldernya). |
| 5 | `Merge branch 'main' of ... into branch-azzahra` | `3ca7590` | **Version Control** | Melakukan penarikan (*pull*) pembaruan dari *branch* utama ke *branch* pribadi untuk sinkronisasi kode. |
| 6 | `Delete app directory`, `Delete public directory`, dll. | `b364cb2` dkk | **Cleanup** | Menghapus folder-folder bawaan instalasi framework (seperti `app`, `config`, `database`, `public`) yang salah ter-*push* di *root directory*. |
| 7 | `feat : add customer review` | `a7a5123` | **Feature / Backend** | Menambahkan dan menyempurnakan fitur backend ulasan produk yang telah mengimplementasikan SSO Dosen, SOAP Audit, dan RabbitMQ. |
| 8 | `fix: upload ulang isi folder CUSTOMER-REVIEW` | `083baa5` | **Bugfix** | Memperbaiki lokasi file yang berantakan dengan melakukan proses *upload* ulang seluruh isi folder microservice `CUSTOMER-REVIEW` agar sesuai standar kelompok. |
| 9 | `Create .gitkeep` | `e7297c4` dkk | **Environment Prep** | Menambahkan *file* `.gitkeep` pada folder kosong agar struktur folder tetap dipertahankan oleh sistem Git. |
