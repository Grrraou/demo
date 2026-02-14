<?php

namespace App\Livewire\Chat;

use App\Events\Chat\MessageSent;
use App\Events\Chat\MeetingStarted;
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
    public ?array $meetingNotification = null;

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
        $this->meetingNotification = null;
        $this->loadMessages();
        $this->dispatch('scrollToBottom');
    }

    public function handleNewMessage(): void
    {
        $this->loadMessages();
        $this->dispatch('refreshConversations');
        $this->dispatch('scrollToBottom');
    }

    #[On('refreshMessages')]
    public function refreshMessages(): void
    {
        $this->loadMessages();
        $this->dispatch('scrollToBottom');
    }

    public function getListeners(): array
    {
        $listeners = [];
        
        if ($this->conversationId) {
            $listeners["echo-private:conversation.{$this->conversationId},.message.sent"] = 'handleNewMessage';
            $listeners["echo-private:conversation.{$this->conversationId},.meeting.started"] = 'onMeetingStarted';
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

        $oldCount = count($this->messages);

        $this->messages = $conversation->messages()
            ->with(['sender:id,name'])
            ->whereNull('meeting_id') // Exclude old meeting messages
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(fn ($m) => [
                'id' => $m->id,
                'type' => $m->type,
                'body' => $m->body,
                'sender_id' => $m->team_member_id,
                'sender_name' => $m->sender?->name ?? 'System',
                'created_at' => $m->created_at->format('H:i'),
                'is_mine' => $m->team_member_id === Auth::id(),
                'is_system' => $m->isSystemMessage(),
            ])
            ->toArray();

        // Scroll to bottom if new messages arrived
        if (count($this->messages) > $oldCount) {
            $this->dispatch('scrollToBottom');
        }

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

        // Notify conversation list to refresh and scroll to bottom
        $this->dispatch('refreshConversations');
        $this->dispatch('scrollToBottom');
    }

    public function startMeeting(): void
    {
        if (!$this->conversationId) {
            return;
        }

        $conversation = Conversation::find($this->conversationId);
        
        if (!$conversation || !$conversation->hasParticipant(Auth::id())) {
            return;
        }

        $userName = Auth::user()->name;
        
        // Broadcast meeting started event to other participants (not the starter)
        broadcast(new MeetingStarted($conversation, Auth::user()))->toOthers();

        // Open the meeting in a new tab with user's name
        $this->dispatch('openMeeting', url: $conversation->getJitsiUrl($userName));
    }

    public function joinMeeting(): void
    {
        if (!$this->conversationId) {
            return;
        }

        $conversation = Conversation::find($this->conversationId);
        
        if (!$conversation || !$conversation->hasParticipant(Auth::id())) {
            return;
        }

        $userName = Auth::user()->name;
        $this->dispatch('openMeeting', url: $conversation->getJitsiUrl($userName));
        
        // Clear the notification when joining
        $this->meetingNotification = null;
    }

    public function dismissMeetingNotification(): void
    {
        $this->meetingNotification = null;
    }

    public function onMeetingStarted(array $data): void
    {
        $this->meetingNotification = [
            'starter_name' => $data['starter_name'],
            'started_at' => now()->timestamp,
        ];
    }

    #[On('meetingStartedNotification')]
    public function handleMeetingStartedNotification(array $data): void
    {
        // Called from JavaScript Echo listener
        $this->meetingNotification = [
            'starter_name' => $data['starter_name'],
            'started_at' => now()->timestamp,
        ];
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
