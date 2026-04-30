<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\JobPosting;
use App\Models\Resume;
use App\Models\ResumeAnalysis;
use App\Models\User;

class DashboardController extends Controller
{
    public function __invoke()
    {
        $stats = [
            'users' => User::count(),
            'candidates' => User::where('role', User::ROLE_CANDIDATE)->count(),
            'recruiters' => User::where('role', User::ROLE_RECRUITER)->count(),
            'admins' => User::where('role', User::ROLE_ADMIN)->count(),
            'resumes' => Resume::count(),
            'analyses' => ResumeAnalysis::count(),
            'jobs' => JobPosting::count(),
            'avg_score' => (int) round((float) ResumeAnalysis::whereNotNull('overall_score')->avg('overall_score')),
        ];

        $recentActivity = ActivityLog::with('user')->latest()->take(15)->get();

        $uploadsByDay = Resume::query()
            ->selectRaw('DATE(created_at) as day, COUNT(*) as count')
            ->where('created_at', '>=', now()->subDays(14))
            ->groupBy('day')
            ->orderBy('day')
            ->get();

        return view('admin.dashboard', compact('stats', 'recentActivity', 'uploadsByDay'));
    }
}
