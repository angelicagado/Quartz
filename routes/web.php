<?php

use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\CertificateTemplateController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EvaluationFormController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\EventParticipantController;
use App\Http\Controllers\ParticipantController;
use App\Http\Controllers\ParticipantProfileController;
use App\Http\Controllers\PublicEventController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SuperAdminDashboardController;
use App\Http\Controllers\SystemLogController;
use App\Http\Controllers\Teams\TeamInvitationController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'Welcome')->name('home');
Route::get('/events', [PublicEventController::class, 'index'])->name('public.events.index');
Route::get('/events/{event}', [PublicEventController::class, 'show'])->name('public.events.show');

// Super admin dashboard lives at a fixed path, not scoped to a team.
Route::middleware(['auth', 'verified', 'role:super_admin|admin'])->group(function () {
    Route::get('super-admin/dashboard', SuperAdminDashboardController::class)->name('super-admin.dashboard');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', DashboardController::class)->name('dashboard');
});

Route::middleware(['auth'])->group(function () {
    Route::get('invitations/{invitation}/accept', [TeamInvitationController::class, 'accept'])->name('invitations.accept');
    Route::delete('invitations/{invitation}', [TeamInvitationController::class, 'decline'])->name('invitations.decline');
});

require __DIR__.'/settings.php';

// QUARTZ SYSTEM ROUTES
Route::middleware(['auth', 'verified'])->group(function () {
    // Admin & Super Admin routes
    Route::middleware('role:super_admin|admin')->group(function () {
        Route::resource('users', UserController::class);
        Route::resource('roles', RoleController::class);
        Route::resource('admin/events', EventController::class)->names('events')->parameters(['admin/events' => 'event']);
        Route::resource('events.participants', EventParticipantController::class)->only(['index', 'store', 'update', 'destroy']);
        Route::get('events/{event}/participants/{participant}/qr', [EventParticipantController::class, 'qrImage'])->name('events.participants.qr');
        Route::post('events/{event}/participants/csv', [EventParticipantController::class, 'uploadCsv'])->name('events.participants.csv');
        Route::post('events/{event}/participants/{user}/issue-certificate', [EventParticipantController::class, 'issueCertificate'])->name('events.participants.issue-certificate');
        Route::resource('events.evaluations', EvaluationFormController::class)->only(['store', 'destroy']);
        Route::resource('events.certificates', CertificateTemplateController::class)->only(['store', 'destroy']);
        Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
        Route::get('super-admin/logs', [SystemLogController::class, 'index'])->name('super-admin.logs');
    });

    // Event Organizer routes
    Route::middleware('role:super_admin|admin|event_organizer')->group(function () {
        Route::get('my-events', [EventController::class, 'myEvents'])->name('events.my');
        Route::get('events/{event}/monitor', [EventController::class, 'monitor'])->name('events.monitor');
        Route::post('events/{event}/attendance/scan', [AttendanceController::class, 'scan'])->name('attendance.scan');

        // Global Attendance Scanner
        Route::get('attendance', [AttendanceController::class, 'index'])->name('attendance.index');
        Route::post('attendance/scan', [AttendanceController::class, 'globalScan'])->name('attendance.global-scan');
    });

    // Participant routes
    Route::middleware('role:super_admin|admin|event_organizer|participant')->group(function () {
        Route::get('portal/my-events', [ParticipantController::class, 'myEvents'])->name('portal.my-events');
        Route::get('portal/certificates', [ParticipantController::class, 'certificates'])->name('portal.certificates');
        Route::get('portal/profile', [ParticipantProfileController::class, 'edit'])->name('portal.profile.edit');
        Route::patch('portal/profile', [ParticipantProfileController::class, 'update'])->name('portal.profile.update');
        Route::get('portal/events', [ParticipantController::class, 'index'])->name('portal.events');
        Route::get('portal/events/{event}', [ParticipantController::class, 'show'])->name('portal.events.show');
        Route::post('portal/events/{event}/register', [ParticipantController::class, 'register'])->name('portal.register');
        Route::get('portal/events/{event}/qr', [ParticipantController::class, 'qrCode'])->name('portal.qr');
        Route::get('portal/events/{event}/qr/image', [ParticipantController::class, 'qrImage'])->name('portal.qr.image');
        Route::get('portal/events/{event}/evaluation', [EvaluationFormController::class, 'showForParticipant'])->name('portal.evaluation.show');
        Route::post('portal/events/{event}/evaluation', [EvaluationFormController::class, 'submit'])->name('portal.evaluation.submit');
        Route::get('portal/events/{event}/certificate/view', [CertificateTemplateController::class, 'view'])->name('portal.certificate.view');
        Route::get('portal/events/{event}/certificate/download', [CertificateTemplateController::class, 'download'])->name('portal.certificate.download');
        Route::get('portal/events/{event}/certificate/pdf', [CertificateTemplateController::class, 'downloadPdf'])->name('portal.certificate.downloadPdf');
    });
});
