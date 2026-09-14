<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Activitățile mele</h1>
    <a href="<?= route_url('?route=applicant/activity/create') ?>" class="btn btn-primary">+ Adaugă activitate</a>
</div>

<form method="get" action="<?= route_url() ?>" class="filter-bar card card-body mb-4">
    <input type="hidden" name="route" value="applicant/activities">
    <div class="row g-2">
        <div class="col-md-3">
            <label class="form-label">Status</label>
            <select name="status" class="form-select">
                <option value="">Toate</option>
                <option value="pending" <?= ($filters['status'] ?? '') === 'pending' ? 'selected' : '' ?>>În așteptare</option>
                <option value="approved" <?= ($filters['status'] ?? '') === 'approved' ? 'selected' : '' ?>>Aprobată</option>
                <option value="rejected" <?= ($filters['status'] ?? '') === 'rejected' ? 'selected' : '' ?>>Respinsă</option>
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label">Tip activitate</label>
            <select name="activity_type_id" class="form-select">
                <option value="">Toate</option>
                <?php foreach ($types as $type): ?>
                    <option value="<?= $type['id'] ?>" <?= (string) ($filters['activity_type_id'] ?? '') === (string) $type['id'] ? 'selected' : '' ?>><?= e($type['name']) ?></option>
                <?php endforeach; ?>
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
        <div class="col-md-2 d-flex align-items-end">
            <button type="submit" class="btn btn-outline-secondary w-100">Filtrează</button>
        </div>
    </div>
</form>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead>
                <tr><th>Data</th><th>Tip</th><th>Descriere</th><th>Ore</th><th>Status</th><th>Motiv respingere</th><th></th></tr>
            </thead>
            <tbody>
            <?php if (!$activities): ?>
                <tr><td colspan="7" class="text-center text-muted py-4">Nicio activitate găsită.</td></tr>
            <?php endif; ?>
            <?php foreach ($activities as $activity): ?>
                <tr>
                    <td><?= format_date_ro($activity['activity_date']) ?></td>
                    <td><?= e($activity['type_name']) ?></td>
                    <td><?= e($activity['description']) ?></td>
                    <td><?= $activity['duration_hours'] !== null ? number_format((float) $activity['duration_hours'], 1) : '—' ?></td>
                    <td><?= status_badge($activity['status']) ?></td>
                    <td class="text-danger small"><?= e($activity['rejection_reason'] ?? '') ?></td>
                    <td>
                        <?php if ($activity['status'] === 'pending'): ?>
                            <a href="<?= route_url('?route=applicant/activity/edit&id=' . $activity['id']) ?>" class="btn btn-sm btn-outline-secondary">Editează</a>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        </div>
    </div>
</div>
