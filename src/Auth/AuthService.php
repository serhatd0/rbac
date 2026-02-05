<?php

declare(strict_types=1);

namespace App\Auth;

use App\User\UserRepository;
use RuntimeException;

class AuthService
{
    private UserRepository $userRepo;
    private RefreshTokenService $refreshService;
    private Jwt $jwt;

    public function __construct(UserRepository $userRepo, RefreshTokenService $refreshService, Jwt $jwt)
    {
        $this->userRepo = $userRepo;
        $this->refreshService = $refreshService;
        $this->jwt = $jwt;
    }

    public function register(string $email, string $password): array
    {
        if ($this->userRepo->findByEmail($email)) {
            throw new RuntimeException("Email already exists");
        }

        $hash = password_hash($password, PASSWORD_BCRYPT);
        $userId = $this->userRepo->create($email, $hash);
        
        // Assign default user role
        $this->userRepo->assignRole($userId, 'user');

        return ['id' => $userId, 'email' => $email];
    }

    public function login(string $email, string $password): array
    {
        $user = $this->userRepo->findByEmail($email);
        if (!$user || !password_verify($password, $user['password_hash'])) {
            throw new RuntimeException("Invalid credentials");
        }

        $accessToken = $this->jwt->encode(['sub' => $user['id'], 'email' => $user['email']]);
        $refreshToken = $this->refreshService->create($user['id']);

        return [
            'accessToken' => $accessToken,
            'refreshToken' => $refreshToken
        ];
    }

    public function refresh(string $refreshToken): array
    {
        // verify token
        $tokenData = $this->refreshService->verifyAndRevoke($refreshToken);
        
        // Revoke the generic one used for refresh? 
        // The prompt says "Refresh token: 30 days...". 
        // Standard practice is rotation: revoke old, issue new.
        // Let's do rotation for better security.
        $this->refreshService->revoke($refreshToken);

        $user = $this->userRepo->findById($tokenData['user_id']);
        if (!$user) {
             throw new RuntimeException("User not found");
        }

        $accessToken = $this->jwt->encode(['sub' => $user['id'], 'email' => $user['email']]);
        $newRefreshToken = $this->refreshService->create($user['id']);

        return [
            'accessToken' => $accessToken,
            'refreshToken' => $newRefreshToken
        ];
    }

    public function logout(string $refreshToken): void
    {
        $this->refreshService->revoke($refreshToken);
    }
}
