<?php

declare(strict_types=1);

namespace App\Services;

use PDO;

final class AuthService
{
    public function __construct(private PDO $pdo)
    {
    }

    public function login(string $email, string $password): ?array
    {
        $stmt = $this->pdo->prepare('SELECT id, name, email, password_hash FROM users WHERE email = :email LIMIT 1');
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($password, $user['password_hash'])) {
            return null;
        }

        return [
            'id' => (int) $user['id'],
            'name' => (string) $user['name'],
            'email' => (string) $user['email'],
        ];
    }

    public function createAdmin(string $name, string $email, string $password): int
    {
        $hash = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $this->pdo->prepare(
            'INSERT INTO users (name, email, password_hash) VALUES (:name, :email, :password_hash)'
        );

        $stmt->execute([
            'name' => $name,
            'email' => $email,
            'password_hash' => $hash,
        ]);

        return (int) $this->pdo->lastInsertId();
    }
}
