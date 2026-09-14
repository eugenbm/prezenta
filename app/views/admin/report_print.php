<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <title>Raport activități · Salvamont Zărnești</title>
    <style>
        body { font-family: Arial, Helvetica, sans-serif; color: #222; margin: 2rem; }
        header { display: flex; align-items: center; gap: 1rem; border-bottom: 3px solid #b3202c; padding-bottom: 1rem; margin-bottom: 1.5rem; }
        header img { height: 60px; }
        h1 { font-size: 1.4rem; margin: 0; }
        .meta { color: #555; font-size: .9rem; margin-bottom: 1.5rem; }
        table { width: 100%; border-collapse: collapse; font-size: .85rem; }
        th, td { border: 1px solid #ccc; padding: .4rem .6rem; text-align: left; }
        th { background: #f2ece9; }
        .totals { margin-top: 1.5rem; font-weight: bold; }
        .no-print { margin-bottom: 1.5rem; }
        @media print {
            .no-print { display: none; }
            body { margin: 0.5cm; }
        }
    </style>
</head>
<body>
    <div class="no-print">
        <button onclick="window.print()">Printează / Salvează ca PDF</button>
    </div>

    <header>
        <img src="<?= asset_url('img/logo-salvamont.svg') ?>" alt="Salvamont Zărnești">
        <div>
            <h1>Raport activități voluntari</h1>
            <div>Salvamont Zărnești — Registru digital de activități</div>
        </div>
    </header>

    <div class="meta">
        <div><strong>Perioada:</strong>
            <?= $filters['date_from'] ? format_date_ro($filters['date_from']) : 'nespecificat' ?>
            &ndash;
            <?= $filters['date_to'] ? format_date_ro($filters['date_to']) : 'nespecificat' ?>
        </div>
        <div><strong>Data generării:</strong> <?= date('d.m.Y H:i') ?></div>
    </div>

    <table>
        <thead>
            <tr><th>Voluntar</th><th>Tip</th><th>Data</th><th>Ore</th><th>Status</th></tr>
        </thead>
        <tbody>
        <?php foreach ($activities as $activity): ?>
            <tr>
                <td><?= e($activity['volunteer_first_name'] . ' ' . $activity['volunteer_last_name']) ?></td>
                <td><?= e($activity['type_name']) ?></td>
                <td><?= format_date_ro($activity['activity_date']) ?></td>
                <td><?= $activity['duration_hours'] !== null ? number_format((float) $activity['duration_hours'], 1) : '—' ?></td>
                <td><?= status_label($activity['status']) ?></td>
            </tr>
        <?php endforeach; ?>
        <?php if (!$activities): ?>
            <tr><td colspan="5">Nicio activitate găsită pentru filtrele selectate.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>

    <div class="totals">
        Total activități: <?= count($activities) ?> &middot;
        Activități aprobate: <?= $totalApproved ?> &middot;
        Total ore aprobate: <?= number_format($totalHours, 1) ?> h
    </div>
</body>
</html>
