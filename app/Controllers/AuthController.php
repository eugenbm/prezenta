<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\User;

final class AuthController extends Controller
{
    private const MAX_ATTEMPTS = 5;
    private const LOCKOUT_SECONDS = 300;

    public function login(): void
    {
        if ($this->isPost()) {
            $this->handleLogin();
            return;
        }

        $this->render('auth/login');
    }

    private function handleLogin(): void
    {
        $username = $this->input('username');
        $password = (string) ($_POST['password'] ?? '');

        $errors = [];
        if ($username === '') {
            $errors['username'] = 'Introduceți numele de utilizator sau adresa de email.';
        }
        if ($password === '') {
            $errors['password'] = 'Introduceți parola.';
        }

        if (!$errors && $this->isLockedOut($username)) {
            $errors['username'] = 'Prea multe încercări eșuate. Reîncercați peste câteva minute.';
        }

        if ($errors) {
            set_old_and_errors(['username' => $username], $errors);
            $this->redirect(route_url('?route=login'));
            return;
        }

        $user = Auth::attempt($username, $password);
        $this->recordAttempt($username, $user !== null);

        if (!$user) {
            flash_set('error', 'Nume de utilizator sau parolă incorectă, ori contul este dezactivat.');
            set_old_and_errors(['username' => $username], []);
            $this->redirect(route_url('?route=login'));
            return;
        }

        $this->redirect($user['role'] === 'admin' ? route_url('?route=admin/dashboard') : route_url('?route=applicant/dashboard'));
    }

    private function isLockedOut(string $username): bool
    {
        $db = \App\Core\Database::connection();
        $stmt = $db->prepare(
            "SELECT COUNT(*) FROM login_attempts
             WHERE username = :username AND success = 0 AND attempted_at > (NOW() - INTERVAL :seconds SECOND)"
        );
        $stmt->bindValue('username', $username);
        $stmt->bindValue('seconds', self::LOCKOUT_SECONDS, \PDO::PARAM_INT);
        $stmt->execute();
        return (int) $stmt->fetchColumn() >= self::MAX_ATTEMPTS;
    }

    private function recordAttempt(string $username, bool $success): void
    {
        $db = \App\Core\Database::connection();
        $stmt = $db->prepare(
            'INSERT INTO login_attempts (username, ip_address, success) VALUES (:username, :ip, :success)'
        );
        $stmt->execute([
            'username' => $username,
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'success' => $success ? 1 : 0,
        ]);
    }

    public function logout(): void
    {
        Auth::logout();
        $this->redirect(route_url('?route=login'));
    }
}
