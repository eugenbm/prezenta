<?php
// Copiați acest fișier ca „config.php” (același folder, direct sub rădăcina
// proiectului, în afara public_html) și completați valorile reale.
// „config.php” NU trebuie urcat niciodată în control de versiuni (vezi .gitignore).
return [
    'app' => [
        'name' => 'Salvamont Zărnești',
        'base_url' => '/registru/public',
        'logo_path' => '/assets/img/logo/salvamont-placeholder.svg',
        // Opțional — implicit false / 'Europe/Bucharest' dacă lipsesc.
        // Setați 'debug' => true temporar doar cât depanați o eroare, apoi reveniți la false.
        'debug' => false,
        'timezone' => 'Europe/Bucharest',
    ],
    'db' => [
        'host' => 'localhost',
        'database' => 'cpanelusername_registru',
        'username' => 'cpanelusername_registru',
        'password' => '',
        'charset' => 'utf8mb4',
    ],
    'roles' => [
        'applicant',
        'admin',
    ],
];
