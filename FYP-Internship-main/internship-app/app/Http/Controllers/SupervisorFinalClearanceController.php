<?php

namespace App\Http\Controllers;

use App\Models\FinalClearance;
use Illuminate\Http\Request;

class SupervisorFinalClearanceController extends Controller
{
    public function index(Request $request)
    {
        $clearances = FinalClearance::with(['student', 'placementClearance', 'events.actor'])
            ->where('supervisor_id', $request->user()->id)
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
            ], true), fn ($query) => $query->where('supervisor_status', $request->input('status')))
            ->orderByRaw("case when supervisor_status = 'pending' then 0 else 1 end")
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('final-clearances.index', [
            'clearances' => $clearances,
            'role' => 'supervisor',
        ]);
    }

    public function approve(Request $request, FinalClearance $finalClearance)
    {
        $this->authorizeAssigned($request, $finalClearance);

        $request->validate([
            'industrial_hours_completed' => ['accepted'],
            'company_property_cleared' => ['accepted'],
        ]);

        $finalClearance->update([
            'supervisor_status' => FinalClearance::STATUS_APPROVED,
            'supervisor_feedback' => null,
            'supervisor_signed_at' => now(),
            'industrial_hours_completed' => true,
            'company_property_cleared' => true,
        ]);
        $finalClearance->events()->create([
            'actor_id' => $request->user()->id,
            'action' => 'supervisor_approved',
            'actor_role' => 'industrial supervisor',
        ]);
        $finalClearance->refreshOverallStatus();

        return back()->with('success', 'Final clearance signed by Supervisor.');
    }

    public function reject(Request $request, FinalClearance $finalClearance)
    {
        $this->authorizeAssigned($request, $finalClearance);

        $validated = $request->validate([
            'feedback' => ['required', 'string', 'max:2000'],
        ]);

        $finalClearance->update([
            'supervisor_status' => FinalClearance::STATUS_REJECTED,
            'supervisor_feedback' => $validated['feedback'],
            'supervisor_signed_at' => null,
            'industrial_hours_completed' => false,
            'company_property_cleared' => false,
        ]);
        $finalClearance->events()->create([
            'actor_id' => $request->user()->id,
            'action' => 'supervisor_rejected',
            'actor_role' => 'industrial supervisor',
            'feedback' => $validated['feedback'],
        ]);
        $finalClearance->refreshOverallStatus();

        return back()->with('success', 'Final clearance returned to the student.');
    }

    private function authorizeAssigned(Request $request, FinalClearance $clearance): void
    {
        abort_unless((int) $clearance->supervisor_id === (int) $request->user()->id, 403);
        abort_unless($clearance->supervisor_status === FinalClearance::STATUS_PENDING, 409);
    }
}
