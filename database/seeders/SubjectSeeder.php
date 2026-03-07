<?php

namespace Database\Seeders;

use App\Models\School;
use App\Models\Subject;
use Illuminate\Database\Seeder;

class SubjectSeeder extends Seeder
{
    public function run(): void
    {
        $school = School::first();
        if (! $school) {
            return;
        }

        $schoolId = $school->id;

        $subjects = [
            ['name' => 'English', 'code' => 'ENG', 'short_name' => 'Eng', 'type' => 'general', 'full_marks' => 100, 'pass_marks' => 40],
            ['name' => 'Mathematics', 'code' => 'MATH', 'short_name' => 'Math', 'type' => 'general', 'full_marks' => 100, 'pass_marks' => 40],
            ['name' => 'Science', 'code' => 'SCI', 'short_name' => 'Sci', 'type' => 'general', 'full_marks' => 100, 'pass_marks' => 40, 'has_practical' => true],
            ['name' => 'Social Studies', 'code' => 'SST', 'short_name' => 'SST', 'type' => 'general', 'full_marks' => 100, 'pass_marks' => 40],
            ['name' => 'Bengali', 'code' => 'BEN', 'short_name' => 'Ben', 'type' => 'general', 'full_marks' => 100, 'pass_marks' => 40],
            ['name' => 'ICT', 'code' => 'ICT', 'short_name' => 'ICT', 'type' => 'general', 'full_marks' => 100, 'pass_marks' => 40],
            ['name' => 'Physical Education', 'code' => 'PE', 'short_name' => 'PE', 'type' => 'general', 'full_marks' => 50, 'pass_marks' => 20],
            ['name' => 'Arts', 'code' => 'ART', 'short_name' => 'Art', 'type' => 'general', 'full_marks' => 50, 'pass_marks' => 20],
            ['name' => 'Religion', 'code' => 'REL', 'short_name' => 'Rel', 'type' => 'general', 'full_marks' => 100, 'pass_marks' => 40],
            ['name' => 'Bangladesh and Global Studies', 'code' => 'BGS', 'short_name' => 'BGS', 'type' => 'general', 'full_marks' => 100, 'pass_marks' => 40],
        ];

        foreach ($subjects as $data) {
            $hasPractical = $data['has_practical'] ?? false;
            Subject::firstOrCreate(
                [
                    'school_id' => $schoolId,
                    'name' => $data['name'],
                ],
                [
                    'code' => $data['code'],
                    'short_name' => $data['short_name'],
                    'type' => $data['type'],
                    'is_optional' => false,
                    'has_practical' => $hasPractical,
                    'full_marks' => $data['full_marks'],
                    'pass_marks' => $data['pass_marks'],
                    'description' => null,
                    'status' => 'active',
                ]
            );
        }
    }
}
