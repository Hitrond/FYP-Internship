<?php

namespace App\Http\Controllers;

use App\Models\FinalClearance;
use App\Models\InternshipCycle;
use App\Models\InternshipResult;
use App\Models\Logbook;
use App\Models\PlacementClearance;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class AdminDashboardController extends Controller
{
    public function index(Request $request): View
    {
        $cycles = InternshipCycle::latest('placement_window_start')->get();
        $selectedCycle = $request->filled('semester')
            ? $cycles->firstWhere('id', (int) $request->input('semester'))
            : ($cycles->firstWhere('status', InternshipCycle::STATUS_ACTIVE) ?? $cycles->first());
        $studentIds = $selectedCycle
            ? $selectedCycle->assignments()->pluck('student_id')
            : null;
        $studentCount = $studentIds?->count() ?? User::where('role', 'student')->count();
        $activeInterns = User::where('role', 'student')
            ->when($studentIds, fn ($query) => $query->whereIn('id', $studentIds))
            ->whereHas('placementClearances', fn ($query) => $query
                ->whereIn('status', ['approved', 'completed'])
                ->when($selectedCycle, fn ($query) => $query->where('internship_cycle_id', $selectedCycle->id)))
            ->count();
        $completedClearances = FinalClearance::where('status', FinalClearance::STATUS_COMPLETED)
            ->when($selectedCycle, fn ($query) => $query->where('internship_cycle_id', $selectedCycle->id))
            ->count();

        $stats = [
            'students' => $studentCount,
            'active_interns' => $activeInterns,
            'unassigned_students' => $selectedCycle
                ? $selectedCycle->assignments()->whereNull('mentor_id')->count()
                : User::where('role', 'student')->whereNull('mentor_id')->count(),
            'pending_placements' => PlacementClearance::where('status', 'pending')
                ->when($selectedCycle, fn ($query) => $query->where('internship_cycle_id', $selectedCycle->id))
                ->count(),
            'overdue_logbooks' => Logbook::where('status', 'overdue_locked')
                ->when($selectedCycle, fn ($query) => $query->where('internship_cycle_id', $selectedCycle->id))
                ->count(),
            'completed_clearances' => $completedClearances,
            'clearance_rate' => $activeInterns > 0
                ? round(($completedClearances / $activeInterns) * 100)
                : 0,
        ];

        $roleCounts = User::query()
            ->selectRaw('role, count(*) as total')
            ->groupBy('role')
            ->pluck('total', 'role');

        $placementCounts = PlacementClearance::query()
            ->when($selectedCycle, fn ($query) => $query->where('internship_cycle_id', $selectedCycle->id))
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $alerts = [
            'unassigned' => User::where('role', 'student')
                ->when(
                    $selectedCycle,
                    fn ($query) => $query->whereIn(
                        'id',
                        $selectedCycle->assignments()->whereNull('mentor_id')->pluck('student_id')
                    ),
                    fn ($query) => $query->whereNull('mentor_id')
                )
                ->latest()
                ->take(6)
                ->get(),
            'overdue' => Logbook::with('student')
                ->where('status', 'overdue_locked')
                ->when($selectedCycle, fn ($query) => $query->where('internship_cycle_id', $selectedCycle->id))
                ->orderBy('submission_due_at')
                ->take(6)
                ->get(),
            'extensions' => Logbook::with('student.mentor')
                ->where('extension_status', 'requested')
                ->when($selectedCycle, fn ($query) => $query->where('internship_cycle_id', $selectedCycle->id))
                ->oldest('extension_requested_at')
                ->take(6)
                ->get(),
            'attendance' => Logbook::with('student.mentor')
                ->where('status', 'rejected')
                ->where('rejection_category', 'attendance')
                ->when($selectedCycle, fn ($query) => $query->where('internship_cycle_id', $selectedCycle->id))
                ->latest('updated_at')
                ->take(6)
                ->get(),
        ];

        $resultCounts = InternshipResult::query()
            ->when($selectedCycle, fn ($query) => $query->where('internship_cycle_id', $selectedCycle->id))
            ->selectRaw('result, count(*) as total')
            ->groupBy('result')
            ->pluck('total', 'result');

        return view('admin.dashboard', compact(
            'stats',
            'roleCounts',
            'placementCounts',
            'alerts',
            'resultCounts',
            'cycles',
            'selectedCycle',
        ));
    }
}
