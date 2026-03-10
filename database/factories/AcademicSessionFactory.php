<?php

namespace Database\Factories;

use App\Models\AcademicSession;
use App\Models\School;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AcademicSession>
 */
class AcademicSessionFactory extends Factory
{
    protected $model = AcademicSession::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $start = fake()->dateTimeBetween('-1 year', 'now');
        $end = (clone $start)->modify('+1 year');
        $baseName = date('Y', $start->getTimestamp()).'-'.(date('Y', $end->getTimestamp()) % 100);
        $name = $baseName.'-'.fake()->unique()->numberBetween(1000, 9999);

        return [
            'school_id' => null,
            'name' => $name,
            'code' => null,
            'start_date' => $start,
            'end_date' => $end,
            'is_current' => false,
            'status' => 'active',
            'description' => null,
        ];
    }

    public function current(): static
    {
        return $this->state(fn (array $attributes) => ['is_current' => true]);
    }

    public function forSchool(School $school): static
    {
        return $this->state(fn (array $attributes) => ['school_id' => $school->id]);
    }
}
