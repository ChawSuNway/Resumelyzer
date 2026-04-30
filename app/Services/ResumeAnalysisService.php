<?php

namespace App\Services;

use App\Models\JobPosting;
use App\Models\Resume;
use App\Models\ResumeAnalysis;
use App\Models\SystemSetting;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;

class ResumeAnalysisService
{
    public function __construct(private readonly GeminiClient $gemini = new GeminiClient())
    {
    }

    public function analyze(Resume $resume, ?JobPosting $job = null): ResumeAnalysis
    {
        $analysis = ResumeAnalysis::create([
            'resume_id' => $resume->id,
            'job_posting_id' => $job?->id,
            'status' => 'running',
        ]);

        try {
            $weights = SystemSetting::get('scoring_weights', [
                'ats' => 30, 'readability' => 20, 'skills' => 30, 'professionalism' => 20,
            ]);
            $generic = SystemSetting::get('generic_terms_blocklist', []);
            $mandatorySections = SystemSetting::get('mandatory_sections', [
                'contact', 'experience', 'education', 'skills',
            ]);

            $payload = $this->callGemini($resume->extracted_text ?? '', $job, $generic, $mandatorySections);

            $sub = $payload['sub_scores'] ?? [];
            $overall = $this->weightedOverall($sub, $weights);

            $analysis->update([
                'status' => 'completed',
                'overall_score' => $overall,
                'ats_score' => (int) ($sub['ats'] ?? 0),
                'readability_score' => (int) ($sub['readability'] ?? 0),
                'skills_score' => (int) ($sub['skills'] ?? 0),
                'professionalism_score' => (int) ($sub['professionalism'] ?? 0),
                'parsed_sections' => $payload['parsed_sections'] ?? [],
                'keyword_match' => $payload['keyword_match'] ?? [],
                'feedback' => $payload['feedback'] ?? [],
                'flags' => $payload['flags'] ?? [],
                'summary' => $payload['summary'] ?? null,
                'analyzed_at' => now(),
            ]);
        } catch (Throwable $e) {
            Log::error('Resume analysis failed', ['resume_id' => $resume->id, 'error' => $e->getMessage()]);
            $analysis->update([
                'status' => 'failed',
                'error' => $e->getMessage(),
            ]);
        }

        return $analysis->fresh();
    }

    private function weightedOverall(array $sub, array $weights): int
    {
        $total = 0;
        $weightSum = 0;
        foreach (['ats', 'readability', 'skills', 'professionalism'] as $key) {
            $score = (int) ($sub[$key] ?? 0);
            $w = (int) ($weights[$key] ?? 0);
            $total += $score * $w;
            $weightSum += $w;
        }
        if ($weightSum === 0) {
            return 0;
        }
        return (int) round($total / $weightSum);
    }

    private function callGemini(string $text, ?JobPosting $job, array $genericBlocklist, array $mandatorySections): array
    {
        if (trim($text) === '') {
            throw new RuntimeException('Resume text is empty — extraction may have failed.');
        }

        $jobBlock = $job
            ? "JOB TITLE: {$job->title}\nCOMPANY: ".($job->company ?? 'N/A')."\nJOB DESCRIPTION:\n{$job->description}\nKEYWORDS: ".implode(', ', $job->keywords ?? [])
            : "No specific job posting provided. Evaluate resume generally and infer likely role from content.";

        $genericList = implode(', ', $genericBlocklist) ?: 'hardworking, team player, detail-oriented';
        $mandatoryList = implode(', ', $mandatorySections);

        $prompt = <<<PROMPT
You are an experienced ATS (Applicant Tracking System) auditor and career coach.
Analyze the candidate's resume below and return STRICT JSON only — no commentary.

{$jobBlock}

MANDATORY SECTIONS THAT MUST BE PRESENT: {$mandatoryList}
GENERIC FILLER TERMS TO FLAG IF OVERUSED: {$genericList}

Return JSON with this exact shape:
{
  "summary": string,                          // 2-3 sentence overview
  "sub_scores": {
    "ats": number 0-100,                      // formatting + keyword match
    "readability": number 0-100,              // grammar, sentence length, clarity
    "skills": number 0-100,                   // alignment with target role / job
    "professionalism": number 0-100           // tone, structure, consistency
  },
  "parsed_sections": {
    "contact": { "name": string|null, "email": string|null, "phone": string|null, "linkedin": string|null },
    "education": [ { "degree": string, "institution": string, "dates": string|null } ],
    "experience": [ { "role": string, "company": string, "dates": string|null, "responsibilities": [string] } ],
    "skills": { "technical": [string], "soft": [string] },
    "certifications": [string],
    "achievements": [string],
    "missing_sections": [string]
  },
  "keyword_match": {
    "matched": [string],
    "missing": [string],
    "density_score": number 0-100,
    "overused_generic_terms": [string]
  },
  "feedback": {
    "strengths": [string],
    "weaknesses": [string],
    "suggestions": [
      { "area": string, "tip": string, "example": string }
    ],
    "action_verbs_to_use": [string],
    "metrics_to_add": [string]
  },
  "flags": {
    "missing_dates": boolean,
    "no_measurable_achievements": boolean,
    "long_paragraphs": boolean,
    "passive_voice_overuse": boolean,
    "section_order_issues": [string]
  }
}

RESUME CONTENT:
\"\"\"
{$text}
\"\"\"
PROMPT;

        return $this->gemini->generateJson($prompt);
    }
}
