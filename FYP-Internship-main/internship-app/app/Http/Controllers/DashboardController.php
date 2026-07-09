<?php

namespace App\Http\Controllers;

use App\Models\FinalClearance;
use App\Models\PlacementClearance;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        if ($user->isMentor()) {
            return redirect()->route('mentor.dashboard');
        }

        if ($user->isSupervisor()) {
            return redirect()->route('supervisor.dashboard');
        }

        if ($user->isAdmin()) {
            return redirect()->route('admin.dashboard');
        }

        abort_unless($user->isStudent(), 403);

        $applications = $user->applications()->latest('updated_at')->get();
        $acceptedApplication = $applications->firstWhere('status', 'Accepted');
        $latestLogbook = $user->logbooks()->latest('updated_at')->first();
        $followUps = $user->applications()
            ->whereNotNull('next_followup_on')
            ->whereNotIn('status', ['Accepted', 'Rejected'])
            ->orderBy('next_followup_on')
            ->take(5)
            ->get();

        $latestClearance = PlacementClearance::where('student_id', $user->id)
            ->with('cycle')
            ->latest()
            ->first();
        $totalWeeks = $latestClearance?->cycle?->duration_weeks
            ?? max(16, (int) $user->logbooks()->where('timeline_generated', true)->max('week_number'));
        $submittedWeeks = $user->logbooks()->pluck('week_number')->all();
        $nextLogbookWeek = collect(range(1, $totalWeeks))->first(
            fn (int $week) => ! in_array($week, $submittedWeeks, true)
        );
        $finalClearance = FinalClearance::where('student_id', $user->id)->first();

        $internshipStatus = $acceptedApplication
            ? 'secured'
            : ($user->profile?->internship_status
                ?? ($applications->contains('status', 'Interviewing') ? 'interviewing' : 'looking'));

        return view('dashboard', [
            'applicationCounts' => [
                'total' => $applications->count(),
                'active' => $applications->whereIn('status', [
                    'Interested',
                    'Applied',
                    'Interviewing',
                    'Offered',
                ])->count(),
                'interviewing' => $applications->where('status', 'Interviewing')->count(),
                'accepted' => $applications->where('status', 'Accepted')->count(),
            ],
            'acceptedApplication' => $acceptedApplication,
            'followUps' => $followUps,
            'latestLogbook' => $latestLogbook,
            'nextLogbookWeek' => $nextLogbookWeek,
            'latestClearance' => $latestClearance,
            'finalClearance' => $finalClearance,
            'internshipStatus' => $internshipStatus,
        ]);
    }
}
