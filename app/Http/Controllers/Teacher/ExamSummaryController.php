<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\MarkEntry;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ExamSummaryController extends Controller
{
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
            $entries = MarkEntry::query()
                ->where('exam_id', $exam->id)
                ->where('class_id', $a->class_id)
                ->where('section_id', $a->section_id)
                ->where('subject_id', $a->subject_id)
                ->with('markEntryItems')
                ->get();

            $totalObtained = $entries->sum(fn ($e) => $e->markEntryItems->sum('obtained_marks'));
            $count = $entries->count();
            $average = $count > 0 ? round($totalObtained / $count, 2) : 0;

            $summary[] = [
                'class_name' => $a->schoolClass?->name,
                'section_name' => $a->section?->name,
                'subject_name' => $a->subject?->name,
                'students_count' => $count,
                'average_marks' => $average,
                'total_obtained' => $totalObtained,
            ];
        }

        return response()->json([
            'exam' => ['id' => $exam->id, 'name' => $exam->name],
            'summary' => $summary,
        ]);
    }
}
