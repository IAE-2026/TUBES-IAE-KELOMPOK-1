
Claude selesai merespons
Ketentuan Tugas 3 & Tugas Besar.pdf
pdf

URL dan Akun Tugas IAE.pdf
pdf

ini adalah ketentuan dan dokumen dari tugas 3 dan tugas besar aku, sekarang kamu fokus ke tugas 3 ajaa,, coba jelasin maksudnya dari tugas inii kayak gimana pake bahasa yang mudah dipahami

Jun 9
Penjelasan Tugas 3 - Integrasi Aplikasi Enterprise
Oke, intinya Tugas 3 ini adalah laporan progress individual dari Tugas Besar kelompok. Kamu harus menunjukkan bahwa mini-service yang kamu buat sendiri sudah bisa "ngobrol" dengan 3 sistem terpusat yang disediakan dosen.

Gambaran Besarnya
Bayangkan mini-service kamu itu kayak satu departemen di sebuah perusahaan besar. Nah, perusahaan itu punya 3 infrastruktur pusat yang harus dipakai semua departemen:

1. SSO (Single Sign-On) → Sistem login terpusat
Ibaratnya kartu akses kantor. Kamu nggak boleh buat sistem login sendiri — harus pakai login dari "kantor pusat" (server dosen). Kalau berhasil login, kamu dapat JWT token (semacam tiket digital), terus kamu petakan user-nya ke tabel role di database lokal kamu sendiri.

2. SOAP Audit → Sistem pencatatan transaksi penting ke sistem lama
Setiap ada transaksi kritis (misalnya transaksi keuangan atau perubahan stok), kamu wajib "melaporkannya" ke sistem audit dosen dalam format XML kuno (SOAP). Sebagai bukti laporan berhasil, kamu dapat ReceiptNumber dari server dosen.

3. RabbitMQ → Broadcast notifikasi ke semua departemen
Kalau ada aktivitas bisnis penting, kamu publish event-nya ke RabbitMQ dosen, biar departemen lain bisa tahu secara asinkron (tidak perlu nunggu).

Yang Harus Kamu Kerjakan
Analisis (33%):

Pilih 1 transaksi paling kritis di service kamu (yang mengubah state — misal: transaksi bayar, update stok)
Buat Sequence Diagram yang menggambarkan alur interaksi service kamu dengan ketiga sistem terpusat itu
Teknis (67%):

Modul	Yang Harus Dibuat
SSO (30%)	Kode untuk login ke SSO dosen, tangkap JWT-nya, petakan ke role lokal
SOAP Client (40%)	Kode untuk kirim data transaksi dalam format XML ke endpoint audit dosen, simpan ReceiptNumber-nya
RabbitMQ Publisher (20%)	Kode untuk publish event JSON ke RabbitMQ dosen
Log Prompting (10%)	File .md berisi riwayat chat kamu dengan AI sebagai bukti eksplorasi mandiri
Output yang Dikumpulkan di Repository
analisis_tugas_3.md → dokumen analisis + sequence diagram
Kode teknis untuk 3 modul di atas
File log prompting AI (.md)
Singkatnya: buat service kamu bisa login SSO, kirim audit SOAP, dan publish ke RabbitMQ — semuanya ke server dosen. Sudah tahu service kamu di kelompok itu mengerjakan bagian apa?

okeee, ini adakah github aku https://github.com/IAE-2026/102022400054_Azzahra-Afidah-Yusfa-Customer-Review.git aku tuh service customer reviiew dan ini penjelasan singkkatnya 4. Services Bisnis Customer Review

Order sudah berstatus delivered (pesanan diterima)
User membuka halaman review produk
User mengisi form review
User menekan tombol "Kirim Review"
Sistem memvalidasi input

Jika validasi gagal → tampilkan pesan error, user mengisi ulang (kembali ke poin 3)
Jika validasi berhasil → sistem menyimpan review ke database dengan data: product_id, order_id, user_id, rating, comment

Sistem menampilkan notifikasi "Review berhasil dikirim"
Review tampil di halaman produk
Endpoint nya

Resource: GET /api/v1/reviews/product/{product_id} Tampilkan review produk yang sudah ada
Action : POST /api/v1/reviews Simpan review baru ke database
Collection: GET /api/v1/reviews Ambil semua review nah coba sekarang buatkan step by steoo aku mengerjakan tugas 3 ini
Tampilkan lebih banyak
Jun 9
Oke dulu aku cek dulu repo kamu biar tahu kondisi kode yang sudah ada ya!


Mencari di web
Mencari di web
Repo-nya private jadi nggak bisa diakses langsung. Tapi nggak masalah — aku sudah punya semua info yang dibutuhkan dari ketentuan tugas + penjelasan service kamu. Langsung aku buatkan step by step-nya!

Step by Step Tugas 3 — Service Customer Review
🗺️ Gambaran Alur Kerja
Service Customer Review kamu
        ↓
[POST /api/v1/reviews] ← transaksi KRITIS ini yang akan diintegrasikan
        ↓              ↓                    ↓
   Simpan DB      Kirim SOAP Audit     Publish RabbitMQ
   (sudah ada)    (Modul 2)            (Modul 3)
        ↑
   Login pakai SSO dosen dulu
   (Modul 1)
STEP 1 — Tentukan Transaksi Kritis (untuk analisis_tugas_3.md)
Transaksi kritis kamu = POST /api/v1/reviews (Submit Review)

Kenapa ini kritis? Karena:

Mengubah state database (insert review baru) → state-changing transaction ✅
Terikat ke order_id dan user_id → ada implikasi bisnis (verifikasi order delivered)
Perlu diaudit karena menyangkut data transaksi pelanggan
Ini yang akan kamu pakai sebagai justifikasi di dokumen analisis.

STEP 2 — Buat analisis_tugas_3.md
Buat file ini di repo kamu. Isinya:

A. Justifikasi transaksi kritis — jelaskan kenapa POST /api/v1/reviews adalah transaksi kritis (state-changing, terikat order, perlu audit trail).

B. Sequence Diagram — pakai format Mermaid, kira-kira seperti ini:

RabbitMQ
SOAP_Audit
SSO_Dosen
ReviewService
RabbitMQ
SOAP_Audit
SSO_Dosen
ReviewService
User
POST /api/v1/reviews (rating, comment, order_id)
POST /api/v1/auth/token (login)
JWT Token
Validasi JWT & petakan role lokal
Simpan review ke DB
POST /soap/v1/audit (XML Envelope)
ReceiptNumber
POST /api/v1/messages/publish (JSON event)
200 OK
Review berhasil dikirim
User
STEP 3 — Modul 1: SSO (Login & JWT)
Tambahkan kode berikut ke service kamu. Ini untuk login ke SSO dosen dan mapping role.

python
# sso_client.py
import requests

SSO_URL = "https://iae-sso.virtualfri.id"

def login_sso(email: str, password: str):
    response = requests.post(f"{SSO_URL}/api/v1/auth/token", json={
        "email": email,
        "password": password
    })
    data = response.json()
    return data.get("access_token")  # ini JWT-nya

def get_jwks():
    response = requests.get(f"{SSO_URL}/api/v1/auth/jwks")
    return response.json()
Lalu di database lokal kamu, buat tabel user_roles:

sql
CREATE TABLE user_roles (
    user_id VARCHAR(100),
    email VARCHAR(100),
    role VARCHAR(50) DEFAULT 'customer'
);
Setelah dapat JWT → decode payload-nya → simpan/update user ke tabel user_roles lokal.

STEP 4 — Modul 2: SOAP Audit Client (BOBOT TERBESAR 40%)
Ini yang paling penting! Setiap kali ada review baru yang berhasil disimpan, kirim audit ke dosen.

python
# soap_client.py
import requests

SOAP_URL = "https://iae-sso.virtualfri.id/soap/v1/audit"

def kirim_soap_audit(jwt_token: str, order_id: str, product_id: str, rating: int, comment: str):
    payload = f"""<?xml version="1.0" encoding="UTF-8"?>
<soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/"
               xmlns:iae="http://iae.central/audit">
  <soap:Body>
    <iae:AuditRequest>
      <iae:TeamID>TEAM-XX</iae:TeamID>
      <iae:ActivityName>CustomerReviewSubmitted</iae:ActivityName>
      <iae:LogContent><![CDATA[{{"order_id":"{order_id}","product_id":"{product_id}","rating":{rating},"comment":"{comment}"}}]]></iae:LogContent>
    </iae:AuditRequest>
  </soap:Body>
</soap:Envelope>"""

    headers = {
        "Content-Type": "text/xml",
        "Authorization": f"Bearer {jwt_token}"
    }

    response = requests.post(SOAP_URL, data=payload, headers=headers)
    
    # Ambil ReceiptNumber dari response XML
    # Simpan ke DB atau log
    print(response.text)
    return response.text
⚠️ Ganti TEAM-XX dengan TeamID kelompok kamu yang sebenarnya.

STEP 5 — Modul 3: RabbitMQ Publisher
Setelah review tersimpan dan audit SOAP sukses, broadcast event-nya:

python
# rabbitmq_publisher.py
import requests

PUBLISH_URL = "https://iae-sso.virtualfri.id/api/v1/messages/publish"

def publish_review_event(jwt_token: str, order_id: str, product_id: str, user_id: str, rating: int):
    headers = {
        "Authorization": f"Bearer {jwt_token}",
        "Content-Type": "application/json"
    }

    payload = {
        "event": "review.submitted",
        "data": {
            "order_id": order_id,
            "product_id": product_id,
            "user_id": user_id,
            "rating": rating
        }
    }

    response = requests.post(PUBLISH_URL, json=payload, headers=headers)
    print(response.json())
    return response.status_code
STEP 6 — Gabungkan ke Endpoint POST /api/v1/reviews
Di endpoint utama kamu, panggil ketiga modul secara berurutan:

python
@app.post("/api/v1/reviews")
def submit_review(data: ReviewRequest):
    # 1. Simpan ke DB (sudah ada)
    review = save_review_to_db(data)

    # 2. Login SSO & dapat JWT
    token = login_sso("warga01@ktp.iae.id", "KtpDigital2026!")

    # 3. Kirim SOAP Audit
    kirim_soap_audit(token, data.order_id, data.product_id, data.rating, data.comment)

    # 4. Publish ke RabbitMQ
    publish_review_event(token, data.order_id, data.product_id, data.user_id, data.rating)

    return {"message": "Review berhasil dikirim"}
