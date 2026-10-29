<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

final class InventoryItem
{
    /** Toate obiectele, cu cantitatea atribuită calculată. */
    public static function all(): array
    {
        $stmt = Database::connection()->query(
            'SELECT i.*, COALESCE(a.assigned, 0) AS assigned_quantity,
                    c.first_name AS creator_first_name, c.last_name AS creator_last_name, c.role AS creator_role
             FROM inventory_items i
             LEFT JOIN (
                 SELECT item_id, SUM(quantity) AS assigned
                 FROM inventory_assignments GROUP BY item_id
             ) a ON a.item_id = i.id
             LEFT JOIN users c ON c.id = i.created_by
             ORDER BY i.name ASC'
        );
        return $stmt->fetchAll();
    }

    /** O pagină de obiecte (cele mai recente întâi), cu cantitatea atribuită calculată. */
    public static function paginate(int $limit, int $offset): array
    {
        $limit = max(1, $limit);
        $offset = max(0, $offset);
        $stmt = Database::connection()->query(
            'SELECT i.*, COALESCE(a.assigned, 0) AS assigned_quantity,
                    c.first_name AS creator_first_name, c.last_name AS creator_last_name, c.role AS creator_role
             FROM inventory_items i
             LEFT JOIN (
                 SELECT item_id, SUM(quantity) AS assigned
                 FROM inventory_assignments GROUP BY item_id
             ) a ON a.item_id = i.id
             LEFT JOIN users c ON c.id = i.created_by
             ORDER BY i.id DESC
             LIMIT ' . $limit . ' OFFSET ' . $offset
        );
        return $stmt->fetchAll();
    }

    /** Un obiect, cu cantitatea atribuită calculată. */
    public static function find(int $id): ?array
    {
        $stmt = Database::connection()->prepare(
            'SELECT i.*, COALESCE(a.assigned, 0) AS assigned_quantity
             FROM inventory_items i
             LEFT JOIN (
                 SELECT item_id, SUM(quantity) AS assigned
                 FROM inventory_assignments GROUP BY item_id
             ) a ON a.item_id = i.id
             WHERE i.id = :id LIMIT 1'
        );
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function countItems(): int
    {
        return (int) Database::connection()->query('SELECT COUNT(*) FROM inventory_items')->fetchColumn();
    }

    public static function create(array $data): int
    {
        $stmt = Database::connection()->prepare(
            'INSERT INTO inventory_items (name, description, total_quantity, created_by)
             VALUES (:name, :description, :total_quantity, :created_by)'
        );
        $stmt->execute([
            'name' => $data['name'],
            'description' => $data['description'],
            'total_quantity' => $data['total_quantity'],
            'created_by' => $data['created_by'] ?? null,
        ]);

        return (int) Database::connection()->lastInsertId();
    }

    public static function update(int $id, array $data): void
    {
        $stmt = Database::connection()->prepare(
            'UPDATE inventory_items SET name = :name, description = :description, total_quantity = :total_quantity
             WHERE id = :id'
        );
        $stmt->execute([
            'name' => $data['name'],
            'description' => $data['description'],
            'total_quantity' => $data['total_quantity'],
            'id' => $id,
        ]);
    }

    public static function delete(int $id): void
    {
        $stmt = Database::connection()->prepare('DELETE FROM inventory_items WHERE id = :id');
        $stmt->execute(['id' => $id]);
    }
}
