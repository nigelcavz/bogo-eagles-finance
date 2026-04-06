<?php

namespace Database\Factories;

use App\Models\Member;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Member>
 */
class MemberFactory extends Factory
{
    protected $model = Member::class;

    public function definition(): array
    {
        return [
            'member_code' => strtoupper(fake()->unique()->bothify('BEC-####')),
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'middle_name' => fake()->optional()->firstName(),
            'suffix' => fake()->optional()->randomElement(['Jr.', 'Sr.', 'III']),
            'gender' => fake()->optional()->randomElement(['male', 'female']),
            'birthdate' => fake()->optional()->date(),
            'contact_number' => fake()->phoneNumber(),
            'address' => fake()->address(),
            'membership_status' => 'active',
            'joined_at' => fake()->date(),
            'notes' => fake()->optional()->sentence(),
        ];
    }
}
