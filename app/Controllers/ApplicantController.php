<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Mailer;
use App\Models\Activity;
use App\Models\ActivityType;
use App\Models\User;

final class ApplicantController extends Controller
{
    public function dashboard(): void
    {
        $user = current_user();
        $counts = Activity::countsForUser((int) $user['id']);
        $recent = Activity::recentForUser((int) $user['id'], 5);
        $year = (int) date('Y');
        $annualDays = Activity::approvedDaysInYear((int) $user['id'], $year);
        $annualGoal = annual_days_goal();

        $this->render('applicant/dashboard', compact('counts', 'recent', 'year', 'annualDays', 'annualGoal'));
    }

    public function activities(): void
    {
        $user = current_user();
        $filters = [
            'status' => $_GET['status'] ?? '',
            'activity_type_id' => $_GET['activity_type_id'] ?? '',
            'date_from' => $_GET['date_from'] ?? '',
            'date_to' => $_GET['date_to'] ?? '',
        ];

        $activities = Activity::listForUser((int) $user['id'], array_filter($filters));
        $types = ActivityType::allActive();

        $this->render('applicant/activities', compact('activities', 'types', 'filters'));
    }

    public function createActivity(): void
    {
        if ($this->isPost()) {
            $this->storeActivity();
            return;
        }

        $types = ActivityType::allActive();
        $this->render('applicant/activity_form', ['types' => $types, 'activity' => null]);
    }

    private function storeActivity(): void
    {
        $user = current_user();
        [$data, $errors] = $this->validateActivity();

        if ($errors) {
            set_old_and_errors($_POST, $errors);
            $this->redirect(route_url('?route=applicant/activity/create'));
            return;
        }

        $data['user_id'] = (int) $user['id'];
        Activity::create($data);
        $notified = $this->notifyAdminsNewActivity($user, $data);

        $message = 'Activitatea a fost înregistrată și așteaptă aprobarea unui administrator.';
        if ($notified > 0) {
            $message .= ' Administratorii au fost notificați pe email.';
        }
        flash_set('success', $message);
        $this->redirect(route_url('?route=applicant/activities'));
    }

    /**
     * Anunță pe email administratorii activi despre o activitate nouă de aprobat.
     * Returnează numărul de emailuri trimise cu succes.
     */
    private function notifyAdminsNewActivity(array $aspirant, array $data): int
    {
        $admins = User::activeAdmins();
        if (!$admins) {
            return 0;
        }

        $type = ActivityType::find((int) $data['activity_type_id']);
        $typeName = $type['name'] ?? 'Activitate';
        $aspirantName = $aspirant['first_name'] . ' ' . $aspirant['last_name'];
        $dateRo = format_date_ro($data['activity_date']);
        $link = absolute_url('?route=admin/activities&status=pending');
        $subject = 'Activitate nouă de aprobat — ' . app_name();
        $html = $this->newActivityEmail($aspirantName, $typeName, $dateRo, $link);

        $sent = 0;
        foreach ($admins as $admin) {
            try {
                Mailer::send($admin['email'], $admin['first_name'] . ' ' . $admin['last_name'], $subject, $html);
                $sent++;
                $this->logMail('admin-notify OK -> ' . $admin['email']);
            } catch (\Throwable $e) {
                error_log('Notificare admin activitate nouă eșuată: ' . $e->getMessage());
                $this->logMail('admin-notify EȘEC -> ' . $admin['email'] . ' : ' . $e->getMessage());
            }
        }

        return $sent;
    }

    private function logMail(string $line): void
    {
        @file_put_contents(
            dirname(__DIR__, 2) . '/storage/logs/mail.log',
            '[' . date('Y-m-d H:i:s') . '] ' . $line . "\n",
            FILE_APPEND
        );
    }

