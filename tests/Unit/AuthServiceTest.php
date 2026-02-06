<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Auth\AuthService;
use App\Auth\Jwt;
use App\Auth\RefreshTokenService;
use App\User\UserRepository;
use PHPUnit\Framework\TestCase;

class AuthServiceTest extends TestCase
{
    private $userRepo;
    private $refreshService;
    private $jwt;
    private $authService;

    protected function setUp(): void
    {
        $this->userRepo = $this->createMock(UserRepository::class);
        $this->refreshService = $this->createMock(RefreshTokenService::class);
        $this->jwt = new Jwt(); // Use real JWT for simplicity or mock it

        $this->authService = new AuthService($this->userRepo, $this->refreshService, $this->jwt);
    }

    public function testLoginSuccess(): void
    {
        $email = 'test@example.com';
        $password = 'password';
        $hash = password_hash($password, PASSWORD_BCRYPT);

        $user = ['id' => 1, 'email' => $email, 'password_hash' => $hash];

        $this->userRepo->method('findByEmail')->willReturn($user);
        $this->refreshService->method('create')->willReturn('refresh_token_123');

        $result = $this->authService->login($email, $password);

        $this->assertArrayHasKey('accessToken', $result);
        $this->assertArrayHasKey('refreshToken', $result);
        $this->assertEquals('refresh_token_123', $result['refreshToken']);
    }

    public function testRegisterSuccess(): void
    {
        $email = 'new@example.com';
        $password = 'password';

        $this->userRepo->method('findByEmail')->willReturn(null);
        $this->userRepo->method('create')->willReturn(2);
        $this->userRepo->expects($this->once())->method('assignRole')->with(2, 'user');

        $result = $this->authService->register($email, $password);

        $this->assertEquals(2, $result['id']);
        $this->assertEquals($email, $result['email']);
    }
}
