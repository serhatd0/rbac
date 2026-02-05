<?php

declare(strict_types=1);

namespace App\Rbac;

use App\Http\Request;
use App\Http\Response;
use App\User\UserRepository;
use PDO;

class AdminController
{
    private PDO $db;
    private UserRepository $userRepo;

    public function __construct(PDO $db, UserRepository $userRepo)
    {
        $this->db = $db;
        $this->userRepo = $userRepo;
    }

    public function index(Request $request): void
    {
        $stmt = $this->db->query("SELECT id, email, created_at FROM users");
        $users = $stmt->fetchAll();
        Response::json($users);
    }

    public function assignRole(Request $request, string $id): void
    {
        $roles = $request->input('roles'); // Expecting array of role names
        if (!is_array($roles)) {
            Response::error('Roles must be an array');
        }

        foreach ($roles as $roleName) {
            $this->userRepo->assignRole((int)$id, $roleName);
        }

        Response::json(['message' => 'Roles assigned']);
    }
}
