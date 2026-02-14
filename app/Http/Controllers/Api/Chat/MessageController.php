<?php

namespace App\Http\Controllers\Api\Chat;

use App\Http\Controllers\Controller;
use App\Jobs\Chat\SendMessageJob;
use App\Models\Chat\Conversation;
use App\Models\Chat\Message;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MessageController extends Controller
{
    public function index(Conversation $conversation, Request $request): JsonResponse
    {
        $this->authorizeParticipant($conversation);

        $validated = $request->validate([
            'before' => ['nullable', 'integer'],
            'limit' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $query = $conversation->messages()
            ->with('sender:id,name')
            ->orderBy('created_at', 'desc');

        if (isset($validated['before'])) {
            $query->where('id', '<', $validated['before']);
        }

        $messages = $query
            ->limit($validated['limit'] ?? 50)
            ->get()
            ->reverse()
            ->values();

        // Mark conversation as read
        $conversation->markAsRead(Auth::id());

        return response()->json([
            'data' => $messages->map(fn ($m) => $this->formatMessage($m)),
        ]);
    }

    public function store(Conversation $conversation, Request $request): JsonResponse
    {
        $this->authorizeParticipant($conversation);

        $validated = $request->validate([
            'body' => ['required', 'string', 'max:10000'],
        ]);

        // Dispatch job to handle message creation, broadcasting, and notifications
        SendMessageJob::dispatch(
            $conversation->id,
            Auth::id(),
            $validated['body']
        );

        // Touch conversation to update timestamps for sorting
        $conversation->touch();

        return response()->json([
            'success' => true,
            'message' => 'Message queued for delivery',
        ], 202);
    }

    public function storeDirect(Conversation $conversation, Request $request): JsonResponse
    {
        $this->authorizeParticipant($conversation);

        $validated = $request->validate([
            'body' => ['required', 'string', 'max:10000'],
        ]);

        // Synchronous message creation (for when you need immediate response)
        $message = Message::create([
            'conversation_id' => $conversation->id,
            'team_member_id' => Auth::id(),
            'body' => $validated['body'],
        ]);

        $message->load('sender:id,name');

        // Dispatch job for broadcasting and notifications only
        SendMessageJob::dispatch(
            $conversation->id,
            Auth::id(),
            $validated['body']
        )->afterResponse();

        $conversation->touch();

        return response()->json([
            'data' => $this->formatMessage($message),
        ], 201);
    }

    private function authorizeParticipant(Conversation $conversation): void
    {
        if (!$conversation->hasParticipant(Auth::id())) {
            abort(403, 'You are not a participant of this conversation');
        }
    }

    private function formatMessage(Message $message): array
    {
        return [
            'id' => $message->id,
            'conversation_id' => $message->conversation_id,
            'body' => $message->body,
            'created_at' => $message->created_at->toISOString(),
            'sender' => [
                'id' => $message->sender->id,
                'name' => $message->sender->name,
            ],
        ];
    }
}
