<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Fișe de caz</h1>
    <div class="d-flex gap-2 flex-wrap">
        <a href="<?= route_url('?route=admin/case-sheets/stats') ?>" class="btn btn-outline-secondary">Statistici</a>
        <a href="<?= route_url('?route=admin/case-sheet/create') ?>" class="btn btn-primary">Fișă nouă</a>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
        <table class="table table-hover table-stack mb-0">
            <thead>
                <tr><th>Nr. fișă</th><th>Data</th><th>Victimă</th><th>Masiv montan</th><th>Întocmit</th><th>Creat la</th><th></th></tr>
            </thead>
            <tbody>
            <?php if (!$sheets): ?>
                <tr><td colspan="7" class="cell-empty text-center text-muted py-4">Nicio fișă de caz salvată.</td></tr>
            <?php endif; ?>
            <?php foreach ($sheets as $sheet): ?>
                <tr>
                    <td data-label="Nr. fișă"><?= e($sheet['nr_fisa']) ?: '—' ?></td>
                    <td data-label="Data"><?= $sheet['data_fisa'] ? format_date_ro($sheet['data_fisa']) : '—' ?></td>
                    <td data-label="Victimă"><?= e($sheet['victima_nume']) ?: '—' ?></td>
                    <td data-label="Masiv montan"><?= e($sheet['masiv_montan']) ?: '—' ?></td>
                    <td data-label="Întocmit"><?= e($sheet['intocmit']) ?: '—' ?></td>
                    <td data-label="Creat la"><?= e(date('d.m.Y H:i', strtotime($sheet['created_at']))) ?></td>
                    <td data-label="Acțiuni" class="cell-actions d-flex gap-1 flex-wrap">
                        <a href="<?= route_url('?route=admin/case-sheet/download&id=' . $sheet['id']) ?>" download class="btn btn-sm btn-primary js-download" data-filename="fisa-caz-<?= e($sheet['nr_fisa'] !== '' ? preg_replace('/[^A-Za-z0-9_-]/', '', $sheet['nr_fisa']) : (string) $sheet['id']) ?>.pdf">Descarcă PDF</a>
                        <a href="<?= route_url('?route=admin/case-sheet/print&id=' . $sheet['id']) ?>" target="_blank" class="btn btn-sm btn-outline-secondary">Vizualizează</a>
                        <form method="post" action="<?= route_url('?route=admin/case-sheet/delete') ?>" class="d-inline" onsubmit="return confirm('Ștergeți definitiv această fișă de caz?');">
                            <?= csrf_field() ?>
                            <input type="hidden" name="id" value="<?= $sheet['id'] ?>">
                            <button type="submit" class="btn btn-sm btn-outline-danger">Șterge</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        </div>
    </div>
</div>
<?php if ($page > 1 || $hasMore): ?>
<nav class="d-flex justify-content-between align-items-center mt-3">
    <?php if ($page > 1): ?>
        <a class="btn btn-outline-secondary btn-sm" href="<?= route_url('?route=admin/case-sheets&page=' . ($page - 1)) ?>">← Anterioarele 10</a>
    <?php else: ?>
        <span class="btn btn-outline-secondary btn-sm disabled">← Anterioarele 10</span>
    <?php endif; ?>
    <span class="text-muted small">Pagina <?= (int) $page ?></span>
    <?php if ($hasMore): ?>
        <a class="btn btn-outline-secondary btn-sm" href="<?= route_url('?route=admin/case-sheets&page=' . ($page + 1)) ?>">Următoarele 10 →</a>
    <?php else: ?>
        <span class="btn btn-outline-secondary btn-sm disabled">Următoarele 10 →</span>
    <?php endif; ?>
</nav>
<?php endif; ?>
<?php $autoDownloadId = isset($_GET['download']) ? (int) $_GET['download'] : 0; ?>
<script>
    // Descarcă PDF-ul ca fișier (fetch -> blob), pentru comportament consecvent
    // inclusiv pe iOS Safari, care altfel deschide PDF-ul inline. Cu rezervă
    // la navigarea normală dacă fetch/blob nu sunt disponibile.
    (function () {
        function downloadPdf(url, filename) {
            if (!window.fetch || !window.URL || !window.URL.createObjectURL) {
                window.location = url;
                return;
            }
            fetch(url, { credentials: 'same-origin' })
                .then(function (r) { if (!r.ok) { throw new Error('HTTP ' + r.status); } return r.blob(); })
                .then(function (blob) {
                    var objUrl = window.URL.createObjectURL(blob);
                    var a = document.createElement('a');
                    a.href = objUrl;
                    a.download = filename || 'fisa-caz.pdf';
                    document.body.appendChild(a);
                    a.click();
                    a.remove();
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

        <?php if ($autoDownloadId): ?>
        downloadPdf(<?= json_encode(route_url('?route=admin/case-sheet/download&id=' . $autoDownloadId)) ?>, 'fisa-caz-<?= $autoDownloadId ?>.pdf');
        <?php endif; ?>
    })();
</script>
