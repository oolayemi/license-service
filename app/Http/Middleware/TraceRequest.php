<?php

namespace App\Http\Middleware;

use App\Observability\Tracer;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TraceRequest
{
    public function handle(Request $request, Closure $next): Response
    {
        $incomingTraceId = $request->header('X-Trace-Id');

        Tracer::startTrace($incomingTraceId);

        Tracer::addContext([
            'path' => $request->path(),
            'method' => $request->method(),
        ]);

        return $next($request);
    }
}
