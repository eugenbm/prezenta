<?php $isEdit = $activity !== null; ?>
<h1 class="h3 mb-4"><?= $isEdit ? 'Editează activitatea' : 'Adaugă o activitate' ?></h1>

<div class="card">
    <div class="card-body">
        <form method="post" action="<?= $isEdit ? route_url('?route=applicant/activity/edit&id=' . $activity['id']) : route_url('?route=applicant/activity/create') ?>" novalidate>
            <?= csrf_field() ?>

            <div class="mb-3">
                <label class="form-label">Voluntar</label>
                <input type="text" class="form-control" value="<?= e($currentUser['first_name'] . ' ' . $currentUser['last_name']) ?>" disabled>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Data activității *</label>
                    <input type="date" name="activity_date" class="form-control <?= isset($errors['activity_date']) ? 'is-invalid' : '' ?>"
                           value="<?= e($isEdit ? $activity['activity_date'] : old('activity_date')) ?>" max="<?= date('Y-m-d') ?>" required>
                    <?php if (isset($errors['activity_date'])): ?><div class="invalid-feedback"><?= e($errors['activity_date']) ?></div><?php endif; ?>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Tip activitate *</label>
                    <select name="activity_type_id" class="form-select <?= isset($errors['activity_type_id']) ? 'is-invalid' : '' ?>" required>
                        <option value="">Alegeți...</option>
                        <?php $selectedId = $isEdit ? $activity['activity_type_id'] : old('activity_type_id'); ?>
                        <?php foreach ($types as $type): ?>
                            <option value="<?= $type['id'] ?>" <?= (string) $selectedId === (string) $type['id'] ? 'selected' : '' ?>><?= e($type['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <?php if (isset($errors['activity_type_id'])): ?><div class="invalid-feedback"><?= e($errors['activity_type_id']) ?></div><?php endif; ?>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Locație / zonă</label>
                <input type="text" name="location" class="form-control" value="<?= e($isEdit ? (string) $activity['location'] : old('location')) ?>">
            </div>

            <div class="mb-3">
                <label class="form-label">Descriere</label>
                <textarea name="description" class="form-control <?= isset($errors['description']) ? 'is-invalid' : '' ?>" rows="3"><?= e($isEdit ? $activity['description'] : old('description')) ?></textarea>
                <?php if (isset($errors['description'])): ?><div class="invalid-feedback"><?= e($errors['description']) ?></div><?php endif; ?>
            </div>

            <div class="mb-4">
                <label class="form-label">Observații</label>
                <textarea name="notes" class="form-control" rows="2"><?= e($isEdit ? (string) $activity['notes'] : old('notes')) ?></textarea>
            </div>

            <button type="submit" class="btn btn-primary">Salvează</button>
            <a href="<?= route_url('?route=applicant/activities') ?>" class="btn btn-outline-secondary">Anulează</a>
        </form>
    </div>
</div>
