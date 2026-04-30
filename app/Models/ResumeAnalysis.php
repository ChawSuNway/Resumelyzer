<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ResumeAnalysis extends Model
{
    protected $fillable = [
        'resume_id',
        'job_posting_id',
        'status',
        'overall_score',
        'ats_score',
        'readability_score',
        'skills_score',
        'professionalism_score',
        'parsed_sections',
        'keyword_match',
        'feedback',
        'flags',
        'summary',
        'error',
        'analyzed_at',
    ];

    protected function casts(): array
    {
        return [
            'parsed_sections' => 'array',
            'keyword_match' => 'array',
            'feedback' => 'array',
            'flags' => 'array',
            'analyzed_at' => 'datetime',
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

    public function isComplete(): bool
    {
        return $this->status === 'completed';
    }

    public function scoreColor(): string
    {
        return match (true) {
            $this->overall_score === null => 'gray',
            $this->overall_score >= 80 => 'green',
            $this->overall_score >= 60 => 'yellow',
            default => 'red',
        };
    }
}
