<?php
    /** @var array $items */
    /** @var int $total */
    /** @var int $pageSize */
?>
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <h1 class="h3 mb-0">Inventar</h1>
    <div class="d-flex gap-2 flex-wrap">
        <a href="<?= route_url('?route=admin/users/inventory') ?>" class="btn btn-outline-primary"><i class="bi bi-person-badge me-1"></i>Inventar pe voluntar</a>
        <a href="<?= route_url('?route=admin/inventory/create') ?>" class="btn btn-primary"><i class="bi bi-plus-circle me-1"></i>Obiect nou</a>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
        <table class="table table-hover table-stack mb-0">
            <thead>
                <tr><th>Denumire</th><th>Total</th><th>Atribuite</th><th>Disponibile</th><th></th></tr>
            </thead>
            <tbody id="inventory-rows">
            <?php if (!$items): ?>
                <tr><td colspan="5" class="cell-empty py-4">
                    <div class="empty-state"><i class="bi bi-box-seam"></i>Niciun obiect în inventar încă.</div>
                </td></tr>
            <?php endif; ?>
            <?php require __DIR__ . '/_inventory_rows.php'; ?>
            </tbody>
        </table>
        </div>
    </div>
</div>

<?php if ($total > $pageSize): ?>
<div class="text-center mt-3">
    <button type="button" id="inventory-load-more"
            class="btn btn-outline-secondary"
            data-url="<?= route_url('?route=admin/inventory/more') ?>"
            data-offset="<?= (int) count($items) ?>"
            data-total="<?= (int) $total ?>">
        <i class="bi bi-arrow-down-circle me-1"></i>Încarcă încă <?= (int) $pageSize ?>
    </button>
    <div class="small text-muted mt-2">
        <span id="inventory-shown"><?= (int) count($items) ?></span> din <?= (int) $total ?> obiecte
    </div>
</div>

<script>
(function () {
    var btn = document.getElementById('inventory-load-more');
    if (!btn) { return; }
    var tbody = document.getElementById('inventory-rows');
    var shown = document.getElementById('inventory-shown');
    var total = parseInt(btn.dataset.total, 10);

    btn.addEventListener('click', function () {
        var offset = parseInt(btn.dataset.offset, 10);
        btn.disabled = true;
        var originalHtml = btn.innerHTML;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Se încarcă…';

        fetch(btn.dataset.url + '&offset=' + offset, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            credentials: 'same-origin'
        })
            .then(function (res) { return res.text(); })
            .then(function (html) {
                var temp = document.createElement('tbody');
                temp.innerHTML = html.trim();
                var added = temp.querySelectorAll('tr').length;
                while (temp.firstChild) { tbody.appendChild(temp.firstChild); }

                offset += added;
                btn.dataset.offset = offset;
                if (shown) { shown.textContent = offset; }

                if (added === 0 || offset >= total) {
                    btn.parentNode.removeChild(btn);
                } else {
                    btn.disabled = false;
                    btn.innerHTML = originalHtml;
                }
            })
            .catch(function () {
                btn.disabled = false;
                btn.innerHTML = originalHtml;
            });
    });
})();
</script>
<?php endif; ?>
