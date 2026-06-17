<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Exception;

class JwtVerifier
{
    protected static $jwksUri = 'https://iae-sso.virtualfri.id/api/v1/auth/jwks';

    /**
     * Decode and verify a JWT using the RS256 algorithm and public JWKS keys.
     *
     * @param string $jwt
     * @return array
     * @throws Exception
     */
    public static function verify(string $jwt): array
    {
        $parts = explode('.', $jwt);
        if (count($parts) !== 3) {
            throw new Exception("Invalid JWT token format.");
        }

        [$headerB64, $payloadB64, $signatureB64] = $parts;

        $header = json_decode(self::base64UrlDecode($headerB64), true);
        $payload = json_decode(self::base64UrlDecode($payloadB64), true);
        $signature = self::base64UrlDecode($signatureB64);

        if (!$header || !$payload) {
            throw new Exception("Unable to parse JWT header or payload.");
        }

        if (($header['alg'] ?? '') !== 'RS256') {
            throw new Exception("Unsupported algorithm: " . ($header['alg'] ?? 'none'));
        }

        $kid = $header['kid'] ?? null;
        if (!$kid) {
            throw new Exception("JWT is missing 'kid' in header.");
        }

        // Fetch JWKS
        $response = Http::get(self::$jwksUri);
        if (!$response->successful()) {
            throw new Exception("Failed to fetch JWKS from central SSO.");
        }

        $jwks = $response->json();
        $keys = $jwks['keys'] ?? [];

        $matchedKey = null;
        foreach ($keys as $key) {
            if ($key['kid'] === $kid) {
                $matchedKey = $key;
                break;
            }
        }

        if (!$matchedKey) {
            throw new Exception("No matching public key found for kid: " . $kid);
        }

        // Convert JWK to PEM public key
        $pem = self::jwkToPem($matchedKey);
        if (!$pem) {
            throw new Exception("Unable to generate PEM from JWK.");
        }

        // Verify signature
        $dataToVerify = $headerB64 . '.' . $payloadB64;
        $verificationResult = openssl_verify($dataToVerify, $signature, $pem, OPENSSL_ALGO_SHA256);

        if ($verificationResult !== 1) {
            throw new Exception("JWT signature verification failed.");
        }

        // Verify expiration
        $exp = $payload['exp'] ?? 0;
        if ($exp < time()) {
            throw new Exception("JWT token has expired.");
        }

        return $payload;
    }

    /**
     * Base64URL decode helper.
     */
    public static function base64UrlDecode(string $input): string
    {
        $remainder = strlen($input) % 4;
        if ($remainder) {
            $padlen = 4 - $remainder;
            $input .= str_repeat('=', $padlen);
        }
        return base64_decode(strtr($input, '-_', '+/'));
    }

    /**
     * Convert RSA JWK to PEM public key.
     */
    public static function jwkToPem(array $jwk): ?string
    {
        if (!isset($jwk['n']) || !isset($jwk['e'])) {
            return null;
        }

        $modulus = self::base64UrlDecode($jwk['n']);
        $exponent = self::base64UrlDecode($jwk['e']);

        $encodeLength = function ($len) {
            if ($len <= 0x7F) {
                return chr($len);
            }
            $temp = ltrim(pack('N', $len), chr(0));
            return chr(0x80 | strlen($temp)) . $temp;
        };

        if (ord($modulus[0]) & 0x80) {
            $modulus = chr(0x00) . $modulus;
        }

        // RSAPublicKey ::= SEQUENCE { modulus INTEGER, publicExponent INTEGER }
        $modulusDer = chr(0x02) . $encodeLength(strlen($modulus)) . $modulus;
        $exponentDer = chr(0x02) . $encodeLength(strlen($exponent)) . $exponent;
        $rsaPublicKeyDer = chr(0x30) . $encodeLength(strlen($modulusDer . $exponentDer)) . $modulusDer . $exponentDer;

        // AlgorithmIdentifier ::= SEQUENCE { algorithm OBJECT IDENTIFIER, parameters ANY DEFINED BY algorithm OPTIONAL }
        // OID rsaEncryption 1.2.840.113549.1.1.1
        $algorithmIdentifierDer = chr(0x30) . chr(0x0d)
            . chr(0x06) . chr(0x09) . chr(0x2a) . chr(0x86) . chr(0x48) . chr(0x86) . chr(0xf7) . chr(0x0d) . chr(0x01) . chr(0x01) . chr(0x01)
            . chr(0x05) . chr(0x00);

        // SubjectPublicKeyInfo ::= SEQUENCE { algorithm AlgorithmIdentifier, subjectPublicKey BIT STRING }
        $subjectPublicKeyDer = chr(0x03) . $encodeLength(strlen($rsaPublicKeyDer) + 1) . chr(0x00) . $rsaPublicKeyDer;
        $subjectPublicKeyInfoDer = chr(0x30) . $encodeLength(strlen($algorithmIdentifierDer . $subjectPublicKeyDer)) . $algorithmIdentifierDer . $subjectPublicKeyDer;

        $pem = "-----BEGIN PUBLIC KEY-----\n" . chunk_split(base64_encode($subjectPublicKeyInfoDer), 64, "\n") . "-----END PUBLIC KEY-----\n";
        return $pem;
    }
}
