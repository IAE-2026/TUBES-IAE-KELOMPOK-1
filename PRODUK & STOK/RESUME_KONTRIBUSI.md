# Resume Kontribusi Individu

- **Nama:** Sepdaffa Raja
- **NIM:** 102022400191
- **Layanan:** Service A – Produk & Stok Service (Kelompok 1)

Di tugas besar EAI (Tubes IAE) kali ini, saya mendapat bagian untuk mengerjakan Service A yang fokusnya mengelola data produk dan stok barang. Di repository kelompok, kontribusi saya dapat dilihat dari beberapa commit berikut ini:

### #1. 062d134 & 3024549 

Saya membuat folder awal untuk service Produk & Stok agar struktur folder tetap terbaca dan ter-track oleh Git menggunakan file .gitkeep.

### #2. fddc660 

Ini merupakan commit terbesar yang saya kerjakan. Pada commit ini saya membuat seluruh fitur utama Service A dari nol, yang meliputi:
- **REST API:** Membuat fitur CRUD untuk menambah, mengubah, menghapus, dan melihat detail produk, serta fungsi untuk mengurangi stok saat proses checkout.
- **GraphQL:** Membuat query agar data produk dapat diakses melalui GraphQL dan memungkinkan pemilihan field yang dibutuhkan.
- **Keamanan (Otorisasi):** Menambahkan middleware untuk validasi API Key (X-IAE-KEY) menggunakan NIM saya serta verifikasi JWT Token dari SSO Pusat agar login dan role pengguna dapat tersinkronisasi.
- **Database & Migrasi:** Mendesain tabel MySQL lokal untuk data produk dan logging.
- **Docker:** Membuat file Dockerfile dan docker-compose agar aplikasi dapat dijalankan menggunakan container.
- **Dokumentasi & Testing:** Membuat dokumentasi menggunakan Swagger OpenAPI serta menulis unit test agar fitur dapat diuji dengan baik.

### #3. 288537a, bbe2ce1, dan 9ef5726

Saya menambahkan log prompting AI, memperbarui file resume kontribusi individu, serta memperbaiki routing Gateway kelompok (Nginx) dan session driver agar tidak mengalami error timeout saat dideploy bersama service anggota kelompok lainnya.

---

Singkatnya, dari pengerjaan service ini saya belajar cara membangun microservice yang terintegrasi menggunakan Laravel, MySQL, GraphQL, JWT, Docker, dan Nginx API Gateway.

Pada tugas besar IAE ini, saya berkontribusi dalam pembuatan Service A yang digunakan untuk mengelola data produk dan stok. Saya membuat struktur awal project, mengembangkan fitur CRUD produk, serta mengatur pengurangan stok saat terjadi transaksi.

Saya juga menambahkan sistem keamanan menggunakan API Key dan JWT dari SSO Pusat, membuat GraphQL untuk menampilkan data produk, serta membuat database dan migration yang dibutuhkan oleh service. Saya juga melakukan konfigurasi Docker, dokumentasi API menggunakan Swagger, dan membuat beberapa pengujian untuk memastikan fitur dapat berjalan dengan baik.