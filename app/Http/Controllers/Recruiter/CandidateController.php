<?php

namespace App\Http\Controllers\Recruiter;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\JobPosting;
use App\Models\RecruiterNote;
use App\Models\Resume;
use App\Models\User;
use App\Services\ResumeAnalysisService;
use Illuminate\Http\Request;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Storage;

class CandidateController extends Controller
{
    public function __construct(private readonly ResumeAnalysisService $analyzer)
    {
    }

    public function index(Request $request)
    {
        $query = Resume::query()
            ->where('shared_with_recruiters', true)
            ->with(['user', 'latestAnalysis']);

        if ($search = $request->string('q')->trim()->toString()) {
            $query->where(function ($q) use ($search) {
                $q->where('extracted_text', 'like', "%{$search}%")
                  ->orWhereHas('user', fn ($u) => $u->where('name', 'like', "%{$search}%")
                                                    ->orWhere('email', 'like', "%{$search}%"));
            });
        }

        if ($minScore = (int) $request->input('min_score')) {
            $query->whereHas('latestAnalysis', fn ($a) => $a->where('overall_score', '>=', $minScore));
        }

        $resumes = $query->latest()->paginate(15)->withQueryString();

        return view('recruiter.candidates.index', [
            'resumes' => $resumes,
            'search' => $search,
            'minScore' => $request->input('min_score'),
        ]);
    }

    public function show(Request $request, Resume $resume)
    {
        abort_unless($resume->shared_with_recruiters, 403);
        $resume->load(['user', 'analyses', 'latestAnalysis']);

        $notes = RecruiterNote::where('candidate_id', $resume->user_id)
            ->where('recruiter_id', $request->user()->id)
            ->latest()->get();

        $jobs = JobPosting::where('recruiter_id', $request->user()->id)
            ->where('is_active', true)->get();

        return view('recruiter.candidates.show', compact('resume', 'notes', 'jobs'));
    }

    public function downloadResume(Request $request, Resume $resume)
    {
        abort_unless($resume->shared_with_recruiters, 403);
        $disk = config('services.resume.disk', 'local');

        if (! Storage::disk($disk)->exists($resume->stored_path)) {
            return redirect()
                ->route('recruiter.candidates.show', $resume)
                ->with('error', 'The original file is no longer available on the server. It may have been purged or removed.');
        }

        $encrypted = Storage::disk($disk)->get($resume->stored_path);

        try {
            $decrypted = Crypt::decrypt($encrypted);
        } catch (DecryptException $e) {
            return redirect()
                ->route('recruiter.candidates.show', $resume)
                ->with('error', 'Unable to decrypt this file. It may have been encrypted with a previous APP_KEY.');
        }

        ActivityLog::record('recruiter.resume_downloaded', $resume);

        return response($decrypted, 200, [
            'Content-Type' => $resume->mime_type,
            'Content-Disposition' => 'attachment; filename="'.$resume->original_filename.'"',
        ]);
    }

    public function compare(Request $request, Resume $resume)
    {
        abort_unless($resume->shared_with_recruiters, 403);

        $request->validate(['job_posting_id' => ['required', 'exists:job_postings,id']]);

        $job = JobPosting::where('id', $request->input('job_posting_id'))
            ->where('recruiter_id', $request->user()->id)
            ->firstOrFail();

        $analysis = $this->analyzer->analyze($resume, $job);

        ActivityLog::record('recruiter.compared', $resume, ['job_posting_id' => $job->id]);

        return redirect()
            ->route('recruiter.candidates.show', $resume)
            ->with('status', "Compared against \"{$job->title}\" — fit score {$analysis->overall_score}.");
    }

    public function storeNote(Request $request, Resume $resume)
    {
        abort_unless($resume->shared_with_recruiters, 403);

        $validated = $request->validate([
            'rating' => ['nullable', 'integer', 'min:1', 'max:5'],
            'note' => ['nullable', 'string', 'max:2000'],
        ]);

        RecruiterNote::create([
            'recruiter_id' => $request->user()->id,
            'candidate_id' => $resume->user_id,
            'resume_id' => $resume->id,
            'rating' => $validated['rating'] ?? null,
            'note' => $validated['note'] ?? null,
        ]);

        return back()->with('status', 'Note saved.');
    }
}
