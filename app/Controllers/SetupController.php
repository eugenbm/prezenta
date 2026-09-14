<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\AuditLog;
use App\Models\User;

/**
 * Pagină de configurare inițială — permite crearea primului cont de administrator.
 * Se dezactivează automat imediat ce există cel puțin un administrator în baza de date.
 */
final class SetupController extends Controller
{
    public function index(): void
    {
        if (User::countAdmins() > 0) {
            http_response_code(403);
            die('Configurarea inițială a fost deja finalizată. Contactați un administrator existent pentru a crea conturi noi.');
        }

        if ($this->isPost()) {
            $this->handleCreate();
            return;
        }

        $this->renderStandalone('auth/setup');
    }

    private function handleCreate(): void
    {
        if (User::countAdmins() > 0) {
            http_response_code(403);
            die('Configurarea inițială a fost deja finalizată.');
        }

        $firstName = $this->input('first_name');
        $lastName = $this->input('last_name');
        $email = $this->input('email');
        $username = $this->input('username');
        $password = (string) ($_POST['password'] ?? '');
        $passwordConfirm = (string) ($_POST['password_confirm'] ?? '');

        $errors = $this->validate($firstName, $lastName, $email, $username, $password, $passwordConfirm);

        if ($errors) {
            set_old_and_errors([
                'first_name' => $firstName, 'last_name' => $lastName, 'email' => $email, 'username' => $username,
            ], $errors);
            $this->redirect(route_url('?route=setup'));
            return;
        }

        $id = User::create([
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => $email,
            'username' => $username,
            'password_hash' => password_hash($password, PASSWORD_DEFAULT),
            'role' => 'admin',
            'is_active' => 1,
        ]);

        AuditLog::record($id, 'admin_setup_created', 'user', $id, 'Primul cont de administrator, creat din pagina de configurare.');

        flash_set('success', 'Contul de administrator a fost creat. Vă puteți autentifica acum.');
        $this->redirect(route_url('?route=login'));
    }

    private function validate(string $firstName, string $lastName, string $email, string $username, string $password, string $passwordConfirm): array
    {
        $errors = [];
        if ($firstName === '') $errors['first_name'] = 'Introduceți prenumele.';
        if ($lastName === '') $errors['last_name'] = 'Introduceți numele.';
        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Introduceți o adresă de email validă.';
        }
        if ($username === '' || !preg_match('/^[a-zA-Z0-9._-]{3,60}$/', $username)) {
            $errors['username'] = 'Numele de utilizator trebuie să aibă 3-60 caractere (litere, cifre, . _ -).';
        }
        if (strlen($password) < 10) {
            $errors['password'] = 'Parola trebuie să aibă cel puțin 10 caractere.';
        } elseif ($password !== $passwordConfirm) {
            $errors['password_confirm'] = 'Parolele nu coincid.';
        }
        if (!$errors && User::existsByUsernameOrEmail($username, $email)) {
            $errors['username'] = 'Numele de utilizator sau adresa de email sunt deja folosite.';
        }

        return $errors;
    }
}
