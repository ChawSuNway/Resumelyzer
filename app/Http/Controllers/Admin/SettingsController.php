<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SystemSetting;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function edit()
    {
        $settings = [
            'mandatory_sections' => SystemSetting::get('mandatory_sections', []),
            'generic_terms_blocklist' => SystemSetting::get('generic_terms_blocklist', []),
            'ats_keyword_library' => SystemSetting::get('ats_keyword_library', []),
            'scoring_weights' => SystemSetting::get('scoring_weights', [
                'ats' => 30, 'readability' => 20, 'skills' => 30, 'professionalism' => 20,
            ]),
            'retention_days' => SystemSetting::get('retention_days', 30),
        ];
        return view('admin.settings.edit', compact('settings'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'mandatory_sections' => ['nullable', 'string'],
            'generic_terms_blocklist' => ['nullable', 'string'],
            'ats_keyword_library' => ['nullable', 'string'],
            'weight_ats' => ['required', 'integer', 'min:0', 'max:100'],
            'weight_readability' => ['required', 'integer', 'min:0', 'max:100'],
            'weight_skills' => ['required', 'integer', 'min:0', 'max:100'],
            'weight_professionalism' => ['required', 'integer', 'min:0', 'max:100'],
            'retention_days' => ['required', 'integer', 'min:1', 'max:365'],
        ]);

        SystemSetting::put('mandatory_sections', $this->splitList($validated['mandatory_sections'] ?? ''));
        SystemSetting::put('generic_terms_blocklist', $this->splitList($validated['generic_terms_blocklist'] ?? ''));

        $library = json_decode($validated['ats_keyword_library'] ?? '{}', true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($library)) {
            SystemSetting::put('ats_keyword_library', $library);
        }

        SystemSetting::put('scoring_weights', [
            'ats' => (int) $validated['weight_ats'],
            'readability' => (int) $validated['weight_readability'],
            'skills' => (int) $validated['weight_skills'],
            'professionalism' => (int) $validated['weight_professionalism'],
        ]);

        SystemSetting::put('retention_days', (int) $validated['retention_days']);

        return back()->with('status', 'Settings updated.');
    }

    private function splitList(string $raw): array
    {
        return collect(preg_split('/[,\n]+/', $raw))
            ->map(fn ($s) => trim($s))->filter()->values()->all();
    }
}
