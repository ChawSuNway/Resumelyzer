<?php

namespace Database\Seeders;

use App\Models\SystemSetting;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@resumelyzer.test'],
            [
                'name' => 'Site Admin',
                'password' => Hash::make('password'),
                'role' => User::ROLE_ADMIN,
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        User::updateOrCreate(
            ['email' => 'recruiter@resumelyzer.test'],
            [
                'name' => 'Demo Recruiter',
                'password' => Hash::make('password'),
                'role' => User::ROLE_RECRUITER,
                'company' => 'Demo HR Co.',
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        User::updateOrCreate(
            ['email' => 'candidate@resumelyzer.test'],
            [
                'name' => 'Demo Candidate',
                'password' => Hash::make('password'),
                'role' => User::ROLE_CANDIDATE,
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        $defaults = [
            'mandatory_sections' => ['contact', 'experience', 'education', 'skills'],
            'generic_terms_blocklist' => [
                'hardworking', 'team player', 'go-getter', 'self-starter',
                'detail-oriented', 'results-driven', 'thinks outside the box',
            ],
            'ats_keyword_library' => [
                'general' => ['communication', 'leadership', 'project management', 'analytics'],
                'engineering' => ['python', 'javascript', 'sql', 'docker', 'kubernetes', 'aws', 'ci/cd'],
                'design' => ['figma', 'user research', 'wireframes', 'prototyping'],
                'marketing' => ['seo', 'campaign', 'analytics', 'content strategy'],
            ],
            'scoring_weights' => [
                'ats' => 30, 'readability' => 20, 'skills' => 30, 'professionalism' => 20,
            ],
            'retention_days' => 30,
        ];

        foreach ($defaults as $key => $value) {
            SystemSetting::put($key, $value);
        }
    }
}
