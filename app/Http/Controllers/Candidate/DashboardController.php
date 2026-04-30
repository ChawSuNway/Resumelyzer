<?php

namespace App\Http\Controllers\Candidate;

use App\Http\Controllers\Controller;
use App\Models\JobPosting;
use App\Models\ResumeAnalysis;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __invoke(Request $request)
    {
        $user = $request->user();
        $resumes = $user->resumes()->with('latestAnalysis')->latest()->get();

        $latestAnalysis = ResumeAnalysis::query()
            ->whereHas('resume', fn ($q) => $q->where('user_id', $user->id))
            ->latest()
            ->first();

        $openJobs = JobPosting::where('is_active', true)->latest()->take(5)->get();

        return view('candidate.dashboard', compact('resumes', 'latestAnalysis', 'openJobs'));
    }
}
