<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            ['module' => 'dashboard', 'name' => 'View Dashboard', 'slug' => 'view-dashboard'],
            ['module' => 'users', 'name' => 'View Users', 'slug' => 'view-users'],
            ['module' => 'users', 'name' => 'Create Users', 'slug' => 'create-users'],
            ['module' => 'users', 'name' => 'Edit Users', 'slug' => 'edit-users'],
            ['module' => 'users', 'name' => 'Delete Users', 'slug' => 'delete-users'],
            ['module' => 'roles', 'name' => 'View Roles', 'slug' => 'view-roles'],
            ['module' => 'roles', 'name' => 'Create Roles', 'slug' => 'create-roles'],
            ['module' => 'roles', 'name' => 'Edit Roles', 'slug' => 'edit-roles'],
            ['module' => 'roles', 'name' => 'Assign Roles', 'slug' => 'assign-roles'],
            ['module' => 'permissions', 'name' => 'View Permissions', 'slug' => 'view-permissions'],
            ['module' => 'academic', 'name' => 'View Academic Setup', 'slug' => 'view-academic-setup'],
            ['module' => 'academic', 'name' => 'Manage Academic Sessions', 'slug' => 'manage-academic-sessions'],
            ['module' => 'academic', 'name' => 'Manage Classes', 'slug' => 'manage-classes'],
            ['module' => 'academic', 'name' => 'Manage Sections', 'slug' => 'manage-sections'],
            ['module' => 'academic', 'name' => 'Manage Subjects', 'slug' => 'manage-subjects'],
            ['module' => 'settings', 'name' => 'View Settings', 'slug' => 'view-settings'],
            ['module' => 'settings', 'name' => 'Manage Settings', 'slug' => 'manage-settings'],
            ['module' => 'notices', 'name' => 'View Notices', 'slug' => 'view-notices'],
            ['module' => 'notices', 'name' => 'Create Notices', 'slug' => 'create-notices'],
            ['module' => 'notices', 'name' => 'Edit Notices', 'slug' => 'edit-notices'],
            ['module' => 'notices', 'name' => 'Publish Notices', 'slug' => 'publish-notices'],
            ['module' => 'events', 'name' => 'View Events', 'slug' => 'view-events'],
            ['module' => 'events', 'name' => 'Create Events', 'slug' => 'create-events'],
            ['module' => 'events', 'name' => 'Edit Events', 'slug' => 'edit-events'],
            ['module' => 'gallery', 'name' => 'View Gallery', 'slug' => 'view-gallery'],
            ['module' => 'gallery', 'name' => 'Manage Gallery', 'slug' => 'manage-gallery'],
            ['module' => 'reports', 'name' => 'View Reports', 'slug' => 'view-reports'],
            ['module' => 'reports', 'name' => 'Export Reports', 'slug' => 'export-reports'],
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(
                ['slug' => $perm['slug']],
                array_merge($perm, ['description' => null])
            );
        }
    }
}
