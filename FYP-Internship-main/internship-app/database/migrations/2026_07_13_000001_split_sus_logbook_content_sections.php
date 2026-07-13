<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $records = [
            'adam@gmail.com' => [
                1 => ['Developed authentication pages and tested login workflow', 'Built Laravel login validation, role-based redirects, and authentication tests using PHP and PostgreSQL.'],
                3 => ['Created student dashboard and company application tracker interface', 'Implemented dashboard cards and company-application CRUD screens using Laravel Blade, Eloquent, HTML, CSS, and JavaScript.'],
            ],
            'aisha@gmail.com' => [
                1 => ['Prepared API endpoints for application tracker', 'Created REST-style routes, controllers, request validation, and API test cases for the application tracker.'],
                2 => ['Updated API validation and fixed database relationship issue', 'Improved validation rules and corrected Eloquent relationships between students and company applications.'],
            ],
            'daniel@gmail.com' => [
                1 => ['Designed wireframes for logbook and supervisor approval pages', 'Produced Figma wireframes and mapped the student submission and supervisor approval user journeys.'],
                2 => ['Implemented logbook submission form and evidence upload placeholder', 'Built the weekly submission form, validation feedback, and evidence-upload handling.'],
                3 => ['Implemented supervisor dashboard table and pending approval list', 'Implemented the assigned-intern table, pending-review filters, and role-based dashboard queries.'],
            ],
        ];

        foreach ($records as $email => $weeks) {
            $studentId = DB::table('users')->where('email', $email)->value('id');
            if (! $studentId) {
                continue;
            }

            foreach ($weeks as $week => [$objectives, $content]) {
                DB::table('logbooks')
                    ->where('user_id', $studentId)
                    ->where('week_number', $week)
                    ->update([
                        'description' => "=== Type(s) & Objective(s) ===\n{$objectives}\n\n=== Content & Skills ===\n{$content}",
                        'updated_at' => now(),
                    ]);
            }
        }
    }

    public function down(): void
    {
        // Preserve participant-visible content on rollback.
    }
};
