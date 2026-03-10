<?php

namespace Database\Factories;

use App\Models\SchoolClass;
use App\Models\Section;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Section>
 */
class SectionFactory extends Factory
{
    protected $model = Section::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'school_id' => null,
            'class_id' => SchoolClass::factory(),
            'name' => 'Section '.fake()->unique()->numberBetween(1, 9999),
            'code' => null,
            'room_no' => null,
            'capacity' => null,
            'description' => null,
            'status' => 'active',
        ];
    }

    public function forClass(SchoolClass $class): static
    {
        return $this->state(fn (array $attributes) => [
            'class_id' => $class->id,
            'school_id' => $class->school_id,
        ]);
    }
}
