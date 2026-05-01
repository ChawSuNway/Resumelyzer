<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
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
        private readonly ?int $connectTimeout = null,
        private readonly ?int $retries = null,
    ) {
    }

    public function generateJson(string $prompt, array $schema = []): array
    {
        $apiKey = $this->apiKey ?? config('services.gemini.key');
        $model = $this->model ?? config('services.gemini.model', 'gemini-2.5-flash');
        $baseUrl = rtrim($this->baseUrl ?? config('services.gemini.base_url'), '/');
        $timeout = $this->timeout ?? config('services.gemini.timeout', 60);
        $connectTimeout = $this->connectTimeout ?? config('services.gemini.connect_timeout', 30);
        $retries = $this->retries ?? config('services.gemini.retries', 3);

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

        try {
            /** @var Response $response */
            $response = Http::timeout($timeout)
                ->connectTimeout($connectTimeout)
                ->retry(max(1, $retries), 1500, function ($exception) {
                    // Retry only on transient network failures, not on 4xx/5xx HTTP responses.
                    return $exception instanceof ConnectionException;
                }, throw: true)
                ->withOptions([
                    // Disable "Expect: 100-continue" — Guzzle adds it for bodies >1KB, but
                    // middleboxes and some Google frontends drop the connection instead of
                    // replying, which surfaces as cURL 52 "Empty reply from server".
                    'expect' => false,
                    'curl' => [
                        // Force HTTP/1.1 — middleboxes/ISPs in some regions break HTTP/2 to Google,
                        // which surfaces as cURL 52 "Empty reply from server".
                        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                        // Keep the TCP connection alive while Gemini is generating the response.
                        // Without this, residential/mobile NATs drop idle sockets after ~30s and
                        // the next read returns 0 bytes (cURL 52).
                        CURLOPT_TCP_KEEPALIVE => 1,
                        CURLOPT_TCP_KEEPIDLE  => 15,
                        CURLOPT_TCP_KEEPINTVL => 15,
                    ],
                ])
                ->withHeaders([
                    'x-goog-api-key' => $apiKey,
                    // Belt-and-braces: explicitly empty Expect overrides any client default.
                    'Expect' => '',
                ])
                ->acceptJson()
                ->post($url, $body);
        } catch (ConnectionException $e) {
            throw new RuntimeException(
                'Gemini API unreachable after '.max(1, $retries).' attempts: '.$e->getMessage().
                ' — check your internet connection or try again; the Generative Language endpoint may be intermittently blocked.',
                0,
                $e
            );
        }

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
