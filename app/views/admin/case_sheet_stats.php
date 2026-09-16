<?php
    /** @var array $stats */

    // Redă o listă de bare orizontale pentru o distribuție (label => count).
    $barList = static function (array $data, string $emptyText, string $barClass = 'bg-danger'): string {
        if (!$data || array_sum($data) === 0) {
            return '<div class="text-muted text-center py-4">' . e($emptyText) . '</div>';
        }
        $max = max($data);
        $html = '<div class="d-flex flex-column gap-2">';
        foreach ($data as $label => $count) {
            $pct = $max > 0 ? (int) round($count / $max * 100) : 0;
            $html .= '<div>'
                . '<div class="d-flex justify-content-between small mb-1">'
                . '<span>' . e((string) $label) . '</span>'
                . '<span class="fw-semibold">' . (int) $count . '</span>'
                . '</div>'
                . '<div class="progress" style="height: 8px;">'
                . '<div class="progress-bar ' . e($barClass) . '" role="progressbar" style="width: ' . $pct . '%"></div>'
                . '</div>'
                . '</div>';
        }
        return $html . '</div>';
    };

    // Formatează o durată în minute ca „Xh Ym”.
    $fmtDuration = static function (?int $minutes): string {
        if ($minutes === null) {
            return '—';
        }
        $h = intdiv($minutes, 60);
        $m = $minutes % 60;
        return $h > 0 ? $h . 'h ' . $m . 'm' : $m . 'm';
    };

    $roMonths = ['', 'ian.', 'feb.', 'mar.', 'apr.', 'mai', 'iun.', 'iul.', 'aug.', 'sep.', 'oct.', 'nov.', 'dec.'];
    $monthMax = $stats['by_month'] ? max($stats['by_month']) : 0;
    $yearDelta = (int) $stats['this_year'] - (int) $stats['prev_year'];
?>

<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
    <h1 class="h3 mb-0">Statistici fișe de caz</h1>
    <div class="d-flex gap-2 flex-wrap">
        <a href="<?= route_url('?route=admin/case-sheets') ?>" class="btn btn-outline-secondary btn-sm">Lista fișelor</a>
        <a href="<?= route_url('?route=admin/case-sheet/create') ?>" class="btn btn-primary btn-sm">+ Fișă nouă</a>
    </div>
</div>

<div class="row g-3 mb-3">
    <div class="col-6 col-md-3">
        <div class="stat-card h-100">
            <div class="stat-value"><?= (int) $stats['total'] ?></div>
            <div class="stat-label">Total fișe</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card stat-success h-100">
            <div class="stat-value"><?= (int) $stats['this_year'] ?></div>
            <div class="stat-label">
                Anul curent
                <?php if ($stats['prev_year'] > 0 || $yearDelta !== 0): ?>
                    <span class="ms-1 <?= $yearDelta >= 0 ? 'text-success' : 'text-danger' ?>"><?= $yearDelta >= 0 ? '▲' : '▼' ?> <?= abs($yearDelta) ?> vs. an precedent</span>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card stat-pending h-100">
            <div class="stat-value"><?= (int) $stats['this_month'] ?></div>
            <div class="stat-label">Luna curentă</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card stat-danger h-100">
            <div class="stat-value"><?= (int) $stats['multiple_victims'] ?></div>
            <div class="stat-label">Cu victime multiple</div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-6 col-md-4">
        <div class="stat-card h-100">
            <div class="stat-value"><?= (int) $stats['total_victims'] ?></div>
            <div class="stat-label">Total victime</div>
        </div>
    </div>
    <div class="col-6 col-md-4">
        <div class="stat-card h-100">
            <div class="stat-value"><?= $stats['avg_victim_age'] !== null ? (int) $stats['avg_victim_age'] . ' ani' : '—' ?></div>
            <div class="stat-label">Vârsta medie victime</div>
        </div>
    </div>
    <div class="col-12 col-md-4">
        <div class="stat-card h-100">
            <div class="stat-value"><?= e($fmtDuration($stats['avg_intervention_minutes'])) ?></div>
            <div class="stat-label">Durată medie intervenție</div>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card h-100">
            <div class="card-header">Fișe pe lună (ultimele 12 luni)</div>
            <div class="card-body">
                <?php if ($monthMax === 0): ?>
                    <div class="text-muted text-center py-4">Nicio fișă cu dată în această perioadă.</div>
                <?php else: ?>
                    <div class="d-flex align-items-end justify-content-between gap-1" style="height: 220px;">
                        <?php foreach ($stats['by_month'] as $ym => $count): ?>
                            <?php
                                [$y, $m] = explode('-', $ym);
                                $h = $monthMax > 0 ? max(2, (int) round($count / $monthMax * 100)) : 2;
                            ?>
                            <div class="d-flex flex-column align-items-center justify-content-end h-100 flex-fill" title="<?= e($roMonths[(int) $m] . ' ' . $y) ?>: <?= (int) $count ?>">
                                <div class="small fw-semibold mb-1"><?= (int) $count ?></div>
                                <div class="w-100 rounded-top" style="background: #841821; height: <?= $h ?>%; min-height: 2px;"></div>
                                <div class="text-muted mt-1" style="font-size: .7rem;"><?= e($roMonths[(int) $m]) ?></div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card h-100">
            <div class="card-header">Distribuție pe sex victime</div>
            <div class="card-body">
                <?= $barList(array_filter($stats['by_sex']), 'Fără date despre sex.', 'bg-primary') ?>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mt-0">
    <div class="col-lg-4">
        <div class="card h-100">
            <div class="card-header">Grupe de vârstă victime</div>
            <div class="card-body">
                <?= $barList($stats['age_groups'], 'Fără date despre vârstă.', 'bg-primary') ?>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card h-100">
            <div class="card-header">Pe zi a săptămânii</div>
            <div class="card-body">
                <?= $barList($stats['by_weekday'], 'Fără date despre dată.', 'bg-secondary') ?>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card h-100">
            <div class="card-header">Interval orar alarmare</div>
            <div class="card-body">
                <?= $barList($stats['by_hour'], 'Fără ore de alarmare.', 'bg-warning') ?>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mt-0">
    <div class="col-lg-4">
        <div class="card h-100">
            <div class="card-header">Tip eveniment (top 8)</div>
            <div class="card-body">
                <?= $barList($stats['by_event_type'], 'Fără date despre tipul evenimentului.', 'bg-danger') ?>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card h-100">
            <div class="card-header">Activitate generatoare (top 8)</div>
            <div class="card-body">
                <?= $barList($stats['by_activity_type'], 'Fără date despre activitate.', 'bg-info') ?>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card h-100">
            <div class="card-header">Sursă alarmare</div>
            <div class="card-body">
                <?= $barList($stats['by_alarm_source'], 'Fără date despre alarmare.', 'bg-success') ?>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mt-0">
    <div class="col-lg-4">
        <div class="card h-100">
            <div class="card-header">Pe sezon</div>
            <div class="card-body">
                <?= $barList($stats['by_season'], 'Fără date despre sezon.', 'bg-info') ?>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card h-100">
            <div class="card-header">Mod de evacuare</div>
            <div class="card-body">
                <?= $barList($stats['by_evacuation'], 'Fără date despre evacuare.', 'bg-success') ?>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card h-100">
            <div class="card-header">Salvatori cei mai activi (top 10)</div>
            <div class="card-body">
                <?= $barList($stats['top_rescuers'], 'Niciun salvator înregistrat.', 'bg-dark') ?>
            </div>
        </div>
    </div>
</div>
