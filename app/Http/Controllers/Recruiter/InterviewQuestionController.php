<?php

namespace App\Http\Controllers\Recruiter;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\JobPosting;
use App\Models\Resume;
use App\Services\InterviewQuestionService;
use Illuminate\Http\Request;
use Throwable;

class InterviewQuestionController extends Controller
{
    public function __construct(private readonly InterviewQuestionService $service)
    {
    }

    public function store(Request $request, Resume $resume)
    {
        abort_unless($resume->shared_with_recruiters, 403);

        $validated = $request->validate([
            'job_posting_id' => ['nullable', 'integer'],
        ]);

        $job = null;
        if (! empty($validated['job_posting_id'])) {
            $job = JobPosting::where('id', $validated['job_posting_id'])
                ->where('recruiter_id', $request->user()->id)
                ->first();
        }

        try {
            $set = $this->service->generate($resume, $job, $request->user());
        } catch (Throwable $e) {
            return redirect()
                ->route('recruiter.candidates.show', $resume)
                ->with('error', 'Could not generate interview questions: '.$e->getMessage());
        }

        ActivityLog::record('recruiter.interview_questions_generated', $resume, [
            'set_id' => $set->id,
            'job_posting_id' => $job?->id,
        ]);

        return redirect()
            ->route('recruiter.candidates.show', $resume)
            ->with('status', 'Interview questions generated.');
    }
}
