<?php

namespace App\Services;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class GeminiClient
{
    public function __construct(
        private readonly ?string $apiKey = null,
        private readonly ?string $model = null,
        private readonly ?string $baseUrl = null,
        private readonly ?int $timeout = null,
    ) {
    }

    public function generateJson(string $prompt, array $schema = []): array
    {
        $apiKey = $this->apiKey ?? config('services.gemini.key');
        $model = $this->model ?? config('services.gemini.model', 'gemini-2.5-flash');
        $baseUrl = rtrim($this->baseUrl ?? config('services.gemini.base_url'), '/');
        $timeout = $this->timeout ?? config('services.gemini.timeout', 60);

        if (! $apiKey) {
            throw new RuntimeException('GEMINI_API_KEY is not configured.');
        }

        $url = "{$baseUrl}/models/{$model}:generateContent";

        $body = [
            'contents' => [[
                'role' => 'user',
                'parts' => [['text' => $prompt]],
            ]],
            'generationConfig' => [
                'temperature' => 0.2,
                'response_mime_type' => 'application/json',
            ],
        ];

        if (! empty($schema)) {
            $body['generationConfig']['response_schema'] = $schema;
        }

        /** @var Response $response */
        $response = Http::timeout($timeout)
            ->withHeaders(['x-goog-api-key' => $apiKey])
            ->acceptJson()
            ->post($url, $body);

        if (! $response->successful()) {
            throw new RuntimeException('Gemini API error: '.$response->status().' '.$response->body());
        }

        $payload = $response->json();
        $text = $payload['candidates'][0]['content']['parts'][0]['text'] ?? null;

        if (! $text) {
            throw new RuntimeException('Empty Gemini response: '.json_encode($payload));
        }

        $decoded = json_decode($text, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            // Try to salvage embedded JSON.
            if (preg_match('/\{.*\}/s', $text, $m)) {
                $decoded = json_decode($m[0], true);
            }
        }

        if (! is_array($decoded)) {
            throw new RuntimeException('Failed to parse Gemini JSON: '.$text);
        }

        return $decoded;
    }
}
