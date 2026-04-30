<?php

namespace App\Http\Controllers\Candidate;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PrivacyController extends Controller
{
    public function edit(Request $request)
    {
        return view('candidate.privacy.edit', ['user' => $request->user()]);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'allow_recruiter_access' => ['nullable', 'boolean'],
            'store_resumes' => ['nullable', 'boolean'],
        ]);

        $user = $request->user();
        $user->update([
            'allow_recruiter_access' => (bool) ($validated['allow_recruiter_access'] ?? false),
            'store_resumes' => (bool) ($validated['store_resumes'] ?? false),
        ]);

        // Cascade resume sharing flag.
        $user->resumes()->update(['shared_with_recruiters' => $user->allow_recruiter_access]);

        ActivityLog::record('privacy.updated', $user);

        return back()->with('status', 'Privacy preferences updated.');
    }

    public function purge(Request $request)
    {
        $request->validate(['confirm' => ['accepted']]);

        $disk = config('services.resume.disk', 'local');
        foreach ($request->user()->resumes as $resume) {
            Storage::disk($disk)->delete($resume->stored_path);
            $resume->delete();
        }

        ActivityLog::record('data.purged', $request->user());

        return back()->with('status', 'All your resume data has been deleted.');
    }
}
