<?php

namespace Database\Factories;

use App\Models\Brand;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'code' => strtoupper($this->faker->unique()->lexify('PRD???')),
            'is_addon' => $this->faker->boolean(),
            'brand_id' => Brand::factory(),
        ];
    }
}
