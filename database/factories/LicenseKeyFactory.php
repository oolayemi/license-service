<?php

namespace Database\Factories;

use App\Models\Brand;
use App\Models\LicenseKey;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class LicenseKeyFactory extends Factory
{
    protected $model = LicenseKey::class;

    public function definition(): array
    {
        return [
            'customer_email' => fake()->unique()->safeEmail(),
            'key' => strtoupper(Str::random()),
            'brand_id' => Brand::factory(),
        ];
    }
}
