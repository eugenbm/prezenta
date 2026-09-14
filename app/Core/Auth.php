<?php

declare(strict_types=1);

namespace App\Core;

use App\Models\User;

final class Auth
{
    public static function attempt(string $username, string $password): ?array
    {
        $user = User::findByUsernameOrEmail($username);

        if (!$user || !password_verify($password, $user['password_hash'])) {
            return null;
        }

        if ((int) $user['is_active'] !== 1) {
            return null;
        }

        self::login($user);

        return $user;
    }

    public static function login(array $user): void
    {
        session_regenerate_id(true);
        $_SESSION['user_id'] = (int) $user['id'];
        $_SESSION['user_role'] = $user['role'];
    }

    public static function logout(): void
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
        }
        session_destroy();
    }

    public static function check(): bool
    {
        return !empty($_SESSION['user_id']);
    }

    public static function user(): ?array
    {
        static $cached = null;
        static $loaded = false;

        if ($loaded) {
            return $cached;
        }
        $loaded = true;

        if (empty($_SESSION['user_id'])) {
            return null;
        }

        $cached = User::find((int) $_SESSION['user_id']);
        if (!$cached || (int) $cached['is_active'] !== 1) {
            self::logout();
            return null;
        }

        return $cached;
    }

    public static function isAdmin(): bool
    {
        $user = self::user();
        return $user !== null && $user['role'] === 'admin';
    }
}
