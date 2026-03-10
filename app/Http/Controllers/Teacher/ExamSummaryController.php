<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Services\MarksEntryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ExamSummaryController extends Controller
{
    public function __construct(
        private MarksEntryService $marksEntryService
    ) {}

    /**
     * Class/subject-wise summary for an exam (teacher's assigned scope only).
     */
    public function show(Request $request, Exam $exam): JsonResponse
    {
        $user = $request->user();
        $user->load(['staff.teacher.subjectAssignments' => fn ($q) => $q->active()->with(['schoolClass', 'section', 'subject'])]);
        $teacher = $user->staff->teacher;

        $assignments = $teacher->subjectAssignments->where('academic_session_id', $exam->academic_session_id);
        $summary = [];

        foreach ($assignments as $a) {
            $contextSummary = $this->marksEntryService->getPerformanceSummaryForContext(
                $exam->id,
                $a->class_id,
                $a->section_id,
                $a->subject_id
            );

            $summary[] = [
                'class_name' => $a->schoolClass?->name,
                'section_name' => $a->section?->name,
                'subject_name' => $a->subject?->name,
                'students_count' => $contextSummary['students_count'],
                'total_obtained' => $contextSummary['total_obtained'],
                'average_marks' => $contextSummary['average_marks'],
                'highest_marks' => $contextSummary['highest_marks'],
                'lowest_marks' => $contextSummary['lowest_marks'],
                'pass_marks' => $contextSummary['pass_marks'],
                'failed_count' => $contextSummary['failed_count'],
                'failed_students' => $contextSummary['failed_students'],
            ];
        }

        return response()->json([
            'exam' => ['id' => $exam->id, 'name' => $exam->name],
            'summary' => $summary,
        ]);
    }
}
