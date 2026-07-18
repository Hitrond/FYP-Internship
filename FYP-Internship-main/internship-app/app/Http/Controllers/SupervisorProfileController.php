<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;

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
            'personal_email' => [
                'nullable',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($request->user()->id),
            ],
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

        $profileData = Arr::except($validated, [
            'personal_email',
            'signature_image',
            'company_stamp',
        ]);

        $user->profile()->updateOrCreate(
            ['user_id' => $user->id],
            $profileData
        );

        return redirect()->route('supervisor.profile.edit')->with('status', 'profile-updated');
    }
}
