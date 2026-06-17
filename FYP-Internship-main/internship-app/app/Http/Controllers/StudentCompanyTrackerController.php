<?php

namespace App\Http\Controllers;

use App\Models\Application;
use Illuminate\Http\Request;

class StudentCompanyTrackerController extends Controller
{
    public const STATUSES = [
        'Interested',
        'Applied',
        'Interviewing',
        'Offered',
        'Rejected',
        'Accepted',
    ];

    public function index(Request $request)
    {
        $user = $request->user();

        abort_unless($user && $user->isStudent(), 403);

        $applications = $user->applications()
            ->latest()
            ->get();

        return view('student.company-tracker.index', [
            'applications' => $applications,
            'statuses' => self::STATUSES,
        ]);
    }

    public function store(Request $request)
    {
        $user = $request->user();

        abort_unless($user && $user->isStudent(), 403);

        $validated = $request->validate([
            'company_name' => ['required', 'string', 'max:255'],
            'position_title' => ['nullable', 'string', 'max:255'],
            'location' => ['nullable', 'string', 'max:255'],
            'status' => ['required', 'string', 'in:' . implode(',', self::STATUSES)],
            'applied_on' => ['nullable', 'date'],
            'last_contacted_on' => ['nullable', 'date'],
            'next_followup_on' => ['nullable', 'date'],
            'job_url' => ['nullable', 'string', 'max:2048'],
            'notes' => ['nullable', 'string'],
        ]);

        $user->applications()->create($validated);

        return redirect()->route('student.company-tracker.index');
    }

    public function update(Request $request, Application $application)
    {
        $user = $request->user();

        abort_unless($user && $user->isStudent(), 403);
        abort_unless((int) $application->user_id === (int) $user->id, 403);

        $validated = $request->validate([
            'company_name' => ['required', 'string', 'max:255'],
            'position_title' => ['nullable', 'string', 'max:255'],
            'location' => ['nullable', 'string', 'max:255'],
            'status' => ['required', 'string', 'in:' . implode(',', self::STATUSES)],
            'applied_on' => ['nullable', 'date'],
            'last_contacted_on' => ['nullable', 'date'],
            'next_followup_on' => ['nullable', 'date'],
            'job_url' => ['nullable', 'string', 'max:2048'],
            'notes' => ['nullable', 'string'],
        ]);

        $application->update($validated);

        return redirect()->route('student.company-tracker.index');
    }

    public function destroy(Request $request, Application $application)
    {
        $user = $request->user();

        abort_unless($user && $user->isStudent(), 403);
        abort_unless((int) $application->user_id === (int) $user->id, 403);

        $application->delete();

        return redirect()->route('student.company-tracker.index');
    }
}
