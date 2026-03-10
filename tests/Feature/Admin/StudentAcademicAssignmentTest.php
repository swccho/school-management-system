<?php

namespace Tests\Feature\Admin;

use App\Models\AcademicSession;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\Student;
use App\Models\StudentAcademicAssignment;
use Tests\Feature\Concerns\ActsAsAdmin;
use Tests\TestCase;

class StudentAcademicAssignmentTest extends TestCase
{
    use ActsAsAdmin;

    public function test_index_requires_view_attendance_permission(): void
    {
        $this->createAdminUser([], true);

        $response = $this->getJson('/api/admin/student-academic-assignments');

        $response->assertStatus(403);
    }

    public function test_index_returns_assignments_filtered_by_student_id(): void
    {
        $this->actingAsAdminWithPermission(['view-attendance']);

        $school = $this->createSchool();
        $session = AcademicSession::factory()->forSchool($school)->create();
        $class = SchoolClass::factory()->forSchool($school)->create();
        $section = Section::factory()->forClass($class)->create(['school_id' => $school->id]);
        $student1 = Student::factory()->forSchool($school)->create();
        $student2 = Student::factory()->forSchool($school)->create();

        StudentAcademicAssignment::factory()->create([
            'school_id' => $school->id,
            'academic_session_id' => $session->id,
            'class_id' => $class->id,
            'section_id' => $section->id,
            'student_id' => $student1->id,
        ]);
        StudentAcademicAssignment::factory()->create([
            'school_id' => $school->id,
            'academic_session_id' => $session->id,
            'class_id' => $class->id,
            'section_id' => $section->id,
            'student_id' => $student2->id,
        ]);

        $response = $this->getJson('/api/admin/student-academic-assignments?student_id='.$student1->id);

        $response->assertStatus(200);
        $data = $response->json();
        $this->assertCount(1, $data);
        $this->assertSame($student1->id, $data[0]['student_id']);
    }

    public function test_store_creates_assignment_with_valid_data(): void
    {
        $this->actingAsAdminWithPermission(['manage-attendance']);

        $school = $this->createSchool();
        $session = AcademicSession::factory()->forSchool($school)->create();
        $class = SchoolClass::factory()->forSchool($school)->create();
        $section = Section::factory()->forClass($class)->create(['school_id' => $school->id]);
        $student = Student::factory()->forSchool($school)->create();

        $response = $this->postJson('/api/admin/student-academic-assignments', [
            'academic_session_id' => $session->id,
            'class_id' => $class->id,
            'section_id' => $section->id,
            'student_id' => $student->id,
            'roll_no' => '42',
            'status' => 'active',
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'message' => 'Student assigned.',
                'assignment' => [
                    'student_id' => $student->id,
                    'roll_no' => '42',
                    'status' => 'active',
                ],
            ]);

        $this->assertDatabaseHas('student_academic_assignments', [
            'student_id' => $student->id,
            'academic_session_id' => $session->id,
            'class_id' => $class->id,
            'section_id' => $section->id,
            'roll_no' => '42',
        ]);
    }

    public function test_store_rejects_duplicate_session_class_section_student(): void
    {
        $this->actingAsAdminWithPermission(['manage-attendance']);

        $school = $this->createSchool();
        $session = AcademicSession::factory()->forSchool($school)->create();
        $class = SchoolClass::factory()->forSchool($school)->create();
        $section = Section::factory()->forClass($class)->create(['school_id' => $school->id]);
        $student = Student::factory()->forSchool($school)->create();

        StudentAcademicAssignment::factory()->create([
            'school_id' => $school->id,
            'academic_session_id' => $session->id,
            'class_id' => $class->id,
            'section_id' => $section->id,
            'student_id' => $student->id,
        ]);

        $response = $this->postJson('/api/admin/student-academic-assignments', [
            'academic_session_id' => $session->id,
            'class_id' => $class->id,
            'section_id' => $section->id,
            'student_id' => $student->id,
            'roll_no' => '99',
            'status' => 'active',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['student_id']);
    }

    public function test_show_returns_assignment(): void
    {
        $this->actingAsAdminWithPermission(['view-attendance']);

        $assignment = StudentAcademicAssignment::factory()->create();

        $response = $this->getJson('/api/admin/student-academic-assignments/'.$assignment->id);

        $response->assertStatus(200)
            ->assertJson([
                'id' => $assignment->id,
                'student_id' => $assignment->student_id,
                'roll_no' => $assignment->roll_no,
            ]);
    }

    public function test_update_modifies_assignment(): void
    {
        $this->actingAsAdminWithPermission(['manage-attendance']);

        $assignment = StudentAcademicAssignment::factory()->create();

        $response = $this->putJson('/api/admin/student-academic-assignments/'.$assignment->id, [
            'roll_no' => '100',
            'status' => 'inactive',
            'remarks' => 'Updated by test',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Assignment updated.',
                'assignment' => [
                    'id' => $assignment->id,
                    'roll_no' => '100',
                    'status' => 'inactive',
                    'remarks' => 'Updated by test',
                ],
            ]);

        $assignment->refresh();
        $this->assertSame('100', $assignment->roll_no);
        $this->assertSame('inactive', $assignment->status);
        $this->assertSame('Updated by test', $assignment->remarks);
    }
}
