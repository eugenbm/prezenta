<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

final class Contract
{
    /** Contractul semnat de un voluntar (cel mult unul per utilizator). */
    public static function forUser(int $userId): ?array
    {
        $stmt = Database::connection()->prepare(
            'SELECT * FROM contracts WHERE user_id = :user_id LIMIT 1'
        );
        $stmt->execute(['user_id' => $userId]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /** Un contract împreună cu datele voluntarului (pentru admin). */
    public static function find(int $id): ?array
    {
        $stmt = Database::connection()->prepare(
            'SELECT c.*, u.first_name, u.last_name, u.email, u.role
             FROM contracts c JOIN users u ON u.id = c.user_id
             WHERE c.id = :id LIMIT 1'
        );
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /** Toate contractele semnate, cu datele voluntarului, cele mai recente întâi. */
    public static function all(): array
    {
        $stmt = Database::connection()->query(
            'SELECT c.*, u.first_name, u.last_name, u.email, u.role
             FROM contracts c JOIN users u ON u.id = c.user_id
             ORDER BY c.signed_at DESC, c.id DESC'
        );
        return $stmt->fetchAll();
    }

    public static function countSigned(): int
    {
        return (int) Database::connection()->query('SELECT COUNT(*) FROM contracts')->fetchColumn();
    }

    /**
     * Salvează (sau actualizează, dacă voluntarul re-semnează) contractul.
     * Semnătura se stochează ca base64 JPEG (fără prefixul „data:").
     */
    public static function save(int $userId, string $type, string $name, string $signatureBase64, ?string $ip): void
    {
        $stmt = Database::connection()->prepare(
            'INSERT INTO contracts (user_id, contract_type, volunteer_name, signature_data, ip_address)
             VALUES (:user_id, :contract_type, :volunteer_name, :signature_data, :ip_address)
             ON DUPLICATE KEY UPDATE
                contract_type = VALUES(contract_type),
                volunteer_name = VALUES(volunteer_name),
                signature_data = VALUES(signature_data),
                ip_address = VALUES(ip_address),
                signed_at = CURRENT_TIMESTAMP'
        );
        $stmt->execute([
            'user_id' => $userId,
            'contract_type' => $type,
            'volunteer_name' => $name,
            'signature_data' => $signatureBase64,
            'ip_address' => $ip,
        ]);
    }
}
