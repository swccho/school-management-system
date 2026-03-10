<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\AcademicSession;
use App\Services\TeacherAnalyticsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AnalyticsController extends Controller
{
    public function attendanceTrends(Request $request): JsonResponse
    {
        $service = $this->makeService($request);
        return response()->json($service->attendanceTrends());
    }

    public function homeworkTrends(Request $request): JsonResponse
    {
        $service = $this->makeService($request);
        return response()->json($service->homeworkTrends());
    }

    public function marksTrends(Request $request): JsonResponse
    {
        $service = $this->makeService($request);
        return response()->json($service->marksTrends());
    }

    public function studentsNeedingAttention(Request $request): JsonResponse
    {
        $service = $this->makeService($request);
        $attendanceThreshold = (float) $request->input('attendance_threshold', 80);
        $marksThreshold = (float) $request->input('marks_threshold', 40);
        $list = $service->studentsNeedingAttention($attendanceThreshold, $marksThreshold);
        return response()->json(['data' => $list]);
    }

    protected function makeService(Request $request): TeacherAnalyticsService
    {
        $user = $request->user();
        $user->load(['staff.teacher']);
        $teacher = $user->staff->teacher;
        $schoolId = $user->school_id;

        $academicSessionId = $request->input('academic_session_id');
        if (! $academicSessionId) {
            $session = AcademicSession::where('school_id', $schoolId)->where('is_current', true)->first();
            $academicSessionId = $session?->id;
        } else {
            $session = AcademicSession::where('school_id', $schoolId)->find($academicSessionId);
            if (! $session) {
                $academicSessionId = null;
            }
        }

        $classId = $request->filled('class_id') ? (int) $request->class_id : null;
        $sectionId = $request->filled('section_id') ? (int) $request->section_id : null;
        $subjectId = $request->filled('subject_id') ? (int) $request->subject_id : null;
        $allowedPairs = $teacher->subjectAssignments()
            ->where('academic_session_id', $academicSessionId)
            ->active()
            ->get()
            ->map(fn ($a) => ['class_id' => $a->class_id, 'section_id' => $a->section_id]);
        $allowedClassIds = $allowedPairs->pluck('class_id')->unique();
        $allowedSectionIds = $allowedPairs->pluck('section_id')->unique();
        $allowedSubjectIds = $teacher->subjectAssignments()->where('academic_session_id', $academicSessionId)->active()->pluck('subject_id')->unique();
        if ($classId !== null && ! $allowedClassIds->contains($classId)) {
            $classId = null;
        }
        if ($sectionId !== null && ! $allowedSectionIds->contains($sectionId)) {
            $sectionId = null;
        }
        if ($subjectId !== null && ! $allowedSubjectIds->contains($subjectId)) {
            $subjectId = null;
        }

        return new TeacherAnalyticsService(
            $teacher,
            $academicSessionId,
            $classId,
            $sectionId,
            $subjectId,
            $request->input('date_from'),
            $request->input('date_to')
        );
    }
}