    private function newActivityEmail(string $aspirantName, string $typeName, string $dateRo, string $link): string
    {
        $appName = e(app_name());
        $aspirantName = e($aspirantName);
        $typeName = e($typeName);
        $dateRo = e($dateRo);
        $linkSafe = e($link);

        return <<<HTML
            <div style="font-family:Arial,Helvetica,sans-serif;color:#2b2523;line-height:1.6;">
                <p>O activitate nouă așteaptă aprobarea în <strong>{$appName}</strong>.</p>
                <ul>
                    <li><strong>Aspirant:</strong> {$aspirantName}</li>
                    <li><strong>Tip activitate:</strong> {$typeName}</li>
                    <li><strong>Data:</strong> {$dateRo}</li>
                </ul>
                <p><a href="{$linkSafe}" style="display:inline-block;background:#841821;color:#fff;padding:10px 18px;border-radius:6px;text-decoration:none;">Vezi activitățile în așteptare</a></p>
                <p style="font-size:13px;color:#777;">Dacă butonul nu funcționează, copiază acest link în browser:<br>{$linkSafe}</p>
            </div>
            HTML;
    }

    public function editActivity(): void
    {
        $user = current_user();
        $id = (int) ($_GET['id'] ?? $_POST['id'] ?? 0);
        $activity = Activity::find($id);

        if (!$activity || (int) $activity['user_id'] !== (int) $user['id']) {
            http_response_code(404);
            die('Activitatea nu a fost găsită.');
        }

        if ($activity['status'] !== 'pending') {
            flash_set('error', 'Doar activitățile aflate în așteptare pot fi editate.');
            $this->redirect(route_url('?route=applicant/activities'));
            return;
        }

        if ($this->isPost()) {
            [$data, $errors] = $this->validateActivity();
            if ($errors) {
                set_old_and_errors($_POST, $errors);
                $this->redirect(route_url('?route=applicant/activity/edit&id=' . $id));
                return;
            }

            $updated = Activity::updateByOwner($id, (int) $user['id'], $data);
            if (!$updated) {
                flash_set('error', 'Activitatea nu a putut fi actualizată (poate a fost deja aprobată/respinsă între timp).');
            } else {
                flash_set('success', 'Activitatea a fost actualizată.');
            }
            $this->redirect(route_url('?route=applicant/activities'));
            return;
        }

        $types = ActivityType::allActive();
        $this->render('applicant/activity_form', ['types' => $types, 'activity' => $activity]);
    }

    public function profile(): void
    {
        $user = current_user();
        $counts = Activity::countsForUser((int) $user['id']);
        $year = (int) date('Y');
        $annualDays = Activity::approvedDaysInYear((int) $user['id'], $year);
        $annualGoal = annual_days_goal();

        $this->render('applicant/profile', compact('user', 'counts', 'year', 'annualDays', 'annualGoal'));
    }

    /** @return array{0: array, 1: array} [date, errors] */
    private function validateActivity(): array
    {
        $activityTypeId = (int) ($_POST['activity_type_id'] ?? 0);
        $activityDate = $this->input('activity_date');
        $location = $this->input('location');
        $description = $this->input('description');
        $notes = $this->input('notes');

        $errors = [];

        $type = ActivityType::find($activityTypeId);
        if (!$type || (int) $type['is_active'] !== 1) {
            $errors['activity_type_id'] = 'Selectați un tip de activitate valid.';
        }

        if ($activityDate === '' || !$this->isValidDate($activityDate)) {
            $errors['activity_date'] = 'Introduceți o dată validă.';
        } elseif ($activityDate > date('Y-m-d')) {
            $errors['activity_date'] = 'Data activității nu poate fi în viitor.';
        }

        $data = [
            'activity_type_id' => $activityTypeId,
            'activity_date' => $activityDate,
            'location' => $location,
            'description' => $description,
            'notes' => $notes,
        ];

        return [$data, $errors];
    }

    private function isValidDate(string $date): bool
    {
        $d = \DateTime::createFromFormat('Y-m-d', $date);
        return $d && $d->format('Y-m-d') === $date;
    }
}
