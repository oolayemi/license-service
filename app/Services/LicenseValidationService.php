<?php

namespace App\Services;

use App\Enums\LicenseStatus;
use App\Exceptions\LicenseExpiredException;
use App\Exceptions\LicenseNotFoundException;
use App\Exceptions\LicenseSuspendedException;
use App\Models\License;
use App\Models\LicenseKey;

class LicenseValidationService
{
    /**
     * Validate that a license exists and is usable.
     *
     * @throws LicenseNotFoundException
     * @throws LicenseExpiredException
     * @throws LicenseSuspendedException
     */
    public function validate(
        string $licenseKeyValue,
        string $productCode
    ): License {
        $licenseKey = LicenseKey::query()
            ->where('key', $licenseKeyValue)
            ->with(['licenses.product'])
            ->first();

        if (! $licenseKey) {
            throw new LicenseNotFoundException('License key not found.');
        }

        $license = $licenseKey->licenses
            ->firstWhere('product.code', $productCode);

        if (! $license) {
            throw new LicenseNotFoundException(
                'No license found for this product.'
            );
        }

        if ($license->status === LicenseStatus::SUSPENDED) {
            throw new LicenseSuspendedException('License is suspended.');
        }

        if ($license->status === LicenseStatus::CANCELLED) {
            throw new LicenseExpiredException('License is cancelled.');
        }

        if ($license->expires_at !== null && now()->greaterThan($license->expires_at)) {
            throw new LicenseExpiredException('License has expired.');
        }

        return $license;
    }
}
