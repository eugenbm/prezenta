<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Setare parolă · <?= e(app_name()) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
          integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link href="<?= asset_url('css/style.css') ?>" rel="stylesheet">
</head>
<body class="auth-page">
<div class="auth-topbar">Sistem oficial de evidență a voluntarilor &middot; Salvamont Zărnești</div>

<main class="auth-main">
<div class="auth-wrap" style="max-width: 460px;">
    <img src="<?= logo_url() ?>" alt="<?= e(app_name()) ?>" class="auth-logo">

    <div class="auth-card">
        <?php if (!$validToken): ?>
            <h1 class="auth-title">Link invalid</h1>
            <p class="auth-subtitle">Linkul de setare a parolei este invalid sau a expirat.</p>
            <div class="alert alert-warning">
                Solicitați unui administrator un nou link de setare a parolei.
            </div>
            <a href="<?= route_url('?route=login') ?>" class="btn btn-outline-secondary w-100">Înapoi la autentificare</a>
        <?php else: ?>
            <h1 class="auth-title">Setați-vă parola</h1>
            <p class="auth-subtitle">Cont: <strong><?= e($userName) ?></strong></p>

            <?php foreach (($flash['error'] ?? []) as $message): ?>
                <div class="alert alert-danger"><?= e($message) ?></div>
            <?php endforeach; ?>

            <form method="post" action="<?= route_url('?route=set-password') ?>" novalidate>
                <?= csrf_field() ?>
                <input type="hidden" name="token" value="<?= e($token) ?>">
                <div class="mb-3">
                    <label class="form-label">Parolă nouă (minim 10 caractere)</label>
                    <input type="password" name="password" class="form-control <?= isset($errors['password']) ? 'is-invalid' : '' ?>" autocomplete="new-password" required>
                    <?php if (isset($errors['password'])): ?><div class="invalid-feedback"><?= e($errors['password']) ?></div><?php endif; ?>
                </div>
                <div class="mb-3">
                    <label class="form-label">Confirmare parolă</label>
                    <input type="password" name="password_confirm" class="form-control <?= isset($errors['password_confirm']) ? 'is-invalid' : '' ?>" autocomplete="new-password" required>
                    <?php if (isset($errors['password_confirm'])): ?><div class="invalid-feedback"><?= e($errors['password_confirm']) ?></div><?php endif; ?>
                </div>
                <button type="submit" class="btn btn-primary w-100">Salvează parola</button>
            </form>
        <?php endif; ?>
    </div>
</div>
</main>
</body>
</html>
