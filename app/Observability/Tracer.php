<?php

namespace App\Observability;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class Tracer
{
    protected static ?string $traceId = null;

    /**
     * @var array<string|float>
     */
    protected static array $spans = [];

    /**
     * Initialize a trace for the current request.
     */
    public static function startTrace(?string $traceId = null): string
    {
        self::$traceId = $traceId ?? (string) Str::uuid();

        Log::withContext([
            'trace_id' => self::$traceId,
        ]);

        return self::$traceId;
    }

    /**
     * Start a span.
     *
     * @param  array<string, mixed>  $context
     */
    public static function startSpan(string $name, array $context = []): void
    {
        //        self::traceId() ?? self::startTrace();
        self::$spans[$name] = microtime(true);

        Log::debug('span.start', [
            'trace_id' => self::$traceId,
            'span' => $name,
            'context' => $context,
        ]);
    }

    /**
     * End a span.
     *
     * @param  array<string, mixed>  $context
     */
    public static function endSpan(string $name, array $context = []): void
    {
        if (! isset(self::$spans[$name])) {
            return;
        }

        $durationMs = (microtime(true) - floatval(self::$spans[$name])) * 1000;
        unset(self::$spans[$name]);

        Log::debug('span.end', [
            'trace_id' => self::$traceId,
            'span' => $name,
            'duration_ms' => round($durationMs, 2),
            'context' => $context,
        ]);
    }

    /**
     * Attach structured context to the trace.
     *
     * @param  array<string, mixed>  $context
     */
    public static function addContext(array $context): void
    {
        Log::withContext(array_merge(
            ['trace_id' => self::$traceId],
            $context
        ));
    }

    /**
     * Get current trace ID.
     */
    public static function traceId(): ?string
    {
        return self::$traceId;
    }
}
