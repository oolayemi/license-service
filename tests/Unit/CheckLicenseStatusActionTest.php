<?php

use App\Actions\Licenses\CheckLicenseStatusAction;
use App\Models\License;
use App\Models\LicenseKey;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(
    Tests\TestCase::class,
    RefreshDatabase::class
);

it('returns license status and entitlements', function () {
    $licenseKey = LicenseKey::factory()->create();
    $product = Product::factory()->create(['brand_id' => $licenseKey->brand_id]);
    $license = License::factory()->create([
        'license_key_id' => $licenseKey->id,
        'product_id' => $product->id,
    ]);

    //    dd($license->toArray());

    $action = resolve(CheckLicenseStatusAction::class);
    $status = $action->execute($licenseKey->key);

    expect($status)->toHaveKey('license_key')
        ->and($status['license_key'])->toBe($licenseKey->key)
        ->and($status['licenses'][0]['product_code'])->toBe($product->code);
});
