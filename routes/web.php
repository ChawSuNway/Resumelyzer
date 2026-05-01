<?php

use App\Http\Controllers\Admin;
use App\Http\Controllers\Candidate;
use App\Http\Controllers\LocaleController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Recruiter;
use App\Models\User;
use App\Services\GeminiClient;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::get('/locale/{locale}', [LocaleController::class, 'switch'])
    ->whereIn('locale', ['en', 'my', 'jp'])
    ->name('locale.switch');


Route::get('/samples/resume', function () {
    $path = public_path('samples/sample_resume.txt');
    abort_unless(file_exists($path), 404);
    return response()->download($path, 'sample_resume.txt', ['Content-Type' => 'text/plain']);
})->name('sample.resume');

Route::get('/test-gemini-api', function () {
    $apiKey = config('services.gemini.key');
    $model  = config('services.gemini.model', 'gemini-2.5-flash');

    if (empty($apiKey)) {
        return response()->json([
            'success' => false,
            'error'   => 'GEMINI_API_KEY is not configured in services.gemini.key.',
            'model'   => $model,
        ], 500);
    }

    try {
        $client = new GeminiClient();
        $result = $client->generateJson('Reply in JSON with a single key "reply" whose value is the word pong.');

        return response()->json([
            'success' => true,
            'model'   => $model,
            'reply'   => $result['reply'] ?? $result,
        ]);
    } catch (\Throwable $e) {
        return response()->json([
            'success' => false,
            'model'   => $model,
            'error'   => $e->getMessage(),
        ], 500);
    }
})->name('test.gemini-api');

Route::get('/dashboard', function () {
    $user = Auth::user();
    if (! $user) {
        return redirect()->route('login');
    }
    return match ($user->role) {
        User::ROLE_ADMIN => redirect()->route('admin.dashboard'),
        User::ROLE_RECRUITER => redirect()->route('recruiter.dashboard'),
        default => redirect()->route('candidate.dashboard'),
    };
})->middleware(['auth'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

/*
|--------------------------------------------------------------------------
| Candidate routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:candidate'])
    ->prefix('candidate')->name('candidate.')
    ->group(function () {
        Route::get('/', Candidate\DashboardController::class)->name('dashboard');

        Route::get('/resumes', [Candidate\ResumeController::class, 'index'])->name('resumes.index');
        Route::get('/resumes/upload', [Candidate\ResumeController::class, 'create'])->name('resumes.create');
        Route::post('/resumes', [Candidate\ResumeController::class, 'store'])->name('resumes.store');
        Route::get('/resumes/{resume}', [Candidate\ResumeController::class, 'show'])->name('resumes.show');
        Route::post('/resumes/{resume}/reanalyze', [Candidate\ResumeController::class, 'reanalyze'])->name('resumes.reanalyze');
        Route::delete('/resumes/{resume}', [Candidate\ResumeController::class, 'destroy'])->name('resumes.destroy');
        Route::get('/resumes/{resume}/download', [Candidate\ResumeController::class, 'download'])->name('resumes.download');

        Route::get('/resumes/{resume}/export/pdf', [Candidate\ExportController::class, 'reportPdf'])->name('resumes.export.pdf');
        Route::get('/resumes/{resume}/export/csv', [Candidate\ExportController::class, 'reportCsv'])->name('resumes.export.csv');
        Route::get('/resumes/{resume}/export/draft', [Candidate\ExportController::class, 'improvedDraft'])->name('resumes.export.draft');

        Route::get('/interview-questions', [Candidate\InterviewQuestionController::class, 'index'])->name('interview-questions.index');
        Route::get('/interview-questions/{resume}', [Candidate\InterviewQuestionController::class, 'show'])->name('interview-questions.show');
        Route::post('/interview-questions/{resume}', [Candidate\InterviewQuestionController::class, 'store'])->name('interview-questions.store');

        Route::get('/privacy', [Candidate\PrivacyController::class, 'edit'])->name('privacy.edit');
        Route::patch('/privacy', [Candidate\PrivacyController::class, 'update'])->name('privacy.update');
        Route::delete('/privacy/purge', [Candidate\PrivacyController::class, 'purge'])->name('privacy.purge');
    });

/*
|--------------------------------------------------------------------------
| Recruiter routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:recruiter'])
    ->prefix('recruiter')->name('recruiter.')
    ->group(function () {
        Route::get('/', Recruiter\DashboardController::class)->name('dashboard');

        Route::get('/candidates', [Recruiter\CandidateController::class, 'index'])->name('candidates.index');
        Route::get('/candidates/{resume}', [Recruiter\CandidateController::class, 'show'])->name('candidates.show');
        Route::get('/candidates/{resume}/download', [Recruiter\CandidateController::class, 'downloadResume'])->name('candidates.download');
        Route::post('/candidates/{resume}/compare', [Recruiter\CandidateController::class, 'compare'])->name('candidates.compare');
        Route::post('/candidates/{resume}/notes', [Recruiter\CandidateController::class, 'storeNote'])->name('candidates.notes.store');
        Route::post('/candidates/{resume}/interview-questions', [Recruiter\InterviewQuestionController::class, 'store'])->name('candidates.interview-questions.store');

        Route::resource('jobs', Recruiter\JobPostingController::class)
            ->except(['show'])
            ->missing(function () {
                return redirect()
                    ->route('recruiter.jobs.index')
                    ->with('error', 'That job posting no longer exists — it may have been deleted.');
            });
    });

/*
|--------------------------------------------------------------------------
| Admin routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:admin'])
    ->prefix('admin')->name('admin.')
    ->group(function () {
        Route::get('/', Admin\DashboardController::class)->name('dashboard');

        Route::get('/users', [Admin\UserController::class, 'index'])->name('users.index');
        Route::get('/users/create', [Admin\UserController::class, 'create'])->name('users.create');
        Route::post('/users', [Admin\UserController::class, 'store'])->name('users.store');
        Route::get('/users/{user}/edit', [Admin\UserController::class, 'edit'])->name('users.edit');
        Route::patch('/users/{user}', [Admin\UserController::class, 'update'])->name('users.update');
        Route::patch('/users/{user}/toggle', [Admin\UserController::class, 'deactivate'])->name('users.toggle');

        Route::get('/settings', [Admin\SettingsController::class, 'edit'])->name('settings.edit');
        Route::patch('/settings', [Admin\SettingsController::class, 'update'])->name('settings.update');
    });

require __DIR__.'/auth.php';
