<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Voluntari</h1>
    <a href="<?= route_url('?route=admin/users/create') ?>" class="btn btn-primary">+ Cont nou</a>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead>
                <tr><th>Nume</th><th>Utilizator</th><th>Email</th><th>Rol</th><th>Status</th><th></th></tr>
            </thead>
            <tbody>
            <?php foreach ($users as $u): ?>
                <tr>
                    <td><?= e($u['first_name'] . ' ' . $u['last_name']) ?></td>
                    <td><?= e($u['username']) ?></td>
                    <td><?= e($u['email']) ?></td>
                    <td><?= $u['role'] === 'admin' ? 'Administrator' : 'Aspirant' ?></td>
                    <td>
                        <?php if ((int) $u['is_active'] === 1): ?>
                            <span class="badge status-badge badge-approved">Activ</span>
                        <?php else: ?>
                            <span class="badge status-badge badge-rejected">Dezactivat</span>
                        <?php endif; ?>
                    </td>
                    <td class="d-flex gap-1 flex-wrap">
                        <a href="<?= route_url('?route=admin/users/edit&id=' . $u['id']) ?>" class="btn btn-sm btn-outline-secondary">Editează</a>

                        <button type="button" class="btn btn-sm btn-outline-warning" data-bs-toggle="modal" data-bs-target="#resetPwd<?= $u['id'] ?>">Resetează parola</button>

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

<?php foreach ($users as $u): ?>
    <div class="modal fade" id="resetPwd<?= $u['id'] ?>" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="post" action="<?= route_url('?route=admin/users/reset-password') ?>">
                    <?= csrf_field() ?>
                    <input type="hidden" name="id" value="<?= $u['id'] ?>">
                    <div class="modal-header">
                        <h5 class="modal-title">Resetează parola — <?= e($u['username']) ?></h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <label class="form-label">Parolă nouă (minim 10 caractere)</label>
                        <input type="password" name="new_password" class="form-control" minlength="10" required>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Anulează</button>
                        <button type="submit" class="btn btn-warning">Resetează parola</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
<?php endforeach; ?>
