<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\ConversationParticipant;
use App\Models\Message;
use App\Models\MessageAttachment;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class MessageController extends Controller
{
    public function recipients(Request $request): JsonResponse
    {
        $user = $request->user();
        $schoolId = $user->school_id;
        if (! $schoolId) {
            return response()->json([]);
        }

        $roleSlugs = config('teacher.messageable_roles', ['school-admin', 'super-admin']);
        $recipients = User::query()
            ->where('school_id', $schoolId)
            ->where('id', '!=', $user->id)
            ->where('status', 'active')
            ->whereHas('roles', fn ($q) => $q->whereIn('slug', $roleSlugs))
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

        $folder = $request->input('folder', 'inbox');
        if ($folder === 'sent') {
            $query->whereHas('messages', fn ($q) => $q->where('sender_id', $user->id));
        }

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
                'last_message_preview' => $lastMessage ? \Illuminate\Support\Str::limit(strip_tags($lastMessage->body), 80) : null,
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
                'download_url' => "conversations/{$conversation->id}/messages/{$m->id}/attachments/{$a->id}/download",
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
            'attachments.*' => ['nullable', 'file', 'max:5120', 'mimes:pdf,jpg,jpeg,png,gif,doc,docx'],
        ]);

        $user = $request->user();
        $schoolId = $user->school_id;
        $recipientId = (int) $request->recipient_id;

        $roleSlugs = config('teacher.messageable_roles', ['school-admin', 'super-admin']);
        $recipient = User::query()
            ->where('id', $recipientId)
            ->where('school_id', $schoolId)
            ->where('status', 'active')
            ->whereHas('roles', fn ($q) => $q->whereIn('slug', $roleSlugs))
            ->first();

        if (! $recipient) {
            return response()->json(['message' => 'Recipient not allowed.'], 403);
        }

        $conversation = DB::transaction(function () use ($request, $user, $schoolId, $recipientId) {
            $conversation = Conversation::create([
                'school_id' => $schoolId,
                'subject' => $request->subject,
                'type' => 'direct',
            ]);
            ConversationParticipant::insert([
                ['conversation_id' => $conversation->id, 'user_id' => $user->id, 'role' => 'teacher', 'created_at' => now(), 'updated_at' => now()],
                ['conversation_id' => $conversation->id, 'user_id' => $recipientId, 'role' => 'admin', 'created_at' => now(), 'updated_at' => now()],
            ]);
            $message = $conversation->messages()->create([
                'sender_id' => $user->id,
                'body' => $request->body,
            ]);
            if ($request->hasFile('attachments')) {
                foreach ($request->file('attachments') as $file) {
                    $path = $file->store('message-attachments', 'public');
                    $message->attachments()->create([
                        'file_path' => $path,
                        'file_name' => $file->getClientOriginalName(),
                        'mime_type' => $file->getMimeType(),
                        'size' => $file->getSize(),
                    ]);
                }
            }
            return $conversation->load(['messages' => fn ($q) => $q->with(['sender:id,name', 'attachments'])->latest()->limit(1)]);
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
                    'attachments' => $lastMessage->attachments->map(fn ($a) => ['id' => $a->id, 'file_name' => $a->file_name]),
                ] : null,
            ],
        ], 201);
    }

    public function reply(Request $request, Conversation $conversation): JsonResponse
    {
        $request->validate([
            'body' => ['required', 'string', 'max:5000'],
            'attachments.*' => ['nullable', 'file', 'max:5120', 'mimes:pdf,jpg,jpeg,png,gif,doc,docx'],
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

        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $path = $file->store('message-attachments', 'public');
                $message->attachments()->create([
                    'file_path' => $path,
                    'file_name' => $file->getClientOriginalName(),
                    'mime_type' => $file->getMimeType(),
                    'size' => $file->getSize(),
                ]);
            }
        }

        $message->load(['sender:id,name', 'attachments']);
        return response()->json([
            'message' => 'Reply sent.',
            'data' => [
                'id' => $message->id,
                'sender_id' => $message->sender_id,
                'sender_name' => $message->sender?->name,
                'body' => $message->body,
                'created_at' => $message->created_at->toIso8601String(),
                'attachments' => $message->attachments->map(fn ($a) => ['id' => $a->id, 'file_name' => $a->file_name]),
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
}
