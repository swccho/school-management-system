<?php

namespace Database\Seeders;

use App\Models\School;
use App\Models\Staff;
use App\Models\Teacher;
use App\Services\TeacherService;
use Illuminate\Database\Seeder;

class TeacherSeeder extends Seeder
{
    public function run(): void
    {
        $school = School::first();
        if (! $school) {
            return;
        }

        $service = app(TeacherService::class);
        $schoolId = $school->id;

        $teacherStaff = Staff::where('school_id', $schoolId)
            ->where('employee_type', 'teacher')
            ->orderBy('id')
            ->get();

        $classTeacherCount = 0;

        foreach ($teacherStaff as $staff) {
            if (Teacher::where('staff_id', $staff->id)->exists()) {
                continue;
            }

            $teacherCode = $service->generateTeacherCode($schoolId);
            $isClassTeacher = $classTeacherCount < 2;

            Teacher::create([
                'school_id' => $schoolId,
                'staff_id' => $staff->id,
                'teacher_code' => $teacherCode,
                'qualification' => 'M.Sc. / M.A. / B.Ed.',
                'specialization' => 'General',
                'experience_years' => rand(3, 15),
                'is_class_teacher' => $isClassTeacher,
                'status' => 'active',
            ]);

            if ($isClassTeacher) {
                $classTeacherCount++;
            }
        }
    }
}
