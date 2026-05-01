<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Resume extends Model
{
    protected $fillable = [
        'user_id',
        'original_filename',
        'stored_path',
        'mime_type',
        'extension',
        'size_bytes',
        'extracted_text',
        'is_encrypted',
        'shared_with_recruiters',
        'purge_after',
    ];

    protected function casts(): array
    {
        return [
            'is_encrypted' => 'boolean',
            'shared_with_recruiters' => 'boolean',
            'purge_after' => 'datetime',
            'size_bytes' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function analyses(): HasMany
    {
        return $this->hasMany(ResumeAnalysis::class);
    }

    public function latestAnalysis(): HasOne
    {
        return $this->hasOne(ResumeAnalysis::class)->latestOfMany();
    }

    public function interviewQuestionSets(): HasMany
    {
        return $this->hasMany(InterviewQuestionSet::class);
    }

    public function latestInterviewQuestionSet(): HasOne
    {
        return $this->hasOne(InterviewQuestionSet::class)->latestOfMany();
    }
}
