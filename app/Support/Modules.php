<?php

namespace App\Support;

use App\Models\SystemSetting;

class Modules
{
    public const ALL = [
        'interview_questions' => [
            'label'       => 'Interview Question Generator',
            'description' => 'AI-generated interview questions for candidates and recruiters.',
        ],
        'resume_export' => [
            'label'       => 'Resume Export (PDF / CSV)',
            'description' => 'Allow candidates to export their analysis reports as PDF or CSV.',
        ],
        'recruiter_access' => [
            'label'       => 'Recruiter Candidate Access',
            'description' => 'Allow recruiters to browse, view, and compare shared candidate resumes.',
        ],
    ];

    public static function enabled(string $key): bool
    {
        $enabled = (array) SystemSetting::get('enabled_modules', array_keys(self::ALL));
        return in_array($key, $enabled, true);
    }

    public static function enabledModules(): array
    {
        return (array) SystemSetting::get('enabled_modules', array_keys(self::ALL));
    }
}
