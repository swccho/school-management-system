<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\AcademicSession;
use App\Models\ExamType;
use App\Models\Exam;
use App\Models\ExamSubjectConfig;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\Subject;
use Carbon\Carbon;
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

        $assignments = $teacher->subjectAssignments->where('academic_session_id', $sessionId);

        if ($request->filled('class_id')) {
            $assignments = $assignments->where('class_id', (int) $request->class_id);
        }
        if ($request->filled('section_id')) {
            $assignments = $assignments->where('section_id', (int) $request->section_id);
        }
        if ($request->filled('subject_id')) {
            $assignments = $assignments->where('subject_id', (int) $request->subject_id);
        }

        $assignmentPairs = $assignments
            ->map(fn ($a) => ['class_id' => $a->class_id, 'section_id' => $a->section_id, 'subject_id' => $a->subject_id])
            ->unique(fn ($a) => $a['class_id'] . '-' . ($a['section_id'] ?? '') . '-' . $a['subject_id'])
            ->values();

        if ($assignmentPairs->isEmpty()) {
            return response()->json([]);
        }

        $examSubjectConfigs = ExamSubjectConfig::query()
            ->whereIn('class_id', $assignmentPairs->pluck('class_id'))
            ->whereIn('subject_id', $assignmentPairs->pluck('subject_id'))
            ->get();

        $examIds = $examSubjectConfigs->pluck('exam_id')->unique()->values();

        $query = Exam::query()
            ->where('school_id', $schoolId)
            ->where('academic_session_id', $sessionId)
            ->whereIn('id', $examIds)
            ->with(['examType']);

        if ($request->filled('exam_type_id')) {
            $query->where('exam_type_id', (int) $request->exam_type_id);
        }

        $exams = $query->orderBy('start_date')->get();

        $assignmentsByClassSubject = $teacher->subjectAssignments
            ->where('academic_session_id', $sessionId)
            ->filter(fn ($a) => $assignmentPairs->contains(fn ($p) => $p['class_id'] === $a->class_id && ($p['section_id'] ?? null) === $a->section_id && $p['subject_id'] === $a->subject_id));

        $today = Carbon::today();

        $list = $exams->map(function (Exam $exam) use ($assignmentsByClassSubject, $examSubjectConfigs, $today) {
            $configs = $examSubjectConfigs->where('exam_id', $exam->id);
            $myConfigs = $configs->filter(function ($c) use ($assignmentsByClassSubject) {
                return $assignmentsByClassSubject->contains(fn ($a) => $a->class_id === $c->class_id && $a->subject_id === $c->subject_id);
            });
            $subjectsCount = $myConfigs->pluck('subject_id')->unique()->count();
            $startDate = $exam->start_date ? Carbon::parse($exam->start_date) : null;
            $endDate = $exam->end_date ? Carbon::parse($exam->end_date) : null;
            $marksEntryOpen = $startDate && $endDate && $today->between($startDate, $endDate);
            return [
                'id' => $exam->id,
                'name' => $exam->name,
                'code' => $exam->code,
                'exam_type' => $exam->examType?->name,
                'exam_type_id' => $exam->exam_type_id,
                'start_date' => $exam->start_date?->format('Y-m-d'),
                'end_date' => $exam->end_date?->format('Y-m-d'),
                'status' => $exam->status,
                'subjects_count' => $subjectsCount,
                'marks_entry_open' => $marksEntryOpen,
            ];
        });

        return response()->json($list->values());
    }

    public function show(Request $request, Exam $exam): JsonResponse
    {
        $user = $request->user();
        $user->load(['staff.teacher.subjectAssignments' => fn ($q) => $q->active()->with(['schoolClass', 'section', 'subject'])]);
        $teacher = $user->staff->teacher;

        $assignments = $teacher->subjectAssignments->where('academic_session_id', $exam->academic_session_id);
        $configs = $exam->examSubjectConfigs;
        $contexts = [];
        foreach ($assignments as $a) {
            $config = $configs->firstWhere(fn ($c) => $c->class_id === $a->class_id && $c->subject_id === $a->subject_id);
            if (! $config) {
                continue;
            }
            $contexts[] = [
                'class_id' => $a->class_id,
                'section_id' => $a->section_id,
                'subject_id' => $a->subject_id,
                'class_name' => $a->schoolClass?->name,
                'section_name' => $a->section?->name,
                'subject_name' => $a->subject?->name,
            ];
        }
        $contexts = array_values(array_unique($contexts, SORT_REGULAR));

        if (empty($contexts)) {
            return response()->json(['message' => 'You have no assigned subjects for this exam.'], 403);
        }

        $startDate = $exam->start_date ? \Carbon\Carbon::parse($exam->start_date) : null;
        $endDate = $exam->end_date ? \Carbon\Carbon::parse($exam->end_date) : null;
        $marksEntryOpen = $startDate && $endDate && \Carbon\Carbon::today()->between($startDate, $endDate);

        return response()->json([
            'id' => $exam->id,
            'name' => $exam->name,
            'code' => $exam->code,
            'exam_type' => $exam->examType?->name,
            'start_date' => $exam->start_date?->format('Y-m-d'),
            'end_date' => $exam->end_date?->format('Y-m-d'),
            'status' => $exam->status,
            'marks_entry_open' => $marksEntryOpen,
            'contexts' => $contexts,
        ]);
    }

    public function filterOptions(Request $request): JsonResponse
    {
        $user = $request->user();
        $user->load(['staff.teacher.subjectAssignments' => fn ($q) => $q->active()->with(['schoolClass', 'section', 'subject'])]);
        $teacher = $user->staff->teacher;
        $schoolId = $user->school_id;

        $academicSessions = AcademicSession::where('school_id', $schoolId)
            ->orderBy('start_date', 'desc')
            ->get(['id', 'name', 'code', 'is_current'])
            ->map(fn ($s) => ['id' => $s->id, 'name' => $s->name, 'code' => $s->code, 'is_current' => $s->is_current]);

        $classIds = $teacher->subjectAssignments->pluck('class_id')->unique()->filter()->values();
        $sectionIds = $teacher->subjectAssignments->pluck('section_id')->unique()->filter()->values();
        $subjectIds = $teacher->subjectAssignments->pluck('subject_id')->unique()->filter()->values();

        $classes = SchoolClass::whereIn('id', $classIds)->orderBy('name')->get(['id', 'name'])->map(fn ($c) => ['id' => $c->id, 'name' => $c->name]);
        $sections = Section::whereIn('id', $sectionIds)->orderBy('name')->get(['id', 'name'])->map(fn ($s) => ['id' => $s->id, 'name' => $s->name]);
        $subjects = Subject::whereIn('id', $subjectIds)->orderBy('name')->get(['id', 'name'])->map(fn ($s) => ['id' => $s->id, 'name' => $s->name]);
        $examTypes = ExamType::where('school_id', $schoolId)->where('status', 'active')->orderBy('name')->get(['id', 'name'])->map(fn ($t) => ['id' => $t->id, 'name' => $t->name]);

        return response()->json([
            'academic_sessions' => $academicSessions,
            'classes' => $classes,
            'sections' => $sections,
            'subjects' => $subjects,
            'exam_types' => $examTypes,
        ]);
    }
}
