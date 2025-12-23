<?php

use App\Models\License;
use App\Models\LicenseKey;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('can activate license via API', function () {
    $licenseKey = LicenseKey::factory()->create();
    $product = Product::factory()->create(['brand_id' => $licenseKey->brand_id]);
    $license = License::factory()->create([
        'license_key_id' => $licenseKey->id,
        'product_id' => $product->id,
    ]);

    $response = $this->postJson('/api/product/license/activate', [
        'license_key' => $licenseKey->key,
        'product_code' => $product->code,
        'instance_id' => 'site_123',
    ]);

    $response->assertStatus(200)
        ->assertJsonStructure(['activation_id', 'activated_at', 'instance_identifier']);
});

it('can check license status via API', function () {
    $licenseKey = LicenseKey::factory()->create();
    $product = Product::factory()->create(['brand_id' => $licenseKey->brand_id]);
    $license = License::factory()->create([
        'license_key_id' => $licenseKey->id,
        'product_id' => $product->id,
    ]);

    $response = $this->getJson('/api/product/license/status?license_key='.$licenseKey->key);
    $response->assertStatus(200)
        ->assertJsonStructure(['license_key', 'licenses']);
});
