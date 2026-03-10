<?php

namespace Database\Factories;

use App\Models\School;
use App\Models\SchoolClass;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SchoolClass>
 */
class SchoolClassFactory extends Factory
{
    protected $model = SchoolClass::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'school_id' => null,
            'name' => 'Class '.fake()->unique()->numberBetween(1, 9999),
            'code' => null,
            'numeric_level' => fake()->numberBetween(1, 12),
            'description' => null,
            'status' => 'active',
        ];
    }

    public function forSchool(School $school): static
    {
        return $this->state(fn (array $attributes) => ['school_id' => $school->id]);
    }
}
