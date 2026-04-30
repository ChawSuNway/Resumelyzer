<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RecruiterNote extends Model
{
    protected $fillable = [
        'recruiter_id',
        'candidate_id',
        'resume_id',
        'rating',
        'note',
    ];

    public function recruiter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recruiter_id');
    }

    public function candidate(): BelongsTo
    {
        return $this->belongsTo(User::class, 'candidate_id');
    }

    public function resume(): BelongsTo
    {
        return $this->belongsTo(Resume::class);
    }
}
