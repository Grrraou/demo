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
    public string $conversationName = '';
    
    // For expanded conversation details
    public ?int $expandedConversationId = null;
    
    // For invite modal
    public bool $showInviteModal = false;
    public ?int $inviteToConversationId = null;
    public string $inviteSearchUsers = '';
    public array $inviteSelectedUsers = [];
    public bool $showInviteWarning = true;
    
    // For leave confirmation
    public bool $showLeaveConfirmation = false;
    public ?int $leaveConversationId = null;
    
    // For archive confirmation
    public bool $showArchiveConfirmation = false;
    public ?int $archiveConversationId = null;
    
    // Show archived toggle
    public bool $showArchived = false;
    
    // Edit conversation name
    public ?int $editingConversationId = null;
    public string $editConversationName = '';

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

    public function toggleExpanded(int $conversationId): void
    {
        if ($this->expandedConversationId === $conversationId) {
            $this->expandedConversationId = null;
        } else {
            $this->expandedConversationId = $conversationId;
        }
    }

    public function openNewChat(): void
    {
        $this->showNewChatModal = true;
        $this->searchUsers = '';
        $this->selectedUsers = [];
        $this->conversationName = '';
    }

    public function closeNewChat(): void
    {
        $this->showNewChatModal = false;
        $this->searchUsers = '';
        $this->selectedUsers = [];
        $this->conversationName = '';
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
        $name = trim($this->conversationName) ?: null;

        if (count($this->selectedUsers) === 1) {
            // Direct conversation
            $conversation = Conversation::findOrCreateDirect($currentUserId, $this->selectedUsers[0], $name);
        } else {
            // Group conversation - current user is the creator
            $conversation = Conversation::createGroup([...$this->selectedUsers, $currentUserId], $name, $currentUserId);
        }

        $this->closeNewChat();
        $this->selectConversation($conversation->id);
    }

    // Invite functionality
    public function openInviteModal(int $conversationId): void
    {
        $this->inviteToConversationId = $conversationId;
        $this->showInviteModal = true;
        $this->inviteSearchUsers = '';
        $this->inviteSelectedUsers = [];
        $this->showInviteWarning = true;
    }

    public function closeInviteModal(): void
    {
        $this->showInviteModal = false;
        $this->inviteToConversationId = null;
        $this->inviteSearchUsers = '';
        $this->inviteSelectedUsers = [];
        $this->showInviteWarning = true;
    }

    public function toggleInviteUser(int $userId): void
    {
        if (in_array($userId, $this->inviteSelectedUsers)) {
            $this->inviteSelectedUsers = array_values(array_diff($this->inviteSelectedUsers, [$userId]));
        } else {
            $this->inviteSelectedUsers[] = $userId;
        }
    }

    public function inviteUsers(): void
    {
        if (empty($this->inviteSelectedUsers) || !$this->inviteToConversationId) {
            return;
        }

        $conversation = Conversation::find($this->inviteToConversationId);
        
        if (!$conversation || !$conversation->hasParticipant(Auth::id())) {
            return;
        }

        $conversation->addParticipants($this->inviteSelectedUsers, Auth::id());
        $this->closeInviteModal();
        
        // Refresh the message panel if this conversation is active
        if ($this->activeConversationId === $this->inviteToConversationId) {
            $this->dispatch('refreshMessages');
        }
    }

    public function createNewConversationInstead(): void
    {
        // Transfer selected users to new conversation modal
        $this->selectedUsers = $this->inviteSelectedUsers;
        $this->closeInviteModal();
        $this->showNewChatModal = true;
    }

    public function getInviteAvailableUsersProperty()
    {
        if (!$this->inviteToConversationId) {
            return collect();
        }

        $conversation = Conversation::find($this->inviteToConversationId);
        if (!$conversation) {
            return collect();
        }

        $existingParticipantIds = $conversation->participants->pluck('id')->toArray();
        
        $query = TeamMember::whereNotIn('id', $existingParticipantIds);
        
        if ($this->inviteSearchUsers) {
            $query->where('name', 'ilike', '%' . $this->inviteSearchUsers . '%');
        }

        return $query->orderBy('name')->limit(20)->get();
    }

    public function getInviteSelectedUsersDetailsProperty()
    {
        if (empty($this->inviteSelectedUsers)) {
            return collect();
        }
        
        return TeamMember::whereIn('id', $this->inviteSelectedUsers)->orderBy('name')->get();
    }

    // Leave functionality
    public function confirmLeave(int $conversationId): void
    {
        $this->leaveConversationId = $conversationId;
        $this->showLeaveConfirmation = true;
    }

    public function cancelLeave(): void
    {
        $this->leaveConversationId = null;
        $this->showLeaveConfirmation = false;
    }

    public function leaveConversation(): void
    {
        if (!$this->leaveConversationId) {
            return;
        }

        $conversation = Conversation::find($this->leaveConversationId);
        
        if (!$conversation) {
            $this->cancelLeave();
            return;
        }

        $wasActive = $this->activeConversationId === $this->leaveConversationId;
        $left = $conversation->removeParticipant(Auth::id());
        
        if ($left && $wasActive) {
            $this->activeConversationId = null;
            $this->dispatch('conversationSelected', conversationId: null);
        }

        $this->cancelLeave();
        $this->expandedConversationId = null;
    }

    // Archive functionality
    public function confirmArchive(int $conversationId): void
    {
        $this->archiveConversationId = $conversationId;
        $this->showArchiveConfirmation = true;
    }

    public function cancelArchive(): void
    {
        $this->archiveConversationId = null;
        $this->showArchiveConfirmation = false;
    }

    public function archiveConversation(): void
    {
        if (!$this->archiveConversationId) {
            return;
        }

        $conversation = Conversation::find($this->archiveConversationId);
        
        if (!$conversation || !$conversation->canArchive(Auth::id())) {
            $this->cancelArchive();
            return;
        }

        $conversation->archive();
        
        if ($this->activeConversationId === $this->archiveConversationId) {
            $this->activeConversationId = null;
            $this->dispatch('conversationSelected', conversationId: null);
        }

        $this->cancelArchive();
        $this->expandedConversationId = null;
    }

    public function unarchiveConversation(int $conversationId): void
    {
        $conversation = Conversation::find($conversationId);
        
        if (!$conversation || !$conversation->canArchive(Auth::id())) {
            return;
        }

        $conversation->unarchive();
    }

    public function toggleShowArchived(): void
    {
        $this->showArchived = !$this->showArchived;
    }

    // Edit conversation name
    public function startEditingName(int $conversationId): void
    {
        $conversation = Conversation::find($conversationId);
        
        if (!$conversation || !$conversation->isCreator(Auth::id())) {
            return;
        }

        $this->editingConversationId = $conversationId;
        $this->editConversationName = $conversation->name ?? '';
    }

    public function cancelEditingName(): void
    {
        $this->editingConversationId = null;
        $this->editConversationName = '';
    }

    public function saveConversationName(): void
    {
        if (!$this->editingConversationId) {
            return;
        }

        $conversation = Conversation::find($this->editingConversationId);
        
        if (!$conversation || !$conversation->isCreator(Auth::id())) {
            $this->cancelEditingName();
            return;
        }

        $name = trim($this->editConversationName) ?: null;
        
        // Only log if name actually changed
        if ($name !== $conversation->name) {
            $conversation->rename($name, Auth::id());
            
            // Refresh the message panel if this conversation is active
            if ($this->activeConversationId === $this->editingConversationId) {
                $this->dispatch('refreshMessages');
            }
        }
        
        $this->cancelEditingName();
    }

    public function getConversationsProperty()
    {
        return Auth::user()
            ->conversations()
            ->whereNull('archived_at') // Exclude archived conversations
            ->with(['participants:id,name', 'latestMessage.sender:id,name'])
            ->latest('updated_at')
            ->get();
    }

    public function getArchivedConversationsProperty()
    {
        return Auth::user()
            ->conversations()
            ->whereNotNull('archived_at')
            ->where('created_by', Auth::id()) // Only show archived conversations created by current user
            ->with(['participants:id,name', 'latestMessage.sender:id,name'])
            ->latest('archived_at')
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

    public function getSelectedUsersDetailsProperty()
    {
        if (empty($this->selectedUsers)) {
            return collect();
        }
        
        return TeamMember::whereIn('id', $this->selectedUsers)->orderBy('name')->get();
    }

    public function render()
    {
        return view('livewire.chat.conversation-list');
    }
}
