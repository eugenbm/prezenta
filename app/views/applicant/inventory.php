<?php /** @var array $assignments */ ?>
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <h1 class="h3 mb-0">Inventarul meu</h1>
    <a href="<?= route_url('?route=applicant/inventory/add') ?>" class="btn btn-primary"><i class="bi bi-plus-circle me-1"></i>Adaugă obiect</a>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
        <table class="table table-hover table-stack mb-0">
            <thead>
                <tr><th>Denumire</th><th>Cantitate</th><th>Atribuit la</th></tr>
            </thead>
            <tbody>
            <?php if (!$assignments): ?>
                <tr><td colspan="3" class="cell-empty py-4">
                    <div class="empty-state"><i class="bi bi-box-seam"></i>Nu ai niciun obiect de inventar atribuit.</div>
                </td></tr>
            <?php endif; ?>
            <?php foreach ($assignments as $a): ?>
                <tr>
                    <td data-label="Denumire">
                        <div class="cell-value">
                            <div class="fw-semibold"><?= e($a['name']) ?></div>
                            <?php if (trim((string) $a['description']) !== ''): ?>
                                <div class="small text-muted"><?= nl2br(e($a['description'])) ?></div>
                            <?php endif; ?>
                        </div>
                    </td>
                    <td data-label="Cantitate"><?= (int) $a['quantity'] ?></td>
                    <td data-label="Atribuit la"><?= e(date('d.m.Y', strtotime($a['assigned_at']))) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        </div>
    </div>
</div>

<p class="text-muted small mt-3"><i class="bi bi-info-circle me-1"></i>Poți adăuga obiecte în inventarul tău, dar nu le poți șterge. Pentru eliminarea unui obiect, contactează un administrator.</p>
