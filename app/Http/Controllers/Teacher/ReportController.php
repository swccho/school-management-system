<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\AcademicSession;
use App\Models\AttendanceRecord;
use App\Models\AttendanceSession;
use App\Models\Exam;
use App\Models\MarkEntry;
use App\Models\MarkEntryItem;
use App\Models\Student;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function attendanceSummary(Request $request): StreamedResponse|\Illuminate\Http\JsonResponse
    {
        $request->validate([
            'academic_session_id' => ['required', 'exists:academic_sessions,id'],
            'class_id' => ['required', 'exists:school_classes,id'],
            'section_id' => ['required', 'exists:sections,id'],
            'date_from' => ['required', 'date'],
            'date_to' => ['required', 'date', 'after_or_equal:date_from'],
            'format' => ['nullable', 'in:csv'],
        ]);

        $user = $request->user();
        $user->load(['staff.teacher']);
        $teacher = $user->staff->teacher;

        $allowed = $teacher->subjectAssignments()
            ->where('academic_session_id', $request->academic_session_id)
            ->where('class_id', $request->class_id)
            ->where('section_id', $request->section_id)
            ->active()
            ->exists();

        if (! $allowed) {
            return response()->json(['message' => 'Not assigned to this class/section.'], 403);
        }

        $sessions = AttendanceSession::query()
            ->where('academic_session_id', $request->academic_session_id)
            ->where('class_id', $request->class_id)
            ->where('section_id', $request->section_id)
            ->whereBetween('attendance_date', [$request->date_from, $request->date_to])
            ->orderBy('attendance_date')
            ->get();

        $sessionIds = $sessions->pluck('id');
        $records = AttendanceRecord::query()->whereIn('attendance_session_id', $sessionIds)->with('student')->get();

        $format = $request->input('format', 'csv');
        if ($format === 'csv') {
            $filename = 'attendance-summary-' . Carbon::today()->format('Y-m-d') . '.csv';
            return response()->streamDownload(function () use ($sessions, $records) {
                $out = fopen('php://output', 'w');
                fputcsv($out, ['Date', 'Student ID', 'Student Name', 'Status']);
                foreach ($sessions as $session) {
                    $sessionRecords = $records->where('attendance_session_id', $session->id);
                    foreach ($sessionRecords as $r) {
                        fputcsv($out, [
                            $session->attendance_date->format('Y-m-d'),
                            $r->student_id,
                            $r->student?->full_name ?? '',
                            $r->attendance_status,
                        ]);
                    }
                }
                fclose($out);
            }, $filename, [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ]);
        }

        return response()->json(['message' => 'Unsupported format.'], 422);
    }

    public function marksSheet(Request $request): StreamedResponse|\Illuminate\Http\JsonResponse
    {
        $request->validate([
            'exam_id' => ['required', 'exists:exams,id'],
            'class_id' => ['required', 'exists:school_classes,id'],
            'section_id' => ['required', 'exists:sections,id'],
            'subject_id' => ['nullable', 'exists:subjects,id'],
            'format' => ['nullable', 'in:csv'],
        ]);

        $user = $request->user();
        $user->load(['staff.teacher']);
        $teacher = $user->staff->teacher;

        $exam = Exam::find($request->exam_id);
        if (! $exam || $exam->school_id !== $user->school_id) {
            return response()->json(['message' => 'Exam not found.'], 404);
        }

        $allowed = $teacher->subjectAssignments()
            ->where('academic_session_id', $exam->academic_session_id)
            ->where('class_id', $request->class_id)
            ->where('section_id', $request->section_id)
            ->when($request->filled('subject_id'), fn ($q) => $q->where('subject_id', $request->subject_id))
            ->active()
            ->exists();

        if (! $allowed) {
            return response()->json(['message' => 'Not assigned to this exam context.'], 403);
        }

        $query = MarkEntry::query()
            ->where('exam_id', $request->exam_id)
            ->where('class_id', $request->class_id)
            ->where('section_id', $request->section_id)
            ->when($request->filled('subject_id'), fn ($q) => $q->where('subject_id', $request->subject_id))
            ->with(['student', 'markEntryItems.markComponent']);

        $entries = $query->get();

        $format = $request->input('format', 'csv');
        if ($format === 'csv') {
            $filename = 'marks-sheet-' . Carbon::today()->format('Y-m-d') . '.csv';
            return response()->streamDownload(function () use ($entries) {
                $out = fopen('php://output', 'w');
                fputcsv($out, ['Student ID', 'Roll', 'Student Name', 'Total Marks', 'Status']);
                foreach ($entries as $e) {
                    $total = $e->markEntryItems->sum('obtained_marks');
                    fputcsv($out, [
                        $e->student_id,
                        $e->student?->roll_no ?? '',
                        $e->student?->full_name ?? '',
                        $total,
                        $e->status,
                    ]);
                }
                fclose($out);
            }, $filename, [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ]);
        }

        return response()->json(['message' => 'Unsupported format.'], 422);
    }

    public function homeworkSummary(Request $request): StreamedResponse|\Illuminate\Http\JsonResponse
    {
        $request->validate([
            'academic_session_id' => ['nullable', 'exists:academic_sessions,id'],
            'class_id' => ['nullable', 'exists:school_classes,id'],
            'section_id' => ['nullable', 'exists:sections,id'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
            'format' => ['nullable', 'in:csv'],
        ]);

        $user = $request->user();
        $user->load(['staff.teacher']);
        $teacher = $user->staff->teacher;

        $query = \App\Models\Homework::query()
            ->where('teacher_id', $teacher->id)
            ->with(['schoolClass', 'section', 'subject']);

        if ($request->filled('academic_session_id')) {
            $query->where('academic_session_id', $request->academic_session_id);
        }
        if ($request->filled('class_id')) {
            $query->where('class_id', $request->class_id);
        }
        if ($request->filled('section_id')) {
            $query->where('section_id', $request->section_id);
        }
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $items = $query->orderBy('due_date')->get();

        $format = $request->input('format', 'csv');
        if ($format === 'csv') {
            $filename = 'homework-summary-' . Carbon::today()->format('Y-m-d') . '.csv';
            return response()->streamDownload(function () use ($items) {
                $out = fopen('php://output', 'w');
                fputcsv($out, ['Title', 'Class', 'Section', 'Subject', 'Due Date', 'Status']);
                foreach ($items as $h) {
                    fputcsv($out, [
                        $h->title,
                        $h->schoolClass?->name ?? '',
                        $h->section?->name ?? '',
                        $h->subject?->name ?? '',
                        $h->due_date?->format('Y-m-d') ?? '',
                        $h->status ?? '',
                    ]);
                }
                fclose($out);
            }, $filename, [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ]);
        }

        return response()->json(['message' => 'Unsupported format.'], 422);
    }
}
