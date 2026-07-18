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
            'signature_image' => ['nullable', 'file', 'mimes:png,jpg,jpeg', 'max:5120'],
            'company_stamp' => ['nullable', 'file', 'mimes:png,jpg,jpeg', 'max:5120'],
        ]);

        $user = $request->user();

        if ($request->hasFile('signature_image')) {
            $validated['signature_path'] = $request->file('signature_image')
                ->store('supervisors/signatures', 'local');
        }

        if ($request->hasFile('company_stamp')) {
            $validated['stamp_path'] = $request->file('company_stamp')
                ->store('supervisors/stamps', 'local');
        }

        $user->profile()->updateOrCreate(
            ['user_id' => $user->id],
            collect($validated)->only(['signature_path', 'stamp_path'])->all()
        );

        return redirect()->route('supervisor.profile.edit')->with('status', 'approval-assets-updated');
    }
}
