<?php

namespace Tests\Feature\Admin;

use App\Models\Subject;
use Tests\Feature\Concerns\ActsAsAdmin;
use Tests\TestCase;

class SubjectTest extends TestCase
{
    use ActsAsAdmin;

    public function test_index_returns_403_without_permission(): void
    {
        $this->createAdminUser([], true);

        $response = $this->getJson('/api/admin/subjects');

        $response->assertStatus(403);
    }

    public function test_index_returns_subjects_with_permission(): void
    {
        $this->actingAsAdminWithPermission('view-academic-setup');
        $this->createSchool();
        Subject::create([
            'school_id' => null,
            'name' => 'Mathematics',
            'code' => 'MATH',
            'status' => 'active',
        ]);
        Subject::create([
            'school_id' => null,
            'name' => 'English',
            'code' => 'ENG',
            'status' => 'active',
        ]);

        $response = $this->getJson('/api/admin/subjects');

        $response->assertStatus(200);
        $data = $response->json();
        $this->assertGreaterThanOrEqual(2, count($data));
    }

    public function test_store_creates_subject_with_valid_data(): void
    {
        $this->actingAsAdminWithPermission('manage-subjects');
        $this->createSchool();

        $response = $this->postJson('/api/admin/subjects', [
            'name' => 'Physics',
            'code' => 'PHY',
            'status' => 'active',
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'message' => 'Subject created.',
                'subject' => [
                    'name' => 'Physics',
                    'code' => 'PHY',
                ],
            ]);

        $this->assertDatabaseHas('subjects', ['name' => 'Physics']);
    }

    public function test_show_returns_subject(): void
    {
        $this->actingAsAdminWithPermission('view-academic-setup');

        $subject = Subject::create([
            'school_id' => null,
            'name' => 'Chemistry',
            'code' => 'CHEM',
            'status' => 'active',
        ]);

        $response = $this->getJson('/api/admin/subjects/'.$subject->id);

        $response->assertStatus(200)
            ->assertJson(['name' => 'Chemistry']);
    }

    public function test_update_modifies_subject(): void
    {
        $this->actingAsAdminWithPermission('manage-subjects');

        $subject = Subject::create([
            'school_id' => null,
            'name' => 'Biology',
            'code' => 'BIO',
            'status' => 'active',
        ]);

        $response = $this->putJson('/api/admin/subjects/'.$subject->id, [
            'name' => $subject->name,
            'code' => $subject->code,
            'status' => 'inactive',
        ]);

        $response->assertStatus(200);
        $subject->refresh();
        $this->assertSame('inactive', $subject->status);
    }
}
