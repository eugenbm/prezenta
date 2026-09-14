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
}
