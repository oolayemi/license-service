<?php

namespace App\Observability;

use Illuminate\Support\Facades\Log;

class Metrics
{
    /**
     * Increment a counter metric.
     * @param string $name
     * @param int $value
     * @param array<int, string>|array<string, mixed> $tags
     * @return void
     */
    public static function increment(
        string $name,
        int $value = 1,
        array $tags = []
    ): void {
        Log::info('metric.increment', [
            'metric' => $name,
            'value' => $value,
            'tags' => $tags,
            'type' => 'counter',
        ]);
    }

    /**
     * Record a timing metric (milliseconds).
     * @param string $name
     * @param float $milliseconds
     * @param array<int, string>|array<string, mixed> $tags
     * @return void
     */
    public static function timing(
        string $name,
        float $milliseconds,
        array $tags = []
    ): void {
        Log::info('metric.timing', [
            'metric' => $name,
            'value_ms' => round($milliseconds, 2),
            'tags' => $tags,
            'type' => 'timing',
        ]);
    }

    /**
     * Track an event (e.g., license activated, seat exceeded)
     * @param string $name
     * @param array<int, string>|array<string, mixed> $payload
     * @return void
     */
    public static function event(string $name, array $payload = []): void
    {
        Log::info("[METRIC] Event: {$name} | payload: ".json_encode($payload));
    }

    /**
     * Record a gauge metric.
     * @param string $name
     * @param float|int $value
     * @param array<int, string>|array<string, mixed> $tags
     * @return void
     */
    public static function gauge(
        string $name,
        float|int $value,
        array $tags = []
    ): void {
        Log::info('metric.gauge', [
            'metric' => $name,
            'value' => $value,
            'tags' => $tags,
            'type' => 'gauge',
        ]);
    }
}
