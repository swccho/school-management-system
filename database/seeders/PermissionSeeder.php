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
            ['module' => 'staff', 'name' => 'View Staff', 'slug' => 'view-staff'],
            ['module' => 'staff', 'name' => 'Manage Staff', 'slug' => 'manage-staff'],
            ['module' => 'staff', 'name' => 'View Teachers', 'slug' => 'view-teachers'],
            ['module' => 'staff', 'name' => 'Manage Teachers', 'slug' => 'manage-teachers'],
            ['module' => 'academic', 'name' => 'View Teacher Subject Assignments', 'slug' => 'view-teacher-subject-assignments'],
            ['module' => 'academic', 'name' => 'Manage Teacher Subject Assignments', 'slug' => 'manage-teacher-subject-assignments'],
            ['module' => 'students', 'name' => 'View Students', 'slug' => 'view-students'],
            ['module' => 'students', 'name' => 'Manage Students', 'slug' => 'manage-students'],
            ['module' => 'guardians', 'name' => 'View Guardians', 'slug' => 'view-guardians'],
            ['module' => 'guardians', 'name' => 'Manage Guardians', 'slug' => 'manage-guardians'],
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
            ['module' => 'attendance', 'name' => 'View Attendance', 'slug' => 'view-attendance'],
            ['module' => 'attendance', 'name' => 'Manage Attendance', 'slug' => 'manage-attendance'],
            ['module' => 'routines', 'name' => 'View Routines', 'slug' => 'view-routines'],
            ['module' => 'routines', 'name' => 'Manage Routines', 'slug' => 'manage-routines'],
            ['module' => 'exams', 'name' => 'View Exams', 'slug' => 'view-exams'],
            ['module' => 'exams', 'name' => 'Manage Exams', 'slug' => 'manage-exams'],
            ['module' => 'marks-entry', 'name' => 'View Marks Entry', 'slug' => 'view-marks-entry'],
            ['module' => 'marks-entry', 'name' => 'Manage Marks Entry', 'slug' => 'manage-marks-entry'],
            ['module' => 'results', 'name' => 'View Results', 'slug' => 'view-results'],
            ['module' => 'results', 'name' => 'Manage Results', 'slug' => 'manage-results'],
            ['module' => 'results', 'name' => 'Manage Grade Scales', 'slug' => 'manage-grade-scales'],
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(
                ['slug' => $perm['slug']],
                array_merge($perm, ['description' => null])
            );
        }
    }
}