STEP 7 — Buat File Prompt Engineering Log
Buat file prompt_engineering_log.md di repo. Isinya rekap percakapan kamu dengan AI (Claude/ChatGPT) selama ngerjain tugas ini. Formatnya bebas, contoh:

markdown
# Prompt Engineering Log - Tugas 3 IAE

## Sesi 1 - 9 Juni 2026
**Prompt:** "Jelaskan cara membuat SOAP client di Python..."
**Hasil:** ...
**Insight yang didapat:** ...
Checklist Akhir ✅
 analisis_tugas_3.md → justifikasi + sequence diagram
 sso_client.py → login SSO, tangkap JWT, mapping role lokal
 soap_client.py → kirim XML audit, simpan ReceiptNumber
 rabbitmq_publisher.py → publish JSON event
 Semua dipanggil di POST /api/v1/reviews
 prompt_engineering_log.md → log AI prompting
 Push semua ke GitHub
Kamu pakai bahasa pemrograman apa untuk service-nya? Python (Flask/FastAPI)? Biar aku bisa sesuaikan kode-nya lebih spesifik!

okee aku udah sampe buat sequence diagram, selanjutnya gimanaa? coba jelasin step byvstepnya banget

Jun 9
Oke sebelum aku jelasin, biar lebih tepat arahannya — aku perlu tahu dulu kondisi service kamu sekarang!

P: Service kamu pakai bahasa/framework apa?
J: liat ss aku

P: Kondisi service kamu sekarang sudah sampai mana?
J: Baru ada endpoint review biasa (belum ada SSO/SOAP/RabbitMQ)

P: Kamu sudah tahu TeamID kelompok kamu berapa?
J: Sudah tahu

Jun 9
Kamu bilang "liat ss aku" tapi sepertinya screenshot-nya belum ke-upload nih! Coba kirim ulang gambarnya ya, biar aku bisa lihat framework/struktur kode kamu sekarang 😊

1781008529249_image.png
Jun 9
Oke ketahuan! Kamu pakai PHP - Laravel ya. Siap, aku sesuaikan semua kodenya ke Laravel!

Step by Step Lanjutan — Mulai dari Modul 1 (SSO)
STEP 3 — Modul 1: SSO Login & JWT
Tujuan: Service kamu bisa login ke SSO dosen, dapat JWT, lalu simpan user ke tabel roles lokal.

3.1 — Buat migration tabel user_roles
Jalankan di terminal:

bash
php artisan make:migration create_user_roles_table
Lalu buka file migration yang baru dibuat di database/migrations/, isi seperti ini:

php
public function up()
{
    Schema::create('user_roles', function (Blueprint $table) {
        $table->id();
        $table->string('email');
        $table->string('sso_user_id')->nullable();
        $table->string('role')->default('customer');
        $table->string('jwt_token')->nullable();
        $table->timestamps();
    });
}
Lalu jalankan:

bash
php artisan migrate
3.2 — Buat SsoService
bash
php artisan make:service SsoService
Kalau command itu error, buat manual: buat folder app/Services/ lalu buat file SsoService.php

Isi file app/Services/SsoService.php:

php
<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class SsoService
{
    protected $baseUrl = 'https://iae-sso.virtualfri.id';

    public function login(string $email, string $password): ?string
    {
        $response = Http::post("{$this->baseUrl}/api/v1/auth/token", [
            'email'    => $email,
            'password' => $password,
        ]);

        if ($response->successful()) {
            return $response->json('access_token');
        }

        return null;
    }

    public function decodeJwtPayload(string $token): array
    {
        // JWT format: header.payload.signature
        $parts = explode('.', $token);
        $payload = base64_decode(str_pad(
            strtr($parts[1], '-_', '+/'),
            strlen($parts[1]) % 4,
            '=',
            STR_PAD_RIGHT
        ));
        return json_decode($payload, true);
    }
}
3.3 — Simpan user ke tabel lokal
Di ReviewController.php kamu (atau buat baru), tambahkan logic ini waktu review di-submit:

php
use App\Services\SsoService;
use App\Models\UserRole;

$sso = new SsoService();
$token = $sso->login('warga01@ktp.iae.id', 'KtpDigital2026!');

if ($token) {
    $payload = $sso->decodeJwtPayload($token);
    
    UserRole::updateOrCreate(
        ['email' => $payload['email'] ?? 'warga01@ktp.iae.id'],
        [
            'sso_user_id' => $payload['sub'] ?? null,
            'role'        => 'customer',
            'jwt_token'   => $token,
        ]
    );
}
Jangan lupa buat model-nya:

bash
php artisan make:model UserRole
STEP 4 — Modul 2: SOAP Audit (Bobot 40% ⚠️)
Tujuan: Setelah review tersimpan, kirim laporan XML ke sistem audit dosen dan simpan ReceiptNumber-nya.

4.1 — Buat tabel untuk simpan ReceiptNumber
bash
php artisan make:migration create_audit_logs_table
php
public function up()
{
    Schema::create('audit_logs', function (Blueprint $table) {
        $table->id();
        $table->string('receipt_number')->nullable();
        $table->string('activity_name');
        $table->text('log_content');
        $table->timestamps();
    });
}
bash
php artisan migrate
4.2 — Buat SoapAuditService
Buat file app/Services/SoapAuditService.php:

php
<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use App\Models\AuditLog;

class SoapAuditService
{
    protected $soapUrl = 'https://iae-sso.virtualfri.id/soap/v1/audit';
    protected $teamId  = 'TEAM-XX'; // ← ganti dengan TeamID kelompok kamu

    public function sendAudit(string $jwtToken, array $reviewData): ?string
    {
        $logContent = json_encode([
            'order_id'   => $reviewData['order_id'],
            'product_id' => $reviewData['product_id'],
            'user_id'    => $reviewData['user_id'],
            'rating'     => $reviewData['rating'],
            'comment'    => $reviewData['comment'],
        ]);

        $xmlPayload = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/"
               xmlns:iae="http://iae.central/audit">
  <soap:Body>
    <iae:AuditRequest>
      <iae:TeamID>{$this->teamId}</iae:TeamID>
      <iae:ActivityName>CustomerReviewSubmitted</iae:ActivityName>
      <iae:LogContent><![CDATA[{$logContent}]]></iae:LogContent>
    </iae:AuditRequest>
  </soap:Body>
</soap:Envelope>
XML;

        $response = Http::withHeaders([
            'Content-Type'  => 'text/xml',
            'Authorization' => "Bearer {$jwtToken}",
        ])->withBody($xmlPayload, 'text/xml')->post($this->soapUrl);

        // Parse ReceiptNumber dari response XML
        $receiptNumber = null;
        if ($response->successful()) {
            preg_match('/<iae:ReceiptNumber>(.*?)<\/iae:ReceiptNumber>/', $response->body(), $matches);
            $receiptNumber = $matches[1] ?? null;

            // Simpan ke DB
            AuditLog::create([
                'receipt_number' => $receiptNumber,
                'activity_name'  => 'CustomerReviewSubmitted',
                'log_content'    => $logContent,
            ]);
        }

        return $receiptNumber;
    }
}
Buat model-nya:

bash
php artisan make:model AuditLog
STEP 5 — Modul 3: RabbitMQ Publisher
Buat file app/Services/RabbitMQService.php:

php
<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class RabbitMQService
{
    protected $publishUrl = 'https://iae-sso.virtualfri.id/api/v1/messages/publish';

    public function publishReviewEvent(string $jwtToken, array $reviewData): bool
    {
        $response = Http::withHeaders([
            'Authorization' => "Bearer {$jwtToken}",
            'Content-Type'  => 'application/json',
        ])->post($this->publishUrl, [
            'event' => 'review.submitted',
            'data'  => [
                'order_id'   => $reviewData['order_id'],
                'product_id' => $reviewData['product_id'],
                'user_id'    => $reviewData['user_id'],
                'rating'     => $reviewData['rating'],
            ],
        ]);

        return $response->successful();
    }
}
STEP 6 — Gabungkan Semua di ReviewController
Buka ReviewController.php kamu, di method store (POST /api/v1/reviews), tambahkan:

php
use App\Services\SsoService;
use App\Services\SoapAuditService;
use App\Services\RabbitMQService;

public function store(Request $request)
{
    // Validasi input (sudah ada sebelumnya)
    $validated = $request->validate([
        'product_id' => 'required',
        'order_id'   => 'required',
        'user_id'    => 'required',
        'rating'     => 'required|integer|min:1|max:5',
        'comment'    => 'required|string',
    ]);

    // 1. Simpan review ke DB (sudah ada)
    $review = Review::create($validated);

    // 2. Login SSO & dapat JWT
    $sso   = new SsoService();
    $token = $sso->login('warga01@ktp.iae.id', 'KtpDigital2026!');

    if ($token) {
        // 3. Kirim SOAP Audit
        $soap    = new SoapAuditService();
        $receipt = $soap->sendAudit($token, $validated);

        // 4. Publish ke RabbitMQ
        $mq = new RabbitMQService();
        $mq->publishReviewEvent($token, $validated);
    }

    return response()->json([
        'message'        => 'Review berhasil dikirim',
        'receipt_number' => $receipt ?? null,
    ], 201);
}
STEP 7 — Test Satu per Satu
Pakai Postman atau curl, test secara urutan:

1. Test SSO dulu → pastikan dapat token
2. Test SOAP → pastikan dapat ReceiptNumber di response
3. Test RabbitMQ → pastikan status 200
4. Test POST /api/v1/reviews → semua harus jalan sekaligus
STEP 8 — Update prompt_engineering_log.md
Tambahkan sesi percakapan kamu dengan Claude ini ke file AI_PROMPTING_LOG.md yang sudah ada di repo kamu (kelihatan di screenshot). Format bebas, yang penting ada prompt & insight-nya.

Mau mulai dari mana dulu? Kalau bingung di bagian SOAP atau struktur ReviewController yang sudah ada sekarang, share aja kodenya biar aku bantu sesuaikan! 🚀

URL dan Akun Tugas IAE.pdf
pdf

wait, kamu sudah menyesuaikan dengan ini  belum? kalo belum sesuaikan dong dan coba tolong jelasin ke aku syep by steonya banget

Jun 9
Sudah aku cek dokumennya! Ada beberapa hal yang perlu disesuaikan. Mari kita mulai dari awal dengan step yang benar.

Yang Perlu Disesuaikan dari Dokumen
Ada 2 cara login ke SSO dosen:

M2M (Machine to Machine): pakai api_key → untuk service-to-service
User login: pakai email + password → untuk simulasi user
Untuk Tugas 3, kamu pakai keduanya — M2M untuk dapat token service, User login untuk simulasi warga.

