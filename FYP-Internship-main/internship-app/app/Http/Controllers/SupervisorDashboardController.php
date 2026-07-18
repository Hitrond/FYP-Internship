<?php

namespace App\Http\Controllers;

use App\Models\FinalClearance;
use App\Models\Logbook;
use App\Models\PerformanceEvaluation;
use Illuminate\Http\Request;

class SupervisorDashboardController extends Controller
{
    public function index(Request $request)
    {
        $supervisor = $request->user();
        $students = $supervisor->supervisedStudents()
            ->with('profile')
            ->when($request->filled('search'), function ($query) use ($request): void {
                $search = trim((string) $request->input('search'));
                $query->where(function ($query) use ($search): void {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhereHas('profile', fn ($profile) => $profile->where('tp_number', 'like', "%{$search}%"));
                });
            })
            ->orderBy('name')
            ->get();
        $studentIds = $students->pluck('id');

        $pendingLogbooks = Logbook::whereIn('user_id', $studentIds)
            ->where('status', 'pending')
            ->count();
        $recentLogbooks = Logbook::with('student.profile')
            ->whereIn('user_id', $studentIds)
            ->latest('updated_at')
            ->limit(5)
            ->get();
        $attendanceIssues = Logbook::whereIn('user_id', $studentIds)
            ->where('status', 'rejected')
            ->where('rejection_category', 'attendance')
            ->count();
        $submittedEvaluations = PerformanceEvaluation::whereIn('student_id', $studentIds)
            ->where('supervisor_id', $supervisor->id)
            ->where('status', PerformanceEvaluation::STATUS_SUBMITTED)
            ->count();
        $pendingEvaluations = max(0, ($students->count() * 2) - $submittedEvaluations);
        $pendingClearances = FinalClearance::where('supervisor_id', $supervisor->id)
            ->where('supervisor_status', FinalClearance::STATUS_PENDING)
            ->count();
        return view('supervisor.dashboard', compact(
            'students',
            'pendingLogbooks',
            'recentLogbooks',
            'attendanceIssues',
            'pendingEvaluations',
            'pendingClearances',
        ));
    }
}
