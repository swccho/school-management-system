<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            ['name' => 'Super Admin', 'slug' => 'super-admin', 'description' => 'Full system access', 'is_system' => true, 'status' => 'active'],
            ['name' => 'School Admin', 'slug' => 'school-admin', 'description' => 'School-level administrator', 'is_system' => false, 'status' => 'active'],
            ['name' => 'Academic Manager', 'slug' => 'academic-manager', 'description' => 'Manages academic setup', 'is_system' => false, 'status' => 'active'],
            ['name' => 'Content Manager', 'slug' => 'content-manager', 'description' => 'Manages notices, events, gallery', 'is_system' => false, 'status' => 'active'],
            ['name' => 'Report Manager', 'slug' => 'report-manager', 'description' => 'Views and exports reports', 'is_system' => false, 'status' => 'active'],
        ];

        foreach ($roles as $role) {
            Role::firstOrCreate(
                ['slug' => $role['slug']],
                $role
            );
        }
    }
}
