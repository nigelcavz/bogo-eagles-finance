<?php

namespace Database\Seeders;

use App\Models\ExpenseCategory;
use Illuminate\Database\Seeder;

class ExpenseCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Event Expenses',
                'description' => 'Expenses related to club events and activities that may need additional detail.',
                'is_active' => true,
            ],
            [
                'name' => 'Seminars',
                'description' => 'Expenses for seminars, trainings, and educational sessions.',
                'is_active' => true,
            ],
            [
                'name' => 'Other',
                'description' => 'Fallback expense category for items that need a custom explanation.',
                'is_active' => true,
            ],
        ];

        foreach ($categories as $category) {
            ExpenseCategory::updateOrCreate(
                ['name' => $category['name']],
                $category
            );
        }
    }
}
