<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlacementClearance extends Model
{
    protected $fillable = [
        'student_id',
        'mentor_id',
        'supervisor_user_id',
        'company_name',
        'office_address',
        'supervisor_name',
        'supervisor_email',
        'supervisor_personal_email',
        'job_offer_path',
        'indemnity_path',
        'placement_agreement_path',
        'status',
        'rejection_reason',
        'approved_at',
        'rejected_at',
    ];

    protected $casts = [
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
    ];

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function mentor()
    {
        return $this->belongsTo(User::class, 'mentor_id');
    }

    public function supervisorUser()
    {
        return $this->belongsTo(User::class, 'supervisor_user_id');
    }
}
