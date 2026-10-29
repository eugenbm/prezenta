<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\ContractPdf;
use App\Core\Mailer;
use App\Models\Activity;
use App\Models\ActivityType;
use App\Models\AuditLog;
use App\Models\Contract;
use App\Models\InventoryAssignment;
use App\Models\InventoryItem;
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
        $roleLabel = role_label($aspirant['role']);
        $dateRo = format_date_ro($data['activity_date']);
        $link = absolute_url('?route=admin/activities&status=pending');
        $subject = 'Activitate nouă de aprobat — ' . app_name();
        $html = $this->newActivityEmail($aspirantName, $roleLabel, $typeName, $dateRo, $link);

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

    private function newActivityEmail(string $aspirantName, string $roleLabel, string $typeName, string $dateRo, string $link): string
    {
        $appName = e(app_name());
        $aspirantName = e($aspirantName);
        $roleLabel = e($roleLabel);
        $typeName = e($typeName);
        $dateRo = e($dateRo);
        $linkSafe = e($link);

        return <<<HTML
            <div style="font-family:Arial,Helvetica,sans-serif;color:#2b2523;line-height:1.6;">
                <p>O activitate nouă așteaptă aprobarea în <strong>{$appName}</strong>.</p>
                <ul>
                    <li><strong>{$roleLabel}:</strong> {$aspirantName}</li>
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

    // --- Inventar (doar vizualizare) --------------------------------------

    public function inventory(): void
    {
        $user = current_user();
        $assignments = InventoryAssignment::forUser((int) $user['id']);
        $this->render('applicant/inventory', compact('assignments'));
    }

    /** Voluntarul își poate adăuga singur obiecte (atribuite automat lui); nu le poate șterge. */
    public function addInventoryItem(): void
    {
        if ($this->isPost()) {
            $this->storeInventoryItem();
            return;
        }

        $this->render('applicant/inventory_form');
    }

    private function storeInventoryItem(): void
    {
        $user = current_user();
        $name = $this->input('name');
        $description = $this->input('description');
        $quantity = (int) $this->input('quantity', '1');

        $errors = [];
        if ($name === '') {
            $errors['name'] = 'Introduceți denumirea obiectului.';
        }
        if ($quantity < 1) {
            $errors['quantity'] = 'Cantitatea trebuie să fie cel puțin 1.';
        }

        if ($errors) {
            set_old_and_errors([
                'name' => $name, 'description' => $description, 'quantity' => (string) $quantity,
            ], $errors);
            $this->redirect(route_url('?route=applicant/inventory/add'));
            return;
        }

        $itemId = InventoryItem::create([
            'name' => $name,
            'description' => $description,
            'total_quantity' => $quantity,
            'created_by' => (int) $user['id'],
        ]);
        InventoryAssignment::assign($itemId, (int) $user['id'], $quantity, (int) $user['id']);
        AuditLog::record((int) $user['id'], 'inventory_item_self_added', 'inventory_item', $itemId);

        flash_set('success', 'Obiectul a fost adăugat în inventarul tău.');
        $this->redirect(route_url('?route=applicant/inventory'));
    }

    // --- Contract de voluntariat ------------------------------------------

    public function contract(): void
    {
        if ($this->isPost()) {
            $this->storeContract();
            return;
        }

        $user = current_user();
        $type = (string) $user['role'];
        if (!ContractPdf::supports($type)) {
            flash_set('error', 'Pentru rolul contului dumneavoastră nu există un model de contract.');
            $this->redirect(route_url('?route=applicant/dashboard'));
            return;
        }

        $contract = Contract::forUser((int) $user['id']);
        $fullName = trim($user['last_name'] . ' ' . $user['first_name']);

        $this->render('applicant/contract', compact('user', 'type', 'contract', 'fullName'));
    }

    private function storeContract(): void
    {
        $user = current_user();
        $type = (string) $user['role'];

        if (!ContractPdf::supports($type)) {
            flash_set('error', 'Pentru rolul contului dumneavoastră nu există un model de contract.');
            $this->redirect(route_url('?route=applicant/dashboard'));
            return;
        }

        $jpeg = $this->decodeSignature((string) ($_POST['signature'] ?? ''));
        if ($jpeg === null) {
            flash_set('error', 'Semnătura lipsește sau este invalidă. Vă rugăm să semnați în casetă și să reîncercați.');
            $this->redirect(route_url('?route=applicant/contract'));
            return;
        }

        $fullName = trim($user['last_name'] . ' ' . $user['first_name']);
        Contract::save((int) $user['id'], $type, $fullName, base64_encode($jpeg), $this->clientIp());
        AuditLog::record((int) $user['id'], 'contract_signed', 'contract', (int) $user['id']);

        flash_set('success', 'Contractul a fost semnat. Îl puteți descărca oricând din această pagină.');
        $this->redirect(route_url('?route=applicant/contract'));
    }

    /** Afișează modelul de contract necompletat (pentru citire înainte de semnare). */
    public function previewContract(): void
    {
        $user = current_user();
        $path = ContractPdf::templatePath((string) $user['role']);
        if ($path === null || !is_file($path)) {
            http_response_code(404);
            die('Modelul de contract nu a fost găsit.');
        }

        header('Content-Type: application/pdf');
        header('Content-Disposition: inline; filename="contract-model.pdf"');
        header('Content-Length: ' . (string) filesize($path));
        header('Cache-Control: private, max-age=0, must-revalidate');
        readfile($path);
        exit;
    }

    public function downloadContract(): void
    {
        $user = current_user();
        $contract = Contract::forUser((int) $user['id']);
        if (!$contract) {
            flash_set('error', 'Nu aveți încă un contract semnat.');
            $this->redirect(route_url('?route=applicant/contract'));
            return;
        }

        $jpeg = base64_decode((string) $contract['signature_data'], true);
        if ($jpeg === false) {
            http_response_code(500);
            die('Semnătura stocată este coruptă.');
        }

        $pdf = ContractPdf::fill((string) $contract['contract_type'], (string) $contract['volunteer_name'], $jpeg);
        $this->streamPdf($pdf, 'contract-voluntariat-' . (int) $user['id'] . '.pdf');
    }

    /** Decodează semnătura primită ca data URL JPEG; returnează binarul sau null. */
    private function decodeSignature(string $dataUrl): ?string
    {
        if (!preg_match('~^data:image/jpe?g;base64,~i', $dataUrl)) {
            return null;
        }
        $base64 = substr($dataUrl, strpos($dataUrl, ',') + 1);
        // Limitează dimensiunea pentru a preveni abuzul (≈3 MB binar).
        if (strlen($base64) > 4_000_000) {
            return null;
        }
        $bin = base64_decode($base64, true);
        if ($bin === false || strlen($bin) < 4 || substr($bin, 0, 2) !== "\xFF\xD8") {
            return null;
        }
        return $bin;
    }

    private function clientIp(): ?string
    {
        $ip = $_SERVER['REMOTE_ADDR'] ?? '';
        return $ip !== '' ? substr($ip, 0, 45) : null;
    }

    private function streamPdf(string $content, string $filename): never
    {
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . strlen($content));
        header('Cache-Control: private, max-age=0, must-revalidate');
        header('Pragma: public');
        echo $content;
        exit;
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
