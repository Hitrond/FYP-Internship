<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Application extends Model
{
    protected $fillable = [
        'user_id',
        'company_name',
        'position_title',
        'location',
        'status',
        'applied_on',
        'last_contacted_on',
        'next_followup_on',
        'job_url',
        'notes',
    ];

    protected $casts = [
        'applied_on' => 'date',
        'last_contacted_on' => 'date',
        'next_followup_on' => 'date',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
