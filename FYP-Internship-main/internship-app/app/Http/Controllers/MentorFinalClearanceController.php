<?php

namespace App\Http\Controllers;

use App\Models\FinalClearance;
use Illuminate\Http\Request;

class MentorFinalClearanceController extends Controller
{
    public function index(Request $request)
    {
        $clearances = FinalClearance::with(['student', 'placementClearance', 'events.actor'])
            ->where('mentor_id', $request->user()->id)
            ->when($request->filled('search'), function ($query) use ($request): void {
                $search = trim((string) $request->input('search'));
                $query->where(function ($query) use ($search): void {
                    $query->whereHas('student', function ($student) use ($search): void {
                        $student->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%")
                            ->orWhereHas('profile', fn ($profile) => $profile->where('tp_number', 'like', "%{$search}%"));
                    })
                        ->orWhereHas('placementClearance', fn ($placement) => $placement->where('company_name', 'like', "%{$search}%"));
                });
            })
            ->when(in_array($request->input('status'), [
                FinalClearance::STATUS_PENDING,
                FinalClearance::STATUS_APPROVED,
                FinalClearance::STATUS_REJECTED,
            ], true), fn ($query) => $query->where('mentor_status', $request->input('status')))
            ->orderByRaw("case when mentor_status = 'pending' then 0 else 1 end")
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('final-clearances.index', [
            'clearances' => $clearances,
            'role' => 'mentor',
        ]);
    }

    public function approve(Request $request, FinalClearance $finalClearance)
    {
        $this->authorizeAssigned($request, $finalClearance);

        $finalClearance->update([
            'mentor_status' => FinalClearance::STATUS_APPROVED,
            'mentor_feedback' => null,
            'mentor_signed_at' => now(),
        ]);
        $finalClearance->events()->create([
            'actor_id' => $request->user()->id,
            'action' => 'mentor_approved',
            'actor_role' => 'academic mentor',
        ]);
        $finalClearance->refreshOverallStatus();

        return back()->with('success', 'Final clearance signed by Mentor.');
    }

    public function reject(Request $request, FinalClearance $finalClearance)
    {
        $this->authorizeAssigned($request, $finalClearance);

        $validated = $request->validate([
            'feedback' => ['required', 'string', 'max:2000'],
        ]);

        $finalClearance->update([
            'mentor_status' => FinalClearance::STATUS_REJECTED,
            'mentor_feedback' => $validated['feedback'],
            'mentor_signed_at' => null,
        ]);
        $finalClearance->events()->create([
            'actor_id' => $request->user()->id,
            'action' => 'mentor_rejected',
            'actor_role' => 'academic mentor',
            'feedback' => $validated['feedback'],
        ]);
        $finalClearance->refreshOverallStatus();

        return back()->with('success', 'Final clearance returned to the student.');
    }

    private function authorizeAssigned(Request $request, FinalClearance $clearance): void
    {
        abort_unless((int) $clearance->mentor_id === (int) $request->user()->id, 403);
        abort_unless($clearance->mentor_status === FinalClearance::STATUS_PENDING, 409);
    }
}
