<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Logbook extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'internship_cycle_id',
        'week_number',
        'timeline_generated',
        'start_date',
        'end_date',
        'submission_due_at',
        'locked_at',
        'overdue_notified_at',
        'extension_status',
        'extension_reason',
        'extension_requested_at',
        'extension_until',
        'extension_decision_note',
        'extension_decided_at',
        'description',
        'attendance_entries',
        'rendered_minutes',
        'verified_minutes',
        'attendance_remarks',
        'status',
        'supervisor_remarks',
        'rejection_category',
        'approved_by_id',
        'approved_at',
        'approval_signature_path',
        'approval_stamp_path',
        'approval_company_name',
        'evidence_file_path',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'attendance_entries' => 'array',
        'approved_at' => 'datetime',
        'timeline_generated' => 'boolean',
        'submission_due_at' => 'datetime',
        'locked_at' => 'datetime',
        'overdue_notified_at' => 'datetime',
        'extension_requested_at' => 'datetime',
        'extension_until' => 'datetime',
        'extension_decided_at' => 'datetime',
    ];

    public function getRenderedHoursAttribute(): float
    {
        return round($this->rendered_minutes / 60, 2);
    }

    public function getVerifiedHoursAttribute(): ?float
    {
        return $this->verified_minutes === null
            ? null
            : round($this->verified_minutes / 60, 2);
    }

    // A logbook belongs to a student
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function student()
    {
        // Explicitly tell it to use 'user_id' to find the student
        return $this->belongsTo(User::class, 'user_id');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by_id');
    }

    public function cycle(): BelongsTo
    {
        return $this->belongsTo(InternshipCycle::class, 'internship_cycle_id');
    }
}
