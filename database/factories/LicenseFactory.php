<?php

namespace Database\Factories;

use App\Enums\LicenseStatus;
use App\Models\License;
use App\Models\LicenseKey;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class LicenseFactory extends Factory
{
    protected $model = License::class;

    public function definition(): array
    {
        return [
            'status' => LicenseStatus::VALID,
            'expires_at' => Carbon::now()->subDays(-10),
            'max_seats' => $this->faker->numberBetween(1, 5),
            'license_key_id' => LicenseKey::factory(),
            'product_id' => Product::factory(),
        ];
    }
}
