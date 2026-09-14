<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Activity;
use App\Models\ActivityType;
use App\Models\AuditLog;
use App\Models\User;

final class AdminController extends Controller
{
    public function dashboard(): void
    {
        $activeVolunteers = User::countActiveVolunteers();
        $counts = Activity::countsGlobal();
        $approvedHours = Activity::sumApprovedHoursGlobal();
        $pending = Activity::pendingForAdmin(10);
        $byType = Activity::reportByType();

        $this->render('admin/dashboard', compact('activeVolunteers', 'counts', 'approvedHours', 'pending', 'byType'));
    }

    // --- Voluntari -----------------------------------------------------

    public function users(): void
    {
        $users = User::all();
        $this->render('admin/users', compact('users'));
    }

    public function createUser(): void
    {
        if ($this->isPost()) {
            $this->storeUser();
            return;
        }

        $this->render('admin/create_user', ['editUser' => null]);
    }

    private function storeUser(): void
    {
        $firstName = $this->input('first_name');
        $lastName = $this->input('last_name');
        $email = $this->input('email');
        $username = $this->input('username');
        $password = (string) ($_POST['password'] ?? '');
        $role = $this->input('role', 'aspirant');

        $errors = $this->validateUser($firstName, $lastName, $email, $username, $role);
        if (strlen($password) < 10) {
            $errors['password'] = 'Parola trebuie să aibă cel puțin 10 caractere.';
        }

        if ($errors) {
            set_old_and_errors([
                'first_name' => $firstName, 'last_name' => $lastName, 'email' => $email, 'username' => $username, 'role' => $role,
            ], $errors);
            $this->redirect(route_url('?route=admin/users/create'));
            return;
        }

        $id = User::create([
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => $email,
            'username' => $username,
            'password_hash' => password_hash($password, PASSWORD_DEFAULT),
            'role' => $role,
            'is_active' => 1,
        ]);

        AuditLog::record((int) current_user()['id'], 'user_created', 'user', $id);

        flash_set('success', 'Contul a fost creat cu succes.');
        $this->redirect(route_url('?route=admin/users'));
    }

    public function editUser(): void
    {
        $id = (int) ($_GET['id'] ?? $_POST['id'] ?? 0);
        $editUser = User::find($id);
        if (!$editUser) {
            http_response_code(404);
            die('Utilizatorul nu a fost găsit.');
        }

        if ($this->isPost()) {
            $firstName = $this->input('first_name');
            $lastName = $this->input('last_name');
            $email = $this->input('email');
            $username = $this->input('username');
            $role = $this->input('role', 'aspirant');

            $errors = $this->validateUser($firstName, $lastName, $email, $username, $role, $id);
            if ($errors) {
                set_old_and_errors([
                    'first_name' => $firstName, 'last_name' => $lastName, 'email' => $email, 'username' => $username, 'role' => $role,
                ], $errors);
                $this->redirect(route_url('?route=admin/users/edit&id=' . $id));
                return;
            }

            User::update($id, [
                'first_name' => $firstName, 'last_name' => $lastName, 'email' => $email, 'username' => $username, 'role' => $role,
            ]);
            AuditLog::record((int) current_user()['id'], 'user_updated', 'user', $id);

            flash_set('success', 'Datele utilizatorului au fost actualizate.');
            $this->redirect(route_url('?route=admin/users'));
            return;
        }

        $this->render('admin/create_user', compact('editUser'));
    }

    public function toggleUser(): void
    {
        $id = (int) ($_POST['id'] ?? 0);
        $user = User::find($id);
        if (!$user) {
            http_response_code(404);
            die('Utilizatorul nu a fost găsit.');
        }

        if ((int) $user['id'] === (int) current_user()['id']) {
            flash_set('error', 'Nu vă puteți dezactiva propriul cont.');
            $this->redirect(route_url('?route=admin/users'));
            return;
        }

        $newState = !((int) $user['is_active'] === 1);
        User::setActive($id, $newState);
        AuditLog::record((int) current_user()['id'], $newState ? 'user_reactivated' : 'user_deactivated', 'user', $id);

        flash_set('success', $newState ? 'Contul a fost reactivat.' : 'Contul a fost dezactivat.');
        $this->redirect(route_url('?route=admin/users'));
    }

