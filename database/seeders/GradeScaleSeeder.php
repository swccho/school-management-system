<?php

namespace Database\Seeders;

use App\Models\GradeScale;
use App\Models\GradeScaleItem;
use App\Models\School;
use Illuminate\Database\Seeder;

class GradeScaleSeeder extends Seeder
{
    public function run(): void
    {
        $school = School::first();
        if (! $school) {
            return;
        }

        $schoolId = $school->id;

        $scale = GradeScale::firstOrCreate(
            [
                'school_id' => $schoolId,
                'name' => 'Letter Grade',
            ],
            [
                'is_default' => true,
                'status' => 'active',
            ]
        );

        $items = [
            ['min_mark' => 80, 'max_mark' => 100, 'letter_grade' => 'A+', 'grade_point' => 5.00, 'remarks' => 'Outstanding'],
            ['min_mark' => 70, 'max_mark' => 79, 'letter_grade' => 'A', 'grade_point' => 4.00, 'remarks' => 'Excellent'],
            ['min_mark' => 60, 'max_mark' => 69, 'letter_grade' => 'B', 'grade_point' => 3.00, 'remarks' => 'Good'],
            ['min_mark' => 50, 'max_mark' => 59, 'letter_grade' => 'C', 'grade_point' => 2.00, 'remarks' => 'Average'],
            ['min_mark' => 40, 'max_mark' => 49, 'letter_grade' => 'D', 'grade_point' => 1.00, 'remarks' => 'Pass'],
            ['min_mark' => 0, 'max_mark' => 39, 'letter_grade' => 'F', 'grade_point' => 0.00, 'remarks' => 'Fail'],
        ];

        foreach ($items as $data) {
            GradeScaleItem::firstOrCreate(
                [
                    'school_id' => $schoolId,
                    'grade_scale_id' => $scale->id,
                    'letter_grade' => $data['letter_grade'],
                ],
                [
                    'min_mark' => $data['min_mark'],
                    'max_mark' => $data['max_mark'],
                    'grade_point' => $data['grade_point'],
                    'remarks' => $data['remarks'],
                ]
            );
        }
    }
}
