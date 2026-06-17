<?php

namespace App\Services;

use App\Models\AuditReceipt;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * SoapAuditService
 *
 * Sends audit logs to the central IAE SOAP audit endpoint.
 * Builds raw SOAP XML envelopes without requiring the PHP SOAP extension.
 */
class SoapAuditService
{
    /**
     * Cache key for the SSO bearer token used by SOAP audit.
     */
    private const TOKEN_CACHE_KEY = 'soap_audit_sso_token';

    /**
     * Cache TTL for the SSO token (in seconds).
     * Set conservatively below typical JWT expiration.
     */
    private const TOKEN_CACHE_TTL = 3300; // 55 minutes

    /**
     * Send an audit log to the SOAP audit service.
     *
     * @param string $activityName  The audit activity name (e.g., 'PromoApplied')
     * @param array  $logData       Associative array of log data to embed as JSON
     * @param string|null $bearerToken  Optional pre-existing bearer token; if null, auto-fetches via SSO
     * @return string|null  The ReceiptNumber on success, or null on failure
     */
    public function sendAudit(string $activityName, array $logData, ?string $bearerToken = null): ?string
    {
        try {
            $token = $bearerToken ?? $this->getToken();
            $teamId = config('iae.team_id');
            $logContentJson = json_encode($logData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

            // Build the SOAP XML envelope
            $soapXml = $this->buildSoapEnvelope($teamId, $activityName, $logContentJson);

            // Send the SOAP request
            $response = Http::withHeaders([
                'Content-Type' => 'text/xml; charset=utf-8',
                'Authorization' => 'Bearer ' . $token,
            ])->withBody($soapXml, 'text/xml; charset=utf-8')
              ->timeout(30)
              ->post(config('iae.soap.audit_url'));

            if ($response->failed()) {
                Log::error('SOAP Audit request failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                    'activity' => $activityName,
                ]);

                // Save failed receipt
                AuditReceipt::create([
                    'activity_name' => $activityName,
                    'log_content' => $logContentJson,
                    'receipt_number' => null,
                    'status' => 'FAILED',
                ]);

                return null;
            }

            // Parse the XML response to extract status and receipt number
            $receiptNumber = $this->parseResponse($response->body());

            // Save the audit receipt
            AuditReceipt::create([
                'activity_name' => $activityName,
                'log_content' => $logContentJson,
                'receipt_number' => $receiptNumber,
                'status' => $receiptNumber ? 'SUCCESS' : 'FAILED',
            ]);

            return $receiptNumber;
        } catch (\Exception $e) {
            Log::error('SOAP Audit exception', [
                'activity' => $activityName,
                'error' => $e->getMessage(),
            ]);

            // Save failed receipt on exception
            AuditReceipt::create([
                'activity_name' => $activityName,
                'log_content' => json_encode($logData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                'receipt_number' => null,
                'status' => 'FAILED',
            ]);

            return null;
        }
    }

    /**
     * Build the SOAP XML envelope for the audit request.
     *
     * @param string $teamId       The team identifier
     * @param string $activityName The activity being audited
     * @param string $logContent   JSON-encoded log content
     * @return string The complete SOAP XML envelope
     */
    private function buildSoapEnvelope(string $teamId, string $activityName, string $logContent): string
    {
        return '<?xml version="1.0" encoding="UTF-8"?>' . "\n" .
            '<soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/" xmlns:iae="http://iae.central/audit">' . "\n" .
            '  <soap:Body>' . "\n" .
            '    <iae:AuditRequest>' . "\n" .
            '      <iae:TeamID>' . htmlspecialchars($teamId, ENT_XML1, 'UTF-8') . '</iae:TeamID>' . "\n" .
            '      <iae:ActivityName>' . htmlspecialchars($activityName, ENT_XML1, 'UTF-8') . '</iae:ActivityName>' . "\n" .
            '      <iae:LogContent><![CDATA[' . $logContent . ']]></iae:LogContent>' . "\n" .
            '    </iae:AuditRequest>' . "\n" .
            '  </soap:Body>' . "\n" .
            '</soap:Envelope>';
    }

    /**
     * Parse the SOAP response XML and extract the ReceiptNumber.
     *
     * @param string $responseBody The raw XML response body
     * @return string|null The receipt number, or null if parsing fails
     */
    private function parseResponse(string $responseBody): ?string
    {
        try {
            // Extract ReceiptNumber using regex (avoids namespace parsing issues)
            if (preg_match('/<[^>]*ReceiptNumber[^>]*>([^<]+)</', $responseBody, $matches)) {
                return $matches[1];
            }

            // Fallback: try SimpleXML parsing
            $cleanXml = preg_replace('/(<\/?)(\w+):([^>]*>)/', '$1$2_$3', $responseBody);
            $xml = simplexml_load_string($cleanXml);

            if ($xml !== false) {
                $body = $xml->soap_Body ?? $xml->Body ?? null;
                if ($body) {
                    $json = json_encode($body);
                    $array = json_decode($json, true);
                    return $this->findInArray($array, 'ReceiptNumber');
                }
            }

            Log::warning('SOAP Audit: Could not parse ReceiptNumber from response', [
                'body' => $responseBody,
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('SOAP Audit response parsing failed', [
                'error' => $e->getMessage(),
                'body' => $responseBody,
            ]);

            return null;
        }
    }

    /**
     * Recursively search for a key in a nested array.
     *
     * @param array  $array The array to search
     * @param string $key   The key to find
     * @return string|null The value if found, null otherwise
     */
    private function findInArray(array $array, string $key): ?string
    {
        foreach ($array as $k => $v) {
            // Check if the key contains the search term (handles namespace prefixes)
            if (is_string($k) && str_contains($k, $key)) {
                return is_string($v) ? $v : null;
            }
            if (is_array($v)) {
                $result = $this->findInArray($v, $key);
                if ($result !== null) {
                    return $result;
                }
            }
        }

        return null;
    }

    /**
     * Get a valid SSO bearer token, using cache when available.
     *
     * Authenticates via SsoService::login() using credentials from config.
     *
     * @return string The bearer token
     *
     * @throws \RuntimeException When SSO authentication fails
     */
    public function getToken(): string
    {
        return Cache::remember(self::TOKEN_CACHE_KEY, self::TOKEN_CACHE_TTL, function () {
            $ssoService = new SsoService();

            $result = $ssoService->login(
                config('iae.sso.email'),
                config('iae.sso.password')
            );

            if (!$result['success']) {
                throw new \RuntimeException('Gagal mendapatkan token SSO untuk SOAP Audit: ' . ($result['message'] ?? 'Unknown error'));
            }

            // Extract the access token from the SSO response
            $token = $result['data']['access_token']
                ?? $result['data']['token']
                ?? null;

            if (!$token) {
                throw new \RuntimeException('Token tidak ditemukan dalam response SSO');
            }

            Log::info('SOAP Audit: SSO token acquired and cached');

            return $token;
        });
    }
}
