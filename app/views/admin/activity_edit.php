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
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label">Ora de început</label>
                    <input type="time" name="start_time" id="start_time" class="form-control" value="<?= e((string) $activity['start_time']) ?>">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Ora de final</label>
                    <input type="time" name="end_time" id="end_time" class="form-control" value="<?= e((string) $activity['end_time']) ?>">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Număr de ore</label>
                    <input type="number" step="0.25" min="0" max="24" name="duration_hours" id="duration_hours" class="form-control" value="<?= e((string) $activity['duration_hours']) ?>">
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

<script>
    const startEl = document.getElementById('start_time');
    const endEl = document.getElementById('end_time');
    const durationEl = document.getElementById('duration_hours');
    function recalcDuration() {
        if (startEl.value && endEl.value && endEl.value > startEl.value) {
            const [sh, sm] = startEl.value.split(':').map(Number);
            const [eh, em] = endEl.value.split(':').map(Number);
            durationEl.value = (((eh * 60 + em) - (sh * 60 + sm)) / 60).toFixed(2);
        }
    }
    startEl.addEventListener('change', recalcDuration);
    endEl.addEventListener('change', recalcDuration);
</script>
