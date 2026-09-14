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
<div class="auth-shell">
    <aside class="auth-brand">
        <div class="auth-brand-inner">
            <img src="<?= logo_url() ?>" alt="<?= e(app_name()) ?>" class="auth-logo">
            <h1 class="auth-brand-title"><?= e(app_name()) ?></h1>
            <p class="auth-brand-tagline">Registru digital de activități ale voluntarilor</p>
            <ul class="auth-brand-points">
                <li>Evidență centralizată a intervențiilor, patrulărilor și activităților</li>
                <li>Flux transparent de aprobare, cu istoric complet</li>
                <li>Acces securizat, pe roluri, exclusiv pe bază de cont creat de administrator</li>
            </ul>
        </div>
        <div class="auth-brand-mountains" aria-hidden="true"></div>
    </aside>

    <section class="auth-form-panel">
        <div class="auth-form-wrap">
            <div class="auth-card">
                <h2 class="auth-form-title h4">Autentificare</h2>
                <p class="auth-form-subtitle">Introduceți datele contului pentru a continua.</p>

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
                               autocomplete="username" inputmode="email" required autofocus>
                        <?php if (isset($errors['username'])): ?><div class="invalid-feedback"><?= e($errors['username']) ?></div><?php endif; ?>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Parolă</label>
                        <div class="password-field">
                            <input type="password" class="form-control <?= isset($errors['password']) ? 'is-invalid' : '' ?>"
                                   id="password" name="password" autocomplete="current-password" required>
                            <button type="button" class="password-toggle" id="togglePassword" aria-label="Arată parola" aria-pressed="false">
                                <svg id="eyeIcon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7-11-7-11-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                            </button>
                        </div>
                        <?php if (isset($errors['password'])): ?><div class="invalid-feedback d-block"><?= e($errors['password']) ?></div><?php endif; ?>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Autentificare</button>
                </form>

                <div class="auth-secure-note">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="11" width="18" height="10" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                    Conexiune securizată — conturile sunt create exclusiv de administrator
                </div>
            </div>
            <p class="auth-footnote">&copy; <?= date('Y') ?> <?= e(app_name()) ?></p>
        </div>
    </section>
</div>

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
