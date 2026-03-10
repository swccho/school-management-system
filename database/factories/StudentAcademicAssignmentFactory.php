<?php

namespace Database\Factories;

use App\Models\AcademicSession;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\Student;
use App\Models\StudentAcademicAssignment;
use App\Models\School;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\StudentAcademicAssignment>
 */
class StudentAcademicAssignmentFactory extends Factory
{
    protected $model = StudentAcademicAssignment::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $school = School::factory()->create();
        $class = SchoolClass::factory()->forSchool($school)->create();
        $section = Section::factory()->forClass($class)->create(['school_id' => $school->id]);
        $session = AcademicSession::factory()->forSchool($school)->create();
        $student = Student::factory()->forSchool($school)->create();

        return [
            'school_id' => $school->id,
            'academic_session_id' => $session->id,
            'class_id' => $class->id,
            'section_id' => $section->id,
            'student_id' => $student->id,
            'roll_no' => (string) fake()->numberBetween(1, 50),
            'status' => 'active',
            'remarks' => null,
        ];
    }
}
