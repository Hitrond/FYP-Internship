<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PerformanceEvaluation extends Model
{
    public const TYPE_MIDTERM = 'midterm';

    public const TYPE_FINAL = 'final';

    public const STATUS_DRAFT = 'draft';

    public const STATUS_SUBMITTED = 'submitted';

    public const DEFAULT_CRITERIA = [
        'attitude_to_supervision' => 'Attitude to supervision',
        'social_integration' => 'Social integration',
        'motivation' => 'Motivation',
        'perseverance' => 'Perseverance',
        'technical_knowledge' => 'Technical knowledge',
        'productivity' => 'Productivity',
        'teamwork' => 'Capacity for teamwork',
        'problem_solving' => 'Problem-solving ability',
        'written_communication' => 'Written communication skills',
        'oral_communication' => 'Oral communication skills',
    ];

    public const CRITERIA = self::DEFAULT_CRITERIA;

    protected $fillable = [
        'student_id',
        'internship_cycle_id',
        'evaluation_form_id',
        'supervisor_id',
        'type',
        'ratings',
        'overall_grade',
        'overall_comments',
        'status',
        'submitted_at',
    ];

    protected $casts = [
        'ratings' => 'array',
        'submitted_at' => 'datetime',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function supervisor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'supervisor_id');
    }

    public function cycle(): BelongsTo
    {
        return $this->belongsTo(InternshipCycle::class, 'internship_cycle_id');
    }

    public function form(): BelongsTo
    {
        return $this->belongsTo(EvaluationForm::class, 'evaluation_form_id');
    }

    public function criteria(): array
    {
        return $this->form?->criteria ?: self::DEFAULT_CRITERIA;
    }

    public function hasConcern(): bool
    {
        return $this->overall_grade <= 3
            || collect($this->ratings)->contains(
                fn (array $rating) => ($rating['rating'] ?? null) === 'D'
            );
    }
}
