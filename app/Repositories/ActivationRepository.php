<?php

namespace App\Repositories;

use App\Models\Activation;
use App\Models\License;
use Illuminate\Support\Collection;

class ActivationRepository
{
    /**
     * Find active activation for a license + instance.
     *
     * Prevents duplicate activations.
     */
    public function findActiveByInstance(
        License $license,
        string $instanceIdentifier
    ): ?Activation {
        return Activation::where('license_id', $license->id)
            ->where('instance_identifier', $instanceIdentifier)
            ->whereNull('deactivated_at')
            ->first();
    }

    /**
     * Count active activations (seats used).
     */
    public function countActive(License $license): int
    {
        return Activation::where('license_id', $license->id)
            ->whereNull('deactivated_at')
            ->count();
    }

    /**
     * Create new activation.
     */
    public function create(
        License $license,
        string $instanceIdentifier
    ): Activation {
        return Activation::create([
            'license_id' => $license->id,
            'instance_identifier' => $instanceIdentifier,
            'activated_at' => now(),
        ]);
    }

    /**
     * Deactivate a specific activation.
     */
    public function deactivate(
        Activation $activation
    ): Activation {
        $activation->update([
            'deactivated_at' => now(),
        ]);

        return $activation;
    }

    /**
     * Deactivate by license and instance identifier.
     */
    public function deactivateByInstance(
        License $license,
        string $instanceIdentifier
    ): bool {
        return Activation::where('license_id', $license->id)
            ->where('instance_identifier', $instanceIdentifier)
            ->whereNull('deactivated_at')
            ->update([
                'deactivated_at' => now(),
            ]) > 0;
    }

    /**
     * List all active activations for a license.
     *
     * @return Collection<int, Activation>
     */
    public function listActive(License $license): Collection
    {
        return Activation::where('license_id', $license->id)
            ->whereNull('deactivated_at')
            ->get();
    }
}
