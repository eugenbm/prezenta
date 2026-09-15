<h1 class="h3 mb-4">Adaugă activitate pentru aspirant</h1>

<div class="card">
    <div class="card-body">
        <form method="post" action="<?= route_url('?route=admin/activity/create') ?>" novalidate>
            <?= csrf_field() ?>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Aspirant *</label>
                    <?php $selectedUser = old('user_id'); ?>
                    <select name="user_id" class="form-select <?= isset($errors['user_id']) ? 'is-invalid' : '' ?>" required>
                        <option value="">— Selectați aspirantul —</option>
                        <?php foreach ($volunteers as $v): ?>
                            <option value="<?= $v['id'] ?>" <?= (string) $selectedUser === (string) $v['id'] ? 'selected' : '' ?>><?= e($v['first_name'] . ' ' . $v['last_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <?php if (isset($errors['user_id'])): ?><div class="invalid-feedback"><?= e($errors['user_id']) ?></div><?php endif; ?>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Status *</label>
                    <?php $selectedStatus = old('status', 'pending'); ?>
                    <select name="status" class="form-select <?= isset($errors['status']) ? 'is-invalid' : '' ?>" required>
                        <option value="pending" <?= $selectedStatus === 'pending' ? 'selected' : '' ?>>În așteptare</option>
                        <option value="approved" <?= $selectedStatus === 'approved' ? 'selected' : '' ?>>Aprobată</option>
                    </select>
                    <?php if (isset($errors['status'])): ?><div class="invalid-feedback"><?= e($errors['status']) ?></div><?php endif; ?>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Data activității *</label>
                    <input type="date" name="activity_date" class="form-control <?= isset($errors['activity_date']) ? 'is-invalid' : '' ?>" value="<?= e(old('activity_date')) ?>" required>
                    <?php if (isset($errors['activity_date'])): ?><div class="invalid-feedback"><?= e($errors['activity_date']) ?></div><?php endif; ?>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Tip activitate *</label>
                    <?php $selectedType = old('activity_type_id'); ?>
                    <select name="activity_type_id" class="form-select <?= isset($errors['activity_type_id']) ? 'is-invalid' : '' ?>" required>
                        <option value="">— Selectați tipul —</option>
                        <?php foreach ($types as $type): ?>
                            <option value="<?= $type['id'] ?>" <?= (string) $selectedType === (string) $type['id'] ? 'selected' : '' ?>><?= e($type['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <?php if (isset($errors['activity_type_id'])): ?><div class="invalid-feedback"><?= e($errors['activity_type_id']) ?></div><?php endif; ?>
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label">Locație / zonă</label>
                <input type="text" name="location" class="form-control" value="<?= e(old('location')) ?>">
            </div>
            <div class="mb-3">
                <label class="form-label">Descriere</label>
                <textarea name="description" class="form-control <?= isset($errors['description']) ? 'is-invalid' : '' ?>" rows="3"><?= e(old('description')) ?></textarea>
                <?php if (isset($errors['description'])): ?><div class="invalid-feedback"><?= e($errors['description']) ?></div><?php endif; ?>
            </div>
            <div class="mb-4">
                <label class="form-label">Observații</label>
                <textarea name="notes" class="form-control" rows="2"><?= e(old('notes')) ?></textarea>
            </div>

            <button type="submit" class="btn btn-primary">Adaugă activitatea</button>
            <a href="<?= route_url('?route=admin/activities') ?>" class="btn btn-outline-secondary">Anulează</a>
        </form>
    </div>
</div>
