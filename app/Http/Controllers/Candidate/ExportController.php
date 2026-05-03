<?php

namespace App\Http\Controllers\Candidate;

use App\Http\Controllers\Controller;
use App\Models\Resume;
use App\Support\Modules;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportController extends Controller
{
    private function requireModule(): void
    {
        abort_unless(Modules::enabled('resume_export'), 503, 'Resume export is currently disabled.');
    }

    public function reportPdf(Request $request, Resume $resume)
    {
        $this->requireModule();
        $this->authorizeOwner($request, $resume);
        $analysis = $resume->latestAnalysis;
        abort_unless($analysis, 404, 'No analysis available.');

        $pdf = Pdf::loadView('exports.analysis-pdf', compact('resume', 'analysis'));
        return $pdf->download('resume-analysis-'.$resume->id.'.pdf');
    }

    public function reportCsv(Request $request, Resume $resume): StreamedResponse
    {
        $this->requireModule();
        $this->authorizeOwner($request, $resume);
        $analysis = $resume->latestAnalysis;
        abort_unless($analysis, 404, 'No analysis available.');

        $filename = 'resume-analysis-'.$resume->id.'.csv';

        return response()->streamDownload(function () use ($analysis, $resume) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Field', 'Value']);
            fputcsv($out, ['Resume', $resume->original_filename]);
            fputcsv($out, ['Overall Score', $analysis->overall_score]);
            fputcsv($out, ['ATS Score', $analysis->ats_score]);
            fputcsv($out, ['Readability Score', $analysis->readability_score]);
            fputcsv($out, ['Skills Score', $analysis->skills_score]);
            fputcsv($out, ['Professionalism Score', $analysis->professionalism_score]);
            fputcsv($out, ['Summary', $analysis->summary]);
            fputcsv($out, ['Matched Keywords', implode(', ', $analysis->keyword_match['matched'] ?? [])]);
            fputcsv($out, ['Missing Keywords', implode(', ', $analysis->keyword_match['missing'] ?? [])]);
            fputcsv($out, ['Strengths', implode(' | ', $analysis->feedback['strengths'] ?? [])]);
            fputcsv($out, ['Weaknesses', implode(' | ', $analysis->feedback['weaknesses'] ?? [])]);
            $flatten = fn ($v) => is_array($v) ? implode(' ', array_filter($v, 'is_scalar')) : (string) ($v ?? '');
            $suggestions = collect($analysis->feedback['suggestions'] ?? [])
                ->map(fn ($s) => $flatten($s['area'] ?? '').': '.$flatten($s['tip'] ?? ''))->implode(' | ');
            fputcsv($out, ['Suggestions', $suggestions]);
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    public function improvedDraft(Request $request, Resume $resume)
    {
        $this->requireModule();
        $this->authorizeOwner($request, $resume);
        $analysis = $resume->latestAnalysis;
        abort_unless($analysis, 404, 'No analysis available.');

        $pdf = Pdf::loadView('exports.improved-draft', compact('resume', 'analysis'));
        return $pdf->download('improved-resume-draft-'.$resume->id.'.pdf');
    }

    private function authorizeOwner(Request $request, Resume $resume): void
    {
        abort_unless($resume->user_id === $request->user()->id, 403);
    }
}
