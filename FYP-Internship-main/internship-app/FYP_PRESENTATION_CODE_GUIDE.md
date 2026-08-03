# InternTrack FYP Presentation and Code Walkthrough Guide

This guide is based on the code currently in this repository. Use it as a speaking guide, not as a script to memorize word-for-word.

## 1. The 30-second project explanation

> InternTrack is a role-based web application that manages the complete internship lifecycle. Students build application documents, track companies, submit placement clearance, complete weekly logbooks, and submit final clearance. Academic mentors approve placements and extensions, industrial supervisors verify logbooks and evaluations, and administrators manage users, semesters, forms, and cohort progress. The application is a Laravel MVC monolith with PostgreSQL, Blade, Tailwind CSS, and Alpine.js, deployed through Docker. I selected this stack because it provides secure authentication, database relationships, validation, file storage, scheduling, queues, and maintainable server-rendered interfaces without the complexity of a separate frontend API.

The strongest architectural point is that access control and business rules are enforced on the server. Hiding a button in the interface is not considered security.

### Presentation login accounts

All four presentation accounts use the password `123456789`.

| Role | Email | Recommended demonstration |
|---|---|---|
| Administrator | `admin1@gmail.com` | User, semester, clearance, form, and reporting administration |
| Student - placement stage | `adam@gmail.com` | Complete ATS resume and a placement form waiting for Academic Mentor approval; no logbooks exist yet |
| Student - mixed logbook stage | `aisha@gmail.com` | All 16 timeline weeks, including approved, rejected, pending, open, and scheduled examples with evidence and attendance |
| Student - supervisor queue | `daniel@gmail.com` | All 16 weekly entries completed and waiting only for Industrial Supervisor approval |
| Academic mentor for Adam | `lecturerapu1@gmail.com` | Adam's placement approval and the live supervisor-login placement |
| Academic mentor for Daniel | `lecturerapu3@gmail.com` | Daniel's complete 16-week submission timeline |
| Industrial supervisor for Aisha | `supervisor2@gmail.com` | Approved logbook with verified hours, e-signature, and company stamp |
| Industrial supervisor for Daniel | `supervisor3@gmail.com` | Sixteen pending logbooks in the approval queue |

Adam's seeded profile includes contact details, summary, links, two projects, education, seven skills, languages, and references. It passes the application’s resume-readiness check and can immediately download the ATS PDF and DOCX.

## 2. Architecture at a glance

```text
Browser
  |
  | HTTP request / form submission
  v
routes/web.php
  |
  | auth + role middleware
  v
Controller
  |-- validates input
  |-- checks record ownership/assignment
  |-- calls a domain service when logic is reusable
  |-- reads/writes Eloquent models
  v
PostgreSQL + private/local file storage
  |
  v
Blade view -> Tailwind CSS + Alpine.js -> HTML response

Background path:
Laravel Scheduler -> queued notification -> database notification + Brevo/email + optional Pusher live alert
```

This is an MVC monolith:

- **Model:** represents data and relationships, for example `User`, `Logbook`, and `FinalClearance`.
- **View:** Blade templates under `resources/views` render the HTML.
- **Controller:** handles an HTTP use case, validates input, authorizes it, changes data, and chooses the response.
- **Service:** contains reusable domain or integration logic, such as timeline generation, document readiness, DOCX generation, and Brevo email delivery.
- **Middleware:** rejects users who do not have the required role before a controller runs.

## 3. Repository structure you should know

| Path | Purpose | What to say |
|---|---|---|
| `routes/web.php` | Maps URLs and HTTP methods to controllers; groups routes by role | “This is the entry map of the web application.” |
| `routes/auth.php` | Login, logout, password reset, and email-verification routes | “Authentication routes are separated from business routes.” |
| `routes/console.php` | Scheduled and manual Artisan commands | “The hourly job keeps logbook deadlines accurate without user action.” |
| `app/Http/Middleware` | Role-based access control | “Middleware provides coarse-grained route protection.” |
| `app/Http/Controllers` | Request/use-case orchestration | “Controllers connect HTTP input to the domain and response.” |
| `app/Models` | Eloquent entities and relationships | “Relationships replace hand-written joins for most use cases.” |
| `app/Services` | Reusable business/integration logic | “Complex logic is extracted so controllers do not duplicate it.” |
| `app/Notifications` | In-app and email workflow alerts | “One notification can be delivered through multiple channels.” |
| `database/migrations` | Version-controlled database schema | “Every schema change is reproducible and reviewable.” |
| `resources/views` | Server-rendered Blade UI | “The interface is rendered on the server and progressively enhanced.” |
| `resources/js` | Alpine UI bootstrap and Echo/Pusher live-notification listener | “JavaScript is intentionally light.” |
| `resources/css/app.css` | Tailwind entry point and responsive safeguards | “Most styling uses utility classes with a small shared CSS layer.” |
| `tests/Feature` | End-to-end application behavior at HTTP/database level | “Feature tests verify permissions and business workflows.” |
| `Dockerfile`, `compose.yaml` | Reproducible PHP, Node, and PostgreSQL environment | “Docker reduces differences between developer and deployment environments.” |

