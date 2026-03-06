<?php

namespace App\Services\Monitoring;

use Illuminate\Support\Facades\Http;

final class HttpPingResult
{
    public function __construct(
        public ?int $statusCode,
        public ?int $responseTimeMs,
        public string $status, // 'up', 'down', 'timeout'
        public ?string $errorMessage = null
    ) {}
}

class HttpPingService
{
    public function ping(string $url): HttpPingResult
    {
        $timeout = config('pulseguard.http.timeout', 10);
        $userAgent = config('pulseguard.http.user_agent', 'PulseGuard-Uptime-Monitor/1.0');

        $start = microtime(true);

        try {
            $response = Http::timeout($timeout)
                ->withHeaders(['User-Agent' => $userAgent])
                ->connectTimeout(5)
                ->get($url);

            $responseTimeMs = (int) round((microtime(true) - $start) * 1000);

            $statusCode = $response->status();
            $status = $statusCode >= 200 && $statusCode < 300 ? 'up' : 'down';

            return new HttpPingResult(
                statusCode: $statusCode,
                responseTimeMs: $responseTimeMs,
                status: $status,
                errorMessage: $status === 'down' ? "HTTP {$statusCode}" : null
            );
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            $responseTimeMs = (int) round((microtime(true) - $start) * 1000);

            return new HttpPingResult(
                statusCode: null,
                responseTimeMs: $responseTimeMs,
                status: 'timeout',
                errorMessage: $e->getMessage()
            );
        } catch (\Throwable $e) {
            $responseTimeMs = (int) round((microtime(true) - $start) * 1000);

            return new HttpPingResult(
                statusCode: null,
                responseTimeMs: $responseTimeMs,
                status: 'down',
                errorMessage: $e->getMessage()
            );
        }
    }
}
