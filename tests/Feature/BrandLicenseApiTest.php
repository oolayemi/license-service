<?php

use App\Models\Brand;
use App\Models\LicenseKey;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('lists licenses by email via API', function () {
    $brand = Brand::factory()->create();
    $licenseKey = LicenseKey::factory()->create([
        'brand_id' => $brand->id,
        'customer_email' => 'customer@example.com',
    ]);

    $response = $this->getJson('/api/brand/licenses?customer_email=customer@example.com', [
        'Authorization' => 'Bearer '.$brand->api_token,
    ]);

    $response->assertStatus(200)
        ->assertJsonStructure(['success', 'message', 'data' => ['customer_email', 'licenses'], 'errors']);
});
