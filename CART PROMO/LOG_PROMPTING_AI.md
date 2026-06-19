# Rekap Log Prompting AI - Tugas Besar IAE 2026

Dokumen ini berisi rekap prompting dengan AI Assistant disini saya pake Antigravity dalam proses integrasi microservices, konfigurasi gateway, dan penyelesaian bug end-to-end.

---

## Daftar Prompting Utama & Solusi

### 1. Masalah Integrasi NIM ke SSO M2M 
* **Prompt dari saya**:
  > *"tolong disesuaikan agar bisa hit dan mendapatkan token baik yang sebelumnya sudah berhasil or belum, sesuai nim kelompok gw yg gw kirimin"*
* **Kendala**: bapak kan punya aturan baru di mana setiap request token M2M antar-servis wajib menyertakan parameter `nim` mahasiswa selain `api_key`.
* **Solusi dari AI**:
  * Menambahkan environment variable `IAE_NIM` untuk setiap microservice di `docker-compose.yml` sesuai NIM anggota kelompok yang bersangkutan.
  * Mengubah script penarikan token SSO di **Service A** (`CentralSSOService.php`), **Service B** (`IaeCentralTokenClient.php`), dan **Service D** (`SsoService.php`) agar secara otomatis menyertakan parameter `'nim' => env('IAE_NIM')` ke body request SSO Dosen.

---

### 2. Masalah Penyesuaian Rute & Kontrak Dosen 
* **Prompt dari saya**:
  > *"tambahin yg disuruh dosen sesuai dengan nim kelompok gw, coba lu review dulu yg gw maksud"*
* **kendala**: di kontrak yang sama bapak rute Service B tidak menggunakan prefix `/v1` dan rute plural promo diubah menjadi `/api/v1/promos`.
* **Solusi dari AI**:
  * Mengubah seluruh file rute `routes/api.php` di Service B untuk menghapus prefix `/v1/` dan mengubah rute dari `/checkouts` menjadi `/checkout`.
  * Mengubah rute promo di Service C dari `/promo` menjadi `/promos`.
  * Memperbarui file konfigurasi Nginx API Gateway agar meneruskan path `/api/checkout`, `/api/payment`, dan `/api/orders` langsung ke Service B tanpa prefix `/v1/`.
  * Memindahkan letak pencocokan lokasi regex Service C di Nginx Gateway ke urutan teratas agar request login `/api/v1/auth/login` tidak bertabrakan dengan Service A.

---

### 3. Masalah Koneksi DNS Container 
* **Prompt dari saya**:
  > *"kenapa service checkout ga bisa nge-hit iae-sso.virtualfri.id?"*
* **Kendala**: Container di Docker WSL2 Windows suka gagal melakukan resolusi DNS eksternal ke domain SSO Dosen.
* **Solusi dari AI**:
  * Menambahkan konfigurasi network `dns` (`8.8.8.8` & `1.1.1.1`) pada seluruh service aplikasi di file `docker-compose.yml` untuk memaksa container menggunakan DNS resolver publik Google dan Cloudflare.

---

### 4. Masalah Error 401 Unauthorized pada Inter-Service Call
* **Prompt dari saya**:
  > *"kenapa waktu checkout dipanggil, responnya 401 Unauthorized dari service keranjang?"*
* **kendala**: Saat Service B mencoba mengambil rincian keranjang belanja ke Service C, request tersebut ditolak karena Service B tidak meneruskan token JWT milik pelanggan.
* **Solusi dari AI**:
  * Memodifikasi `CartPromoClient.php` di Service B agar menangkap Bearer token dari request pelanggan asli, lalu meneruskannya di header request (`Authorization: Bearer <TOKEN>`) saat menghubungi Service C.

---

### 5. Masalah Postman Redirect (Laravel Welcome Page HTML)
* **Prompt dari saya**:
  > *"gabisaa, kok malah dapet halaman HTML Laravel Not Found / Welcome Page?"*
* **kendala**: saya kan nge POST data ke `/api/v1/products` di Postman tapi datanya ditolak validasi. Padahal di Postman saya udah pake header **`Accept: application/json`**, tapi tetep aja redirect ke halaman Welcome Page HTML, itu kenapa ya?
* **Solusi dari AI**:
  * Menginstruksikan user untuk menambahkan header **`Accept: application/json`** pada setiap request API di Postman agar Laravel selalu mengembalikan respon validasi dalam bentuk format JSON, bukan redirect HTML.

---

### 6. Masalah Validasi Tipe Data `cart_id` (Error 422)
* **Prompt dari user**:
  > *"The cart id field must be a string. Padahal saya input cart_id: 2"*
* **kendala**: Validator di `CheckoutController.php` menetapkan aturan `'cart_id' => 'required|string'`, sehingga input angka biasa (`2`) dianggap tidak sah.
* **Solusi dari AI**:
  * Mengubah aturan validasi di `CheckoutController.php` menjadi `'cart_id' => ['nullable']` agar bisa menerima angka langsung (integer) maupun teks string (seperti `"2"`) guna menghindari kegagalan tipe data.

---

### 7. Masalah Promo "Minimum transaksi belum memenuhi" (Error 400)
* **Prompt dari user**:
  > *"Minimum transaksi belum memenuhi saat apply promo..."*
* **kendala**: Endpoint `/api/v1/promos/apply` membutuhkan nominal belanja (`total_price`) untuk dihitung terhadap limit minimum transaksi promo, namun user hanya mengirimkan parameter `"code": "PROMO10"`.
* **Solusi dari AI**:
  * Menjelaskan parameter wajib yang harus dikirim di request body JSON yaitu `total_price` (dengan nilai minimal Rp 50.000 untuk kode `PROMO10`).
