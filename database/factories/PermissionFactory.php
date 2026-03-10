<?php

namespace Database\Factories;

use App\Models\Permission;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Permission>
 */
class PermissionFactory extends Factory
{
    protected $model = Permission::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $slug = 'perm-'.fake()->unique()->slug(2);

        return [
            'module' => fake()->word(),
            'name' => fake()->words(2, true),
            'slug' => $slug,
            'description' => null,
        ];
    }
}
