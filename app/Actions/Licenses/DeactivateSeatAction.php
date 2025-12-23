<?php

namespace App\Actions\Licenses;

use App\Models\Activation;
use App\Observability\Metrics;
use App\Observability\Tracer;
use App\Services\SeatManager;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class DeactivateSeatAction
{
    public function __construct(
        protected SeatManager $seatManager,
    ) {}

    /**
     * Deactivate specific activation by its ID.
     *
     *
     * @throws \Exception
     */
    public function execute(string $activationId): Activation
    {
        // Start trace
        Tracer::startSpan('activation.deactivate', ['activation_id' => $activationId]);

        try {
            $activation = Activation::find($activationId);

            if (! $activation) {
                throw new ModelNotFoundException('Activation record not found');
            }

            if (! $activation->isActive()) {
                throw new \Exception('Activation is already deactivated.');
            }

            $this->seatManager->deactivate($activation);

            // Metrics
            Metrics::increment('license.seat.deactivated', 1, [
                'license_id' => $activation->license_id,
            ]);

            // Add trace context
            Tracer::addContext([
                'license_id' => $activation->license_id,
                'instance_identifier' => $activation->instance_identifier,
            ]);

            // End span
            Tracer::endSpan('activation.deactivate');

            return $activation;
        } catch (\Exception $e) {
            Tracer::endSpan('activation.deactivate', [
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }
}
