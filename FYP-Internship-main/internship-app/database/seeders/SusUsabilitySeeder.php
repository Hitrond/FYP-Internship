<?php

namespace Database\Seeders;

use App\Models\Application;
use App\Models\EvaluationForm;
use App\Models\InternshipCycle;
use App\Models\InternshipCycleStudent;
use App\Models\Logbook;
use App\Models\PerformanceEvaluation;
use App\Models\PlacementClearance;
use App\Models\Profile;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

/**
 * Simulated records used only for SUS and role-based workflow evaluation.
 *
 * Safe to rerun: records have stable natural keys and cleanup is restricted to
 * the explicitly listed SUS accounts. Four existing evaluator accounts are
 * never deleted: Dhaya, Gobi, the system administrator, and James.
 */
class SusUsabilitySeeder extends Seeder
{
    private const PASSWORD = '123456789';

    private const PROTECTED_EMAILS = [
        'dhayanandahnaidu@gmail.com',
        'gobi@gmail.com',
        'admin@admin.com',
        'james@crs.com',
    ];

    private const CORE_USERS = [
        'admin@admin.com' => ['System Administrator', 'admin'],
        'dhayanandahnaidu@gmail.com' => ['Dhaya', 'student'],
        'james@crs.com' => ['James', 'mentor'],
        'gobi@gmail.com' => ['Gobi', 'supervisor'],
    ];

    private const OLD_DEMO_EMAILS = [
        'sarah.mentor@crs.com',
        'haris@gmail.com',
        'james.student03@example.test',
        'james.student04@example.test',
        'james.student05@example.test',
        'sarah.student01@example.test',
        'sarah.student02@example.test',
        'sarah.student03@example.test',
        'sarah.student04@example.test',
        'sarah.student05@example.test',
        'mastervirey@gmail.com',
    ];

    private const SUS_USERS = [
        'admin1@gmail.com' => ['Admin One', 'admin'],
        'admin2@gmail.com' => ['Admin Two', 'admin'],
        'admin3@gmail.com' => ['Admin Three', 'admin'],
        'lecturerapu1@gmail.com' => ['Dr. Salasiah Sulaiman', 'mentor'],
        'lecturerapu2@gmail.com' => ['Mr. Firdaus Rahman', 'mentor'],
        'lecturerapu3@gmail.com' => ['Ms. Kavitha Raman', 'mentor'],
        'adam@gmail.com' => ['Adam Lee', 'student'],
        'aisha@gmail.com' => ['Aisha Kumar', 'student'],
        'daniel@gmail.com' => ['Daniel Tan', 'student'],
        'supervisor1@gmail.com' => ['Mr. Adrian Lim', 'supervisor'],
        'supervisor2@gmail.com' => ['Ms. Priya Nair', 'supervisor'],
        'supervisor3@gmail.com' => ['Mr. Marcus Wong', 'supervisor'],
    ];

    public function run(): void
    {
        DB::transaction(function (): void {
            $this->removeOldDemoUsers();
            $users = $this->seedUsers();
            $cycles = $this->seedCycles();
            $this->seedProfiles($users);
            $this->seedApplications($users);
            $this->seedAssignments($users, $cycles['active']);
            $this->seedPendingPlacements($users, $cycles['active']);
            $form = $this->seedEvaluationForm($cycles['active']);
            $this->seedLogbooks($users, $cycles['active']);
            $this->seedEvaluations($users, $cycles['active'], $form);
        });
    }

    private function removeOldDemoUsers(): void
    {
        // Delete only the known obsolete demo identities. Never use a broad
        // "delete everyone except" query, which could erase a real user added
        // after this seeder was written.
        User::query()
            ->whereIn('email', self::OLD_DEMO_EMAILS)
            ->whereNotIn('email', self::PROTECTED_EMAILS)
            ->delete();
    }

    private function seedUsers(): array
    {
        $users = [];
        foreach (array_merge(self::CORE_USERS, self::SUS_USERS) as $email => [$name, $role]) {
            $users[$email] = User::updateOrCreate(
                ['email' => $email],
                ['name' => $name, 'role' => $role, 'password' => Hash::make(self::PASSWORD)]
            );
        }

        return $users;
    }

    private function seedCycles(): array
    {
        $today = Carbon::today();
        $active = InternshipCycle::updateOrCreate(['intake_code' => 'APU3F2511SE'], [
            'name' => 'APU 2026 Internship Semester', 'academic_year' => '2026',
            'placement_window_start' => $today->copy()->subWeeks(3),
            'placement_window_end' => $today->copy()->addWeeks(13), 'duration_weeks' => 16,
            'deadline_weekday' => 5, 'deadline_time' => '23:59:00',
            'timezone' => config('app.timezone', 'Asia/Singapore'), 'status' => 'active', 'activated_at' => now(),
        ]);
        $future = InternshipCycle::updateOrCreate(['intake_code' => 'APU3F2601SE'], [
            'name' => 'APU 2026 Future Internship Semester', 'academic_year' => '2026',
            'placement_window_start' => $today->copy()->addWeeks(8),
            'placement_window_end' => $today->copy()->addWeeks(24), 'duration_weeks' => 16,
            'deadline_weekday' => 5, 'deadline_time' => '23:59:00',
            'timezone' => config('app.timezone', 'Asia/Singapore'), 'status' => 'draft',
        ]);

        return compact('active', 'future');
    }

