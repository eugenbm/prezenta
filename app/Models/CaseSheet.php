<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

final class CaseSheet
{
    /** Coloanele completabile din formular (created_at e generat de DB). */
    private const COLUMNS = [
        'nr_fisa', 'data_fisa', 'judet', 'serviciu', 'formatie',
        'alarmare_prin', 'alarmare_data', 'alarmare_ora',
        'tip_eveniment', 'responsabilitate', 'interventie', 'coordonator', 'salvatori',
        'locatie_judet', 'masiv_montan', 'sezon', 'loc_producere', 'tip_activitate', 'numar_persoane',
        'victima_nume', 'victima_varsta', 'victima_sex', 'victima_judet', 'victima_tara', 'victima_stare', 'victima_contact',
        'localizare_afectiune', 'finalitate_caz',
        'predare_data', 'predare_ora', 'transport', 'mod_evacuare', 'predata_catre', 'victime_multiple',
        'revenire_data', 'revenire_ora', 'intocmit', 'created_by',
    ];

    /** Următorul număr de fișă (cel mai mare nr_fisa numeric + 1). */
    public static function nextNumber(): int
    {
        $stmt = Database::connection()->query(
            "SELECT COALESCE(MAX(CAST(nr_fisa AS UNSIGNED)), 0) FROM case_sheets WHERE nr_fisa REGEXP '^[0-9]+$'"
        );
        return (int) $stmt->fetchColumn() + 1;
    }

    public static function create(array $data): int
    {
        $columns = implode(', ', self::COLUMNS);
        $placeholders = ':' . implode(', :', self::COLUMNS);

        $params = [];
        foreach (self::COLUMNS as $col) {
            $params[$col] = $data[$col] ?? null;
        }

        $stmt = Database::connection()->prepare(
            "INSERT INTO case_sheets ({$columns}) VALUES ({$placeholders})"
        );
        $stmt->execute($params);

        return (int) Database::connection()->lastInsertId();
    }

    public static function all(): array
    {
        $stmt = Database::connection()->query(
            'SELECT * FROM case_sheets ORDER BY created_at DESC, id DESC'
        );
        return $stmt->fetchAll();
    }

    public static function find(int $id): ?array
    {
        $stmt = Database::connection()->prepare('SELECT * FROM case_sheets WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function delete(int $id): void
    {
        $stmt = Database::connection()->prepare('DELETE FROM case_sheets WHERE id = :id');
        $stmt->execute(['id' => $id]);
    }
}
