<?php

namespace App\Http\Controllers;

use App\Models\Education;
use App\Models\Skill;
use App\Services\StudentDocumentReadinessService;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class StudentProfileController extends Controller
{
    /**
     * Display the student profile form.
     */
    public function edit(Request $request, StudentDocumentReadinessService $readinessService)
    {
        $user = $request->user();
        $user->load(['profile', 'education', 'skills']);

        return view('student.profile.edit', [
            'user' => $user,
            'resumeReadiness' => $readinessService->resume($user),
            'coverLetterReadiness' => $readinessService->coverLetter($user),
        ]);
    }

    /**
     * Update the student's profile information.
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'name' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'full_name' => 'nullable|string|max:255',
            'tp_number' => 'nullable|string|max:50',
            'course_name' => 'nullable|string|max:255',
            'specialization' => 'nullable|string|max:255',
            'intake_code' => 'nullable|string|max:50',
            'personal_email' => 'nullable|email|max:255',
            'contact_number' => 'nullable|string|max:30',
            'internship_status' => 'nullable|string|in:looking,interviewing,secured',
            'bio' => 'nullable|string|max:3000',
            'projects_summary' => 'nullable|string|max:3000',
            'languages_summary' => 'nullable|string|max:3000',
            'references_summary' => 'nullable|string|max:3000',
            'linkedin_url' => 'nullable|url|max:500',
            'github_url' => 'nullable|url|max:500',
            'portfolio_url' => 'nullable|url|max:500',
        ]);

        $user = $request->user();
        $accountData = Arr::only($validated, ['name', 'email']);
        if (filled($accountData['name'] ?? null) || filled($accountData['email'] ?? null)) {
            if (filled($accountData['name'] ?? null)) {
                $user->name = $accountData['name'];
            }

            if (filled($accountData['email'] ?? null)) {
                $user->email = $accountData['email'];
                $user->email_verified_at = null;
            }

            $user->save();
        }

        $profileData = Arr::only($validated, [
            'full_name',
            'tp_number',
            'course_name',
            'specialization',
            'intake_code',
            'personal_email',
            'contact_number',
            'internship_status',
            'bio',
            'projects_summary',
            'languages_summary',
            'references_summary',
            'linkedin_url',
            'github_url',
            'portfolio_url',
        ]);

        if ($user->profile) {
            $user->profile->update($profileData);
        } else {
            $user->profile()->create($profileData);
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
