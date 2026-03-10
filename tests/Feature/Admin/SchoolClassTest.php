<?php

namespace Tests\Feature\Admin;

use App\Models\SchoolClass;
use Tests\Feature\Concerns\ActsAsAdmin;
use Tests\TestCase;

class SchoolClassTest extends TestCase
{
    use ActsAsAdmin;

    public function test_index_returns_403_without_permission(): void
    {
        $this->createAdminUser([], true);

        $response = $this->getJson('/api/admin/classes');

        $response->assertStatus(403);
    }

    public function test_index_returns_classes_with_permission(): void
    {
        $this->actingAsAdminWithPermission('view-academic-setup');

        $school = $this->createSchool();
        SchoolClass::factory()->forSchool($school)->count(2)->create();

        $response = $this->getJson('/api/admin/classes');

        $response->assertStatus(200);
        $this->assertCount(2, $response->json());
    }

    public function test_store_creates_class_with_valid_data(): void
    {
        $this->actingAsAdminWithPermission('manage-classes');
        $this->createSchool();

        $response = $this->postJson('/api/admin/classes', [
            'name' => 'Class 10',
            'code' => 'C10',
            'numeric_level' => 10,
            'status' => 'active',
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'message' => 'Class created.',
                'class' => [
                    'name' => 'Class 10',
                    'code' => 'C10',
                ],
            ]);

        $this->assertDatabaseHas('school_classes', ['name' => 'Class 10']);
    }

    public function test_show_returns_class(): void
    {
        $this->actingAsAdminWithPermission('view-academic-setup');

        $school = $this->createSchool();
        $class = SchoolClass::factory()->forSchool($school)->create(['name' => 'Class 5']);

        $response = $this->getJson('/api/admin/classes/'.$class->id);

        $response->assertStatus(200)
            ->assertJson(['name' => 'Class 5']);
    }

    public function test_update_modifies_class(): void
    {
        $this->actingAsAdminWithPermission('manage-classes');

        $school = $this->createSchool();
        $class = SchoolClass::factory()->forSchool($school)->create();

        $response = $this->putJson('/api/admin/classes/'.$class->id, [
            'name' => $class->name,
            'code' => $class->code,
            'numeric_level' => $class->numeric_level,
            'status' => 'inactive',
        ]);

        $response->assertStatus(200);
        $class->refresh();
        $this->assertSame('inactive', $class->status);
    }
}
