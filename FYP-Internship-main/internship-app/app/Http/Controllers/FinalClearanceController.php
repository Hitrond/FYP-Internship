<?php

namespace App\Http\Controllers;

use App\Models\FinalClearance;
use App\Models\PlacementClearance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class FinalClearanceController extends Controller
{
    public function store(Request $request)
    {
        $student = $request->user();

        if (! $student->mentor_id || ! $student->supervisor_id) {
            return back()->with(
                'final-error',
                'A Mentor and Supervisor must both be assigned before final clearance can be submitted.'
            );
        }

        $placementClearance = PlacementClearance::where('student_id', $student->id)
            ->whereIn('status', ['approved', 'completed'])
            ->latest()
            ->first();

        if (! $placementClearance) {
            return back()->with(
                'final-error',
                'An approved placement submission is required before final clearance.'
            );
        }

        $clearance = FinalClearance::where('student_id', $student->id)->first();

        if ($clearance && in_array($clearance->status, [
            FinalClearance::STATUS_PENDING,
            FinalClearance::STATUS_COMPLETED,
        ], true)) {
            return back()->with(
                'final-error',
                $clearance->status === FinalClearance::STATUS_COMPLETED
                    ? 'Your final clearance is already complete.'
                    : 'Your final clearance is still awaiting review.'
            );
        }

        $validated = $request->validate([
            'final_report' => ['required', 'file', 'mimes:pdf,doc,docx', 'max:51200'],
            'report_clearance_form' => ['required', 'file', 'mimes:pdf,doc,docx', 'max:10240'],
        ]);

        $report = $validated['final_report'];
        $reportClearanceForm = $validated['report_clearance_form'];
        $reportPath = $report->store('final-clearances/'.$student->id, 'local');
        $reportClearanceFormPath = $reportClearanceForm->store('final-clearances/'.$student->id, 'local');
        $oldPaths = $clearance
            ? [
                $clearance->report_path,
                $clearance->report_clearance_form_path,
                $clearance->slides_path,
            ]
            : [];

        $clearance = FinalClearance::updateOrCreate(
            ['student_id' => $student->id],
            [
                'internship_cycle_id' => $placementClearance->internship_cycle_id,
                'placement_clearance_id' => $placementClearance->id,
                'mentor_id' => $student->mentor_id,
                'supervisor_id' => $student->supervisor_id,
                'report_path' => $reportPath,
                'report_original_name' => $report->getClientOriginalName(),
                'report_clearance_form_path' => $reportClearanceFormPath,
                'report_clearance_form_original_name' => $reportClearanceForm->getClientOriginalName(),
                'slides_path' => null,
                'slides_original_name' => null,
                'status' => FinalClearance::STATUS_PENDING,
                'mentor_status' => FinalClearance::STATUS_PENDING,
                'mentor_feedback' => null,
                'mentor_signed_at' => null,
                'industrial_hours_completed' => false,
                'company_property_cleared' => false,
                'supervisor_status' => FinalClearance::STATUS_PENDING,
                'supervisor_feedback' => null,
                'supervisor_signed_at' => null,
                'completed_at' => null,
            ]
        );

        foreach (array_unique(array_filter($oldPaths)) as $oldPath) {
            Storage::disk('local')->delete($oldPath);
        }

        return redirect()->route('student.clearance.create')
            ->with('final-success', 'Final clearance submitted to your Mentor and Supervisor.');
    }

    public function download(Request $request, FinalClearance $finalClearance, string $type)
    {
        $file = $this->authorizedFile($request, $finalClearance, $type);

        return Storage::disk('local')->download($file[0], $file[1]);
    }

    public function view(Request $request, FinalClearance $finalClearance, string $type)
    {
        $file = $this->authorizedFile($request, $finalClearance, $type);

        return Storage::disk('local')->response($file[0], $file[1], [], 'inline');
    }

    private function authorizedFile(
        Request $request,
        FinalClearance $finalClearance,
        string $type
    ): array {
        $user = $request->user();
        $canAccess = (int) $finalClearance->student_id === (int) $user->id
            || (int) $finalClearance->mentor_id === (int) $user->id
            || (int) $finalClearance->supervisor_id === (int) $user->id
            || $user->isAdmin();

        abort_unless($canAccess, 403);

        $file = match ($type) {
            'report' => [$finalClearance->report_path, $finalClearance->report_original_name],
            'report-clearance-form' => $finalClearance->report_clearance_form_path
                ? [
                    $finalClearance->report_clearance_form_path,
                    $finalClearance->report_clearance_form_original_name,
                ]
                : null,
            'slides' => $finalClearance->slides_path
                ? [$finalClearance->slides_path, $finalClearance->slides_original_name]
                : null,
            default => null,
        };

        abort_unless($file && Storage::disk('local')->exists($file[0]), 404);

        return $file;
    }
}
