<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

final class ActivityType
{
    public static function allActive(): array
    {
        $stmt = Database::connection()->query(
            'SELECT * FROM activity_types WHERE is_active = 1 ORDER BY name ASC'
        );
        return $stmt->fetchAll();
    }

    public static function all(): array
    {
        $stmt = Database::connection()->query('SELECT * FROM activity_types ORDER BY name ASC');
        return $stmt->fetchAll();
    }

    public static function find(int $id): ?array
    {
        $stmt = Database::connection()->prepare('SELECT * FROM activity_types WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function findByName(string $name): ?array
    {
        $stmt = Database::connection()->prepare('SELECT * FROM activity_types WHERE name = :name LIMIT 1');
        $stmt->execute(['name' => $name]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /** Găsește tipul după nume sau îl creează (activ) dacă lipsește. */
    public static function findOrCreateByName(string $name): ?array
    {
        $existing = self::findByName($name);
        if ($existing) {
            return $existing;
        }
        try {
            $stmt = Database::connection()->prepare(
                'INSERT INTO activity_types (name, is_active) VALUES (:name, 1)'
            );
            $stmt->execute(['name' => $name]);
        } catch (\PDOException $e) {
            // Poate fi creat concurent sau tabelul poate cere coloane suplimentare.
        }
        return self::findByName($name);
    }
}
