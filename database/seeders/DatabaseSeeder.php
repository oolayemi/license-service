<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Activation;
use App\Models\Brand;
use App\Models\License;
use App\Models\LicenseKey;
use App\Models\Product;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    //    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $brands = Brand::factory()->count(3)->create();

        foreach ($brands as $brand) {
            // Each brand has 2-4 products
            $products = Product::factory()->count(rand(2, 4))->create(['brand_id' => $brand->id]);

            // Each brand has 5 customers (license keys)
            $licenseKeys = LicenseKey::factory()->count(5)->create([
                'brand_id' => $brand->id,
            ]);

            foreach ($licenseKeys as $licenseKey) {
                // Assign a license for each product randomly
                $assignedProducts = $products->random(rand(1, $products->count()));

                foreach ($assignedProducts as $product) {
                    $license = License::factory()->create([
                        'license_key_id' => $licenseKey->id,
                        'product_id' => $product->id,
                    ]);

                    // Each license has 0-3 activations
                    Activation::factory()->count(rand(0, 3))->create([
                        'license_id' => $license->id,
                    ]);
                }
            }
        }
    }
}
