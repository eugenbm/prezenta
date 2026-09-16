<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;

final class Activity
{
    private const SELECT_WITH_JOINS = "
        SELECT a.*, t.name AS type_name,
               u.first_name AS volunteer_first_name, u.last_name AS volunteer_last_name
        FROM activities a
        INNER JOIN activity_types t ON t.id = a.activity_type_id
        INNER JOIN users u ON u.id = a.user_id
    ";

    public static function find(int $id): ?array
    {
        $stmt = Database::connection()->prepare(self::SELECT_WITH_JOINS . ' WHERE a.id = :id');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function create(array $data): int
    {
        $status = $data['status'] ?? 'pending';
        if (!in_array($status, ['pending', 'approved', 'rejected'], true)) {
            $status = 'pending';
        }

        $stmt = Database::connection()->prepare(
            'INSERT INTO activities
                (user_id, activity_type_id, activity_date, location, description, notes, status)
             VALUES
                (:user_id, :activity_type_id, :activity_date, :location, :description, :notes, :status)'
        );
        $stmt->execute([
            'user_id' => $data['user_id'],
            'activity_type_id' => $data['activity_type_id'],
            'activity_date' => $data['activity_date'],
            'location' => $data['location'] ?: null,
            'description' => $data['description'],
            'notes' => $data['notes'] ?: null,
            'status' => $status,
        ]);

        return (int) Database::connection()->lastInsertId();
    }

    public static function updateByOwner(int $id, int $userId, array $data): bool
    {
        $stmt = Database::connection()->prepare(
            "UPDATE activities SET
                activity_type_id = :activity_type_id,
                activity_date = :activity_date,
                location = :location,
                description = :description,
                notes = :notes
             WHERE id = :id AND user_id = :user_id AND status = 'pending'"
        );
        $stmt->execute([
            'activity_type_id' => $data['activity_type_id'],
            'activity_date' => $data['activity_date'],
            'location' => $data['location'] ?: null,
            'description' => $data['description'],
            'notes' => $data['notes'] ?: null,
            'id' => $id,
            'user_id' => $userId,
        ]);

        return $stmt->rowCount() > 0;
    }

    public static function updateByAdmin(int $id, array $data): void
    {
        $stmt = Database::connection()->prepare(
            'UPDATE activities SET
                activity_type_id = :activity_type_id,
                activity_date = :activity_date,
                location = :location,
                description = :description,
                notes = :notes
             WHERE id = :id'
        );
        $stmt->execute([
            'activity_type_id' => $data['activity_type_id'],
            'activity_date' => $data['activity_date'],
            'location' => $data['location'] ?: null,
            'description' => $data['description'],
            'notes' => $data['notes'] ?: null,
            'id' => $id,
        ]);
    }

    public static function approve(int $id, int $adminId): void
    {
        $db = Database::connection();
        $db->beginTransaction();
        try {
            $stmt = $db->prepare(
                "UPDATE activities SET status = 'approved', rejection_reason = NULL WHERE id = :id"
            );
            $stmt->execute(['id' => $id]);

            $stmt = $db->prepare(
                "INSERT INTO activity_approvals (activity_id, administrator_id, action, comment)
                 VALUES (:activity_id, :admin_id, 'approved', NULL)"
            );
            $stmt->execute(['activity_id' => $id, 'admin_id' => $adminId]);
            $db->commit();
        } catch (\Throwable $e) {
            $db->rollBack();
            throw $e;
        }
    }

    public static function reject(int $id, int $adminId, string $reason): void
    {
        $db = Database::connection();
        $db->beginTransaction();
        try {
            $stmt = $db->prepare(
                "UPDATE activities SET status = 'rejected', rejection_reason = :reason WHERE id = :id"
            );
            $stmt->execute(['reason' => $reason, 'id' => $id]);

            $stmt = $db->prepare(
                "INSERT INTO activity_approvals (activity_id, administrator_id, action, comment)
                 VALUES (:activity_id, :admin_id, 'rejected', :comment)"
            );
            $stmt->execute(['activity_id' => $id, 'admin_id' => $adminId, 'comment' => $reason]);
            $db->commit();
        } catch (\Throwable $e) {
            $db->rollBack();
            throw $e;
        }
    }

    public static function listForUser(int $userId, array $filters = []): array
    {
        $sql = self::SELECT_WITH_JOINS . ' WHERE a.user_id = :user_id';
        $params = ['user_id' => $userId];
        self::applyCommonFilters($sql, $params, $filters);
        $sql .= ' ORDER BY a.activity_date DESC, a.id DESC';

        $stmt = Database::connection()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function listForAdmin(array $filters = [], ?int $limit = null, int $offset = 0): array
    {
        $sql = self::SELECT_WITH_JOINS . ' WHERE 1 = 1';
        $params = [];
        if (!empty($filters['user_id'])) {
            $sql .= ' AND a.user_id = :user_id';
            $params['user_id'] = $filters['user_id'];
        }
        self::applyCommonFilters($sql, $params, $filters);
        $sql .= ' ORDER BY a.activity_date DESC, a.id DESC';
        if ($limit !== null) {
            $sql .= ' LIMIT ' . (int) $limit . ' OFFSET ' . max(0, $offset);
        }

        $stmt = Database::connection()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    private static function applyCommonFilters(string &$sql, array &$params, array $filters): void
    {
        if (!empty($filters['activity_type_id'])) {
            $sql .= ' AND a.activity_type_id = :activity_type_id';
            $params['activity_type_id'] = $filters['activity_type_id'];
        }
        if (!empty($filters['status'])) {
            $sql .= ' AND a.status = :status';
            $params['status'] = $filters['status'];
        }
        if (!empty($filters['date_from'])) {
            $sql .= ' AND a.activity_date >= :date_from';
            $params['date_from'] = $filters['date_from'];
        }
        if (!empty($filters['date_to'])) {
            $sql .= ' AND a.activity_date <= :date_to';
            $params['date_to'] = $filters['date_to'];
        }
    }

    public static function recentForUser(int $userId, int $limit = 5): array
    {
        $stmt = Database::connection()->prepare(
            self::SELECT_WITH_JOINS . ' WHERE a.user_id = :user_id ORDER BY a.created_at DESC LIMIT ' . (int) $limit
        );
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetchAll();
    }

    public static function recentForAdmin(int $limit = 8): array
    {
        $stmt = Database::connection()->prepare(
            self::SELECT_WITH_JOINS . ' ORDER BY a.created_at DESC, a.id DESC LIMIT ' . (int) $limit
        );
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public static function pendingForAdmin(int $limit = 10): array
    {
        $stmt = Database::connection()->prepare(
            self::SELECT_WITH_JOINS . " WHERE a.status = 'pending' ORDER BY a.created_at ASC LIMIT " . (int) $limit
        );
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /** Numărul de zile calendaristice distincte cu activitate aprobată, într-un an. */
    public static function approvedDaysInYear(int $userId, int $year): int
    {
        $stmt = Database::connection()->prepare(
            "SELECT COUNT(DISTINCT activity_date) FROM activities
             WHERE user_id = :user_id AND status = 'approved' AND YEAR(activity_date) = :year"
        );
        $stmt->execute(['user_id' => $userId, 'year' => $year]);
        return (int) $stmt->fetchColumn();
    }

    /** Hartă user_id => zile aprobate distincte în anul dat, pentru toți aspiranții. */
    public static function approvedDaysInYearForApplicants(int $year): array
    {
        $stmt = Database::connection()->prepare(
            "SELECT user_id, COUNT(DISTINCT activity_date) AS days
             FROM activities
             WHERE status = 'approved' AND YEAR(activity_date) = :year
             GROUP BY user_id"
        );
        $stmt->execute(['year' => $year]);

        $map = [];
        foreach ($stmt->fetchAll() as $row) {
            $map[(int) $row['user_id']] = (int) $row['days'];
        }
        return $map;
    }

    public static function countsForUser(int $userId): array
    {
        $stmt = Database::connection()->prepare(
            'SELECT status, COUNT(*) AS total FROM activities WHERE user_id = :user_id GROUP BY status'
        );
        $stmt->execute(['user_id' => $userId]);
        return self::normalizeCounts($stmt->fetchAll());
    }

    public static function countsGlobal(): array
    {
        $stmt = Database::connection()->query('SELECT status, COUNT(*) AS total FROM activities GROUP BY status');
        return self::normalizeCounts($stmt->fetchAll());
    }

    private static function normalizeCounts(array $rows): array
    {
        $counts = ['pending' => 0, 'approved' => 0, 'rejected' => 0];
        foreach ($rows as $row) {
            $counts[$row['status']] = (int) $row['total'];
        }
        $counts['total'] = array_sum($counts);
        return $counts;
    }

    public static function reportByType(array $filters = []): array
    {
        // Condițiile de dată merg în clauza ON (nu în WHERE), altfel LEFT JOIN
        // ar exclude tipurile de activitate fără nicio activitate în perioadă.
        $joinConditions = ['a.activity_type_id = t.id'];
        $params = [];
        if (!empty($filters['date_from'])) {
            $joinConditions[] = 'a.activity_date >= :date_from';
            $params['date_from'] = $filters['date_from'];
        }
        if (!empty($filters['date_to'])) {
            $joinConditions[] = 'a.activity_date <= :date_to';
            $params['date_to'] = $filters['date_to'];
        }

        $sql = "SELECT t.name AS type_name, COUNT(a.id) AS total,
                       SUM(CASE WHEN a.status = 'approved' THEN 1 ELSE 0 END) AS approved
                FROM activity_types t
                LEFT JOIN activities a ON " . implode(' AND ', $joinConditions) . '
                GROUP BY t.id, t.name ORDER BY t.name ASC';

        $stmt = Database::connection()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function reportByVolunteer(array $filters = []): array
    {
        // Condițiile de dată merg în clauza ON, altfel voluntarii fără activități
        // în perioada selectată ar dispărea din raport (LEFT JOIN ar deveni INNER JOIN).
        $joinConditions = ['a.user_id = u.id'];
        $params = [];
        if (!empty($filters['date_from'])) {
            $joinConditions[] = 'a.activity_date >= :date_from';
            $params['date_from'] = $filters['date_from'];
        }
        if (!empty($filters['date_to'])) {
            $joinConditions[] = 'a.activity_date <= :date_to';
            $params['date_to'] = $filters['date_to'];
        }

        $sql = "SELECT u.id AS user_id, u.first_name, u.last_name, COUNT(a.id) AS total,
                       SUM(CASE WHEN a.status = 'approved' THEN 1 ELSE 0 END) AS approved,
                       SUM(CASE WHEN a.status = 'pending' THEN 1 ELSE 0 END) AS pending,
                       SUM(CASE WHEN a.status = 'rejected' THEN 1 ELSE 0 END) AS rejected,
                       COUNT(DISTINCT CASE WHEN a.status = 'approved' THEN a.activity_date END) AS active_days
                FROM users u
                LEFT JOIN activities a ON " . implode(' AND ', $joinConditions) . "
                WHERE u.role <> 'admin'";
        $sql .= ' GROUP BY u.id, u.first_name, u.last_name ORDER BY u.last_name ASC, u.first_name ASC';

        $stmt = Database::connection()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
}
