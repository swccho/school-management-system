<?php

namespace Database\Factories;

use App\Models\Role;
use App\Models\School;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Role>
 */
class RoleFactory extends Factory
{
    protected $model = Role::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $slug = 'role-'.fake()->unique()->slug(2);

        return [
            'school_id' => null,
            'name' => fake()->words(2, true),
            'slug' => $slug,
            'description' => null,
            'is_system' => false,
            'status' => 'active',
        ];
    }

    public function forSchool(School $school): static
    {
        return $this->state(fn (array $attributes) => ['school_id' => $school->id]);
    }
}
