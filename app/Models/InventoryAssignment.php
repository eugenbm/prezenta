<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

final class InventoryAssignment
{
    /** Atribuirile unui obiect, cu datele voluntarului. */
    public static function forItem(int $itemId): array
    {
        $stmt = Database::connection()->prepare(
            'SELECT a.*, u.first_name, u.last_name, u.role
             FROM inventory_assignments a
             JOIN users u ON u.id = a.user_id
             WHERE a.item_id = :item_id
             ORDER BY u.last_name ASC, u.first_name ASC'
        );
        $stmt->execute(['item_id' => $itemId]);
        return $stmt->fetchAll();
    }

    /** Obiectele atribuite unui voluntar, cu datele obiectului. */
    public static function forUser(int $userId): array
    {
        $stmt = Database::connection()->prepare(
            'SELECT a.*, i.name, i.description
             FROM inventory_assignments a
             JOIN inventory_items i ON i.id = a.item_id
             WHERE a.user_id = :user_id
             ORDER BY i.name ASC'
        );
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetchAll();
    }

    public static function find(int $id): ?array
    {
        $stmt = Database::connection()->prepare(
            'SELECT * FROM inventory_assignments WHERE id = :id LIMIT 1'
        );
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /** Numărul total de bucăți atribuite dintr-un obiect. */
    public static function assignedQuantity(int $itemId): int
    {
        $stmt = Database::connection()->prepare(
            'SELECT COALESCE(SUM(quantity), 0) FROM inventory_assignments WHERE item_id = :item_id'
        );
        $stmt->execute(['item_id' => $itemId]);
        return (int) $stmt->fetchColumn();
    }

    /**
     * Atribuie (sau adaugă la o atribuire existentă) un număr de bucăți unui
     * voluntar. Re-atribuirea aceluiași obiect aceluiași voluntar adună cantitatea.
     */
    public static function assign(int $itemId, int $userId, int $quantity, ?int $assignedBy): void
    {
        $stmt = Database::connection()->prepare(
            'INSERT INTO inventory_assignments (item_id, user_id, quantity, assigned_by)
             VALUES (:item_id, :user_id, :quantity, :assigned_by)
             ON DUPLICATE KEY UPDATE
                quantity = quantity + VALUES(quantity),
                assigned_by = VALUES(assigned_by),
                assigned_at = CURRENT_TIMESTAMP'
        );
        $stmt->execute([
            'item_id' => $itemId,
            'user_id' => $userId,
            'quantity' => $quantity,
            'assigned_by' => $assignedBy,
        ]);
    }

    public static function unassign(int $id): void
    {
        $stmt = Database::connection()->prepare('DELETE FROM inventory_assignments WHERE id = :id');
        $stmt->execute(['id' => $id]);
    }
}
