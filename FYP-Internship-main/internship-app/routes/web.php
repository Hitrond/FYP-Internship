<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\StudentProfileController;
use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\StudentClearanceController;
use App\Http\Controllers\MentorClearanceController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MentorDashboardController;
use App\Http\Controllers\MentorProfileController;
use App\Http\Controllers\SupervisorProfileController;
use App\Http\Controllers\StudentResumeController;
use App\Http\Controllers\StudentCompanyTrackerController;
use App\Http\Controllers\CoverLetterController; // <-- Added Cover Letter Controller
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Student Profile & Competency Management
    Route::get('/student/profile', [StudentProfileController::class, 'edit'])->name('student.profile.edit');
    Route::put('/student/profile', [StudentProfileController::class, 'update'])->name('student.profile.update');
    Route::post('/student/education', [StudentProfileController::class, 'storeEducation'])->name('student.education.store');
    Route::delete('/student/education/{education}', [StudentProfileController::class, 'destroyEducation'])->name('student.education.destroy');
    Route::post('/student/skill', [StudentProfileController::class, 'storeSkill'])->name('student.skill.store');
    Route::delete('/student/skill/{skill}', [StudentProfileController::class, 'destroySkill'])->name('student.skill.destroy');
    Route::get('/student/clearance', [StudentClearanceController::class, 'create'])->name('student.clearance.create');
    Route::post('/student/clearance', [StudentClearanceController::class, 'store'])->name('student.clearance.store');
    
    // Student Documents (Resume & Cover Letter)
    Route::get('/student/documents/resume', [StudentResumeController::class, 'builder'])->name('student.resume.builder');
    Route::get('/student/documents/resume/download', [StudentResumeController::class, 'download'])->name('student.resume.download');
    
    // <-- Added Cover Letter Routes Here -->
    Route::get('/student/documents/cover-letter', [CoverLetterController::class, 'create'])->name('student.cover-letter.create');
    Route::post('/student/documents/cover-letter', [CoverLetterController::class, 'store'])->name('student.cover-letter.store');
    Route::get('/student/documents/cover-letter/download', [CoverLetterController::class, 'download'])->name('student.cover-letter.download');

    // Student Company Tracker
    Route::get('/student/company-tracker', [StudentCompanyTrackerController::class, 'index'])->name('student.company-tracker.index');
    Route::post('/student/company-tracker', [StudentCompanyTrackerController::class, 'store'])->name('student.company-tracker.store');
    Route::put('/student/company-tracker/{application}', [StudentCompanyTrackerController::class, 'update'])->name('student.company-tracker.update');
    Route::delete('/student/company-tracker/{application}', [StudentCompanyTrackerController::class, 'destroy'])->name('student.company-tracker.destroy');

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
    });

    // Admin User Management
    Route::middleware('admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');
        Route::get('/users/create', [AdminUserController::class, 'create'])->name('users.create');
        Route::post('/users', [AdminUserController::class, 'store'])->name('users.store');
        Route::delete('/users/{user}', [AdminUserController::class, 'destroy'])->name('users.destroy');
    });

    // Supervisor Portal
    Route::middleware('supervisor')->prefix('supervisor')->name('supervisor.')->group(function () {
        Route::get('/dashboard', function () {
            return view('supervisor.dashboard');
        })->name('dashboard');
        Route::get('/profile', [SupervisorProfileController::class, 'edit'])->name('profile.edit');
        Route::put('/profile', [SupervisorProfileController::class, 'update'])->name('profile.update');
        Route::get('/logbooks', function () {
            return view('supervisor.logbooks.index');
        })->name('logbooks.index');
        Route::get('/evaluation', function () {
            return view('supervisor.evaluation.create');
        })->name('evaluation.create');
    });
});

require __DIR__.'/auth.php';