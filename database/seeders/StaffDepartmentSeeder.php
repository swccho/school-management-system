<?php

namespace Database\Seeders;

use App\Models\School;
use App\Models\StaffDepartment;
use App\Services\StaffDepartmentService;
use Illuminate\Database\Seeder;

class StaffDepartmentSeeder extends Seeder
{
    public function run(): void
    {
        $school = School::first();
        if (! $school) {
            return;
        }

        $service = app(StaffDepartmentService::class);
        $schoolId = $school->id;

        $departments = [
            ['name' => 'Administration', 'description' => 'School administration and management'],
            ['name' => 'Accounts', 'description' => 'Finance and accounts'],
            ['name' => 'Teaching (Science)', 'description' => 'Science stream teachers'],
            ['name' => 'Teaching (Arts)', 'description' => 'Arts stream teachers'],
            ['name' => 'Teaching (Commerce)', 'description' => 'Commerce stream teachers'],
            ['name' => 'Support Staff', 'description' => 'Office and support staff'],
        ];

        foreach ($departments as $data) {
            $code = $service->generateDepartmentCode($schoolId);
            StaffDepartment::firstOrCreate(
                [
                    'school_id' => $schoolId,
                    'name' => $data['name'],
                ],
                [
                    'code' => $code,
                    'description' => $data['description'] ?? null,
                    'status' => 'active',
                ]
            );
        }
    }
}
