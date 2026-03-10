<?php

namespace Database\Factories;

use App\Models\School;
use App\Models\Student;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Student>
 */
class StudentFactory extends Factory
{
    protected $model = Student::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $firstName = fake()->firstName();
        $lastName = fake()->lastName();

        return [
            'school_id' => null,
            'user_id' => null,
            'admission_no' => 'ADM'.fake()->unique()->numerify('#####'),
            'registration_no' => null,
            'roll_no' => null,
            'first_name' => $firstName,
            'last_name' => $lastName,
            'gender' => fake()->randomElement(['male', 'female']),
            'date_of_birth' => fake()->dateTimeBetween('-15 years', '-5 years'),
            'blood_group' => null,
            'religion' => null,
            'photo_path' => null,
            'phone' => null,
            'email' => null,
            'present_address' => null,
            'permanent_address' => null,
            'status' => 'active',
        ];
    }

    public function forSchool(School $school): static
    {
        return $this->state(fn (array $attributes) => ['school_id' => $school->id]);
    }
}
