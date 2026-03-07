<?php

namespace Database\Seeders;

use App\Models\ExamType;
use App\Models\School;
use Illuminate\Database\Seeder;

class ExamTypeSeeder extends Seeder
{
    public function run(): void
    {
        $school = School::first();
        if (! $school) {
            return;
        }

        $schoolId = $school->id;

        $types = [
            ['name' => 'Half-Yearly', 'code' => 'EXT-001'],
            ['name' => 'Annual', 'code' => 'EXT-002'],
            ['name' => 'Monthly', 'code' => 'EXT-003'],
            ['name' => 'Class Test', 'code' => 'EXT-004'],
        ];

        foreach ($types as $i => $data) {
            ExamType::firstOrCreate(
                [
                    'school_id' => $schoolId,
                    'name' => $data['name'],
                ],
                [
                    'code' => $data['code'],
                    'description' => null,
                    'status' => 'active',
                ]
            );
        }
    }
}
