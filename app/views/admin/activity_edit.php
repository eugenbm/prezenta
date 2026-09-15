<h1 class="h3 mb-4">Editează activitatea (administrator)</h1>

<div class="card">
    <div class="card-body">
        <div class="mb-3 text-muted">
            Voluntar: <strong><?= e($activity['volunteer_first_name'] . ' ' . $activity['volunteer_last_name']) ?></strong>
            &middot; Status curent: <?= status_badge($activity['status']) ?>
        </div>
        <form method="post" action="<?= route_url('?route=admin/activity/edit&id=' . $activity['id']) ?>" novalidate>
            <?= csrf_field() ?>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Data activității *</label>
                    <input type="date" name="activity_date" class="form-control" value="<?= e($activity['activity_date']) ?>" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Tip activitate *</label>
                    <select name="activity_type_id" class="form-select" required>
                        <?php foreach ($types as $type): ?>
                            <option value="<?= $type['id'] ?>" <?= (int) $activity['activity_type_id'] === (int) $type['id'] ? 'selected' : '' ?>><?= e($type['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label">Locație / zonă</label>
                <input type="text" name="location" class="form-control" value="<?= e((string) $activity['location']) ?>">
            </div>
            <div class="mb-3">
                <label class="form-label">Descriere *</label>
                <textarea name="description" class="form-control" rows="3" required><?= e($activity['description']) ?></textarea>
            </div>
            <div class="mb-4">
                <label class="form-label">Observații</label>
                <textarea name="notes" class="form-control" rows="2"><?= e((string) $activity['notes']) ?></textarea>
            </div>

            <button type="submit" class="btn btn-primary">Salvează modificările</button>
            <a href="<?= route_url('?route=admin/activities') ?>" class="btn btn-outline-secondary">Anulează</a>
        </form>
    </div>
</div>
