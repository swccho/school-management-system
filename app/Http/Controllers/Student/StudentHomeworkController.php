<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Http\Requests\Student\SubmitHomeworkRequest;
use App\Models\AcademicSession;
use App\Models\Homework;
use App\Models\HomeworkSubmission;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class StudentHomeworkController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $user->load(['student']);
        $student = $user->student;
        $assignment = $this->getCurrentAssignment($user);
        if (! $assignment) {
            return response()->json(['data' => []]);
        }

        $query = Homework::query()
            ->where('academic_session_id', $assignment['academic_session_id'])
            ->where('class_id', $assignment['class_id'])
            ->where('section_id', $assignment['section_id'])
            ->with(['subject', 'teacher.staff.user', 'submissions' => fn ($q) => $q->where('student_id', $student->id)]);

        if ($request->filled('status') && $request->status !== 'all') {
            $today = Carbon::today();
            if ($request->status === 'pending') {
                $query->where('due_date', '>=', $today)->where('status', 'active')
                    ->whereDoesntHave('submissions', fn ($q) => $q->where('student_id', $student->id));
            } elseif ($request->status === 'overdue') {
                $query->where('due_date', '<', $today)->where('status', 'active')
                    ->whereDoesntHave('submissions', fn ($q) => $q->where('student_id', $student->id));
            } elseif ($request->status === 'closed') {
                $query->where('status', '!=', 'active');
            } elseif ($request->status === 'submitted' || $request->status === 'late') {
                $query->whereHas('submissions', fn ($q) => $q->where('student_id', $student->id)->where('status', 'submitted'));
            } elseif ($request->status === 'graded') {
                $query->whereHas('submissions', fn ($q) => $q->where('student_id', $student->id)->where('status', 'graded'));
            }
        }

        $items = $query->orderByDesc('due_date')->orderByDesc('created_at')->limit(100)->get();
        $today = Carbon::today();

        $data = $items->map(function ($h) use ($today) {
            $sub = $h->submissions->first();
            $submissionData = $sub ? [
                'submitted_at' => $sub->submitted_at?->toIso8601String(),
                'status' => $sub->status,
            ] : null;
            $status = $this->deriveStatus($h, $today, $submissionData);
            return [
                'id' => $h->id,
                'title' => $h->title,
                'subject_name' => $h->subject?->name,
                'teacher_name' => $h->teacher?->staff?->user?->name ?? '',
                'publish_date' => $h->created_at?->format('Y-m-d'),
                'due_date' => $h->due_date?->format('Y-m-d'),
                'has_attachment' => ! empty($h->attachment_path),
                'status' => $status,
            ];
        })->values()->toArray();

        return response()->json(['data' => $data]);
    }

    public function show(Request $request, Homework $homework): JsonResponse
    {
        $user = $request->user();
        $user->load(['student']);
        $student = $user->student;
        $assignment = $this->getCurrentAssignment($user);
        if (! $assignment) {
            abort(404);
        }
        if ($homework->class_id != $assignment['class_id'] || $homework->section_id != $assignment['section_id']
            || $homework->academic_session_id != $assignment['academic_session_id']) {
            abort(404);
        }

        $homework->load(['subject', 'teacher.staff.user']);
        $submission = HomeworkSubmission::query()
            ->where('homework_id', $homework->id)
            ->where('student_id', $student->id)
            ->first();

        $attachmentUrl = $homework->attachment_path
            ? Storage::disk('public')->url($homework->attachment_path)
            : null;

        return response()->json([
            'id' => $homework->id,
            'title' => $homework->title,
            'description' => $homework->description,
            'subject_name' => $homework->subject?->name,
            'teacher_name' => $homework->teacher?->staff?->user?->name ?? '',
            'publish_date' => $homework->created_at?->format('Y-m-d'),
            'due_date' => $homework->due_date?->format('Y-m-d'),
            'attachment_path' => $homework->attachment_path,
            'attachment_url' => $attachmentUrl,
            'status' => $homework->status,
            'submission' => $submission ? [
                'id' => $submission->id,
                'submitted_at' => $submission->submitted_at?->toIso8601String(),
                'file_path' => $submission->file_path,
                'file_url' => $submission->file_path ? Storage::disk('public')->url($submission->file_path) : null,
                'note' => $submission->note,
                'status' => $submission->status,
                'grade' => $submission->grade,
                'feedback' => $submission->feedback,
                'graded_at' => $submission->graded_at?->toIso8601String(),
            ] : null,
        ]);
    }

    public function submit(SubmitHomeworkRequest $request, Homework $homework): JsonResponse
    {
        $user = $request->user();
        $user->load(['student']);
        $student = $user->student;
        $assignment = $this->getCurrentAssignment($user);
        if (! $assignment) {
            return response()->json(['message' => 'No active assignment.'], 403);
        }
        if ($homework->class_id != $assignment['class_id'] || $homework->section_id != $assignment['section_id']
            || $homework->academic_session_id != $assignment['academic_session_id']) {
            return response()->json(['message' => 'Assignment not found.'], 404);
        }
        if ($homework->status !== 'active') {
            return response()->json(['message' => 'This assignment is no longer accepting submissions.'], 422);
        }
        $today = Carbon::today();
        if ($homework->due_date && $homework->due_date->isBefore($today)) {
            return response()->json(['message' => 'The deadline for this assignment has passed.'], 422);
        }

        $path = null;
        if ($request->hasFile('file')) {
            $path = $request->file('file')->store('homework_submissions', 'public');
        }

        $submission = HomeworkSubmission::query()
            ->where('homework_id', $homework->id)
            ->where('student_id', $student->id)
            ->first();

        if ($submission) {
            if ($submission->file_path) {
                Storage::disk('public')->delete($submission->file_path);
            }
            $submission->update([
                'submitted_at' => now(),
                'file_path' => $path ?? $submission->file_path,
                'note' => $request->input('note', $submission->note),
                'status' => 'submitted',
            ]);
        } else {
            HomeworkSubmission::create([
                'homework_id' => $homework->id,
                'student_id' => $student->id,
                'submitted_at' => now(),
                'file_path' => $path,
                'note' => $request->input('note'),
                'status' => 'submitted',
            ]);
        }

        return response()->json(['message' => 'Submission saved successfully.'], 200);
    }

    public function downloadAttachment(Request $request, Homework $homework): mixed
    {
        $user = $request->user();
        $assignment = $this->getCurrentAssignment($user);
        if (! $assignment || $homework->class_id != $assignment['class_id'] || $homework->section_id != $assignment['section_id']
            || $homework->academic_session_id != $assignment['academic_session_id']) {
            abort(404);
        }
        if (! $homework->attachment_path) {
            abort(404);
        }
        return Storage::disk('public')->download(
            $homework->attachment_path,
            $homework->title . '-' . basename($homework->attachment_path)
        );
    }

    private function getCurrentAssignment($user): ?array
    {
        $currentSession = AcademicSession::query()
            ->where('school_id', $user->school_id)
            ->where('is_current', true)
            ->first();
        if (! $currentSession) {
            return null;
        }
        $student = $user->student;
        $assignment = $student->studentAcademicAssignments()
            ->where('academic_session_id', $currentSession->id)
            ->active()
            ->first();
        if (! $assignment) {
            return null;
        }
        return [
            'class_id' => $assignment->class_id,
            'section_id' => $assignment->section_id,
            'academic_session_id' => $currentSession->id,
        ];
    }

    private function deriveStatus(Homework $homework, Carbon $today, $submission): string
    {
        if ($submission) {
            if (is_array($submission) && isset($submission['status']) && $submission['status'] === 'graded') {
                return 'graded';
            }
            if ($submission instanceof HomeworkSubmission && $submission->status === 'graded') {
                return 'graded';
            }
            $submittedAt = is_array($submission) ? ($submission['submitted_at'] ?? null) : $submission?->submitted_at;
            if ($submittedAt && $homework->due_date) {
                $at = is_string($submittedAt) ? Carbon::parse($submittedAt) : $submittedAt;
                if ($at->isAfter($homework->due_date)) {
                    return 'late';
                }
            }
            return 'submitted';
        }
        if ($homework->status !== 'active') {
            return 'closed';
        }
        if ($homework->due_date && $homework->due_date->isBefore($today)) {
            return 'overdue';
        }
        return 'pending';
    }
}
