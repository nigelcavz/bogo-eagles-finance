<?php

namespace Database\Seeders;

use App\Models\ContributionCategory;
use Illuminate\Database\Seeder;

class ContributionCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Monthly Dues/Contributions',
                'description' => 'Regular monthly member dues and standard club contributions.',
                'default_amount' => null,
                'january_full_payment_discount_months' => 2,
                'is_active' => true,
            ],
            [
                'name' => 'Alalayan ng Agila',
                'description' => 'Support contributions collected for Alalayan ng Agila.',
                'default_amount' => null,
                'is_active' => true,
            ],
            [
                'name' => 'Voluntary Contributions',
                'description' => 'Optional member contributions beyond standard dues.',
                'default_amount' => null,
                'is_active' => true,
            ],
            [
                'name' => 'Other',
                'description' => 'Fallback contribution category for entries that need a custom explanation.',
                'default_amount' => null,
                'is_active' => true,
            ],
        ];

        foreach ($categories as $category) {
            ContributionCategory::updateOrCreate(
                ['name' => $category['name']],
                $category
            );
        }
    }
}