Current scale of the codebase: 134 declared web/auth routes, 36 controllers, 16 models, 43 migrations, 116 feature-test methods, and one unit-test method.

## 4. Technology stack and why it fits

Versions below are the versions locked in this repository, not just the version ranges in the manifest.

| Technology/library | Version | What it does here | Why it was a reasonable choice | Trade-off |
|---|---:|---|---|---|
| PHP | `^8.3`; Docker uses PHP 8.4 | Backend language | Mature web ecosystem, strong Laravel support, typed modern syntax | Request-based runtime and dynamic language errors require good tests/static analysis |
| Laravel | 13.23.0 | MVC, routing, authentication, validation, ORM, storage, queues, notifications, scheduling | Provides most cross-cutting web concerns consistently, reducing custom security code | Framework conventions create coupling to Laravel and a learning curve |
| PostgreSQL | 15 in Compose | Relational persistent data | Suits highly related data and transactional workflows such as student–mentor–supervisor approvals | Requires schema migrations and database administration; horizontal scaling is not automatic |
| Blade | Laravel built-in | Server-rendered views | Simple integration with authorization, validation errors, and forms; no separate API required | Full-page requests are less interactive than a large SPA |
| Tailwind CSS | 3.4.19 | Responsive visual styling | Fast, consistent design using utilities directly in Blade templates | Long class lists can reduce template readability |
| Alpine.js | 3.15.12 | Modals, menus, password visibility, small UI states | Gives lightweight interactivity without a React/Vue application | Not intended for large client-side state or complex offline behavior |
| Vite | 8.2.0 | Bundles CSS and JavaScript | Fast development/build process and official Laravel integration | Node tooling is still required even though the app is PHP-based |
| Eloquent ORM | Laravel built-in | Models, relationships, queries | Expressive relationship code and protection against raw-SQL injection when used correctly | Poorly designed relationship loading can cause N+1 queries |
| Laravel Breeze | 2.4.1, development dependency | Authentication scaffold | A small, auditable starting point for session authentication | Scaffolded code still has to be maintained and customized |
| mPDF | 8.3.1 | Generates PDF resumes and cover letters | Converts controlled HTML templates to downloadable PDF files | Complex CSS/layout may render differently and PDF generation consumes memory |
| PHPWord | 1.4.0 | Generates editable DOCX files | Provides native editable documents rather than fake HTML downloads | PDF and DOCX templates need separate layout implementations |
| Brevo API | Direct Laravel HTTP integration | Transactional emails and workflow alerts | HTTPS API delivery works in hosted environments where SMTP can be restricted | External-service availability, API limits, and credentials are dependencies |
| Laravel Notifications/Queue | Laravel built-in | In-app database alerts plus queued email | Decouples a workflow change from slow email delivery | A queue worker must continuously run; failed jobs require monitoring |
| Pusher Channels + Laravel Echo | Pusher PHP 7.2.7; Echo 2.4.0; Pusher JS 8.6.0 | Pushes a logged-in user's workflow notification to the browser, updates the bell count, and shows a toast without refreshing | Managed real-time delivery avoids operating a WebSocket server and Laravel provides private-channel authorization | Requires a Pusher account, credentials, network access, a running queue worker, and service availability/quotas |
| Laravel Scheduler | Laravel built-in | Hourly logbook status synchronization | Centralizes time-driven rules and prevents overlapping executions | Deadlines are only as accurate as the scheduler/worker and configured timezone |
| Docker | Multi-stage build | Reproducible app, assets, PHP extensions, and PostgreSQL | Makes setup and deployment repeatable | More memory/disk usage and added container operational knowledge |
| PHPUnit | 12.5.24 | Automated feature and unit tests | Protects permissions and multi-role workflows from regressions | Tests need maintenance and do not replace usability/security testing |

### Removed unused dependencies

The unused Socialite/Azure integration, Axios bootstrap, and Tailwind 4 Vite plugin were removed. The application keeps standard Laravel session authentication, form submissions, Tailwind 3 through PostCSS, and the active Pusher/Echo notification integration. This reduces dependency maintenance and attack surface without removing an active feature.

## 5. Main end-to-end business process

