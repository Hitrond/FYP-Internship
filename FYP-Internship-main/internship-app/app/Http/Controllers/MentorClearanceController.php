<?php

namespace App\Http\Controllers;

use App\Models\PlacementClearance;
use App\Services\PlacementTimelineService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class MentorClearanceController extends Controller
{
    public function index(Request $request)
    {
        $clearances = PlacementClearance::with('student')
            ->whereHas('student', fn ($query) => $query->where('mentor_id', auth()->id()))
            ->when($request->filled('search'), function ($query) use ($request): void {
                $search = trim((string) $request->input('search'));
                $query->where(function ($query) use ($search): void {
                    $query->where('company_name', 'like', "%{$search}%")
                        ->orWhere('supervisor_name', 'like', "%{$search}%")
                        ->orWhere('supervisor_personal_email', 'like', "%{$search}%")
                        ->orWhereHas('student', function ($student) use ($search): void {
                            $student->where('name', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%")
                                ->orWhereHas('profile', fn ($profile) => $profile->where('tp_number', 'like', "%{$search}%"));
                        });
                });
            })
            ->when(in_array($request->input('status'), ['pending', 'approved', 'rejected', 'completed'], true), fn ($query) => $query->where('status', $request->input('status')))
            ->orderByRaw("case when status = 'pending' then 0 else 1 end")
            ->latest()
            ->paginate(25)
            ->withQueryString();

        return view('mentor.clearance.index', compact('clearances'));
    }

    public function show(PlacementClearance $clearance)
    {
        $this->authorizeClearance($clearance);

        return view('mentor.clearance.show', compact('clearance'));
    }

    public function download(PlacementClearance $clearance, string $type)
    {
        $this->authorizeClearance($clearance);

        $files = [
            'job_offer' => $clearance->job_offer_path,
            'indemnity_letter' => $clearance->indemnity_path,
            'placement_agreement' => $clearance->placement_agreement_path,
        ];

        if (! array_key_exists($type, $files)) {
            abort(404);
        }

        return Storage::disk('local')->download($files[$type]);
    }

    public function approve(
        PlacementClearance $clearance,
        PlacementTimelineService $timeline
    ) {
        $this->authorizeClearance($clearance);

        if ($clearance->status !== 'pending') {
            return redirect()->route('mentor.clearances.show', $clearance)
                ->with('error', 'This submission has already been processed.');
        }
        if (! $clearance->start_date || ! $clearance->end_date) {
            return back()->with(
                'error',
                'Official start and end dates are required before placement approval.'
            );
        }

        DB::transaction(function () use ($clearance, $timeline) {
            $clearance->update([
                'mentor_id' => auth()->id(),
                'status' => 'approved',
                'approved_at' => now(),
                'rejected_at' => null,
                'rejection_reason' => null,
            ]);

            $timeline->generate($clearance->fresh());
        });

        return redirect()->route('mentor.clearances.index')->with(
            'success',
            'Placement approved and weekly logbooks generated. Admin must now send the supervisor login email to the personal email address.'
        );
    }

    public function reject(Request $request, PlacementClearance $clearance)
    {
        $this->authorizeClearance($clearance);

        if ($clearance->status !== 'pending') {
            return redirect()->route('mentor.clearances.show', $clearance)
                ->with('error', 'This submission has already been processed.');
        }

        $validated = $request->validate([
            'rejection_reason' => ['required', 'string', 'max:500'],
        ]);

        $clearance->update([
            'mentor_id' => auth()->id(),
            'status' => 'rejected',
            'rejection_reason' => $validated['rejection_reason'],
            'rejected_at' => now(),
            'approved_at' => null,
        ]);

        return redirect()->route('mentor.clearances.index')
            ->with('success', 'Placement submission rejected.');
    }

    private function authorizeClearance(PlacementClearance $clearance): void
    {
        $clearance->loadMissing('student');

        abort_unless(
            (int) $clearance->student?->mentor_id === (int) auth()->id(),
            403,
            'This student is not assigned to you.'
        );
    }
}
