<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EvaluationForm extends Model
{
    public const TYPE_MIDTERM = 'midterm';

    public const TYPE_FINAL = 'final';

    protected $fillable = [
        'internship_cycle_id',
        'type',
        'title',
        'version',
        'criteria',
        'instructions',
        'uploaded_file_path',
        'uploaded_file_name',
        'is_active',
    ];

    protected $casts = [
        'criteria' => 'array',
        'is_active' => 'boolean',
    ];

    public function cycle(): BelongsTo
    {
        return $this->belongsTo(InternshipCycle::class, 'internship_cycle_id');
    }

    public static function defaultCriteria(): array
    {
        return PerformanceEvaluation::DEFAULT_CRITERIA;
    }

    public static function activeFor(string $type, ?int $cycleId = null): ?self
    {
        return static::query()
            ->where('type', $type)
            ->where('is_active', true)
            ->where(function ($query) use ($cycleId): void {
                $query->whereNull('internship_cycle_id');

                if ($cycleId) {
                    $query->orWhere('internship_cycle_id', $cycleId);
                }
            })
            ->orderByRaw('case when internship_cycle_id is null then 1 else 0 end')
            ->latest()
            ->first();
    }
}