STEP 3 — Modul 1: SSO
3.1 — Buat migration dulu
Buka terminal di VS Code kamu, ketik:

bash
php artisan make:migration create_user_roles_table
Cari file baru di database/migrations/, buka, lalu ganti isi fungsi up() dengan ini:

php
public function up()
{
    Schema::create('user_roles', function (Blueprint $table) {
        $table->id();
        $table->string('email')->unique();
        $table->string('sso_user_id')->nullable();
        $table->string('role')->default('customer');
        $table->text('jwt_token')->nullable();
        $table->timestamps();
    });
}
Simpan, lalu jalankan:

bash
php artisan migrate
3.2 — Buat Model UserRole
bash
php artisan make:model UserRole
Buka app/Models/UserRole.php, tambahkan $fillable:

php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserRole extends Model
{
    protected $fillable = [
        'email',
        'sso_user_id',
        'role',
        'jwt_token',
    ];
}
3.3 — Buat SsoService
Buat folder dulu kalau belum ada:

bash
mkdir app/Services
Lalu buat file app/Services/SsoService.php dan isi dengan ini:

php
<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use App\Models\UserRole;

class SsoService
{
    protected $baseUrl = 'https://iae-sso.virtualfri.id';

    // Login sebagai USER (warga) — sesuai dokumen dosen
    public function loginAsUser(string $email, string $password): ?string
    {
        $response = Http::post("{$this->baseUrl}/api/v1/auth/token", [
            'email'    => $email,
            'password' => $password,
        ]);

        if ($response->successful()) {
            $token = $response->json('access_token');

            // Decode payload JWT untuk ambil data user
            $payload = $this->decodeJwtPayload($token);

            // Simpan/update user ke tabel lokal
            UserRole::updateOrCreate(
                ['email' => $email],
                [
                    'sso_user_id' => $payload['sub'] ?? null,
                    'role'        => 'customer', // role lokal default
                    'jwt_token'   => $token,
                ]
            );

            return $token;
        }

        return null;
    }

    // Login sebagai M2M (service) — pakai api_key
    public function loginAsM2M(): ?string
    {
        $response = Http::post("{$this->baseUrl}/api/v1/auth/token", [
            'api_key' => 'KEY-MHS-01', // sesuai dokumen dosen
        ]);

        if ($response->successful()) {
            return $response->json('access_token');
        }

        return null;
    }

    // Decode JWT payload (tanpa library tambahan)
    public function decodeJwtPayload(string $token): array
    {
        $parts   = explode('.', $token);
        $payload = base64_decode(str_pad(
            strtr($parts[1], '-_', '+/'),
            strlen($parts[1]) % 4,
            '=',
            STR_PAD_RIGHT
        ));

        return json_decode($payload, true) ?? [];
    }
}
💡 Kenapa ada dua method?

loginAsUser → untuk simulasi customer yang submit review (sesuai skenario bisnis)
loginAsM2M → untuk keperluan service-to-service kalau dibutuhkan nanti di Tugas Besar
STEP 4 — Modul 2: SOAP Audit
4.1 — Buat migration audit_logs
bash
php artisan make:migration create_audit_logs_table
Isi fungsi up():

php
public function up()
{
    Schema::create('audit_logs', function (Blueprint $table) {
        $table->id();
        $table->string('receipt_number')->nullable(); // dari response dosen
        $table->string('activity_name');
        $table->text('log_content');
        $table->timestamps();
    });
}
bash
php artisan migrate
4.2 — Buat Model AuditLog
bash
php artisan make:model AuditLog
php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    protected $fillable = [
        'receipt_number',
        'activity_name',
        'log_content',
    ];
}
4.3 — Buat SoapAuditService
Buat file app/Services/SoapAuditService.php:

php
<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use App\Models\AuditLog;

class SoapAuditService
{
    // Sesuai dokumen dosen
    protected $soapUrl = 'https://iae-sso.virtualfri.id/soap/v1/audit';
    protected $teamId  = 'TEAM-XX'; // ← GANTI dengan TeamID kelompok kamu

    public function sendReviewAudit(string $jwtToken, array $reviewData): ?string
    {
        // Siapkan isi LogContent dalam format JSON (sesuai contoh di dokumen)
        $logContent = json_encode([
            'order_id'   => $reviewData['order_id'],
            'product_id' => $reviewData['product_id'],
            'user_id'    => $reviewData['user_id'],
            'rating'     => $reviewData['rating'],
            'comment'    => $reviewData['comment'],
        ]);

        // Buat XML Envelope — struktur PERSIS seperti sample di dokumen dosen
        $xmlPayload = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/"
               xmlns:iae="http://iae.central/audit">
  <soap:Body>
    <iae:AuditRequest>
      <iae:TeamID>{$this->teamId}</iae:TeamID>
      <iae:ActivityName>CustomerReviewSubmitted</iae:ActivityName>
      <iae:LogContent><![CDATA[{$logContent}]]></iae:LogContent>
    </iae:AuditRequest>
  </soap:Body>
</soap:Envelope>
XML;

        // Kirim ke server dosen — pakai Bearer token (sesuai kolom Auth di dokumen)
        $response = Http::withHeaders([
            'Content-Type'  => 'text/xml',
            'Authorization' => "Bearer {$jwtToken}",
        ])->withBody($xmlPayload, 'text/xml')->post($this->soapUrl);

        $receiptNumber = null;

        if ($response->successful()) {
            // Ambil ReceiptNumber dari response XML
            // Contoh response: <iae:ReceiptNumber>IAE-LOG-2026-8891A7BC</iae:ReceiptNumber>
            preg_match(
                '/<iae:ReceiptNumber>(.*?)<\/iae:ReceiptNumber>/',
                $response->body(),
                $matches
            );
            $receiptNumber = $matches[1] ?? null;

            // Simpan ke DB sebagai bukti audit berhasil
            AuditLog::create([
                'receipt_number' => $receiptNumber,
                'activity_name'  => 'CustomerReviewSubmitted',
                'log_content'    => $logContent,
            ]);
        }

        return $receiptNumber;
    }
}
STEP 5 — Modul 3: RabbitMQ Publisher
Buat file app/Services/RabbitMQService.php:

php
<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class RabbitMQService
{
    // Sesuai dokumen dosen — endpoint publish
    protected $publishUrl = 'https://iae-sso.virtualfri.id/api/v1/messages/publish';

    public function publishReviewEvent(string $jwtToken, array $reviewData): bool
    {
        $response = Http::withHeaders([
            'Authorization' => "Bearer {$jwtToken}",
            'Content-Type'  => 'application/json',
        ])->post($this->publishUrl, [
            // Dikirim ke iae.central.exchange (sesuai dokumen)
            'event'   => 'review.submitted',
            'data'    => [
                'order_id'   => $reviewData['order_id'],
                'product_id' => $reviewData['product_id'],
                'user_id'    => $reviewData['user_id'],
                'rating'     => $reviewData['rating'],
            ],
        ]);

        return $response->successful();
    }
}
STEP 6 — Gabungkan di ReviewController
Buka ReviewController.php kamu, cari method store, lalu tambahkan bagian integrasi:

php
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Review;
use App\Services\SsoService;
use App\Services\SoapAuditService;
use App\Services\RabbitMQService;

class ReviewController extends Controller
{
    public function store(Request $request)
    {
        // Validasi input
        $validated = $request->validate([
            'product_id' => 'required',
            'order_id'   => 'required',
            'user_id'    => 'required',
            'rating'     => 'required|integer|min:1|max:5',
            'comment'    => 'required|string',
        ]);

        // 1. Simpan review ke DB lokal
        $review = Review::create($validated);

        // 2. Login ke SSO dosen sebagai user warga
        //    Sesuai dokumen: POST /api/v1/auth/token dengan email & password
        $sso   = new SsoService();
        $token = $sso->loginAsUser('warga01@ktp.iae.id', 'KtpDigital2026!');

        $receiptNumber = null;
        $mqSuccess     = false;

        if ($token) {
            // 3. Kirim SOAP Audit ke server dosen
            $soap          = new SoapAuditService();
            $receiptNumber = $soap->sendReviewAudit($token, $validated);

            // 4. Publish event ke RabbitMQ dosen
            $mq        = new RabbitMQService();
            $mqSuccess = $mq->publishReviewEvent($token, $validated);
        }

        return response()->json([
            'message'        => 'Review berhasil dikirim',
            'review_id'      => $review->id,
            'receipt_number' => $receiptNumber, // bukti SOAP audit berhasil
            'mq_published'   => $mqSuccess,
        ], 201);
    }
}
STEP 7 — Test Pakai Postman
Test satu per satu secara urutan:

Test 1 — Cek server dosen hidup:

GET https://iae-sso.virtualfri.id/health
Harusnya dapat response JSON sukses.

Test 2 — Cek SSO login:

POST https://iae-sso.virtualfri.id/api/v1/auth/token
Body JSON:
{
  "email": "warga01@ktp.iae.id",
  "password": "KtpDigital2026!"
}
Harusnya dapat access_token.

Test 3 — Test endpoint review kamu:

POST http://localhost:8000/api/v1/reviews
Body JSON:
{
  "product_id": "PROD-001",
  "order_id": "ORD-001",
  "user_id": "USR-001",
  "rating": 5,
  "comment": "Produk bagus!"
}
Response yang diharapkan:

json
{
  "message": "Review berhasil dikirim",
  "review_id": 1,
  "receipt_number": "IAE-LOG-2026-XXXXXXXX",
  "mq_published": true
}
Kalau receipt_number muncul → SOAP berhasil ✅
Kalau mq_published: true → RabbitMQ berhasil ✅

STEP 8 — Update AI_PROMPTING_LOG.md
File ini sudah ada di repo kamu (kelihatan di screenshot)! Tinggal tambahkan sesi ini. Contoh isinya:

markdown
## Sesi 2 - 9 Juni 2026
**Topik:** Implementasi SSO, SOAP Audit, dan RabbitMQ Publisher

**Prompt yang digunakan:**
- "Jelaskan step by step cara mengintegrasikan SSO dosen ke Laravel service Customer Review"
- "Bagaimana cara membuat SOAP client di Laravel tanpa library tambahan?"

**Insight yang didapat:**
- SOAP bisa dikirim pakai Http::withBody() di Laravel tanpa install package zerodayz/laravel-soap
- JWT bisa di-decode manual pakai base64_decode tanpa package firebase/jwt
- Ada 2 mode login SSO: M2M (api_key) dan User (email+password)
Sekarang mulai dari STEP 3.1 ya — jalankan migration dulu. Kalau ada error pas migrate atau pas test Postman, screenshot dan kirim ke sini! 🚀