    private function seedProfiles(array $u): void
    {
        $students = [
            'adam@gmail.com' => ['TP070001', '0123456781', 'Laravel, PHP, PostgreSQL, Docker, HTML, CSS, JavaScript', 'Internship Management System Prototype; Student Attendance Tracker'],
            'aisha@gmail.com' => ['TP070002', '0123456782', 'Java, Spring Boot, MySQL, React, Git, REST API', 'Online Booking System; Clinic Appointment System'],
            'daniel@gmail.com' => ['TP070003', '0123456783', 'Python, Django, PostgreSQL, UI Design, Figma, JavaScript', 'Portfolio Website; Digital Logbook Prototype'],
        ];
        foreach ($students as $email => [$tp, $phone, $skills, $projects]) {
            Profile::updateOrCreate(['user_id' => $u[$email]->id], [
                'tp_number' => $tp, 'full_name' => $u[$email]->name,
                'course_name' => 'BSc (Hons) Software Engineering', 'specialization' => 'Year 3',
                'intake_code' => 'APU3F2511SE', 'personal_email' => $email,
                'contact_number' => $phone, 'phone_number' => $phone, 'internship_status' => 'Active',
                'bio' => "Final-year software engineering student. Skills: {$skills}.", 'projects_summary' => $projects,
            ]);
        }
        $supervisors = [
            'supervisor1@gmail.com' => ['DataCore Malaysia', 'Senior Software Engineer', '0133456781'],
            'supervisor2@gmail.com' => ['SecureSoft Sdn Bhd', 'QA Lead', '0133456782'],
            'supervisor3@gmail.com' => ['LogicPulse Sdn Bhd', 'Web Development Manager', '0133456783'],
        ];
        foreach ($supervisors as $email => [$company, $title, $phone]) {
            Profile::updateOrCreate(['user_id' => $u[$email]->id], [
                'full_name' => $u[$email]->name, 'company_name' => $company,
                'supervisor_job_title' => $title, 'supervisor_contact_number' => $phone,
                'phone_number' => $phone, 'company_email' => $email,
            ]);
        }
    }

    private function seedApplications(array $u): void
    {
        $sets = [
            'adam@gmail.com' => [['TechNova Solutions', 'Web Developer Intern'], ['BrightApps Sdn Bhd', 'Software Engineer Intern'], ['DataCore Malaysia', 'Backend Developer Intern'], ['CloudBridge Asia', 'IT Intern']],
            'aisha@gmail.com' => [['CodeWave Systems', 'Java Developer Intern'], ['FinTech Labs', 'Backend Developer Intern'], ['SecureSoft Sdn Bhd', 'Software Tester Intern'], ['AppWorks Malaysia', 'Junior Developer Intern']],
            'daniel@gmail.com' => [['PixelForge Studio', 'Frontend Developer Intern'], ['GreenCloud Tech', 'Full Stack Intern'], ['LogicPulse Sdn Bhd', 'Web Application Intern'], ['CyberNest Asia', 'Technical Support Intern']],
        ];
        $statuses = ['Applied', 'Interview', 'Offered', 'Rejected'];
        foreach ($sets as $email => $applications) {
            foreach ($applications as $i => [$company, $position]) {
                Application::updateOrCreate(['user_id' => $u[$email]->id, 'company_name' => $company], [
                    'position_title' => $position, 'location' => 'Kuala Lumpur', 'status' => $statuses[$i],
                    'applied_on' => today()->subDays(18 - $i * 3), 'notes' => 'Simulated SUS application record.',
                ]);
            }
        }
    }

    private function seedAssignments(array $u, InternshipCycle $cycle): void
    {
        $map = [
            'adam@gmail.com' => ['lecturerapu1@gmail.com', 'supervisor1@gmail.com'],
            'aisha@gmail.com' => ['lecturerapu2@gmail.com', 'supervisor2@gmail.com'],
            'daniel@gmail.com' => ['lecturerapu3@gmail.com', 'supervisor3@gmail.com'],
        ];
        foreach ($map as $studentEmail => [$mentorEmail, $supervisorEmail]) {
            $student = $u[$studentEmail];
            $student->update(['mentor_id' => $u[$mentorEmail]->id, 'supervisor_id' => $u[$supervisorEmail]->id]);
            InternshipCycleStudent::updateOrCreate(
                ['internship_cycle_id' => $cycle->id, 'student_id' => $student->id],
                ['mentor_id' => $u[$mentorEmail]->id, 'status' => 'enrolled', 'assigned_at' => now()->subWeeks(3)]
            );
        }
    }

