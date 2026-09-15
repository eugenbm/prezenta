<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="color-scheme" content="light">
    <meta name="theme-color" content="#841821">
    <title>Autentificare · Salvamont Zărnești</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
          integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link href="<?= asset_url('css/style.css') ?>" rel="stylesheet">
</head>
<body class="auth-page auth-login">
<main class="auth-main">
    <div class="auth-shell">
        <aside class="auth-brand">
            <div class="auth-brand__decor" aria-hidden="true">
                <span class="auth-orb auth-orb--1"></span>
                <span class="auth-orb auth-orb--2"></span>
                <span class="auth-orb auth-orb--3"></span>
            </div>

            <span class="auth-eyebrow">Sistem oficial de evidență</span>

            <div class="auth-brand__body">
                <img src="<?= logo_url() ?>" alt="<?= e(app_name()) ?>" class="auth-brand__logo">
                <h2 class="auth-brand__title"><?= e(app_name()) ?></h2>
                <p class="auth-brand__text">Registru digital de activități ale voluntarilor — evidența orelor și a intervențiilor echipei, într-o platformă sigură.</p>
            </div>

            <ul class="auth-brand__list">
                <li>
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="20 6 9 17 4 12"/></svg>
                    Acces securizat, dedicat voluntarilor
                </li>
                <li>
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="20 6 9 17 4 12"/></svg>
                    Evidența activităților și a orelor
                </li>
                <li>
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="20 6 9 17 4 12"/></svg>
                    Rapoarte clare pentru coordonatori
                </li>
            </ul>
        </aside>

        <section class="auth-panel">
            <div class="auth-formwrap">
                <header class="auth-formwrap__head">
                    <h1 class="auth-heading">Autentificare</h1>
                    <p class="auth-subheading">Introduceți datele de acces pentru a continua.</p>
                </header>

                <?php foreach (($flash['error'] ?? []) as $message): ?>
                    <div class="alert alert-danger" role="alert"><?= e($message) ?></div>
                <?php endforeach; ?>
                <?php foreach (($flash['success'] ?? []) as $message): ?>
                    <div class="alert alert-success" role="alert"><?= e($message) ?></div>
                <?php endforeach; ?>

                <form id="loginForm" method="post" action="<?= route_url('?route=login') ?>" novalidate>
                    <?= csrf_field() ?>
                    <div class="auth-field">
                        <label for="username" class="form-label">Utilizator sau email</label>
                        <div class="auth-input">
                            <svg class="auth-input__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                            <input type="text" class="form-control <?= isset($errors['username']) ? 'is-invalid' : '' ?>"
                                   id="username" name="username" value="<?= old('username') ?>"
                                   autocomplete="username" required autofocus>
                        </div>
                        <?php if (isset($errors['username'])): ?><div class="invalid-feedback"><?= e($errors['username']) ?></div><?php endif; ?>
                    </div>
                    <div class="auth-field">
                        <label for="password" class="form-label">Parolă</label>
                        <div class="auth-input password-field">
                            <svg class="auth-input__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                            <input type="password" class="form-control <?= isset($errors['password']) ? 'is-invalid' : '' ?>"
                                   id="password" name="password" autocomplete="current-password" required>
                            <button type="button" class="password-toggle" id="togglePassword" aria-label="Arată parola" aria-pressed="false">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7-11-7-11-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                            </button>
                        </div>
                        <?php if (isset($errors['password'])): ?><div class="invalid-feedback"><?= e($errors['password']) ?></div><?php endif; ?>
                    </div>
                    <button type="submit" class="btn btn-primary w-100 auth-submit" id="loginSubmit">
                        <span class="auth-submit__label">Autentificare</span>
                        <span class="auth-submit__spinner" aria-hidden="true"></span>
                    </button>
                </form>

                <p class="auth-footnote">Conturile sunt create exclusiv de administrator.</p>
                <div class="auth-secure-note">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10Z"/><path d="m9 12 2 2 4-4"/></svg>
                    Conexiune securizată
                </div>
            </div>
        </section>
    </div>
</main>

<script>
    (function () {
        var toggle = document.getElementById('togglePassword');
        var input = document.getElementById('password');
        if (toggle && input) {
            toggle.addEventListener('click', function () {
                var isHidden = input.type === 'password';
                input.type = isHidden ? 'text' : 'password';
                toggle.setAttribute('aria-pressed', String(isHidden));
                toggle.setAttribute('aria-label', isHidden ? 'Ascunde parola' : 'Arată parola');
            });
        }

        var form = document.getElementById('loginForm');
        var submitBtn = document.getElementById('loginSubmit');
        if (form && submitBtn) {
            form.addEventListener('submit', function () {
                submitBtn.classList.add('is-loading');
                submitBtn.setAttribute('aria-busy', 'true');
                window.setTimeout(function () { submitBtn.disabled = true; }, 0);
            });
        }
    })();
</script>
</body>
</html>