```text
Admin creates/activates semester and assigns student + academic mentor
  -> Student tracks company and accepts an offer
  -> Student uploads placement documents and official dates
  -> Academic mentor approves or rejects placement
  -> Approval generates N weekly logbook blocks
  -> Admin provisions industrial-supervisor account and emails credentials
  -> Student submits each open weekly logbook
  -> Industrial supervisor approves/signs or rejects it
  -> Supervisor submits midterm/final evaluation
  -> Student submits final report and clearance form
  -> Mentor and supervisor independently approve
  -> System marks final clearance completed only after both approvals
  -> Academic mentor records the final result
```

The important design pattern is a server-controlled state machine. Examples of states include placement `pending/approved/rejected/completed`, logbook `scheduled/open/overdue_locked/pending/approved/rejected`, and final clearance reviewer states.

## 6. Exact code walkthroughs

### A. Login, session security, and role redirection

Start at `routes/auth.php`, lines 11–18: the GET route shows the login form and the POST route processes it.

Then show `app/Http/Requests/Auth/LoginRequest.php`, lines 41–54:

```php
public function authenticate(): void
{
    $this->ensureIsNotRateLimited();

    if (! Auth::attempt($this->only('email', 'password'), $this->boolean('remember'))) {
        RateLimiter::hit($this->throttleKey());
        throw ValidationException::withMessages(['email' => trans('auth.failed')]);
    }

    RateLimiter::clear($this->throttleKey());
}
```

What to say:

1. Laravel validates the email and password before authentication.
2. `ensureIsNotRateLimited()` limits repeated guesses to five attempts for the email/IP key.
3. `Auth::attempt()` checks the submitted password against the stored hash; the application never decrypts a password.
4. Failed login returns a controlled validation error.

Next show `app/Http/Controllers/Auth/AuthenticatedSessionController.php`, lines 25–39:

```php
$request->authenticate();
$request->session()->regenerate();

if ($request->user()->isAdmin()) {
    return redirect()->intended(route('admin.dashboard', absolute: false));
}
if ($request->user()->isSupervisor()) {
    return redirect()->intended(route('supervisor.dashboard', absolute: false));
}
return redirect()->intended(route('dashboard', absolute: false));
```

What to say: “After successful authentication I regenerate the session identifier to prevent session fixation, then redirect users based on their role. The generic dashboard performs a second role dispatch for mentors and students.”

### B. Role-based access control

Show `bootstrap/app.php`, lines 31–36, where aliases such as `student` and `mentor` are registered. Then show `routes/web.php`, lines 49–120, where student routes are inside both `auth` and `student` middleware groups.

Finally show `app/Http/Middleware/EnsureUserIsStudent.php`, lines 11–17:

```php
public function handle(Request $request, Closure $next): Response
{
    if (! $request->user()?->isStudent()) {
        abort(403, 'Unauthorized access.');
    }

    return $next($request);
}
```

What to say: “Authentication proves who the user is. Role middleware decides which category of user may enter the route. Controllers then perform record-level authorization, such as checking whether the requested logbook belongs to that student.”

### C. Student placement-clearance submission

Entry route: `routes/web.php`, lines 56–59.

Core method: `app/Http/Controllers/StudentClearanceController.php`, lines 40–141.

Use these blocks in order:

```php
$activeCycle = InternshipCycle::active();

if (! $activeCycle && InternshipCycle::exists()) {
    return back()->with('error', 'Placement submissions are closed...');
}
```

“This enforces the semester window as a business constraint.”

```php
$assignment = $student->cycleAssignments()
    ->where('internship_cycle_id', $activeCycle->id)
    ->first();

if (! $assignment || ! $assignment->mentor_id) {
    // return a controlled error
}
```

“The student must belong to the active cohort and have an assigned academic mentor.”

```php
$validated = $request->validate([
    'company_name' => ['required', 'string', 'max:255'],
    'start_date' => ['required', 'date'],
    'end_date' => ['required', 'date', 'after:start_date'],
    'supervisor_email' => ['required', 'string', 'email', 'max:255'],
    'job_offer' => ['required', 'file', 'extensions:pdf', 'max:102400'],
    // two more required PDF documents
]);
```

“Validation is performed before storage. Files are restricted by extension and size, and the dates must form a valid interval.”

```php
$jobOfferPath = $request->file('job_offer')->store('clearances', 'local');

$clearance = PlacementClearance::create([
    'student_id' => $student->id,
    'internship_cycle_id' => $activeCycle?->id,
    'job_offer_path' => $jobOfferPath,
    'status' => 'pending',
]);
```

“Files are kept on the private local disk and the database stores their paths and workflow metadata. The initial state is pending, so the student cannot approve their own placement.”

The custom placement-date algorithm is at lines 175–218. It requires the placement start to be inside the semester window and calculates the expected end across the configured number of Monday–Friday work weeks.

### D. Mentor approval and automatic timeline generation

Entry routes are in `routes/web.php`, inside the mentor group. Show `app/Http/Controllers/MentorClearanceController.php`, lines 63–95:

