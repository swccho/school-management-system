<?php

namespace Tests\Feature\Admin;

use App\Models\SchoolClass;
use App\Models\Section;
use Tests\Feature\Concerns\ActsAsAdmin;
use Tests\TestCase;

class SectionTest extends TestCase
{
    use ActsAsAdmin;

    public function test_index_returns_403_without_permission(): void
    {
        $this->createAdminUser([], true);

        $response = $this->getJson('/api/admin/sections');

        $response->assertStatus(403);
    }

    public function test_index_returns_sections_with_permission(): void
    {
        $this->actingAsAdminWithPermission('view-academic-setup');

        $school = $this->createSchool();
        $class = SchoolClass::factory()->forSchool($school)->create();
        Section::factory()->forClass($class)->count(2)->create(['school_id' => $school->id]);

        $response = $this->getJson('/api/admin/sections');

        $response->assertStatus(200);
        $this->assertCount(2, $response->json());
    }

    public function test_store_creates_section_with_valid_data(): void
    {
        $this->actingAsAdminWithPermission('manage-sections');
        $school = $this->createSchool();
        $class = SchoolClass::factory()->forSchool($school)->create();

        $response = $this->postJson('/api/admin/sections', [
            'class_id' => $class->id,
            'name' => 'Section A',
            'status' => 'active',
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'message' => 'Section created.',
                'section' => [
                    'name' => 'Section A',
                ],
            ]);

        $this->assertDatabaseHas('sections', [
            'class_id' => $class->id,
            'name' => 'Section A',
        ]);
    }

    public function test_show_returns_section(): void
    {
        $this->actingAsAdminWithPermission('view-academic-setup');

        $class = SchoolClass::factory()->create();
        $section = Section::factory()->forClass($class)->create(['name' => 'Section B']);

        $response = $this->getJson('/api/admin/sections/'.$section->id);

        $response->assertStatus(200)
            ->assertJson(['name' => 'Section B']);
    }

    public function test_update_modifies_section(): void
    {
        $this->actingAsAdminWithPermission('manage-sections');

        $class = SchoolClass::factory()->create();
        $section = Section::factory()->forClass($class)->create();

        $response = $this->putJson('/api/admin/sections/'.$section->id, [
            'class_id' => $section->class_id,
            'name' => $section->name,
            'status' => 'inactive',
        ]);

        $response->assertStatus(200);
        $section->refresh();
        $this->assertSame('inactive', $section->status);
    }
}