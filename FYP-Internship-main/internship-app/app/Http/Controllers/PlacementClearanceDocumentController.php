<?php

namespace App\Http\Controllers;

use App\Models\PlacementClearance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PlacementClearanceDocumentController extends Controller
{
    public function download(
        Request $request,
        PlacementClearance $placementClearance,
        string $type
    ) {
        $file = $this->authorizedFile($request, $placementClearance, $type);

        return Storage::disk('local')->download($file[0], $file[1]);
    }

    public function view(
        Request $request,
        PlacementClearance $placementClearance,
        string $type
    ) {
        $file = $this->authorizedFile($request, $placementClearance, $type);

        return Storage::disk('local')->response($file[0], $file[1], [], 'inline');
    }

    private function authorizedFile(
        Request $request,
        PlacementClearance $placementClearance,
        string $type
    ): array {
        $placementClearance->loadMissing('student');
        $user = $request->user();
        $canAccess = (int) $placementClearance->student_id === (int) $user->id
            || (int) $placementClearance->mentor_id === (int) $user->id
            || (int) $placementClearance->student?->mentor_id === (int) $user->id
            || (int) $placementClearance->student?->supervisor_id === (int) $user->id
            || $user->isAdmin();

        abort_unless($canAccess, 403);

        $file = match ($type) {
            'job-offer' => [$placementClearance->job_offer_path, 'job-offer.pdf'],
            'indemnity-letter' => [$placementClearance->indemnity_path, 'indemnity-letter.pdf'],
            'placement-agreement' => [
                $placementClearance->placement_agreement_path,
                'placement-agreement.pdf',
            ],
            default => null,
        };

        abort_unless($file && Storage::disk('local')->exists($file[0]), 404);

        return $file;
    }
}
