<?php

namespace App\Livewire\Chat;

use App\Models\Chat\Conversation;
use App\Models\TeamMember;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;
use Livewire\Component;

class ConversationList extends Component
{
    public ?int $activeConversationId = null;
    public bool $showNewChatModal = false;
    public string $searchUsers = '';
    public array $selectedUsers = [];

    #[On('conversationSelected')]
    public function setActive(?int $conversationId): void
    {
        $this->activeConversationId = $conversationId;
    }

    #[On('refreshConversations')]
    public function refresh(): void
    {
        // This triggers a re-render
    }

    public function selectConversation(int $id): void
    {
        $this->activeConversationId = $id;
        $this->dispatch('conversationSelected', conversationId: $id);
    }

    public function openNewChat(): void
    {
        $this->showNewChatModal = true;
        $this->searchUsers = '';
        $this->selectedUsers = [];
    }

    public function closeNewChat(): void
    {
        $this->showNewChatModal = false;
        $this->searchUsers = '';
        $this->selectedUsers = [];
    }

    public function toggleUser(int $userId): void
    {
        if (in_array($userId, $this->selectedUsers)) {
            $this->selectedUsers = array_values(array_diff($this->selectedUsers, [$userId]));
        } else {
            $this->selectedUsers[] = $userId;
        }
    }

    public function startConversation(): void
    {
        if (empty($this->selectedUsers)) {
            return;
        }

        $currentUserId = Auth::id();

        if (count($this->selectedUsers) === 1) {
            // Direct conversation
            $conversation = Conversation::findOrCreateDirect($currentUserId, $this->selectedUsers[0]);
        } else {
            // Group conversation
            $conversation = Conversation::createGroup([...$this->selectedUsers, $currentUserId]);
        }

        $this->closeNewChat();
        $this->selectConversation($conversation->id);
    }

    public function getConversationsProperty()
    {
        return Auth::user()
            ->conversations()
            ->with(['participants:id,name', 'latestMessage.sender:id,name'])
            ->latest('updated_at')
            ->get();
    }

    public function getAvailableUsersProperty()
    {
        $query = TeamMember::where('id', '!=', Auth::id());
        
        if ($this->searchUsers) {
            $query->where('name', 'ilike', '%' . $this->searchUsers . '%');
        }

        return $query->orderBy('name')->limit(20)->get();
    }

    public function render()
    {
        return view('livewire.chat.conversation-list');
    }
}
