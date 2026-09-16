<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Toate activitățile</h1>
    <a href="<?= route_url('?route=admin/activity/create') ?>" class="btn btn-primary">Adaugă activitate</a>
</div>

<form method="get" action="<?= route_url() ?>" class="filter-bar card card-body mb-4">
    <input type="hidden" name="route" value="admin/activities">
    <div class="row g-2">
        <div class="col-md-3">
            <label class="form-label">Voluntar</label>
            <select name="user_id" class="form-select">
                <option value="">Toți</option>
                <?php foreach ($volunteers as $v): ?>
                    <option value="<?= $v['id'] ?>" <?= (string) ($filters['user_id'] ?? '') === (string) $v['id'] ? 'selected' : '' ?>><?= e($v['first_name'] . ' ' . $v['last_name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label">Tip</label>
            <select name="activity_type_id" class="form-select">
                <option value="">Toate</option>
                <?php foreach ($types as $type): ?>
                    <option value="<?= $type['id'] ?>" <?= (string) ($filters['activity_type_id'] ?? '') === (string) $type['id'] ? 'selected' : '' ?>><?= e($type['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label">Status</label>
            <select name="status" class="form-select">
                <option value="">Toate</option>
                <option value="pending" <?= ($filters['status'] ?? '') === 'pending' ? 'selected' : '' ?>>În așteptare</option>
                <option value="approved" <?= ($filters['status'] ?? '') === 'approved' ? 'selected' : '' ?>>Aprobată</option>
                <option value="rejected" <?= ($filters['status'] ?? '') === 'rejected' ? 'selected' : '' ?>>Respinsă</option>
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label">De la</label>
            <input type="date" name="date_from" class="form-control" value="<?= e($filters['date_from'] ?? '') ?>">
        </div>
        <div class="col-md-2">
            <label class="form-label">Până la</label>
            <input type="date" name="date_to" class="form-control" value="<?= e($filters['date_to'] ?? '') ?>">
        </div>
        <div class="col-md-1 d-flex align-items-end">
            <button type="submit" class="btn btn-outline-secondary w-100">Filtrează</button>
        </div>
    </div>
</form>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
        <table class="table table-hover table-stack mb-0">
            <thead>
                <tr><th>Voluntar</th><th>Data</th><th>Tip</th><th>Descriere</th><th>Status</th><th></th></tr>
            </thead>
            <tbody>
            <?php if (!$activities): ?>
                <tr><td colspan="6" class="cell-empty text-center text-muted py-4">Nicio activitate găsită.</td></tr>
            <?php endif; ?>
            <?php foreach ($activities as $activity): ?>
                <tr>
                    <td data-label="Voluntar"><?= e($activity['volunteer_first_name'] . ' ' . $activity['volunteer_last_name']) ?></td>
                    <td data-label="Data"><?= format_date_ro($activity['activity_date']) ?></td>
                    <td data-label="Tip"><?= e($activity['type_name']) ?></td>
                    <td data-label="Descriere"><?= e(mb_strimwidth($activity['description'], 0, 50, '…')) ?></td>
                    <td data-label="Status"><?= status_badge($activity['status']) ?></td>
                    <td data-label="Acțiuni" class="cell-actions d-flex gap-1 flex-wrap">
                        <a href="<?= route_url('?route=admin/activity/edit&id=' . $activity['id']) ?>" class="btn btn-sm btn-outline-secondary">Editează</a>
                        <?php if ($activity['status'] === 'pending'): ?>
                            <form method="post" action="<?= route_url('?route=admin/activity/approve') ?>" class="d-inline">
                                <?= csrf_field() ?>
                                <input type="hidden" name="id" value="<?= $activity['id'] ?>">
                                <button type="submit" class="btn btn-sm btn-success">Aprobă</button>
                            </form>
                            <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#reject<?= $activity['id'] ?>">Respinge</button>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        </div>
    </div>
</div>

<?php
    $pageParams = array_filter([
        'user_id' => $filters['user_id'] ?? '',
        'activity_type_id' => $filters['activity_type_id'] ?? '',
        'status' => $filters['status'] ?? '',
        'date_from' => $filters['date_from'] ?? '',
        'date_to' => $filters['date_to'] ?? '',
    ], fn ($v) => $v !== '');
    $extra = http_build_query($pageParams);
    $pageUrl = fn (int $p) => route_url('?route=admin/activities' . ($extra !== '' ? '&' . $extra : '') . '&page=' . $p);
?>
<?php if ($page > 1 || $hasMore): ?>
<nav class="d-flex justify-content-between align-items-center mt-3">
    <?php if ($page > 1): ?>
        <a class="btn btn-outline-secondary btn-sm" href="<?= $pageUrl($page - 1) ?>">← Anterioarele 10</a>
    <?php else: ?>
        <span class="btn btn-outline-secondary btn-sm disabled">← Anterioarele 10</span>
    <?php endif; ?>
    <span class="text-muted small">Pagina <?= (int) $page ?></span>
    <?php if ($hasMore): ?>
        <a class="btn btn-outline-secondary btn-sm" href="<?= $pageUrl($page + 1) ?>">Următoarele 10 →</a>
    <?php else: ?>
        <span class="btn btn-outline-secondary btn-sm disabled">Următoarele 10 →</span>
    <?php endif; ?>
</nav>
<?php endif; ?>

<?php foreach ($activities as $activity): ?>
    <?php if ($activity['status'] === 'pending'): ?>
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
    <?php endif; ?>
<?php endforeach; ?>
