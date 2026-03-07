<?php

namespace Database\Seeders;

use App\Models\School;
use App\Models\Student;
use App\Models\StudentGuardian;
use Illuminate\Database\Seeder;

class StudentGuardianSeeder extends Seeder
{
    public function run(): void
    {
        $school = School::first();
        if (! $school) {
            return;
        }

        $schoolId = $school->id;
        $students = Student::where('school_id', $schoolId)->orderBy('id')->get();
        if ($students->isEmpty()) {
            return;
        }

        foreach ($students as $index => $student) {
            $name = $student->first_name.' '.$student->last_name;
            $base = $index + 1;

            $father = StudentGuardian::firstOrCreate(
                [
                    'school_id' => $schoolId,
                    'phone' => '017'.str_pad((string) (10000000 + $index * 2), 8, '0', STR_PAD_LEFT),
                ],
                [
                    'user_id' => null,
                    'name' => 'Father of '.$name,
                    'relation_type' => 'Father',
                    'email' => 'father'.$base.'@guardian.demoschool.edu',
                    'occupation' => 'Business',
                    'address' => 'Demo Address, Dhaka',
                    'photo_path' => null,
                    'status' => 'active',
                ]
            );

            $mother = StudentGuardian::firstOrCreate(
                [
                    'school_id' => $schoolId,
                    'phone' => '017'.str_pad((string) (10000001 + $index * 2), 8, '0', STR_PAD_LEFT),
                ],
                [
                    'user_id' => null,
                    'name' => 'Mother of '.$name,
                    'relation_type' => 'Mother',
                    'email' => 'mother'.$base.'@guardian.demoschool.edu',
                    'occupation' => 'Homemaker',
                    'address' => 'Demo Address, Dhaka',
                    'photo_path' => null,
                    'status' => 'active',
                ]
            );
        }
    }
}
