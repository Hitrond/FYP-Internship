<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class MentorProfileController extends Controller
{
    public function edit(Request $request)
    {
        $user = $request->user();
        $user->load('profile');

        return view('mentor.profile.edit', compact('user'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'mentor_staff_id' => ['nullable', 'string', 'max:50'],
            'mentor_department' => ['nullable', 'string', 'max:255'],
            'notify_email_missed_logbook' => ['nullable', 'boolean'],
            'notify_dashboard_only' => ['nullable', 'boolean'],
        ]);

        $validated['notify_email_missed_logbook'] = $request->boolean('notify_email_missed_logbook');
        $validated['notify_dashboard_only'] = $request->boolean('notify_dashboard_only');

        $user = $request->user();

        if ($user->profile) {
            $user->profile->update($validated);
        } else {
            $user->profile()->create($validated);
        }

        return redirect()->route('mentor.profile.edit')->with('status', 'profile-updated');
    }
}
