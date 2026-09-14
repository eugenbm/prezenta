<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Autentificare · Salvamont Zărnești</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
          integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link href="<?= asset_url('css/style.css') ?>" rel="stylesheet">
</head>
<body class="auth-page">
<div class="auth-topbar">Sistem oficial de evidență a voluntarilor &middot; Salvamont Zărnești</div>

<main class="auth-main">
<div class="auth-wrap">
    <img src="<?= logo_url() ?>" alt="<?= e(app_name()) ?>" class="auth-logo">

    <div class="auth-card">
        <h1 class="auth-title"><?= e(app_name()) ?></h1>
        <p class="auth-subtitle">Registru digital de activități ale voluntarilor</p>

        <?php foreach (($flash['error'] ?? []) as $message): ?>
            <div class="alert alert-danger"><?= e($message) ?></div>
        <?php endforeach; ?>
        <?php foreach (($flash['success'] ?? []) as $message): ?>
            <div class="alert alert-success"><?= e($message) ?></div>
        <?php endforeach; ?>

        <form method="post" action="<?= route_url('?route=login') ?>" novalidate>
            <?= csrf_field() ?>
            <div class="mb-3">
                <label for="username" class="form-label">Utilizator sau email</label>
                <input type="text" class="form-control <?= isset($errors['username']) ? 'is-invalid' : '' ?>"
                       id="username" name="username" value="<?= old('username') ?>"
                       autocomplete="username" required autofocus>
                <?php if (isset($errors['username'])): ?><div class="invalid-feedback"><?= e($errors['username']) ?></div><?php endif; ?>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Parolă</label>
                <div class="password-field">
                    <input type="password" class="form-control <?= isset($errors['password']) ? 'is-invalid' : '' ?>"
                           id="password" name="password" autocomplete="current-password" required>
                    <button type="button" class="password-toggle" id="togglePassword" aria-label="Arată parola" aria-pressed="false">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7-11-7-11-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                    </button>
                </div>
                <?php if (isset($errors['password'])): ?><div class="invalid-feedback d-block"><?= e($errors['password']) ?></div><?php endif; ?>
            </div>
            <button type="submit" class="btn btn-primary w-100">Autentificare</button>
        </form>
    </div>

    <p class="auth-footnote">Conturile sunt create exclusiv de administrator.</p>
</div>
</main>

<script>
    (function () {
        var toggle = document.getElementById('togglePassword');
        var input = document.getElementById('password');
        if (!toggle || !input) return;
        toggle.addEventListener('click', function () {
            var isHidden = input.type === 'password';
            input.type = isHidden ? 'text' : 'password';
            toggle.setAttribute('aria-pressed', String(isHidden));
            toggle.setAttribute('aria-label', isHidden ? 'Ascunde parola' : 'Arată parola');
        });
    })();
</script>
</body>
</html>
