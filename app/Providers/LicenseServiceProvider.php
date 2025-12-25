<?php

namespace App\Providers;

use App\Models\License;
use App\Policies\LicensePolicy;
use App\Repositories\ActivationRepository;
use App\Repositories\LicenseRepository;
use App\Services\LicenseKeyGenerator;
use App\Services\LicenseValidationService;
use App\Services\SeatManager;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class LicenseServiceProvider extends ServiceProvider
{
    /**
     * Register application services.
     */
    public function register(): void
    {
        /*
        |--------------------------------------------------------------------------
        | Repositories
        |--------------------------------------------------------------------------
        | Abstract data access so domain logic does not depend on Eloquent directly.
        | This also makes testing and future refactors easier.
        */

        $this->app->bind(LicenseRepository::class, function ($app) {
            return new LicenseRepository;
        });

        $this->app->bind(ActivationRepository::class, function ($app) {
            return new ActivationRepository;
        });

        /*
        |--------------------------------------------------------------------------
        | Domain Services
        |--------------------------------------------------------------------------
        */

        $this->app->singleton(LicenseKeyGenerator::class, function ($app) {
            return new LicenseKeyGenerator;
        });

        $this->app->singleton(LicenseValidationService::class, function ($app) {
            return new LicenseValidationService;
        });

        $this->app->singleton(SeatManager::class, function ($app) {
            return new SeatManager;
        });
    }

    /**
     * Bootstrap application services.
     */
    public function boot(): void
    {
        /*
        |--------------------------------------------------------------------------
        | Authorization Policies
        |--------------------------------------------------------------------------
        | Used primarily for brand-facing APIs.
        */

        Gate::policy(License::class, LicensePolicy::class);

        /*
        |--------------------------------------------------------------------------
        | Model Observers / Domain Hooks (Optional but Valuable)
        |--------------------------------------------------------------------------
        | Useful for auditing, metrics, or async processing later.
        */

        License::created(function (License $license) {
            Log::info('License created', [
                'license_id' => $license->id,
                'product_id' => $license->product_id,
                'status' => $license->status,
            ]);
        });

        License::updated(function (License $license) {
            Log::info('License updated', [
                'license_id' => $license->id,
                'status' => $license->status,
                'expires_at' => $license->expires_at,
            ]);
        });

        $this->configureRateLimiting();
        $this->registerHealthChecks();
    }

    /**
     * Configure rate limiters for product-facing APIs.
     */
    protected function configureRateLimiting(): void
    {
        /*
         |--------------------------------------------------------------------------
         | Product API Rate Limiting
         |--------------------------------------------------------------------------
         | End-user products (plugins, apps, CLIs) can be high traffic.
         | We rate-limit primarily by license key, falling back to IP.
         */

        RateLimiter::for('product', function (Request $request) {
            $licenseKey = $request->input('license_key')
                ?? $request->query('license_key');

            return [
                Limit::perMinute(60)
                    ->by($licenseKey ?: $request->ip())
                    ->response(function () {
                        return response()->json([
                            'error' => 'Too many requests',
                            'message' => 'Rate limit exceeded for license validation',
                        ], 429);
                    }),
            ];
        });

        /*
         |--------------------------------------------------------------------------
         | Brand API Rate Limiting
         |--------------------------------------------------------------------------
         | Brand systems are trusted but still rate-limited to avoid abuse.
         */

        RateLimiter::for('brand', function (Request $request) {
            $brandId = $request->attributes->get('brand_id');

            return Limit::perMinute(120)
                ->by($brandId ?: $request->ip());
        });
    }

    /**
     * Register basic health checks and boot-time observability.
     */
    protected function registerHealthChecks(): void
    {
        /*
         |--------------------------------------------------------------------------
         | Boot Log
         |--------------------------------------------------------------------------
         | Useful in containerized or orchestrated environments.
         */

        Log::info('License Service booted', [
            'environment' => app()->environment(),
            'version' => config('app.version'),
        ]);
    }
}
