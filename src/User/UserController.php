<?php

declare(strict_types=1);

namespace App\User;

use App\Http\Request;
use App\Http\Response;

class UserController
{
    private UserRepository $userRepo;

    public function __construct(UserRepository $userRepo)
    {
        $this->userRepo = $userRepo;
    }

    public function me(Request $request): void
    {
        $userId = $request->getAttribute('user_id');
        $user = $this->userRepo->findById($userId);
        
        if (!$user) {
            Response::error('User not found', 404);
        }

        unset($user['password_hash']);
        Response::json($user);
    }
}