```php
$this->authorizeClearance($clearance);

DB::transaction(function () use ($clearance, $timeline) {
    $clearance->update([
        'mentor_id' => auth()->id(),
        'status' => 'approved',
        'approved_at' => now(),
    ]);

    $timeline->generate($clearance->fresh());
});
```

What to say: “The controller first verifies that this student is assigned to the logged-in mentor. Approval and timeline generation are placed in one database transaction, so a failure rolls back the whole database change instead of leaving a partially approved workflow.”

Then show `app/Services/PlacementTimelineService.php`, lines 19–60:

```php
$weekStart = $placement->start_date->copy()->startOfDay();
$durationWeeks = $placement->cycle?->duration_weeks ?? 16;

for ($week = 1; $week <= $durationWeeks; $week++) {
    $weekEnd = $weekStart->copy();
    while (! $weekEnd->isFriday()) {
        $weekEnd->addDay();
    }

    $status = now()->lt($weekStart)
        ? 'scheduled'
        : (now()->gt($dueAt) ? 'overdue_locked' : 'open');

    $logbook = Logbook::firstOrNew([
        'user_id' => $placement->student_id,
        'week_number' => $week,
    ]);
    // assign dates, deadline, state and save
}
```

What to say:

- The duration is configured per internship cycle, with 16 weeks as a legacy fallback.
- `firstOrNew` makes generation idempotent: calling it again updates/reuses the weekly record rather than deliberately creating duplicates.
- Each week starts from the placement timeline and ends on Friday.
- Time decides whether it is scheduled, open, or overdue and locked.

### E. Supervisor account provisioning

Entry route: `routes/web.php`, line 197. Core method: `app/Http/Controllers/AdminClearanceController.php`, lines 105–168.

```php
$supervisor = User::where('email', $clearance->supervisor_personal_email)->first();
$rawPassword = Str::password(12);

if (! $supervisor) {
    $supervisor = User::create([
        'name' => $clearance->supervisor_name,
        'email' => $clearance->supervisor_personal_email,
        'password' => Hash::make($rawPassword),
        'role' => 'supervisor',
    ]);
}

$clearance->student->update(['supervisor_id' => $supervisor->id]);
$clearance->update([
    'supervisor_user_id' => $supervisor->id,
    'status' => 'completed',
]);
```

What to say: “Only an administrator route can provision the account, and only for an approved or completed placement. The random password is hashed before storage. The student and placement are linked to the supervisor. Brevo is used when configured; otherwise Laravel mail is the fallback.”

Do not say the raw password is encrypted. It is **hashed** for storage. Hashing is one-way; encryption is reversible.

### F. Weekly logbook submission

Entry route: `routes/web.php`, lines 112–120. Core method: `app/Http/Controllers/LogbookController.php`, lines 116–165.

```php
$validated = $request->validate($this->rules(true, $this->totalWeeksFor($request->user())));
$logbook = $request->user()->logbooks()
    ->where('week_number', $validated['week_number'])
    ->first();

if ($logbook) {
    $timeline->sync($logbook);
    if ($logbook->status !== 'open' || $logbook->description) {
        return back()->with('error', 'This weekly block is not available...');
    }
}
```

“The query starts from the authenticated user, so another student’s record cannot be selected. Before accepting data, the current deadline state is synchronized and only an open, unused week is allowed.”

```php
[$attendanceEntries, $renderedMinutes] = $this->prepareAttendance($request, $validated);

$data = [
    'description' => $this->formatDescription($validated),
    'attendance_entries' => $attendanceEntries,
    'rendered_minutes' => $renderedMinutes,
    'status' => 'pending',
];

$logbook->update($data);
$this->notifySupervisorOfPendingLogbook($logbook);
```

“Daily attendance is normalized and the total is stored as integer minutes to avoid floating-point hour errors. Submission changes the state to pending and alerts the assigned industrial supervisor.”

### G. Supervisor approval, signature, and rejection

Entry routes: `routes/web.php`, lines 209–212. Core method: `app/Http/Controllers/LogbookController.php`, lines 279–395.

```php
$logbook = Logbook::findOrFail($id);
$this->authorizeIndustrialSupervisor($logbook);

if (! $profile?->signature_path || ! $profile?->stamp_path) {
    return back()->with('error', 'Upload your e-signature and company stamp...');
}
```

“Finding a record is not enough; record-level authorization confirms that the supervisor is assigned to this student. Approval also requires stored signature and stamp assets.”

```php
if ($verifiedMinutes < $logbook->rendered_minutes
    && empty($validated['attendance_remarks'])) {
    throw ValidationException::withMessages([
        'attendance_remarks' => 'Explain why the verified hours were reduced.',
    ]);
}
```

“The supervisor may correct declared hours, but must leave an audit explanation when reducing them.”

