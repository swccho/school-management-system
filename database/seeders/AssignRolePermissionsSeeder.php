<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class AssignRolePermissionsSeeder extends Seeder
{
    public function run(): void
    {
        $allPermissionIds = Permission::pluck('id')->all();

        $superAdmin = Role::where('slug', 'super-admin')->first();
        if ($superAdmin) {
            $superAdmin->permissions()->sync($allPermissionIds);
        }

        $schoolAdmin = Role::where('slug', 'school-admin')->first();
        if ($schoolAdmin) {
            $schoolAdmin->permissions()->sync($allPermissionIds);
        }

        $academicManager = Role::where('slug', 'academic-manager')->first();
        if ($academicManager) {
            $academicManager->permissions()->sync(
                Permission::whereIn('slug', [
                    'view-dashboard',
                    'view-academic-setup',
                    'manage-academic-sessions',
                    'manage-classes',
                    'manage-sections',
                    'manage-subjects',
                    'view-staff',
                    'manage-staff',
                    'view-teachers',
                    'manage-teachers',
                    'view-teacher-subject-assignments',
                    'manage-teacher-subject-assignments',
                    'view-students',
                    'manage-students',
                    'view-guardians',
                    'manage-guardians',
                    'view-attendance',
                    'manage-attendance',
                    'view-routines',
                    'manage-routines',
                    'view-exams',
                    'manage-exams',
                    'view-marks-entry',
                    'manage-marks-entry',
                    'view-results',
                    'manage-results',
                    'manage-grade-scales',
                    'view-admin-guide',
                ])->pluck('id')
            );
        }

        $contentManager = Role::where('slug', 'content-manager')->first();
        if ($contentManager) {
            $contentManager->permissions()->sync(
                Permission::whereIn('slug', [
                    'view-dashboard',
                    'view-settings',
                    'view-notices',
                    'create-notices',
                    'edit-notices',
                    'publish-notices',
                    'delete-notices',
                    'view-news-posts',
                    'create-news-posts',
                    'edit-news-posts',
                    'delete-news-posts',
                    'publish-news-posts',
                    'view-events',
                    'create-events',
                    'edit-events',
                    'delete-events',
                    'publish-events',
                    'view-gallery',
                    'manage-gallery',
                    'view-downloads',
                    'create-downloads',
                    'edit-downloads',
                    'delete-downloads',
                    'publish-downloads',
                    'view-pages',
                    'create-pages',
                    'edit-pages',
                    'delete-pages',
                    'publish-pages',
                    'manage-banners',
                    'manage-homepage-sections',
                    'view-admin-guide',
                ])->pluck('id')
            );
        }

        $reportManager = Role::where('slug', 'report-manager')->first();
        if ($reportManager) {
            $reportManager->permissions()->sync(
                Permission::whereIn('slug', [
                    'view-dashboard',
                    'view-reports',
                    'export-reports',
                    'view-admin-guide',
                ])->pluck('id')
            );
        }
    }
}
