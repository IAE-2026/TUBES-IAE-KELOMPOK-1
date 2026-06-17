<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Exception;

class CentralSSOService
{
    protected static $baseUrl = 'https://iae-sso.virtualfri.id';
    protected static $apiKey = 'KEY-MHS-282';
    protected static $teamId = 'TEAM-01'; // From task requirements/identity

    /**
     * Retrieve the M2M bearer token, with local caching.
     */
    public static function getM2MToken(): string
    {
        return Cache::remember('sso_m2m_token', 3000, function () {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->post(self::$baseUrl . '/api/v1/auth/token', [
                'api_key' => self::$apiKey,
            ]);

            if (!$response->successful()) {
                throw new Exception("Failed to retrieve M2M token from Central SSO: " . $response->body());
            }

            $data = $response->json();
            $token = $data['token'] ?? null;

            if (!$token) {
                throw new Exception("M2M token not present in SSO response.");
            }

            return $token;
        });
    }

    /**
     * Perform SOAP Audit log request to central audit service.
     *
     * @param string $activityName
     * @param array $data
     * @return string|null The ReceiptNumber returned from Dosen server.
     */
    public static function audit(string $activityName, array $data): ?string
    {
        try {
            $token = self::getM2MToken();
            $jsonContent = json_encode($data);

            // Construct rigid SOAP XML Envelope
            $xmlPayload = '<?xml version="1.0" encoding="UTF-8"?>
<soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/" xmlns:iae="http://iae.central/audit">
  <soap:Body>
    <iae:AuditRequest>
      <iae:TeamID>' . e(self::$teamId) . '</iae:TeamID>
      <iae:ActivityName>' . e($activityName) . '</iae:ActivityName>
      <iae:LogContent><![CDATA[' . $jsonContent . ']]></iae:LogContent>
    </iae:AuditRequest>
  </soap:Body>
</soap:Envelope>';

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $token,
                'Content-Type' => 'text/xml; charset=utf-8',
            ])->withBody($xmlPayload, 'text/xml')
              ->post(self::$baseUrl . '/soap/v1/audit');

            if (!$response->successful()) {
                Log::error("SOAP Audit request failed", ['status' => $response->status(), 'body' => $response->body()]);
                return null;
            }

            $responseBody = $response->body();

            // Parse SOAP XML response to extract ReceiptNumber
            // Clean/remove namespace prefixes to parse easily
            $cleanXml = preg_replace('/<(\/?)(\w+):([^>]*?)>/', '<$1$3>', $responseBody);
            $xml = simplexml_load_string($cleanXml);

            if ($xml === false) {
                Log::error("Failed parsing SOAP response XML", ['body' => $responseBody]);
                return null;
            }

            // Path: Envelope/Body/AuditResponse/ReceiptNumber or directly ReceiptNumber depending on namespace removal
            // Let's use xpath to find ReceiptNumber regardless of structure
            $receiptNumbers = $xml->xpath('//ReceiptNumber');
            if (!empty($receiptNumbers)) {
                return (string) $receiptNumbers[0];
            }

            // Fallback: search regex if xpath failed
            if (preg_match('/<iae:ReceiptNumber>(.*?)<\/iae:ReceiptNumber>/', $responseBody, $matches)) {
                return $matches[1];
            }

            Log::warning("ReceiptNumber not found in SOAP response", ['body' => $responseBody]);
            return null;

        } catch (Exception $e) {
            Log::error("Error in CentralSSOService::audit: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Publish JSON event notification to RabbitMQ via Central SSO API.
     *
     * @param array $payload
     * @return bool
     */
    public static function publishMessage(array $payload): bool
    {
        try {
            $token = self::getM2MToken();

            // Embed student name and NIM as requested
            $messageData = [
                'student_name' => 'Sepdaffa Raja',
                'student_nim' => '102022400191',
                'timestamp' => now()->toDateTimeString(),
                'payload' => $payload
            ];

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $token,
                'Content-Type' => 'application/json',
            ])->post(self::$baseUrl . '/api/v1/messages/publish', [
                'message' => $messageData,
            ]);

            if (!$response->successful()) {
                Log::error("RabbitMQ message publish failed", ['status' => $response->status(), 'body' => $response->body()]);
                return false;
            }

            return true;

        } catch (Exception $e) {
            Log::error("Error in CentralSSOService::publishMessage: " . $e->getMessage());
            return false;
        }
    }
}
