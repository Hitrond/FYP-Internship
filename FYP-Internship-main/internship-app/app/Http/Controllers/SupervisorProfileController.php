<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SupervisorProfileController extends Controller
{
    public function edit(Request $request)
    {
        $user = $request->user();
        $user->load('profile');

        return view('supervisor.profile.edit', compact('user'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'supervisor_job_title' => ['nullable', 'string', 'max:100'],
            'supervisor_contact_number' => ['nullable', 'string', 'max:30'],
            'company_email' => ['nullable', 'email', 'max:255'],
            'company_name' => ['nullable', 'string', 'max:255'],
            'company_address' => ['nullable', 'string', 'max:255'],
            'industry' => ['nullable', 'string', 'max:100'],
            'personal_email' => ['nullable', 'email', 'max:255'],
            'signature_image' => ['nullable', 'file', 'mimes:png,jpg,jpeg', 'max:5120'],
            'company_stamp' => ['nullable', 'file', 'mimes:png,jpg,jpeg', 'max:5120'],
        ]);

        $user = $request->user();

        if (! empty($validated['personal_email']) && $validated['personal_email'] !== $user->email) {
            $user->email = $validated['personal_email'];
            $user->email_verified_at = null;
            $user->save();
        }

        if ($request->hasFile('signature_image')) {
            $validated['signature_path'] = $request->file('signature_image')
                ->store('supervisors/signatures', 'local');
        }

        if ($request->hasFile('company_stamp')) {
            $validated['stamp_path'] = $request->file('company_stamp')
                ->store('supervisors/stamps', 'local');
        }

        if ($user->profile) {
            $user->profile->update($validated);
        } else {
            $user->profile()->create($validated);
        }

        return redirect()->route('supervisor.profile.edit')->with('status', 'profile-updated');
    }
}
