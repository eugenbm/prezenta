<?php
    $route = $_GET['route'] ?? '';
    $navActive = static function (array $needles) use ($route): string {
        foreach ($needles as $needle) {
            if (str_starts_with($route, $needle)) {
                return 'active';
            }
        }
        return '';
    };
?>
<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registru Voluntari · <?= e(app_name()) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
          integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="<?= asset_url('css/style.css') ?>" rel="stylesheet">
</head>
<body>
<header class="app-header">
    <nav class="navbar navbar-expand-lg" data-bs-theme="dark">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center gap-2" href="<?= route_url($currentUser ? ($currentUser['role'] === 'admin' ? '?route=admin/dashboard' : '?route=applicant/dashboard') : '?route=login') ?>">
                <img src="<?= logo_url() ?>" alt="<?= e(app_name()) ?>" height="42">
                <span class="brand-text"><?= e(app_name()) ?><br><small>Registru voluntari</small></span>
            </a>
            <?php if ($currentUser): ?>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav" aria-controls="mainNav" aria-expanded="false" aria-label="Deschide meniul">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="mainNav">
                <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                    <?php if ($currentUser['role'] === 'admin'): ?>
                        <li class="nav-item"><a class="nav-link <?= $navActive(['admin/dashboard']) ?>" href="<?= route_url('?route=admin/dashboard') ?>"><i class="bi bi-speedometer2 me-1"></i>Dashboard</a></li>
                        <li class="nav-item"><a class="nav-link <?= $navActive(['admin/activities', 'admin/activity']) ?>" href="<?= route_url('?route=admin/activities') ?>"><i class="bi bi-list-check me-1"></i>Activități</a></li>
                        <li class="nav-item"><a class="nav-link <?= $navActive(['admin/case-sheet']) ?>" href="<?= route_url('?route=admin/case-sheets') ?>"><i class="bi bi-file-earmark-medical me-1"></i>Fișe de caz</a></li>
                        <li class="nav-item"><a class="nav-link <?= $navActive(['admin/contract']) ?>" href="<?= route_url('?route=admin/contracts') ?>"><i class="bi bi-file-earmark-text me-1"></i>Contracte</a></li>
                        <li class="nav-item"><a class="nav-link <?= $navActive(['admin/inventory']) ?>" href="<?= route_url('?route=admin/inventory') ?>"><i class="bi bi-box-seam me-1"></i>Inventar</a></li>
                        <li class="nav-item"><a class="nav-link <?= $navActive(['admin/users']) ?>" href="<?= route_url('?route=admin/users') ?>"><i class="bi bi-people me-1"></i>Voluntari</a></li>
                        <li class="nav-item"><a class="nav-link <?= $navActive(['admin/reports']) ?>" href="<?= route_url('?route=admin/reports') ?>"><i class="bi bi-bar-chart-line me-1"></i>Rapoarte</a></li>
                    <?php else: ?>
                        <li class="nav-item"><a class="nav-link <?= $navActive(['applicant/dashboard']) ?>" href="<?= route_url('?route=applicant/dashboard') ?>"><i class="bi bi-speedometer2 me-1"></i>Dashboard</a></li>
                        <li class="nav-item"><a class="nav-link <?= $navActive(['applicant/activities', 'applicant/activity/edit']) ?>" href="<?= route_url('?route=applicant/activities') ?>"><i class="bi bi-list-check me-1"></i>Activitățile mele</a></li>
                        <li class="nav-item"><a class="nav-link <?= $navActive(['applicant/activity/create']) ?>" href="<?= route_url('?route=applicant/activity/create') ?>"><i class="bi bi-plus-circle me-1"></i>Adaugă activitate</a></li>
                        <li class="nav-item"><a class="nav-link <?= $navActive(['applicant/contract']) ?>" href="<?= route_url('?route=applicant/contract') ?>"><i class="bi bi-file-earmark-text me-1"></i>Contract</a></li>
                        <li class="nav-item"><a class="nav-link <?= $navActive(['applicant/inventory']) ?>" href="<?= route_url('?route=applicant/inventory') ?>"><i class="bi bi-box-seam me-1"></i>Inventarul meu</a></li>
                        <li class="nav-item"><a class="nav-link <?= $navActive(['applicant/profile']) ?>" href="<?= route_url('?route=applicant/profile') ?>"><i class="bi bi-person me-1"></i>Profil</a></li>
                    <?php endif; ?>
                </ul>
                <div class="d-flex align-items-center flex-wrap gap-2 gap-lg-3 ms-lg-3 nav-user-group">
                    <div class="user-chip">
                        <span class="user-chip__avatar"><?= e(mb_strtoupper(mb_substr($currentUser['first_name'], 0, 1) . mb_substr($currentUser['last_name'], 0, 1))) ?></span>
                        <span class="user-chip__name"><?= e($currentUser['first_name'] . ' ' . $currentUser['last_name']) ?></span>
                    </div>
                    <a href="<?= route_url('?route=logout') ?>" class="btn btn-sm btn-outline-light"><i class="bi bi-box-arrow-right me-1"></i>Deconectare</a>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </nav>
</header>

<main class="container py-4">
    <div class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index: 1090;">
        <?php foreach (($flash['success'] ?? []) as $message): ?>
            <div class="toast app-toast align-items-center text-bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="d-flex">
                    <div class="toast-body"><i class="bi bi-check-circle-fill me-2"></i><?= e($message) ?></div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
            </div>
        <?php endforeach; ?>
        <?php foreach (($flash['error'] ?? []) as $message): ?>
            <div class="toast app-toast align-items-center text-bg-danger border-0" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="d-flex">
                    <div class="toast-body"><i class="bi bi-exclamation-triangle-fill me-2"></i><?= e($message) ?></div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <?php require $viewPath; ?>
</main>

<footer class="app-footer text-center py-3">
    <div class="container">
        <small>&copy; <?= date('Y') ?> <?= e(app_name()) ?> — Registru digital de activități ale voluntarilor</small>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
<script>
    // Afișează automat mesajele flash ca toast-uri.
    document.querySelectorAll('.app-toast').forEach(function (el) {
        new bootstrap.Toast(el, { delay: 5000 }).show();
    });
</script>
</body>
</html>
