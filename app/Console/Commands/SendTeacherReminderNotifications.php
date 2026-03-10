<?php

namespace App\Console\Commands;

use App\Models\AcademicSession;
use App\Models\AttendanceSession;
use App\Models\ClassRoutineItem;
use App\Models\Exam;
use App\Models\Teacher;
use App\Services\TeacherNotificationService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SendTeacherReminderNotifications extends Command
{
    protected $signature = 'teacher:send-reminder-notifications';

    protected $description = 'Create notifications for teachers: attendance due today, marks deadline tomorrow.';

    public function handle(): int
    {
        $today = Carbon::today();
        $todayDay = strtolower($today->format('l'));

        $sessions = AcademicSession::query()->where('is_current', true)->get();
        foreach ($sessions as $session) {
            $routineItems = ClassRoutineItem::query()
                ->whereHas('classRoutine', fn ($q) => $q->where('academic_session_id', $session->id))
                ->where('day_of_week', $todayDay)
                ->with('classRoutine')
                ->get();

            $teacherIds = $routineItems->pluck('teacher_id')->unique();
            foreach ($teacherIds as $teacherId) {
                $teacher = Teacher::find($teacherId);
                if (! $teacher || $teacher->school_id !== $session->school_id) {
                    continue;
                }
                $user = $teacher->staff?->user;
                if (! $user) {
                    continue;
                }
                $pairs = $routineItems->where('teacher_id', $teacherId)->map(fn ($r) => [
                    'class_id' => $r->classRoutine?->class_id,
                    'section_id' => $r->classRoutine?->section_id,
                ])->unique(fn ($p) => $p['class_id'].'_'.$p['section_id']);

                foreach ($pairs as $pair) {
                    $exists = AttendanceSession::query()
                        ->where('academic_session_id', $session->id)
                        ->where('class_id', $pair['class_id'])
                        ->where('section_id', $pair['section_id'])
                        ->whereDate('attendance_date', $today)
                        ->exists();
                    if (! $exists) {
                        TeacherNotificationService::create(
                            $user->id,
                            $session->school_id,
                            'attendance_reminder',
                            'Attendance due today',
                            'You have classes today. Please take attendance.',
                            []
                        );
                        break;
                    }
                }
            }
        }

        $tomorrow = Carbon::tomorrow();
        $exams = Exam::query()
            ->whereDate('end_date', $tomorrow)
            ->where('status', '!=', 'cancelled')
            ->get();

        foreach ($exams as $exam) {
            $teachers = Teacher::query()
                ->where('school_id', $exam->school_id)
                ->whereHas('subjectAssignments', fn ($q) => $q->where('academic_session_id', $exam->academic_session_id)->active())
                ->get();

            foreach ($teachers as $teacher) {
                $user = $teacher->staff?->user;
                if (! $user) {
                    continue;
                }
                TeacherNotificationService::create(
                    $user->id,
                    $exam->school_id,
                    'marks_deadline',
                    'Marks deadline tomorrow',
                    "Marks for exam \"{$exam->name}\" are due tomorrow.",
                    ['exam_id' => $exam->id]
                );
            }
        }

        return self::SUCCESS;
    }
}