2026_06_09_123605_create_user_roles_table .......................................................................................... 401.93ms FAIL
   Illuminate\Database\QueryException 
  SQLSTATE[HY000]: General error: 1 duplicate column name: created_at (Connection: sqlite, Database: C:\Users\Azzahra Afidah Yusfa\customer-review-service\database\database.sqlite, SQL: create table "user_roles" ("id" integer primary key autoincrement not null, "created_at" datetime, "updated_at" datetime, "email" varchar not null, "sso_user_id" varchar, "role" varchar not null default 'customer', "jwt_token" text, "created_at" datetime, "updated_at" datetime))
  at vendor\laravel\framework\src\Illuminate\Database\Connection.php:838
    834▕             $exceptionType = $this->isUniqueConstraintError($e)
    835▕                 ? UniqueConstraintViolationException::class
    836▕                 : QueryException::class;
    837▕ 
  ➜ 838▕             throw new $exceptionType(
    839▕                 $this->getNameWithReadWriteType(),
    840▕                 $query,
    841▕                 $this->prepareBindings($bindings),
    842▕                 $e,
  1   vendor\laravel\framework\src\Illuminate\Database\Connection.php:578
      PDOException::("SQLSTATE[HY000]: General error: 1 duplicate column name: created_at")
  2   vendor\laravel\framework\src\Illuminate\Database\Connection.php:578
      PDO::prepare("create table "user_roles" ("id" integer primary key autoincrement not null, "created_at" datetime, "updated_at" datetime, "email" varchar not null, "sso_user_id" varchar, "role" varchar not null default 'customer', "jwt_token" text, "created_at" datetime, "updated_at" datetime)")
PS C:\Users\Azzahra Afidah Yusfa\customer-review-service>

Tampilkan lebih banyak
Jun 9
Error ini gampang fix-nya! Masalahnya created_at dan updated_at muncul dua kali karena Laravel otomatis tambahkan timestamps() tapi di migration kamu ada duplikat.

Buka file migration create_user_roles_table di database/migrations/, cari bagian up() dan pastikan isinya seperti ini (hapus kalau ada $table->timestamps() yang dobel):

php
public function up()
{
    Schema::create('user_roles', function (Blueprint $table) {
        $table->id();
        $table->string('email')->unique();
        $table->string('sso_user_id')->nullable();
        $table->string('role')->default('customer');
        $table->text('jwt_token')->nullable();
        $table->timestamps(); // ← cukup SATU ini saja
    });
}
Setelah diperbaiki, jalankan:

bash
php artisan migrate
Kalau masih error karena "table already exists", jalankan ini dulu:

bash
php artisan migrate:rollback
php artisan migrate
Coba lagi dan kirim hasilnya ya! 🙂

aku sampe mau buat ssoservice tappi PS C:\Users\Azzahra Afidah Yusfa\customer-review-service> mkdir app/Services
mkdir : An item with the specified name C:\Users\Azzahra Afidah Yusfa\customer-review-service\app\Services already exists.
At line:1 char:1
+ mkdir app/Services
+ ~~~~~~~~~~~~~~~~~~
    + CategoryInfo          : ResourceExists: (C:\Users\Azzahr...ce\app\Services:String) [New-Item], IOException
    + FullyQualifiedErrorId : DirectoryExist,Microsoft.PowerShell.Commands.NewItemCommand

PS C:\Users\Azzahra Afidah Yusfa\customer-review-service>

Jun 9
Itu bukan error! Itu artinya folder app/Services sudah ada — jadi kamu nggak perlu buat lagi. Langsung aja lanjut ke langkah berikutnya.

Langsung buat file SsoService.php di dalam folder yang sudah ada itu. Ketik di terminal:

bash
New-Item app/Services/SsoService.php
Lalu buka file tersebut di VS Code dan isi dengan kode SsoService yang sudah aku kasih di step sebelumnya. Setelah itu kirim screenshot hasilnya ya! 🙂

PS C:\Users\Azzahra Afidah Yusfa\customer-review-service> New-Item app/Services/SsoService.php
    Directory: C:\Users\Azzahra Afidah Yusfa\customer-review-service\app\Services
Mode                 LastWriteTime         Length Name                                                                                                   
----                 -------------         ------ ----                                                                                                   
-a----          6/9/2026   7:44 PM              0 SsoService.php                                                                                         
PS C:\Users\Azzahra Afidah Yusfa\customer-review-service>

Jun 9
File berhasil dibuat! Sekarang buka file SsoService.php di VS Code, lalu copy-paste kode ini ke dalamnya:

php
<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use App\Models\UserRole;

class SsoService
{
    protected $baseUrl = 'https://iae-sso.virtualfri.id';

    // Login sebagai USER (warga) — sesuai dokumen dosen
    public function loginAsUser(string $email, string $password): ?string
    {
        $response = Http::post("{$this->baseUrl}/api/v1/auth/token", [
            'email'    => $email,
            'password' => $password,
        ]);

        if ($response->successful()) {
            $token = $response->json('access_token');

            // Decode payload JWT untuk ambil data user
            $payload = $this->decodeJwtPayload($token);

            // Simpan/update user ke tabel lokal
            UserRole::updateOrCreate(
                ['email' => $email],
                [
                    'sso_user_id' => $payload['sub'] ?? null,
                    'role'        => 'customer',
                    'jwt_token'   => $token,
                ]
            );

            return $token;
        }

        return null;
    }

    // Login sebagai M2M (service) — pakai api_key
    public function loginAsM2M(): ?string
    {
        $response = Http::post("{$this->baseUrl}/api/v1/auth/token", [
            'api_key' => 'KEY-MHS-01',
        ]);

        if ($response->successful()) {
            return $response->json('access_token');
        }

        return null;
    }

    // Decode JWT payload tanpa library tambahan
    public function decodeJwtPayload(string $token): array
    {
        $parts   = explode('.', $token);
        $payload = base64_decode(str_pad(
            strtr($parts[1], '-_', '+/'),
            strlen($parts[1]) % 4,
            '=',
            STR_PAD_RIGHT
        ));

        return json_decode($payload, true) ?? [];
    }
}
Setelah di-paste, Ctrl+S untuk save. Lalu kirim screenshot file-nya biar aku konfirmasi sudah benar sebelum lanjut ke SOAP! 🙂

PS C:\Users\Azzahra Afidah Yusfa\customer-review-service> php artisan make:app/Services/SoapAuditService.php
   ERROR  Command "make:app/Services/SoapAuditService. Did you mean one of these?  
  ⇂ make:cache-table  
  ⇂ make:cast  
  ⇂ make:channel  
  ⇂ make:class  
  ⇂ make:command  
  ⇂ make:component  
  ⇂ make:config  
  ⇂ make:controller  
  ⇂ make:enum  
  ⇂ make:event  
  ⇂ make:exception  
  ⇂ make:factory  
  ⇂ make:interface  
  ⇂ make:job  
  ⇂ make:job-middleware  
  ⇂ make:listener  
  ⇂ make:mail  
  ⇂ make:middleware  
  ⇂ make:migration  
  ⇂ make:model  
  ⇂ make:notification  
  ⇂ make:notifications-table  
  ⇂ make:observer  
  ⇂ make:policy  
  ⇂ make:provider  
  ⇂ make:queue-batches-table  
  ⇂ make:queue-failed-table  
  ⇂ make:queue-table  
  ⇂ make:request  
  ⇂ make:resource  
  ⇂ make:rule  
  ⇂ make:scope  
  ⇂ make:seeder  
  ⇂ make:session-table  
  ⇂ make:test  
  ⇂ make:trait  
  ⇂ make:view  
PS C:\Users\Azzahra Afidah Yusfa\customer-review-service>

Tampilkan lebih banyak
Jun 9
Hehe salah command! Untuk file Service, nggak pakai php artisan make: — langsung buat file aja seperti tadi. Ketik ini:

bash
New-Item app/Services/SoapAuditService.php
bash
New-Item app/Services/RabbitMQService.php
Sekalian buat dua-duanya sekarang ya biar efisien! Setelah itu buka kedua file di VS Code dan paste kode yang sudah aku kasih sebelumnya. 🙂

STEP 6 — Gabungkan di ReviewController
Buka ReviewController.phpini tuh ada dimana

Jun 9
Cek di app/Http/Controllers/ReviewController.php — sesuai struktur Laravel standar.

Lihat di panel kiri VS Code kamu, klik folder app → Http → Controllers → cari file ReviewController.php seperti ini:

app/
└── Http/
    └── Controllers/
        ├── ReviewController.php  ← ini yang dicari
        └── SwaggerInfo.php       (yang tadi kelihatan di screenshot)
Kalau ketemu, buka dan screenshot isinya ke sini ya biar aku bantu sesuaikan kodenya dengan yang sudah ada! 🙂

<?php

namespace App\Http\Controllers;

use App\Models\Review;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class ReviewController extends Controller
{
    #[OA\Get(
        path: "/api/v1/reviews",
        summary: "Ambil semua review",
        security: [["ApiKeyAuth" => []]],
        responses: [
            new OA\Response(response: 200, description: "Success"),
            new OA\Response(response: 401, description: "Unauthorized")
        ]
    )]
    public function index()
    {
        $reviews = Review::all();
        return response()->json([
            'success' => true,
            'data' => $reviews
        ], 200);
    }

    #[OA\Get(
        path: "/api/v1/reviews/product/{product_id}",
        summary: "Ambil review berdasarkan produk",
        security: [["ApiKeyAuth" => []]],
        parameters: [
            new OA\Parameter(name: "product_id", in: "path", required: true, schema: new OA\Schema(type: "string"))
        ],
        responses: [
            new OA\Response(response: 200, description: "Success"),
            new OA\Response(response: 404, description: "Not Found"),
            new OA\Response(response: 401, description: "Unauthorized")
        ]
    )]
    public function byProduct($product_id)
    {
        $reviews = Review::where('product_id', $product_id)->get();

        if ($reviews->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No reviews found for this product'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $reviews
        ], 200);
    }

    #[OA\Post(
        path: "/api/v1/reviews",
        summary: "Simpan review baru",
        security: [["ApiKeyAuth" => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["product_id", "reviewer_name", "rating", "comment"],
                properties: [
                    new OA\Property(property: "product_id", type: "string", example: "PROD-001"),
                    new OA\Property(property: "reviewer_name", type: "string", example: "Azzahra"),
                    new OA\Property(property: "rating", type: "integer", example: 5),
                    new OA\Property(property: "comment", type: "string", example: "Produk bagus!")
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: "Created"),
            new OA\Response(response: 401, description: "Unauthorized")
        ]
    )]
    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_id'    => 'required|string',
            'reviewer_name' => 'required|string',
            'rating'        => 'required|integer|min:1|max:5',
            'comment'       => 'required|string',
        ]);

        $review = Review::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Review created successfully',
            'data' => $review
        ], 201);
    }
}
Tampilkan lebih banyak
Jun 9
Oke ketahuan strukturnya! Field di service kamu ada reviewer_name (bukan user_id dan order_id). Jadi aku sesuaikan. Ganti seluruh isi ReviewController.php dengan ini:

