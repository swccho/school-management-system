<?php

namespace Tests\Feature\Admin;

use App\Models\Student;
use Tests\Feature\Concerns\ActsAsAdmin;
use Tests\TestCase;

class StudentTest extends TestCase
{
    use ActsAsAdmin;

    public function test_index_returns_403_without_permission(): void
    {
        $this->createAdminUser([], true);

        $response = $this->getJson('/api/admin/students');

        $response->assertStatus(403);
    }

    public function test_index_returns_students_with_permission(): void
    {
        $this->actingAsAdminWithPermission('view-students');

        $school = $this->createSchool();
        Student::factory()->forSchool($school)->count(2)->create();

        $response = $this->getJson('/api/admin/students');

        $response->assertStatus(200);
        $this->assertCount(2, $response->json());
    }

    public function test_store_creates_student_with_valid_data(): void
    {
        $this->actingAsAdminWithPermission('manage-students');
        $this->createSchool();

        $response = $this->postJson('/api/admin/students', [
            'admission_no' => 'ADM001',
            'first_name' => 'John',
            'last_name' => 'Doe',
            'gender' => 'male',
            'status' => 'active',
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'message' => 'Student created.',
                'student' => [
                    'admission_no' => 'ADM001',
                    'first_name' => 'John',
                    'last_name' => 'Doe',
                ],
            ]);

        $this->assertDatabaseHas('students', [
            'admission_no' => 'ADM001',
            'first_name' => 'John',
        ]);
    }

    public function test_show_returns_student_with_current_academic_assignment_key(): void
    {
        $this->actingAsAdminWithPermission('view-students');

        $school = $this->createSchool();
        $student = Student::factory()->forSchool($school)->create();

        $response = $this->getJson('/api/admin/students/'.$student->id);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'id',
                'admission_no',
                'first_name',
                'current_academic_assignment',
            ]);
    }

    public function test_update_modifies_student(): void
    {
        $this->actingAsAdminWithPermission('manage-students');

        $school = $this->createSchool();
        $student = Student::factory()->forSchool($school)->create();

        $response = $this->putJson('/api/admin/students/'.$student->id, [
            'admission_no' => $student->admission_no,
            'first_name' => 'Jane',
            'last_name' => $student->last_name,
            'gender' => $student->gender,
            'status' => 'inactive',
        ]);

        $response->assertStatus(200);
        $student->refresh();
        $this->assertSame('Jane', $student->first_name);
        $this->assertSame('inactive', $student->status);
    }
}
