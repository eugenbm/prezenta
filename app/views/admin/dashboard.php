<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
    <h1 class="h3 mb-0">Dashboard administrator</h1>
    <div class="d-flex gap-2 flex-wrap">
        <a href="<?= route_url('?route=admin/activity/create') ?>" class="btn btn-primary btn-sm">+ Adaugă activitate</a>
        <a href="<?= route_url('?route=admin/reports') ?>" class="btn btn-outline-secondary btn-sm">Rapoarte</a>
        <a href="<?= route_url('?route=admin/users') ?>" class="btn btn-outline-secondary btn-sm">Conturi</a>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <a href="<?= route_url('?route=admin/users') ?>" class="text-decoration-none">
            <div class="stat-card h-100">
                <div class="stat-value"><?= $activeVolunteers ?></div>
                <div class="stat-label">Aspiranți activi</div>
            </div>
        </a>
    </div>
    <div class="col-6 col-md-3">
        <a href="<?= route_url('?route=admin/activities&status=pending') ?>" class="text-decoration-none">
            <div class="stat-card stat-pending h-100">
                <div class="stat-value"><?= $counts['pending'] ?></div>
                <div class="stat-label">În așteptare</div>
            </div>
        </a>
    </div>
    <div class="col-6 col-md-3">
        <a href="<?= route_url('?route=admin/activities&status=approved') ?>" class="text-decoration-none">
            <div class="stat-card stat-success h-100">
                <div class="stat-value"><?= $counts['approved'] ?></div>
                <div class="stat-label">Aprobate</div>
            </div>
        </a>
    </div>
    <div class="col-6 col-md-3">
        <a href="<?= route_url('?route=admin/activities&status=rejected') ?>" class="text-decoration-none">
            <div class="stat-card stat-danger h-100">
                <div class="stat-value"><?= $counts['rejected'] ?></div>
                <div class="stat-label">Respinse</div>
            </div>
        </a>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-7">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span>Activități în așteptare de aprobare</span>
                <?php if ($counts['pending'] > 0): ?>
                    <span class="badge bg-warning text-dark"><?= $counts['pending'] ?></span>
                <?php endif; ?>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                <table class="table table-hover table-stack mb-0 align-middle">
                    <thead><tr><th>Aspirant</th><th>Data</th><th>Tip</th><th></th></tr></thead>
                    <tbody>
                    <?php if (!$pending): ?>
                        <tr><td colspan="4" class="cell-empty text-center text-muted py-4">Nicio activitate în așteptare.</td></tr>
                    <?php endif; ?>
                    <?php foreach ($pending as $activity): ?>
                        <tr>
                            <td data-label="Aspirant"><?= e($activity['volunteer_first_name'] . ' ' . $activity['volunteer_last_name']) ?></td>
                            <td data-label="Data"><?= format_date_ro($activity['activity_date']) ?></td>
                            <td data-label="Tip"><?= e($activity['type_name']) ?></td>
                            <td data-label="Acțiuni" class="cell-actions text-end d-flex gap-1 flex-wrap justify-content-end">
                                <a href="<?= route_url('?route=admin/activity/edit&id=' . $activity['id']) ?>" class="btn btn-sm btn-outline-secondary">Editează</a>
                                <form method="post" action="<?= route_url('?route=admin/activity/approve') ?>" class="d-inline">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="id" value="<?= $activity['id'] ?>">
                                    <button type="submit" class="btn btn-sm btn-success">Aprobă</button>
                                </form>
                                <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#reject<?= $activity['id'] ?>">Respinge</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
                </div>
            </div>
            <?php if ($counts['pending'] > count($pending)): ?>
            <div class="card-footer text-center">
                <a href="<?= route_url('?route=admin/activities&status=pending') ?>">Vezi toate cele <?= $counts['pending'] ?> activități în așteptare</a>
            </div>
            <?php endif; ?>
        </div>
    </div>
    <div class="col-lg-5">
        <div class="card h-100">
            <div class="card-header">Pe tipuri de activități</div>
            <div class="card-body p-0">
                <div class="table-responsive">
                <table class="table table-stack mb-0">
                    <thead><tr><th>Tip</th><th class="text-end">Total</th><th class="text-end">Aprobate</th></tr></thead>
                    <tbody>
                    <?php if (!$byType): ?>
                        <tr><td colspan="3" class="cell-empty text-center text-muted py-4">Niciun tip de activitate.</td></tr>
                    <?php endif; ?>
                    <?php foreach ($byType as $row): ?>
                        <tr>
                            <td data-label="Tip"><?= e($row['type_name']) ?></td>
                            <td data-label="Total" class="text-end"><?= $row['total'] ?></td>
                            <td data-label="Aprobate" class="text-end"><?= $row['approved'] ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card mt-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span>Activități recente</span>
        <a href="<?= route_url('?route=admin/activities') ?>" class="small">Vezi toate</a>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
        <table class="table table-hover table-stack mb-0 align-middle">
            <thead><tr><th>Aspirant</th><th>Data</th><th>Tip</th><th>Status</th></tr></thead>
            <tbody>
            <?php if (empty($recent)): ?>
                <tr><td colspan="4" class="cell-empty text-center text-muted py-4">Nicio activitate înregistrată.</td></tr>
            <?php endif; ?>
            <?php foreach (($recent ?? []) as $activity): ?>
                <?php
                    $statusBadge = [
                        'approved' => ['Aprobată', 'bg-success'],
                        'pending' => ['În așteptare', 'bg-warning text-dark'],
                        'rejected' => ['Respinsă', 'bg-danger'],
                    ][$activity['status']] ?? [$activity['status'], 'bg-secondary'];
                ?>
                <tr>
                    <td data-label="Aspirant"><?= e($activity['volunteer_first_name'] . ' ' . $activity['volunteer_last_name']) ?></td>
                    <td data-label="Data"><?= format_date_ro($activity['activity_date']) ?></td>
                    <td data-label="Tip"><?= e($activity['type_name']) ?></td>
                    <td data-label="Status"><span class="badge <?= $statusBadge[1] ?>"><?= e($statusBadge[0]) ?></span></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        </div>
    </div>
</div>

<?php foreach ($pending as $activity): ?>
    <div class="modal fade" id="reject<?= $activity['id'] ?>" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="post" action="<?= route_url('?route=admin/activity/reject') ?>">
                    <?= csrf_field() ?>
                    <input type="hidden" name="id" value="<?= $activity['id'] ?>">
                    <div class="modal-header">
                        <h5 class="modal-title">Respinge activitatea</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <label class="form-label">Motivul respingerii *</label>
                        <textarea name="rejection_reason" class="form-control" rows="3" required></textarea>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Anulează</button>
                        <button type="submit" class="btn btn-danger">Respinge activitatea</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
<?php endforeach; ?>

