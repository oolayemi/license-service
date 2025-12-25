<?php

namespace App\Policies;

use App\Models\Brand;
use App\Models\License;
use App\Models\LicenseKey;
use App\Models\Product;

class LicensePolicy
{
    /**
     * Brand systems can create licenses
     * (Brand can provision a license)
     */
    public function create(Brand $brand): bool
    {
        // Any authenticated brand is allowed to provision licenses
        return true;
    }

    /**
     * Brand systems can update licenses they own
     * (suspend / resume / cancel / renew)
     */
    public function update(Brand $brand, License $license): bool
    {
        return $license->licenseKey->brand_id === $brand->id;
    }

    /**
     * Brand systems can view licenses they own
     */
    public function view(Brand $brand, License $license): bool
    {
        return $license->licenseKey->brand_id === $brand->id;
    }

    /**
     * Brand systems can list licenses by customer email
     * (cross-brand listing is allowed only for trusted brand systems)
     *
     * This assumes an "admin" or "internal" flag on the brand.
     */
    public function listByEmail(Brand $brand): bool
    {
        return $brand->is_internal === true;
    }

    /**
     * End-user products can activate a license
     * (End-user product can activate a license)
     */
    public function activate(
        Product $product,
        LicenseKey $licenseKey,
        License $license
    ): bool {
        // Product must belong to the same brand as the license key
        if ($product->brand_id !== $licenseKey->brand_id) {
            return false;
        }

        // License must belong to the same license key
        if ($license->license_key_id !== $licenseKey->id) {
            return false;
        }

        // License must be for this product
        if ($license->product_id !== $product->id) {
            return false;
        }

        // License must be in a usable state
        if (! $license->isUsable()) {
            return false;
        }

        return true;
    }

    /**
     * End-user products or customers can check license status
     * (Check status & entitlements)
     */
    public function checkStatus(LicenseKey $licenseKey): bool
    {
        // Possession of the license key is sufficient
        return true;
    }

    /**
     * End-user products or customers can deactivate a seat
     * (Deactivate activation)
     */
    public function deactivateSeat(
        LicenseKey $licenseKey,
        License $license
    ): bool {
        return $license->license_key_id == $licenseKey->id;
    }
}
