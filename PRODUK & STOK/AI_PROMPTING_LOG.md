# Rekap Log Prompting dengan AI (Produk & Stok Service)

## Identitas Layanan
- **Nama Pengembang:** Sepdaffa Raja
- **NIM:** 102022400191
- **Nama Layanan:** Service A - Produk & Stok Service

---

### Sesi 1: Perancangan Struktur Awal & Database Laravel

**User:**
> Halo! Saya mendapat tugas membuat Service A (Produk & Stok) menggunakan Laravel 11. Layanan ini harus mendukung REST API dengan routing versioning `/api/v1/products` dan database MySQL. Di dalam database, saya butuh tabel `products` dengan field: `id` (string uuid/custom), `name`, `sku`, `price`, `stock`, dan `description`. Tolong buatkan contoh file migration dan model Laravel untuk kebutuhan tersebut.

**AI:**
> Tentu! Di Laravel 11, Anda bisa membuat model beserta file migrasinya sekaligus dengan perintah:
> ```bash
> php artisan make:model Product -m
> ```
> 
> Berikut adalah struktur file migrasinya (`database/migrations/xxxx_create_products_table.php`):
> ```php
> use Illuminate\Database\Migrations\Migration;
> use Illuminate\Database\Schema\Blueprint;
> use Illuminate\Support\Facades\Schema;
> 
> return new class extends Migration {
>     public function up(): void {
>         Schema::create('products', function (Blueprint $table) {
>             $table->string('id')->primary(); // ID Kustom (contoh: PROD-101)
>             $table->string('name');
>             $table->string('sku')->unique();
>             $table->decimal('price', 12, 2);
>             $table->integer('stock')->default(0);
>             $table->text('description')->nullable();
>             $table->timestamps();
>         });
>     }
>     
>     public function down(): void {
>         Schema::dropIfExists('products');
>     }
> };
> ```
> 
> Dan di file Model (`app/Models/Product.php`), tambahkan konfigurasi agar `id` tidak di-auto-increment secara integer:
> ```php
> namespace App\Models;
> 
> use Illuminate\Database\Eloquent\Model;
> 
> class Product extends Model {
>     protected $primaryKey = 'id';
>     public $incrementing = false;
>     protected $keyType = 'string';
>     
>     protected $fillable = [
>         'id', 'name', 'sku', 'price', 'stock', 'description'
>     ];
> }
> ```

---

### Sesi 2: Implementasi REST API, API Key, dan Otorisasi SSO (JWT)

**User:**
> Oke, database sudah siap. Sekarang saya perlu membuat endpoint REST API berikut:
> 1. `GET /api/v1/products` (ambil semua produk)
> 2. `GET /api/v1/products/{id}` (ambil detail satu produk)
> 3. `POST /api/v1/products` (tambah produk baru)
> 
> Endpoint ini harus dilindungi dua middleware:
> 1. `X-IAE-KEY` di header untuk memvalidasi API Key (isinya NIM saya: `102022400191`).
> 2. Pengecekan JWT Token dari SSO Pusat (`https://iae-sso.virtualfri.id`) untuk membatasi hak akses role. Hanya user dengan role `admin` atau `staff` yang boleh memanggil `POST /api/v1/products`.
> Tolong bantu implementasikan middleware dan Controller-nya.

**AI:**
> Bagus! Mari kita buat middleware keamanannya terlebih dahulu.
> 
> 1. **ApiKeyMiddleware (`app/Http/Middleware/ApiKeyMiddleware.php`):**
> ```php
> namespace App\Http\Middleware;
> 
> use Closure;
> use Illuminate\Http\Request;
> 
> class ApiKeyMiddleware {
>     public function handle(Request $request, Closure $next) {
>         $apiKey = $request->header('X-IAE-KEY');
>         if ($apiKey !== '102022400191') {
>             return response()->json([
>                 'status' => 'error',
>                 'message' => 'Unauthorized. Invalid API Key.'
>             ], 401);
>         }
>         return $next($request);
>     }
> }
> ```
> 
> 2. **SsoAuthMiddleware (`app/Http/Middleware/SsoAuthMiddleware.php`):**
> Middleware ini mendecode JWT dan mengecek role.
> ```php
> namespace App\Http\Middleware;
> 
> use Closure;
> use Illuminate\Http\Request;
> use App\Services\JwtVerifier;
> 
> class SsoAuthMiddleware {
>     public function handle(Request $request, Closure $next, ...$roles) {
>         $authHeader = $request->header('Authorization');
>         if (!$authHeader || !str_starts_with($authHeader, 'Bearer ')) {
>             return response()->json(['status' => 'error', 'message' => 'Token JWT tidak ditemukan'], 401);
>         }
>         
>         $token = substr($authHeader, 7);
>         $verifier = new JwtVerifier();
>         $payload = $verifier->verifyAndDecode($token);
>         
>         if (!$payload) {
>             return response()->json(['status' => 'error', 'message' => 'Token JWT tidak valid/kedaluwarsa'], 401);
>         }
>         
>         $userRole = $payload['role'] ?? 'customer';
>         if (!empty($roles) && !in_array($userRole, $roles)) {
>             return response()->json(['status' => 'error', 'message' => 'Otorisasi gagal. Anda tidak memiliki akses.'], 403);
>         }
>         
>         $request->attributes->set('sso_user', $payload);
>         return $next($request);
>     }
> }
> ```
> 
> 3. **ProductController (`app/Http/Controllers/Api/ProductController.php`):**
> Mengembalikan format standard JSON response sesuai kontrak.
> ```php
> namespace App\Http\Controllers\Api;
> 
> use App\Http\Controllers\Controller;
> use App\Models\Product;
> use Illuminate\Http\Request;
> 
> class ProductController extends Controller {
>     public function index() {
>         $products = Product::all();
>         return response()->json([
>             'status' => 'success',
>             'message' => 'Products retrieved successfully',
>             'data' => $products,
>             'meta' => ['service_name' => 'produk-stok-service', 'api_version' => 'v1']
>         ], 200);
>     }
> 
>     public function show($id) {
>         $product = Product::find($id);
>         if (!$product) {
>             return response()->json(['status' => 'error', 'message' => 'Product not found'], 404);
>         }
>         return response()->json([
>             'status' => 'success',
>             'message' => 'Product detail retrieved',
>             'data' => $product,
>             'meta' => ['service_name' => 'produk-stok-service', 'api_version' => 'v1']
>         ], 200);
>     }
> 
>     public function store(Request $request) {
>         $validated = $request->validate([
>             'id' => 'required|string|unique:products,id',
>             'name' => 'required|string',
>             'sku' => 'required|string|unique:products,sku',
>             'price' => 'required|numeric|min:0',
>             'stock' => 'required|integer|min:0',
>             'description' => 'nullable|string'
>         ]);
>         
>         $product = Product::create($validated);
>         return response()->json([
>             'status' => 'success',
>             'message' => 'Product created successfully',
>             'data' => $product,
>             'meta' => ['service_name' => 'produk-stok-service', 'api_version' => 'v1']
>         ], 201);
>     }
> }
> ```

