<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Auth\Jwt;
use App\Http\Request;
use App\Http\Response;
use RuntimeException;

class AuthMiddleware
{
    private Jwt $jwt;

    public function __construct(Jwt $jwt)
    {
        $this->jwt = $jwt;
    }

    public function handle(Request $request): void
    {
        $header = $request->getHeader('Authorization');
        if (!$header || !preg_match('/Bearer\s+(.*)$/i', $header, $matches)) {
            Response::error('Unauthorized: Missing token', 401);
        }

        try {
            $payload = $this->jwt->decode($matches[1]);
            $request->setAttribute('user_id', $payload['sub']);
            $request->setAttribute('email', $payload['email']);
        } catch (RuntimeException $e) {
            Response::error('Unauthorized: ' . $e->getMessage(), 401);
        }
    }
}
