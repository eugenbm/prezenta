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
                        <li class="nav-item"><a class="nav-link <?= $navActive(['admin/dashboard']) ?>" href="<?= route_url('?route=admin/dashboard') ?>">Dashboard</a></li>
                        <li class="nav-item"><a class="nav-link <?= $navActive(['admin/activities', 'admin/activity']) ?>" href="<?= route_url('?route=admin/activities') ?>">Activități</a></li>
                        <li class="nav-item"><a class="nav-link <?= $navActive(['admin/case-sheet']) ?>" href="<?= route_url('?route=admin/case-sheets') ?>">Fișe de caz</a></li>
                        <li class="nav-item"><a class="nav-link <?= $navActive(['admin/users']) ?>" href="<?= route_url('?route=admin/users') ?>">Voluntari</a></li>
                        <li class="nav-item"><a class="nav-link <?= $navActive(['admin/reports']) ?>" href="<?= route_url('?route=admin/reports') ?>">Rapoarte</a></li>
                    <?php else: ?>
                        <li class="nav-item"><a class="nav-link <?= $navActive(['applicant/dashboard']) ?>" href="<?= route_url('?route=applicant/dashboard') ?>">Dashboard</a></li>
                        <li class="nav-item"><a class="nav-link <?= $navActive(['applicant/activities', 'applicant/activity/edit']) ?>" href="<?= route_url('?route=applicant/activities') ?>">Activitățile mele</a></li>
                        <li class="nav-item"><a class="nav-link <?= $navActive(['applicant/activity/create']) ?>" href="<?= route_url('?route=applicant/activity/create') ?>">Adaugă activitate</a></li>
                        <li class="nav-item"><a class="nav-link <?= $navActive(['applicant/profile']) ?>" href="<?= route_url('?route=applicant/profile') ?>">Profil</a></li>
                    <?php endif; ?>
                </ul>
                <div class="d-flex align-items-center flex-wrap gap-2 gap-lg-3 ms-lg-3 nav-user-group">
                    <div class="user-chip">
                        <span class="user-chip__avatar"><?= e(mb_strtoupper(mb_substr($currentUser['first_name'], 0, 1) . mb_substr($currentUser['last_name'], 0, 1))) ?></span>
                        <span class="user-chip__name"><?= e($currentUser['first_name'] . ' ' . $currentUser['last_name']) ?></span>
                    </div>
                    <a href="<?= route_url('?route=logout') ?>" class="btn btn-sm btn-outline-light">Deconectare</a>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </nav>
</header>

<main class="container py-4">
    <?php foreach (($flash['success'] ?? []) as $message): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert"><?= e($message) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endforeach; ?>
    <?php foreach (($flash['error'] ?? []) as $message): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert"><?= e($message) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endforeach; ?>

    <?php require $viewPath; ?>
</main>

<footer class="app-footer text-center py-3">
    <div class="container">
        <small>&copy; <?= date('Y') ?> <?= e(app_name()) ?> — Registru digital de activități ale voluntarilor</small>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>
