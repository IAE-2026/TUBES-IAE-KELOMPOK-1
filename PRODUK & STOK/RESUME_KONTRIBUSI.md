# Resume Kontribusi Individu

- **Nama:** Sepdaffa Raja
- **NIM:** 102022400191
- **Layanan:** Service A – Produk & Stok Service (Kelompok 1)

Di tugas besar EAI (Tubes IAE) kali ini, gua dapet bagian buat ngerjain Service A yang fokusnya buat ngelola data produk sama stok barang. Di repository kelompok, kontribusi gua bisa dilihat dari beberapa commit berikut ini:

1. **`062d134` & `3024549` (17 Juni 2026)**
   Gua bikin folder awal buat service Produk & Stok biar foldernya kebaca sama Git (pake file `.gitkeep`).

2. **`fddc660` (17 Juni 2026)**
   Ini commit paling gede gua. Di sini gua bikin semua fitur utama Service A dari nol, isinya:
   - **REST API:** Bikin CRUD buat nambah, ngedit, hapus, sama ngeliat detail produk, sekalian fungsi buat ngurangin stok pas checkout.
   - **GraphQL:** Bikin query biar data produk bisa dipanggil lewat GraphQL (dan bisa milih field apa aja).
   - **Keamanan (Otorisasi):** Pasang middleware buat cek API Key (`X-IAE-KEY` pake NIM gua) sama verifikasi JWT Token dari SSO Pusat biar akses login/role kesinkron.
   - **Database & Migrasi:** Desain tabel MySQL lokal buat produk dan logging.
   - **Docker:** Bikin file Dockerfile dan docker-compose biar aplikasinya bisa langsung dijalanin di container.
   - **Dokumentasi & Testing:** Bikin dokumentasi pake Swagger OpenAPI sama nulis unit test biar pas dicek dosen aman.

3. **`288537a` & `bbe2ce1` & `9ef5726` (20 Juni 2026)**
   Gua nambahin log prompting AI, nge-update file resume kontribusi ini, sekalian benerin routing Gateway kelompok (Nginx) sama session driver biar gak kena error timeout pas dideploy bareng service temen-temen yang lain.

Singkatnya, dari pengerjaan service ini gua belajar cara bikin microservice terintegrasi pake Laravel, MySQL, GraphQL, JWT, Docker, dan Nginx API Gateway.
