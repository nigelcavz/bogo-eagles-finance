<?php

namespace Database\Factories;

use App\Models\ContributionCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ContributionCategory>
 */
class ContributionCategoryFactory extends Factory
{
    protected $model = ContributionCategory::class;

    public function definition(): array
    {
        return [
            'name' => fake()->unique()->words(2, true),
            'description' => fake()->sentence(),
            'default_amount' => fake()->randomFloat(2, 100, 5000),
            'is_active' => true,
        ];
    }
}
