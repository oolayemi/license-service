<?php

namespace App\Actions\Licenses;

use App\Exceptions\SeatLimitExceededException;
use App\Models\Activation;
use App\Observability\Metrics;
use App\Observability\Tracer;
use App\Repositories\ActivationRepository;
use App\Services\LicenseValidationService;
use App\Services\SeatManager;
use Illuminate\Support\Str;

class ActivateLicenseAction
{
    public function __construct(
        protected LicenseValidationService $licenseValidator,
        protected SeatManager $seatManager,
        protected ActivationRepository $activationRepo,
    ) {}

    /**
     * Activate a license for a specific instance.
     *
     *
     * @throws \Throwable
     */
    public function execute(string $licenseKey, string $productCode, ?string $instanceIdentifier = null): Activation
    {
        $instanceIdentifier = $instanceIdentifier ?? Str::uuid()->toString();

        // Start span for activation
        Tracer::startSpan('license.activate', [
            'license_key' => $licenseKey,
            'product_code' => $productCode,
            'instance_identifier' => $instanceIdentifier,
        ]);

        try {
            // 1. Validate the license and product
            $license = $this->licenseValidator->validate($licenseKey, $productCode);

            // 2. Check if a seat is available
            if (! $this->seatManager->canActivate($license)) {
                Metrics::event('license.activation.failed', [
                    'license_id' => $license->id,
                    'reason' => 'seat_limit_exceeded',
                ]);
                throw new SeatLimitExceededException;
            }

            // 3. Persist activation
            $activation = $this->seatManager->activate($license, $instanceIdentifier);

            // 4. Metrics
            Metrics::increment('license.activations', 1, [
                'brand_id' => $license->licenseKey->brand_id,
                'product_code' => $productCode,
            ]);

            // Attach extra context to trace
            Tracer::addContext([
                'license_id' => $license->id,
                'activation_id' => $activation->id,
            ]);

            // End span
            Tracer::endSpan('license.activate');

            return $activation;
        } catch (\Throwable $e) {
            Tracer::addContext([
                'error' => $e->getMessage(),
            ]);
            Tracer::endSpan('license.activate');
            throw $e;
        }
    }
}
