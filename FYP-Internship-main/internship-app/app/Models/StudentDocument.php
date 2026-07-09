<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentDocument extends Model
{
    public const TYPE_RESUME = 'resume';

    public const TYPE_COVER_LETTER = 'cover_letter';

    protected $fillable = [
        'user_id',
        'type',
        'title',
        'source',
        'original_name',
        'file_path',
        'mime_type',
        'size',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
