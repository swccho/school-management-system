<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $admin = User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'user_type' => 'admin',
            'status' => 'active',
        ]);

        $this->call([
            RoleSeeder::class,
            PermissionSeeder::class,
            AssignRolePermissionsSeeder::class,
            SchoolSeeder::class,
        ]);

        $superAdmin = Role::where('slug', 'super-admin')->first();
        if ($superAdmin && $admin) {
            $admin->roles()->syncWithoutDetaching([$superAdmin->id]);
        }
    }
}
