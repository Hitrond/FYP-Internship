<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Services\BrevoPasswordResetSender;
use Database\Factories\UserFactory;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'role', 'mentor_id', 'supervisor_id'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function sendPasswordResetNotification($token): void
    {
        if (config('services.brevo.use_api')) {
            app(BrevoPasswordResetSender::class)->send($this, $token);

            return;
        }

        $this->notify(new ResetPassword($token));
    }

    public function profile()
    {
        return $this->hasOne(Profile::class);
    }

    public function education()
    {
        return $this->hasMany(Education::class);
    }

    public function skills()
    {
        return $this->hasMany(Skill::class);
    }

    public function applications()
    {
        return $this->hasMany(Application::class);
    }

    public function logbooks()
    {
        return $this->hasMany(Logbook::class);
    }

    public function studentDocuments()
    {
        return $this->hasMany(StudentDocument::class);
    }

    public function finalClearance()
    {
        return $this->hasOne(FinalClearance::class, 'student_id');
    }

    public function placementClearances()
    {
        return $this->hasMany(PlacementClearance::class, 'student_id');
    }

    public function latestPlacementClearance()
    {
        return $this->hasOne(PlacementClearance::class, 'student_id')->latestOfMany();
    }

    public function cycleAssignments()
    {
        return $this->hasMany(InternshipCycleStudent::class, 'student_id');
    }

    public function mentoredCycleAssignments()
    {
        return $this->hasMany(InternshipCycleStudent::class, 'mentor_id');
    }

    public function performanceEvaluations()
    {
        return $this->hasMany(PerformanceEvaluation::class, 'student_id');
    }

    public function submittedPerformanceEvaluations()
    {
        return $this->hasMany(PerformanceEvaluation::class, 'supervisor_id');
    }

    public function internshipResult()
    {
        return $this->hasOne(InternshipResult::class, 'student_id');
    }

    public function isAdmin()
    {
        return $this->role === 'admin';
    }

    public function isMentor()
    {
        return $this->role === 'mentor';
    }

    public function isSupervisor()
    {
        return $this->role === 'supervisor';
    }

    public function isStudent()
    {
        return $this->role === 'student';
    }

    /**
     * For a Student: Get the supervisor assigned to them.
     */
    public function supervisor()
    {
        return $this->belongsTo(User::class, 'supervisor_id');
    }

    /**
     * For a Supervisor: Get all students assigned to them.
     */
    public function supervisedStudents()
    {
        return $this->hasMany(User::class, 'supervisor_id');
    }

    /**
     * For a Student: Get the mentor assigned to them.
     */
    public function mentor()
    {
        return $this->belongsTo(User::class, 'mentor_id');
    }

    /**
     * For a Mentor: Get all students assigned to them.
     */
    public function assignedStudents()
    {
        return $this->hasMany(User::class, 'mentor_id');
    }
}
