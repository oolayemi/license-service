<?php

namespace App\Http\Middleware;

use App\Models\Brand;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateBrandApi
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken();

        if (! $token) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $brand = Brand::where('api_token', $token)->first();

        if (! $brand) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // Set the brand in request for controllers / policies
        $request->attributes->set('brand', $brand);

        return $next($request);
    }
}