php
<?php

namespace App\Http\Controllers;

use App\Models\Review;
use App\Services\SsoService;
use App\Services\SoapAuditService;
use App\Services\RabbitMQService;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class ReviewController extends Controller
{
    #[OA\Get(
        path: "/api/v1/reviews",
        summary: "Ambil semua review",
        security: [["ApiKeyAuth" => []]],
        responses: [
            new OA\Response(response: 200, description: "Success"),
            new OA\Response(response: 401, description: "Unauthorized")
        ]
    )]
    public function index()
    {
        $reviews = Review::all();
        return response()->json([
            'success' => true,
            'data' => $reviews
        ], 200);
    }

    #[OA\Get(
        path: "/api/v1/reviews/product/{product_id}",
        summary: "Ambil review berdasarkan produk",
        security: [["ApiKeyAuth" => []]],
        parameters: [
            new OA\Parameter(name: "product_id", in: "path", required: true, schema: new OA\Schema(type: "string"))
        ],
        responses: [
            new OA\Response(response: 200, description: "Success"),
            new OA\Response(response: 404, description: "Not Found"),
            new OA\Response(response: 401, description: "Unauthorized")
        ]
    )]
    public function byProduct($product_id)
    {
        $reviews = Review::where('product_id', $product_id)->get();

        if ($reviews->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No reviews found for this product'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $reviews
        ], 200);
    }

    #[OA\Post(
        path: "/api/v1/reviews",
        summary: "Simpan review baru",
        security: [["ApiKeyAuth" => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["product_id", "reviewer_name", "rating", "comment"],
                properties: [
                    new OA\Property(property: "product_id", type: "string", example: "PROD-001"),
                    new OA\Property(property: "reviewer_name", type: "string", example: "Azzahra"),
                    new OA\Property(property: "rating", type: "integer", example: 5),
                    new OA\Property(property: "comment", type: "string", example: "Produk bagus!")
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: "Created"),
            new OA\Response(response: 401, description: "Unauthorized")
        ]
    )]
    public function store(Request $request)
    {
        // Validasi input — sesuai field asli kamu
        $validated = $request->validate([
            'product_id'    => 'required|string',
            'reviewer_name' => 'required|string',
            'rating'        => 'required|integer|min:1|max:5',
            'comment'       => 'required|string',
        ]);

        // 1. Simpan review ke DB lokal (sama seperti sebelumnya)
        $review = Review::create($validated);

        // 2. Login SSO dosen pakai akun warga
        $sso   = new SsoService();
        $token = $sso->loginAsUser('warga01@ktp.iae.id', 'KtpDigital2026!');

        $receiptNumber = null;
        $mqSuccess     = false;

        if ($token) {
            // 3. Kirim SOAP Audit ke server dosen
            $soap = new SoapAuditService();
            $receiptNumber = $soap->sendReviewAudit($token, [
                'product_id'    => $validated['product_id'],
                'reviewer_name' => $validated['reviewer_name'],
                'rating'        => $validated['rating'],
                'comment'       => $validated['comment'],
            ]);

            // 4. Publish event ke RabbitMQ dosen
            $mq = new RabbitMQService();
            $mqSuccess = $mq->publishReviewEvent($token, [
                'product_id'    => $validated['product_id'],
                'reviewer_name' => $validated['reviewer_name'],
                'rating'        => $validated['rating'],
            ]);
        }

        return response()->json([
            'success'        => true,
            'message'        => 'Review created successfully',
            'data'           => $review,
            'receipt_number' => $receiptNumber, // bukti SOAP berhasil
            'mq_published'   => $mqSuccess,     // bukti RabbitMQ berhasil
        ], 201);
    }
}
Perlu juga update SoapAuditService sedikit karena field-nya beda. Buka app/Services/SoapAuditService.php, ganti bagian $logContent di method sendReviewAudit jadi:

php
$logContent = json_encode([
    'product_id'    => $reviewData['product_id'],
    'reviewer_name' => $reviewData['reviewer_name'],
    'rating'        => $reviewData['rating'],
    'comment'       => $reviewData['comment'],
]);
Dan RabbitMQService juga, ganti bagian data di method publishReviewEvent:

php
'data' => [
    'product_id'    => $reviewData['product_id'],
    'reviewer_name' => $reviewData['reviewer_name'],
    'rating'        => $reviewData['rating'],
],
Setelah semua disave, lanjut ke STEP 7 — Test pakai Postman. Jalankan dulu servernya:

bash
php artisan serve
Lalu test di Postman dengan kirim POST ke http://localhost:8000/api/v1/reviews. Screenshot hasilnya ya! 🙂

1781244606063_image.png
coba ini kenapa yah

1:10 PM
Error 401 Unauthorized - Invalid or missing API Key — ini karena service kamu punya middleware yang butuh header X-IAE-KEY, tapi value KEY-MHS-24 yang kamu pakai salah atau tidak dikenali.

Cek dulu API Key yang valid
Buka file app/Http/Middleware/ApiKeyMiddleware.php di VS Code, screenshot isinya ke sini. Nanti kita lihat API key apa yang seharusnya dipakai.

Atau cek juga file .env kamu, kemungkinan ada variable seperti:

API_KEY=xxxxx
Kalau ketemu, ganti value di Postman header X-IAE-KEY dengan API key yang ada di sana. 🙂


customer-review-service.zip
zip

ini adalah file codingan aku, coba deh check mana yang miss

1:11 PM

Menjalankan 4 perintah, melihat file
Menjalankan 4 perintah, melihat file
Ketemu masalahnya! Ada 2 hal yang miss:

🔴 Masalah 1 — API Key di Postman Salah
Di .env kamu, API key yang benar adalah:

IAE_API_KEY=KEY-MHS-07
Tapi di Postman kamu isi KEY-MHS-24 — itu salah! Ganti jadi KEY-MHS-07.

🔴 Masalah 2 — SSO Email Salah Format
Di .env kamu ada:

SSO_EMAIL=warga07@tp.iae.id
Tapi format yang benar sesuai dokumen dosen adalah:

warga07@ktp.iae.id
Ada yang kelewat ktp. — perlu ditambahkan!

Fix Keduanya Sekarang
Fix 1 — .env, buka file .env dan ubah baris SSO_EMAIL:

SSO_EMAIL=warga07@ktp.iae.id
Fix 2 — SsoService.php, supaya pakai nilai dari .env bukan hardcode. Buka app/Services/SsoService.php, ubah method loginAsUser yang dipanggil di controller. Di ReviewController.php ubah bagian login jadi:

php
$token = $sso->loginAsUser(
    env('SSO_EMAIL', 'warga07@ktp.iae.id'),
    env('SSO_PASSWORD', 'KtpDigital2026!')
);
Dan di loginAsM2M dalam SsoService.php, ubah hardcode api_key jadi:

php
'api_key' => env('IAE_API_KEY', 'KEY-MHS-07'),
Fix 3 — Postman, ganti header X-IAE-KEY valuenya dari KEY-MHS-24 → KEY-MHS-07

Setelah semua difix, coba Send lagi di Postman. Jangan lupa isi Body dulu ya — klik tab Body → raw → JSON, isi:

json
{
    "product_id": "PROD-001",
    "reviewer_name": "Azzahra",
    "rating": 5,
    "comment": "Produk bagus!"
}
Screenshot hasilnya ya! 🙂

nah aku tuh yg benr harusya     
akun Warga: warga22@ktp.iae.id & API-KEY: KEY-MHS-124

1:12 PM
Oke! Jadi yang benar untuk kamu:

Email: warga22@ktp.iae.id
API Key: KEY-MHS-124
Fix di 2 tempat:
1. Buka .env, ubah jadi:

IAE_API_KEY=KEY-MHS-124
SSO_EMAIL=warga22@ktp.iae.id
SSO_PASSWORD=KtpDigital2026!
2. Buka SsoService.php, pastikan loginAsM2M pakai env:

php
'api_key' => env('IAE_API_KEY', 'KEY-MHS-124'),
3. Di Postman, ganti header X-IAE-KEY valuenya jadi KEY-MHS-124

4. Buka ReviewController.php, pastikan bagian login SSO pakai env:

php
$token = $sso->loginAsUser(
    env('SSO_EMAIL', 'warga22@ktp.iae.id'),
    env('SSO_PASSWORD', 'KtpDigital2026!')
);
Setelah semua diubah, coba Send lagi di Postman dengan Body:

json
{
    "product_id": "PROD-001",
    "reviewer_name": "Azzahra",
    "rating": 5,
    "comment": "Produk bagus!"
}
Screenshot hasilnya ya! 🙂

cara jalanin doicker tuh syntaxnya apa ya

1:19 PM
Untuk jalanin Docker di terminal VS Code, syntaxnya:

bash
docker-compose up -d
-d artinya running di background. Kalau mau lihat prosesnya langsung:

bash
docker-compose up
Kalau mau matiin:

bash
docker-compose down
Kalau mau build ulang dulu (misalnya ada perubahan kode):

bash
docker-compose up -d --build
Coba jalankan docker-compose up -d dulu, tunggu sampai selesai, lalu baru test di Postman. Screenshot kalau ada error ya! 🙂

1781245300752_image.png
aku udah ikutin semua kata kata kamu tapi

1:21 PM
Error 404 Not Found dari Apache — ini artinya request kamu nyasar ke Apache (port 8000) bukan ke Laravel. Ini masalah Docker/server yang jalan.

Cek dulu port yang bener
Lihat di response: Apache/2.4.67 (Debian) Server at localhost Port 8000 — berarti Docker sudah jalan tapi routing-nya ke Apache, bukan Laravel.

Cek docker-compose.yml kamu dulu, jalankan di terminal:

