<?php

namespace App\Http\Controllers\Candidate;

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

    public function index(Request $request)
    {
        $resumes = $request->user()->resumes()
            ->with('latestInterviewQuestionSet', 'latestAnalysis')
            ->latest()
            ->get();

        return view('candidate.interview-questions.index', compact('resumes'));
    }

    public function show(Request $request, Resume $resume)
    {
        abort_unless($resume->user_id === $request->user()->id, 403);
        $resume->load('latestAnalysis.jobPosting', 'latestInterviewQuestionSet');

        return view('candidate.interview-questions.show', [
            'resume' => $resume,
            'interviewSet' => $resume->latestInterviewQuestionSet,
        ]);
    }

    public function store(Request $request, Resume $resume)
    {
        abort_unless($resume->user_id === $request->user()->id, 403);

        $validated = $request->validate([
            'job_posting_id' => ['nullable', 'integer'],
        ]);

        $job = null;
        if (! empty($validated['job_posting_id'])) {
            $job = JobPosting::find($validated['job_posting_id']);
        } else {
            $job = $resume->latestAnalysis?->jobPosting;
        }

        try {
            $set = $this->service->generate($resume, $job, $request->user());
        } catch (Throwable $e) {
            return redirect()
                ->route('candidate.interview-questions.show', $resume)
                ->with('error', 'Could not generate interview questions: '.$e->getMessage());
        }

        ActivityLog::record('interview_questions.generated', $resume, [
            'set_id' => $set->id,
            'job_posting_id' => $job?->id,
        ]);

        return redirect()
            ->route('candidate.interview-questions.show', $resume)
            ->with('status', 'Interview questions generated.');
    }
}
