<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

final class AuditLog
{
    public static function record(?int $actorId, string $action, ?string $targetType = null, ?int $targetId = null, ?string $details = null): void
    {
        $stmt = Database::connection()->prepare(
            'INSERT INTO audit_log (actor_id, action, target_type, target_id, details)
             VALUES (:actor_id, :action, :target_type, :target_id, :details)'
        );
        $stmt->execute([
            'actor_id' => $actorId,
            'action' => $action,
            'target_type' => $targetType,
            'target_id' => $targetId,
            'details' => $details,
        ]);
    }
}
