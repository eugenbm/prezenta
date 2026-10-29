<h1 class="h3 mb-4">Adaugă obiect în inventar</h1>

<div class="card" style="max-width: 640px;">
    <div class="card-body">
        <form method="post" action="<?= route_url('?route=applicant/inventory/add') ?>" novalidate>
            <?= csrf_field() ?>
            <div class="mb-3">
                <label class="form-label">Denumire *</label>
                <input type="text" name="name" class="form-control <?= isset($errors['name']) ? 'is-invalid' : '' ?>"
                       value="<?= old('name') ?>" required>
                <?php if (isset($errors['name'])): ?><div class="invalid-feedback"><?= e($errors['name']) ?></div><?php endif; ?>
            </div>
            <div class="mb-3">
                <label class="form-label">Descriere</label>
                <textarea name="description" rows="3" class="form-control"><?= old('description') ?></textarea>
            </div>
            <div class="mb-3">
                <label class="form-label">Cantitate (bucăți) *</label>
                <input type="number" name="quantity" min="1" step="1"
                       class="form-control <?= isset($errors['quantity']) ? 'is-invalid' : '' ?>"
                       value="<?= old('quantity', '1') ?>" required>
                <?php if (isset($errors['quantity'])): ?><div class="invalid-feedback"><?= e($errors['quantity']) ?></div><?php endif; ?>
            </div>

            <div class="alert alert-info small mb-4">
                Obiectele adăugate aici intră în inventarul tău. Pentru a le elimina, contactează un administrator.
            </div>

            <button type="submit" class="btn btn-primary"><i class="bi bi-plus-circle me-1"></i>Adaugă obiectul</button>
            <a href="<?= route_url('?route=applicant/inventory') ?>" class="btn btn-outline-secondary">Anulează</a>
        </form>
    </div>
</div>
