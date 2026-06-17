<?php

namespace App\Http\Controllers;

use App\Models\PlacementClearance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class StudentClearanceController extends Controller
{
    public function create(Request $request)
    {
        $student = $request->user();
        $latestClearance = PlacementClearance::where('student_id', $student->id)
            ->latest()
            ->first();

        $prefillClearance = ($latestClearance && $latestClearance->status !== 'pending')
            ? $latestClearance
            : null;

        return view('student.clearance.create', compact('latestClearance', 'prefillClearance'));
    }

    public function store(Request $request)
    {
        $latestClearance = PlacementClearance::where('student_id', auth()->id())
            ->latest()
            ->first();

        if ($latestClearance && $latestClearance->status === 'pending') {
            return redirect()->route('student.clearance.create')
                ->with('error', 'You already have a pending submission. Please wait for mentor review.');
        }

        $validated = $request->validate([
            'company_name' => ['required', 'string', 'max:255'],
            'office_address' => ['required', 'string', 'max:255'],
            'supervisor_name' => ['required', 'string', 'max:255'],
            'supervisor_email' => ['required', 'string', 'email', 'max:255'],
            'supervisor_personal_email' => ['required', 'string', 'email', 'max:255'],
            'job_offer' => ['required', 'file', 'mimes:pdf', 'max:5120'],
            'indemnity_letter' => ['required', 'file', 'mimes:pdf', 'max:5120'],
            'placement_agreement' => ['required', 'file', 'mimes:pdf', 'max:5120'],
        ]);

        $jobOfferPath = $request->file('job_offer')->store('clearances', 'local');
        $indemnityPath = $request->file('indemnity_letter')->store('clearances', 'local');
        $agreementPath = $request->file('placement_agreement')->store('clearances', 'local');

        $clearance = PlacementClearance::create([
            'student_id' => $student->id,
            'mentor_id' => $student->mentor_id,
            'company_name' => $validated['company_name'],
            'office_address' => $validated['office_address'],
            'supervisor_name' => $validated['supervisor_name'],
            'supervisor_email' => $validated['supervisor_email'],
            'supervisor_personal_email' => $validated['supervisor_personal_email'],
            'job_offer_path' => $jobOfferPath,
            'indemnity_path' => $indemnityPath,
            'placement_agreement_path' => $agreementPath,
            'status' => 'pending',
        ]);

        $warning = null;
        $mentorEmail = $student->mentor?->email;

        if ($mentorEmail) {
            try {
                Mail::raw(
                    "A new placement clearance submission is ready for review.\n\n".
                    "Student ID: {$clearance->student_id}\n".
                    "Company: {$clearance->company_name}\n".
                    "Supervisor: {$clearance->supervisor_name}",
                    function ($message) use ($mentorEmail) {
                        $message->to($mentorEmail)
                            ->subject('New Placement Clearance Submission');
                    }
                );
            } catch (\Throwable $exception) {
                $warning = 'Submission saved, but mentor email notification failed.';
            }
        } else {
            $warning = 'Submission saved, but no mentor is assigned to this student.';
        }

        return redirect()->route('student.clearance.create')
            ->with($warning ? 'warning' : 'success', $warning ?? 'Placement submission sent to mentor for review.');
    }
}