bash
cat docker-compose.yml
Screenshot hasilnya ke sini. Kemungkinan port Laravel-nya bukan 8000 tapi port lain (misalnya 8080 atau 9000). Nanti kita sesuaikan URL di Postman! 🙂

PS C:\Users\Azzahra Afidah Yusfa\customer-review-service> cat docker-compose.yml
services:
  app:
    build: .
    ports:
      - "8000:80"
    environment:
      APP_KEY: base64:your-app-key-here
      APP_ENV: local
      APP_DEBUG: true
      DB_CONNECTION: sqlite
      API_KEY: reviewservice-secret-key-2024
    volumes:
      - .:/var/www/html
PS C:\Users\Azzahra Afidah Yusfa\customer-review-service>

Tampilkan lebih banyak
1:22 PM
Ketahuan masalahnya! Ada 2 issue:

🔴 Issue 1 — API_KEY di docker-compose.yml beda sama .env
Di docker-compose.yml:

API_KEY: reviewservice-secret-key-2024
Tapi middleware kamu baca config('app.api_key') yang ngambil dari .env variable IAE_API_KEY=KEY-MHS-124.

🔴 Issue 2 — Route 404
Apache jalan tapi Laravel tidak merespons, kemungkinan route /api/v1/reviews belum terdaftar dengan benar di Docker environment.

Fix Sekarang
Fix 1 — Buka docker-compose.yml, ubah bagian environment jadi:

yaml
services:
  app:
    build: .
    ports:
      - "8000:80"
    environment:
      APP_KEY: base64:wdHbcRloV42SuQyYbiRHfmOPkoV4tMuHeoFUZanSE2o=
      APP_ENV: local
      APP_DEBUG: true
      DB_CONNECTION: sqlite
      API_KEY: KEY-MHS-124
      IAE_API_KEY: KEY-MHS-124
      SSO_EMAIL: warga22@ktp.iae.id
      SSO_PASSWORD: KtpDigital2026!
      IAE_TEAM_ID: TEAM-01
    volumes:
      - .:/var/www/html
Fix 2 — Cek routes, jalankan di terminal:

bash
php artisan route:list | grep reviews
Screenshot hasilnya ke sini biar aku bisa lihat route-nya terdaftar atau tidak!

Setelah itu rebuild Docker:

bash
docker-compose down
docker-compose up -d --build
PS C:\Users\Azzahra Afidah Yusfa\customer-review-service> php artisan route:list | grep reviews
grep : The term 'grep' is not recognized as the name of a cmdlet, function, script file, or operable program. Check the spelling of the name, 
or if a path was included, verify that the path is correct and try again.
At line:1 char:26
+ php artisan route:list | grep reviews
+                          ~~~~
    + CategoryInfo          : ObjectNotFound: (grep:String) [], CommandNotFoundException
    + FullyQualifiedErrorId : CommandNotFoundException

PS C:\Users\Azzahra Afidah Yusfa\customer-review-service>

1:23 PM
Windows PowerShell nggak punya grep! Pakai ini sebagai gantinya:

bash
php artisan route:list | findstr reviews
Screenshot hasilnya ya! 🙂

okee klo udah aku check lagii?

1:26 PM
Yap, coba jalankan dulu:

bash
php artisan route:list | findstr reviews
Screenshot hasilnya ke sini! Kalau route-nya ada, langsung kita fix Docker-nya biar Postman bisa hit endpoint dengan benar. 🙂

PS C:\Users\Azzahra Afidah Yusfa\customer-review-service> php artisan route:list | findstr reviews
  GET|HEAD  api/v1/reviews ............................................................................................. ReviewController@index
  POST      api/v1/reviews ............................................................................................. ReviewController@store
  GET|HEAD  api/v1/reviews/product/{product_id} .................................................................... ReviewController@byProduct
PS C:\Users\Azzahra Afidah Yusfa\customer-review-service>

1:27 PM
Route sudah terdaftar semua dengan benar! ✅

Sekarang masalahnya ada di Docker. Jalankan ini secara berurutan:

bash
docker-compose down
bash
docker-compose up -d --build
Tunggu sampai selesai build, lalu coba lagi di Postman. Kalau masih 404, kita coba akses langsung pakai Laravel tanpa Docker:

bash
php artisan serve
Lalu di Postman tetap pakai http://localhost:8000/api/v1/reviews. Screenshot hasilnya ya! 🙂


TSRM -I/usr/local/include/php/Zend -I/usr/local/include/php/ext -I/usr/local/include/php/ext/date/lib -I/usr/src/php/ext/mbstring/libmbfl -I/usr/src/php/ext/mbstring/libmbfl/mbfl -fstack-protector-strong -fpic -fpie -O2 -D_LARGEFILE_SOURCE -D_FILE_OFFSET_BITS=64 -DHAVE_CONFIG_H -fstack-protector-s

pasted

loh tadi sebelumnya tuh aku udh doocjer compose sama build, aku ulang lagi kah

1:28 PM
Docker build-nya sudah berhasil! ✅

Lihat bagian paling bawah:

✔ Image customer-review-service-app   Built
✔ Container customer-review-service-app-1  Started
Jadi Docker sudah jalan. Sekarang langsung test di Postman aja!

Pastikan di Postman:

URL: http://localhost:8000/api/v1/reviews
Method: POST
Headers:

X-IAE-KEY: KEY-MHS-124
Content-Type: application/json
Body → raw → JSON:

json
{
    "product_id": "PROD-001",
    "reviewer_name": "Azzahra",
    "rating": 5,
    "comment": "Produk bagus!"
}
Klik Send dan screenshot hasilnya! 🙂

1781245940618_image.png
aduh sama aja kayak gini

1:32 PM
Masalahnya jelas — Docker container jalan tapi Apache di dalamnya tidak mengarah ke Laravel. Ini masalah konfigurasi Dockerfile, bukan kode kamu.

Coba skip Docker dulu, pakai php artisan serve langsung biar lebih simpel. Jalankan di terminal:

bash
php artisan serve
Biarkan terminal itu jalan, lalu di Postman ganti URL-nya tetap sama:

http://localhost:8000/api/v1/reviews
Screenshot hasilnya ya! Kalau berhasil, berarti kode kamu sudah benar dan masalahnya memang cuma di konfigurasi Docker. 🙂

1781245967134_image.png
nihh udah

1:32 PM
Laravel server sudah jalan di http://127.0.0.1:8000! ✅

Sekarang di Postman, pastikan juga isi Body-nya. Klik tab Body → pilih raw → pilih JSON, lalu isi:

json
{
    "product_id": "PROD-001",
    "reviewer_name": "Azzahra",
    "rating": 5,
    "comment": "Produk bagus!"
}
Lalu klik Send! Screenshot hasilnya ya 🙂

tetep ajaaa gabisa

1:33 PM
Coba ganti URL-nya pakai 127.0.0.1 bukan localhost:

http://127.0.0.1:8000/api/v1/reviews
Kadang Windows ada masalah resolve localhost ke port yang salah. Screenshot hasilnya ya! 🙂

1781246040135_image.png
klo aku ganti linknya malah begini sih

1:34 PM
Progress! Sekarang beda errornya — 401 Unauthorized bukan 404 lagi. Artinya Laravel sudah bisa diakses, tinggal API Key-nya yang salah terbaca.

Masalahnya middleware kamu baca dari config('app.api_key') tapi di .env variable-nya IAE_API_KEY. Cek dulu config/app.php, cari bagian yang define api_key.

Jalankan di terminal baru (bukan yang lagi serve):

bash
php artisan tinker
Lalu ketik:

php
config('app.api_key')
Screenshot hasilnya — kita lihat value apa yang terbaca! 🙂

PS C:\Users\Azzahra Afidah Yusfa\customer-review-service> php artisan tinker
Psy Shell v0.12.22 (PHP 8.2.12 — cli) by Justin Hileman
New PHP manual is available (latest: 3.0.0). Update with doc --update-manual
> config('app.api_key')                                                                                                                          
= "KEY-MHS-07"
wah ko maish gini, kan harusnya 124

1:34 PM
Ketemu! config('app.api_key') masih baca KEY-MHS-07 karena Laravel cache config lama.

Keluar dulu dari tinker dengan exit, lalu jalankan:

bash
php artisan config:clear
Lalu cek lagi:

bash
php artisan tinker
php
config('app.api_key')
Harusnya sekarang sudah KEY-MHS-124. Kalau sudah, langsung test lagi di Postman! 🙂

PS C:\Users\Azzahra Afidah Yusfa\customer-review-service> php artisan config:clear
   INFO  Configuration cache cleared successfully.  
PS C:\Users\Azzahra Afidah Yusfa\customer-review-service> php artisan tinker
Psy Shell v0.12.22 (PHP 8.2.12 — cli) by Justin Hileman
New PHP manual is available (latest: 3.0.0). Update with doc --update-manual
> config('app.api_key')                                                                                                                          
= "KEY-MHS-07"
>

1:35 PM
Masih KEY-MHS-07 berarti config/app.php-nya yang hardcode nilai itu. Buka file config/app.php, cari baris yang ada api_key, pasti ada yang seperti ini:

php
'api_key' => env('API_KEY', 'KEY-MHS-07'),
Ganti jadi:

php
'api_key' => env('IAE_API_KEY', 'KEY-MHS-124'),
Simpan, lalu jalankan lagi:

bash
php artisan config:clear
php artisan tinker
php
config('app.api_key')
Harusnya sekarang muncul KEY-MHS-124! 🙂

oke udah

1:36 PM
Sekarang test di Postman lagi! Pastikan:

URL: http://127.0.0.1:8000/api/v1/reviews
Method: POST
Headers:

X-IAE-KEY: KEY-MHS-124
Content-Type: application/json
Body → raw → JSON:

json
{
    "product_id": "PROD-001",
    "reviewer_name": "Azzahra",
    "rating": 5,
    "comment": "Produk bagus!"
}
Klik Send dan screenshot hasilnya! 🙂


"message": "App\\Services\\SsoService::decodeJwtPayload(): Argument #1 ($token) must be of type string, null given, called in C:\\Users\\Azzahra Afidah Yusfa\\customer-review-service\\app\\Services\\SsoService.php on line 21", "exception": "TypeError", "file": "C:\\Users\\Azzahra Afidah Yu

pasted

1:37 PM
Progress bagus! API Key sudah lolos, sekarang error beda — SSO login gagal dapat token (return null), jadi decodeJwtPayload dapat null bukan string.