```php
$logbook->update([
    'status' => 'approved',
    'verified_minutes' => $verifiedMinutes,
    'approved_by_id' => $request->user()->id,
    'approved_at' => now(),
    'approval_signature_path' => $profile->signature_path,
    'approval_stamp_path' => $profile->stamp_path,
]);
```

“The decision records who approved, when it happened, verified time, and the approval assets. A rejection instead records an issue category and reason and returns the logbook to the student.”

### H. Overdue deadline and extension workflow

Show `app/Services/PlacementTimelineService.php`, lines 63–94. It recalculates a non-final logbook as `scheduled`, `open`, or `overdue_locked`.

Show `routes/console.php`, lines 67–69:

```php
Schedule::command('logbooks:sync-timeline')
    ->hourly()
    ->withoutOverlapping();
```

“The scheduler applies deadlines even if nobody visits a page. `withoutOverlapping` prevents two copies of the same task changing rows simultaneously.”

Then show `LogbookController`, lines 397–488. A student can request an extension only for their own overdue locked week. Only their academic mentor can approve it, and an approved future `extension_until` reopens the week.

### I. Performance evaluation

Entry routes: `routes/web.php`, lines 216–224. Core method: `app/Http/Controllers/PerformanceEvaluationController.php`, lines 71–136.

```php
$this->authorizeAssignedSupervisor($request, $student);
$this->validateType($type);
abort_if($evaluation?->status === PerformanceEvaluation::STATUS_SUBMITTED, 409);
```

“Only the assigned supervisor can evaluate the student, only midterm/final types are accepted, and a submitted evaluation is locked.”

```php
foreach ($criteria as $key => $label) {
    $rules["ratings.$key.rating"] = ['required', Rule::in(['A', 'B', 'C', 'D', 'U'])];
}
```

“The criteria can come from the cycle’s active evaluation form, while the server still validates every dynamic field. A poor `D` rating also requires a written comment.”

### J. Final clearance with two independent reviewers

Student submission: `app/Http/Controllers/FinalClearanceController.php`, lines 26–122.

The method verifies that both reviewers are assigned, requires an approved placement, validates two documents, stores them privately, resets both reviewer states to pending, and records a submission event.

Mentor decision: `app/Http/Controllers/MentorFinalClearanceController.php`, lines 41–87.

Supervisor decision: `app/Http/Controllers/SupervisorFinalClearanceController.php`, lines 41–96. The supervisor must additionally confirm industrial hours and company-property clearance.

Overall decision: `app/Models/FinalClearance.php`, lines 81–100:

```php
if ($this->mentor_status === self::STATUS_REJECTED
    || $this->supervisor_status === self::STATUS_REJECTED) {
    $this->status = self::STATUS_REJECTED;
} elseif ($this->mentor_status === self::STATUS_APPROVED
    && $this->supervisor_status === self::STATUS_APPROVED) {
    $this->status = self::STATUS_COMPLETED;
    $this->completed_at ??= now();
} else {
    $this->status = self::STATUS_PENDING;
}
```

What to say: “This is an explicit two-person approval rule. Any rejection makes the overall state rejected; completion requires both approvals; all other combinations remain pending. Events preserve a simple audit history.”

### K. ATS resume PDF/DOCX generation

The resume builder offers three single-column ATS layouts for the on-screen preview, PDF, and editable DOCX:

- `classic`: formal black typography with a left-aligned identity block.
- `prime-ats`: contemporary blue headings with a centered identity block.
- `traditional`: a full-width identity header followed by two native reading columns.

All three use the same normalized applicant data. Classic and Modern keep a single-column section order. Two-Column ATS puts Summary, Skills, Languages, and References in the left column, then Projects and Education in the right column, preserving a deliberate left-to-right extraction order.

Core PDF method: `app/Http/Controllers/StudentResumeController.php`.

```php
[$user, $selectedTemplate, $html, $redirect] = $this->readyResume($request, $readinessService);
$mpdf = new Mpdf(['format' => 'A4', /* margins */]);
$mpdf->WriteHTML($html);
$contents = $mpdf->Output($fileName, 'S');
Storage::disk('local')->put($path, $contents);
```

What to say: “The readiness service blocks generation until required profile data exists. `StudentResumeDataService` converts the user’s profile, projects, education, and skills into one normalized data structure. The Blade partial renders that structure for the preview and mPDF, while `StudentWordDocumentService` uses the same structure for DOCX. This prevents the PDF and Word versions from showing different or invented content.”

The ATS layouts deliberately use standard fonts, standard headings, one column, real text bullets, and no tables, sidebars, icons, photos, or skill bars. These choices improve text extraction and make the resume easier for an applicant tracking system to parse. DOCX output escaping is enabled before writing the file so characters such as `&` in university or company names produce valid Word XML.

