<?php

namespace App\Http\Controllers\Candidate;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\JobPosting;
use App\Models\Resume;
use App\Services\ResumeAnalysisService;
use App\Services\ResumeTextExtractor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ResumeController extends Controller
{
    public function __construct(
        private readonly ResumeTextExtractor $extractor,
        private readonly ResumeAnalysisService $analyzer,
    ) {
    }

    public function index(Request $request)
    {
        $resumes = $request->user()->resumes()->with('latestAnalysis')->latest()->get();
        return view('candidate.resumes.index', compact('resumes'));
    }

    public function create(Request $request)
    {
        $jobs = JobPosting::where('is_active', true)->latest()->get();
        return view('candidate.resumes.create', compact('jobs'));
    }

    public function store(Request $request)
    {
        $maxKb = (int) config('services.resume.max_size_kb', 10240);

        $validated = $request->validate([
            'resume' => ['required', 'file', "max:{$maxKb}", 'mimes:pdf,doc,docx,txt'],
            'job_posting_id' => ['nullable', 'exists:job_postings,id'],
        ]);

        $file = $validated['resume'];
        $extension = strtolower($file->getClientOriginalExtension());
        $disk = config('services.resume.disk', 'local');

        $tmpPath = $file->getRealPath();
        $extracted = $this->extractor->extract($tmpPath, $extension);

        $rawContent = file_get_contents($tmpPath);
        $encrypted = Crypt::encrypt($rawContent);

        $storedName = 'resumes/'.$request->user()->id.'/'.Str::uuid().'.enc';
        Storage::disk($disk)->put($storedName, $encrypted);

        $retentionDays = (int) config('services.resume.retention_days', 30);

        $resume = Resume::create([
            'user_id' => $request->user()->id,
            'original_filename' => $file->getClientOriginalName(),
            'stored_path' => $storedName,
            'mime_type' => $file->getClientMimeType(),
            'extension' => $extension,
            'size_bytes' => $file->getSize(),
            'extracted_text' => $extracted,
            'is_encrypted' => true,
            'shared_with_recruiters' => $request->user()->allow_recruiter_access,
            'purge_after' => $request->user()->store_resumes ? null : now()->addDays($retentionDays),
        ]);

        $job = $validated['job_posting_id'] ? JobPosting::find($validated['job_posting_id']) : null;
        $analysis = $this->analyzer->analyze($resume, $job);

        ActivityLog::record('resume.uploaded', $resume, ['filename' => $resume->original_filename]);

        return redirect()
            ->route('candidate.resumes.show', $resume)
            ->with('status', $analysis->status === 'completed' ? 'Resume analyzed.' : 'Resume uploaded but analysis failed: '.$analysis->error);
    }

    public function show(Request $request, Resume $resume)
    {
        $this->authorizeOwner($request, $resume);
        $resume->load('analyses', 'latestAnalysis');
        return view('candidate.resumes.show', ['resume' => $resume, 'analysis' => $resume->latestAnalysis]);
    }

    public function reanalyze(Request $request, Resume $resume)
    {
        $this->authorizeOwner($request, $resume);

        $request->validate(['job_posting_id' => ['nullable', 'exists:job_postings,id']]);
        $job = $request->input('job_posting_id') ? JobPosting::find($request->input('job_posting_id')) : null;

        $analysis = $this->analyzer->analyze($resume, $job);

        return redirect()
            ->route('candidate.resumes.show', $resume)
            ->with('status', $analysis->status === 'completed' ? 'Resume re-analyzed.' : 'Re-analysis failed: '.$analysis->error);
    }

    public function destroy(Request $request, Resume $resume)
    {
        $this->authorizeOwner($request, $resume);

        Storage::disk(config('services.resume.disk', 'local'))->delete($resume->stored_path);
        $resume->delete();

        ActivityLog::record('resume.deleted', null, ['resume_id' => $resume->id]);

        return redirect()->route('candidate.resumes.index')->with('status', 'Resume permanently deleted.');
    }

    public function download(Request $request, Resume $resume)
    {
        $this->authorizeOwner($request, $resume);
        $disk = config('services.resume.disk', 'local');
        $encrypted = Storage::disk($disk)->get($resume->stored_path);
        $decrypted = Crypt::decrypt($encrypted);

        return response($decrypted, 200, [
            'Content-Type' => $resume->mime_type,
            'Content-Disposition' => 'attachment; filename="'.$resume->original_filename.'"',
        ]);
    }

    private function authorizeOwner(Request $request, Resume $resume): void
    {
        abort_unless($resume->user_id === $request->user()->id, 403);
    }
}
