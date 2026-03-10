<?php

namespace Tests\Feature\Admin;

use App\Models\AcademicSession;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\Student;
use Tests\Feature\Concerns\ActsAsAdmin;
use Tests\TestCase;

class ValidationTest extends TestCase
{
    use ActsAsAdmin;

    public function test_store_student_academic_assignment_rejects_missing_required_fields(): void
    {
        $this->actingAsAdminWithPermission('manage-attendance');

        $response = $this->postJson('/api/admin/student-academic-assignments', [
            'academic_session_id' => null,
            'class_id' => null,
            'section_id' => null,
            'student_id' => null,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['academic_session_id', 'class_id', 'section_id', 'student_id']);
    }

    public function test_store_student_academic_assignment_rejects_invalid_section_for_class(): void
    {
        $this->actingAsAdminWithPermission('manage-attendance');
        $school = $this->createSchool();
        $session = AcademicSession::factory()->forSchool($school)->create();
        $class1 = SchoolClass::factory()->forSchool($school)->create();
        $class2 = SchoolClass::factory()->forSchool($school)->create();
        $sectionOfClass2 = Section::factory()->forClass($class2)->create(['school_id' => $school->id]);
        $student = Student::factory()->forSchool($school)->create();

        $response = $this->postJson('/api/admin/student-academic-assignments', [
            'academic_session_id' => $session->id,
            'class_id' => $class1->id,
            'section_id' => $sectionOfClass2->id,
            'student_id' => $student->id,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['section_id']);
    }

    public function test_store_academic_session_rejects_missing_name(): void
    {
        $this->actingAsAdminWithPermission('manage-academic-sessions');
        $this->createSchool();

        $response = $this->postJson('/api/admin/academic-sessions', [
            'name' => '',
            'start_date' => '2024-04-01',
            'end_date' => '2025-03-31',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    public function test_store_academic_session_rejects_end_date_before_start_date(): void
    {
        $this->actingAsAdminWithPermission('manage-academic-sessions');
        $this->createSchool();

        $response = $this->postJson('/api/admin/academic-sessions', [
            'name' => '2024-25',
            'start_date' => '2025-04-01',
            'end_date' => '2024-03-31',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['end_date']);
    }

    public function test_store_student_rejects_missing_first_name(): void
    {
        $this->actingAsAdminWithPermission('manage-students');
        $school = $this->createSchool();
        $role = \App\Models\Role::factory()->create(['school_id' => $school->id]);

        $response = $this->postJson('/api/admin/students', [
            'admission_no' => 'ADM999',
            'first_name' => '',
            'last_name' => 'Doe',
            'status' => 'active',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['first_name']);
    }
}
