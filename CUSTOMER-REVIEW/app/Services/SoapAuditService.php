<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\AuditLog;

class SoapAuditService
{
    protected string $soapUrl;
    protected string $teamId;

    public function __construct()
    {
        $this->soapUrl = config('services.iae.base_url') . '/soap/v1/audit';
        $this->teamId  = config('services.iae.team_id');
    }

    /**
     * Kirim audit log transaksi review ke SOAP Audit Dosen.
     * Transformasi data JSON ke XML Envelope, lalu simpan ReceiptNumber.
     */
    public function sendReviewAudit(array $reviewData): ?string
    {
        // SOAP butuh M2M token, bukan user token
        $sso = new SsoService();
        $m2mToken = $sso->loginAsM2M();

        if (!$m2mToken) {
            Log::error('SOAP Audit gagal: M2M token tidak tersedia');
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

            Log::info('SOAP Audit berhasil', ['receipt_number' => $receiptNumber]);
        } else {
            Log::error('SOAP Audit gagal', [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);
        }

        return $receiptNumber;
    }
}