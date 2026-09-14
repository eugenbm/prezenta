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
<div class="auth-card">
    <div class="text-center mb-4">
        <img src="<?= logo_url() ?>" alt="Salvamont Zărnești" height="72">
        <h1 class="h4 mt-3 mb-0">Registru digital de activități</h1>
        <p class="text-muted">Salvamont Zărnești</p>
    </div>

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
                   id="username" name="username" value="<?= old('username') ?>" required autofocus>
            <?php if (isset($errors['username'])): ?><div class="invalid-feedback"><?= e($errors['username']) ?></div><?php endif; ?>
        </div>
        <div class="mb-3">
            <label for="password" class="form-label">Parolă</label>
            <input type="password" class="form-control <?= isset($errors['password']) ? 'is-invalid' : '' ?>"
                   id="password" name="password" required>
            <?php if (isset($errors['password'])): ?><div class="invalid-feedback"><?= e($errors['password']) ?></div><?php endif; ?>
        </div>
        <button type="submit" class="btn btn-primary w-100">Autentificare</button>
    </form>
    <p class="text-center text-muted small mt-4 mb-0">Conturile sunt create exclusiv de administrator.</p>
</div>
</body>
</html>
