<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

final class PasswordReset
{
    private const TTL_HOURS = 48;

    /**
     * Generează un token nou pentru utilizator (invalidând tokenurile anterioare)
     * și returnează valoarea brută (care ajunge în link, nu în baza de date).
     */
    public static function create(int $userId): string
    {
        self::deleteForUser($userId);

        $rawToken = bin2hex(random_bytes(32));
        $stmt = Database::connection()->prepare(
            'INSERT INTO password_resets (user_id, token_hash, expires_at)
             VALUES (:user_id, :token_hash, :expires_at)'
        );
        $stmt->execute([
            'user_id' => $userId,
            'token_hash' => hash('sha256', $rawToken),
            'expires_at' => date('Y-m-d H:i:s', time() + self::TTL_HOURS * 3600),
        ]);

        return $rawToken;
    }

    /** Returnează rândul valid (neexpirat, nefolosit) pentru un token brut, sau null. */
    public static function findValid(string $rawToken): ?array
    {
        if ($rawToken === '') {
            return null;
        }
        $stmt = Database::connection()->prepare(
            'SELECT * FROM password_resets
             WHERE token_hash = :token_hash AND used_at IS NULL AND expires_at > NOW()
             LIMIT 1'
        );
        $stmt->execute(['token_hash' => hash('sha256', $rawToken)]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function markUsed(int $id): void
    {
        $stmt = Database::connection()->prepare(
            'UPDATE password_resets SET used_at = NOW() WHERE id = :id'
        );
        $stmt->execute(['id' => $id]);
    }

    public static function deleteForUser(int $userId): void
    {
        $stmt = Database::connection()->prepare('DELETE FROM password_resets WHERE user_id = :user_id');
        $stmt->execute(['user_id' => $userId]);
    }
}