Limitation to state honestly: “No design can guarantee acceptance by every ATS because parsing rules differ between employers. The single-column Classic and Modern templates provide the safest parsing compatibility. The Two-Column option preserves a logical reading order but can still be interpreted incorrectly by older ATS software. The student also needs relevant keywords, accurate content, and a final proofreading pass.”

### L. Multi-channel notifications

Show `app/Notifications/WorkflowAlertNotification.php`:

```php
class WorkflowAlertNotification extends Notification implements ShouldQueue
{
    public function via(object $notifiable): array
    {
        $channels = [
            'database',
            config('services.brevo.use_api') ? BrevoWorkflowChannel::class : 'mail',
        ];

        if ($this->pusherIsConfigured()) {
            $channels[] = 'broadcast';
        }

        return $channels;
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage($this->toArray($notifiable));
    }
}
```

What to say: “The same domain event creates a permanent database notification and an email. When complete Pusher credentials are configured, it also adds the broadcast channel. `ShouldQueue` keeps the request responsive, while `toBroadcast()` defines the small payload sent to the browser. If Pusher is not configured, the database and email channels continue normally.”

Then show the private-channel rule in `routes/channels.php`:

```php
Broadcast::channel('App.Models.User.{id}', function (User $user, int $id): bool {
    return $user->id === $id;
});
```

What to say: “This is the security boundary. Laravel authenticates the `/broadcasting/auth` request, and the callback only authorizes a user to subscribe to their own channel. A student cannot listen to another user’s notification stream by changing the ID in JavaScript.”

Finally show the browser listener in `resources/js/app.js`:

```js
window.Echo
    .private(`App.Models.User.${userId}`)
    .notification((notification) => {
        updateUnreadNotificationBadges();
        showRealtimeNotification(notification);
    });
```

What to say: “Laravel Echo subscribes the authenticated browser to that private user channel. When Pusher delivers an event, the page increments both responsive bell badges and displays a ten-second toast. The notification remains stored in the database, so real-time delivery is an enhancement rather than the source of truth.”

For a live demonstration, create a Pusher Channels application and place its values in `.env`:

```dotenv
BROADCAST_CONNECTION=pusher
PUSHER_APP_ID=your-app-id
PUSHER_APP_KEY=your-public-key
PUSHER_APP_SECRET=your-server-secret
PUSHER_APP_CLUSTER=ap1
```

Then clear cached configuration and keep the queue worker running with `php artisan config:clear` and `php artisan queue:work`. The app key and cluster may be exposed to the browser, but the app secret must remain server-side and must never be committed.

## 7. Database relationships to explain

The `User` model is the relationship centre:

```text
User(student)
  |-- hasOne Profile
  |-- hasMany Education, Skill, Application, StudentDocument
  |-- hasMany PlacementClearance and Logbook
  |-- belongsTo User(mentor) through mentor_id
  |-- belongsTo User(supervisor) through supervisor_id
  |-- hasMany PerformanceEvaluation
  |-- hasOne FinalClearance and InternshipResult

InternshipCycle
  |-- hasMany InternshipCycleStudent assignments
  |-- hasMany placements, logbooks, evaluations, final clearances, results

FinalClearance
  |-- belongsTo student, mentor, supervisor, placement, cycle
  |-- hasMany FinalClearanceEvent audit records
```

Why a relational database: these records have strong identity, ownership, foreign-key relationships, and multi-record consistency requirements. A document database would not remove these relationships; it would move more integrity work into application code.

Why migrations: a migration is schema version control. A new environment can reproduce the tables in the correct order, and the deployment script runs `php artisan migrate --force`.

## 8. Security controls you can defend

- Passwords use Laravel hashing; the model also casts assigned passwords as `hashed`.
- Sessions are regenerated after login and invalidated on logout.
- Login attempts are rate limited.
- CSRF protection is provided for Blade forms by Laravel’s web middleware and `@csrf` form tokens.
- Route-level `auth` and role middleware block broad unauthorized access.
- Record-level checks verify ownership or mentor/supervisor assignment.
- Uploaded workflow documents mainly use the private `local` disk and controller-authorized downloads.
- Validation limits types, sizes, lengths, dates, states, and allowed enum-like values.
- Database transactions protect multi-record changes in important admin/approval operations.
- Laravel’s query builder/Eloquent parameter binding reduces SQL-injection risk; output is escaped by Blade by default.
- The notification HTML explicitly escapes dynamic values before inserting them.

Do not claim the system is “fully secure.” Say: “These are defense-in-depth controls, but production security still requires dependency scanning, penetration testing, secure secrets, backups, logging, and monitoring.”

## 9. Constraints versus limitations

An examiner may ask the difference:

- A **constraint** is a boundary imposed by requirements, policies, time, budget, or chosen environment.
- A **limitation** is something the current solution does not handle well or cannot do yet.

