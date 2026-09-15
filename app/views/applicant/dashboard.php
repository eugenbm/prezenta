<h1 class="h3 mb-4">Bine ai venit, <?= e($currentUser['first_name']) ?>!</h1>

<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="stat-card">
            <div class="stat-value"><?= $counts['total'] ?></div>
            <div class="stat-label">Total activități</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card stat-success">
            <div class="stat-value"><?= $counts['approved'] ?></div>
            <div class="stat-label">Aprobate</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card stat-pending">
            <div class="stat-value"><?= $counts['pending'] ?></div>
            <div class="stat-label">În așteptare</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card stat-danger">
            <div class="stat-value"><?= $counts['rejected'] ?></div>
            <div class="stat-label">Respinse</div>
        </div>
    </div>
</div>

<?php $annualPct = min(100, (int) round($annualDays / max(1, $annualGoal) * 100)); ?>
<div class="row g-3 mb-4">
    <div class="col-md-5">
        <div class="stat-card h-100">
            <div class="stat-value"><?= $annualDays ?> / <?= $annualGoal ?></div>
            <div class="stat-label">Zile cu activitate în <?= $year ?> (obiectiv anual)</div>
            <div class="progress mt-2" style="height: 8px;">
                <div class="progress-bar <?= $annualDays >= $annualGoal ? 'bg-success' : '' ?>" role="progressbar" style="width: <?= $annualPct ?>%" aria-valuenow="<?= $annualDays ?>" aria-valuemin="0" aria-valuemax="<?= $annualGoal ?>"></div>
            </div>
            <div class="stat-label mt-1">
                <?php if ($annualDays >= $annualGoal): ?>
                    Obiectiv atins! 🎉
                <?php else: ?>
                    Îți mai trebuie <?= $annualGoal - $annualDays ?> zile.
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="col-md-7 d-flex align-items-center">
        <a href="<?= route_url('?route=applicant/activity/create') ?>" class="btn btn-primary btn-lg">+ Adaugă o activitate</a>
    </div>
</div>

<div class="card">
    <div class="card-header">Ultimele activități</div>
    <div class="card-body p-0">
        <div class="table-responsive">
        <table class="table table-hover table-stack mb-0">
            <thead>
                <tr><th>Data</th><th>Tip</th><th>Descriere</th><th>Status</th></tr>
            </thead>
            <tbody>
            <?php if (!$recent): ?>
                <tr><td colspan="4" class="cell-empty text-center text-muted py-4">Nu ați introdus încă nicio activitate.</td></tr>
            <?php endif; ?>
            <?php foreach ($recent as $activity): ?>
                <tr>
                    <td data-label="Data"><?= format_date_ro($activity['activity_date']) ?></td>
                    <td data-label="Tip"><?= e($activity['type_name']) ?></td>
                    <td data-label="Descriere"><?= e(mb_strimwidth($activity['description'], 0, 60, '…')) ?></td>
                    <td data-label="Status"><?= status_badge($activity['status']) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        </div>
    </div>
</div>
