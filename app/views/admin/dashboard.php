<h1 class="h3 mb-4">Dashboard administrator</h1>

<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="stat-card">
            <div class="stat-value"><?= $activeVolunteers ?></div>
            <div class="stat-label">Voluntari activi</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card stat-pending">
            <div class="stat-value"><?= $counts['pending'] ?></div>
            <div class="stat-label">Activități în așteptare</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card stat-success">
            <div class="stat-value"><?= $counts['approved'] ?></div>
            <div class="stat-label">Activități aprobate</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card stat-danger">
            <div class="stat-value"><?= $counts['rejected'] ?></div>
            <div class="stat-label">Activități respinse</div>
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
</div>

<?php if ($counts['pending'] > 0): ?>
<div class="alert alert-warning d-flex justify-content-between align-items-center">
    <span><strong><?= $counts['pending'] ?></strong> activitate(activități) necesită aprobare.</span>
    <a href="<?= route_url('?route=admin/activities&status=pending') ?>" class="btn btn-sm btn-warning">Vezi activitățile</a>
</div>
<?php endif; ?>

<div class="row g-4">
    <div class="col-lg-7">
        <div class="card">
            <div class="card-header">Activități în așteptare de aprobare</div>
            <div class="card-body p-0">
                <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead><tr><th>Voluntar</th><th>Data</th><th>Tip</th><th></th></tr></thead>
                    <tbody>
                    <?php if (!$pending): ?>
                        <tr><td colspan="4" class="text-center text-muted py-4">Nicio activitate în așteptare.</td></tr>
                    <?php endif; ?>
                    <?php foreach ($pending as $activity): ?>
                        <tr>
                            <td><?= e($activity['volunteer_first_name'] . ' ' . $activity['volunteer_last_name']) ?></td>
                            <td><?= format_date_ro($activity['activity_date']) ?></td>
                            <td><?= e($activity['type_name']) ?></td>
                            <td><a href="<?= route_url('?route=admin/activities') ?>" class="btn btn-sm btn-outline-secondary">Vezi</a></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-5">
        <div class="card">
            <div class="card-header">Statistici pe tipuri de activități</div>
            <div class="card-body p-0">
                <div class="table-responsive">
                <table class="table mb-0">
                    <thead><tr><th>Tip</th><th>Total</th><th>Aprobate</th><th>Ore</th></tr></thead>
                    <tbody>
                    <?php foreach ($byType as $row): ?>
                        <tr>
                            <td><?= e($row['type_name']) ?></td>
                            <td><?= $row['total'] ?></td>
                            <td><?= $row['approved'] ?></td>
                            <td><?= number_format((float) $row['approved_hours'], 1) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
                </div>
            </div>
        </div>
    </div>
</div>