### Business and technical constraints

1. The workflow has four fixed roles: admin, student, academic mentor, and industrial supervisor.
2. Public self-registration is disabled; accounts are institution/admin provisioned.
3. A student needs active-cycle enrollment and a mentor before placement submission.
4. Placement dates follow a Monday-to-Friday calculation and cycle-configured duration, with a 16-week fallback.
5. Placement requires three PDF files, each limited to 100 MB by Laravel validation.
6. Final clearance requires two files and both assigned reviewers.
7. Logbook approval requires the supervisor’s signature and company stamp.
8. Evaluation types are fixed to midterm and final, with a controlled rating scale.
9. The deployed application depends on a PostgreSQL database, running scheduler/queue process, and configured email provider; live notification delivery additionally depends on Pusher Channels.
10. The project is a server-rendered web system and requires network/browser access; it is not an offline mobile application.

### Current limitations and honest improvement proposals

1. **Single Laravel monolith.** Good for an FYP and current scope, but independently scaling document generation, notifications, and web requests is harder. Improvement: keep the monolith until measured load justifies separating a worker/service.
2. **Local file storage.** Several critical files are stored on the container filesystem. Without a mounted persistent disk, a hosted container redeploy can lose them and multiple web instances cannot share them. Improvement: use S3-compatible object storage with encryption, lifecycle rules, malware scanning, and backups.
3. **One privacy inconsistency.** General logbook evidence uses Laravel’s `public` disk even though access goes through an authorized controller. Depending on the public-disk symlink/web-server configuration, the underlying file may be directly addressable. Improvement: move all evidence to the private disk and use temporary authorized downloads.
4. **Timezone is UTC.** `config/app.php` fixes the application timezone to UTC. If semester deadlines are meant to follow Malaysia/Singapore local time, the displayed and enforced deadline can differ by eight hours. Improvement: store instants in UTC but configure/convert the institutional timezone explicitly at the domain/UI boundary.
5. **Scheduler granularity is hourly.** A status may remain stale for up to roughly one scheduler interval unless a page access calls `sync()`. Improvement: run every minute where needed or compare deadlines directly in queries/UI.
6. **String-based state machines.** Workflow statuses are repeated as strings across controllers. Improvement: PHP backed enums plus dedicated transition services/policies would prevent invalid transitions and centralize rules.
7. **Some controllers are large.** `LogbookController` mixes student submission, attendance parsing, supervisor decisions, extensions, downloads, and notifications. Improvement: split it into role/use-case controllers and Form Requests/domain services.
8. **File validation relies mainly on allowed extensions.** Improvement: verify MIME/content signatures, scan for malware, cap PDF page counts where appropriate, and quarantine uploads before release.
9. **Email/provider dependency.** Brevo downtime or a stopped queue worker delays external alerts. Database notifications still help, but operations need failed-job monitoring and retries.
10. **Supervisor credential delivery.** If email fails, the temporary password is returned to the administrator in a flash message. It also resets an existing supervisor’s password when reprovisioned. Improvement: send a one-time set-password link, never display a credential, and avoid changing existing credentials.
11. **External API call inside a database transaction.** Supervisor provisioning sends email while the transaction is open, which may keep database locks longer during network delay. Improvement: commit first, then dispatch an after-commit queued job.
12. **No institutional single sign-on.** Social login was outside the implemented scope, so its unused packages and controller were removed. Improvement: add and test institutional SSO only when it becomes an approved requirement.
13. **Managed real-time dependency.** Live alerts now use Pusher Channels, so delivery depends on its network availability, credentials, connection/message quotas, and a running queue worker. The database notification centre remains the durable fallback. Improvement: monitor failed jobs and Pusher usage, and move to Laravel Reverb only if self-hosting becomes operationally justified.
14. **Minimal client-side application behavior.** This is intentional, but it means no offline support, optimistic UI, or sophisticated client caching. A PWA or SPA would be a future project, not automatically a better architecture.
15. **Tests are mostly feature tests.** That is strong for workflows, but only one unit-test method exists. Improvement: add focused unit tests for date calculations, attendance minutes, and state transitions, plus browser/end-to-end, accessibility, performance, and security tests.

## 10. Likely examiner questions and concise answers

### Why Laravel instead of plain PHP?

“Plain PHP would require me to design authentication, CSRF protection, validation, routing, ORM, storage, queues, notifications, and migrations myself. Laravel gives tested conventions for those concerns, so I could focus on the internship workflow while keeping a maintainable MVC structure.”

### Why not React or Vue?

“Most interactions are form-based approval workflows, not a highly interactive client application. Blade reduces duplicated validation and avoids a separate API/authentication layer. Alpine.js covers small UI states. If future requirements need offline behavior or complex real-time dashboards, a separate frontend could then be justified.”

