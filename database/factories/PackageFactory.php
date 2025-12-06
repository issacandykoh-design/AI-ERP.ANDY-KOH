<?php

namespace Database\Factories\SuperAdmin;

use App\Models\SuperAdmin\Package;
use Illuminate\Database\Eloquent\Factories\Factory;

class PackageFactory extends Factory
{
    protected $model = Package::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->words(2, true) . ' Package',
            'description' => $this->faker->sentence(),
            'monthly_price' => $this->faker->randomFloat(2, 10, 500),
            'annual_price' => $this->faker->randomFloat(2, 100, 5000),
            'max_employees' => $this->faker->numberBetween(1, 1000),
            'max_storage_size' => $this->faker->numberBetween(100, 10000),
            'max_file_size' => $this->faker->numberBetween(1, 100),
            'billing_cycle' => $this->faker->randomElement([1, 2]),
            'is_free' => false,
            'is_private' => false,
            'is_recommended' => false,
            'is_auto_renew' => true,
            'module_in_package' => json_encode([]),
        ];
    }
}

