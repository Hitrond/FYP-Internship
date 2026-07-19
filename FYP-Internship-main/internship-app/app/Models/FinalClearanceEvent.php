<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FinalClearanceEvent extends Model
{
    protected $fillable = [
        'actor_id',
        'action',
        'actor_role',
        'feedback',
    ];

    public function finalClearance(): BelongsTo
    {
        return $this->belongsTo(FinalClearance::class);
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }
}
