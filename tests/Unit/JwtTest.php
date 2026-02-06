<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Auth\Jwt;
use PHPUnit\Framework\TestCase;

class JwtTest extends TestCase
{
    public function testEncodeAndDecode(): void
    {
        $jwt = new Jwt();
        $payload = ['sub' => 123, 'email' => 'test@example.com'];

        $token = $jwt->encode($payload);
        $decoded = $jwt->decode($token);

        $this->assertEquals($payload['sub'], $decoded['sub']);
        $this->assertEquals($payload['email'], $decoded['email']);
    }

    public function testExpiredToken(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Token expired');

        $jwt = new Jwt();
        $payload = ['sub' => 123, 'exp' => time() - 3600];

        $token = $jwt->encode($payload);
        $jwt->decode($token);
    }
}
