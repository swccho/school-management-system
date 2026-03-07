<?php

namespace Database\Seeders;

use App\Models\AcademicSession;
use App\Models\Exam;
use App\Models\ExamType;
use App\Models\School;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class ExamSeeder extends Seeder
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

        $halfYearly = ExamType::where('school_id', $schoolId)->where('name', 'Half-Yearly')->first();
        $annual = ExamType::where('school_id', $schoolId)->where('name', 'Annual')->first();
        if (! $halfYearly || ! $annual) {
            return;
        }

        $sessionStart = Carbon::parse($currentSession->start_date);
        $sessionEnd = Carbon::parse($currentSession->end_date);

        $exams = [
            [
                'name' => 'Half-Yearly '.$sessionStart->year,
                'code' => 'H1-'.$sessionStart->year,
                'exam_type_id' => $halfYearly->id,
                'start_date' => $sessionStart->copy()->addMonths(4),
                'end_date' => $sessionStart->copy()->addMonths(5),
                'result_publish_date' => $sessionStart->copy()->addMonths(6),
                'status' => 'published',
            ],
            [
                'name' => 'Annual '.$sessionStart->year,
                'code' => 'ANN-'.$sessionStart->year,
                'exam_type_id' => $annual->id,
                'start_date' => $sessionEnd->copy()->subMonths(2),
                'end_date' => $sessionEnd->copy()->subMonth(),
                'result_publish_date' => $sessionEnd,
                'status' => 'draft',
            ],
        ];

        foreach ($exams as $data) {
            Exam::firstOrCreate(
                [
                    'school_id' => $schoolId,
                    'academic_session_id' => $currentSession->id,
                    'name' => $data['name'],
                ],
                [
                    'exam_type_id' => $data['exam_type_id'],
                    'code' => $data['code'],
                    'start_date' => $data['start_date'],
                    'end_date' => $data['end_date'],
                    'result_publish_date' => $data['result_publish_date'],
                    'status' => $data['status'],
                    'description' => null,
                ]
            );
        }
    }
}
