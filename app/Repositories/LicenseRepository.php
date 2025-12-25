<?php

namespace App\Repositories;

use App\Enums\LicenseStatus;
use App\Models\Brand;
use App\Models\License;
use App\Models\LicenseKey;
use App\Models\Product;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class LicenseRepository
{
    /**
     * Find an existing license key for a brand and customer email.
     */
    public function findLicenseKeyByEmail(Brand $brand, string $email): ?LicenseKey
    {
        return LicenseKey::where('brand_id', $brand->id)
            ->where('customer_email', $email)
            ->first();
    }

    /**
     * Find a license key by its public key string.
     */
    public function findLicenseKeyByKey(string $key): ?LicenseKey
    {
        return LicenseKey::where('key', $key)->first();
    }

    /**
     * Create a new license key for a brand + customer.
     */
    public function createLicenseKey(
        Brand $brand,
        string $email,
        string $key
    ): LicenseKey {
        return LicenseKey::create([
            'brand_id' => $brand->id,
            'customer_email' => $email,
            'key' => $key,
        ]);
    }

    /**
     * Create a license for a specific product.
     */
    public function createLicense(
        LicenseKey $licenseKey,
        Product $product,
        Carbon $expiresAt,
        ?int $maxSeats = null
    ): License {
        return License::create([
            'license_key_id' => $licenseKey->id,
            'product_id' => $product->id,
            'status' => LicenseStatus::VALID,
            'expires_at' => $expiresAt,
            'max_seats' => $maxSeats,
        ]);
    }

    /**
     * Retrieve all licenses attached to a license key.
     *
     * @return Collection<int, License>
     */
    public function getLicensesForKey(LicenseKey $licenseKey): Collection
    {
        return License::with('product')
            ->where('license_key_id', $licenseKey->id)
            ->get();
    }

    /**
     * Find a license for a given license key + product code.
     *
     * Used during activation & validation.
     */
    public function findLicenseForProduct(
        LicenseKey $licenseKey,
        string $productCode
    ): ?License {
        return License::where('license_key_id', $licenseKey->id)
            ->whereHas('product', function ($query) use ($productCode) {
                $query->where('code', $productCode);
            })
            ->with('product')
            ->first();
    }

    /**
     * Update license status (suspend, resume, cancel).
     */
    public function updateStatus(
        License $license,
        LicenseStatus $status
    ): License {
        $license->update(['status' => $status]);

        return $license;
    }

    /**
     * Extend a license expiration date.
     */
    public function extendExpiration(
        License $license,
        Carbon $newExpiration
    ): License {
        $license->update([
            'expires_at' => $newExpiration,
        ]);

        return $license;
    }

    /**
     * List all licenses associated with a customer email
     * across all brands.
     *
     * (Admin / brand-only)
     *
     * @return Collection<int, License>
     */
    public function listLicensesByEmail(string $email): Collection
    {
        return License::with([
            'licenseKey.brand',
            'product',
        ])
            ->whereHas('licenseKey', function ($query) use ($email) {
                $query->where('customer_email', $email);
            })
            ->orderByDesc('created_at')
            ->get();
    }
}
