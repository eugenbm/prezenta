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

<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="stat-card stat-hours">
            <div class="stat-value"><?= number_format($approvedHours, 1) ?> h</div>
            <div class="stat-label">Total ore aprobate</div>
        </div>
    </div>
    <div class="col-md-8 d-flex align-items-center">
        <a href="<?= route_url('?route=applicant/activity/create') ?>" class="btn btn-primary btn-lg">+ Adaugă o activitate</a>
    </div>
</div>

<div class="card">
    <div class="card-header">Ultimele activități</div>
    <div class="card-body p-0">
        <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead>
                <tr><th>Data</th><th>Tip</th><th>Descriere</th><th>Status</th></tr>
            </thead>
            <tbody>
            <?php if (!$recent): ?>
                <tr><td colspan="4" class="text-center text-muted py-4">Nu ați introdus încă nicio activitate.</td></tr>
            <?php endif; ?>
            <?php foreach ($recent as $activity): ?>
                <tr>
                    <td><?= format_date_ro($activity['activity_date']) ?></td>
                    <td><?= e($activity['type_name']) ?></td>
                    <td><?= e(mb_strimwidth($activity['description'], 0, 60, '…')) ?></td>
                    <td><?= status_badge($activity['status']) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        </div>
    </div>
</div>