    private function seedPendingPlacements(array $u, InternshipCycle $cycle): void
    {
        $placements = [
            'adam@gmail.com' => ['lecturerapu1@gmail.com', 'DataCore Malaysia', 'Mr. Adrian Lim', 'supervisor1@gmail.com'],
            'aisha@gmail.com' => ['lecturerapu2@gmail.com', 'SecureSoft Sdn Bhd', 'Ms. Priya Nair', 'supervisor2@gmail.com'],
            'daniel@gmail.com' => ['lecturerapu3@gmail.com', 'LogicPulse Sdn Bhd', 'Mr. Marcus Wong', 'supervisor3@gmail.com'],
        ];

        foreach ($placements as $studentEmail => [$mentorEmail, $company, $supervisorName, $supervisorEmail]) {
            $studentKey = strstr($studentEmail, '@', true);
            $documentPaths = [
                'job_offer_path' => "sus/placements/{$studentKey}-offer.pdf",
                'indemnity_path' => "sus/placements/{$studentKey}-indemnity.pdf",
                'placement_agreement_path' => "sus/placements/{$studentKey}-agreement.pdf",
            ];

            foreach ($documentPaths as $type => $path) {
                Storage::disk('local')->put(
                    $path,
                    $this->placeholderPdf($u[$studentEmail]->name, $company, str_replace('_path', '', $type))
                );
            }

            PlacementClearance::updateOrCreate(
                ['student_id' => $u[$studentEmail]->id, 'company_name' => $company],
                [
                    'internship_cycle_id' => $cycle->id,
                    'mentor_id' => $u[$mentorEmail]->id,
                    'supervisor_user_id' => null,
                    'office_address' => 'Kuala Lumpur, Malaysia',
                    'start_date' => today()->subWeeks(3),
                    'end_date' => today()->addWeeks(13),
                    'supervisor_name' => $supervisorName,
                    'supervisor_email' => $supervisorEmail,
                    'supervisor_personal_email' => $supervisorEmail,
                    ...$documentPaths,
                    'status' => 'pending',
                    'approved_at' => null,
                    'rejected_at' => null,
                    'rejection_reason' => null,
                ]
            );
        }
    }

    private function placeholderPdf(string $student, string $company, string $type): string
    {
        $label = strtoupper(str_replace('_', ' ', $type));
        $text = "SIMULATED SUS TEST DOCUMENT - {$label} - {$student} - {$company}";
        $stream = "BT /F1 16 Tf 50 750 Td ({$text}) Tj ET";
        $objects = [
            '<< /Type /Catalog /Pages 2 0 R >>',
            '<< /Type /Pages /Kids [3 0 R] /Count 1 >>',
            '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 612 792] /Resources << /Font << /F1 5 0 R >> >> /Contents 4 0 R >>',
            '<< /Length '.strlen($stream)." >>\nstream\n{$stream}\nendstream",
            '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>',
        ];
        $pdf = "%PDF-1.4\n";
        $offsets = [0];
        foreach ($objects as $i => $object) {
            $offsets[] = strlen($pdf);
            $pdf .= ($i + 1)." 0 obj\n{$object}\nendobj\n";
        }
        $xref = strlen($pdf);
        $pdf .= "xref\n0 6\n0000000000 65535 f \n";
        foreach (array_slice($offsets, 1) as $offset) {
            $pdf .= sprintf("%010d 00000 n \n", $offset);
        }

        return $pdf."trailer << /Size 6 /Root 1 0 R >>\nstartxref\n{$xref}\n%%EOF\n";
    }

    private function seedEvaluationForm(InternshipCycle $cycle): EvaluationForm
    {
        return EvaluationForm::updateOrCreate(
            ['internship_cycle_id' => $cycle->id, 'type' => 'final', 'version' => 'SUS-1'],
            ['title' => 'SUS Supervisor Performance Evaluation', 'criteria' => [
                'attendance' => 'Attendance and Punctuality', 'communication' => 'Communication Skills',
                'technical_performance' => 'Technical Performance', 'problem_solving' => 'Problem-Solving Ability',
                'professional_attitude' => 'Professional Attitude',
            ], 'instructions' => 'Simulated evaluation form for usability testing only.', 'is_active' => true]
        );
    }

