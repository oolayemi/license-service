<?php

use App\Enums\LicenseStatus;
use App\Models\License;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(
    Tests\TestCase::class,
    RefreshDatabase::class
);

it('is usable when valid and not expired', function () {
    $license = License::factory()->create([
        'status' => LicenseStatus::VALID,
        'expires_at' => now()->addDay(),
    ]);

    expect($license->isUsable())->toBeTrue();
});

it('is not usable when suspended', function () {
    $license = License::factory()->create([
        'status' => LicenseStatus::SUSPENDED,
        'expires_at' => now()->addDay(),
    ]);

    expect($license->isUsable())->toBeFalse();
});

it('is not usable when expired', function () {
    $license = License::factory()->create([
        'status' => LicenseStatus::VALID,
        'expires_at' => now()->subDay(),
    ]);

    expect($license->isUsable())->toBeFalse();
});
