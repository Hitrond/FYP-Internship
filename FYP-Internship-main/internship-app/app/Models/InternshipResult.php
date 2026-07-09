<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InternshipResult extends Model
{
    protected $fillable = [
        'student_id',
        'internship_cycle_id',
        'mentor_id',
        'final_evaluation_id',
        'approved_logbooks',
        'total_logbooks',
        'supervisor_score',
        'result',
        'rationale',
        'locked_at',
    ];

    protected $casts = [
        'locked_at' => 'datetime',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function mentor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'mentor_id');
    }

    public function finalEvaluation(): BelongsTo
    {
        return $this->belongsTo(PerformanceEvaluation::class, 'final_evaluation_id');
    }

    public function cycle(): BelongsTo
    {
        return $this->belongsTo(InternshipCycle::class, 'internship_cycle_id');
    }
}
