<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Mailer;
use App\Core\Pdf;
use App\Models\Activity;
use App\Models\ActivityType;
use App\Models\AuditLog;
use App\Models\CaseSheet;
use App\Models\CaseSheetVictim;
use App\Models\PasswordReset;
use App\Models\User;

final class AdminController extends Controller
{
    public function dashboard(): void
    {
        $activeVolunteers = User::countActiveVolunteers();
        $counts = Activity::countsGlobal();
        $pending = Activity::pendingForAdmin(5);
        $byType = Activity::reportByType();
        $recent = Activity::recentForAdmin(8);

        $this->render('admin/dashboard', compact('activeVolunteers', 'counts', 'pending', 'byType', 'recent'));
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
        $role = $this->input('role', 'applicant');

        $errors = $this->validateUser($firstName, $lastName, $email, $username, $role);

        if ($errors) {
            set_old_and_errors([
                'first_name' => $firstName, 'last_name' => $lastName, 'email' => $email, 'username' => $username, 'role' => $role,
            ], $errors);
            $this->redirect(route_url('?route=admin/users/create'));
            return;
        }

        // Parolă aleatorie temporară — utilizatorul își setează propria parolă prin linkul din email.
        $id = User::create([
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => $email,
            'username' => $username,
            'password_hash' => password_hash(bin2hex(random_bytes(16)), PASSWORD_DEFAULT),
            'role' => $role,
            'is_active' => 1,
        ]);

        AuditLog::record((int) current_user()['id'], 'user_created', 'user', $id);

        $token = PasswordReset::create($id);
        $link = absolute_url('?route=set-password&token=' . urlencode($token));

        try {
            Mailer::send($email, $firstName . ' ' . $lastName, 'Setați-vă parola — ' . app_name(), $this->passwordSetupEmail($firstName, $username, $link));
            flash_set('success', 'Contul a fost creat. Un email cu linkul de setare a parolei a fost trimis la ' . $email . '.');
        } catch (\Throwable $e) {
            error_log('Trimitere email setare parolă eșuată: ' . $e->getMessage());
            @file_put_contents(
                dirname(__DIR__, 2) . '/storage/logs/mail.log',
                '[' . date('Y-m-d H:i:s') . '] ' . $e->getMessage() . "\n",
                FILE_APPEND
            );
            $reason = config('app.debug') ? ' (motiv: ' . $e->getMessage() . ')' : '';
            flash_set('error', 'Contul a fost creat, dar emailul nu a putut fi trimis' . $reason . '. Trimiteți manual acest link utilizatorului: ' . $link);
        }

        $this->redirect(route_url('?route=admin/users'));
    }

