<?php

namespace App\Services;

use App\Models\User;

class StudentDocumentReadinessService
{
    public function resume(User $user): array
    {
        $user->loadMissing(['profile', 'education', 'skills']);

        return $this->summarize(
            [
                ['label' => 'Full name', 'complete' => filled($user->name)],
                ['label' => 'Email address', 'complete' => filled($user->profile?->personal_email ?: $user->email)],
                ['label' => 'Contact number', 'complete' => filled($user->profile?->contact_number)],
                ['label' => 'Profile summary', 'complete' => filled($user->profile?->bio)],
                ['label' => 'Education history', 'complete' => $user->education->isNotEmpty()],
                ['label' => 'Skills', 'complete' => $user->skills->isNotEmpty()],
            ],
            [
                ['label' => 'Course name', 'complete' => filled($user->profile?->course_name)],
                ['label' => 'Projects', 'complete' => filled($user->profile?->projects_summary)],
                ['label' => 'Languages', 'complete' => filled($user->profile?->languages_summary)],
                ['label' => 'LinkedIn or portfolio', 'complete' => filled($user->profile?->linkedin_url) || filled($user->profile?->portfolio_url)],
            ]
        );
    }

    public function coverLetter(User $user): array
    {
        $user->loadMissing(['profile', 'skills']);

        return $this->summarize(
            [
                ['label' => 'Full name', 'complete' => filled($user->name)],
                ['label' => 'Email address', 'complete' => filled($user->profile?->personal_email ?: $user->email)],
                ['label' => 'Contact number', 'complete' => filled($user->profile?->contact_number)],
                ['label' => 'At least one skill', 'complete' => $user->skills->isNotEmpty()],
            ],
            [
                ['label' => 'Profile summary', 'complete' => filled($user->profile?->bio)],
                ['label' => 'Projects', 'complete' => filled($user->profile?->projects_summary)],
                ['label' => 'LinkedIn or portfolio', 'complete' => filled($user->profile?->linkedin_url) || filled($user->profile?->portfolio_url)],
            ]
        );
    }

    private function summarize(array $required, array $recommended): array
    {
        $completed = collect($required)->where('complete', true)->count();
        $total = count($required);

        return [
            'required' => $required,
            'recommended' => $recommended,
            'complete' => $completed === $total,
            'completed' => $completed,
            'total' => $total,
            'percentage' => $total > 0 ? (int) round(($completed / $total) * 100) : 100,
            'missing' => collect($required)
                ->where('complete', false)
                ->pluck('label')
                ->values()
                ->all(),
        ];
    }
}
