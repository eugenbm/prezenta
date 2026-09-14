<?php

declare(strict_types=1);

use App\Core\Auth;
use App\Core\Csrf;

/**
 * Returnează o valoare din configurația aplicației, citită din config.php
 * aflat direct sub rădăcina proiectului, în afara public_html.
 * Ex: config('db.host') sau config('app') pentru toată secțiunea.
 */
function config(string $key, mixed $default = null): mixed
{
    static $config = null;
    if ($config === null) {
        $path = dirname(__DIR__, 2) . '/config.php';
        if (!is_file($path)) {
            http_response_code(500);
            die('Fișierul de configurare config.php lipsește. Copiați config.example.php ca config.php și completați datele de conectare la baza de date.');
        }
        $config = require $path;
    }

    $segments = explode('.', $key);
    $value = $config;
    foreach ($segments as $segment) {
        if (!is_array($value) || !array_key_exists($segment, $value)) {
            return $default;
        }
        $value = $value[$segment];
    }

    return $value;
}

/** Calea de bază a aplicației, din config('app.base_url'); detectare automată doar ca rezervă. */
function base_path(): string
{
    static $base = null;
    if ($base === null) {
        $configured = (string) config('app.base_url', '');
        $base = $configured !== ''
            ? rtrim($configured, '/')
            : rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/')), '/');
    }

    return $base;
}

/** Construiește un URL relativ către o rută internă, ex: route_url('?route=login'). */
function route_url(string $query = ''): string
{
    return base_path() . '/index.php' . $query;
}

/** Construiește un URL relativ către un asset din public/assets. */
function asset_url(string $path): string
{
    return base_path() . '/assets/' . ltrim($path, '/');
}

/** Construiește URL-ul siglei, din config('app.logo_path'). */
function logo_url(): string
{
    return base_path() . '/' . ltrim((string) config('app.logo_path', '/assets/img/logo/salvamont-placeholder.svg'), '/');
}

function app_name(): string
{
    return (string) config('app.name', 'Salvamont Zărnești');
}

function redirect(string $url): never
{
    header('Location: ' . $url, true, 302);
    exit;
}

/** Escapare HTML sigură pentru afișarea datelor introduse de utilizator. */
function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

/** Recuperează o valoare introdusă anterior într-un formular (după o eroare de validare). */
function old(string $key, string $default = ''): string
{
    return e($_SESSION['_old'][$key] ?? $default);
}

function flash_set(string $type, string $message): void
{
    $_SESSION['_flash'][$type][] = $message;
}

function flash_get_all(): array
{
    $flash = $_SESSION['_flash'] ?? [];
    unset($_SESSION['_flash']);
    return $flash;
}

function set_old_and_errors(array $old, array $errors): void
{
    $_SESSION['_old'] = $old;
    $_SESSION['_errors'] = $errors;
}

function get_errors(): array
{
    $errors = $_SESSION['_errors'] ?? [];
    unset($_SESSION['_errors']);
    return $errors;
}

function clear_old(): void
{
    unset($_SESSION['_old'], $_SESSION['_errors']);
}

function status_label(string $status): string
{
    return match ($status) {
        'approved' => 'Aprobată',
        'rejected' => 'Respinsă',
        default => 'În așteptare',
    };
}

function status_badge_class(string $status): string
{
    return match ($status) {
        'approved' => 'badge-approved',
        'rejected' => 'badge-rejected',
        default => 'badge-pending',
    };
}

function status_badge(string $status): string
{
    return '<span class="badge status-badge ' . status_badge_class($status) . '">' . status_label($status) . '</span>';
}

function csrf_field(): string
{
    return Csrf::field();
}

function current_user(): ?array
{
    return Auth::user();
}

function require_login(): void
{
    if (!Auth::check()) {
        redirect(route_url('?route=login'));
    }
}

function require_role(string $role): void
{
    require_login();
    $user = current_user();
    if (!$user || $user['role'] !== $role) {
        http_response_code(403);
        die('Acces interzis: nu aveți permisiunea de a accesa această pagină.');
    }
}

function format_date_ro(?string $date): string
{
    if (!$date) {
        return '';
    }
    $ts = strtotime($date);
    return $ts ? date('d.m.Y', $ts) : '';
}
