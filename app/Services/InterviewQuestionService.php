<?php

namespace App\Services;

use App\Models\InterviewQuestionSet;
use App\Models\JobPosting;
use App\Models\Resume;
use App\Models\User;
use RuntimeException;

class InterviewQuestionService
{
    public function __construct(private readonly GeminiClient $gemini = new GeminiClient())
    {
    }

    public function generate(Resume $resume, ?JobPosting $job, User $generatedBy): InterviewQuestionSet
    {
        $text = trim((string) $resume->extracted_text);
        if ($text === '') {
            throw new RuntimeException('Resume text is empty — cannot generate questions.');
        }

        $jobBlock = $job
            ? "TARGET ROLE: {$job->title}\nCOMPANY: ".($job->company ?? 'N/A')."\nJOB DESCRIPTION:\n{$job->description}\nKEYWORDS: ".implode(', ', $job->keywords ?? [])
            : 'No specific role provided. Infer the most likely role from the resume content.';

        // Trim resume text to keep total request small and Gemini fast.
        $resumeForPrompt = mb_substr($text, 0, 6000);

        $prompt = <<<PROMPT
You are an experienced technical interviewer.
Based on the resume (and optional target role), produce realistic interview questions tailored to specific
projects, technologies, and experience visible in the resume. Return STRICT JSON only — no commentary.

{$jobBlock}

Return JSON with this exact shape — 3 questions per category, mixed difficulty:
{
  "summary": string,
  "categories": {
    "technical":     [{ "question": string, "difficulty": "easy"|"intermediate"|"hard", "based_on": string }],
    "behavioral":    [{ "question": string, "difficulty": "easy"|"intermediate"|"hard", "based_on": string }],
    "role_specific": [{ "question": string, "difficulty": "easy"|"intermediate"|"hard", "based_on": string }],
    "situational":   [{ "question": string, "difficulty": "easy"|"intermediate"|"hard", "based_on": string }]
  }
}

Make questions concrete and resume-specific. Keep each "based_on" to under 12 words.

RESUME:
\"\"\"
{$resumeForPrompt}
\"\"\"
PROMPT;

        $payload = $this->gemini->generateJson($prompt);

        if (! isset($payload['categories']) || ! is_array($payload['categories'])) {
            throw new RuntimeException('Gemini response missing "categories" key.');
        }

        return InterviewQuestionSet::create([
            'resume_id' => $resume->id,
            'job_posting_id' => $job?->id,
            'generated_by_user_id' => $generatedBy->id,
            'questions' => $payload['categories'],
            'meta' => [
                'summary' => $payload['summary'] ?? null,
                'job_title' => $job?->title,
            ],
        ]);
    }
}
