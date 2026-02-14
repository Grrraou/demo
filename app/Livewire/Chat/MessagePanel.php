<?php

namespace App\Livewire\Chat;

use App\Events\Chat\MessageSent;
use App\Models\Chat\Conversation;
use App\Models\Chat\Message;
use App\Notifications\Chat\NewMessageNotification;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;
use Livewire\Component;

class MessagePanel extends Component
{
    public ?int $conversationId = null;
    public string $newMessage = '';
    public array $messages = [];

    public function mount(?int $conversationId = null): void
    {
        if ($conversationId) {
            $this->loadConversation($conversationId);
        }
    }

    #[On('conversationSelected')]
    public function loadConversation(int $conversationId): void
    {
        $this->conversationId = $conversationId;
        $this->loadMessages();
    }

    public function handleNewMessage(): void
    {
        $this->loadMessages();
        $this->dispatch('refreshConversations');
    }

    public function getListeners(): array
    {
        $listeners = [];
        
        if ($this->conversationId) {
            $listeners["echo-private:conversation.{$this->conversationId},.message.sent"] = 'handleNewMessage';
        }

        return $listeners;
    }

    public function loadMessages(): void
    {
        if (!$this->conversationId) {
            $this->messages = [];
            return;
        }

        $conversation = Conversation::find($this->conversationId);
        
        if (!$conversation || !$conversation->hasParticipant(Auth::id())) {
            $this->messages = [];
            return;
        }

        $this->messages = $conversation->messages()
            ->with('sender:id,name')
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(fn ($m) => [
                'id' => $m->id,
                'body' => $m->body,
                'sender_id' => $m->team_member_id,
                'sender_name' => $m->sender->name,
                'created_at' => $m->created_at->format('H:i'),
                'is_mine' => $m->team_member_id === Auth::id(),
            ])
            ->toArray();

        // Mark as read
        $conversation->markAsRead(Auth::id());
    }

    public function sendMessage(): void
    {
        if (!$this->conversationId || trim($this->newMessage) === '') {
            return;
        }

        $conversation = Conversation::find($this->conversationId);
        
        if (!$conversation || !$conversation->hasParticipant(Auth::id())) {
            return;
        }

        // Create message
        $message = Message::create([
            'conversation_id' => $this->conversationId,
            'team_member_id' => Auth::id(),
            'body' => trim($this->newMessage),
        ]);

        $message->load('sender:id,name');

        // Broadcast to other participants
        broadcast(new MessageSent($message))->toOthers();

        // Send notifications to other participants
        $conversation->otherParticipants(Auth::id())
            ->get()
            ->each(fn ($p) => $p->notify(new NewMessageNotification($message)));

        // Update conversation timestamp
        $conversation->touch();

        // Clear input and reload messages
        $this->newMessage = '';
        $this->loadMessages();

        // Notify conversation list to refresh
        $this->dispatch('refreshConversations');
    }

    public function getConversationProperty()
    {
        if (!$this->conversationId) {
            return null;
        }

        return Conversation::with('participants:id,name')->find($this->conversationId);
    }

    public function render()
    {
        return view('livewire.chat.message-panel');
    }
}
