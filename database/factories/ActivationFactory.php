<?php

namespace Database\Factories;

use App\Models\Activation;
use App\Models\License;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class ActivationFactory extends Factory
{
    protected $model = Activation::class;

    public function definition(): array
    {
        return [
            'instance_identifier' => $this->faker->uuid(),
            'activated_at' => Carbon::now(),
            'deactivated_at' => null,

            'license_id' => License::factory(),
        ];
    }
}
