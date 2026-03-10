<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\AcademicSession;
use App\Models\Conversation;
use App\Models\ConversationParticipant;
use App\Models\Message;
use App\Models\MessageAttachment;
use App\Models\TeacherSubjectAssignment;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class StudentMessageController extends Controller
{
    public function recipients(Request $request): JsonResponse
    {
        $user = $request->user();
        $user->load(['student']);
        $student = $user->student;
        if (! $student) {
            return response()->json([]);
        }

        $assignment = $this->getCurrentAssignment($user);
        if (! $assignment) {
            return response()->json([]);
        }

        $teacherIds = TeacherSubjectAssignment::query()
            ->where('academic_session_id', $assignment['academic_session_id'])
            ->where('class_id', $assignment['class_id'])
            ->where('section_id', $assignment['section_id'])
            ->active()
            ->pluck('teacher_id')
            ->unique()
            ->values();

        if ($teacherIds->isEmpty()) {
            return response()->json([]);
        }

        $recipients = User::query()
            ->where('school_id', $user->school_id)
            ->where('id', '!=', $user->id)
            ->where('status', 'active')
            ->whereHas('staff.teacher', fn ($q) => $q->whereIn('teachers.id', $teacherIds)->active())
            ->orderBy('name')
            ->get(['id', 'name', 'email'])
            ->map(fn ($u) => [
                'id' => $u->id,
                'name' => $u->name,
                'email' => $u->email,
            ]);

        return response()->json($recipients);
    }

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $schoolId = $user->school_id;

        $participantIds = ConversationParticipant::query()
            ->where('user_id', $user->id)
            ->pluck('conversation_id');

        $query = Conversation::query()
            ->where('school_id', $schoolId)
            ->whereIn('id', $participantIds)
            ->with(['participants.user:id,name,email', 'messages' => fn ($q) => $q->latest()->limit(1)]);

        $conversations = $query->orderByRaw(
            '(SELECT MAX(created_at) FROM messages WHERE messages.conversation_id = conversations.id) DESC'
        )->paginate(20);

        $items = $conversations->getCollection()->map(function (Conversation $c) use ($user) {
            $lastMessage = $c->messages->first();
            $otherParticipant = $c->participants->first(fn ($p) => $p->user_id !== $user->id);
            $participant = $c->participants->first(fn ($p) => $p->user_id === $user->id);
            $lastReadAt = $participant?->last_read_at;
            $unreadCount = $lastReadAt
                ? $c->messages()->where('created_at', '>', $lastReadAt)->where('sender_id', '!=', $user->id)->count()
                : $c->messages()->where('sender_id', '!=', $user->id)->count();

            return [
                'id' => $c->id,
                'subject' => $c->subject,
                'other_participant_name' => $otherParticipant?->user?->name ?? 'Unknown',
                'other_participant_id' => $otherParticipant?->user_id,
                'last_message_at' => $lastMessage?->created_at?->toIso8601String(),
                'last_message_preview' => $lastMessage ? Str::limit(strip_tags($lastMessage->body), 80) : null,
                'unread_count' => $unreadCount,
            ];
        });

        return response()->json([
            'data' => $items,
            'meta' => [
                'current_page' => $conversations->currentPage(),
                'last_page' => $conversations->lastPage(),
                'per_page' => $conversations->perPage(),
                'total' => $conversations->total(),
            ],
        ]);
    }

    public function show(Request $request, Conversation $conversation): JsonResponse
    {
        $user = $request->user();
        $participant = ConversationParticipant::query()
            ->where('conversation_id', $conversation->id)
            ->where('user_id', $user->id)
            ->first();

        if (! $participant || $conversation->school_id !== $user->school_id) {
            abort(403);
        }

        $other = $conversation->participants()->where('user_id', '!=', $user->id)->with('user:id,name,email')->first();

        return response()->json([
            'id' => $conversation->id,
            'subject' => $conversation->subject,
            'other_participant' => $other?->user ? ['id' => $other->user->id, 'name' => $other->user->name, 'email' => $other->user->email] : null,
        ]);
    }

    public function messages(Request $request, Conversation $conversation): JsonResponse
    {
        $user = $request->user();
        $participant = ConversationParticipant::query()
            ->where('conversation_id', $conversation->id)
            ->where('user_id', $user->id)
            ->first();

        if (! $participant || $conversation->school_id !== $user->school_id) {
            abort(403);
        }

        $messages = $conversation->messages()
            ->with(['sender:id,name', 'attachments'])
            ->orderBy('created_at')
            ->paginate(20);

        $items = $messages->getCollection()->map(function (Message $m) use ($conversation) {
            $attachments = $m->attachments->map(fn ($a) => [
                'id' => $a->id,
                'file_name' => $a->file_name,
                'download_url' => "/api/student/messages/conversations/{$conversation->id}/messages/{$m->id}/attachments/{$a->id}/download",
            ]);
            return [
                'id' => $m->id,
                'sender_id' => $m->sender_id,
                'sender_name' => $m->sender?->name,
                'body' => $m->body,
                'created_at' => $m->created_at->toIso8601String(),
                'attachments' => $attachments,
            ];
        });

        return response()->json([
            'data' => $items,
            'meta' => [
                'current_page' => $messages->currentPage(),
                'last_page' => $messages->lastPage(),
                'per_page' => $messages->perPage(),
                'total' => $messages->total(),
            ],
        ]);
    }

    public function markRead(Request $request, Conversation $conversation): JsonResponse
    {
        $user = $request->user();
        $participant = ConversationParticipant::query()
            ->where('conversation_id', $conversation->id)
            ->where('user_id', $user->id)
            ->first();

        if (! $participant || $conversation->school_id !== $user->school_id) {
            abort(403);
        }

        $participant->update(['last_read_at' => now()]);

        return response()->json(['message' => 'Marked as read']);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'recipient_id' => ['required', 'exists:users,id'],
            'subject' => ['nullable', 'string', 'max:255'],
            'body' => ['required', 'string', 'max:5000'],
        ]);

        $user = $request->user();
        $user->load(['student']);
        $schoolId = $user->school_id;
        $recipientId = (int) $request->recipient_id;

        $allowedUserIds = $this->getAllowedTeacherUserIds($user);
        if (! in_array($recipientId, $allowedUserIds)) {
            return response()->json(['message' => 'Recipient not allowed.'], 403);
        }

        $recipient = User::query()->where('id', $recipientId)->where('school_id', $schoolId)->where('status', 'active')->first();
        if (! $recipient) {
            return response()->json(['message' => 'Recipient not found.'], 404);
        }

        $existing = Conversation::query()
            ->where('school_id', $schoolId)
            ->where('type', 'student_teacher')
            ->whereHas('participants', fn ($q) => $q->where('user_id', $user->id))
            ->whereHas('participants', fn ($q) => $q->where('user_id', $recipientId))
            ->first();

        if ($existing) {
            return response()->json(['message' => 'Conversation already exists.', 'conversation_id' => $existing->id], 422);
        }

        $conversation = DB::transaction(function () use ($request, $user, $schoolId, $recipientId) {
            $conversation = Conversation::create([
                'school_id' => $schoolId,
                'subject' => $request->subject ?? 'Message',
                'type' => 'student_teacher',
            ]);
            ConversationParticipant::insert([
                ['conversation_id' => $conversation->id, 'user_id' => $user->id, 'role' => 'student', 'created_at' => now(), 'updated_at' => now()],
                ['conversation_id' => $conversation->id, 'user_id' => $recipientId, 'role' => 'teacher', 'created_at' => now(), 'updated_at' => now()],
            ]);
            $conversation->messages()->create([
                'sender_id' => $user->id,
                'body' => $request->body,
            ]);
            return $conversation->load(['messages' => fn ($q) => $q->with(['sender:id,name'])->latest()->limit(1)]);
        });

        $lastMessage = $conversation->messages->first();
        return response()->json([
            'message' => 'Message sent.',
            'conversation' => [
                'id' => $conversation->id,
                'subject' => $conversation->subject,
                'last_message' => $lastMessage ? [
                    'id' => $lastMessage->id,
                    'sender_name' => $lastMessage->sender?->name,
                    'body' => $lastMessage->body,
                    'created_at' => $lastMessage->created_at->toIso8601String(),
                ] : null,
            ],
        ], 201);
    }

    public function reply(Request $request, Conversation $conversation): JsonResponse
    {
        $request->validate([
            'body' => ['required', 'string', 'max:5000'],
        ]);

        $user = $request->user();
        $participant = ConversationParticipant::query()
            ->where('conversation_id', $conversation->id)
            ->where('user_id', $user->id)
            ->first();

        if (! $participant || $conversation->school_id !== $user->school_id) {
            abort(403);
        }

        $message = $conversation->messages()->create([
            'sender_id' => $user->id,
            'body' => $request->body,
        ]);

        $message->load(['sender:id,name']);
        return response()->json([
            'message' => 'Reply sent.',
            'data' => [
                'id' => $message->id,
                'sender_id' => $message->sender_id,
                'sender_name' => $message->sender?->name,
                'body' => $message->body,
                'created_at' => $message->created_at->toIso8601String(),
            ],
        ], 201);
    }

    public function downloadAttachment(Request $request, Conversation $conversation, Message $message, MessageAttachment $attachment): \Symfony\Component\HttpFoundation\StreamedResponse|JsonResponse
    {
        $user = $request->user();
        $participant = ConversationParticipant::query()
            ->where('conversation_id', $conversation->id)
            ->where('user_id', $user->id)
            ->first();

        if (! $participant || $conversation->school_id !== $user->school_id) {
            abort(403);
        }
        if ($attachment->message_id !== $message->id || $message->conversation_id !== $conversation->id) {
            abort(404);
        }

        if (! Storage::disk('public')->exists($attachment->file_path)) {
            abort(404);
        }

        return Storage::disk('public')->download(
            $attachment->file_path,
            $attachment->file_name,
            ['Content-Type' => $attachment->mime_type]
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

    private function getAllowedTeacherUserIds($user): array
    {
        $assignment = $this->getCurrentAssignment($user);
        if (! $assignment) {
            return [];
        }
        $teacherIds = TeacherSubjectAssignment::query()
            ->where('academic_session_id', $assignment['academic_session_id'])
            ->where('class_id', $assignment['class_id'])
            ->where('section_id', $assignment['section_id'])
            ->active()
            ->pluck('teacher_id')
            ->unique()
            ->values()
            ->all();
        if (empty($teacherIds)) {
            return [];
        }
        return User::query()
            ->where('school_id', $user->school_id)
            ->whereHas('staff.teacher', fn ($q) => $q->whereIn('teachers.id', $teacherIds)->active())
            ->pluck('id')
            ->all();
    }
}
