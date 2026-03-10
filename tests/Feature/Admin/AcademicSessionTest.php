<?php

namespace Tests\Feature\Admin;

use App\Models\AcademicSession;
use Tests\Feature\Concerns\ActsAsAdmin;
use Tests\TestCase;

class AcademicSessionTest extends TestCase
{
    use ActsAsAdmin;

    public function test_index_returns_401_without_auth(): void
    {
        $response = $this->getJson('/api/admin/academic-sessions');

        $response->assertStatus(401);
    }

    public function test_index_returns_403_without_permission(): void
    {
        $this->createAdminUser([], true);

        $response = $this->getJson('/api/admin/academic-sessions');

        $response->assertStatus(403);
    }

    public function test_index_returns_sessions_with_permission(): void
    {
        $this->actingAsAdminWithPermission('view-academic-setup');

        $school = $this->createSchool();
        AcademicSession::factory()->forSchool($school)->count(2)->create();

        $response = $this->getJson('/api/admin/academic-sessions');

        $response->assertStatus(200);
        $data = $response->json();
        $this->assertCount(2, $data);
    }

    public function test_store_creates_session_with_valid_data(): void
    {
        $this->actingAsAdminWithPermission('manage-academic-sessions');
        $this->createSchool();

        $response = $this->postJson('/api/admin/academic-sessions', [
            'name' => '2024-2025',
            'code' => '2425',
            'start_date' => '2024-04-01',
            'end_date' => '2025-03-31',
            'status' => 'active',
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'message' => 'Academic session created.',
                'session' => [
                    'name' => '2024-2025',
                    'code' => '2425',
                ],
            ]);

        $this->assertDatabaseHas('academic_sessions', [
            'name' => '2024-2025',
            'code' => '2425',
        ]);
    }

    public function test_show_returns_session(): void
    {
        $this->actingAsAdminWithPermission('view-academic-setup');

        $school = $this->createSchool();
        $session = AcademicSession::factory()->forSchool($school)->create(['name' => 'Test Session']);

        $response = $this->getJson('/api/admin/academic-sessions/'.$session->id);

        $response->assertStatus(200)
            ->assertJson(['name' => 'Test Session']);
    }

    public function test_update_modifies_session(): void
    {
        $this->actingAsAdminWithPermission('manage-academic-sessions');

        $school = $this->createSchool();
        $session = AcademicSession::factory()->forSchool($school)->create();

        $response = $this->putJson('/api/admin/academic-sessions/'.$session->id, [
            'name' => $session->name,
            'code' => $session->code,
            'start_date' => $session->start_date->toDateString(),
            'end_date' => $session->end_date->toDateString(),
            'status' => 'inactive',
        ]);

        $response->assertStatus(200);
        $session->refresh();
        $this->assertSame('inactive', $session->status);
    }
}
