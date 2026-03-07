<?php

namespace Database\Seeders;

use App\Models\AcademicSession;
use App\Models\AttendanceSession;
use App\Models\School;
use App\Models\Section;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class AttendanceSessionSeeder extends Seeder
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

        $takenBy = User::where('email', 'admin@example.com')->first()?->id;
        if (! $takenBy) {
            return;
        }

        $sections = Section::where('school_id', $schoolId)
            ->whereHas('schoolClass', fn ($q) => $q->where('numeric_level', 6))
            ->with('schoolClass')
            ->orderBy('name')
            ->take(2)
            ->get();

        if ($sections->isEmpty()) {
            return;
        }

        $today = Carbon::today();
        for ($d = 0; $d < 5; $d++) {
            $date = $today->copy()->subDays($d);
            if ($date->isWeekend()) {
                continue;
            }
            foreach ($sections as $section) {
                AttendanceSession::firstOrCreate(
                    [
                        'school_id' => $schoolId,
                        'academic_session_id' => $currentSession->id,
                        'class_id' => $section->class_id,
                        'section_id' => $section->id,
                        'attendance_date' => $date,
                    ],
                    [
                        'taken_by' => $takenBy,
                        'status' => 'taken',
                        'remarks' => null,
                    ]
                );
            }
        }
    }
}
