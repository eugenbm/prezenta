<?php

declare(strict_types=1);

namespace App\Core;

use App\Controllers\AdminController;
use App\Controllers\ApplicantController;
use App\Controllers\AuthController;
use App\Controllers\PasswordController;
use App\Controllers\SetupController;
use App\Models\User;

final class App
{
    /**
     * Hartă rută => [Controller::class, metodă, rol necesar].
     * Rol: null = doar autentificat (orice rol), 'guest' = acces public,
     * 'admin' / 'applicant' = rol specific necesar.
     */
    private const ROUTES = [
        'login' => [AuthController::class, 'login', 'guest'],
        'logout' => [AuthController::class, 'logout', null],
        'setup' => [SetupController::class, 'index', 'guest'],
        'set-password' => [PasswordController::class, 'setPassword', 'guest'],

        'applicant/dashboard' => [ApplicantController::class, 'dashboard', 'applicant'],
        'applicant/activities' => [ApplicantController::class, 'activities', 'applicant'],
        'applicant/activity/create' => [ApplicantController::class, 'createActivity', 'applicant'],
        'applicant/activity/edit' => [ApplicantController::class, 'editActivity', 'applicant'],
        'applicant/profile' => [ApplicantController::class, 'profile', 'applicant'],

        'admin/dashboard' => [AdminController::class, 'dashboard', 'admin'],
        'admin/users' => [AdminController::class, 'users', 'admin'],
        'admin/users/create' => [AdminController::class, 'createUser', 'admin'],
        'admin/users/edit' => [AdminController::class, 'editUser', 'admin'],
        'admin/users/toggle' => [AdminController::class, 'toggleUser', 'admin'],
        'admin/users/reset-password' => [AdminController::class, 'resetPassword', 'admin'],
        'admin/activities' => [AdminController::class, 'activities', 'admin'],
        'admin/activity/create' => [AdminController::class, 'createActivity', 'admin'],
        'admin/activity/edit' => [AdminController::class, 'editActivity', 'admin'],
        'admin/activity/approve' => [AdminController::class, 'approveActivity', 'admin'],
        'admin/activity/reject' => [AdminController::class, 'rejectActivity', 'admin'],
        'admin/reports' => [AdminController::class, 'reports', 'admin'],
        'admin/reports/export-csv' => [AdminController::class, 'exportCsv', 'admin'],
        'admin/reports/print' => [AdminController::class, 'printReport', 'admin'],
        'admin/case-sheets' => [AdminController::class, 'caseSheets', 'admin'],
        'admin/case-sheet/create' => [AdminController::class, 'caseSheet', 'admin'],
        'admin/case-sheet/print' => [AdminController::class, 'printCaseSheet', 'admin'],
        'admin/case-sheet/download' => [AdminController::class, 'downloadCaseSheet', 'admin'],
        'admin/case-sheet/delete' => [AdminController::class, 'deleteCaseSheet', 'admin'],
    ];

    public function run(): void
    {
        $route = trim((string) ($_GET['route'] ?? ''), '/');

        if ($route === '') {
            $this->redirectHome();
            return;
        }

        if (!array_key_exists($route, self::ROUTES)) {
            http_response_code(404);
            require dirname(__DIR__) . '/views/errors/404.php';
            return;
        }

        [$class, $method, $role] = self::ROUTES[$route];

        if ($role === 'guest') {
            if (Auth::check()) {
                $this->redirectHome();
                return;
            }
        } elseif ($role === null) {
            require_login();
        } else {
            require_role($role);
        }

        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
            if (!Csrf::verify($_POST['csrf_token'] ?? null)) {
                flash_set('error', 'Sesiunea a expirat sau formularul este invalid. Vă rugăm reîncercați.');
                $this->redirectBack();
                return;
            }
        }

        $controller = new $class();
        $controller->$method();
    }

    private function redirectHome(): void
    {
        if (!Auth::check()) {
            redirect(route_url('?route=login'));
        }

        $user = Auth::user();
        if ($user && $user['role'] === 'admin') {
            redirect(route_url('?route=admin/dashboard'));
        }

        redirect(route_url('?route=applicant/dashboard'));
    }

    private function redirectBack(): void
    {
        $referer = $_SERVER['HTTP_REFERER'] ?? route_url('');
        redirect($referer);
    }
}
