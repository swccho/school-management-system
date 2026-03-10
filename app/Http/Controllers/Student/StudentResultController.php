<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\ResultSummary;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StudentResultController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $user->load(['student']);
        $student = $user->student;

        $summaries = ResultSummary::query()
            ->where('student_id', $student->id)
            ->published()
            ->with(['exam.academicSession', 'exam.examType', 'resultSubjectDetails.subject'])
            ->orderByDesc('generated_at')
            ->get();

        $data = $summaries->map(function ($rs) {
            $subjectDetails = $rs->resultSubjectDetails->map(fn ($d) => [
                'subject_name' => $d->subject?->name,
                'full_marks' => $d->full_marks,
                'obtained_marks' => $d->obtained_marks,
                'grade_letter' => $d->grade_letter,
                'grade_point' => $d->grade_point,
                'pass_status' => $d->pass_status,
                'remarks' => $d->remarks,
            ])->toArray();
            return [
                'id' => $rs->id,
                'exam_id' => $rs->exam_id,
                'exam_name' => $rs->exam?->name,
                'exam_code' => $rs->exam?->code,
                'academic_session' => $rs->exam?->academicSession ? [
                    'id' => $rs->exam->academicSession->id,
                    'name' => $rs->exam->academicSession->name,
                ] : null,
                'exam_type' => $rs->exam?->examType ? [
                    'id' => $rs->exam->examType->id,
                    'name' => $rs->exam->examType->name,
                ] : null,
                'total_marks' => $rs->total_marks,
                'obtained_marks' => $rs->obtained_marks,
                'gpa' => $rs->gpa,
                'letter_grade' => $rs->letter_grade,
                'merit_position' => $rs->merit_position,
                'pass_status' => $rs->pass_status,
                'remarks' => $rs->remarks,
                'generated_at' => $rs->generated_at?->toIso8601String(),
                'subject_details' => $subjectDetails,
            ];
        })->toArray();

        return response()->json(['data' => $data]);
    }
}
