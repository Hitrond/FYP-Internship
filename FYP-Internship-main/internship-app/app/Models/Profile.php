<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Profile extends Model
{
    protected $fillable = [
        'user_id',
        'tp_number',
        'full_name',
        'course_name',
        'specialization',
        'intake_code',
        'personal_email',
        'contact_number',
        'internship_status',
        'phone_number',
        'bio',
        'linkedin_url',
        'github_url',
        'portfolio_url',
        'projects_summary',
        'mentor_staff_id',
        'languages_summary',
        'references_summary',
        'notify_email_missed_logbook',
        'notify_dashboard_only',
        'supervisor_job_title',
        'supervisor_contact_number',
        'company_email',
        'company_name',
        'company_address',
        'industry',
        'signature_path',
        'stamp_path',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
