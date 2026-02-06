<?php

declare(strict_types=1);

namespace App\Auth;

use App\Config\Env;
use RuntimeException;

class Jwt
{
    private string $secret;
    private string $algo = 'HS256';

    public function __construct()
    {
        $this->secret = Env::get('JWT_SECRET', 'secret_key_change_me');
    }

    public function encode(array $payload): string
    {
        $header = json_encode(['typ' => 'JWT', 'alg' => $this->algo]);
        $payload['iat'] = time();
        // Default 15 mins if not set
        if (!isset($payload['exp'])) {
            $payload['exp'] = time() + (15 * 60);
        }

        $payloadJson = json_encode($payload);

        $base64UrlHeader = $this->base64UrlEncode($header);
        $base64UrlPayload = $this->base64UrlEncode($payloadJson);

        $signature = hash_hmac('sha256', $base64UrlHeader . "." . $base64UrlPayload, $this->secret, true);
        $base64UrlSignature = $this->base64UrlEncode($signature);

        return $base64UrlHeader . "." . $base64UrlPayload . "." . $base64UrlSignature;
    }

    public function decode(string $token): array
    {
        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            throw new RuntimeException('Invalid token format');
        }

        [$headerMd, $payloadMd, $sigMd] = $parts;

        $signature = $this->base64UrlDecode($sigMd);
        $expectedSignature = hash_hmac('sha256', $headerMd . "." . $payloadMd, $this->secret, true);

        if (!hash_equals($expectedSignature, $signature)) {
            throw new RuntimeException('Invalid signature');
        }

        $payload = json_decode($this->base64UrlDecode($payloadMd), true);

        if (($payload['exp'] ?? 0) < time()) {
            throw new RuntimeException('Token expired');
        }

        return $payload;
    }

    private function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    private function base64UrlDecode(string $data): string
    {
        return base64_decode(str_pad(strtr($data, '-_', '+/'), strlen($data) % 4, '=', STR_PAD_RIGHT));
    }
}
