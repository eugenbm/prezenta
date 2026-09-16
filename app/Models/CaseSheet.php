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

    /** O pagină de fișe (cele mai recente întâi), pentru afișare paginată. */
    public static function paginate(int $limit, int $offset): array
    {
        $stmt = Database::connection()->query(
            'SELECT * FROM case_sheets ORDER BY created_at DESC, id DESC '
            . 'LIMIT ' . (int) $limit . ' OFFSET ' . max(0, $offset)
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

    /**
     * Statistici agregate pentru dashboard-ul fișelor de caz.
     * Cheile temporale se bazează pe data intervenției (data_fisa).
     */
    public static function statistics(): array
    {
        $db = Database::connection();

        $total = (int) $db->query('SELECT COUNT(*) FROM case_sheets')->fetchColumn();

        $thisMonth = (int) $db->query(
            "SELECT COUNT(*) FROM case_sheets WHERE data_fisa IS NOT NULL AND DATE_FORMAT(data_fisa, '%Y-%m') = DATE_FORMAT(CURDATE(), '%Y-%m')"
        )->fetchColumn();

        $thisYear = (int) $db->query(
            "SELECT COUNT(*) FROM case_sheets WHERE data_fisa IS NOT NULL AND YEAR(data_fisa) = YEAR(CURDATE())"
        )->fetchColumn();

        $multipleVictims = (int) $db->query(
            "SELECT COUNT(*) FROM case_sheets WHERE victime_multiple = 'Da'"
        )->fetchColumn();

        // Numărul total de victime = victimele principale nominalizate + victimele suplimentare.
        $primaryVictims = (int) $db->query(
            "SELECT COUNT(*) FROM case_sheets WHERE TRIM(victima_nume) <> ''"
        )->fetchColumn();
        $extraVictims = 0;
        try {
            $extraVictims = (int) $db->query('SELECT COUNT(*) FROM case_sheet_victims')->fetchColumn();
        } catch (\PDOException $e) {
            // Tabelul victimelor suplimentare poate lipsi dacă migrarea nu a fost rulată.
        }

        return [
            'total' => $total,
            'this_month' => $thisMonth,
            'this_year' => $thisYear,
            'prev_year' => self::countForYear(-1),
            'multiple_victims' => $multipleVictims,
            'total_victims' => $primaryVictims + $extraVictims,
            'avg_intervention_minutes' => self::avgInterventionMinutes(),
            'avg_victim_age' => self::avgVictimAge(),
            'by_season' => self::groupCount('sezon'),
            'by_evacuation' => self::groupCount('mod_evacuare'),
            'by_event_type' => self::groupCount('tip_eveniment', 8),
            'by_activity_type' => self::groupCount('tip_activitate', 8),
            'by_alarm_source' => self::groupCount('alarmare_prin'),
            'by_weekday' => self::countByWeekday(),
            'by_hour' => self::countByHour(),
            'age_groups' => self::victimAgeGroups(),
            'by_month' => self::countByMonth(12),
            'by_sex' => self::victimSexDistribution(),
            'top_rescuers' => self::topRescuers(10),
        ];
    }

    /** Numărul de fișe pentru anul curent + $offset (ex. -1 = anul precedent). */
    private static function countForYear(int $offset): int
    {
        $stmt = Database::connection()->prepare(
            'SELECT COUNT(*) FROM case_sheets WHERE data_fisa IS NOT NULL AND YEAR(data_fisa) = YEAR(CURDATE()) + :off'
        );
        $stmt->execute(['off' => $offset]);
        return (int) $stmt->fetchColumn();
    }

    /** Durata medie a intervenției în minute (de la alarmare la revenirea la bază). */
    private static function avgInterventionMinutes(): ?int
    {
        $avg = Database::connection()->query(
            'SELECT AVG(TIMESTAMPDIFF(MINUTE, '
            . "TIMESTAMP(alarmare_data, COALESCE(alarmare_ora, '00:00:00')), "
            . "TIMESTAMP(revenire_data, COALESCE(revenire_ora, '00:00:00')))) "
            . 'FROM case_sheets '
            . 'WHERE alarmare_data IS NOT NULL AND revenire_data IS NOT NULL '
            . "AND TIMESTAMP(revenire_data, COALESCE(revenire_ora, '00:00:00')) "
            . ">= TIMESTAMP(alarmare_data, COALESCE(alarmare_ora, '00:00:00'))"
        )->fetchColumn();

        return $avg === null || $avg === false ? null : (int) round((float) $avg);
    }

    /** Vârsta medie a victimelor (principale + suplimentare). */
    private static function avgVictimAge(): ?int
    {
        $sql = 'SELECT AVG(age) FROM ('
            . "SELECT CAST(victima_varsta AS UNSIGNED) age FROM case_sheets WHERE victima_varsta REGEXP '^[0-9]+$' "
            . 'UNION ALL '
            . "SELECT CAST(varsta AS UNSIGNED) FROM case_sheet_victims WHERE varsta REGEXP '^[0-9]+$'"
            . ') t';
        $fallback = "SELECT AVG(CAST(victima_varsta AS UNSIGNED)) FROM case_sheets WHERE victima_varsta REGEXP '^[0-9]+$'";

        try {
            $avg = Database::connection()->query($sql)->fetchColumn();
        } catch (\PDOException $e) {
            $avg = Database::connection()->query($fallback)->fetchColumn();
        }
        return $avg === null || $avg === false ? null : (int) round((float) $avg);
    }

    /** Distribuție pe grupe de vârstă a victimelor (principale + suplimentare). */
    private static function victimAgeGroups(): array
    {
        $inner = "SELECT CAST(victima_varsta AS UNSIGNED) age FROM case_sheets WHERE victima_varsta REGEXP '^[0-9]+$' "
            . 'UNION ALL '
            . "SELECT CAST(varsta AS UNSIGNED) FROM case_sheet_victims WHERE varsta REGEXP '^[0-9]+$'";
        $fallback = "SELECT CAST(victima_varsta AS UNSIGNED) age FROM case_sheets WHERE victima_varsta REGEXP '^[0-9]+$'";

        $sql = 'SELECT '
            . 'SUM(age < 18) g1, '
            . 'SUM(age BETWEEN 18 AND 30) g2, '
            . 'SUM(age BETWEEN 31 AND 45) g3, '
            . 'SUM(age BETWEEN 46 AND 60) g4, '
            . 'SUM(age > 60) g5 '
            . 'FROM (%s) t';

        try {
            $row = Database::connection()->query(sprintf($sql, $inner))->fetch();
        } catch (\PDOException $e) {
            $row = Database::connection()->query(sprintf($sql, $fallback))->fetch();
        }

        return [
            '0–17 ani' => (int) ($row['g1'] ?? 0),
            '18–30 ani' => (int) ($row['g2'] ?? 0),
            '31–45 ani' => (int) ($row['g3'] ?? 0),
            '46–60 ani' => (int) ($row['g4'] ?? 0),
            'peste 60 ani' => (int) ($row['g5'] ?? 0),
        ];
    }

    /** Distribuția fișelor pe zilele săptămânii (Luni → Duminică). */
    private static function countByWeekday(): array
    {
        $rows = Database::connection()->query(
            'SELECT DAYOFWEEK(data_fisa) AS dow, COUNT(*) AS total '
            . 'FROM case_sheets WHERE data_fisa IS NOT NULL GROUP BY dow'
        )->fetchAll();

        // DAYOFWEEK: 1=Duminică … 7=Sâmbătă.
        $labels = [2 => 'Luni', 3 => 'Marți', 4 => 'Miercuri', 5 => 'Joi', 6 => 'Vineri', 7 => 'Sâmbătă', 1 => 'Duminică'];
        $counts = [];
        foreach ($rows as $r) {
            $counts[(int) $r['dow']] = (int) $r['total'];
        }

        $out = [];
        foreach ($labels as $dow => $label) {
            $out[$label] = $counts[$dow] ?? 0;
        }
        return $out;
    }

    /** Distribuția alarmărilor pe intervale orare (după ora de alarmare). */
    private static function countByHour(): array
    {
        $rows = Database::connection()->query(
            'SELECT HOUR(alarmare_ora) AS h, COUNT(*) AS total '
            . 'FROM case_sheets WHERE alarmare_ora IS NOT NULL GROUP BY h'
        )->fetchAll();

        $counts = [];
        foreach ($rows as $r) {
            $counts[(int) $r['h']] = (int) $r['total'];
        }

        $buckets = [
            '00–06' => [0, 6],
            '06–12' => [6, 12],
            '12–18' => [12, 18],
            '18–24' => [18, 24],
        ];
        $out = [];
        foreach ($buckets as $label => [$from, $to]) {
            $sum = 0;
            for ($h = $from; $h < $to; $h++) {
                $sum += $counts[$h] ?? 0;
            }
            $out[$label] = $sum;
        }
        return $out;
    }

    /** Cei mai activi salvatori (câmpul salvatori e text cu nume separate prin virgulă). */
    private static function topRescuers(int $limit): array
    {
        $values = Database::connection()->query(
            "SELECT salvatori FROM case_sheets WHERE salvatori IS NOT NULL AND TRIM(salvatori) <> ''"
        )->fetchAll(\PDO::FETCH_COLUMN);

        $counts = [];
        foreach ($values as $value) {
            foreach (explode(',', (string) $value) as $name) {
                $name = trim($name);
                if ($name === '') {
                    continue;
                }
                $counts[$name] = ($counts[$name] ?? 0) + 1;
            }
        }
        arsort($counts);
        return array_slice($counts, 0, $limit, true);
    }

    /** Distribuție pe o coloană (ignoră valorile goale), opțional limitată la primele N. */
    private static function groupCount(string $column, ?int $limit = null): array
    {
        $sql = "SELECT {$column} AS label, COUNT(*) AS total FROM case_sheets "
            . "WHERE {$column} IS NOT NULL AND TRIM({$column}) <> '' "
            . "GROUP BY {$column} ORDER BY total DESC, label ASC";
        if ($limit !== null) {
            $sql .= ' LIMIT ' . (int) $limit;
        }
        $rows = Database::connection()->query($sql)->fetchAll();

        $out = [];
        foreach ($rows as $r) {
            $out[(string) $r['label']] = (int) $r['total'];
        }
        return $out;
    }

    /** Numărul de fișe pe ultimele $months luni (după data_fisa), cronologic. */
    private static function countByMonth(int $months): array
    {
        $rows = Database::connection()->query(
            "SELECT DATE_FORMAT(data_fisa, '%Y-%m') AS ym, COUNT(*) AS total "
            . 'FROM case_sheets WHERE data_fisa IS NOT NULL '
            . 'GROUP BY ym'
        )->fetchAll();

        $counts = [];
        foreach ($rows as $r) {
            $counts[(string) $r['ym']] = (int) $r['total'];
        }

        $out = [];
        $cursor = new \DateTimeImmutable('first day of this month');
        for ($i = $months - 1; $i >= 0; $i--) {
            $ym = $cursor->modify("-{$i} month")->format('Y-m');
            $out[$ym] = $counts[$ym] ?? 0;
        }
        return $out;
    }

    /** Distribuția pe sex a victimelor (principale + suplimentare). */
    private static function victimSexDistribution(): array
    {
        $out = ['M' => 0, 'F' => 0];
        try {
            $rows = Database::connection()->query(
                'SELECT sex, COUNT(*) AS total FROM ('
                . "SELECT victima_sex AS sex FROM case_sheets WHERE victima_sex IN ('M', 'F') "
                . 'UNION ALL '
                . "SELECT sex FROM case_sheet_victims WHERE sex IN ('M', 'F')"
                . ') t GROUP BY sex'
            )->fetchAll();
        } catch (\PDOException $e) {
            $rows = Database::connection()->query(
                "SELECT victima_sex AS sex, COUNT(*) AS total FROM case_sheets WHERE victima_sex IN ('M', 'F') GROUP BY victima_sex"
            )->fetchAll();
        }
        foreach ($rows as $r) {
            $out[(string) $r['sex']] = (int) $r['total'];
        }
        return $out;
    }
}
