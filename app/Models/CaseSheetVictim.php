<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

final class CaseSheetVictim
{
    /** Coloanele completabile pentru o victimă suplimentară. */
    private const COLUMNS = [
        'nume', 'varsta', 'sex', 'judet', 'tara', 'stare', 'contact',
        'localizare_afectiune', 'finalitate_caz',
    ];

    /**
     * Salvează victimele suplimentare ale unei fișe de caz.
     * @param array<int, array<string, mixed>> $victims
     */
    public static function createMany(int $caseSheetId, array $victims): void
    {
        if (!$victims) {
            return;
        }

        $columns = array_merge(['case_sheet_id', 'position'], self::COLUMNS);
        $placeholders = ':' . implode(', :', $columns);
        $stmt = Database::connection()->prepare(
            'INSERT INTO case_sheet_victims (' . implode(', ', $columns) . ") VALUES ({$placeholders})"
        );

        $position = 1;
        foreach ($victims as $victim) {
            $params = [
                'case_sheet_id' => $caseSheetId,
                'position' => $position++,
            ];
            foreach (self::COLUMNS as $col) {
                $params[$col] = $victim[$col] ?? '';
            }
            $stmt->execute($params);
        }
    }

    /** Victimele suplimentare ale unei fișe de caz, în ordinea introducerii. */
    public static function forSheet(int $caseSheetId): array
    {
        $stmt = Database::connection()->prepare(
            'SELECT * FROM case_sheet_victims WHERE case_sheet_id = :id ORDER BY position ASC, id ASC'
        );
        $stmt->execute(['id' => $caseSheetId]);
        return $stmt->fetchAll();
    }
}
