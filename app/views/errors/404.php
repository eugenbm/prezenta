<?php http_response_code(404); ?>
<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="color-scheme" content="light">
    <meta name="theme-color" content="#841821">
    <title>Pagina nu a fost găsită · Salvamont Zărnești</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
          integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link href="<?= asset_url('css/style.css') ?>" rel="stylesheet">
</head>
<body class="auth-page">
<main class="auth-main">
    <div class="auth-wrap text-center">
        <div class="auth-card">
            <div class="error-code">404</div>
            <h1 class="auth-title">Pagina nu a fost găsită</h1>
            <p class="auth-subtitle">Adresa accesată nu există sau a fost mutată.</p>
            <a href="<?= route_url('') ?>" class="btn btn-primary">Înapoi la pagina principală</a>
        </div>
    </div>
</main>
</body>
</html>
