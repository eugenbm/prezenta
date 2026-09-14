<?php
/**
 * Configurare centrală a aplicației. Citește variabilele din fișierul .env
 * aflat la rădăcina proiectului (un nivel deasupra directorului public/).
 */

declare(strict_types=1);

// --- Încărcător simplu de .env (fără dependențe externe) -------------------
function env_load(string $path): void
{
    if (!is_file($path) || !is_readable($path)) {
        return;
    }

    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#')) {
            continue;
        }
        if (!str_contains($line, '=')) {
            continue;
        }
        [$name, $value] = explode('=', $line, 2);
        $name = trim($name);
        $value = trim($value);
        // Elimină ghilimelele opționale din jurul valorii.
        if (strlen($value) >= 2 && $value[0] === '"' && $value[-1] === '"') {
            $value = substr($value, 1, -1);
        }
        if (!array_key_exists($name, $_ENV)) {
            $_ENV[$name] = $value;
            putenv("$name=$value");
        }
    }
}

function env(string $key, mixed $default = null): mixed
{
    $value = $_ENV[$key] ?? getenv($key);
    if ($value === false || $value === null) {
        return $default;
    }
    if (is_string($value)) {
        $lower = strtolower($value);
        if ($lower === 'true') return true;
        if ($lower === 'false') return false;
    }
    return $value;
}

$envFile = dirname(__DIR__) . '/.env';
env_load($envFile);

return [
    'app' => [
        'env' => env('APP_ENV', 'production'),
        'debug' => env('APP_DEBUG', false),
        'url' => rtrim((string) env('APP_URL', ''), '/'),
        'timezone' => env('APP_TIMEZONE', 'Europe/Bucharest'),
        'key' => env('APP_KEY', ''),
    ],
    'db' => [
        'host' => env('DB_HOST', 'localhost'),
        'port' => env('DB_PORT', '3306'),
        'name' => env('DB_NAME', ''),
        'user' => env('DB_USER', ''),
        'pass' => env('DB_PASS', ''),
        'charset' => 'utf8mb4',
    ],
];
