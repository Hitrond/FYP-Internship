<?php

use App\Http\Controllers\AdminClearanceController;
use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\AdminEvaluationFormController;
use App\Http\Controllers\AdminInternshipCycleController;
use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\CoverLetterController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FinalClearanceController;
use App\Http\Controllers\LogbookController;
use App\Http\Controllers\MentorClearanceController;
use App\Http\Controllers\MentorDashboardController;
use App\Http\Controllers\MentorFinalClearanceController;
use App\Http\Controllers\MentorProfileController;
use App\Http\Controllers\MentorResultController;
use App\Http\Controllers\NotificationCenterController;
use App\Http\Controllers\PerformanceEvaluationController;
use App\Http\Controllers\PlacementClearanceDocumentController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\StudentClearanceController;
use App\Http\Controllers\StudentCompanyTrackerController;
use App\Http\Controllers\StudentDocumentController;
use App\Http\Controllers\StudentProfileController;
use App\Http\Controllers\StudentResumeController;
use App\Http\Controllers\SupervisorDashboardController;
use App\Http\Controllers\SupervisorFinalClearanceController;
use App\Http\Controllers\SupervisorProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/notifications', [NotificationCenterController::class, 'index'])->name('notifications.index');
    Route::patch('/notifications/read-all', [NotificationCenterController::class, 'readAll'])->name('notifications.read-all');
    Route::patch('/notifications/{notification}/read', [NotificationCenterController::class, 'read'])->name('notifications.read');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::middleware('student')->group(function () {
        // Student Profile & Competency Management
        Route::get('/student/profile', [StudentProfileController::class, 'edit'])->name('student.profile.edit');
        Route::put('/student/profile', [StudentProfileController::class, 'update'])->name('student.profile.update');
        Route::post('/student/education', [StudentProfileController::class, 'storeEducation'])->name('student.education.store');
        Route::delete('/student/education/{education}', [StudentProfileController::class, 'destroyEducation'])->name('student.education.destroy');
        Route::post('/student/skill', [StudentProfileController::class, 'storeSkill'])->name('student.skill.store');
        Route::delete('/student/skill/{skill}', [StudentProfileController::class, 'destroySkill'])->name('student.skill.destroy');
        Route::get('/student/clearance', [StudentClearanceController::class, 'create'])->name('student.clearance.create');
        Route::post('/student/clearance', [StudentClearanceController::class, 'store'])->name('student.clearance.store');
        Route::patch('/student/clearance/{placementClearance}/dates', [StudentClearanceController::class, 'updateDates'])
            ->name('student.clearance.dates.update');
        Route::post('/student/clearance/final', [FinalClearanceController::class, 'store'])->name('student.final-clearance.store');
        Route::get('/student/evaluations', [PerformanceEvaluationController::class, 'studentIndex'])
            ->name('student.evaluations.index');

        // Student Documents (Resume & Cover Letter)
        Route::get('/student/resume', [StudentResumeController::class, 'builder'])->name('student.resume.builder');
        Route::get('/student/resume/download', [StudentResumeController::class, 'download'])->name('student.resume.download');
        Route::get('/student/resume/download-doc', [StudentResumeController::class, 'downloadDoc'])->name('student.resume.download-doc');
        Route::post('/student/resume/upload', [StudentDocumentController::class, 'upload'])
            ->defaults('type', 'resume')
            ->name('student.resume.upload');

        Route::get('/student/cover-letter', [CoverLetterController::class, 'create'])->name('student.cover-letter.create');
        Route::post('/student/cover-letter', [CoverLetterController::class, 'store'])->name('student.cover-letter.store');
        Route::get('/student/cover-letter/download', [CoverLetterController::class, 'download'])->name('student.cover-letter.download');
        Route::get('/student/cover-letter/download-doc', [CoverLetterController::class, 'downloadDoc'])->name('student.cover-letter.download-doc');
        Route::post('/student/cover-letter/upload', [StudentDocumentController::class, 'upload'])
            ->defaults('type', 'cover-letter')
            ->name('student.cover-letter.upload');

        Route::get('/student/documents/{document}/download', [StudentDocumentController::class, 'download'])
            ->whereNumber('document')
            ->name('student.documents.download');
        Route::delete('/student/documents/{document}', [StudentDocumentController::class, 'destroy'])
            ->whereNumber('document')
            ->name('student.documents.destroy');

        // Backward-compatible document URLs.
        Route::get('/student/documents/resume', fn () => redirect()->route('student.resume.builder', request()->query()));
        Route::get('/student/documents/resume/download', fn () => redirect()->route('student.resume.download', request()->query()));
        Route::get('/student/documents/resume/download-doc', fn () => redirect()->route('student.resume.download-doc', request()->query()));
        Route::get('/student/documents/cover-letter', fn () => redirect()->route('student.cover-letter.create'));
        Route::post('/student/documents/cover-letter', [CoverLetterController::class, 'store']);
        Route::get('/student/documents/cover-letter/download', fn () => redirect()->route('student.cover-letter.download'));

        // Student Company Tracker
        Route::get('/student/companies', [StudentCompanyTrackerController::class, 'index'])->name('student.companies.index');
        Route::post('/student/companies', [StudentCompanyTrackerController::class, 'store'])->name('student.companies.store');
        Route::put('/student/companies/{application}', [StudentCompanyTrackerController::class, 'update'])->name('student.companies.update');
        Route::delete('/student/companies/{application}', [StudentCompanyTrackerController::class, 'destroy'])->name('student.companies.destroy');
        Route::get('/student/companies/{application}/offer-letter', [StudentCompanyTrackerController::class, 'downloadOfferLetter'])
            ->name('student.companies.offer-letter');

        // Backward-compatible routes for existing bookmarks and forms.
        Route::get('/student/company-tracker', fn () => redirect()->route('student.companies.index'))
            ->name('student.company-tracker.index');
        Route::post('/student/company-tracker', [StudentCompanyTrackerController::class, 'store'])->name('student.company-tracker.store');
        Route::put('/student/company-tracker/{application}', [StudentCompanyTrackerController::class, 'update'])->name('student.company-tracker.update');
        Route::delete('/student/company-tracker/{application}', [StudentCompanyTrackerController::class, 'destroy'])->name('student.company-tracker.destroy');

        // Student Weekly Logbook
        Route::get('/student/logbook', [LogbookController::class, 'index'])->name('student.logbook.index');
        Route::get('/student/logbook/create', [LogbookController::class, 'create'])->name('student.logbook.create');
        Route::post('/student/logbook', [LogbookController::class, 'store'])->name('student.logbook.store');
        // --> ADD THESE THREE NEW LINES <--
        Route::get('/student/logbook/{id}', [LogbookController::class, 'show'])->name('student.logbook.show');
        Route::get('/student/logbook/{id}/edit', [LogbookController::class, 'edit'])->name('student.logbook.edit');
        Route::put('/student/logbook/{id}', [LogbookController::class, 'update'])->name('student.logbook.update');
        Route::post('/student/logbook/{logbook}/extension', [LogbookController::class, 'requestExtension'])
            ->name('student.logbook.extension.request');
    });

    Route::get('/logbooks/{logbook}/evidence', [LogbookController::class, 'downloadEvidence'])
        ->name('logbooks.evidence');
    Route::get('/logbooks/{logbook}/view', [LogbookController::class, 'view'])
        ->name('logbooks.show');
    Route::get('/logbooks/{logbook}/mc-evidence/{day}', [LogbookController::class, 'downloadMcEvidence'])
        ->whereNumber('day')
        ->name('logbooks.mc-evidence');
    Route::get('/logbooks/{logbook}/approval/{asset}', [LogbookController::class, 'viewApprovalAsset'])
        ->whereIn('asset', ['signature', 'stamp'])
        ->name('logbooks.approval-asset');
    Route::get('/final-clearances/{finalClearance}/download/{type}', [FinalClearanceController::class, 'download'])
        ->name('final-clearances.download');
    Route::get('/final-clearances/{finalClearance}/view/{type}', [FinalClearanceController::class, 'view'])
        ->name('final-clearances.view');
    Route::get('/placement-clearances/{placementClearance}/download/{type}', [PlacementClearanceDocumentController::class, 'download'])
        ->name('placement-clearances.download');
    Route::get('/placement-clearances/{placementClearance}/view/{type}', [PlacementClearanceDocumentController::class, 'view'])
        ->name('placement-clearances.view');
    Route::get('/applications/{application}/offer-letter', [StudentCompanyTrackerController::class, 'downloadOfferLetter'])
        ->name('applications.offer-letter');

    // Mentor Clearance Review
    Route::middleware('mentor')->prefix('mentor')->name('mentor.')->group(function () {
        Route::get('/dashboard', [MentorDashboardController::class, 'index'])->name('dashboard');
        Route::get('/profile', [MentorProfileController::class, 'edit'])->name('profile.edit');
        Route::put('/profile', [MentorProfileController::class, 'update'])->name('profile.update');
        Route::get('/clearances', [MentorClearanceController::class, 'index'])->name('clearances.index');
        Route::get('/clearances/{clearance}', [MentorClearanceController::class, 'show'])->name('clearances.show');
        Route::get('/clearances/{clearance}/download/{type}', [MentorClearanceController::class, 'download'])->name('clearances.download');
        Route::patch('/clearances/{clearance}/approve', [MentorClearanceController::class, 'approve'])->name('clearances.approve');
        Route::patch('/clearances/{clearance}/reject', [MentorClearanceController::class, 'reject'])->name('clearances.reject');
        Route::get('/final-clearances', [MentorFinalClearanceController::class, 'index'])->name('final-clearances.index');
        Route::patch('/final-clearances/{finalClearance}/approve', [MentorFinalClearanceController::class, 'approve'])->name('final-clearances.approve');
        Route::patch('/final-clearances/{finalClearance}/reject', [MentorFinalClearanceController::class, 'reject'])->name('final-clearances.reject');
        Route::get('/evaluations', [PerformanceEvaluationController::class, 'mentorIndex'])->name('evaluations.index');
        Route::patch('/logbooks/{logbook}/extension/approve', [LogbookController::class, 'approveExtension'])->name('logbooks.extension.approve');
        Route::patch('/logbooks/{logbook}/extension/reject', [LogbookController::class, 'rejectExtension'])->name('logbooks.extension.reject');
        Route::post('/results/{student}', [MentorResultController::class, 'store'])->name('results.store');
        Route::get('/results-export', [MentorResultController::class, 'export'])->name('results.export');
    });

    // Admin User Management
    Route::middleware('admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
        Route::get('/semesters', [AdminInternshipCycleController::class, 'index'])->name('semesters.index');
        Route::get('/semesters/create', [AdminInternshipCycleController::class, 'create'])->name('semesters.create');
        Route::post('/semesters', [AdminInternshipCycleController::class, 'store'])->name('semesters.store');
        Route::get('/semesters/{semester}', [AdminInternshipCycleController::class, 'show'])->name('semesters.show');
        Route::get('/semesters/{semester}/edit', [AdminInternshipCycleController::class, 'edit'])->name('semesters.edit');
        Route::put('/semesters/{semester}', [AdminInternshipCycleController::class, 'update'])->name('semesters.update');
        Route::post('/semesters/{semester}/students', [AdminInternshipCycleController::class, 'assignStudents'])->name('semesters.students.store');
        Route::patch('/semesters/{semester}/students/{student}', [AdminInternshipCycleController::class, 'updateAssignment'])->name('semesters.students.update');
        Route::delete('/semesters/{semester}/students/{student}', [AdminInternshipCycleController::class, 'removeStudent'])->name('semesters.students.destroy');
        Route::patch('/semesters/{semester}/activate', [AdminInternshipCycleController::class, 'activate'])->name('semesters.activate');
        Route::patch('/semesters/{semester}/close', [AdminInternshipCycleController::class, 'close'])->name('semesters.close');
        Route::patch('/semesters/{semester}/archive', [AdminInternshipCycleController::class, 'archive'])->name('semesters.archive');
        Route::get('/evaluation-forms', [AdminEvaluationFormController::class, 'index'])->name('evaluation-forms.index');
        Route::post('/evaluation-forms', [AdminEvaluationFormController::class, 'store'])->name('evaluation-forms.store');
        Route::patch('/evaluation-forms/{evaluationForm}/activate', [AdminEvaluationFormController::class, 'activate'])->name('evaluation-forms.activate');
        Route::get('/evaluation-forms/{evaluationForm}/download', [AdminEvaluationFormController::class, 'download'])->name('evaluation-forms.download');
        Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');
        Route::get('/users/create', [AdminUserController::class, 'create'])->name('users.create');
        Route::post('/users', [AdminUserController::class, 'store'])->name('users.store');
        Route::get('/users/{user}/edit', [AdminUserController::class, 'edit'])->name('users.edit');
        Route::put('/users/{user}', [AdminUserController::class, 'update'])->name('users.update');
        Route::delete('/users/{user}', [AdminUserController::class, 'destroy'])->name('users.destroy');

        // Assign Mentor Route
        Route::patch('/users/{user}/assign-mentor', [AdminUserController::class, 'assignMentor'])->name('users.assign-mentor');

        // Admin Clearance Routes
        Route::get('/clearances', [AdminClearanceController::class, 'index'])->name('clearances.index');
        Route::get('/clearances-export', [AdminClearanceController::class, 'export'])->name('clearances.export');
        Route::post('/clearances/{id}/generate-supervisor', [AdminClearanceController::class, 'generateSupervisorAccount'])->name('clearances.generate-supervisor');
    });

    Route::middleware('supervisor')->prefix('supervisor')->name('supervisor.')->group(function () {

        // The dashboard redirect we added in the last step
        Route::get('/dashboard', [SupervisorDashboardController::class, 'index'])->name('dashboard');

        Route::get('/profile', [SupervisorProfileController::class, 'edit'])->name('profile.edit');
        Route::put('/profile', [SupervisorProfileController::class, 'update'])->name('profile.update');

        // Your existing logbook routes...
        Route::get('/logbooks', [LogbookController::class, 'supervisorIndex'])->name('logbooks.index');
        Route::patch('/logbooks/{id}/approve', [LogbookController::class, 'approve'])->name('logbooks.approve');
        Route::patch('/logbooks/{id}/reject', [LogbookController::class, 'reject'])->name('logbooks.reject');
        Route::get('/logbooks/history', [LogbookController::class, 'supervisorHistory'])->name('logbooks.history');
        Route::get('/final-clearances', [SupervisorFinalClearanceController::class, 'index'])->name('final-clearances.index');
        Route::patch('/final-clearances/{finalClearance}/approve', [SupervisorFinalClearanceController::class, 'approve'])->name('final-clearances.approve');
        Route::patch('/final-clearances/{finalClearance}/reject', [SupervisorFinalClearanceController::class, 'reject'])->name('final-clearances.reject');
        Route::get('/evaluations', [PerformanceEvaluationController::class, 'supervisorIndex'])->name('evaluations.index');
        Route::get('/evaluations/{student}/{type}', [PerformanceEvaluationController::class, 'edit'])
            ->whereNumber('student')
            ->whereIn('type', ['midterm', 'final'])
            ->name('evaluations.edit');
        Route::put('/evaluations/{student}/{type}', [PerformanceEvaluationController::class, 'store'])
            ->whereNumber('student')
            ->whereIn('type', ['midterm', 'final'])
            ->name('evaluations.store');

    });

}); // This is the brace that closes the main 'auth' middleware group!

require __DIR__.'/auth.php';