Artinya login ke SSO dosen gagal. Fix-nya tambahkan pengecekan null di SsoService.php. Buka file itu, ganti method loginAsUser jadi:

php
public function loginAsUser(string $email, string $password): ?string
{
    $response = Http::post("{$this->baseUrl}/api/v1/auth/token", [
        'email'    => $email,
        'password' => $password,
    ]);

    if ($response->successful()) {
        $token = $response->json('access_token');

        // Pastikan token tidak null sebelum decode
        if (!$token) {
            return null;
        }

        $payload = $this->decodeJwtPayload($token);

        UserRole::updateOrCreate(
            ['email' => $email],
            [
                'sso_user_id' => $payload['sub'] ?? null,
                'role'        => 'customer',
                'jwt_token'   => $token,
            ]
        );

        return $token;
    }

    return null;
}
Tapi yang lebih penting — kita perlu tahu kenapa SSO gagal. Tambahkan log sementara di ReviewController.php, ganti bagian login SSO jadi:

php
$sso   = new SsoService();
$token = $sso->loginAsUser(
    env('SSO_EMAIL', 'warga22@ktp.iae.id'),
    env('SSO_PASSWORD', 'KtpDigital2026!')
);

// Tambahkan ini sementara untuk debug
\Log::info('SSO Token: ' . ($token ?? 'NULL - Login gagal'));
Setelah save, test lagi di Postman, lalu cek log-nya:

bash
php artisan tail
Atau buka file storage/logs/laravel.log di VS Code, scroll ke paling bawah. Screenshot isi log-nya ya! 🙂

1781246649395_image.png
 "mq_published": false
harusnya publisehednya tuh true biar bisa aku chck di url dosen

1:44 PM
201 Created dan review tersimpan! ✅ Tinggal fix receipt_number: null dan mq_published: false — berarti SSO login masih gagal sehingga SOAP dan RabbitMQ tidak jalan.

Cek kenapa SSO gagal
Jalankan di terminal baru:

bash
php artisan tinker
Lalu ketik:

php
Http::post('https://iae-sso.virtualfri.id/api/v1/auth/token', ['email' => 'warga22@ktp.iae.id', 'password' => 'KtpDigital2026!'])->json()
Screenshot hasilnya — kita lihat response apa yang dikembalikan server dosen! 🙂

S C:\Users\Azzahra Afidah Yusfa\customer-review-service> php artisan tinker
Psy Shell v0.12.22 (PHP 8.2.12 — cli) by Justin Hileman
New PHP manual is available (latest: 3.0.0). Update with doc --update-manual
> Http::post('https://iae-sso.virtualfri.id/api/v1/auth/token', ['email' => 'warga22@ktp.iae.id', 'password' => 'KtpDigital2026!'])->json()      
= [
    "status" => "success",
    "token_type" => "user",
    "grant_type" => "password",
    "algorithm" => "RS256",
    "jwks_uri" => "/api/v1/auth/jwks",
    "token" => "eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiIsImtpZCI6ImlhZS1jZW50cmFsLTIwMjYifQ.eyJpc3MiOiJpYWUtY2VudHJhbC1tb2NrIiwic3ViIjoid2FyZ2EyMkBrdHAuaWFlLmlkIiwiaWF0IjoxNzgxMjQ2NzA4LCJleHAiOjE3ODEyNTAzMDgsImdyYW50X3R5cGUiOiJwYXNzd29yZCIsInRva2VuX3R5cGUiOiJ1c2VyIiwicHJvZmlsZSI6eyJuYW1lIjoiVmluYSBNZWxhdGkiLCJuaW0iOiIyMDI2MDAwMDIyIiwiZW1haWwiOiJ3YXJnYTIyQGt0cC5pYWUuaWQifX0.oIhtlXGbASBxGd5ZeYi10A0bUzTIKrJ1KHaab8L7SaI1G6HxZ56B04HezaQ_YsbamuuBN3m7v59dJn66Y2_3U2ZzV8Y3uNCxARBKwLvoujEIM7FvasTPJggldGTuy9_ScXzncvutMlo5fXaaBI76fRncZuWZqQWQaU9W73xhYNLmEW87QgBIKIJaWfqcRE6jw8q52qSE2tltC-h_OJtieMjndcSTvGvMxVDx-R_eubaaUWSsqS5kX_jJmexqQMqOCXk_PI9zHvj8mnaSBFmUaojvk-OgW3IXOeoHkLCYicOISJ7XqSEkdnWzo3yCpLo_wfzWlpXDkFZnybd5l2DzQA",
    "expires_in" => 3600,
    "profile" => [
      "name" => "Vina Melati",
      "nim" => "2026000022",
      "email" => "warga22@ktp.iae.id",
    ],
  ]
>

Tampilkan lebih banyak
1:45 PM
Ketemu masalahnya! Server dosen return key-nya token bukan access_token. Makanya selalu null!

Buka SsoService.php, ubah baris ini:

php
$token = $response->json('access_token');
Ganti jadi:

php
$token = $response->json('token');
Simpan, lalu test lagi di Postman. Sekarang receipt_number dan mq_published harusnya sudah terisi! 🙂

masih null

1:46 PM
Cek dulu di tinker, apakah SOAP-nya bisa hit server dosen. Ketik di tinker:

php
$token = Http::post('https://iae-sso.virtualfri.id/api/v1/auth/token', ['email' => 'warga22@ktp.iae.id', 'password' => 'KtpDigital2026!'])->json('token');
echo $token;
Kalau token muncul, lanjut test SOAP-nya:

php
Http::withHeaders(['Content-Type' => 'text/xml', 'Authorization' => 'Bearer ' . $token])->withBody('<?xml version="1.0" encoding="UTF-8"?><soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/" xmlns:iae="http://iae.central/audit"><soap:Body><iae:AuditRequest><iae:TeamID>TEAM-01</iae:TeamID><iae:ActivityName>CustomerReviewSubmitted</iae:ActivityName><iae:LogContent><![CDATA[{"test":"test"}]]></iae:LogContent></iae:AuditRequest></soap:Body></soap:Envelope>', 'text/xml')->post('https://iae-sso.virtualfri.id/soap/v1/audit')->body()
Screenshot hasilnya! 🙂

PS C:\Users\Azzahra Afidah Yusfa\customer-review-service> php artisan tinker
Psy Shell v0.12.22 (PHP 8.2.12 — cli) by Justin Hileman
New PHP manual is available (latest: 3.0.0). Update with doc --update-manual
> $token = Http::post('https://iae-sso.virtualfri.id/api/v1/auth/token', ['email' => 'warga22@ktp.iae.id', 'password' => 'KtpDigital2026!'])->jso
n('token');                                                                                                                                      
= "eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiIsImtpZCI6ImlhZS1jZW50cmFsLTIwMjYifQ.eyJpc3MiOiJpYWUtY2VudHJhbC1tb2NrIiwic3ViIjoid2FyZ2EyMkBrdHAuaWFlLmlkIiwiaWF0IjoxNzgxMjQ2ODI3LCJleHAiOjE3ODEyNTA0MjcsImdyYW50X3R5cGUiOiJwYXNzd29yZCIsInRva2VuX3R5cGUiOiJ1c2VyIiwicHJvZmlsZSI6eyJuYW1lIjoiVmluYSBNZWxhdGkiLCJuaW0iOiIyMDI2MDAwMDIyIiwiZW1haWwiOiJ3YXJnYTIyQGt0cC5pYWUuaWQifX0.RZLUXdS83zNtnQ8MWR_J28pnXMkEFrnEuSyQlVvAYaHU5vsToBXHVdo-wkdsf1O_1ox6Wbge8drUAunS7i2npOG1uP7P8ymXuEWf-x04JRz5hUVp7fDMRfIEtffM-kskQUzUNDwr9vyRsVEWZZ4Tw3m_Ne_M_HF1n_hfw_hvtSE4HXGoS3qTsGVLeoS1za_ltQT2lfeP5tJ-eVY3JRsTjJYgBaBiPfmunMty-BteduALLmm2magGve5J2FWKDtbguRU7hzUA-G-HsQspFeKXUG6SUp510ZN5F00j3prtN06CCSJRYmdnD9hAO8vOF6a51Zy_j0cps1tReeVIYZAzCw"
> echo $token;                                                                                                                                   
eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiIsImtpZCI6ImlhZS1jZW50cmFsLTIwMjYifQ.eyJpc3MiOiJpYWUtY2VudHJhbC1tb2NrIiwic3ViIjoid2FyZ2EyMkBrdHAuaWFlLmlkIiwiaWF0IjoxNzgxMjQ2ODI3LCJleHAiOjE3ODEyNTA0MjcsImdyYW50X3R5cGUiOiJwYXNzd29yZCIsInRva2VuX3R5cGUiOiJ1c2VyIiwicHJvZmlsZSI6eyJuYW1lIjoiVmluYSBNZWxhdGkiLCJuaW0iOiIyMDI2MDAwMDIyIiwiZW1haWwiOiJ3YXJnYTIyQGt0cC5pYWUuaWQifX0.RZLUXdS83zNtnQ8MWR_J28pnXMkEFrnEuSyQlVvAYaHU5vsToBXHVdo-wkdsf1O_1ox6Wbge8drUAunS7i2npOG1uP7P8ymXuEWf-x04JRz5hUVp7fDMRfIEtffM-kskQUzUNDwr9vyRsVEWZZ4Tw3m_Ne_M_HF1n_hfw_hvtSE4HXGoS3qTsGVLeoS1za_ltQT2lfeP5tJ-eVY3JRsTjJYgBaBiPfmunMty-BteduALLmm2magGve5J2FWKDtbguRU7hzUA-G-HsQspFeKXUG6SUp510ZN5F00j3prtN06CCSJRYmdnD9hAO8vOF6a51Zy_j0cps1tReeVIYZAzCw⏎

Tampilkan lebih banyak
1:47 PM
Token berhasil! ✅ Sekarang test SOAP-nya, copy paste command ini ke tinker:

php
Http::withHeaders(['Content-Type' => 'text/xml', 'Authorization' => 'Bearer ' . $token])->withBody('<?xml version="1.0" encoding="UTF-8"?><soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/" xmlns:iae="http://iae.central/audit"><soap:Body><iae:AuditRequest><iae:TeamID>TEAM-01</iae:TeamID><iae:ActivityName>CustomerReviewSubmitted</iae:ActivityName><iae:LogContent><![CDATA[{"test":"test"}]]></iae:LogContent></iae:AuditRequest></soap:Body></soap:Envelope>', 'text/xml')->post('https://iae-sso.virtualfri.id/soap/v1/audit')->body()
Screenshot hasilnya! 🙂

