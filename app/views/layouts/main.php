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
    <nav class="navbar navbar-expand-lg">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center gap-2" href="<?= route_url($currentUser ? ($currentUser['role'] === 'admin' ? '?route=admin/dashboard' : '?route=applicant/dashboard') : '?route=login') ?>">
                <img src="<?= logo_url() ?>" alt="<?= e(app_name()) ?>" height="42">
                <span class="brand-text"><?= e(app_name()) ?><br><small>Registru voluntari</small></span>
            </a>
            <?php if ($currentUser): ?>
            <div class="d-flex align-items-center gap-3">
                <ul class="navbar-nav flex-row gap-3">
                    <?php if ($currentUser['role'] === 'admin'): ?>
                        <li class="nav-item"><a class="nav-link" href="<?= route_url('?route=admin/dashboard') ?>">Dashboard</a></li>
                        <li class="nav-item"><a class="nav-link" href="<?= route_url('?route=admin/activities') ?>">Activități</a></li>
                        <li class="nav-item"><a class="nav-link" href="<?= route_url('?route=admin/users') ?>">Voluntari</a></li>
                        <li class="nav-item"><a class="nav-link" href="<?= route_url('?route=admin/reports') ?>">Rapoarte</a></li>
                    <?php else: ?>
                        <li class="nav-item"><a class="nav-link" href="<?= route_url('?route=applicant/dashboard') ?>">Dashboard</a></li>
                        <li class="nav-item"><a class="nav-link" href="<?= route_url('?route=applicant/activities') ?>">Activitățile mele</a></li>
                        <li class="nav-item"><a class="nav-link" href="<?= route_url('?route=applicant/activity/create') ?>">Adaugă activitate</a></li>
                        <li class="nav-item"><a class="nav-link" href="<?= route_url('?route=applicant/profile') ?>">Profil</a></li>
                    <?php endif; ?>
                </ul>
                <span class="text-muted small d-none d-md-inline"><?= e($currentUser['first_name'] . ' ' . $currentUser['last_name']) ?></span>
                <a href="<?= route_url('?route=logout') ?>" class="btn btn-sm btn-outline-light">Deconectare</a>
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
