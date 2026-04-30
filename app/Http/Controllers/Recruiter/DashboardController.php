<?php

namespace App\Http\Controllers\Recruiter;

use App\Http\Controllers\Controller;
use App\Models\JobPosting;
use App\Models\Resume;
use App\Models\User;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __invoke(Request $request)
    {
        $jobs = JobPosting::where('recruiter_id', $request->user()->id)
            ->latest()->take(5)->get();

        $candidates = User::where('role', User::ROLE_CANDIDATE)
            ->where('allow_recruiter_access', true)
            ->withCount('resumes')
            ->latest()->take(10)->get();

        $sharedResumes = Resume::where('shared_with_recruiters', true)
            ->with(['user', 'latestAnalysis'])
            ->latest()->take(5)->get();

        return view('recruiter.dashboard', compact('jobs', 'candidates', 'sharedResumes'));
    }
}
