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
        abort(403, 'Academic Mentor profile details can only be managed by an administrator.');
    }
}
