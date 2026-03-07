<?php

namespace Database\Seeders;

use App\Models\School;
use App\Models\Student;
use App\Models\StudentGuardian;
use App\Models\StudentGuardianLink;
use Illuminate\Database\Seeder;

class StudentGuardianLinkSeeder extends Seeder
{
    public function run(): void
    {
        $school = School::first();
        if (! $school) {
            return;
        }

        $schoolId = $school->id;
        $students = Student::where('school_id', $schoolId)->orderBy('id')->get();
        $guardians = StudentGuardian::where('school_id', $schoolId)->orderBy('id')->get();

        if ($students->count() * 2 > $guardians->count()) {
            return;
        }

        foreach ($students as $i => $student) {
            $g1 = $guardians->get(2 * $i);
            $g2 = $guardians->get(2 * $i + 1);
            if (! $g1 || ! $g2) {
                continue;
            }

            StudentGuardianLink::firstOrCreate(
                [
                    'student_id' => $student->id,
                    'guardian_id' => $g1->id,
                ],
                [
                    'school_id' => $schoolId,
                    'relationship_label' => 'Father',
                    'is_primary' => true,
                    'can_receive_sms' => true,
                    'can_receive_email' => true,
                    'can_login' => false,
                ]
            );
            StudentGuardianLink::firstOrCreate(
                [
                    'student_id' => $student->id,
                    'guardian_id' => $g2->id,
                ],
                [
                    'school_id' => $schoolId,
                    'relationship_label' => 'Mother',
                    'is_primary' => false,
                    'can_receive_sms' => true,
                    'can_receive_email' => true,
                    'can_login' => false,
                ]
            );
        }
    }
}
