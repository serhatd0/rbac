<?php

declare(strict_types=1);

namespace App\User;

use App\Database\Connection;
use PDO;

class UserRepository
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function findByEmail(string $email): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE email = :email");
        $stmt->execute(['email' => $email]);
        return $stmt->fetch() ?: null;
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE id = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch() ?: null;
    }

    public function create(string $email, string $passwordHash): int
    {
        $stmt = $this->db->prepare("INSERT INTO users (email, password_hash) VALUES (:email, :hash)");
        $stmt->execute(['email' => $email, 'hash' => $passwordHash]);
        return (int)$this->db->lastInsertId();
    }

    public function assignRole(int $userId, string $roleName): void
    {
         // Find role id
         $stmt = $this->db->prepare("SELECT id FROM roles WHERE name = :name");
         $stmt->execute(['name' => $roleName]);
         $role = $stmt->fetch();

        if ($role) {
            $stmt = $this->db->prepare("INSERT IGNORE INTO user_roles (user_id, role_id) VALUES (:uid, :rid)");
            $stmt->execute(['uid' => $userId, 'rid' => $role['id']]);
        }
    }
}
