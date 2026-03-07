<?php

namespace Database\Seeders;

use App\Models\AcademicSession;
use App\Models\School;
use App\Models\Section;
use App\Models\Student;
use App\Models\StudentAcademicAssignment;
use Illuminate\Database\Seeder;

class StudentAcademicAssignmentSeeder extends Seeder
{
    public function run(): void
    {
        $school = School::first();
        if (! $school) {
            return;
        }

        $schoolId = $school->id;
        $currentSession = AcademicSession::where('school_id', $schoolId)->where('is_current', true)->first();
        if (! $currentSession) {
            return;
        }

        $students = Student::where('school_id', $schoolId)->where('status', 'active')->orderBy('id')->get();
        if ($students->isEmpty()) {
            return;
        }

        $sections = Section::where('school_id', $schoolId)
            ->whereHas('schoolClass', fn ($q) => $q->whereNotNull('numeric_level')->where('numeric_level', '>=', 1)->where('numeric_level', '<=', 10))
            ->with('schoolClass')
            ->orderBy('class_id')
            ->orderBy('name')
            ->get();

        if ($sections->isEmpty()) {
            return;
        }

        $rollPerSection = [];
        $assigned = 0;
        foreach ($students as $student) {
            $section = $sections->get($assigned % $sections->count());
            $key = $section->id;
            $rollPerSection[$key] = ($rollPerSection[$key] ?? 0) + 1;
            $rollNo = (string) $rollPerSection[$key];

            StudentAcademicAssignment::firstOrCreate(
                [
                    'school_id' => $schoolId,
                    'academic_session_id' => $currentSession->id,
                    'student_id' => $student->id,
                ],
                [
                    'class_id' => $section->class_id,
                    'section_id' => $section->id,
                    'roll_no' => $rollNo,
                    'status' => 'active',
                    'remarks' => null,
                ]
            );
            $assigned++;
        }
    }
}
