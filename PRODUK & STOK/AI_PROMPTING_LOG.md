# Rekap Log Prompting dengan AI (Produk & Stok Service)

## Identitas Layanan
- **Nama Pengembang:** Sepdaffa Raja
- **NIM:** 102022400191
- **Nama Layanan:** Service A - Produk & Stok Service

---

### Sesi 1: Perancangan Struktur Awal & Database Laravel

**User:**
Saya mendapat tugas membuat Service A (Produk & Stok) menggunakan Laravel 11. Layanan ini harus mendukung REST API dengan routing versioning `/api/v1/products` dan database MySQL. Di dalam database, saya butuh tabel `products` dengan field: `id` (string uuid/custom), `name`, `sku`, `price`, `stock`, dan `description`. Tolong buatkan contoh file migration dan model Laravel untuk kebutuhan tersebut.

**AI:**
AI membantu memberikan contoh migration dan model Laravel yang sesuai dengan kebutuhan tabel produk. Dari contoh tersebut saya memahami cara membuat struktur database, menentukan field yang dibutuhkan, serta menghubungkan model dengan tabel yang ada pada database.

---

### Sesi 2: Implementasi REST API, API Key, dan Otorisasi SSO (JWT)

**User:**
Oke, database sudah siap. Sekarang saya perlu membuat endpoint REST API berikut: GET, POST, dan detail produk. Endpoint ini juga harus dilindungi menggunakan API Key dan JWT dari SSO Pusat. Tolong bantu implementasikan middleware dan controller-nya.

**AI:**
AI membantu memberikan contoh implementasi controller dan middleware yang digunakan untuk REST API. Selain itu, AI juga memberikan gambaran mengenai proses validasi API Key, penggunaan JWT, serta pembatasan akses berdasarkan role pengguna sehingga saya lebih memahami alur keamanan pada service.

---

### Sesi 3: Integrasi GraphQL dan Playground

**User:**
Saya harus menambahkan endpoint GraphQL yang bisa melakukan query daftar produk dengan model schema yang sama seperti REST API. Saya ingin menggunakan GraphQL Playground untuk pengetesannya. Bagaimana caranya di Laravel?

**AI:**
AI membantu menjelaskan konsep dasar GraphQL serta memberikan contoh schema dan query yang dapat digunakan untuk menampilkan data produk. Dari penjelasan tersebut saya memahami cara kerja GraphQL dan bagaimana melakukan pengujian endpoint menggunakan GraphQL Playground.

---

### Sesi 4: Dockerization & Docker Compose

**User:**
Layanan saya harus bisa dijalankan di dalam Docker secara mandiri. Tolong buatkan Dockerfile berbasis Apache + PHP 8.2 dan docker-compose.yml untuk mempermudah menjalankan container bersama database MySQL.

**AI:**
AI membantu memberikan contoh konfigurasi Dockerfile dan Docker Compose sebagai referensi dalam menjalankan aplikasi Laravel dan database MySQL menggunakan container. Dari contoh tersebut saya dapat memahami proses konfigurasi dasar Docker dan cara menghubungkan aplikasi dengan database dalam satu lingkungan container.
