<?php

namespace App\Actions\Licenses;

use App\Models\Brand;
use App\Models\License;
use App\Models\LicenseKey;
use App\Observability\Tracer;

class ListLicensesByEmailAction
{
    /**
     * List all licenses for a customer email within a brand.
     */
    public function execute(Brand $brand, string $customerEmail): array
    {
        Tracer::startSpan('license.list_by_email', [
            'brand_id' => $brand->id,
            'customer_email' => $customerEmail,
        ]);

        $licenseKeys = LicenseKey::with('licenses.product')
            ->where('brand_id', $brand->id)
            ->where('customer_email', $customerEmail)
            ->get();

        $result = $licenseKeys->map(function ($licenseKey) {
            return [
                'license_key' => $licenseKey->key,
                'customer_email' => $licenseKey->customer_email,
                'licenses' => $licenseKey->licenses->map(function (License $license) {
                    return [
                        'product_code' => $license->product->code,
                        'product_name' => $license->product->name,
                        'status' => $license->status->value,
                        'expires_at' => optional($license->expires_at)->toDateTimeString(),
                        'max_seats' => $license->max_seats,
                    ];
                }),
            ];
        })->toArray();

        Tracer::endSpan('license.list_by_email', [
            'license_keys_count' => $licenseKeys->count(),
        ]);

        return $result;
    }
}
