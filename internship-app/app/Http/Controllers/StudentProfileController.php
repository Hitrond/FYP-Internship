<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Education;
use App\Models\Skill;
use Illuminate\Support\Facades\Auth;

class StudentProfileController extends Controller
{
    /**
     * Display the student profile form.
     */
    public function edit(Request $request)
    {
        $user = $request->user();
        $user->load(['profile', 'education', 'skills']);

        return view('student.profile.edit', [
            'user' => $user,
        ]);
    }

    /**
     * Update the student's profile information.
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'phone_number' => 'nullable|string|max:20',
            'bio' => 'nullable|string',
            'linkedin_url' => 'nullable|url|max:255',
            'github_url' => 'nullable|url|max:255',
            'portfolio_url' => 'nullable|url|max:255',
        ]);

        $user = $request->user();
        
        if ($user->profile) {
            $user->profile->update($validated);
        } else {
            $user->profile()->create($validated);
        }

        return redirect()->route('student.profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Store a new education entry.
     */
    public function storeEducation(Request $request)
    {
        $validated = $request->validate([
            'institution_name' => 'required|string|max:255',
            'degree' => 'required|string|max:255',
            'field_of_study' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'description' => 'nullable|string',
        ]);

        $request->user()->education()->create($validated);

        return redirect()->route('student.profile.edit')->with('status', 'education-added');
    }

    /**
     * Delete an education entry.
     */
    public function destroyEducation(Request $request, Education $education)
    {
        if ($education->user_id !== $request->user()->id) {
            abort(403);
        }

        $education->delete();

        return redirect()->route('student.profile.edit')->with('status', 'education-deleted');
    }

    /**
     * Store a new skill entry.
     */
    public function storeSkill(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'proficiency' => 'required|string|in:Beginner,Intermediate,Advanced,Expert',
        ]);

        $request->user()->skills()->create($validated);

        return redirect()->route('student.profile.edit')->with('status', 'skill-added');
    }

    /**
     * Delete a skill entry.
     */
    public function destroySkill(Request $request, Skill $skill)
    {
        if ($skill->user_id !== $request->user()->id) {
            abort(403);
        }

        $skill->delete();

        return redirect()->route('student.profile.edit')->with('status', 'skill-deleted');
    }
}
