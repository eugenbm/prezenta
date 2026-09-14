<?php

declare(strict_types=1);

/**
 * Front controller — punctul unic de intrare al aplicației.
 * Configurează document root-ul domeniului/subdomeniului către acest folder (public/).
 */

require dirname(__DIR__) . '/autoload.php';

use App\Core\App;

// config() (definit în app/Core/helpers.php) încarcă fișierul o singură dată
// și îl păstrează în cache — nu necesită un require separat aici.
date_default_timezone_set((string) config('app.timezone', 'Europe/Bucharest'));

if (config('app.debug')) {
    ini_set('display_errors', '1');
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', '0');
    error_reporting(E_ALL & ~E_DEPRECATED);
}

// --- Configurare sesiune securizată -----------------------------------------
$isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
    || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');

session_set_cookie_params([
    'lifetime' => 0,
    'path' => rtrim(dirname($_SERVER['SCRIPT_NAME']), '/') . '/',
    'domain' => '',
    'secure' => $isHttps,
    'httponly' => true,
    'samesite' => 'Lax',
]);
session_start();

(new App())->run();
