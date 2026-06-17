<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CoverLetter extends Model
{
    use HasFactory;

    // This tells Laravel it is safe to auto-save these specific columns
    protected $fillable = [
        'user_id',
        'company_name',
        'hiring_manager',
        'role',
        'body_text',
    ];

    // Establish the relationship back to the student
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}