<?php

namespace App\Actions\Licenses;

use App\Enums\LicenseStatus;
use App\Models\Brand;
use App\Models\License;
use App\Models\LicenseKey;
use App\Models\Product;
use App\Observability\Metrics;
use App\Observability\Tracer;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Throwable;

class ProvisionLicenseAction
{
    /**
     * Provision licenses for a customer email and one or more products.
     *
     * @param  array  $productCodes  Array of product codes to provision licenses for
     * @param  \DateTime|string|null  $expiresAt
     *
     * @throws \Throwable
     */
    public function execute(Brand $brand, string $customerEmail, array $productCodes, $expiresAt = null, ?int $maxSeats = null): LicenseKey
    {
        Tracer::startSpan('license.provision', [
            'brand_id' => $brand->id,
            'customer_email' => $customerEmail,
            'product_codes' => $productCodes,
        ]);

        try {
            return DB::transaction(function () use ($brand, $customerEmail, $productCodes, $expiresAt, $maxSeats) {
                // 1. Check if a license key already exists for this brand & customer
                $licenseKey = LicenseKey::firstOrCreate(
                    [
                        'brand_id' => $brand->id,
                        'customer_email' => $customerEmail,
                    ],
                    [
                        'key' => Str::upper(Str::random(16)), // generate license key
                    ]
                );

                // 2. Provision licenses for each product
                $products = Product::whereIn('code', $productCodes)
                    ->where('brand_id', $brand->id)
                    ->get();

                foreach ($products as $product) {
                    License::updateOrCreate(
                        [
                            'license_key_id' => $licenseKey->id,
                            'product_id' => $product->id,
                        ],
                        [
                            'status' => LicenseStatus::VALID,
                            'expires_at' => $expiresAt,
                            'max_seats' => $maxSeats,
                        ]
                    );
                }

                // 3. Metrics
                Metrics::increment('license.provisioned', count($products), [
                    'brand_id' => $brand->id,
                    'license_key' => $licenseKey->key,
                ]);

                Tracer::endSpan('license.provision', [
                    'license_key' => $licenseKey->key,
                    'products_count' => count($products),
                ]);

                return $licenseKey->load('licenses.product');
            });
        } catch (Throwable $e) {
            Tracer::endSpan('license.provision', [
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }
}
