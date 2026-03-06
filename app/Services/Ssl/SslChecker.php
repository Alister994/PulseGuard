<?php

namespace App\Services\Ssl;

use App\Events\SslExpiringSoon;
use App\Models\MonitoredSite;
use App\Models\SslCheck;
use Carbon\Carbon;

class SslChecker
{
    protected int $alertDaysBefore;

    public function __construct()
    {
        $this->alertDaysBefore = config('pulseguard.ssl.alert_days_before', 30);
    }

    /**
     * Check SSL certificate for the given site and persist result.
     */
    public function check(MonitoredSite $site): SslCheck
    {
        $host = $this->extractHost($site->url);
        $result = $this->fetchCertificateInfo($host);

        $check = $site->sslChecks()->create([
            'is_valid' => $result['valid'],
            'valid_from' => isset($result['valid_from']) ? Carbon::createFromTimestamp($result['valid_from']->getTimestamp()) : null,
            'valid_until' => isset($result['valid_until']) ? Carbon::createFromTimestamp($result['valid_until']->getTimestamp()) : null,
            'issuer' => $result['issuer'] ?? null,
            'error_message' => $result['error'] ?? null,
            'checked_at' => now(),
        ]);

        if ($result['valid'] && isset($result['valid_until'])) {
            $validUntil = $result['valid_until'];
            $daysUntil = (int) now()->diffInDays($validUntil, false);
            if ($daysUntil <= $this->alertDaysBefore && $daysUntil > 0) {
                event(new SslExpiringSoon($site, $check));
            }
        }

        return $check;
    }

    protected function extractHost(string $url): string
    {
        $parsed = parse_url($url);
        $host = $parsed['host'] ?? $url;
        $port = $parsed['port'] ?? 443;

        return $port === 443 ? $host : "{$host}:{$port}";
    }

    /**
     * @return array{valid: bool, valid_from?: \DateTimeImmutable, valid_until?: \DateTimeImmutable, issuer?: string, error?: string}
     */
    protected function fetchCertificateInfo(string $host): array
    {
        $timeout = config('pulseguard.ssl.timeout', 10);

        $context = stream_context_create([
            'ssl' => [
                'capture_peer_cert' => true,
                'verify_peer' => false,
                'verify_peer_name' => false,
            ],
        ]);

        $errNo = 0;
        $errStr = '';

        $client = @stream_socket_client(
            "ssl://{$host}",
            $errNo,
            $errStr,
            $timeout,
            STREAM_CLIENT_CONNECT,
            $context
        );

        if ($client === false) {
            return [
                'valid' => false,
                'error' => $errStr ?: 'Could not connect to host',
            ];
        }

        $params = stream_context_get_params($client);
        fclose($client);

        $cert = $params['options']['ssl']['peer_certificate'] ?? null;

        if (! $cert) {
            return [
                'valid' => false,
                'error' => 'No certificate received',
            ];
        }

        $info = openssl_x509_parse($cert);

        if ($info === false) {
            return [
                'valid' => false,
                'error' => 'Failed to parse certificate',
            ];
        }

        $validFrom = \DateTimeImmutable::createFromFormat('U', (string) $info['validFrom_time_t']);
        $validUntil = \DateTimeImmutable::createFromFormat('U', (string) $info['validTo_time_t']);
        $issuer = $info['issuer']['O'] ?? $info['issuer']['CN'] ?? null;

        $valid = $validUntil && $validUntil->getTimestamp() > time();

        return [
            'valid' => $valid,
            'valid_from' => $validFrom,
            'valid_until' => $validUntil,
            'issuer' => is_string($issuer) ? $issuer : null,
            'error' => $valid ? null : 'Certificate expired or invalid',
        ];
    }
}
