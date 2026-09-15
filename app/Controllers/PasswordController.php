<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\AuditLog;
use App\Models\PasswordReset;
use App\Models\User;

final class PasswordController extends Controller
{
    /** Pagina „Setează-ți parola” accesată prin linkul din email (GET) și trimiterea formularului (POST). */
    public function setPassword(): void
    {
        $token = (string) ($_POST['token'] ?? $_GET['token'] ?? '');
        $reset = PasswordReset::findValid($token);

        if (!$reset) {
            $this->renderStandalone('auth/set_password', ['validToken' => false, 'token' => '', 'userName' => '']);
            return;
        }

        $user = User::find((int) $reset['user_id']);
        if (!$user) {
            $this->renderStandalone('auth/set_password', ['validToken' => false, 'token' => '', 'userName' => '']);
            return;
        }

        if ($this->isPost()) {
            $password = (string) ($_POST['password'] ?? '');
            $confirm = (string) ($_POST['password_confirm'] ?? '');

            $errors = [];
            if (strlen($password) < 10) {
                $errors['password'] = 'Parola trebuie să aibă cel puțin 10 caractere.';
            }
            if ($password !== $confirm) {
                $errors['password_confirm'] = 'Parolele nu coincid.';
            }

            if ($errors) {
                set_old_and_errors([], $errors);
                $this->redirect(route_url('?route=set-password&token=' . urlencode($token)));
                return;
            }

            User::updatePassword((int) $user['id'], password_hash($password, PASSWORD_DEFAULT));
            PasswordReset::markUsed((int) $reset['id']);
            AuditLog::record((int) $user['id'], 'password_set_via_link', 'user', (int) $user['id']);

            flash_set('success', 'Parola a fost setată. Vă puteți autentifica.');
            $this->redirect(route_url('?route=login'));
            return;
        }

        $this->renderStandalone('auth/set_password', [
            'validToken' => true,
            'token' => $token,
            'userName' => $user['username'],
        ]);
    }
}
