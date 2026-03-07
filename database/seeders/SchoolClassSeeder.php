<?php

namespace Database\Seeders;

use App\Models\School;
use App\Models\SchoolClass;
use App\Services\SchoolClassService;
use Illuminate\Database\Seeder;

class SchoolClassSeeder extends Seeder
{
    public function run(): void
    {
        $school = School::first();
        if (! $school) {
            return;
        }

        $service = app(SchoolClassService::class);
        $schoolId = $school->id;

        $classes = [
            ['name' => 'Play', 'numeric_level' => null],
            ['name' => 'Nursery', 'numeric_level' => null],
            ['name' => 'KG', 'numeric_level' => null],
            ['name' => 'Class 1', 'numeric_level' => 1],
            ['name' => 'Class 2', 'numeric_level' => 2],
            ['name' => 'Class 3', 'numeric_level' => 3],
            ['name' => 'Class 4', 'numeric_level' => 4],
            ['name' => 'Class 5', 'numeric_level' => 5],
            ['name' => 'Class 6', 'numeric_level' => 6],
            ['name' => 'Class 7', 'numeric_level' => 7],
            ['name' => 'Class 8', 'numeric_level' => 8],
            ['name' => 'Class 9', 'numeric_level' => 9],
            ['name' => 'Class 10', 'numeric_level' => 10],
        ];

        foreach ($classes as $data) {
            $code = $service->generateCode($data['name'], $data['numeric_level'], null, $schoolId);
            SchoolClass::firstOrCreate(
                [
                    'school_id' => $schoolId,
                    'name' => $data['name'],
                ],
                [
                    'code' => $code,
                    'numeric_level' => $data['numeric_level'],
                    'description' => null,
                    'status' => 'active',
                ]
            );
        }
    }
}
