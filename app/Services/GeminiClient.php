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
                ->retry(max(1, $retries), function (int $attempt, $exception) {
                    // Exponential backoff: 2s, 5s, 10s, 20s …
                    // Longer delays give Google's 503 backend time to recover.
                    $delay = [2000, 5000, 10000, 20000][$attempt - 1] ?? 20000;

                    // Extra pause for 503 — server is overloaded, hammering it faster makes it worse.
                    if ($exception instanceof \Illuminate\Http\Client\RequestException
                        && $exception->response?->status() === 503) {
                        return $delay * 2;
                    }

                    return $delay;
                }, function ($exception) {
                    // Retry on any transient network failure OR on HTTP 503 (server overload).
                    if ($exception instanceof ConnectionException) {
                        return true;
                    }
                    if ($exception instanceof \Illuminate\Http\Client\RequestException) {
                        return $exception->response?->status() === 503;
                    }
                    return false;
                }, throw: true)
                ->withOptions([
                    'expect' => false,
                    'curl' => [
                        // Force HTTP/1.1. Middleboxes and some regional ISPs break HTTP/2 to
                        // Google, causing cURL 52 "Empty reply from server".
                        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,

                        // TCP keepalives prevent NAT/firewall devices from evicting the
                        // connection mid-response on long Gemini generations.
                        CURLOPT_TCP_KEEPALIVE => 1,
                        CURLOPT_TCP_KEEPIDLE  => 10,
                        CURLOPT_TCP_KEEPINTVL => 5,
                    ],
                ])
                ->withHeaders([
                    'x-goog-api-key' => $apiKey,
                    'Expect'         => '',
                    'User-Agent'     => 'Resumelyzer/1.0 (GeminiClient; PHP/'.PHP_MAJOR_VERSION.'.'.PHP_MINOR_VERSION.')',
                ])
                ->acceptJson()
                ->post($url, $body);
        } catch (ConnectionException $e) {
            throw new RuntimeException(
                'Gemini API unreachable after '.max(1, $retries).' attempts: '.$e->getMessage()
                .' — Check your internet connection. If the issue persists, the Generative Language'
                .' endpoint may be blocked or throttled by your ISP.',
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
