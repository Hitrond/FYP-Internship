<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FinalClearance extends Model
{
    public const STATUS_PENDING = 'pending';

    public const STATUS_APPROVED = 'approved';

    public const STATUS_REJECTED = 'rejected';

    public const STATUS_COMPLETED = 'completed';

    protected $fillable = [
        'student_id',
        'internship_cycle_id',
        'placement_clearance_id',
        'mentor_id',
        'supervisor_id',
        'report_path',
        'report_original_name',
        'report_clearance_form_path',
        'report_clearance_form_original_name',
        'slides_path',
        'slides_original_name',
        'status',
        'mentor_status',
        'mentor_feedback',
        'mentor_signed_at',
        'industrial_hours_completed',
        'company_property_cleared',
        'supervisor_status',
        'supervisor_feedback',
        'supervisor_signed_at',
        'completed_at',
    ];

    protected $casts = [
        'mentor_signed_at' => 'datetime',
        'supervisor_signed_at' => 'datetime',
        'completed_at' => 'datetime',
        'industrial_hours_completed' => 'boolean',
        'company_property_cleared' => 'boolean',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function placementClearance(): BelongsTo
    {
        return $this->belongsTo(PlacementClearance::class);
    }

    public function mentor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'mentor_id');
    }

    public function supervisor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'supervisor_id');
    }

    public function cycle(): BelongsTo
    {
        return $this->belongsTo(InternshipCycle::class, 'internship_cycle_id');
    }

    public function events(): HasMany
    {
        return $this->hasMany(FinalClearanceEvent::class)->oldest();
    }

    public function refreshOverallStatus(): void
    {
        if (
            $this->mentor_status === self::STATUS_REJECTED
            || $this->supervisor_status === self::STATUS_REJECTED
        ) {
            $this->status = self::STATUS_REJECTED;
            $this->completed_at = null;
        } elseif (
            $this->mentor_status === self::STATUS_APPROVED
            && $this->supervisor_status === self::STATUS_APPROVED
        ) {
            $this->status = self::STATUS_COMPLETED;
            $this->completed_at ??= now();
        } else {
            $this->status = self::STATUS_PENDING;
            $this->completed_at = null;
        }

        $this->save();
    }
}