    private function seedLogbooks(array $u, InternshipCycle $cycle): void
    {
        $rows = [
            'adam@gmail.com' => [
                1 => ['approved', 'Developed authentication pages and tested login workflow', 'Good progress. Authentication flow was completed clearly.', 40],
                2 => ['overdue_locked', null, null, 0],
                3 => ['pending', 'Created student dashboard and company application tracker interface', null, 38],
                4 => ['open', null, null, 0],
            ],
            'aisha@gmail.com' => [
                1 => ['rejected', 'Prepared API endpoints for application tracker', 'Please provide clearer evidence and include API testing screenshots.', 35],
                2 => ['approved', 'Updated API validation and fixed database relationship issue', 'Good improvement after correction.', 40],
                3 => ['overdue_locked', null, null, 0],
                4 => ['open', null, null, 0],
            ],
            'daniel@gmail.com' => [
                1 => ['approved', 'Designed wireframes for logbook and supervisor approval pages', 'Good design work and clear interface flow.', 40],
                2 => ['pending', 'Implemented logbook submission form and evidence upload placeholder', null, 39],
                3 => ['approved', 'Implemented supervisor dashboard table and pending approval list', 'Clear progress and dashboard is easy to review.', 40],
                4 => ['open', null, null, 0],
            ],
        ];
        $supervisorEmails = ['adam@gmail.com' => 'supervisor1@gmail.com', 'aisha@gmail.com' => 'supervisor2@gmail.com', 'daniel@gmail.com' => 'supervisor3@gmail.com'];
        foreach ($rows as $email => $weeks) {
            foreach ($weeks as $week => [$status, $description, $feedback, $hours]) {
                $due = match ($week) {
                    1 => now()->subDays(21), 2 => now()->subDays(14), 3 => now()->subDays(7), default => now()->addDays(7)
                };
                $reviewed = in_array($status, ['approved', 'rejected'], true);
                $startDate = $due->copy()->subDays(6);
                $renderedMinutes = $hours * 60;
                Logbook::updateOrCreate(['user_id' => $u[$email]->id, 'week_number' => $week], [
                    'internship_cycle_id' => $cycle->id, 'timeline_generated' => true,
                    'start_date' => $startDate->toDateString(), 'end_date' => $due->toDateString(),
                    'submission_due_at' => $due, 'locked_at' => $status === 'overdue_locked' ? $due : null,
                    'description' => $description, 'rendered_minutes' => $renderedMinutes,
                    'attendance_entries' => $description ? $this->attendanceEntries($startDate, $renderedMinutes) : null,
                    'status' => $status, 'supervisor_remarks' => $feedback,
                    'approved_by_id' => $reviewed ? $u[$supervisorEmails[$email]]->id : null,
                    'approved_at' => $reviewed ? $due->copy()->addDay() : null,
                    'evidence_file_path' => $description ? 'evidence/'.strstr($email, '@', true)."/week{$week}.pdf" : null,
                ]);
            }
        }
    }

    private function attendanceEntries(Carbon $startDate, int $totalMinutes): array
    {
        $minutesPerDay = intdiv($totalMinutes, 5);
        $remainder = $totalMinutes % 5;

        return collect(range(0, 4))->map(fn (int $day): array => [
            'date' => $startDate->copy()->addDays($day)->toDateString(),
            'status' => 'present',
            'rendered_minutes' => $minutesPerDay + ($day < $remainder ? 1 : 0),
            'note' => null,
            'mc_evidence_path' => null,
        ])->all();
    }

    private function seedEvaluations(array $u, InternshipCycle $cycle, EvaluationForm $form): void
    {
        $items = [
            'adam@gmail.com' => ['supervisor1@gmail.com', [5, 4, 4, 4, 5], 'Adam showed strong commitment and completed assigned tasks responsibly.'],
            'aisha@gmail.com' => ['supervisor2@gmail.com', [4, 4, 3, 4, 4], 'Aisha demonstrated improvement after feedback and completed tasks with guidance.'],
            'daniel@gmail.com' => ['supervisor3@gmail.com', [5, 5, 5, 4, 5], 'Daniel performed well and showed good understanding of the system workflow.'],
        ];
        $keys = ['attendance', 'communication', 'technical_performance', 'problem_solving', 'professional_attitude'];
        foreach ($items as $email => [$supervisor, $scores, $comments]) {
            $ratings = [];
            foreach ($keys as $i => $key) {
                $ratings[$key] = ['rating' => match ($scores[$i]) {
                    5 => 'A', 4 => 'B', 3 => 'C', default => 'D'
                }, 'comment' => null];
            }
            PerformanceEvaluation::updateOrCreate(['student_id' => $u[$email]->id, 'type' => 'final'], [
                'internship_cycle_id' => $cycle->id, 'evaluation_form_id' => $form->id,
                'supervisor_id' => $u[$supervisor]->id, 'ratings' => $ratings,
                'overall_grade' => (int) round(array_sum($scores) / count($scores)),
                'overall_comments' => $comments, 'status' => 'submitted', 'submitted_at' => now()->subDays(3),
            ]);
        }
    }
}
