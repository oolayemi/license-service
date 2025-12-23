<?php

use App\Actions\Licenses\ActivateLicenseAction;
use App\Models\License;
use App\Models\LicenseKey;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(
    Tests\TestCase::class,
    RefreshDatabase::class
);

it('can activate license for an instance', function () {
    $licenseKey = LicenseKey::factory()->create();
    $product = Product::factory()->create(['brand_id' => $licenseKey->brand_id]);
    $license = License::factory()->create([
        'license_key_id' => $licenseKey->id,
        'product_id' => $product->id,
    ]);

    $action = resolve(ActivateLicenseAction::class);
    $activation = $action->execute($licenseKey->key, $product->code, 'site_123');

    expect($activation)->not()->toBeNull()
        ->and($activation->instance_identifier)->toBe('site_123')
        ->and($activation->license_id)->toBe($license->id);
});
