<?php

declare(strict_types=1);

namespace App\Auth;

use App\Http\Request;
use App\Http\Response;
use RuntimeException;

class AuthController
{
    private AuthService $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    public function register(Request $request): void
    {
        $email = $request->input('email');
        $password = $request->input('password');

        if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            Response::error('Invalid email');
        }
        if (!$password || strlen($password) < 6) {
            Response::error('Password must be at least 6 chars');
        }

        try {
            $user = $this->authService->register($email, $password);
            Response::json($user, 201);
        } catch (RuntimeException $e) {
            Response::error($e->getMessage(), 400);
        }
    }

    public function login(Request $request): void
    {
        $email = $request->input('email');
        $password = $request->input('password');

        try {
            $tokens = $this->authService->login($email, $password);
            Response::json($tokens);
        } catch (RuntimeException $e) {
            Response::error($e->getMessage(), 401);
        }
    }

    public function refresh(Request $request): void
    {
        $refreshToken = $request->input('refreshToken');
        if (!$refreshToken) {
            Response::error('Refresh token required');
        }

        try {
            $tokens = $this->authService->refresh($refreshToken);
            Response::json($tokens);
        } catch (RuntimeException $e) {
            Response::error($e->getMessage(), 401);
        }
    }

    public function logout(Request $request): void
    {
        $refreshToken = $request->input('refreshToken');
        if ($refreshToken) {
            $this->authService->logout($refreshToken);
        }

        Response::json(null, 204);
    }
}
