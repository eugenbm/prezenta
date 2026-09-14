<?php http_response_code(404); ?>
<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pagina nu a fost găsită · Salvamont Zărnești</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
          integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
</head>
<body class="d-flex align-items-center justify-content-center vh-100 bg-light">
    <div class="text-center">
        <h1 class="display-4">404</h1>
        <p class="lead">Pagina căutată nu a fost găsită.</p>
        <a href="<?= route_url('') ?>" class="btn btn-primary">Înapoi la pagina principală</a>
    </div>
</body>
</html>
