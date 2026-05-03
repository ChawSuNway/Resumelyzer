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

        $completedAnalyses = ResumeAnalysis::query()
            ->whereHas('resume', fn ($q) => $q->where('user_id', $user->id))
            ->where('status', 'completed')
            ->whereNotNull('overall_score')
            ->latest()
            ->take(8)
            ->get(['id', 'overall_score', 'created_at']);

        $scoreTrend = $completedAnalyses->reverse()->values();

        $stats = [
            'resumes_count'    => $resumes->count(),
            'analyses_count'   => $completedAnalyses->count(),
            'shared_count'     => $resumes->where('shared_with_recruiters', true)->count(),
            'best_score'       => (int) ($completedAnalyses->max('overall_score') ?? 0),
            'avg_score'        => $completedAnalyses->count()
                ? (int) round($completedAnalyses->avg('overall_score'))
                : 0,
            'latest_score'     => (int) ($latestAnalysis?->overall_score ?? 0),
            'pending_count'    => $resumes->filter(
                fn ($r) => $r->latestAnalysis && ! $r->latestAnalysis->isComplete()
            )->count(),
        ];

        // Score delta vs previous analysis
        $previousScore   = $completedAnalyses->skip(1)->first()?->overall_score;
        $stats['delta']  = ($previousScore !== null && $latestAnalysis?->overall_score !== null)
            ? (int) $latestAnalysis->overall_score - (int) $previousScore
            : null;

        $strengths   = is_array($latestAnalysis->feedback['strengths']   ?? null) ? $latestAnalysis->feedback['strengths']   : [];
        $weaknesses  = is_array($latestAnalysis->feedback['weaknesses']  ?? null) ? $latestAnalysis->feedback['weaknesses']  : [];
        $suggestions = is_array($latestAnalysis->feedback['suggestions'] ?? null) ? $latestAnalysis->feedback['suggestions'] : [];

        $openJobs = JobPosting::where('is_active', true)->latest()->take(5)->get();

        $hour    = (int) now()->format('G');
        $greeting = match (true) {
            $hour < 5  => 'Good evening',
            $hour < 12 => 'Good morning',
            $hour < 18 => 'Good afternoon',
            default    => 'Good evening',
        };

        return view('candidate.dashboard', compact(
            'resumes',
            'latestAnalysis',
            'openJobs',
            'stats',
            'scoreTrend',
            'strengths',
            'weaknesses',
            'suggestions',
            'greeting',
        ));
    }
}
