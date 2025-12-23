<?php

use App\Actions\Licenses\ListLicensesByEmailAction;
use App\Models\Brand;
use App\Models\License;
use App\Models\LicenseKey;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(
    Tests\TestCase::class,
    RefreshDatabase::class,);

it('lists licenses by email for a brand', function () {
    $brand = Brand::factory()->create();
    $licenseKey = LicenseKey::factory()->create([
        'brand_id' => $brand->id,
        'customer_email' => 'test@example.com',
    ]);

    $product = Product::factory()->create(['brand_id' => $brand->id]);
    $license = License::factory()->create([
        'license_key_id' => $licenseKey->id,
        'product_id' => $product->id,
    ]);

    $action = resolve(ListLicensesByEmailAction::class);
    $licenses = $action->execute($brand, 'test@example.com');

    expect($licenses)->toHaveCount(1)
        ->and($licenses[0]['license_key'])->toBe($licenseKey->key)
        ->and($licenses[0]['licenses'][0]['product_code'])->toBe($product->code);
});
