<?php

namespace App\Http\Controllers\Recruiter;

use App\Http\Controllers\Controller;
use App\Models\JobPosting;
use Illuminate\Http\Request;

class JobPostingController extends Controller
{
    public function index(Request $request)
    {
        $jobs = JobPosting::where('recruiter_id', $request->user()->id)
            ->latest()->paginate(15);
        return view('recruiter.jobs.index', compact('jobs'));
    }

    public function create()
    {
        return view('recruiter.jobs.create');
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['recruiter_id'] = $request->user()->id;
        $data['keywords'] = $this->splitKeywords($request->input('keywords_raw'));

        JobPosting::create($data);

        return redirect()->route('recruiter.jobs.index')->with('status', 'Job posting created.');
    }

    public function edit(Request $request, JobPosting $job)
    {
        $this->authorizeOwner($request, $job);
        return view('recruiter.jobs.edit', compact('job'));
    }

    public function update(Request $request, JobPosting $job)
    {
        $this->authorizeOwner($request, $job);

        $data = $this->validated($request);
        $data['keywords'] = $this->splitKeywords($request->input('keywords_raw'));
        $job->update($data);

        return redirect()->route('recruiter.jobs.index')->with('status', 'Job posting updated.');
    }

    public function destroy(Request $request, JobPosting $job)
    {
        $this->authorizeOwner($request, $job);
        $job->delete();
        return back()->with('status', 'Job posting deleted.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'company' => ['nullable', 'string', 'max:255'],
            'location' => ['nullable', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ]) + ['is_active' => (bool) $request->input('is_active', true)];
    }

    private function splitKeywords(?string $raw): array
    {
        if (! $raw) {
            return [];
        }
        return collect(preg_split('/[,\n]+/', $raw))
            ->map(fn ($s) => trim($s))
            ->filter()->values()->all();
    }

    private function authorizeOwner(Request $request, JobPosting $job): void
    {
        abort_unless($job->recruiter_id === $request->user()->id, 403);
    }
}
