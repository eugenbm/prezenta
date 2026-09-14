<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Activity;
use App\Models\ActivityType;
use App\Models\User;

final class ApplicantController extends Controller
{
    public function dashboard(): void
    {
        $user = current_user();
        $counts = Activity::countsForUser((int) $user['id']);
        $approvedHours = Activity::sumApprovedHoursForUser((int) $user['id']);
        $recent = Activity::recentForUser((int) $user['id'], 5);

        $this->render('applicant/dashboard', compact('counts', 'approvedHours', 'recent'));
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

        flash_set('success', 'Activitatea a fost înregistrată și așteaptă aprobarea unui administrator.');
        $this->redirect(route_url('?route=applicant/activities'));
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
        $approvedHours = Activity::sumApprovedHoursForUser((int) $user['id']);

        $this->render('applicant/profile', compact('user', 'counts', 'approvedHours'));
    }

    /** @return array{0: array, 1: array} [date, errors] */
    private function validateActivity(): array
    {
        $activityTypeId = (int) ($_POST['activity_type_id'] ?? 0);
        $activityDate = $this->input('activity_date');
        $startTime = $this->input('start_time');
        $endTime = $this->input('end_time');
        $location = $this->input('location');
        $description = $this->input('description');
        $notes = $this->input('notes');
        $durationRaw = $this->input('duration_hours');

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

        if ($description === '' || mb_strlen($description) < 5) {
            $errors['description'] = 'Descrierea trebuie să aibă cel puțin 5 caractere.';
        }

        if ($startTime !== '' && $endTime !== '' && $startTime >= $endTime) {
            $errors['end_time'] = 'Ora de final trebuie să fie după ora de început.';
        }

        $duration = null;
        if ($startTime !== '' && $endTime !== '' && $startTime < $endTime) {
            $duration = round((strtotime($endTime) - strtotime($startTime)) / 3600, 2);
        } elseif ($durationRaw !== '') {
            if (!is_numeric($durationRaw) || (float) $durationRaw < 0 || (float) $durationRaw > 24) {
                $errors['duration_hours'] = 'Numărul de ore trebuie să fie între 0 și 24.';
            } else {
                $duration = (float) $durationRaw;
            }
        }

        $data = [
            'activity_type_id' => $activityTypeId,
            'activity_date' => $activityDate,
            'start_time' => $startTime,
            'end_time' => $endTime,
            'duration_hours' => $duration,
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
