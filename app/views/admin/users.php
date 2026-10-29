<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Voluntari</h1>
    <a href="<?= route_url('?route=admin/users/create') ?>" class="btn btn-primary"><i class="bi bi-person-plus me-1"></i>Cont nou</a>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
        <table class="table table-hover table-stack mb-0">
            <thead>
                <tr><th>Nume</th><th>Utilizator</th><th>Email</th><th>Rol</th><th>Status</th><th></th></tr>
            </thead>
            <tbody>
            <?php foreach ($users as $u): ?>
                <tr>
                    <td data-label="Nume"><?= e($u['first_name'] . ' ' . $u['last_name']) ?></td>
                    <td data-label="Utilizator"><?= e($u['username']) ?></td>
                    <td data-label="Email"><?= e($u['email']) ?></td>
                    <td data-label="Rol"><?= role_badge($u['role']) ?></td>
                    <td data-label="Status">
                        <?php if ((int) $u['is_active'] === 1): ?>
                            <span class="badge status-badge badge-approved">Activ</span>
                        <?php else: ?>
                            <span class="badge status-badge badge-rejected">Dezactivat</span>
                        <?php endif; ?>
                    </td>
                    <td data-label="Acțiuni" class="cell-actions d-flex gap-1 flex-wrap">
                        <a href="<?= route_url('?route=admin/users/edit&id=' . $u['id']) ?>" class="btn btn-sm btn-outline-secondary"><i class="bi bi-pencil me-1"></i>Editează</a>

                        <?php if ($u['role'] !== 'admin'): ?>
                        <a href="<?= route_url('?route=admin/users/inventory&id=' . $u['id']) ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-box-seam me-1"></i>Inventar</a>
                        <?php endif; ?>

                        <form method="post" action="<?= route_url('?route=admin/users/reset-password') ?>" class="d-inline" onsubmit="return confirm('Generați o parolă temporară și trimiteți emailul de resetare către <?= e($u['username']) ?>?');">
                            <?= csrf_field() ?>
                            <input type="hidden" name="id" value="<?= $u['id'] ?>">
                            <button type="submit" class="btn btn-sm btn-outline-warning"><i class="bi bi-key me-1"></i>Resetează parola</button>
                        </form>

                        <?php if ((int) $u['id'] !== (int) $currentUser['id']): ?>
                        <form method="post" action="<?= route_url('?route=admin/users/toggle') ?>" class="d-inline">
                            <?= csrf_field() ?>
                            <input type="hidden" name="id" value="<?= $u['id'] ?>">
                            <?php if ((int) $u['is_active'] === 1): ?>
                                <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Dezactivați acest cont?')">Dezactivează</button>
                            <?php else: ?>
                                <button type="submit" class="btn btn-sm btn-outline-success">Reactivează</button>
                            <?php endif; ?>
                        </form>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        </div>
    </div>
</div>
