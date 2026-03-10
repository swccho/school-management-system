<?php

namespace Tests\Feature\Admin;

use App\Models\Role;
use Tests\Feature\Concerns\ActsAsAdmin;
use Tests\TestCase;

class RoleTest extends TestCase
{
    use ActsAsAdmin;

    public function test_index_returns_403_without_permission(): void
    {
        $this->createAdminUser([], true);

        $response = $this->getJson('/api/admin/roles');

        $response->assertStatus(403);
    }

    public function test_index_returns_roles_with_permission(): void
    {
        $this->actingAsAdminWithPermission('view-roles');
        Role::factory()->count(2)->create();

        $response = $this->getJson('/api/admin/roles');

        $response->assertStatus(200);
        $this->assertGreaterThanOrEqual(2, count($response->json()));
    }

    public function test_store_creates_role_with_valid_data(): void
    {
        $this->actingAsAdminWithPermission('create-roles');

        $response = $this->postJson('/api/admin/roles', [
            'name' => 'Editor',
            'slug' => 'editor',
            'description' => 'Can edit content',
            'status' => 'active',
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'message' => 'Role created.',
                'role' => [
                    'name' => 'Editor',
                    'slug' => 'editor',
                ],
            ]);

        $this->assertDatabaseHas('roles', ['slug' => 'editor']);
    }

    public function test_show_returns_role(): void
    {
        $this->actingAsAdminWithPermission('view-roles');
        $role = Role::factory()->create(['name' => 'Viewer']);

        $response = $this->getJson('/api/admin/roles/'.$role->id);

        $response->assertStatus(200)
            ->assertJson(['name' => 'Viewer']);
    }
}
