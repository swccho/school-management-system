<?php

namespace Database\Seeders;

use App\Models\AcademicSession;
use App\Models\ClassRoutine;
use App\Models\ClassRoutineItem;
use App\Models\School;
use App\Models\Section;
use App\Models\Subject;
use App\Models\Teacher;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class ClassRoutineSeeder extends Seeder
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

        $section = Section::where('school_id', $schoolId)
            ->whereHas('schoolClass', fn ($q) => $q->where('numeric_level', 6))
            ->where('name', 'A')
            ->first();
        if (! $section) {
            return;
        }

        $routine = ClassRoutine::firstOrCreate(
            [
                'school_id' => $schoolId,
                'academic_session_id' => $currentSession->id,
                'class_id' => $section->class_id,
                'section_id' => $section->id,
            ],
            [
                'title' => 'Class 6-A Routine',
                'effective_from' => Carbon::parse($currentSession->start_date),
                'effective_to' => Carbon::parse($currentSession->end_date),
                'status' => 'active',
                'remarks' => null,
            ]
        );

        $subjects = Subject::where('school_id', $schoolId)->where('status', 'active')->orderBy('id')->take(5)->get();
        $teachers = Teacher::where('school_id', $schoolId)->where('status', 'active')->orderBy('id')->take(5)->get();
        if ($subjects->isEmpty() || $teachers->isEmpty()) {
            return;
        }

        $slots = [
            ['start' => '09:00', 'end' => '09:45'],
            ['start' => '09:45', 'end' => '10:30'],
            ['start' => '10:50', 'end' => '11:35'],
            ['start' => '11:35', 'end' => '12:20'],
        ];
        $days = ['sunday', 'monday', 'tuesday', 'wednesday', 'thursday'];

        foreach ($days as $dayIndex => $day) {
            foreach ($slots as $periodIndex => $slot) {
                $subject = $subjects->get(($dayIndex + $periodIndex) % $subjects->count());
                $teacher = $teachers->get(($dayIndex + $periodIndex) % $teachers->count());
                ClassRoutineItem::firstOrCreate(
                    [
                        'school_id' => $schoolId,
                        'class_routine_id' => $routine->id,
                        'day_of_week' => $day,
                        'period_no' => $periodIndex + 1,
                    ],
                    [
                        'start_time' => $slot['start'],
                        'end_time' => $slot['end'],
                        'subject_id' => $subject->id,
                        'teacher_id' => $teacher->id,
                        'room_label' => 'Room '.($periodIndex + 101),
                        'remarks' => null,
                    ]
                );
            }
        }
    }
}
