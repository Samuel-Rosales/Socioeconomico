<?php

namespace App\Core;

/**
 * AuthToken
 *
 * Implementación ligera tipo JWT (HS256) compatible con PHP 7.1.
 * - Firma HMAC-SHA256
 * - Expiración (exp)
 *
 * Token format: base64url(header).base64url(payload).base64url(signature)
 */
class AuthToken
{
    // 8 horas
    const DEFAULT_TTL_SECONDS = 28800;

    public static function issue(array $payload, $ttlSeconds = null)
    {
        $ttlSeconds = is_numeric($ttlSeconds) && (int)$ttlSeconds > 0 ? (int)$ttlSeconds : self::DEFAULT_TTL_SECONDS;

        $now = time();
        $payload['iat'] = $now;
        $payload['exp'] = $now + $ttlSeconds;

        $header = [
            'alg' => 'HS256',
            'typ' => 'JWT',
        ];

        $headerB64 = self::base64UrlEncode(json_encode($header));
        $payloadB64 = self::base64UrlEncode(json_encode($payload));

        $dataToSign = $headerB64 . '.' . $payloadB64;
        $signature = self::sign($dataToSign, self::getSecret());
        $signatureB64 = self::base64UrlEncode($signature);

        return $dataToSign . '.' . $signatureB64;
    }

    public static function verify($token)
    {
        if (!is_string($token) || trim($token) === '') {
            return null;
        }

        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            return null;
        }

        list($headerB64, $payloadB64, $signatureB64) = $parts;

        $headerJson = self::base64UrlDecode($headerB64);
        $payloadJson = self::base64UrlDecode($payloadB64);
        $signature = self::base64UrlDecode($signatureB64);

        if ($headerJson === false || $payloadJson === false || $signature === false) {
            return null;
        }

        $header = json_decode($headerJson, true);
        $payload = json_decode($payloadJson, true);

        if (!is_array($header) || !is_array($payload)) {
            return null;
        }

        if (($header['alg'] ?? null) !== 'HS256') {
            return null;
        }

        $dataToSign = $headerB64 . '.' . $payloadB64;
        $expected = self::sign($dataToSign, self::getSecret());

        if (!self::hashEquals($expected, $signature)) {
            return null;
        }

        $exp = isset($payload['exp']) ? (int)$payload['exp'] : 0;
        if ($exp > 0 && time() >= $exp) {
            return null;
        }

        return $payload;
    }

    private static function sign($data, $secret)
    {
        return hash_hmac('sha256', $data, $secret, true);
    }

    private static function base64UrlEncode($data)
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    private static function base64UrlDecode($data)
    {
        if (!is_string($data)) {
            return false;
        }

        $remainder = strlen($data) % 4;
        if ($remainder) {
            $data .= str_repeat('=', 4 - $remainder);
        }

        return base64_decode(strtr($data, '-_', '+/'));
    }

    private static function hashEquals($a, $b)
    {
        if (function_exists('hash_equals')) {
            return hash_equals($a, $b);
        }

        // Fallback (PHP < 5.6). En este repo estamos en 7.1, pero mantenemos compatibilidad.
        if (!is_string($a) || !is_string($b)) {
            return false;
        }
        if (strlen($a) !== strlen($b)) {
            return false;
        }

        $result = 0;
        $len = strlen($a);
        for ($i = 0; $i < $len; $i++) {
            $result |= ord($a[$i]) ^ ord($b[$i]);
        }

        return $result === 0;
    }

    private static function getSecret()
    {
        // 1) Variable de entorno
        $env = getenv('AUTH_SECRET');
        if ($env !== false && trim($env) !== '') {
            return (string)$env;
        }

        $env = getenv('JWT_SECRET');
        if ($env !== false && trim($env) !== '') {
            return (string)$env;
        }

        // // 2) Archivo persistente (config/auth.secret)
        // $configDir = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'config';
        // $secretPath = $configDir . DIRECTORY_SEPARATOR . 'auth.secret';

        // if (is_file($secretPath)) {
        //     $secret = trim((string)@file_get_contents($secretPath));
        //     if ($secret !== '') {
        //         return $secret;
        //     }
        // }

        // // 3) Intentar generar y persistir el secreto
        // $secret = base64_encode(random_bytes(32));

        // if (is_dir($configDir) || @mkdir($configDir, 0775, true)) {
        //     @file_put_contents($secretPath, $secret);
        // }

        // // Si no se puede persistir, caemos a un secreto estable (menos ideal, pero evita invalidar tokens por request)
        // if (!is_file($secretPath)) {
        //     return hash('sha256', __DIR__ . '|' . PHP_VERSION . '|' . php_uname(), true);
        // }

        return $env; // En este caso, solo usamos la variable de entorno para evitar problemas de permisos en hosting compartido
    }
}
