<?php

namespace Database\Factories;

use App\Models\School;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\School>
 */
class SchoolFactory extends Factory
{
    protected $model = School::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->company().' School',
            'code' => strtoupper(fake()->unique()->lexify('???')),
            'email' => fake()->unique()->safeEmail(),
            'phone' => fake()->optional()->phoneNumber(),
            'website' => null,
            'established_year' => fake()->numberBetween(1990, 2020),
            'principal_name' => fake()->name(),
            'slogan' => null,
            'short_name' => null,
            'address' => fake()->address(),
            'city' => fake()->city(),
            'district' => null,
            'country' => null,
            'postal_code' => null,
            'description' => null,
            'logo_path' => null,
            'favicon_path' => null,
            'status' => 'active',
        ];
    }
}
