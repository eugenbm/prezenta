<?php /** @var array $items */ ?>
<?php foreach ($items as $item): ?>
    <?php
        $total = (int) $item['total_quantity'];
        $assigned = (int) $item['assigned_quantity'];
        $available = $total - $assigned;
    ?>
    <tr>
        <td data-label="Denumire">
            <div class="cell-value">
                <div class="fw-semibold"><?= e($item['name']) ?></div>
                <?php if (trim((string) $item['description']) !== ''): ?>
                    <div class="small text-muted"><?= nl2br(e($item['description'])) ?></div>
                <?php endif; ?>
                <?php if (!empty($item['creator_role']) && $item['creator_role'] !== 'admin'): ?>
                    <div class="small text-muted"><i class="bi bi-person-plus me-1"></i>Adăugat de <?= e($item['creator_last_name'] . ' ' . $item['creator_first_name']) ?></div>
                <?php endif; ?>
            </div>
        </td>
        <td data-label="Total"><?= $total ?></td>
        <td data-label="Atribuite"><?= $assigned ?></td>
        <td data-label="Disponibile">
            <span class="badge status-badge <?= $available > 0 ? 'badge-approved' : 'badge-pending' ?>"><?= $available ?></span>
        </td>
        <td data-label="Acțiuni" class="cell-actions d-flex gap-1 flex-wrap">
            <a href="<?= route_url('?route=admin/inventory/item&id=' . (int) $item['id']) ?>" class="btn btn-sm btn-primary"><i class="bi bi-people me-1"></i>Atribuie</a>
            <a href="<?= route_url('?route=admin/inventory/edit&id=' . (int) $item['id']) ?>" class="btn btn-sm btn-outline-secondary"><i class="bi bi-pencil me-1"></i>Editează</a>
            <form method="post" action="<?= route_url('?route=admin/inventory/delete') ?>" class="d-inline" onsubmit="return confirm('Ștergeți acest obiect din inventar? Toate atribuirile vor fi eliminate.');">
                <?= csrf_field() ?>
                <input type="hidden" name="id" value="<?= (int) $item['id'] ?>">
                <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash me-1"></i>Șterge</button>
            </form>
        </td>
    </tr>
<?php endforeach; ?>
