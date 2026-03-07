<?php

namespace Database\Seeders;

use App\Models\AcademicSession;
use App\Models\School;
use App\Models\Section;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\TeacherSubjectAssignment;
use Illuminate\Database\Seeder;

class TeacherSubjectAssignmentSeeder extends Seeder
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

        $teachers = Teacher::where('school_id', $schoolId)->where('status', 'active')->orderBy('id')->get();
        $subjects = Subject::where('school_id', $schoolId)->where('status', 'active')->orderBy('id')->get();
        $sections = Section::where('school_id', $schoolId)
            ->whereHas('schoolClass', fn ($q) => $q->whereNotNull('numeric_level')->where('numeric_level', '>=', 6)->where('numeric_level', '<=', 8))
            ->with('schoolClass')
            ->orderBy('class_id')
            ->orderBy('name')
            ->get();

        if ($teachers->isEmpty() || $subjects->isEmpty() || $sections->isEmpty()) {
            return;
        }

        $subjectNames = ['Mathematics', 'English', 'Science', 'Bengali', 'Social Studies'];
        $subjectIds = $subjects->whereIn('name', $subjectNames)->pluck('id')->values()->all();
        if (empty($subjectIds)) {
            $subjectIds = $subjects->pluck('id')->take(5)->all();
        }

        foreach ($teachers as $tIndex => $teacher) {
            $section = $sections->get($tIndex % $sections->count());
            $subjectId = $subjectIds[$tIndex % count($subjectIds)];

            TeacherSubjectAssignment::firstOrCreate(
                [
                    'school_id' => $schoolId,
                    'academic_session_id' => $currentSession->id,
                    'teacher_id' => $teacher->id,
                    'class_id' => $section->class_id,
                    'section_id' => $section->id,
                    'subject_id' => $subjectId,
                ],
                [
                    'status' => 'active',
                    'remarks' => null,
                ]
            );
        }
    }
}
