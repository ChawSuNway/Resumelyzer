<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    public const ROLE_ADMIN = 'admin';
    public const ROLE_RECRUITER = 'recruiter';
    public const ROLE_CANDIDATE = 'candidate';

    public const ROLES = [
        self::ROLE_ADMIN,
        self::ROLE_RECRUITER,
        self::ROLE_CANDIDATE,
    ];

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'phone',
        'linkedin',
        'company',
        'is_active',
        'store_resumes',
        'allow_recruiter_access',
        'last_login_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'store_resumes' => 'boolean',
            'allow_recruiter_access' => 'boolean',
        ];
    }

    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    public function isRecruiter(): bool
    {
        return $this->role === self::ROLE_RECRUITER;
    }

    public function isCandidate(): bool
    {
        return $this->role === self::ROLE_CANDIDATE;
    }

    public function resumes(): HasMany
    {
        return $this->hasMany(Resume::class);
    }

    public function jobPostings(): HasMany
    {
        return $this->hasMany(JobPosting::class, 'recruiter_id');
    }

    public function notesAuthored(): HasMany
    {
        return $this->hasMany(RecruiterNote::class, 'recruiter_id');
    }
}