    public function resetPassword(): void
    {
        $id = (int) ($_POST['id'] ?? 0);
        $newPassword = (string) ($_POST['new_password'] ?? '');
        $user = User::find($id);

        if (!$user) {
            http_response_code(404);
            die('Utilizatorul nu a fost găsit.');
        }

        if (strlen($newPassword) < 10) {
            flash_set('error', 'Noua parolă trebuie să aibă cel puțin 10 caractere.');
            $this->redirect(route_url('?route=admin/users'));
            return;
        }

        User::updatePassword($id, password_hash($newPassword, PASSWORD_DEFAULT));
        AuditLog::record((int) current_user()['id'], 'password_reset', 'user', $id);

        flash_set('success', 'Parola a fost resetată cu succes.');
        $this->redirect(route_url('?route=admin/users'));
    }

    private function validateUser(string $firstName, string $lastName, string $email, string $username, string $role, ?int $excludeId = null): array
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
        if (!in_array($role, ['admin', 'aspirant'], true)) {
            $errors['role'] = 'Rol invalid.';
        }
        if (!$errors && User::existsByUsernameOrEmail($username, $email, $excludeId)) {
            $errors['username'] = 'Numele de utilizator sau adresa de email sunt deja folosite.';
        }

        return $errors;
    }

    // --- Activități ------------------------------------------------------

    public function activities(): void
    {
        $filters = [
            'user_id' => $_GET['user_id'] ?? '',
            'activity_type_id' => $_GET['activity_type_id'] ?? '',
            'status' => $_GET['status'] ?? '',
            'date_from' => $_GET['date_from'] ?? '',
            'date_to' => $_GET['date_to'] ?? '',
        ];

        $activities = Activity::listForAdmin(array_filter($filters));
        $types = ActivityType::allActive();
        $volunteers = array_filter(User::all(), fn ($u) => $u['role'] === 'aspirant');

        $this->render('admin/activities', compact('activities', 'types', 'volunteers', 'filters'));
    }

    public function editActivity(): void
    {
        $id = (int) ($_GET['id'] ?? $_POST['id'] ?? 0);
        $activity = Activity::find($id);
        if (!$activity) {
            http_response_code(404);
            die('Activitatea nu a fost găsită.');
        }

        if ($this->isPost()) {
            $data = $this->collectActivityInput();
            Activity::updateByAdmin($id, $data);
            AuditLog::record((int) current_user()['id'], 'activity_edited_by_admin', 'activity', $id);

            flash_set('success', 'Activitatea a fost actualizată.');
            $this->redirect(route_url('?route=admin/activities'));
            return;
        }

        $types = ActivityType::allActive();
        $this->render('admin/activity_edit', compact('activity', 'types'));
    }

    private function collectActivityInput(): array
    {
        $startTime = $this->input('start_time');
        $endTime = $this->input('end_time');
        $durationRaw = $this->input('duration_hours');

        $duration = null;
        if ($startTime !== '' && $endTime !== '' && $startTime < $endTime) {
            $duration = round((strtotime($endTime) - strtotime($startTime)) / 3600, 2);
        } elseif ($durationRaw !== '' && is_numeric($durationRaw)) {
            $duration = (float) $durationRaw;
        }

        return [
            'activity_type_id' => (int) ($_POST['activity_type_id'] ?? 0),
            'activity_date' => $this->input('activity_date'),
            'start_time' => $startTime,
            'end_time' => $endTime,
            'duration_hours' => $duration,
            'location' => $this->input('location'),
            'description' => $this->input('description'),
            'notes' => $this->input('notes'),
        ];
    }

    public function approveActivity(): void
    {
        $id = (int) ($_POST['id'] ?? 0);
        $activity = Activity::find($id);
        if (!$activity) {
            http_response_code(404);
            die('Activitatea nu a fost găsită.');
        }

        if ((int) $activity['user_id'] === (int) current_user()['id']) {
            flash_set('error', 'Nu vă puteți aproba propria activitate.');
            $this->redirect(route_url('?route=admin/activities'));
            return;
        }

        Activity::approve($id, (int) current_user()['id']);
        flash_set('success', 'Activitatea a fost aprobată.');
        $this->redirect(route_url('?route=admin/activities'));
    }

    public function rejectActivity(): void
    {
        $id = (int) ($_POST['id'] ?? 0);
        $reason = trim((string) ($_POST['rejection_reason'] ?? ''));
        $activity = Activity::find($id);

        if (!$activity) {
            http_response_code(404);
            die('Activitatea nu a fost găsită.');
        }

        if ((int) $activity['user_id'] === (int) current_user()['id']) {
            flash_set('error', 'Nu vă puteți respinge propria activitate.');
            $this->redirect(route_url('?route=admin/activities'));
            return;
        }

        if ($reason === '') {
            flash_set('error', 'Introduceți un motiv pentru respingere.');
            $this->redirect(route_url('?route=admin/activities'));
            return;
        }

        Activity::reject($id, (int) current_user()['id'], $reason);
        flash_set('success', 'Activitatea a fost respinsă.');
        $this->redirect(route_url('?route=admin/activities'));
    }

    // --- Rapoarte ----------------------------------------------------------

    public function reports(): void
    {
        $filters = [
            'user_id' => $_GET['user_id'] ?? '',
            'activity_type_id' => $_GET['activity_type_id'] ?? '',
            'status' => $_GET['status'] ?? '',
            'date_from' => $_GET['date_from'] ?? '',
            'date_to' => $_GET['date_to'] ?? '',
        ];

        $activities = Activity::listForAdmin(array_filter($filters));
        $byType = Activity::reportByType(array_filter($filters));
        $byVolunteer = Activity::reportByVolunteer(array_filter($filters));
        $types = ActivityType::allActive();
        $volunteers = array_filter(User::all(), fn ($u) => $u['role'] === 'aspirant');

        $this->render('admin/reports', compact('activities', 'byType', 'byVolunteer', 'types', 'volunteers', 'filters'));
    }

    public function exportCsv(): void
    {
        $filters = [
            'user_id' => $_GET['user_id'] ?? '',
            'activity_type_id' => $_GET['activity_type_id'] ?? '',
            'status' => $_GET['status'] ?? '',
            'date_from' => $_GET['date_from'] ?? '',
            'date_to' => $_GET['date_to'] ?? '',
        ];

        $activities = Activity::listForAdmin(array_filter($filters));

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="raport-activitati-' . date('Y-m-d') . '.csv"');

        $out = fopen('php://output', 'w');
        // BOM UTF-8 pentru compatibilitate cu Excel.
        fwrite($out, "\xEF\xBB\xBF");
        fputcsv($out, ['Voluntar', 'Tip activitate', 'Data', 'Ora start', 'Ora final', 'Ore', 'Locație', 'Descriere', 'Status', 'Motiv respingere']);

        foreach ($activities as $activity) {
            fputcsv($out, [
                $activity['volunteer_last_name'] . ' ' . $activity['volunteer_first_name'],
                $activity['type_name'],
                $activity['activity_date'],
                $activity['start_time'],
                $activity['end_time'],
                $activity['duration_hours'],
                $activity['location'],
                $activity['description'],
                status_label($activity['status']),
                $activity['rejection_reason'],
            ]);
        }

        fclose($out);
        exit;
    }

    /**
     * Versiune printabilă a raportului (fără layout/navigare), destinată
     * exportului în PDF prin funcția „Print” / „Salvează ca PDF” a browserului
     * — nu necesită nicio bibliotecă PHP suplimentară pe hosting.
     */
    public function printReport(): void
    {
        $filters = [
            'user_id' => $_GET['user_id'] ?? '',
            'activity_type_id' => $_GET['activity_type_id'] ?? '',
            'status' => $_GET['status'] ?? '',
            'date_from' => $_GET['date_from'] ?? '',
            'date_to' => $_GET['date_to'] ?? '',
        ];

        $activities = Activity::listForAdmin(array_filter($filters));
        $totalHours = 0.0;
        $totalApproved = 0;
        foreach ($activities as $activity) {
            if ($activity['status'] === 'approved') {
                $totalApproved++;
                $totalHours += (float) ($activity['duration_hours'] ?? 0);
            }
        }

        $this->renderPartialView('admin/report_print', compact('activities', 'filters', 'totalHours', 'totalApproved'));
    }
}
