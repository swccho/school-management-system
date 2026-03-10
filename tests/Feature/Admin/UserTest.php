<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use App\Models\Role;
use Tests\Feature\Concerns\ActsAsAdmin;
use Tests\TestCase;

class UserTest extends TestCase
{
    use ActsAsAdmin;

    public function test_index_returns_403_without_permission(): void
    {
        $this->createAdminUser([], true);

        $response = $this->getJson('/api/admin/users');

        $response->assertStatus(403);
    }

    public function test_index_returns_users_with_permission(): void
    {
        $this->actingAsAdminWithPermission('view-users');
        $user = User::factory()->create();

        $response = $this->getJson('/api/admin/users');

        $response->assertStatus(200);
        $data = $response->json();
        $this->assertGreaterThanOrEqual(1, count($data));
    }

    public function test_show_returns_user(): void
    {
        $this->actingAsAdminWithPermission('view-users');
        $user = User::factory()->create(['name' => 'Test User']);

        $response = $this->getJson('/api/admin/users/'.$user->id);

        $response->assertStatus(200)
            ->assertJson(['name' => 'Test User']);
    }

    public function test_store_creates_user_with_valid_data(): void
    {
        $this->actingAsAdminWithPermission('create-users');
        $school = $this->createSchool();
        $role = Role::factory()->create(['school_id' => $school->id]);

        $response = $this->postJson('/api/admin/users', [
            'name' => 'New Admin',
            'email' => 'newadmin@test.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'status' => 'active',
            'user_type' => 'admin',
            'role_ids' => [$role->id],
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'message' => 'User created.',
                'user' => [
                    'name' => 'New Admin',
                    'email' => 'newadmin@test.com',
                ],
            ]);

        $this->assertDatabaseHas('users', ['email' => 'newadmin@test.com']);
    }
}
