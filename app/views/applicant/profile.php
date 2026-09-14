<h1 class="h3 mb-4">Profilul meu</h1>

<div class="row g-4">
    <div class="col-md-5">
        <div class="card">
            <div class="card-header">Date cont</div>
            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-5">Nume</dt><dd class="col-7"><?= e($user['first_name'] . ' ' . $user['last_name']) ?></dd>
                    <dt class="col-5">Utilizator</dt><dd class="col-7"><?= e($user['username']) ?></dd>
                    <dt class="col-5">Email</dt><dd class="col-7"><?= e($user['email']) ?></dd>
                    <dt class="col-5">Rol</dt><dd class="col-7">Aspirant</dd>
                    <dt class="col-5">Membru din</dt><dd class="col-7"><?= format_date_ro($user['created_at']) ?></dd>
                </dl>
                <p class="text-muted small mt-3 mb-0">Pentru schimbarea datelor sau resetarea parolei, contactați un administrator.</p>
            </div>
        </div>
    </div>
    <div class="col-md-7">
        <div class="card">
            <div class="card-header">Sumar activități</div>
            <div class="card-body">
                <div class="row g-3 text-center">
                    <div class="col-3"><div class="stat-value"><?= $counts['total'] ?></div><div class="stat-label">Total</div></div>
                    <div class="col-3"><div class="stat-value text-success"><?= $counts['approved'] ?></div><div class="stat-label">Aprobate</div></div>
                    <div class="col-3"><div class="stat-value text-warning"><?= $counts['pending'] ?></div><div class="stat-label">În așteptare</div></div>
                    <div class="col-3"><div class="stat-value text-danger"><?= $counts['rejected'] ?></div><div class="stat-label">Respinse</div></div>
                </div>
                <hr>
                <div class="text-center">
                    <div class="stat-value"><?= number_format($approvedHours, 1) ?> h</div>
                    <div class="stat-label">Total ore aprobate</div>
                </div>
            </div>
        </div>
    </div>
</div>
