<?php
    /** @var array $item */
    /** @var array $assignments */
    /** @var array $volunteers */
    /** @var int $available */
    $total = (int) $item['total_quantity'];
    $assignedTotal = (int) $item['assigned_quantity'];
?>
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <h1 class="h3 mb-0"><?= e($item['name']) ?></h1>
    <a href="<?= route_url('?route=admin/inventory') ?>" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>Înapoi la inventar</a>
</div>

<?php if (trim((string) $item['description']) !== ''): ?>
    <p class="text-muted"><?= nl2br(e($item['description'])) ?></p>
<?php endif; ?>

<div class="row g-3 mb-4">
    <div class="col-6 col-md-4">
        <div class="stat-card"><div class="stat-value"><?= $total ?></div><div class="stat-label">Total bucăți</div></div>
    </div>
    <div class="col-6 col-md-4">
        <div class="stat-card stat-pending"><div class="stat-value"><?= $assignedTotal ?></div><div class="stat-label">Atribuite</div></div>
    </div>
    <div class="col-6 col-md-4">
        <div class="stat-card stat-success"><div class="stat-value"><?= $available ?></div><div class="stat-label">Disponibile</div></div>
    </div>
</div>

<div class="card mb-4" style="max-width: 640px;">
    <div class="card-header">Atribuie unui voluntar</div>
    <div class="card-body">
        <?php if ($available < 1): ?>
            <div class="alert alert-warning mb-0">Nu mai există bucăți disponibile de atribuit. Retrageți atribuiri sau măriți cantitatea totală.</div>
        <?php elseif (!$volunteers): ?>
            <div class="alert alert-info mb-0">Nu există voluntari activi disponibili pentru atribuire.</div>
        <?php else: ?>
        <form method="post" action="<?= route_url('?route=admin/inventory/assign') ?>">
            <?= csrf_field() ?>
            <input type="hidden" name="item_id" value="<?= (int) $item['id'] ?>">
            <div class="row g-2 align-items-end">
                <div class="col-md-7">
                    <label class="form-label">Voluntar</label>
                    <select name="user_id" class="form-select" required>
                        <option value="">Selectați un voluntar…</option>
                        <?php foreach ($volunteers as $v): ?>
                            <option value="<?= (int) $v['id'] ?>"><?= e($v['last_name'] . ' ' . $v['first_name']) ?> — <?= e(role_label($v['role'])) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Cantitate</label>
                    <input type="number" name="quantity" class="form-control" min="1" max="<?= $available ?>" value="1" required>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100"><i class="bi bi-check2 me-1"></i>Atribuie</button>
                </div>
            </div>
        </form>
        <?php endif; ?>
    </div>
</div>

<div class="card">
    <div class="card-header">Atribuiri curente</div>
    <div class="card-body p-0">
        <div class="table-responsive">
        <table class="table table-hover table-stack mb-0">
            <thead>
                <tr><th>Voluntar</th><th>Rol</th><th>Cantitate</th><th>Atribuit la</th><th></th></tr>
            </thead>
            <tbody>
            <?php if (!$assignments): ?>
                <tr><td colspan="5" class="cell-empty py-4">
                    <div class="empty-state"><i class="bi bi-person-dash"></i>Obiectul nu este atribuit niciunui voluntar.</div>
                </td></tr>
            <?php endif; ?>
            <?php foreach ($assignments as $a): ?>
                <tr>
                    <td data-label="Voluntar"><?= e($a['last_name'] . ' ' . $a['first_name']) ?></td>
                    <td data-label="Rol"><?= role_badge($a['role']) ?></td>
                    <td data-label="Cantitate"><?= (int) $a['quantity'] ?></td>
                    <td data-label="Atribuit la"><?= e(date('d.m.Y H:i', strtotime($a['assigned_at']))) ?></td>
                    <td data-label="Acțiuni" class="cell-actions">
                        <form method="post" action="<?= route_url('?route=admin/inventory/unassign') ?>" class="d-inline" onsubmit="return confirm('Retrageți acest obiect de la voluntar?');">
                            <?= csrf_field() ?>
                            <input type="hidden" name="assignment_id" value="<?= (int) $a['id'] ?>">
                            <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-x-circle me-1"></i>Retrage</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        </div>
    </div>
</div>