    private function passwordSetupEmail(string $firstName, string $username, string $link): string
    {
        $appName = e(app_name());
        $firstName = e($firstName);
        $username = e($username);
        $linkSafe = e($link);

        return <<<HTML
            <div style="font-family:Arial,Helvetica,sans-serif;color:#2b2523;line-height:1.6;">
                <p>Bună, {$firstName},</p>
                <p>A fost creat un cont pentru tine în aplicația <strong>{$appName}</strong>.</p>
                <p>Numele tău de utilizator este: <strong>{$username}</strong></p>
                <p>Pentru a-ți seta parola, accesează linkul de mai jos (valabil 48 de ore):</p>
                <p><a href="{$linkSafe}" style="display:inline-block;background:#841821;color:#fff;padding:10px 18px;border-radius:6px;text-decoration:none;">Setează-ți parola</a></p>
                <p style="font-size:13px;color:#777;">Dacă butonul nu funcționează, copiază acest link în browser:<br>{$linkSafe}</p>
                <p style="font-size:13px;color:#777;">Dacă nu te așteptai la acest email, ignoră-l.</p>
            </div>
            HTML;
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
            $role = $this->input('role', 'applicant');

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
        if (!in_array($role, assignable_roles(), true)) {
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

        $perPage = 10;
        $page = max(1, (int) ($_GET['page'] ?? 1));
        // Se cere un rând în plus pentru a ști dacă există pagina următoare, fără un COUNT separat.
        $rows = Activity::listForAdmin(array_filter($filters), $perPage + 1, ($page - 1) * $perPage);
        $hasMore = count($rows) > $perPage;
        $activities = array_slice($rows, 0, $perPage);

        $types = ActivityType::allActive();
        $volunteers = array_filter(User::all(), fn ($u) => in_array($u['role'], volunteer_roles(), true));

        $this->render('admin/activities', compact('activities', 'types', 'volunteers', 'filters', 'page', 'hasMore'));
    }

    public function createActivity(): void
    {
        if ($this->isPost()) {
            $this->storeActivity();
            return;
        }

        $types = ActivityType::allActive();
        $volunteers = array_filter(User::all(), fn ($u) => in_array($u['role'], volunteer_roles(), true));
        $this->render('admin/activity_create', compact('types', 'volunteers'));
    }

    private function storeActivity(): void
    {
        $userId = (int) ($_POST['user_id'] ?? 0);
        $status = $this->input('status', 'pending');
        $data = $this->collectActivityInput();

        $errors = [];
        $volunteer = User::find($userId);
        if (!$volunteer || !in_array($volunteer['role'], volunteer_roles(), true)) {
            $errors['user_id'] = 'Selectați un aspirant valid.';
        }
        $type = ActivityType::find($data['activity_type_id']);
        if (!$type || (int) $type['is_active'] !== 1) {
            $errors['activity_type_id'] = 'Selectați un tip de activitate valid.';
        }
        if ($data['activity_date'] === '' || !$this->isValidDate($data['activity_date'])) {
            $errors['activity_date'] = 'Introduceți o dată validă.';
        } elseif ($data['activity_date'] > date('Y-m-d')) {
            $errors['activity_date'] = 'Data activității nu poate fi în viitor.';
        }
        if (!in_array($status, ['pending', 'approved'], true)) {
            $errors['status'] = 'Status invalid.';
        }

        if ($errors) {
            set_old_and_errors(array_merge($_POST, ['user_id' => $userId, 'status' => $status]), $errors);
            $this->redirect(route_url('?route=admin/activity/create'));
            return;
        }

        $data['user_id'] = $userId;
        $data['status'] = $status;
        $id = Activity::create($data);

        if ($status === 'approved') {
            Activity::approve($id, (int) current_user()['id']);
        }

        AuditLog::record((int) current_user()['id'], 'activity_created_by_admin', 'activity', $id);

        flash_set('success', 'Activitatea a fost adăugată pentru aspirant.');
        $this->redirect(route_url('?route=admin/activities'));
    }

    private function isValidDate(string $date): bool
    {
        $d = \DateTime::createFromFormat('Y-m-d', $date);
        return $d && $d->format('Y-m-d') === $date;
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
        return [
            'activity_type_id' => (int) ($_POST['activity_type_id'] ?? 0),
            'activity_date' => $this->input('activity_date'),
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
        $volunteers = array_filter(User::all(), fn ($u) => in_array($u['role'], volunteer_roles(), true));

        $year = (int) date('Y');
        $annualDays = Activity::approvedDaysInYearForApplicants($year);
        $annualGoal = annual_days_goal();

        $this->render('admin/reports', compact('activities', 'byType', 'byVolunteer', 'types', 'volunteers', 'filters', 'year', 'annualDays', 'annualGoal'));
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
        fputcsv($out, ['Voluntar', 'Tip activitate', 'Data', 'Locație', 'Descriere', 'Status', 'Motiv respingere']);

        foreach ($activities as $activity) {
            fputcsv($out, [
                $activity['volunteer_last_name'] . ' ' . $activity['volunteer_first_name'],
                $activity['type_name'],
                $activity['activity_date'],
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
        $totalApproved = 0;
        $approvedDays = [];
        foreach ($activities as $activity) {
            if ($activity['status'] === 'approved') {
                $totalApproved++;
                $approvedDays[$activity['activity_date']] = true;
            }
        }
        $totalDays = count($approvedDays);

        $this->renderPartialView('admin/report_print', compact('activities', 'filters', 'totalApproved', 'totalDays'));
    }

    // --- Fișă de caz -------------------------------------------------------

    public function caseSheets(): void
    {
        $perPage = 10;
        $page = max(1, (int) ($_GET['page'] ?? 1));
        $rows = CaseSheet::paginate($perPage + 1, ($page - 1) * $perPage);
        $hasMore = count($rows) > $perPage;
        $sheets = array_slice($rows, 0, $perPage);

        $this->render('admin/case_sheets', compact('sheets', 'page', 'hasMore'));
    }

    /** Dashboard cu statistici bazate pe fișele de caz. */
    public function caseSheetStats(): void
    {
        $stats = CaseSheet::statistics();
        $this->render('admin/case_sheet_stats', compact('stats'));
    }

    /**
     * Fișa de caz (intervenție Salvamont). GET afișează formularul, POST
     * salvează fișa în baza de date și redirecționează către versiunea
     * printabilă (export PDF prin funcția „Print” a browserului).
     */
    public function caseSheet(): void
    {
        // Membri disponibili pentru selecția salvatorilor.
        $members = array_values(array_filter(User::all(), fn ($u) => (int) $u['is_active'] === 1));

        if ($this->isPost()) {
            $data = $this->collectCaseSheetInput($members);
            $data['created_by'] = (int) current_user()['id'];
            $id = CaseSheet::create($data);

            if ($data['victime_multiple'] === 'Da') {
                CaseSheetVictim::createMany($id, $this->collectCaseSheetVictims());
            }

            AuditLog::record((int) current_user()['id'], 'case_sheet_created', 'case_sheet', $id);

            $this->createSalvatorActivities($members, $data, $id);

            flash_set('success', 'Fișa de caz a fost salvată.');
            $this->redirect(route_url('?route=admin/case-sheets&download=' . $id));
            return;
        }

        $current = current_user();
        $intocmitDefault = $current ? trim($current['first_name'] . ' ' . $current['last_name']) : '';
        $nextNumber = CaseSheet::nextNumber();
        $types = ActivityType::allActive();
        $defaultType = ActivityType::findByName('Intervenție Salvamont');
        $defaultActivityTypeId = $defaultType ? (int) $defaultType['id'] : 0;

        $this->render('admin/case_sheet', compact('members', 'intocmitDefault', 'nextNumber', 'types', 'defaultActivityTypeId'));
    }

    /**
     * Creează opțional câte o activitate (aprobată) pentru fiecare salvator
     * selectat pe fișa de caz care are rol de voluntar. Se declanșează doar
     * dacă utilizatorul a bifat opțiunea și a ales un tip de activitate valid.
     */
    private function createSalvatorActivities(array $members, array $sheet, int $sheetId): void
    {
        if ($this->input('create_activities') !== '1') {
            return;
        }

        // Tipul ales; dacă lipsește/nevalid, se folosește (și se creează la nevoie) „Intervenție Salvamont”.
        $type = ActivityType::find((int) ($_POST['activity_type_id'] ?? 0));
        if (!$type || (int) $type['is_active'] !== 1) {
            $type = ActivityType::findOrCreateByName('Intervenție Salvamont');
        }
        if (!$type || (int) ($type['id'] ?? 0) === 0) {
            return;
        }

        // Data activității = data fișei (fără a depăși ziua curentă); implicit azi.
        $date = $sheet['data_fisa'] ?: date('Y-m-d');
        if ($date > date('Y-m-d')) {
            $date = date('Y-m-d');
        }

        $byId = [];
        foreach ($members as $m) {
            $byId[(int) $m['id']] = $m;
        }

        $location = trim(implode(', ', array_filter([
            $sheet['masiv_montan'], $sheet['loc_producere'], $sheet['locatie_judet'],
        ])), ', ');

        $description = 'Intervenție Salvamont';
        if ($sheet['nr_fisa'] !== '') {
            $description .= ' — fișa nr. ' . $sheet['nr_fisa'];
        }
        if ($sheet['tip_eveniment'] !== '') {
            $description .= ' (' . $sheet['tip_eveniment'] . ')';
        }

        $adminId = (int) current_user()['id'];
        $selectedIds = array_map('intval', (array) ($_POST['salvatori'] ?? []));
        $created = 0;
        foreach ($selectedIds as $sid) {
            $user = $byId[$sid] ?? null;
            if (!$user || !in_array($user['role'], volunteer_roles(), true)) {
                continue;
            }
            $activityId = Activity::create([
                'user_id' => $sid,
                'activity_type_id' => (int) $type['id'],
                'activity_date' => $date,
                'location' => $location,
                'description' => $description,
                'notes' => 'Generată automat din fișa de caz nr. ' . ($sheet['nr_fisa'] ?: (string) $sheetId) . '.',
                'status' => 'approved',
            ]);
            Activity::approve($activityId, $adminId);
            AuditLog::record($adminId, 'activity_created_from_case_sheet', 'activity', $activityId);
            $created++;
        }

        if ($created > 0) {
            flash_set('success', $created === 1
                ? 'O activitate a fost înregistrată și aprobată pentru salvatorul selectat.'
                : "{$created} activități au fost înregistrate și aprobate pentru salvatorii selectați.");
        }
    }

    /** Versiune printabilă (export PDF) a unei fișe de caz salvate. */
    public function printCaseSheet(): void
    {
        $id = (int) ($_GET['id'] ?? 0);
        $row = CaseSheet::find($id);
        if (!$row) {
            http_response_code(404);
            die('Fișa de caz nu a fost găsită.');
        }

        $sheet = $this->caseSheetForDisplay($row);
        $victims = CaseSheetVictim::forSheet($id);
        $autoprint = isset($_GET['autoprint']);
        $this->renderPartialView('admin/case_sheet_print', compact('sheet', 'victims', 'autoprint'));
    }

    /** Descarcă fișa de caz ca fișier PDF generat pe server (fără print din browser). */
    public function downloadCaseSheet(): void
    {
        $id = (int) ($_GET['id'] ?? 0);
        $row = CaseSheet::find($id);
        if (!$row) {
            http_response_code(404);
            die('Fișa de caz nu a fost găsită.');
        }

        $s = $this->caseSheetForDisplay($row);
        $pdf = new Pdf();
        $pdf->title('Fișă de caz', $s['serviciu'] ?: 'Salvamont Zărnești', [
            'Generat la: ' . date('d.m.Y H:i'),
        ]);

        $pdf->line([['Nr. fișă', $s['nr_fisa']], ['Data', $s['data_fisa']]]);
        $pdf->line([['Județ', $s['judet']], ['Serviciu', $s['serviciu']]]);
        $pdf->row('Formație', $s['formatie']);
        $pdf->group('Date despre alarmarea inițială', [
            [['Alarmare prin', $s['alarmare_prin']]],
            [['Data / Ora', $s['alarmare_datetime']], ['Tip eveniment', $s['tip_eveniment']]],
        ]);
        $pdf->line([['Responsabilitate', $s['responsabilitate']], ['Intervenție', $s['interventie']]]);
        $pdf->row('Coordonator intervenție', $s['coordonator']);
        $pdf->row('Salvatori', $s['salvatori']);
        $pdf->group('Locație', [
            [['Județ', $s['locatie_judet']], ['Masiv montan', $s['masiv_montan']]],
            [['Sezon', $s['sezon']], ['Loc producere', $s['loc_producere']]],
        ]);
        $pdf->line([['Tip de activitate generatoare', $s['tip_activitate']], ['Nr. persoane implicate', $s['numar_persoane']]]);
        $pdf->group('Date de identificare victimă', [
            [['Nume și prenume', $s['victima_nume']], ['Vârstă', $s['victima_varsta']]],
            [['Sex', $s['victima_sex']], ['Județ', $s['victima_judet']], ['Țară', $s['victima_tara']]],
            [['Stare pacient', $s['victima_stare']], ['Contact victimă', $s['victima_contact']]],
            [['Localizare afecțiune', $s['localizare_afectiune']]],
            [['Finalitate caz', $s['finalitate_caz']]],
        ]);
        $victims = CaseSheetVictim::forSheet($id);
        foreach ($victims as $i => $v) {
            $pdf->group('Victimă ' . ($i + 2), [
                [['Nume și prenume', (string) $v['nume']], ['Vârstă', (string) $v['varsta']]],
                [['Sex', (string) $v['sex']], ['Județ', (string) $v['judet']], ['Țară', (string) $v['tara']]],
                [['Stare pacient', (string) $v['stare']], ['Contact victimă', (string) $v['contact']]],
                [['Localizare afecțiune', (string) $v['localizare_afectiune']]],
                [['Finalitate caz', (string) $v['finalitate_caz']]],
            ]);
        }
        $pdf->group('Transport și finalizare', [
            [['Predare victimă', $s['predare_datetime']], ['Transport accidentat', $s['transport']]],
            [['Mod evacuare', $s['mod_evacuare']], ['Predată către', $s['predata_catre']]],
            [['Victime multiple', $s['victime_multiple']], ['Revenire bază', $s['revenire_datetime']]],
        ]);
        $pdf->row('Întocmit', $s['intocmit']);

        $content = $pdf->output();
        $slug = $s['nr_fisa'] !== '' ? preg_replace('/[^A-Za-z0-9_-]/', '', $s['nr_fisa']) : (string) $id;
        $filename = 'fisa-caz-' . ($slug !== '' ? $slug : (string) $id) . '.pdf';

        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . strlen($content));
        header('Cache-Control: private, max-age=0, must-revalidate');
        header('Pragma: public');
        echo $content;
        exit;
    }

    public function deleteCaseSheet(): void
    {
        $id = (int) ($_POST['id'] ?? 0);
        $row = CaseSheet::find($id);
        if (!$row) {
            http_response_code(404);
            die('Fișa de caz nu a fost găsită.');
        }

        CaseSheet::delete($id);
        AuditLog::record((int) current_user()['id'], 'case_sheet_deleted', 'case_sheet', $id);

        flash_set('success', 'Fișa de caz a fost ștearsă.');
        $this->redirect(route_url('?route=admin/case-sheets'));
    }

    /**
     * Adună și normalizează datele fișei de caz din formular pentru salvare.
     * Rezolvă ID-urile salvatorilor selectați în nume complete (text) și
     * păstrează datele/orele brute (ora fiind opțională).
     */
    private function collectCaseSheetInput(array $members): array
    {
        $names = [];
        foreach ($members as $m) {
            $names[(int) $m['id']] = trim($m['first_name'] . ' ' . $m['last_name']);
        }

        $selectedIds = array_map('intval', (array) ($_POST['salvatori'] ?? []));
        $salvatori = [];
        foreach ($selectedIds as $id) {
            if (isset($names[$id])) {
                $salvatori[] = $names[$id];
            }
        }

        $sezon = $this->input('sezon');
        $victimeMultiple = $this->input('victime_multiple');

        return [
            'nr_fisa' => $this->input('nr_fisa'),
            'data_fisa' => $this->normalizeDate($this->input('data_fisa')),
            'judet' => $this->input('judet', 'Brașov'),
            'serviciu' => $this->input('serviciu', 'Salvamont Zărnești'),
            'formatie' => $this->input('formatie'),
            'alarmare_prin' => $this->input('alarmare_prin'),
            'alarmare_data' => $this->normalizeDate($this->input('alarmare_data')),
            'alarmare_ora' => $this->normalizeTime($this->input('alarmare_ora')),
            'tip_eveniment' => $this->input('tip_eveniment'),
            'responsabilitate' => $this->input('responsabilitate'),
            'interventie' => $this->input('interventie'),
            'coordonator' => $this->input('coordonator'),
            'salvatori' => implode(', ', $salvatori),
            'locatie_judet' => $this->input('locatie_judet', 'Brașov'),
            'masiv_montan' => $this->input('masiv_montan'),
            'sezon' => in_array($sezon, ['Vara', 'Iarna'], true) ? $sezon : '',
            'loc_producere' => $this->input('loc_producere'),
            'tip_activitate' => $this->input('tip_activitate'),
            'numar_persoane' => $this->input('numar_persoane'),
            'victima_nume' => $this->input('victima_nume'),
            'victima_varsta' => $this->input('victima_varsta'),
            'victima_sex' => $this->input('victima_sex'),
            'victima_judet' => $this->input('victima_judet'),
            'victima_tara' => $this->input('victima_tara', 'România'),
            'victima_stare' => $this->input('victima_stare'),
            'victima_contact' => $this->input('victima_contact'),
            'localizare_afectiune' => $this->input('localizare_afectiune'),
            'finalitate_caz' => $this->input('finalitate_caz'),
            'predare_data' => $this->normalizeDate($this->input('predare_data')),
            'predare_ora' => $this->normalizeTime($this->input('predare_ora')),
            'transport' => $this->input('transport'),
            'mod_evacuare' => in_array($this->input('mod_evacuare'), ['Aero', 'Terestru'], true) ? $this->input('mod_evacuare') : '',
            'predata_catre' => $this->input('predata_catre'),
            'victime_multiple' => in_array($victimeMultiple, ['Da', 'Nu'], true) ? $victimeMultiple : '',
            'revenire_data' => $this->normalizeDate($this->input('revenire_data')),
            'revenire_ora' => $this->normalizeTime($this->input('revenire_ora')),
            'intocmit' => $this->input('intocmit'),
        ];
    }

    /**
     * Adună victimele suplimentare trimise din formular (câmpuri sub numele
     * `victime[i][...]`). Ignoră intrările complet goale.
     *
     * @return array<int, array<string, string>>
     */
    private function collectCaseSheetVictims(): array
    {
        $raw = $_POST['victime'] ?? [];
        if (!is_array($raw)) {
            return [];
        }

        $fields = ['nume', 'varsta', 'sex', 'judet', 'tara', 'stare', 'contact', 'localizare_afectiune', 'finalitate_caz'];
        $victims = [];
        foreach ($raw as $entry) {
            if (!is_array($entry)) {
                continue;
            }
            $victim = [];
            foreach ($fields as $field) {
                $victim[$field] = trim((string) ($entry[$field] ?? ''));
            }
            if (implode('', $victim) === '') {
                continue;
            }
            $victim['sex'] = in_array($victim['sex'], ['M', 'F'], true) ? $victim['sex'] : '';
            $victims[] = $victim;
        }

        return $victims;
    }

    private function normalizeDate(string $date): ?string
    {
        return ($date !== '' && $this->isValidDate($date)) ? $date : null;
    }

    private function normalizeTime(string $time): ?string
    {
        return preg_match('/^([01]\d|2[0-3]):[0-5]\d$/', $time) ? $time : null;
    }

    /** Transformă un rând din baza de date în structura afișată de view-ul printabil. */
    private function caseSheetForDisplay(array $r): array
    {
        return [
            'nr_fisa' => (string) $r['nr_fisa'],
            'data_fisa' => $this->combineDateTime((string) $r['data_fisa'], ''),
            'judet' => (string) $r['judet'],
            'serviciu' => (string) $r['serviciu'],
            'formatie' => (string) $r['formatie'],
            'alarmare_prin' => (string) $r['alarmare_prin'],
            'alarmare_datetime' => $this->combineDateTime((string) $r['alarmare_data'], (string) $r['alarmare_ora']),
            'tip_eveniment' => (string) $r['tip_eveniment'],
            'responsabilitate' => (string) $r['responsabilitate'],
            'interventie' => (string) $r['interventie'],
            'coordonator' => (string) $r['coordonator'],
            'salvatori' => (string) $r['salvatori'],
            'locatie_judet' => (string) $r['locatie_judet'],
            'masiv_montan' => (string) $r['masiv_montan'],
            'sezon' => (string) $r['sezon'],
            'loc_producere' => (string) $r['loc_producere'],
            'tip_activitate' => (string) $r['tip_activitate'],
            'numar_persoane' => (string) $r['numar_persoane'],
            'victima_nume' => (string) $r['victima_nume'],
            'victima_varsta' => (string) $r['victima_varsta'],
            'victima_sex' => (string) $r['victima_sex'],
            'victima_judet' => (string) $r['victima_judet'],
            'victima_tara' => (string) $r['victima_tara'],
            'victima_stare' => (string) $r['victima_stare'],
            'victima_contact' => (string) ($r['victima_contact'] ?? ''),
            'localizare_afectiune' => (string) $r['localizare_afectiune'],
            'finalitate_caz' => (string) $r['finalitate_caz'],
            'predare_datetime' => $this->combineDateTime((string) $r['predare_data'], (string) $r['predare_ora']),
            'transport' => (string) $r['transport'],
            'mod_evacuare' => (string) ($r['mod_evacuare'] ?? ''),
            'predata_catre' => (string) ($r['predata_catre'] ?? ''),
            'victime_multiple' => (string) $r['victime_multiple'],
            'revenire_datetime' => $this->combineDateTime((string) $r['revenire_data'], (string) $r['revenire_ora']),
            'intocmit' => (string) $r['intocmit'],
        ];
    }

    /**
     * Combină o dată (Y-m-d) cu o oră opțională (H:i[:s]) într-un text lizibil
     * în limba română. Dacă ora lipsește, se afișează doar data.
     */
    private function combineDateTime(string $date, string $time): string
    {
        if ($date === '') {
            return '';
        }
        $formatted = format_date_ro($date);
        if ($formatted === '') {
            return '';
        }
        if ($time !== '' && preg_match('/^(\d{1,2}:\d{2})/', $time, $m)) {
            return $formatted . ', ' . $m[1];
        }
        return $formatted;
    }
}
