<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;

final class User
{
    public static function find(int $id): ?array
    {
        $stmt = Database::connection()->prepare('SELECT * FROM users WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function findByUsernameOrEmail(string $identifier): ?array
    {
        // Placeholder-uri distincte pentru fiecare apariție: MySQL cu prepared
        // statements native (fără emulare) nu acceptă același nume de două ori.
        $stmt = Database::connection()->prepare(
            'SELECT * FROM users WHERE username = :username OR email = :email LIMIT 1'
        );
        $stmt->execute(['username' => $identifier, 'email' => $identifier]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function existsByUsernameOrEmail(string $username, string $email, ?int $excludeId = null): bool
    {
        $sql = 'SELECT COUNT(*) FROM users WHERE (username = :username OR email = :email)';
        $params = ['username' => $username, 'email' => $email];
        if ($excludeId !== null) {
            $sql .= ' AND id != :id';
            $params['id'] = $excludeId;
        }
        $stmt = Database::connection()->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn() > 0;
    }

    public static function countAdmins(): int
    {
        $stmt = Database::connection()->query("SELECT COUNT(*) FROM users WHERE role = 'admin'");
        return (int) $stmt->fetchColumn();
    }

    public static function countActiveVolunteers(): int
    {
        $stmt = Database::connection()->query(
            "SELECT COUNT(*) FROM users WHERE role <> 'admin' AND is_active = 1"
        );
        return (int) $stmt->fetchColumn();
    }

    public static function activeAdmins(): array
    {
        $stmt = Database::connection()->query(
            "SELECT * FROM users WHERE role = 'admin' AND is_active = 1"
        );
        return $stmt->fetchAll();
    }

    public static function all(): array
    {
        $stmt = Database::connection()->query(
            "SELECT * FROM users ORDER BY role DESC, last_name ASC, first_name ASC"
        );
        return $stmt->fetchAll();
    }

    public static function create(array $data): int
    {
        $stmt = Database::connection()->prepare(
            'INSERT INTO users (first_name, last_name, email, username, password_hash, role, is_active)
             VALUES (:first_name, :last_name, :email, :username, :password_hash, :role, :is_active)'
        );
        $stmt->execute([
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'email' => $data['email'],
            'username' => $data['username'],
            'password_hash' => $data['password_hash'],
            'role' => $data['role'],
            'is_active' => $data['is_active'] ?? 1,
        ]);

        return (int) Database::connection()->lastInsertId();
    }

    public static function update(int $id, array $data): void
    {
        $stmt = Database::connection()->prepare(
            'UPDATE users SET first_name = :first_name, last_name = :last_name, email = :email,
             username = :username, role = :role WHERE id = :id'
        );
        $stmt->execute([
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'email' => $data['email'],
            'username' => $data['username'],
            'role' => $data['role'],
            'id' => $id,
        ]);
    }

    public static function setActive(int $id, bool $active): void
    {
        $stmt = Database::connection()->prepare('UPDATE users SET is_active = :active WHERE id = :id');
        $stmt->execute(['active' => $active ? 1 : 0, 'id' => $id]);
    }

    public static function updatePassword(int $id, string $passwordHash): void
    {
        $stmt = Database::connection()->prepare('UPDATE users SET password_hash = :hash WHERE id = :id');
        $stmt->execute(['hash' => $passwordHash, 'id' => $id]);
    }
}
