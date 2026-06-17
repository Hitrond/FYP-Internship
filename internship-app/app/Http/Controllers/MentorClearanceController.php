<?php

namespace App\Http\Controllers;

use App\Models\PlacementClearance;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MentorClearanceController extends Controller
{
    public function index()
    {
        $clearances = PlacementClearance::with('student')
            ->orderByRaw("case when status = 'pending' then 0 else 1 end")
            ->latest()
            ->get();

        return view('mentor.clearance.index', compact('clearances'));
    }

    public function show(PlacementClearance $clearance)
    {
        return view('mentor.clearance.show', compact('clearance'));
    }

    public function download(PlacementClearance $clearance, string $type)
    {
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

    public function approve(PlacementClearance $clearance)
    {
        if ($clearance->status !== 'pending') {
            return redirect()->route('mentor.clearances.show', $clearance)
                ->with('error', 'This submission has already been processed.');
        }

        $emailWarning = null;

        DB::transaction(function () use ($clearance, &$emailWarning) {
            $loginEmail = $clearance->supervisor_personal_email ?? $clearance->supervisor_email;
            $supervisorUser = User::where('email', $loginEmail)->first();
            $generatedPassword = null;

            if (! $supervisorUser) {
                $generatedPassword = Str::password(32);
                $supervisorUser = User::create([
                    'name' => $clearance->supervisor_name,
                    'email' => $loginEmail,
                    'password' => Hash::make($generatedPassword),
                    'role' => 'supervisor',
                ]);

                try {
                    $token = Password::broker()->createToken($supervisorUser);
                    $resetUrl = route('password.reset', [
                        'token' => $token,
                        'email' => $supervisorUser->email,
                    ]);

                    Mail::raw(
                        "Your supervisor account has been created.\n\n".
                        "Email: {$loginEmail}\n".
                        "Activate your account and set a password here:\n".
                        "{$resetUrl}\n\n".
                        "If you did not request this, please ignore this email.",
                        function ($message) use ($clearance) {
                            $message->to($clearance->supervisor_personal_email ?? $clearance->supervisor_email)
                                ->subject('Supervisor Account Access');
                        }
                    );
                } catch (\Throwable $exception) {
                    $emailWarning = 'Supervisor account created, but email delivery failed.';
                }
            } elseif ($supervisorUser->role !== 'supervisor') {
                $emailWarning = 'Supervisor email already belongs to a non-supervisor account.';
            }

            $supervisorUser?->profile()->updateOrCreate(
                ['user_id' => $supervisorUser->id],
                [
                    'company_email' => $clearance->supervisor_email,
                    'company_name' => $clearance->company_name,
                    'company_address' => $clearance->office_address,
                ]
            );

            $clearance->update([
                'mentor_id' => auth()->id(),
                'supervisor_user_id' => $supervisorUser?->id,
                'status' => 'approved',
                'approved_at' => now(),
                'rejected_at' => null,
                'rejection_reason' => null,
            ]);
        });

        $message = $emailWarning ?? 'Placement approved and supervisor account processed.';

        return redirect()->route('mentor.clearances.index')->with(
            $emailWarning ? 'warning' : 'success',
            $message
        );
    }

    public function reject(Request $request, PlacementClearance $clearance)
    {
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
}
