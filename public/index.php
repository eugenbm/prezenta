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

// Fără acest handler, o eroare/excepție neprinsă produce o pagină complet
// albă (display_errors e dezactivat în producție) — greu de diagnosticat.
// Acum se afișează mereu un mesaj vizibil, iar detaliile se scriu în log.
set_exception_handler(function (\Throwable $e): void {
    error_log('Eroare neprinsă: ' . $e->getMessage() . ' în ' . $e->getFile() . ':' . $e->getLine());
    if (!headers_sent()) {
        http_response_code(500);
    }
    render_fatal_error_page($e->getMessage() . ' în ' . $e->getFile() . ':' . $e->getLine());
});

register_shutdown_function(function (): void {
    $error = error_get_last();
    if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR], true)) {
        error_log('Eroare fatală: ' . $error['message'] . ' în ' . $error['file'] . ':' . $error['line']);
        if (!headers_sent()) {
            http_response_code(500);
        }
        render_fatal_error_page($error['message'] . ' în ' . $error['file'] . ':' . $error['line']);
    }
});

// --- Configurare sesiune securizată -----------------------------------------
$isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
    || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');

session_set_cookie_params([
    'lifetime' => 0,
    'path' => base_path() . '/',
    'domain' => '',
    'secure' => $isHttps,
    'httponly' => true,
    'samesite' => 'Lax',
]);
session_start();

(new App())->run();
