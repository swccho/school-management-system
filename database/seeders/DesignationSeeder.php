<?php

namespace Database\Seeders;

use App\Models\Designation;
use App\Models\School;
use App\Models\StaffDepartment;
use App\Services\DesignationService;
use Illuminate\Database\Seeder;

class DesignationSeeder extends Seeder
{
    public function run(): void
    {
        $school = School::first();
        if (! $school) {
            return;
        }

        $service = app(DesignationService::class);
        $schoolId = $school->id;

        $dept = fn (string $name) => StaffDepartment::where('school_id', $schoolId)->where('name', $name)->first()?->id;

        $designations = [
            ['name' => 'Principal', 'department' => 'Administration'],
            ['name' => 'Vice Principal', 'department' => 'Administration'],
            ['name' => 'Senior Teacher', 'department' => 'Teaching (Science)'],
            ['name' => 'Teacher', 'department' => 'Teaching (Science)'],
            ['name' => 'Accountant', 'department' => 'Accounts'],
            ['name' => 'Office Assistant', 'department' => 'Support Staff'],
            ['name' => 'Lab Assistant', 'department' => 'Support Staff'],
        ];

        foreach ($designations as $data) {
            $code = $service->generateDesignationCode($schoolId);
            $departmentId = $dept($data['department']);
            Designation::firstOrCreate(
                [
                    'school_id' => $schoolId,
                    'name' => $data['name'],
                ],
                [
                    'department_id' => $departmentId,
                    'code' => $code,
                    'description' => null,
                    'status' => 'active',
                ]
            );
        }
    }
}
