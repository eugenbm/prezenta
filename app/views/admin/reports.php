<h1 class="h3 mb-4">Rapoarte</h1>

<form method="get" action="<?= route_url() ?>" class="filter-bar card card-body mb-4">
    <input type="hidden" name="route" value="admin/reports">
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
    <div class="mt-2">
        <?php
            $exportParams = array_filter($filters);
            $exportQuery = $exportParams ? '&' . http_build_query($exportParams) : '';
        ?>
        <a href="<?= route_url('?route=admin/reports/export-csv' . $exportQuery) ?>" class="btn btn-sm btn-outline-primary">Exportă CSV</a>
        <a href="<?= route_url('?route=admin/reports/print' . $exportQuery) ?>" class="btn btn-sm btn-outline-secondary" target="_blank">Vizualizează / Exportă PDF</a>
    </div>
</form>

<div class="row g-4 mb-4">
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">Sumar pe tip de activitate</div>
            <div class="card-body p-0">
                <div class="table-responsive">
                <table class="table mb-0">
                    <thead><tr><th>Tip</th><th>Total</th><th>Aprobate</th><th>Ore aprobate</th></tr></thead>
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
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">Sumar pe voluntar</div>
            <div class="card-body p-0">
                <div class="table-responsive">
                <table class="table mb-0">
                    <thead><tr><th>Voluntar</th><th>Total</th><th>Aprobate</th><th>Așteptare</th><th>Respinse</th><th>Ore</th></tr></thead>
                    <tbody>
                    <?php foreach ($byVolunteer as $row): ?>
                        <tr>
                            <td><?= e($row['first_name'] . ' ' . $row['last_name']) ?></td>
                            <td><?= $row['total'] ?></td>
                            <td><?= $row['approved'] ?></td>
                            <td><?= $row['pending'] ?></td>
                            <td><?= $row['rejected'] ?></td>
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

<div class="card">
    <div class="card-header">Detaliu activități (<?= count($activities) ?>)</div>
    <div class="card-body p-0">
        <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead>
                <tr><th>Voluntar</th><th>Data</th><th>Tip</th><th>Ore</th><th>Status</th></tr>
            </thead>
            <tbody>
            <?php if (!$activities): ?>
                <tr><td colspan="5" class="text-center text-muted py-4">Nicio activitate găsită pentru filtrele selectate.</td></tr>
            <?php endif; ?>
            <?php foreach ($activities as $activity): ?>
                <tr>
                    <td><?= e($activity['volunteer_first_name'] . ' ' . $activity['volunteer_last_name']) ?></td>
                    <td><?= format_date_ro($activity['activity_date']) ?></td>
                    <td><?= e($activity['type_name']) ?></td>
                    <td><?= $activity['duration_hours'] !== null ? number_format((float) $activity['duration_hours'], 1) : '—' ?></td>
                    <td><?= status_badge($activity['status']) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        </div>
    </div>
</div>
