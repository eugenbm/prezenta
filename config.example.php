<?php
// Copiați acest fișier ca „config.php” (același folder, direct sub rădăcina
// proiectului, în afara public_html) și completați valorile reale.
// „config.php” NU trebuie urcat niciodată în control de versiuni (vezi .gitignore).
return [
    'app' => [
        'name' => 'Salvamont Zărnești',
        'base_url' => '/registru/public',
        // URL-ul public COMPLET al aplicației (schemă + domeniu + cale), folosit
        // pentru linkurile din emailuri (ex. setarea parolei). Dacă e gol, se
        // deduce automat din cererea curentă. Ex: 'https://salvamontzarnesti.ro/registru/public'.
        'url' => '',
        'logo_path' => '/assets/img/logo/salvamont-placeholder.png',
        // Opțional — implicit false / 'Europe/Bucharest' dacă lipsesc.
        // Setați 'debug' => true temporar doar cât depanați o eroare, apoi reveniți la false.
        'debug' => false,
        'timezone' => 'Europe/Bucharest',
        // Obiectivul de zile cu activitate pe an calendaristic, pentru fiecare aspirant.
        'annual_days_goal' => 20,
    ],
    'db' => [
        'host' => 'localhost',
        'database' => 'cpanelusername_registru',
        'username' => 'cpanelusername_registru',
        'password' => '',
        'charset' => 'utf8mb4',
    ],
    // Contul de email folosit pentru trimiterea notificărilor (setarea parolei etc.).
    'mail' => [
        // Pe cPanel folosiți 'localhost' (firewall-ul blochează SMTP-ul extern);
        // numele public (ex. mail.domeniu.ro) merge doar de pe alte servere.
        'host' => 'localhost',
        'port' => 465,
        // 'ssl' pentru portul 465, 'tls' (STARTTLS) pentru portul 587.
        'encryption' => 'ssl',
        'username' => 'registru.voluntari@salvamontzarnesti.ro',
        'password' => '', // Parola căsuței de email — se completează DOAR în config.php (pe server).
        'from_email' => 'registru.voluntari@salvamontzarnesti.ro',
        'from_name' => 'Salvamont Zărnești',
    ],
    'roles' => [
        'applicant',
        'admin',
    ],
];