### Why PostgreSQL?

“The domain is relational: users have assignments, placements, logbooks, evaluations, and approvals with foreign-key integrity. PostgreSQL provides transactions, constraints, indexing, and reliable concurrent writes, which fit this better than an unstructured store.”

### Why store minutes instead of decimal hours?

“Integers avoid floating-point rounding errors. I can total attendance reliably in minutes and convert to hours only for display.”

### How do you stop a student from accessing another student’s file?

“Role middleware is only the first layer. The controller checks record ownership or assignment before calling the private storage response. For example, `StudentDocumentController::authorizeOwner()` compares the document’s `user_id` with the authenticated user.”

### Why use both database and email notifications?

“Email is useful when the user is not logged in, while the database notification is an in-system audit-visible inbox and remains available if external mail is delayed. Queuing prevents email latency from slowing the main request.”

### Why use Pusher if database notifications already exist?

“The database is the durable source of truth, but it only becomes visible after a page request. Pusher adds immediate delivery while the user is online, so the bell count and toast update without refreshing. I use a private per-user channel and server-side authorization to prevent cross-user access. It is optional: if Pusher is unavailable or not configured, database and email notifications still work.”

### What happens if both final reviewers do not approve?

“The model derives the overall state. Any rejection makes it rejected; both approvals make it completed; otherwise it remains pending. Therefore one reviewer cannot complete the workflow alone.”

### What happens if timeline generation runs twice?

“It looks up each week by student ID and week number using `firstOrNew`, and the database has a unique student/week rule. This makes regeneration recoverable instead of intentionally duplicating weeks.”

### How would the system scale?

“First I would move files to shared object storage, use managed PostgreSQL with indexes and backups, run multiple queue workers, cache expensive dashboard aggregates, and profile slow queries. I would scale the monolith horizontally before introducing microservices because services add distributed-system complexity.”

### What is your most important current limitation?

“Persistent private file storage is the first production concern. The database can be managed externally, but uploaded evidence and clearance documents must also survive redeployment and be shared safely. My next step would be S3-compatible private object storage with backups and malware scanning.”

### What did automated testing focus on?

“The suite emphasizes business behavior: role isolation, record ownership, placement approval, timeline deadlines, supervisor signing, final two-party approval, dynamic evaluations, private document history, notifications, and semester isolation. These are higher-risk than simply testing whether a page exists.”

## 11. Suggested live demonstration order

Keep the demonstration to one complete story rather than opening every screen:

1. Log in as Adam and show that his placement is pending and all 16 logbook rows say "Not generated."
2. Log in as `lecturerapu1@gmail.com`, approve Adam's placement, and explain that approval generates his dated 16-week timeline.
3. Log in as Aisha and show the approved, rejected, pending, open, and scheduled examples. Open Week 1 to show objectives, skills, attendance, evidence, verified hours, signature, and stamp.
4. Log in as Daniel and show all 16 completed entries waiting for review.
5. Log in as `supervisor3@gmail.com` to show those same 16 entries in the Industrial Supervisor approval queue.
6. Log in as `lecturerapu3@gmail.com` to show that the Academic Mentor can monitor Daniel's progress but does not perform the Industrial Supervisor approval.
7. Log in as Admin One, open Placement & Supervisor Account Management, and use Nadia Rahman's "Create supervisor login" action for `mastervirey@gmail.com`.
8. Show the Gmail credential message, then log in with the generated supervisor password.
9. End on the admin progress dashboard to show the consolidated role-based lifecycle.

Before the live email step, confirm the presentation network's outbound IP is authorised in Brevo Security. Keep the displayed temporary password as a fallback if the external provider or venue network is unavailable.

## 12. Five code files to keep open during Q&A

If you can only keep five editor tabs open, use these:

1. `routes/web.php` — overall entry map and role grouping.
2. `app/Http/Controllers/StudentClearanceController.php` — validation and placement submission.
3. `app/Services/PlacementTimelineService.php` — core automation algorithm.
4. `app/Http/Controllers/LogbookController.php` — student/supervisor workflow and authorization.
5. `app/Models/FinalClearance.php` — clearest example of a business state rule.

Optional sixth tab: `tests/Feature/AcademicMentorWorkflowTest.php`, because it demonstrates that the main multi-role process is expressed as executable acceptance criteria.

## 13. Final presentation advice

- Explain a line using the pattern **input -> validation/authorization -> state change -> output/side effect**.
- Say “the code enforces…” only when you can point to the exact condition.
- Distinguish authentication, role authorization, and record ownership.
- Distinguish hashing from encryption.
- Call a transaction “atomic database work,” not a backup mechanism.
- Do not describe installed-but-unused libraries as implemented features.
- Present limitations with a prioritized improvement, which shows engineering judgment rather than weakness.
