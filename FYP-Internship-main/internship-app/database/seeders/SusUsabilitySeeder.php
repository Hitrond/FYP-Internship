<?php

namespace Database\Seeders;

use App\Models\Application;
use App\Models\Education;
use App\Models\EvaluationForm;
use App\Models\FinalClearance;
use App\Models\InternshipCycle;
use App\Models\InternshipCycleStudent;
use App\Models\Logbook;
use App\Models\PerformanceEvaluation;
use App\Models\PlacementClearance;
use App\Models\Profile;
use App\Models\Skill;
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
        'dhayanandahnaidu@gmail.com' => ['Dhaya', 'supervisor'],
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
        'nadia.presentation@example.test' => ['Nadia Rahman', 'student'],
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
            $this->seedStudentResumeData($users);
            $this->seedApplications($users);
            $this->seedAssignments($users, $cycles['active']);
            $this->seedPendingPlacements($users, $cycles['active']);
            $this->seedPresentationStages($users, $cycles['active']);
            $evaluationForm = $this->seedEvaluationForm($cycles['active']);
            $this->seedLogbooks($users, $cycles['active']);
            $this->seedEvaluationStages($users, $cycles['active'], $evaluationForm);
            $this->seedDanielFinalClearance($users, $cycles['active']);
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
            'placement_window_start' => $today->copy()->subWeeks(16),
            'placement_window_end' => $today->copy()->addWeeks(16), 'duration_weeks' => 16,
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
            'adam@gmail.com' => ['TP070001', '0123456781', 'Laravel, PHP, PostgreSQL, Docker, HTML, CSS, JavaScript', "Internship Management System Prototype: Built placement and logbook workflows with Laravel.\nStudent Attendance Tracker: Created attendance reporting with PostgreSQL."],
            'aisha@gmail.com' => ['TP070002', '0123456782', 'Java, Spring Boot, MySQL, React, Git, REST API', "Online Booking System: Developed a Spring Boot booking workflow.\nClinic Appointment System: Built appointment management with React and MySQL."],
            'daniel@gmail.com' => ['TP070003', '0123456783', 'Python, Django, PostgreSQL, UI Design, Figma, JavaScript', "Portfolio Website: Designed and developed a responsive personal portfolio.\nDigital Logbook Prototype: Implemented weekly progress tracking with Django."],
            'nadia.presentation@example.test' => ['TP070004', '0123456784', 'PHP, Laravel, PostgreSQL, HTML, CSS, JavaScript', "Internship Portal: Implemented role-based internship workflows with Laravel.\nWeekly Progress Tracker: Built submission and review features for weekly reports."],
        ];
        foreach ($students as $email => [$tp, $phone, $skills, $projects]) {
            Profile::updateOrCreate(['user_id' => $u[$email]->id], [
                'tp_number' => $tp, 'full_name' => $u[$email]->name,
                'course_name' => 'BSc (Hons) Software Engineering', 'specialization' => 'Year 3',
                'intake_code' => 'APU3F2511SE', 'personal_email' => $email,
                'contact_number' => $phone, 'phone_number' => $phone, 'internship_status' => 'Active',
                'bio' => "Final-year software engineering student with practical project experience in {$skills}.",
                'linkedin_url' => 'https://www.linkedin.com/in/'.str_replace(['@gmail.com', '.presentation@example.test'], '', $email),
                'github_url' => 'https://github.com/'.str_replace(['@gmail.com', '.presentation@example.test'], '', $email),
                'portfolio_url' => 'https://portfolio.example.com/'.str_replace(['@gmail.com', '.presentation@example.test'], '', $email),
                'projects_summary' => $projects,
                'languages_summary' => "English (Fluent)\nMalay (Conversational)",
                'references_summary' => 'Available upon request.',
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

    private function seedStudentResumeData(array $u): void
    {
        $studentSkills = [
            'adam@gmail.com' => ['Laravel', 'PHP', 'PostgreSQL', 'Docker', 'HTML', 'CSS', 'JavaScript'],
            'aisha@gmail.com' => ['Java', 'Spring Boot', 'MySQL', 'React', 'Git', 'REST API'],
            'daniel@gmail.com' => ['Python', 'Django', 'PostgreSQL', 'UI Design', 'Figma', 'JavaScript'],
            'nadia.presentation@example.test' => ['PHP', 'Laravel', 'PostgreSQL', 'HTML', 'CSS', 'JavaScript'],
        ];

        foreach ($studentSkills as $email => $skills) {
            Education::updateOrCreate(
                [
                    'user_id' => $u[$email]->id,
                    'institution_name' => 'Asia Pacific University of Technology & Innovation',
                ],
                [
                    'degree' => 'Bachelor of Science (Hons)',
                    'field_of_study' => 'Software Engineering',
                    'start_date' => '2023-01-01',
                    'end_date' => '2026-12-31',
                    'description' => "Final Year Project: Internship Management System.\nRelevant coursework: Web Development, Software Testing, and Database Design.",
                ]
            );

            foreach ($skills as $index => $skill) {
                Skill::updateOrCreate(
                    ['user_id' => $u[$email]->id, 'name' => $skill],
                    ['proficiency' => $index < 2 ? 'Advanced' : 'Intermediate']
                );
            }
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
            'adam@gmail.com' => ['lecturerapu1@gmail.com', null],
            'aisha@gmail.com' => ['lecturerapu2@gmail.com', 'supervisor2@gmail.com'],
            'daniel@gmail.com' => ['lecturerapu3@gmail.com', 'supervisor3@gmail.com'],
        ];
        foreach ($map as $studentEmail => [$mentorEmail, $supervisorEmail]) {
            $student = $u[$studentEmail];
            $student->update([
                'mentor_id' => $u[$mentorEmail]->id,
                'supervisor_id' => $supervisorEmail ? $u[$supervisorEmail]->id : null,
            ]);
            InternshipCycleStudent::updateOrCreate(
                ['internship_cycle_id' => $cycle->id, 'student_id' => $student->id],
                ['mentor_id' => $u[$mentorEmail]->id, 'status' => 'enrolled', 'assigned_at' => now()->subWeeks(3)]
            );
        }

        $presentationStudent = $u['nadia.presentation@example.test'];
        $presentationMentor = $u['lecturerapu1@gmail.com'];
        $presentationStudent->update([
            'mentor_id' => $presentationMentor->id,
            'supervisor_id' => null,
        ]);
        InternshipCycleStudent::updateOrCreate(
            ['internship_cycle_id' => $cycle->id, 'student_id' => $presentationStudent->id],
            ['mentor_id' => $presentationMentor->id, 'status' => 'enrolled', 'assigned_at' => now()->subWeeks(3)]
        );
    }

    private function seedPendingPlacements(array $u, InternshipCycle $cycle): void
    {
        $placements = [
            'adam@gmail.com' => ['lecturerapu1@gmail.com', 'DataCore Malaysia', 'Mr. Adrian Lim', 'supervisor1@gmail.com', today()->addWeek()->startOfWeek(), today()->addWeek()->startOfWeek()->addWeeks(15)->addDays(4)],
            'aisha@gmail.com' => ['lecturerapu2@gmail.com', 'SecureSoft Sdn Bhd', 'Ms. Priya Nair', 'supervisor2@gmail.com', today()->subWeeks(3)->startOfWeek(), today()->subWeeks(3)->startOfWeek()->addWeeks(15)->addDays(4)],
            'daniel@gmail.com' => ['lecturerapu3@gmail.com', 'LogicPulse Sdn Bhd', 'Mr. Marcus Wong', 'supervisor3@gmail.com', today()->subWeeks(16)->startOfWeek(), today()->subWeeks(16)->startOfWeek()->addWeeks(15)->addDays(4)],
            'nadia.presentation@example.test' => ['lecturerapu1@gmail.com', 'InnovateLab Sdn Bhd', 'Master Virey', 'mastervirey@gmail.com', today()->startOfWeek(), today()->startOfWeek()->addWeeks(15)->addDays(4)],
        ];

        foreach ($placements as $studentEmail => [$mentorEmail, $company, $supervisorName, $supervisorEmail, $startDate, $endDate]) {
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
                    'start_date' => $startDate,
                    'end_date' => $endDate,
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

    private function seedPresentationStages(array $u, InternshipCycle $cycle): void
    {
        $adam = $u['adam@gmail.com'];
        $aisha = $u['aisha@gmail.com'];
        $daniel = $u['daniel@gmail.com'];
        $nadia = $u['nadia.presentation@example.test'];

        $adam->update(['supervisor_id' => null]);
        $this->placementFor($adam, $cycle)->update([
            'supervisor_user_id' => null,
            'status' => 'pending',
            'approved_at' => null,
            'rejected_at' => null,
            'rejection_reason' => null,
        ]);

        $aishaSupervisor = $u['supervisor2@gmail.com'];
        $aisha->update(['supervisor_id' => $aishaSupervisor->id]);
        $this->placementFor($aisha, $cycle)->update([
            'supervisor_user_id' => $aishaSupervisor->id,
            'status' => 'completed',
            'approved_at' => now()->subWeeks(3),
            'rejected_at' => null,
            'rejection_reason' => null,
        ]);

        $danielSupervisor = $u['supervisor3@gmail.com'];
        $daniel->update(['supervisor_id' => $danielSupervisor->id]);
        $this->placementFor($daniel, $cycle)->update([
            'supervisor_user_id' => $danielSupervisor->id,
            'status' => 'completed',
            'approved_at' => now()->subWeeks(16),
            'rejected_at' => null,
            'rejection_reason' => null,
        ]);

        // This approved placement deliberately remains unlinked so Admin One
        // can demonstrate "Create supervisor login" and send credentials to
        // the real presentation inbox. An existing supervisor account may be
        // safely reused; the provisioning action resets and emails a password.
        $nadia->update(['supervisor_id' => null]);
        $this->placementFor($nadia, $cycle)->update([
            'mentor_id' => $u['lecturerapu1@gmail.com']->id,
            'supervisor_user_id' => null,
            'supervisor_name' => 'Master Virey',
            'supervisor_email' => 'mastervirey@gmail.com',
            'supervisor_personal_email' => 'mastervirey@gmail.com',
            'status' => 'approved',
            'approved_at' => now()->subDay(),
            'rejected_at' => null,
            'rejection_reason' => null,
        ]);

        FinalClearance::whereIn('student_id', [
            $adam->id,
            $aisha->id,
            $daniel->id,
            $nadia->id,
        ])->delete();
    }

    private function placementFor(User $student, InternshipCycle $cycle): PlacementClearance
    {
        return PlacementClearance::where('student_id', $student->id)
            ->where('internship_cycle_id', $cycle->id)
            ->firstOrFail();
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
        $students = collect([
            $u['adam@gmail.com'],
            $u['aisha@gmail.com'],
            $u['daniel@gmail.com'],
            $u['nadia.presentation@example.test'],
        ]);

        Logbook::whereIn('user_id', $students->pluck('id'))->delete();

        // Adam deliberately has no weekly records. His placement is still
        // waiting for Academic Mentor approval, which is what generates them.
        $this->seedAishaTimeline($u, $cycle);
        $this->seedDanielTimeline($u, $cycle);
        $this->seedSupervisorLoginTimeline($u, $cycle);
    }

    private function seedAishaTimeline(array $u, InternshipCycle $cycle): void
    {
        $student = $u['aisha@gmail.com'];
        $supervisor = $u['supervisor2@gmail.com'];
        $placement = $this->placementFor($student, $cycle);
        $firstMonday = $placement->start_date->copy()->startOfWeek(Carbon::MONDAY);
        $submitted = [
            1 => [
                'approved',
                'Implemented secure authentication and role-based dashboard navigation.',
                'Built Laravel validation, middleware, feature tests, and PostgreSQL user-role queries.',
                'Approved. The entry clearly explains the objectives, technical work, attendance, and evidence.',
            ],
            2 => [
                'rejected',
                'Prepared REST endpoints for the internship application tracker.',
                'Created controllers, request validation, Eloquent relationships, and API test cases.',
                'Please add clearer testing evidence and explain how failed validation cases were handled.',
            ],
            3 => [
                'pending',
                'Corrected the application tracker after supervisor feedback.',
                'Added validation screenshots, improved error handling, and reran the Laravel feature tests.',
                null,
            ],
        ];

        foreach (range(1, $cycle->duration_weeks) as $week) {
            $startDate = $firstMonday->copy()->addWeeks($week - 1);
            $endDate = $startDate->copy()->addDays(4);
            $dueAt = $startDate->copy()->addDays(11)->endOfDay();
            [$status, $objective, $content, $remarks] = $submitted[$week]
                ?? [$week === 4 ? 'open' : 'scheduled', null, null, null];
            $description = $objective
                ? "=== Type(s) & Objective(s) ===\n{$objective}\n\n=== Content & Skills ===\n{$content}"
                : null;
            $evidencePath = $description ? "evidence/aisha/week{$week}.pdf" : null;

            if ($evidencePath) {
                Storage::disk('local')->put(
                    $evidencePath,
                    $this->placeholderPdf($student->name, $placement->company_name, "Week {$week} evidence")
                );
            }

            Logbook::create([
                'user_id' => $student->id,
                'internship_cycle_id' => $cycle->id,
                'week_number' => $week,
                'timeline_generated' => true,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'submission_due_at' => $dueAt,
                'description' => $description,
                'attendance_entries' => $description ? $this->attendanceEntries($startDate, 2400) : null,
                'rendered_minutes' => $description ? 2400 : 0,
                'verified_minutes' => $status === 'approved' ? 2340 : null,
                'attendance_remarks' => $status === 'approved' ? 'Thirty minutes excluded after attendance verification.' : null,
                'status' => $status,
                'supervisor_remarks' => $remarks,
                'rejection_category' => $status === 'rejected' ? 'content' : null,
                'approved_by_id' => $status === 'approved' ? $supervisor->id : null,
                'approved_at' => $status === 'approved' ? $dueAt->copy()->addDay() : null,
                'evidence_file_path' => $evidencePath,
            ]);
        }
    }

    private function seedDanielTimeline(array $u, InternshipCycle $cycle): void
    {
        $student = $u['daniel@gmail.com'];
        $supervisor = $u['supervisor3@gmail.com'];
        $placement = $this->placementFor($student, $cycle);
        $firstMonday = $placement->start_date->copy()->startOfWeek(Carbon::MONDAY);

        foreach (range(1, $cycle->duration_weeks) as $week) {
            $startDate = $firstMonday->copy()->addWeeks($week - 1);
            $endDate = $startDate->copy()->addDays(4);
            $evidencePath = "evidence/daniel/week{$week}.pdf";
            Storage::disk('local')->put(
                $evidencePath,
                $this->placeholderPdf($student->name, $placement->company_name, "Week {$week} evidence")
            );

            Logbook::create([
                'user_id' => $student->id,
                'internship_cycle_id' => $cycle->id,
                'week_number' => $week,
                'timeline_generated' => true,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'submission_due_at' => $startDate->copy()->addDays(11)->endOfDay(),
                'description' => "=== Type(s) & Objective(s) ===\nCompleted the assigned internship objectives for Week {$week}.\n\n=== Content & Skills ===\nDocumented development work, testing results, professional communication, and lessons learned for supervisor review.",
                'attendance_entries' => $this->attendanceEntries($startDate, 2400),
                'rendered_minutes' => 2400,
                'verified_minutes' => 2400,
                'attendance_remarks' => 'Attendance and rendered hours verified against the weekly record.',
                'status' => 'approved',
                'supervisor_remarks' => 'Approved. The objectives, activities, evidence, and attendance are complete.',
                'approved_by_id' => $supervisor->id,
                'approved_at' => $endDate->copy()->addDay()->setTime(10, 0),
                'evidence_file_path' => $evidencePath,
            ]);
        }
    }

    private function seedSupervisorLoginTimeline(array $u, InternshipCycle $cycle): void
    {
        $student = $u['nadia.presentation@example.test'];
        $placement = $this->placementFor($student, $cycle);
        $firstMonday = $placement->start_date->copy()->startOfWeek(Carbon::MONDAY);

        foreach (range(1, $cycle->duration_weeks) as $week) {
            $startDate = $firstMonday->copy()->addWeeks($week - 1);
            Logbook::create([
                'user_id' => $student->id,
                'internship_cycle_id' => $cycle->id,
                'week_number' => $week,
                'timeline_generated' => true,
                'start_date' => $startDate,
                'end_date' => $startDate->copy()->addDays(4),
                'submission_due_at' => $startDate->copy()->addDays(11)->endOfDay(),
                'rendered_minutes' => 0,
                'status' => $week === 1 ? 'open' : 'scheduled',
            ]);
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

    private function seedEvaluationStages(array $u, InternshipCycle $cycle, EvaluationForm $evaluationForm): void
    {
        PerformanceEvaluation::whereIn('student_id', [
            $u['adam@gmail.com']->id,
            $u['aisha@gmail.com']->id,
            $u['daniel@gmail.com']->id,
            $u['nadia.presentation@example.test']->id,
        ])->delete();

        $criteria = collect($evaluationForm->criteria)->mapWithKeys(
            fn (string $label, string $key): array => [$key => [
                'rating' => 'A',
                'comment' => "Daniel consistently demonstrated strong {$label} throughout the internship.",
            ]]
        )->all();

        PerformanceEvaluation::create([
            'student_id' => $u['daniel@gmail.com']->id,
            'internship_cycle_id' => $cycle->id,
            'evaluation_form_id' => $evaluationForm->id,
            'supervisor_id' => $u['supervisor3@gmail.com']->id,
            'type' => PerformanceEvaluation::TYPE_FINAL,
            'ratings' => $criteria,
            'overall_grade' => 9,
            'overall_comments' => 'Daniel completed the internship successfully and demonstrated reliable technical, communication, and professional skills.',
            'status' => PerformanceEvaluation::STATUS_SUBMITTED,
            'submitted_at' => now()->subDays(2),
        ]);
    }

    private function seedDanielFinalClearance(array $u, InternshipCycle $cycle): void
    {
        $student = $u['daniel@gmail.com'];
        $placement = $this->placementFor($student, $cycle);
        $reportPath = 'sus/final-clearances/daniel-internship-report.pdf';
        $clearanceFormPath = 'sus/final-clearances/daniel-report-clearance-form.pdf';

        Storage::disk('local')->put(
            $reportPath,
            $this->placeholderPdf($student->name, $placement->company_name, 'final internship report')
        );
        Storage::disk('local')->put(
            $clearanceFormPath,
            $this->placeholderPdf($student->name, $placement->company_name, 'signed report clearance form')
        );

        $clearance = FinalClearance::updateOrCreate(
            ['student_id' => $student->id],
            [
                'internship_cycle_id' => $cycle->id,
                'placement_clearance_id' => $placement->id,
                'mentor_id' => $u['lecturerapu3@gmail.com']->id,
                'supervisor_id' => $u['supervisor3@gmail.com']->id,
                'report_path' => $reportPath,
                'report_original_name' => 'Daniel-Tan-Internship-Report.pdf',
                'report_clearance_form_path' => $clearanceFormPath,
                'report_clearance_form_original_name' => 'Daniel-Tan-Report-Clearance-Form.pdf',
                'status' => FinalClearance::STATUS_PENDING,
                'mentor_status' => FinalClearance::STATUS_PENDING,
                'mentor_feedback' => null,
                'mentor_signed_at' => null,
                'industrial_hours_completed' => false,
                'company_property_cleared' => false,
                'supervisor_status' => FinalClearance::STATUS_PENDING,
                'supervisor_feedback' => null,
                'supervisor_signed_at' => null,
                'completed_at' => null,
            ]
        );

        $clearance->events()->delete();
        $clearance->events()->create([
            'actor_id' => $student->id,
            'action' => 'submitted',
            'actor_role' => 'student',
        ]);
    }
}
