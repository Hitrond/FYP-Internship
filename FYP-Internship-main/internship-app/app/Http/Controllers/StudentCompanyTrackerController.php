<?php

namespace App\Http\Controllers;

use App\Models\Application;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

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

        $baseQuery = $user->applications();
        $statusCounts = (clone $baseQuery)->selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status');
        $totalApplications = (clone $baseQuery)->count();

        $applications = $baseQuery
            ->when($request->filled('search'), function ($query) use ($request): void {
                $search = trim((string) $request->input('search'));
                $query->where(function ($query) use ($search): void {
                    $query->where('company_name', 'like', "%{$search}%")
                        ->orWhere('position_title', 'like', "%{$search}%")
                        ->orWhere('location', 'like', "%{$search}%");
                });
            })
            ->when(in_array($request->input('status'), self::STATUSES, true), fn ($query) => $query->where('status', $request->input('status')))
            ->orderByRaw("case when status = 'Accepted' then 0 when status = 'Offered' then 1 when status = 'Interviewing' then 2 when status = 'Applied' then 3 when status = 'Interested' then 4 else 5 end")
            ->latest('updated_at')
            ->paginate(8)
            ->withQueryString();

        return view('student.company-tracker.index', [
            'applications' => $applications,
            'statuses' => self::STATUSES,
            'statusCounts' => $statusCounts,
            'totalApplications' => $totalApplications,
        ]);
    }

    public function store(Request $request)
    {
        $user = $request->user();

        abort_unless($user && $user->isStudent(), 403);

        $validated = $this->validatedData($request);

        if ($request->hasFile('offer_letter')) {
            $validated['offer_letter_path'] = $request->file('offer_letter')
                ->store('application-offers', 'local');
        }

        $user->applications()->create($validated);

        return redirect()->route('student.companies.index')
            ->with('success', 'Company application added.');
    }

    public function update(Request $request, Application $application)
    {
        $user = $request->user();

        abort_unless($user && $user->isStudent(), 403);
        abort_unless((int) $application->user_id === (int) $user->id, 403);

        $validated = $this->validatedData($request);

        if ($request->hasFile('offer_letter')) {
            $newPath = $request->file('offer_letter')->store('application-offers', 'local');

            if ($application->offer_letter_path) {
                Storage::disk('local')->delete($application->offer_letter_path);
            }

            $validated['offer_letter_path'] = $newPath;
        }

        $application->update($validated);

        return redirect()->route('student.companies.index')
            ->with('success', 'Application updated.');
    }

    public function destroy(Request $request, Application $application)
    {
        $user = $request->user();

        abort_unless($user && $user->isStudent(), 403);
        abort_unless((int) $application->user_id === (int) $user->id, 403);

        if ($application->offer_letter_path) {
            Storage::disk('local')->delete($application->offer_letter_path);
        }

        $application->delete();

        return redirect()->route('student.companies.index')
            ->with('success', 'Application removed.');
    }

    public function downloadOfferLetter(Request $request, Application $application)
    {
        $application->loadMissing('user');
        $user = $request->user();
        $canAccess = (int) $application->user_id === (int) $user->id
            || ($user->isMentor()
                && (int) $application->user?->mentor_id === (int) $user->id)
            || $user->isAdmin();

        abort_unless($canAccess, 403);
        abort_unless(
            $application->offer_letter_path
                && Storage::disk('local')->exists($application->offer_letter_path),
            404
        );

        $extension = pathinfo($application->offer_letter_path, PATHINFO_EXTENSION);
        $filename = Str::slug($application->company_name).'-offer-letter.'.$extension;

        return Storage::disk('local')->download($application->offer_letter_path, $filename);
    }

    private function validatedData(Request $request): array
    {
        $validated = $request->validate([
            'company_name' => ['required', 'string', 'max:255'],
            'position_title' => ['nullable', 'string', 'max:255'],
            'location' => ['nullable', 'string', 'max:255'],
            'contact_name' => ['nullable', 'string', 'max:255'],
            'contact_email' => ['nullable', 'email', 'max:255'],
            'contact_phone' => ['nullable', 'string', 'max:50'],
            'status' => ['required', 'string', 'in:'.implode(',', self::STATUSES)],
            'applied_on' => ['nullable', 'date'],
            'last_contacted_on' => ['nullable', 'date'],
            'next_followup_on' => ['nullable', 'date'],
            'job_url' => ['nullable', 'url', 'max:2048'],
            'offer_letter' => ['nullable', 'file', 'extensions:pdf', 'max:102400'],
            'notes' => ['nullable', 'string', 'max:5000'],
        ]);

        unset($validated['offer_letter']);

        return $validated;
    }
}