---

### Sesi 3: Integrasi GraphQL dan Playground

**User:**
> Saya harus menambahkan endpoint GraphQL yang bisa melakukan query daftar produk dengan model schema yang sama seperti REST API. Saya ingin menggunakan GraphQL Playground untuk pengetesannya. Bagaimana caranya di Laravel?

**AI:**
> Anda bisa mengimplementasikan schema GraphQL sederhana. Buat endpoint di `/graphql` dan daftarkan route-nya.
> 
> 1. **GraphQL Type Definition (`app/GraphQL/Types/ProductType.php`):**
> ```php
> namespace App\GraphQL\Types;
> 
> class ProductType {
>     public static function toType() {
>         return [
>             'name' => 'Product',
>             'fields' => [
>                 'id' => ['type' => 'String'],
>                 'name' => ['type' => 'String'],
>                 'sku' => ['type' => 'String'],
>                 'price' => ['type' => 'Float'],
>                 'stock' => ['type' => 'Int'],
>                 'description' => ['type' => 'String'],
>             ]
>         ];
>     }
> }
> ```
> 
> 2. **GraphQL Query Resolver (`app/GraphQL/Queries/ProductsQuery.php`):**
> ```php
> namespace App\GraphQL\Queries;
> 
> use App\Models\Product;
> 
> class ProductsQuery {
>     public function resolve() {
>         return Product::all();
>     }
> }
> ```
> 
> Dengan mendaftarkan endpoint `/graphql` di routes, GraphQL client dapat memilah field mana saja yang ingin dipanggil (misal: hanya meminta `name` dan `price` saja).

---

### Sesi 4: Dockerization & Docker Compose

**User:**
> Layanan saya harus bisa dijalankan di dalam Docker secara mandiri. Tolong buatkan `Dockerfile` berbasis Apache + PHP 8.2 dan `docker-compose.yml` untuk mempermudah menjalankan container bersama database MySQL.

**AI:**
> Berikut konfigurasi Dockernya:
> 
> 1. **`Dockerfile`:**
> ```dockerfile
> FROM php:8.2-apache
> 
> RUN apt-get update && apt-get install -y \
>     git curl zip unzip libpng-dev libonig-dev libxml2-dev \
>     && docker-php-ext-install pdo pdo_mysql mbstring
> 
> RUN a2enmod rewrite
> 
> ENV APACHE_DOCUMENT_ROOT /app/public
> RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
> RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf
> 
> COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
> 
> WORKDIR /app
> COPY . .
> 
> RUN composer install --no-dev --optimize-autoloader
> RUN chown -R www-data:www-data storage bootstrap/cache
> 
> EXPOSE 8000
> ```
> 
> 2. **`docker-compose.yml`:**
> ```yaml
> version: '3.8'
> services:
>   produk-stok-app:
>     build:
>       context: .
>       dockerfile: Dockerfile
>     container_name: produk-stok-app
>     restart: unless-stopped
>     expose:
>       - "8000"
>     working_dir: /app
>     volumes:
>       - .:/app
>     environment:
>       APP_ENV: local
>       DB_CONNECTION: mysql
>       DB_HOST: produk-stok-db
>       DB_PORT: 3306
>       DB_DATABASE: produk_stok_service
>       DB_USERNAME: root
>       DB_PASSWORD: root123
>     depends_on:
>       - produk-stok-db
>     networks:
>       - iae-network
> 
>   produk-stok-db:
>     image: mysql:8.0
>     container_name: produk-stok-db
>     environment:
>       MYSQL_ROOT_PASSWORD: root123
>       MYSQL_DATABASE: produk_stok_service
>     networks:
>       - iae-network
> 
> networks:
>   iae-network:
>     external: true
> ```
