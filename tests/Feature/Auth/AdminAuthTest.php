<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Tests\Feature\Concerns\ActsAsAdmin;
use Tests\TestCase;

class AdminAuthTest extends TestCase
{
    use ActsAsAdmin;

    public function test_login_with_valid_credentials_returns_success(): void
    {
        $user = User::factory()->create([
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
            'user_type' => 'admin',
            'status' => 'active',
        ]);

        $response = $this->postJson('/api/admin/login', [
            'email' => 'admin@test.com',
            'password' => 'password',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Authenticated.',
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'user_type' => 'admin',
                ],
            ]);
    }

    public function test_login_with_invalid_credentials_returns_422(): void
    {
        User::factory()->create([
            'email' => 'admin@test.com',
            'user_type' => 'admin',
            'status' => 'active',
        ]);

        $response = $this->postJson('/api/admin/login', [
            'email' => 'admin@test.com',
            'password' => 'wrong-password',
        ]);

        $response->assertStatus(422)
            ->assertJson(['message' => 'Invalid credentials.']);
    }

    public function test_login_with_non_admin_user_returns_403(): void
    {
        User::factory()->create([
            'email' => 'user@test.com',
            'password' => bcrypt('password'),
            'user_type' => 'staff',
            'status' => 'active',
        ]);

        $response = $this->postJson('/api/admin/login', [
            'email' => 'user@test.com',
            'password' => 'password',
        ]);

        $response->assertStatus(403)
            ->assertJson(['message' => 'Access denied. Admin only.']);
    }

    public function test_me_requires_authentication(): void
    {
        $response = $this->getJson('/api/admin/me');

        $response->assertStatus(401);
    }

    public function test_me_returns_user_when_authenticated(): void
    {
        $user = User::factory()->create([
            'user_type' => 'admin',
            'status' => 'active',
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/admin/me');

        $response->assertStatus(200)
            ->assertJson([
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'user_type' => 'admin',
            ]);
    }

    public function test_dashboard_returns_401_when_unauthenticated(): void
    {
        $response = $this->getJson('/api/admin/dashboard');

        $response->assertStatus(401);
    }

    public function test_dashboard_returns_403_when_authenticated_without_permission(): void
    {
        $this->createAdminUser([], true);

        $response = $this->getJson('/api/admin/dashboard');

        $response->assertStatus(403);
    }

    public function test_dashboard_returns_200_when_authenticated_with_permission(): void
    {
        $this->actingAsAdminWithPermission('view-dashboard');

        $response = $this->getJson('/api/admin/dashboard');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'summary_cards',
                'academic_overview',
                'attendance_overview',
                'exam_overview',
                'recent_activity',
            ]);
    }
}
