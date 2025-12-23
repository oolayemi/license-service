<?php

namespace App\Services;

use App\Exceptions\SeatLimitExceededException;
use App\Models\Activation;
use App\Models\License;

class SeatManager
{
    /**
     * Determine whether a new activation can be created.
     */
    public function canActivate(License $license): bool
    {
        if ($license->max_seats === null) {
            return true; // unlimited seats
        }

        return $this->activeSeatCount($license) < $license->max_seats;
    }

    /**
     * Register new activation (seat).
     *
     * @throws SeatLimitExceededException
     */
    public function activate(License $license, string $instanceIdentifier): Activation
    {
        if (! $this->canActivate($license)) {
            throw new SeatLimitExceededException("Seats limit exceeded");
        }

        return Activation::create([
            'license_id' => $license->id,
            'instance_identifier' => $instanceIdentifier,
            'activated_at' => now(),
        ]);
    }

    /**
     * Deactivate existing activation.
     */
    public function deactivate(Activation $activation): void
    {
        $activation->update([
            'deactivated_at' => now(),
        ]);
    }

    /**
     * Count currently active seats.
     */
    protected function activeSeatCount(License $license): int
    {
        return Activation::query()
            ->where('license_id', $license->id)
            ->whereNull('deactivated_at')
            ->count();
    }
}
