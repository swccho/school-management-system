<?php

namespace Tests\Feature\Concerns;

use App\Models\Permission;
use App\Models\Role;
use App\Models\School;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

trait ActsAsAdmin
{
    use RefreshDatabase;

    protected function createSchool(array $attributes = []): School
    {
        return School::factory()->create($attributes);
    }

    /**
     * Create an admin user with the given permission slug(s) and optionally act as them.
     * If $permissions is empty, no permissions are attached (useful for 403 tests).
     *
     * @param  array<string>  $permissions  Permission slugs (e.g. ['view-dashboard', 'manage-students'])
     * @return User
     */
    protected function createAdminUser(array $permissions = [], bool $actAs = true): User
    {
        $school = School::factory()->create();
        $user = User::factory()->create([
            'school_id' => $school->id,
            'user_type' => 'admin',
            'status' => 'active',
        ]);

        if (! empty($permissions)) {
            $role = Role::factory()->create(['school_id' => $school->id]);
            $permIds = collect($permissions)->map(function ($slug) {
                return Permission::firstOrCreate(
                    ['slug' => $slug],
                    ['module' => 'test', 'name' => str_replace('-', ' ', $slug)]
                )->id;
            });
            $role->permissions()->attach($permIds);
            $user->roles()->attach($role->id);
        }

        if ($actAs) {
            $this->actingAs($user, 'sanctum');
        }

        return $user;
    }

    /**
     * Act as an admin user that has the given permission(s). Creates user, role, and permissions.
     *
     * @param  array<string>|string  $permissions  Permission slug(s)
     */
    protected function actingAsAdminWithPermission(array|string $permissions): User
    {
        $slugs = is_array($permissions) ? $permissions : [$permissions];

        return $this->createAdminUser($slugs, true);
    }
}
