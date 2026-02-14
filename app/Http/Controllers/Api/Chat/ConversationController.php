<?php

namespace App\Http\Controllers\Api\Chat;

use App\Http\Controllers\Controller;
use App\Models\Chat\Conversation;
use App\Models\TeamMember;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class ConversationController extends Controller
{
    public function index(): JsonResponse
    {
        $conversations = Auth::user()
            ->conversations()
            ->with(['participants:id,name', 'latestMessage.sender:id,name'])
            ->withCount(['messages as unread_count' => function ($query) {
                $pivot = Auth::user()->conversations()
                    ->where('conversations.id', $query->getModel()->conversation_id ?? 0)
                    ->first()
                    ?->pivot;
                
                $query->where('team_member_id', '!=', Auth::id());
                
                if ($pivot?->last_read_at) {
                    $query->where('created_at', '>', $pivot->last_read_at);
                }
            }])
            ->latest('updated_at')
            ->get();

        return response()->json([
            'data' => $conversations->map(fn ($c) => $this->formatConversation($c)),
        ]);
    }

    public function show(Conversation $conversation): JsonResponse
    {
        $this->authorizeParticipant($conversation);

        $conversation->load(['participants:id,name', 'latestMessage.sender:id,name']);
        
        // Mark as read
        $conversation->markAsRead(Auth::id());

        return response()->json([
            'data' => $this->formatConversation($conversation),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'type' => ['required', Rule::in(['direct', 'group', 'entity'])],
            'user_id' => ['required_if:type,direct', 'integer', 'exists:team_members,id'],
            'user_ids' => ['required_if:type,group', 'array', 'min:2'],
            'user_ids.*' => ['integer', 'exists:team_members,id'],
            'entity_type' => ['required_if:type,entity', 'string', 'max:50'],
            'entity_id' => ['required_if:type,entity', 'integer'],
        ]);

        $conversation = match ($validated['type']) {
            'direct' => $this->createDirectConversation($validated['user_id']),
            'group' => $this->createGroupConversation($validated['user_ids']),
            'entity' => $this->createEntityConversation(
                $validated['entity_type'],
                $validated['entity_id'],
                $validated['user_ids'] ?? []
            ),
        };

        $conversation->load(['participants:id,name']);

        return response()->json([
            'data' => $this->formatConversation($conversation),
        ], 201);
    }

    public function markAsRead(Conversation $conversation): JsonResponse
    {
        $this->authorizeParticipant($conversation);

        $conversation->markAsRead(Auth::id());

        return response()->json(['success' => true]);
    }

    private function createDirectConversation(int $userId): Conversation
    {
        if ($userId === Auth::id()) {
            abort(422, 'Cannot create conversation with yourself');
        }

        return Conversation::findOrCreateDirect(Auth::id(), $userId);
    }

    private function createGroupConversation(array $userIds): Conversation
    {
        // Ensure current user is included
        $userIds = array_unique([...array_map('intval', $userIds), Auth::id()]);

        if (count($userIds) < 2) {
            abort(422, 'Group conversation requires at least 2 participants');
        }

        return Conversation::createGroup($userIds);
    }

    private function createEntityConversation(string $entityType, int $entityId, array $userIds): Conversation
    {
        $userIds = array_unique([...array_map('intval', $userIds), Auth::id()]);

        return Conversation::findOrCreateForEntity($entityType, $entityId, $userIds);
    }

    private function authorizeParticipant(Conversation $conversation): void
    {
        if (!$conversation->hasParticipant(Auth::id())) {
            abort(403, 'You are not a participant of this conversation');
        }
    }

    private function formatConversation(Conversation $conversation): array
    {
        return [
            'id' => $conversation->id,
            'type' => $conversation->type,
            'entity_type' => $conversation->entity_type,
            'entity_id' => $conversation->entity_id,
            'participants' => $conversation->participants->map(fn ($p) => [
                'id' => $p->id,
                'name' => $p->name,
            ]),
            'latest_message' => $conversation->latestMessage->first() ? [
                'id' => $conversation->latestMessage->first()->id,
                'body' => $conversation->latestMessage->first()->body,
                'created_at' => $conversation->latestMessage->first()->created_at->toISOString(),
                'sender' => [
                    'id' => $conversation->latestMessage->first()->sender->id,
                    'name' => $conversation->latestMessage->first()->sender->name,
                ],
            ] : null,
            'unread_count' => $conversation->unreadCountFor(Auth::id()),
            'created_at' => $conversation->created_at->toISOString(),
            'updated_at' => $conversation->updated_at->toISOString(),
        ];
    }
}