PS C:\Users\Azzahra Afidah Yusfa\customer-review-service> php artisan tinker                                                                     
Psy Shell v0.12.22 (PHP 8.2.12 — cli) by Justin Hileman                                                                                          
New PHP manual is available (latest: 3.0.0). Update with doc --update-manual                                                                   

Http::withHeaders(['Content-Type' => 'text/xml', 'Authorization' => 'Bearer ' . $token])->withBody('<?xml version="1.0" encoding="UTF-8"?><soap
:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/" xmlns:iae="http://iae.central/audit"><soap:Body><iae:AuditRequest><iae:TeamID>TE
AM-01</iae:TeamID><iae:ActivityName>CustomerReviewSubmitted</iae:ActivityName><iae:LogContent><![CDATA[{"test":"test"}]]></iae:LogContent></iae:A
uditRequest></soap:Body></soap:Envelope>', 'text/xml')->post('https://iae-sso.virtualfri.id/soap/v1/audit')->body()                              
   WARNING  Undefined variable $token.
= <<<EOS
  <?xml version="1.0" encoding="UTF-8"?>
  <soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">
    <soap:Body>
      <soap:Fault>
        <faultcode>soap:Client</faultcode>
        <faultstring>Unauthorized: Missing Bearer token.</faultstring>
      </soap:Fault>
    </soap:Body>
  </soap:Envelope>
  EOS

Tampilkan lebih banyak
1:48 PM
Variable $token hilang karena tinker session baru. Jalankan sekaligus dalam satu blok, copy paste ini semua sekaligus:

php
$token = Http::post('https://iae-sso.virtualfri.id/api/v1/auth/token', ['email' => 'warga22@ktp.iae.id', 'password' => 'KtpDigital2026!'])->json('token'); Http::withHeaders(['Content-Type' => 'text/xml', 'Authorization' => 'Bearer ' . $token])->withBody('<?xml version="1.0" encoding="UTF-8"?><soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/" xmlns:iae="http://iae.central/audit"><soap:Body><iae:AuditRequest><iae:TeamID>TEAM-01</iae:TeamID><iae:ActivityName>CustomerReviewSubmitted</iae:ActivityName><iae:LogContent><![CDATA[{"test":"test"}]]></iae:LogContent></iae:AuditRequest></soap:Body></soap:Envelope>', 'text/xml')->post('https://iae-sso.virtualfri.id/soap/v1/audit')->body()
Screenshot hasilnya! 🙂

WARNING  Undefined variable $token.
= <<<EOS
  <?xml version="1.0" encoding="UTF-8"?>
  <soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">
    <soap:Body>
      <soap:Fault>
        <faultcode>soap:Client</faultcode>
        <faultstring>Unauthorized: Missing Bearer token.</faultstring>
      </soap:Fault>
    </soap:Body>
  </soap:Envelope>
  EOS
>                                                                                                                                                
PS C:\Users\Azzahra Afidah Yusfa\customer-review-service> php artisan tinker                                                                     
Psy Shell v0.12.22 (PHP 8.2.12 — cli) by Justin Hileman                                                                                          
New PHP manual is available (latest: 3.0.0). Update with doc --update-manual                                                                   

$token = Http::post('https://iae-sso.virtualfri.id/api/v1/auth/token', ['email' => 'warga22@ktp.iae.id', 'password' => 'KtpDigital2026!'])->json('token'); Http::withHeaders(['Content-Type' => 'text/xml', 'Authorization' => 'Bearer ' . $token])->withBody('<?xml version="1.0" encoding="UTF
-8"?><soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/" xmlns:iae="http://iae.central/audit"><soap:Body><iae:AuditRequest><iae
:TeamID>TEAM-01</iae:TeamID><iae:ActivityName>CustomerReviewSubmitted</iae:ActivityName><iae:LogContent><![CDATA[{"test":"test"}]]></iae:LogConte
nt></iae:AuditRequest></soap:Body></soap:Envelope>', 'text/xml')->post('https://iae-sso.virtualfri.id/soap/v1/audit')->body()\                   
.                                                                                                                                                
= <<<EOS
  <?xml version="1.0" encoding="UTF-8"?>
  <soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">
    <soap:Body>
      <soap:Fault>
        <faultcode>soap:Client</faultcode>
        <faultstring>Forbidden: M2M Bearer token required.</faultstring>
      </soap:Fault>
    </soap:Body>
  </soap:Envelope>
  EOS

Tampilkan lebih banyak
1:49 PM
Ketemu! Error-nya "M2M Bearer token required" — artinya endpoint SOAP dosen tidak bisa pakai user token, harus pakai M2M token (login pakai api_key, bukan email/password)!

Buka SoapAuditService.php, tambahkan import SsoService dan ubah method sendReviewAudit untuk pakai M2M token:

php
<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use App\Models\AuditLog;

class SoapAuditService
{
    protected $soapUrl = 'https://iae-sso.virtualfri.id/soap/v1/audit';
    protected $teamId  = 'TEAM-01';

    public function sendReviewAudit(string $jwtToken, array $reviewData): ?string
    {
        // SOAP butuh M2M token, bukan user token!
        $sso = new SsoService();
        $m2mToken = $sso->loginAsM2M();

        if (!$m2mToken) {
            return null;
        }

        $logContent = json_encode([
            'product_id'    => $reviewData['product_id'],
            'reviewer_name' => $reviewData['reviewer_name'],
            'rating'        => $reviewData['rating'],
            'comment'       => $reviewData['comment'],
        ]);

        $xmlPayload = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/"
               xmlns:iae="http://iae.central/audit">
  <soap:Body>
    <iae:AuditRequest>
      <iae:TeamID>{$this->teamId}</iae:TeamID>
      <iae:ActivityName>CustomerReviewSubmitted</iae:ActivityName>
      <iae:LogContent><![CDATA[{$logContent}]]></iae:LogContent>
    </iae:AuditRequest>
  </soap:Body>
</soap:Envelope>
XML;

        $response = Http::withHeaders([
            'Content-Type'  => 'text/xml',
            'Authorization' => "Bearer {$m2mToken}",
        ])->withBody($xmlPayload, 'text/xml')->post($this->soapUrl);

        $receiptNumber = null;

        if ($response->successful()) {
            preg_match(
                '/<iae:ReceiptNumber>(.*?)<\/iae:ReceiptNumber>/',
                $response->body(),
                $matches
            );
            $receiptNumber = $matches[1] ?? null;

            AuditLog::create([
                'receipt_number' => $receiptNumber,
                'activity_name'  => 'CustomerReviewSubmitted',
                'log_content'    => $logContent,
            ]);
        }

        return $receiptNumber;
    }
}
Cek juga SsoService.php, pastikan loginAsM2M pakai env:

php
public function loginAsM2M(): ?string
{
    $response = Http::post("{$this->baseUrl}/api/v1/auth/token", [
        'api_key' => env('IAE_API_KEY', 'KEY-MHS-124'),
    ]);

    if ($response->successful()) {
        return $response->json('token'); // ← pastikan 'token' bukan 'access_token'
    }

    return null;
}
Simpan semua, test lagi di Postman! 🙂

1781247106054_image.png
tapi publishednya masih false

1:51 PM
SOAP berhasil! receipt_number: "IAE-LOG-2026-164736B3" sudah muncul! ✅

Sekarang tinggal fix RabbitMQ. Sama seperti SOAP, kemungkinan RabbitMQ juga butuh M2M token. Buka RabbitMQService.php, ubah jadi:

php
<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class RabbitMQService
{
    protected $publishUrl = 'https://iae-sso.virtualfri.id/api/v1/messages/publish';

    public function publishReviewEvent(string $jwtToken, array $reviewData): bool
    {
        // Coba pakai M2M token seperti SOAP
        $sso = new SsoService();
        $m2mToken = $sso->loginAsM2M();

        if (!$m2mToken) {
            return false;
        }

        $response = Http::withHeaders([
            'Authorization' => "Bearer {$m2mToken}",
            'Content-Type'  => 'application/json',
        ])->post($this->publishUrl, [
            'event' => 'review.submitted',
            'data'  => [
                'product_id'    => $reviewData['product_id'],
                'reviewer_name' => $reviewData['reviewer_name'],
                'rating'        => $reviewData['rating'],
            ],
        ]);

        return $response->successful();
    }
}
Simpan, test lagi di Postman! 🙂

masih false

1:52 PM
Cek dulu response aslinya dari server dosen. Jalankan di tinker:

php
$token = Http::post('https://iae-sso.virtualfri.id/api/v1/auth/token', ['api_key' => 'KEY-MHS-124'])->json('token'); Http::withHeaders(['Authorization' => 'Bearer ' . $token, 'Content-Type' => 'application/json'])->post('https://iae-sso.virtualfri.id/api/v1/messages/publish', ['event' => 'review.submitted', 'data' => ['product_id' => 'PROD-001', 'rating' => 5]])->json()
Screenshot hasilnya biar kita tahu error apa yang dikembalikan server! 🙂

PS C:\Users\Azzahra Afidah Yusfa\customer-review-service> php artisan tinker
Psy Shell v0.12.22 (PHP 8.2.12 — cli) by Justin Hileman
New PHP manual is available (latest: 3.0.0). Update with doc --update-manual
> $token = Http::post('https://iae-sso.virtualfri.id/api/v1/auth/token', ['api_key' => 'KEY-MHS-124'])->json('token'); Http::withHeaders(['Author
ization' => 'Bearer ' . $token, 'Content-Type' => 'application/json'])->post('https://iae-sso.virtualfri.id/api/v1/messages/publish', ['event' =>
 'review.submitted', 'data' => ['product_id' => 'PROD-001', 'rating' => 5]])->json()                                                             
= [
    "status" => "error",
    "message" => "message (object or string) is required.",
  ]
>

Tampilkan lebih banyak
1:53 PM
Ketemu! Server minta field message bukan event dan data. Buka RabbitMQService.php, ubah bagian payload-nya jadi:

php
$response = Http::withHeaders([
    'Authorization' => "Bearer {$m2mToken}",
    'Content-Type'  => 'application/json',
])->post($this->publishUrl, [
    'message' => [
        'event' => 'review.submitted',
        'data'  => [
            'product_id'    => $reviewData['product_id'],
            'reviewer_name' => $reviewData['reviewer_name'],
            'rating'        => $reviewData['rating'],
        ],
    ],
]);
Simpan, test lagi di Postman! 🙂