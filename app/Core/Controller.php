<?php

declare(strict_types=1);

namespace App\Core;

abstract class Controller
{
    protected function render(string $view, array $data = []): void
    {
        $viewPath = dirname(__DIR__) . '/views/' . $view . '.php';
        if (!is_file($viewPath)) {
            throw new \RuntimeException("View-ul '{$view}' nu a fost găsit la {$viewPath}.");
        }

        $flash = flash_get_all();
        $errors = get_errors();
        $currentUser = current_user();

        extract($data, EXTR_SKIP);
        require dirname(__DIR__) . '/views/layouts/main.php';
        // Datele vechi de formular sunt relevante o singură dată (imediat după eroarea de validare).
        clear_old();
    }

    protected function renderPartialView(string $view, array $data = []): void
    {
        $viewPath = dirname(__DIR__) . '/views/' . $view . '.php';
        if (!is_file($viewPath)) {
            throw new \RuntimeException("View-ul '{$view}' nu a fost găsit la {$viewPath}.");
        }
        extract($data, EXTR_SKIP);
        require $viewPath;
    }

    /**
     * La fel ca render(), dar fără layout-ul principal (header/navbar/footer) —
     * pentru pagini de sine stătătoare (autentificare, configurare inițială),
     * care sunt deja documente HTML complete.
     */
    protected function renderStandalone(string $view, array $data = []): void
    {
        $viewPath = dirname(__DIR__) . '/views/' . $view . '.php';
        if (!is_file($viewPath)) {
            throw new \RuntimeException("View-ul '{$view}' nu a fost găsit la {$viewPath}.");
        }

        $flash = flash_get_all();
        $errors = get_errors();
        $currentUser = current_user();

        extract($data, EXTR_SKIP);
        require $viewPath;
        clear_old();
    }

    protected function redirect(string $url): never
    {
        redirect($url);
    }

    protected function input(string $key, string $default = ''): string
    {
        return trim((string) ($_POST[$key] ?? $default));
    }

    protected function isPost(): bool
    {
        return ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST';
    }
}
