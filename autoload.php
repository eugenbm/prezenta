<?php

declare(strict_types=1);

/**
 * Autoloader minimal PSR-4 (fără Composer), mapează namespace-ul "App\" pe folderul app/.
 */

require __DIR__ . '/app/Core/helpers.php';

spl_autoload_register(function (string $class): void {
    $prefix = 'App\\';
    if (!str_starts_with($class, $prefix)) {
        return;
    }

    $relative = substr($class, strlen($prefix));
    $path = __DIR__ . '/app/' . str_replace('\\', '/', $relative) . '.php';

    if (is_file($path)) {
        require $path;
    }
});
