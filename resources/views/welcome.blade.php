<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Resumelyzer — Smart Resume Analyzer</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="antialiased bg-gradient-to-br from-indigo-50 via-white to-emerald-50 min-h-screen">
<div class="max-w-6xl mx-auto px-6 py-10">
    <header class="flex items-center justify-between">
        <div class="text-2xl font-bold text-indigo-600 tracking-tight">Resumelyzer</div>
        <nav class="space-x-4">
            @auth
                <a href="{{ route('dashboard') }}" class="text-sm font-medium text-gray-700 hover:text-indigo-600">Dashboard</a>
            @else
                <a href="{{ route('login') }}" class="text-sm font-medium text-gray-700 hover:text-indigo-600">Log in</a>
                <a href="{{ route('register') }}" class="px-4 py-2 rounded-md bg-indigo-600 text-white text-sm font-medium hover:bg-indigo-700">Get started</a>
            @endauth
        </nav>
    </header>

    <main class="mt-16">
        <section class="text-center max-w-3xl mx-auto">
            <h1 class="text-5xl font-bold text-gray-900 leading-tight">Make every resume.</h1>
            <p class="mt-4 text-lg text-gray-600">Upload a PDF, DOCX, or TXT resume and get an instant breakdown — ATS compatibility, skills match, readability, and concrete suggestions powered by Google Gemini.</p>
            <div class="mt-8 flex justify-center gap-3">
                <a href="{{ route('register') }}" class="px-6 py-3 rounded-md bg-indigo-600 text-white font-semibold hover:bg-indigo-700 shadow-sm">Analyze a resume</a>
                <a href="{{ route('login') }}" class="px-6 py-3 rounded-md bg-white border border-gray-200 text-gray-700 font-semibold hover:bg-gray-50">I have an account</a>
            </div>
        </section>

        <section class="mt-20 grid md:grid-cols-3 gap-6">
            @foreach ([
                ['Candidates', 'Upload, score, and get section-by-section feedback. Decide who can see your resume.'],
                ['Recruiters', 'Search shared resumes, compare against your job posts, and capture notes & ratings.'],
                ['Admins', 'Manage users, scoring rules, ATS keyword libraries, and retention policies.'],
            ] as $card)
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <h3 class="text-lg font-semibold text-gray-900">{{ $card[0] }}</h3>
                    <p class="mt-2 text-sm text-gray-600">{{ $card[1] }}</p>
                </div>
            @endforeach
        </section>

        <section class="mt-16 text-center text-sm text-gray-500">
            @Copyright 2026 Resumelyzer. All rights reserved.
        </section>
    </main>
</div>
</body>
</html>
