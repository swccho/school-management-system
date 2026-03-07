<?php

namespace Database\Seeders;

use App\Models\School;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Services\SectionService;
use Illuminate\Database\Seeder;

class SectionSeeder extends Seeder
{
    public function run(): void
    {
        $school = School::first();
        if (! $school) {
            return;
        }

        $service = app(SectionService::class);
        $schoolId = $school->id;

        $classByName = fn (string $name) => SchoolClass::where('school_id', $schoolId)->where('name', $name)->first();

        $singleSectionClasses = ['Play', 'Nursery', 'KG'];
        $multiSectionClasses = ['Class 1', 'Class 2', 'Class 3', 'Class 4', 'Class 5', 'Class 6', 'Class 7', 'Class 8', 'Class 9', 'Class 10'];
        $sectionLetters = ['A', 'B', 'C'];

        foreach ($singleSectionClasses as $className) {
            $schoolClass = $classByName($className);
            if (! $schoolClass) {
                continue;
            }
            $code = $service->generateCode($schoolId, null);
            Section::firstOrCreate(
                [
                    'school_id' => $schoolId,
                    'class_id' => $schoolClass->id,
                    'name' => 'A',
                ],
                [
                    'code' => $code,
                    'room_no' => null,
                    'capacity' => null,
                    'description' => null,
                    'status' => 'active',
                ]
            );
        }

        foreach ($multiSectionClasses as $className) {
            $schoolClass = $classByName($className);
            if (! $schoolClass) {
                continue;
            }
            foreach ($sectionLetters as $letter) {
                $code = $service->generateCode($schoolId, null);
                Section::firstOrCreate(
                    [
                        'school_id' => $schoolId,
                        'class_id' => $schoolClass->id,
                        'name' => $letter,
                    ],
                    [
                        'code' => $code,
                        'room_no' => null,
                        'capacity' => null,
                        'description' => null,
                        'status' => 'active',
                    ]
                );
            }
        }
    }
}
