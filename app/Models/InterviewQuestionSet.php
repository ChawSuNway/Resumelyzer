<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InterviewQuestionSet extends Model
{
    protected $fillable = [
        'resume_id',
        'job_posting_id',
        'generated_by_user_id',
        'questions',
        'meta',
    ];

    protected function casts(): array
    {
        return [
            'questions' => 'array',
            'meta' => 'array',
        ];
    }

    public function resume(): BelongsTo
    {
        return $this->belongsTo(Resume::class);
    }

    public function jobPosting(): BelongsTo
    {
        return $this->belongsTo(JobPosting::class);
    }

    public function generatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'generated_by_user_id');
    }
}
