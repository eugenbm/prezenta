<?php
    /** @var array $volunteers */
    /** @var array|null $volunteer */
    /** @var array $assignments */
    $totalPieces = 0;
    foreach ($assignments as $a) {
        $totalPieces += (int) $a['quantity'];
    }
    $selectedId = $volunteer ? (int) $volunteer['id'] : 0;
?>
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <h1 class="h3 mb-0">Inventar pe voluntar</h1>
    <a href="<?= route_url('?route=admin/inventory') ?>" class="btn btn-outline-secondary"><i class="bi bi-box-seam me-1"></i>Inventar general</a>
</div>

<div class="card mb-4" style="max-width: 640px;">
    <div class="card-header">Selectează un voluntar</div>
    <div class="card-body">
        <form method="get" action="<?= route_url('') ?>" class="row g-2 align-items-end">
            <input type="hidden" name="route" value="admin/users/inventory">
            <div class="col-sm-9">
                <label class="form-label">Voluntar</label>
                <select name="id" class="form-select" onchange="this.form.submit()" required>
                    <option value="">Selectați un voluntar…</option>
                    <?php foreach ($volunteers as $v): ?>
                        <option value="<?= (int) $v['id'] ?>" <?= $selectedId === (int) $v['id'] ? 'selected' : '' ?>>
                            <?= e($v['last_name'] . ' ' . $v['first_name']) ?> — <?= e($v['username']) ?><?= (int) $v['is_active'] === 1 ? '' : ' (dezactivat)' ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-sm-3">
                <button type="submit" class="btn btn-primary w-100"><i class="bi bi-eye me-1"></i>Vezi</button>
            </div>
        </form>
    </div>
</div>

<?php if (!$volunteer): ?>
    <div class="alert alert-info"><i class="bi bi-info-circle me-1"></i>Alege un voluntar din lista de mai sus pentru a-i vedea inventarul.</div>
<?php else: ?>
    <div class="d-flex align-items-center flex-wrap gap-2 mb-3">
        <h2 class="h5 mb-0">Inventarul lui <?= e($volunteer['first_name'] . ' ' . $volunteer['last_name']) ?></h2>
        <span class="text-muted small"><?= role_badge($volunteer['role']) ?> &middot; <?= e($volunteer['username']) ?></span>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-6 col-md-4">
            <div class="stat-card"><div class="stat-value"><?= count($assignments) ?></div><div class="stat-label">Tipuri de obiecte</div></div>
        </div>
        <div class="col-6 col-md-4">
            <div class="stat-card stat-success"><div class="stat-value"><?= $totalPieces ?></div><div class="stat-label">Total bucăți</div></div>
        </div>
    </div>

    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
            <table class="table table-hover table-stack mb-0">
                <thead>
                    <tr><th>Denumire</th><th>Cantitate</th><th>Atribuit la</th></tr>
                </thead>
                <tbody>
                <?php if (!$assignments): ?>
                    <tr><td colspan="3" class="cell-empty py-4">
                        <div class="empty-state"><i class="bi bi-box-seam"></i>Acest voluntar nu are niciun obiect de inventar atribuit.</div>
                    </td></tr>
                <?php endif; ?>
                <?php foreach ($assignments as $a): ?>
                    <tr>
                        <td data-label="Denumire">
                            <div class="cell-value">
                                <div class="fw-semibold"><?= e($a['name']) ?></div>
                                <?php if (trim((string) $a['description']) !== ''): ?>
                                    <div class="small text-muted"><?= nl2br(e($a['description'])) ?></div>
                                <?php endif; ?>
                            </div>
                        </td>
                        <td data-label="Cantitate"><?= (int) $a['quantity'] ?></td>
                        <td data-label="Atribuit la"><?= e(date('d.m.Y', strtotime($a['assigned_at']))) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            </div>
        </div>
    </div>
<?php endif; ?>
