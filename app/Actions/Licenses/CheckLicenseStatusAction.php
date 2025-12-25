<?php

namespace App\Actions\Licenses;

use App\Models\LicenseKey;
use App\Observability\Metrics;
use App\Observability\Tracer;
use Throwable;

class CheckLicenseStatusAction
{
    public function __construct() {}

    /**
     * Check the status and entitlements of a license key.
     *
     * @return array{
     *     license_key: string,
     *     customer_email: string|null,
     *     licenses: array<int, array{
     *         license_id: string,
     *         product_code: string,
     *         product_name: string,
     *         status: string,
     *         expires_at: string|null,
     *         max_seats: int|null,
     *         active_seats: int,
     *         remaining_seats: int|null,
     *         is_usable: bool
     *     }>
     * }
     *
     * @throws Throwable
     */
    public function execute(string $licenseKeyValue): array
    {
        Tracer::startSpan('license.status.check', ['license_key' => $licenseKeyValue]);

        try {
            $licenseKey = LicenseKey::with(['licenses.product', 'licenses.activations'])
                ->where('key', $licenseKeyValue)
                ->firstOrFail();

            // Prepare result per license
            $licensesData = $licenseKey->licenses->map(function ($license) {
                $activeSeats = $license->activations->whereNull('deactivated_at')->count();
                $remainingSeats = $license->max_seats ? $license->max_seats - $activeSeats : null;

                return [
                    'license_id' => $license->id,
                    'product_code' => $license->product->code,
                    'product_name' => $license->product->name,
                    'status' => $license->status->value,
                    'expires_at' => $license->expires_at
                        ? $license->expires_at->toDateTimeString()
                        : null,
                    'max_seats' => $license->max_seats,
                    'active_seats' => $activeSeats,
                    'remaining_seats' => $remainingSeats,
                    'is_usable' => $license->isUsable(),
                ];
            })->all();

            // Metrics
            Metrics::increment('license.status.checked', 1, [
                'license_key' => $licenseKeyValue,
                'brand_id' => $licenseKey->brand_id,
            ]);

            Tracer::endSpan('license.status.check', [
                'licenses_count' => $licenseKey->licenses->count(),
            ]);

            return [
                'license_key' => $licenseKey->key,
                'customer_email' => $licenseKey->customer_email,
                'licenses' => $licensesData,
            ];
        } catch (Throwable $e) {
            Tracer::endSpan('license.status.check', [
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }
}
