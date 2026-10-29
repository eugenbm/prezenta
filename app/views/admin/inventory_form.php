<?php
    /** @var array|null $item */
    $isEdit = isset($item) && $item !== null;
    $assigned = $isEdit ? (int) $item['assigned_quantity'] : 0;
?>
<h1 class="h3 mb-4"><?= $isEdit ? 'Editează obiect de inventar' : 'Obiect de inventar nou' ?></h1>

<div class="card" style="max-width: 640px;">
    <div class="card-body">
        <form method="post" action="<?= $isEdit ? route_url('?route=admin/inventory/edit&id=' . (int) $item['id']) : route_url('?route=admin/inventory/create') ?>" novalidate>
            <?= csrf_field() ?>
            <div class="mb-3">
                <label class="form-label">Denumire *</label>
                <input type="text" name="name" class="form-control <?= isset($errors['name']) ? 'is-invalid' : '' ?>"
                       value="<?= e($isEdit ? $item['name'] : old('name')) ?>" required>
                <?php if (isset($errors['name'])): ?><div class="invalid-feedback"><?= e($errors['name']) ?></div><?php endif; ?>
            </div>
            <div class="mb-3">
                <label class="form-label">Descriere</label>
                <textarea name="description" rows="3" class="form-control"><?= e($isEdit ? $item['description'] : old('description')) ?></textarea>
            </div>
            <div class="mb-3">
                <label class="form-label">Cantitate totală (bucăți) *</label>
                <input type="number" name="total_quantity" min="<?= $assigned ?>" step="1"
                       class="form-control <?= isset($errors['total_quantity']) ? 'is-invalid' : '' ?>"
                       value="<?= e($isEdit ? (string) $item['total_quantity'] : old('total_quantity', '1')) ?>" required>
                <?php if (isset($errors['total_quantity'])): ?><div class="invalid-feedback"><?= e($errors['total_quantity']) ?></div><?php endif; ?>
                <?php if ($isEdit && $assigned > 0): ?>
                    <div class="form-text"><?= $assigned ?> bucăți sunt deja atribuite voluntarilor; totalul nu poate scădea sub această valoare.</div>
                <?php endif; ?>
            </div>

            <button type="submit" class="btn btn-primary"><?= $isEdit ? 'Salvează modificările' : 'Adaugă obiectul' ?></button>
            <a href="<?= route_url('?route=admin/inventory') ?>" class="btn btn-outline-secondary">Anulează</a>
        </form>
    </div>
</div>
