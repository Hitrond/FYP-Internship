<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InternshipCycle extends Model
{
    public const STATUS_DRAFT = 'draft';

    public const STATUS_ACTIVE = 'active';

    public const STATUS_CLOSED = 'closed';

    public const STATUS_ARCHIVED = 'archived';

    protected $fillable = [
        'name',
        'intake_code',
        'academic_year',
        'placement_window_start',
        'placement_window_end',
        'duration_weeks',
        'deadline_weekday',
        'deadline_time',
        'timezone',
        'status',
        'activated_at',
        'closed_at',
    ];

    protected $casts = [
        'placement_window_start' => 'date',
        'placement_window_end' => 'date',
        'deadline_time' => 'datetime:H:i',
        'activated_at' => 'datetime',
        'closed_at' => 'datetime',
    ];

    public function assignments(): HasMany
    {
        return $this->hasMany(InternshipCycleStudent::class);
    }

    public function placements(): HasMany
    {
        return $this->hasMany(PlacementClearance::class);
    }

    public function logbooks(): HasMany
    {
        return $this->hasMany(Logbook::class);
    }

    public static function active(): ?self
    {
        return static::where('status', self::STATUS_ACTIVE)->first();
    }
}
