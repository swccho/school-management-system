<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\AcademicSession;
use App\Models\Exam;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StudentExamController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $user->load(['student']);
        $student = $user->student;

        $currentSession = AcademicSession::query()
            ->where('school_id', $user->school_id)
            ->where('is_current', true)
            ->first();

        if (! $currentSession) {
            return response()->json(['data' => []]);
        }

        $assignment = $student->studentAcademicAssignments()
            ->where('academic_session_id', $currentSession->id)
            ->active()
            ->first();

        if (! $assignment) {
            return response()->json(['data' => []]);
        }

        $examIds = \App\Models\ExamClassConfig::query()
            ->where('class_id', $assignment->class_id)
            ->where('section_id', $assignment->section_id)
            ->pluck('exam_id');

        $exams = Exam::query()
            ->whereIn('id', $examIds)
            ->where('academic_session_id', $currentSession->id)
            ->active()
            ->with(['examType', 'examSubjectConfigs' => fn ($q) => $q->where('class_id', $assignment->class_id)->with('subject')->orderBy('sort_order')])
            ->orderBy('start_date')
            ->get();

        $data = $exams->map(function ($exam) {
            $subjects = $exam->examSubjectConfigs->map(fn ($c) => [
                'subject_name' => $c->subject?->name,
                'full_marks' => $c->full_marks,
            ])->toArray();
            return [
                'id' => $exam->id,
                'name' => $exam->name,
                'code' => $exam->code,
                'exam_type' => $exam->examType ? ['id' => $exam->examType->id, 'name' => $exam->examType->name] : null,
                'start_date' => $exam->start_date?->format('Y-m-d'),
                'end_date' => $exam->end_date?->format('Y-m-d'),
                'description' => $exam->description,
                'subjects' => $subjects,
            ];
        })->toArray();

        return response()->json(['data' => $data]);
    }
}
