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

            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label">Ora de început</label>
                    <input type="time" name="start_time" id="start_time" class="form-control"
                           value="<?= e($isEdit ? (string) $activity['start_time'] : old('start_time')) ?>">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Ora de final</label>
                    <input type="time" name="end_time" id="end_time" class="form-control <?= isset($errors['end_time']) ? 'is-invalid' : '' ?>"
                           value="<?= e($isEdit ? (string) $activity['end_time'] : old('end_time')) ?>">
                    <?php if (isset($errors['end_time'])): ?><div class="invalid-feedback"><?= e($errors['end_time']) ?></div><?php endif; ?>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Număr de ore</label>
                    <input type="number" step="0.25" min="0" max="24" name="duration_hours" id="duration_hours"
                           class="form-control <?= isset($errors['duration_hours']) ? 'is-invalid' : '' ?>"
                           value="<?= e($isEdit ? (string) $activity['duration_hours'] : old('duration_hours')) ?>">
                    <div class="form-text">Calculat automat din ore, dacă sunt completate. Editabil dacă este justificat.</div>
                    <?php if (isset($errors['duration_hours'])): ?><div class="invalid-feedback"><?= e($errors['duration_hours']) ?></div><?php endif; ?>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Locație / zonă</label>
                <input type="text" name="location" class="form-control" value="<?= e($isEdit ? (string) $activity['location'] : old('location')) ?>">
            </div>

            <div class="mb-3">
                <label class="form-label">Descriere *</label>
                <textarea name="description" class="form-control <?= isset($errors['description']) ? 'is-invalid' : '' ?>" rows="3" required><?= e($isEdit ? $activity['description'] : old('description')) ?></textarea>
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

<script>
    // Calculează automat numărul de ore când sunt completate ambele ore.
    const startEl = document.getElementById('start_time');
    const endEl = document.getElementById('end_time');
    const durationEl = document.getElementById('duration_hours');

    function recalcDuration() {
        if (startEl.value && endEl.value && endEl.value > startEl.value) {
            const [sh, sm] = startEl.value.split(':').map(Number);
            const [eh, em] = endEl.value.split(':').map(Number);
            const hours = ((eh * 60 + em) - (sh * 60 + sm)) / 60;
            durationEl.value = hours.toFixed(2);
        }
    }
    startEl.addEventListener('change', recalcDuration);
    endEl.addEventListener('change', recalcDuration);
</script>
