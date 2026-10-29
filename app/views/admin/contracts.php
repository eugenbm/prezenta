<?php /** @var array $contracts */ ?>
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <h1 class="h3 mb-0">Contracte de voluntariat</h1>
    <span class="text-muted"><?= count($contracts) ?> contract<?= count($contracts) === 1 ? '' : 'e' ?> semnat<?= count($contracts) === 1 ? '' : 'e' ?></span>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
        <table class="table table-hover table-stack mb-0">
            <thead>
                <tr><th>Voluntar</th><th>Rol</th><th>Email</th><th>Semnat la</th><th></th></tr>
            </thead>
            <tbody>
            <?php if (!$contracts): ?>
                <tr><td colspan="5" class="cell-empty py-4">
                    <div class="empty-state"><i class="bi bi-file-earmark-text"></i>Niciun contract semnat încă.</div>
                </td></tr>
            <?php endif; ?>
            <?php foreach ($contracts as $c): ?>
                <?php $name = trim($c['last_name'] . ' ' . $c['first_name']);
                      $slug = trim((string) preg_replace('/[^A-Za-z0-9_-]+/', '-', $c['last_name'] . '-' . $c['first_name']), '-');
                      $slug = $slug !== '' ? $slug : (string) $c['id']; ?>
                <tr>
                    <td data-label="Voluntar"><?= e($name) ?></td>
                    <td data-label="Rol"><?= role_badge($c['role']) ?></td>
                    <td data-label="Email"><?= e($c['email']) ?></td>
                    <td data-label="Semnat la"><?= e(date('d.m.Y H:i', strtotime($c['signed_at']))) ?></td>
                    <td data-label="Acțiuni" class="cell-actions d-flex gap-1 flex-wrap">
                        <a href="<?= route_url('?route=admin/contract/download&id=' . (int) $c['id']) ?>" download
                           class="btn btn-sm btn-primary js-download" data-filename="contract-<?= e($slug) ?>.pdf">
                            <i class="bi bi-download me-1"></i>Descarcă PDF
                        </a>
                        <a href="<?= route_url('?route=admin/contract/preview&id=' . (int) $c['id']) ?>" target="_blank" rel="noopener"
                           class="btn btn-sm btn-outline-secondary">
                            <i class="bi bi-eye me-1"></i>Previzualizează
                        </a>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        </div>
    </div>
</div>

<script>
(function () {
    function downloadPdf(url, filename) {
        if (!window.fetch || !window.URL || !window.URL.createObjectURL) { window.location = url; return; }
        fetch(url, { credentials: 'same-origin' })
            .then(function (r) { if (!r.ok) { throw new Error('HTTP ' + r.status); } return r.blob(); })
            .then(function (blob) {
                var objUrl = window.URL.createObjectURL(blob);
                var a = document.createElement('a');
                a.href = objUrl; a.download = filename || 'contract.pdf';
                document.body.appendChild(a); a.click(); a.remove();
                setTimeout(function () { window.URL.revokeObjectURL(objUrl); }, 4000);
            })
            .catch(function () { window.location = url; });
    }
    document.querySelectorAll('.js-download').forEach(function (el) {
        el.addEventListener('click', function (e) {
            e.preventDefault();
            downloadPdf(el.getAttribute('href'), el.getAttribute('data-filename'));
        });
    });
})();
</script>
