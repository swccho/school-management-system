<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\AcademicSession;
use App\Models\Exam;
use App\Models\ExamSubjectConfig;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ExamController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $user->load(['staff.teacher.subjectAssignments' => fn ($q) => $q->active()]);
        $teacher = $user->staff->teacher;
        $schoolId = $user->school_id;

        $sessionId = $request->input('academic_session_id');
        if (! $sessionId) {
            $current = AcademicSession::where('school_id', $schoolId)->where('is_current', true)->first();
            $sessionId = $current?->id;
        }

        $assignmentPairs = $teacher->subjectAssignments
            ->where('academic_session_id', $sessionId)
            ->map(fn ($a) => ['class_id' => $a->class_id, 'subject_id' => $a->subject_id])
            ->unique(fn ($a) => $a['class_id'] . '-' . $a['subject_id'])
            ->values();

        if ($assignmentPairs->isEmpty()) {
            return response()->json([]);
        }

        $examSubjectConfigs = ExamSubjectConfig::query()
            ->whereIn('class_id', $assignmentPairs->pluck('class_id'))
            ->whereIn('subject_id', $assignmentPairs->pluck('subject_id'))
            ->get();

        $examIds = $examSubjectConfigs->pluck('exam_id')->unique()->values();

        $exams = Exam::query()
            ->where('school_id', $schoolId)
            ->where('academic_session_id', $sessionId)
            ->whereIn('id', $examIds)
            ->with(['examType'])
            ->orderBy('start_date')
            ->get();

        $assignmentsByClassSubject = $teacher->subjectAssignments
            ->where('academic_session_id', $sessionId)
            ->filter(fn ($a) => $assignmentPairs->contains(fn ($p) => $p['class_id'] === $a->class_id && $p['subject_id'] === $a->subject_id));

        $list = $exams->map(function (Exam $exam) use ($assignmentsByClassSubject, $examSubjectConfigs) {
            $configs = $examSubjectConfigs->where('exam_id', $exam->id);
            $myConfigs = $configs->filter(function ($c) use ($assignmentsByClassSubject) {
                return $assignmentsByClassSubject->contains(fn ($a) => $a->class_id === $c->class_id && $a->subject_id === $c->subject_id);
            });
            $subjectsCount = $myConfigs->pluck('subject_id')->unique()->count();
            return [
                'id' => $exam->id,
                'name' => $exam->name,
                'code' => $exam->code,
                'exam_type' => $exam->examType?->name,
                'start_date' => $exam->start_date?->format('Y-m-d'),
                'end_date' => $exam->end_date?->format('Y-m-d'),
                'status' => $exam->status,
                'subjects_count' => $subjectsCount,
            ];
        });

        return response()->json($list->values());
    }
}
