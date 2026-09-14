<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configurare inițială · Salvamont Zărnești</title>
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
        <h1 class="auth-title">Configurare inițială</h1>
        <p class="auth-subtitle">Creați primul cont de administrator</p>

        <form method="post" action="<?= route_url('?route=setup') ?>" novalidate>
            <?= csrf_field() ?>
            <div class="row">
                <div class="col-6 mb-3">
                    <label class="form-label">Prenume</label>
                    <input type="text" name="first_name" class="form-control <?= isset($errors['first_name']) ? 'is-invalid' : '' ?>" value="<?= old('first_name') ?>" autocomplete="given-name" required>
                    <?php if (isset($errors['first_name'])): ?><div class="invalid-feedback"><?= e($errors['first_name']) ?></div><?php endif; ?>
                </div>
                <div class="col-6 mb-3">
                    <label class="form-label">Nume</label>
                    <input type="text" name="last_name" class="form-control <?= isset($errors['last_name']) ? 'is-invalid' : '' ?>" value="<?= old('last_name') ?>" autocomplete="family-name" required>
                    <?php if (isset($errors['last_name'])): ?><div class="invalid-feedback"><?= e($errors['last_name']) ?></div><?php endif; ?>
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-control <?= isset($errors['email']) ? 'is-invalid' : '' ?>" value="<?= old('email') ?>" autocomplete="email" required>
                <?php if (isset($errors['email'])): ?><div class="invalid-feedback"><?= e($errors['email']) ?></div><?php endif; ?>
            </div>
            <div class="mb-3">
                <label class="form-label">Utilizator</label>
                <input type="text" name="username" class="form-control <?= isset($errors['username']) ? 'is-invalid' : '' ?>" value="<?= old('username') ?>" autocomplete="username" required>
                <?php if (isset($errors['username'])): ?><div class="invalid-feedback"><?= e($errors['username']) ?></div><?php endif; ?>
            </div>
            <div class="mb-3">
                <label class="form-label">Parolă (minim 10 caractere)</label>
                <input type="password" name="password" class="form-control <?= isset($errors['password']) ? 'is-invalid' : '' ?>" autocomplete="new-password" required>
                <?php if (isset($errors['password'])): ?><div class="invalid-feedback"><?= e($errors['password']) ?></div><?php endif; ?>
            </div>
            <div class="mb-3">
                <label class="form-label">Confirmare parolă</label>
                <input type="password" name="password_confirm" class="form-control <?= isset($errors['password_confirm']) ? 'is-invalid' : '' ?>" autocomplete="new-password" required>
                <?php if (isset($errors['password_confirm'])): ?><div class="invalid-feedback"><?= e($errors['password_confirm']) ?></div><?php endif; ?>
            </div>
            <button type="submit" class="btn btn-primary w-100">Creează contul de administrator</button>
        </form>
    </div>

    <p class="auth-footnote">Această pagină se dezactivează automat după crearea primului administrator.</p>
</div>
</main>
</body>
</html>
