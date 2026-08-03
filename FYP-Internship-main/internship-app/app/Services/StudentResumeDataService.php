<?php

namespace App\Services;

use App\Models\User;

class StudentResumeDataService
{
    public function for(User $user): array
    {
        $user->loadMissing(['profile', 'education', 'skills']);
        $profile = $user->profile;

        $links = array_values(array_filter([
            filled($profile?->linkedin_url) ? 'LinkedIn: '.$profile->linkedin_url : null,
            filled($profile?->github_url) ? 'GitHub: '.$profile->github_url : null,
            filled($profile?->portfolio_url) ? 'Portfolio: '.$profile->portfolio_url : null,
        ]));

        return [
            'name' => $profile?->full_name ?: $user->name,
            'title' => $profile?->course_name ?: 'Software Engineering Student',
            'contact' => array_values(array_filter([
                $profile?->personal_email ?: $user->email,
                $profile?->contact_number,
                ...$links,
            ])),
            'summary' => $profile?->bio ?: '',
            'projects' => $this->lines($profile?->projects_summary),
            'education' => $user->education->map(function ($education): array {
                $qualification = $education->degree;

                if (filled($education->field_of_study)) {
                    $qualification .= ' in '.$education->field_of_study;
                }

                $start = $education->start_date?->format('M Y');
                $end = $education->end_date?->format('M Y') ?: 'Present';

                return [
                    'qualification' => $qualification,
                    'institution' => $education->institution_name,
                    'dates' => $start ? $start.' - '.$end : '',
                    'details' => $this->lines($education->description),
                ];
            })->values()->all(),
            'skills' => $user->skills->map(
                fn ($skill): string => $skill->name.(filled($skill->proficiency) ? ' ('.$skill->proficiency.')' : '')
            )->values()->all(),
            'languages' => $this->lines($profile?->languages_summary),
            'references' => $this->lines($profile?->references_summary),
        ];
    }

    private function lines(?string $value): array
    {
        if (blank($value)) {
            return [];
        }

        return array_values(array_filter(array_map(
            'trim',
            preg_split('/\R/', $value) ?: []
        )));
    }
}
