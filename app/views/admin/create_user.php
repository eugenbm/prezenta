<?php $isEdit = isset($editUser) && $editUser !== null; ?>
<h1 class="h3 mb-4"><?= $isEdit ? 'Editează cont voluntar' : 'Cont nou' ?></h1>

<div class="card" style="max-width: 640px;">
    <div class="card-body">
        <form method="post" action="<?= $isEdit ? route_url('?route=admin/users/edit&id=' . $editUser['id']) : route_url('?route=admin/users/create') ?>" novalidate>
            <?= csrf_field() ?>
            <div class="row">
                <div class="col-6 mb-3">
                    <label class="form-label">Prenume *</label>
                    <input type="text" name="first_name" class="form-control <?= isset($errors['first_name']) ? 'is-invalid' : '' ?>"
                           value="<?= e($isEdit ? $editUser['first_name'] : old('first_name')) ?>" required>
                    <?php if (isset($errors['first_name'])): ?><div class="invalid-feedback"><?= e($errors['first_name']) ?></div><?php endif; ?>
                </div>
                <div class="col-6 mb-3">
                    <label class="form-label">Nume *</label>
                    <input type="text" name="last_name" class="form-control <?= isset($errors['last_name']) ? 'is-invalid' : '' ?>"
                           value="<?= e($isEdit ? $editUser['last_name'] : old('last_name')) ?>" required>
                    <?php if (isset($errors['last_name'])): ?><div class="invalid-feedback"><?= e($errors['last_name']) ?></div><?php endif; ?>
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label">Email *</label>
                <input type="email" name="email" class="form-control <?= isset($errors['email']) ? 'is-invalid' : '' ?>"
                       value="<?= e($isEdit ? $editUser['email'] : old('email')) ?>" required>
                <?php if (isset($errors['email'])): ?><div class="invalid-feedback"><?= e($errors['email']) ?></div><?php endif; ?>
            </div>
            <div class="mb-3">
                <label class="form-label">Utilizator *</label>
                <input type="text" name="username" class="form-control <?= isset($errors['username']) ? 'is-invalid' : '' ?>"
                       value="<?= e($isEdit ? $editUser['username'] : old('username')) ?>" required>
                <?php if (isset($errors['username'])): ?><div class="invalid-feedback"><?= e($errors['username']) ?></div><?php endif; ?>
            </div>
            <div class="mb-3">
                <label class="form-label">Rol *</label>
                <?php $selectedRole = $isEdit ? $editUser['role'] : old('role', 'applicant'); ?>
                <select name="role" class="form-select <?= isset($errors['role']) ? 'is-invalid' : '' ?>" required>
                    <option value="applicant" <?= $selectedRole === 'applicant' ? 'selected' : '' ?>>Aspirant</option>
                    <option value="admin" <?= $selectedRole === 'admin' ? 'selected' : '' ?>>Administrator</option>
                </select>
                <?php if (isset($errors['role'])): ?><div class="invalid-feedback"><?= e($errors['role']) ?></div><?php endif; ?>
            </div>
            <?php if (!$isEdit): ?>
            <div class="mb-4">
                <label class="form-label">Parolă inițială * (minim 10 caractere)</label>
                <input type="password" name="password" class="form-control <?= isset($errors['password']) ? 'is-invalid' : '' ?>" required>
                <?php if (isset($errors['password'])): ?><div class="invalid-feedback"><?= e($errors['password']) ?></div><?php endif; ?>
            </div>
            <?php endif; ?>

            <button type="submit" class="btn btn-primary"><?= $isEdit ? 'Salvează modificările' : 'Creează contul' ?></button>
            <a href="<?= route_url('?route=admin/users') ?>" class="btn btn-outline-secondary">Anulează</a>
        </form>
    </div>
</div>
