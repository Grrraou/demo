<div class="flex flex-col h-full" wire:poll.10s>
    {{-- Header --}}
    <div class="p-4 border-b border-gray-200">
        <div class="flex items-center justify-between">
            <h2 class="text-lg font-semibold text-gray-900">Conversations</h2>
            <div class="flex items-center gap-1">
                <button 
                    wire:click="toggleSearch"
                    class="p-2 text-gray-500 hover:text-gray-700 hover:bg-gray-100 rounded-full transition {{ $showSearch ? 'bg-gray-100 text-gray-700' : '' }}"
                    title="Search"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </button>
                <button 
                    wire:click="openNewChat"
                    class="p-2 text-gray-500 hover:text-gray-700 hover:bg-gray-100 rounded-full transition"
                    title="New conversation"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                </button>
            </div>
        </div>

        {{-- Search Input --}}
        @if($showSearch)
            <div class="mt-3 relative">
                <div class="relative">
                    <input 
                        type="text"
                        wire:model.live.debounce.300ms="searchQuery"
                        placeholder="Search conversations or messages..."
                        class="w-full pl-9 pr-8 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        autofocus
                    >
                    <svg class="absolute left-3 top-2.5 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    @if($searchQuery)
                        <button 
                            wire:click="clearSearch"
                            class="absolute right-2 top-2 p-0.5 text-gray-400 hover:text-gray-600 rounded"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    @endif
                </div>

                {{-- Search Results --}}
                @if(count($searchResults) > 0)
                    <div class="absolute left-0 right-0 mt-1 bg-white border border-gray-200 rounded-lg shadow-lg z-20 max-h-80 overflow-y-auto">
                        @foreach($searchResults as $result)
                            <button 
                                wire:click="goToSearchResult('{{ $result['type'] }}', {{ $result['conversation_id'] }}, {{ $result['id'] }})"
                                class="w-full px-3 py-2 text-left hover:bg-gray-50 border-b border-gray-100 last:border-b-0"
                            >
                                <div class="flex items-start gap-2">
                                    @if($result['type'] === 'conversation')
                                        <div class="flex-shrink-0 mt-0.5">
                                            <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8h2a2 2 0 012 2v6a2 2 0 01-2 2h-2v4l-4-4H9a1.994 1.994 0 01-1.414-.586m0 0L11 14h4a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2v4l.586-.586z"/>
                                            </svg>
                                        </div>
                                    @else
                                        <div class="flex-shrink-0 mt-0.5">
                                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
                                            </svg>
                                        </div>
                                    @endif
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm text-gray-900 truncate">{{ $result['title'] }}</p>
                                        <p class="text-xs text-gray-500 truncate">
                                            {{ $result['subtitle'] }}
                                            @if(isset($result['created_at']))
                                                <span class="text-gray-400">· {{ $result['created_at'] }}</span>
                                            @endif
                                        </p>
                                    </div>
                                </div>
                            </button>
                        @endforeach
                    </div>
                @elseif($searchQuery && strlen($searchQuery) >= 2)
                    <div class="absolute left-0 right-0 mt-1 bg-white border border-gray-200 rounded-lg shadow-lg z-20 p-4">
                        <p class="text-sm text-gray-500 text-center">No results found</p>
                    </div>
                @endif
            </div>
        @endif
    </div>

    {{-- Conversation List --}}
    <div class="flex-1 overflow-y-auto">
        @forelse($this->conversations as $conversation)
            @php
                $displayName = $conversation->getDisplayName(auth()->id());
                $unreadCount = $conversation->unreadCountFor(auth()->id());
                $isExpanded = $expandedConversationId === $conversation->id;
                $participantCount = $conversation->participants->count();
            @endphp
            <div class="border-b border-gray-100 {{ $activeConversationId === $conversation->id ? 'bg-blue-50 border-l-4 border-l-blue-500' : '' }}">
                {{-- Main conversation row --}}
                <div class="flex items-start gap-2 p-4">
                    {{-- Expand button --}}
                    <button 
                        wire:click="toggleExpanded({{ $conversation->id }})"
                        class="mt-1 p-0.5 text-gray-400 hover:text-gray-600 rounded transition flex-shrink-0"
                        title="Show participants"
                    >
                        <svg class="w-4 h-4 transition-transform {{ $isExpanded ? 'rotate-90' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </button>
                    
                    {{-- Conversation info (clickable) --}}
                    <button
                        wire:click="selectConversation({{ $conversation->id }})"
                        class="flex-1 min-w-0 text-left hover:bg-gray-50/50 rounded transition"
                    >
                        <div class="flex items-start justify-between gap-2">
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2">
                                    <span class="font-medium text-gray-900 truncate">{{ $displayName }}</span>
                                    @if($conversation->type === 'group')
                                        <span class="text-xs text-gray-500 bg-gray-100 px-1.5 py-0.5 rounded">{{ $participantCount }}</span>
                                    @elseif($conversation->type === 'entity')
                                        <span class="text-xs text-purple-600 bg-purple-50 px-1.5 py-0.5 rounded">{{ ucfirst($conversation->entity_type) }}</span>
                                    @endif
                                </div>
                                @if($conversation->latestMessage->first())
                                    <p class="text-sm text-gray-500 truncate mt-1">
                                        @if($conversation->latestMessage->first()->team_member_id === auth()->id())
                                            <span class="text-gray-400">You:</span>
                                        @endif
                                        {{ $conversation->latestMessage->first()->body }}
                                    </p>
                                @else
                                    <p class="text-sm text-gray-400 italic mt-1">No messages yet</p>
                                @endif
                            </div>
                            <div class="flex flex-col items-end gap-1">
                                @if($conversation->latestMessage->first())
                                    <span class="text-xs text-gray-400">
                                        {{ $conversation->latestMessage->first()->created_at->diffForHumans(short: true) }}
                                    </span>
                                @endif
                                @if($unreadCount > 0)
                                    <span class="inline-flex items-center justify-center w-5 h-5 text-xs font-bold text-white bg-blue-500 rounded-full">
                                        {{ $unreadCount > 9 ? '9+' : $unreadCount }}
                                    </span>
                                @endif
                            </div>
                        </div>
                    </button>
                </div>

                {{-- Expanded participants section --}}
                @if($isExpanded)
                    <div class="px-4 pb-3 ml-6 border-t border-gray-100">
                        <div class="pt-3">
                            {{-- Conversation Name Edit (only for creator) --}}
                            @if($conversation->isCreator(auth()->id()))
                                <div class="mb-3 pb-3 border-b border-gray-100">
                                    @if($editingConversationId === $conversation->id)
                                        <div class="flex items-center gap-2">
                                            <input 
                                                type="text"
                                                wire:model="editConversationName"
                                                wire:keydown.enter="saveConversationName"
                                                wire:keydown.escape="cancelEditingName"
                                                placeholder="Conversation name"
                                                class="flex-1 px-2 py-1 text-sm border border-gray-300 rounded focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                                autofocus
                                            >
                                            <button 
                                                wire:click="saveConversationName"
                                                class="p-1 text-green-600 hover:text-green-700 hover:bg-green-50 rounded"
                                                title="Save"
                                            >
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                                </svg>
                                            </button>
                                            <button 
                                                wire:click="cancelEditingName"
                                                class="p-1 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded"
                                                title="Cancel"
                                            >
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                                </svg>
                                            </button>
                                        </div>
                                    @else
                                        <div class="flex items-center justify-between">
                                            <div class="flex items-center gap-2">
                                                <span class="text-xs font-medium text-gray-500 uppercase tracking-wider">Name</span>
                                                <span class="text-sm text-gray-700">{{ $conversation->name ?? '(none)' }}</span>
                                            </div>
                                            <button 
                                                wire:click="startEditingName({{ $conversation->id }})"
                                                class="p-1 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded"
                                                title="Edit name"
                                            >
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                                                </svg>
                                            </button>
                                        </div>
                                    @endif
                                </div>
                            @endif

                            <div class="flex items-center justify-between mb-2">
                                <span class="text-xs font-medium text-gray-500 uppercase tracking-wider">Participants ({{ $participantCount }})</span>
                                <div class="flex items-center gap-1">
                                    {{-- Invite button --}}
                                    <button 
                                        wire:click="openInviteModal({{ $conversation->id }})"
                                        class="p-1 text-blue-600 hover:text-blue-700 hover:bg-blue-50 rounded transition"
                                        title="Invite people"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                                        </svg>
                                    </button>
                                    {{-- Archive button (only for creator) --}}
                                    @if($conversation->canArchive(auth()->id()))
                                        <button 
                                            wire:click="confirmArchive({{ $conversation->id }})"
                                            class="p-1 text-amber-500 hover:text-amber-600 hover:bg-amber-50 rounded transition"
                                            title="Archive conversation"
                                        >
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/>
                                            </svg>
                                        </button>
                                    @endif
                                    {{-- Leave button (for non-creators) --}}
                                    @if($conversation->canLeave(auth()->id()))
                                        <button 
                                            wire:click="confirmLeave({{ $conversation->id }})"
                                            class="p-1 text-red-500 hover:text-red-600 hover:bg-red-50 rounded transition"
                                            title="Leave conversation"
                                        >
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                                            </svg>
                                        </button>
                                    @endif
                                </div>
                            </div>
                            <div class="space-y-1">
                                @foreach($conversation->participants as $participant)
                                    <div class="flex items-center gap-2 py-1">
                                        <div class="w-6 h-6 {{ $conversation->isCreator($participant->id) ? 'bg-blue-500 text-white' : 'bg-gray-300 text-gray-600' }} rounded-full flex items-center justify-center text-xs font-medium">
                                            {{ strtoupper(substr($participant->name, 0, 1)) }}
                                        </div>
                                        <span class="text-sm text-gray-700">
                                            {{ $participant->name }}
                                            @if($participant->id === auth()->id())
                                                <span class="text-gray-400">(you)</span>
                                            @endif
                                            @if($conversation->isCreator($participant->id))
                                                <span class="text-xs text-blue-500 ml-1">owner</span>
                                            @endif
                                        </span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        @empty
            <div class="p-8 text-center text-gray-500">
                <svg class="w-12 h-12 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                </svg>
                <p class="text-sm">No conversations yet</p>
                <button 
                    wire:click="openNewChat"
                    class="mt-4 text-sm text-blue-600 hover:text-blue-700 font-medium"
                >
                    Start a new conversation
                </button>
            </div>
        @endforelse

        {{-- Archived Conversations Section --}}
        @if($this->archivedConversations->count() > 0)
            <div class="border-t border-gray-200 mt-2">
                <button 
                    wire:click="toggleShowArchived"
                    class="w-full px-4 py-2 text-left text-sm text-gray-500 hover:bg-gray-50 flex items-center justify-between"
                >
                    <span class="flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/>
                        </svg>
                        Archived ({{ $this->archivedConversations->count() }})
                    </span>
                    <svg class="w-4 h-4 transition-transform {{ $showArchived ? 'rotate-180' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>

                @if($showArchived)
                    <div class="bg-gray-50">
                        @foreach($this->archivedConversations as $conversation)
                            @php
                                $displayName = $conversation->getDisplayName(auth()->id());
                                $participantCount = $conversation->participants->count();
                            @endphp
                            <div class="border-b border-gray-100 opacity-75">
                                <div class="flex items-center gap-2 p-4">
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center gap-2">
                                            <span class="font-medium text-gray-700 truncate">{{ $displayName }}</span>
                                            @if($conversation->type === 'group')
                                                <span class="text-xs text-gray-500 bg-gray-200 px-1.5 py-0.5 rounded">{{ $participantCount }}</span>
                                            @endif
                                            <span class="text-xs text-amber-600 bg-amber-100 px-1.5 py-0.5 rounded">Archived</span>
                                        </div>
                                        <p class="text-xs text-gray-400 mt-1">
                                            Archived {{ $conversation->archived_at->diffForHumans() }}
                                        </p>
                                    </div>
                                    <button 
                                        wire:click="unarchiveConversation({{ $conversation->id }})"
                                        class="px-2 py-1 text-xs font-medium text-blue-600 hover:text-blue-700 hover:bg-blue-50 rounded transition"
                                        title="Unarchive"
                                    >
                                        Restore
                                    </button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        @endif
    </div>

    {{-- New Chat Modal --}}
    @if($showNewChatModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-modal="true">
            <div class="flex min-h-screen items-center justify-center p-4">
                {{-- Backdrop --}}
                <div 
                    wire:click="closeNewChat"
                    class="fixed inset-0 bg-black/50 transition-opacity"
                ></div>

                {{-- Modal --}}
                <div class="relative bg-white rounded-lg shadow-xl w-full max-w-md">
                    <div class="p-4 border-b border-gray-200">
                        <div class="flex items-center justify-between">
                            <h3 class="text-lg font-semibold text-gray-900">New Conversation</h3>
                            <button 
                                wire:click="closeNewChat"
                                class="text-gray-400 hover:text-gray-600"
                            >
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <div class="p-4 space-y-4">
                        {{-- Conversation Name (optional) --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Conversation name <span class="text-gray-400">(optional)</span>
                            </label>
                            <input
                                type="text"
                                wire:model="conversationName"
                                placeholder="e.g., Project Alpha Team"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            >
                        </div>

                        {{-- Search Members --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Add members</label>
                            <input
                                type="text"
                                wire:model.live.debounce.300ms="searchUsers"
                                placeholder="Search team members..."
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            >
                        </div>

                        {{-- Selected Users --}}
                        @if(count($selectedUsers) > 0)
                            <div class="flex flex-wrap gap-2 mt-3">
                                @foreach($this->selectedUsersDetails as $user)
                                    <span class="inline-flex items-center gap-1 px-2 py-1 text-sm bg-blue-100 text-blue-700 rounded-full">
                                        {{ $user->name }}
                                        <button 
                                            wire:click="toggleUser({{ $user->id }})"
                                            class="hover:text-blue-900"
                                        >
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                            </svg>
                                        </button>
                                    </span>
                                @endforeach
                            </div>
                        @endif

                        {{-- User List --}}
                        <div class="mt-3 max-h-60 overflow-y-auto border border-gray-200 rounded-lg">
                            @forelse($this->availableUsers as $user)
                                <button
                                    wire:click="toggleUser({{ $user->id }})"
                                    class="w-full px-3 py-2 text-left hover:bg-gray-50 flex items-center justify-between {{ in_array($user->id, $selectedUsers) ? 'bg-blue-50' : '' }}"
                                >
                                    <span class="text-sm text-gray-900">{{ $user->name }}</span>
                                    @if(in_array($user->id, $selectedUsers))
                                        <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                        </svg>
                                    @endif
                                </button>
                            @empty
                                <p class="p-3 text-sm text-gray-500 text-center">No team members found</p>
                            @endforelse
                        </div>
                    </div>

                    <div class="p-4 border-t border-gray-200 flex justify-end gap-3">
                        <button 
                            wire:click="closeNewChat"
                            class="px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100 rounded-lg transition"
                        >
                            Cancel
                        </button>
                        <button 
                            wire:click="startConversation"
                            @if(count($selectedUsers) === 0) disabled @endif
                            class="px-4 py-2 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-lg transition disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            Start Chat
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Invite Modal --}}
    @if($showInviteModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-modal="true">
            <div class="flex min-h-screen items-center justify-center p-4">
                {{-- Backdrop --}}
                <div 
                    wire:click="closeInviteModal"
                    class="fixed inset-0 bg-black/50 transition-opacity"
                ></div>

                {{-- Modal --}}
                <div class="relative bg-white rounded-lg shadow-xl w-full max-w-md">
                    <div class="p-4 border-b border-gray-200">
                        <div class="flex items-center justify-between">
                            <h3 class="text-lg font-semibold text-gray-900">Invite People</h3>
                            <button 
                                wire:click="closeInviteModal"
                                class="text-gray-400 hover:text-gray-600"
                            >
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <div class="p-4 space-y-4">
                        {{-- Warning about history access --}}
                        @if($showInviteWarning)
                            <div class="bg-amber-50 border border-amber-200 rounded-lg p-3">
                                <div class="flex gap-3">
                                    <div class="flex-shrink-0">
                                        <svg class="w-5 h-5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                        </svg>
                                    </div>
                                    <div class="flex-1">
                                        <p class="text-sm text-amber-800 font-medium">New members will see conversation history</p>
                                        <p class="text-xs text-amber-700 mt-1">Invited users will have access to all previous messages in this conversation.</p>
                                        <button 
                                            wire:click="createNewConversationInstead"
                                            class="mt-2 text-xs font-medium text-amber-700 hover:text-amber-800 underline"
                                        >
                                            Create a new conversation instead
                                        </button>
                                    </div>
                                    <button 
                                        wire:click="$set('showInviteWarning', false)"
                                        class="flex-shrink-0 text-amber-400 hover:text-amber-600"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        @endif

                        {{-- Search Users --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Search team members</label>
                            <input
                                type="text"
                                wire:model.live.debounce.300ms="inviteSearchUsers"
                                placeholder="Search by name..."
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            >
                        </div>

                        {{-- Selected Users --}}
                        @if(count($inviteSelectedUsers) > 0)
                            <div class="flex flex-wrap gap-2">
                                @foreach($this->inviteSelectedUsersDetails as $user)
                                    <span class="inline-flex items-center gap-1 px-2 py-1 text-sm bg-blue-100 text-blue-700 rounded-full">
                                        {{ $user->name }}
                                        <button 
                                            wire:click="toggleInviteUser({{ $user->id }})"
                                            class="hover:text-blue-900"
                                        >
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                            </svg>
                                        </button>
                                    </span>
                                @endforeach
                            </div>
                        @endif

                        {{-- User List --}}
                        <div class="max-h-60 overflow-y-auto border border-gray-200 rounded-lg">
                            @forelse($this->inviteAvailableUsers as $user)
                                <button
                                    wire:click="toggleInviteUser({{ $user->id }})"
                                    class="w-full px-3 py-2 text-left hover:bg-gray-50 flex items-center justify-between {{ in_array($user->id, $inviteSelectedUsers) ? 'bg-blue-50' : '' }}"
                                >
                                    <div class="flex items-center gap-2">
                                        <div class="w-6 h-6 bg-gray-300 rounded-full flex items-center justify-center text-xs font-medium text-gray-600">
                                            {{ strtoupper(substr($user->name, 0, 1)) }}
                                        </div>
                                        <span class="text-sm text-gray-900">{{ $user->name }}</span>
                                    </div>
                                    @if(in_array($user->id, $inviteSelectedUsers))
                                        <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                        </svg>
                                    @endif
                                </button>
                            @empty
                                <p class="p-3 text-sm text-gray-500 text-center">
                                    @if($inviteSearchUsers)
                                        No team members found
                                    @else
                                        All team members are already in this conversation
                                    @endif
                                </p>
                            @endforelse
                        </div>
                    </div>

                    <div class="p-4 border-t border-gray-200 flex justify-end gap-3">
                        <button 
                            wire:click="closeInviteModal"
                            class="px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100 rounded-lg transition"
                        >
                            Cancel
                        </button>
                        <button 
                            wire:click="inviteUsers"
                            @if(count($inviteSelectedUsers) === 0) disabled @endif
                            class="px-4 py-2 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-lg transition disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            Invite {{ count($inviteSelectedUsers) > 0 ? '(' . count($inviteSelectedUsers) . ')' : '' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Leave Confirmation Modal --}}
    @if($showLeaveConfirmation)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-modal="true">
            <div class="flex min-h-screen items-center justify-center p-4">
                {{-- Backdrop --}}
                <div 
                    wire:click="cancelLeave"
                    class="fixed inset-0 bg-black/50 transition-opacity"
                ></div>

                {{-- Modal --}}
                <div class="relative bg-white rounded-lg shadow-xl w-full max-w-sm">
                    <div class="p-6">
                        <div class="flex items-center justify-center w-12 h-12 mx-auto mb-4 bg-red-100 rounded-full">
                            <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 text-center mb-2">Leave Conversation?</h3>
                        <p class="text-sm text-gray-500 text-center">
                            You will no longer receive messages from this conversation. You can be invited back by other participants.
                        </p>
                    </div>
                    <div class="px-6 pb-6 flex gap-3">
                        <button 
                            wire:click="cancelLeave"
                            class="flex-1 px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition"
                        >
                            Cancel
                        </button>
                        <button 
                            wire:click="leaveConversation"
                            class="flex-1 px-4 py-2 text-sm font-medium text-white bg-red-600 hover:bg-red-700 rounded-lg transition"
                        >
                            Leave
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Archive Confirmation Modal --}}
    @if($showArchiveConfirmation)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-modal="true">
            <div class="flex min-h-screen items-center justify-center p-4">
                {{-- Backdrop --}}
                <div 
                    wire:click="cancelArchive"
                    class="fixed inset-0 bg-black/50 transition-opacity"
                ></div>

                {{-- Modal --}}
                <div class="relative bg-white rounded-lg shadow-xl w-full max-w-sm">
                    <div class="p-6">
                        <div class="flex items-center justify-center w-12 h-12 mx-auto mb-4 bg-amber-100 rounded-full">
                            <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/>
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 text-center mb-2">Archive Conversation?</h3>
                        <p class="text-sm text-gray-500 text-center">
                            This conversation will be hidden from your Talk list but all messages will be preserved. As the owner, you can unarchive it later.
                        </p>
                    </div>
                    <div class="px-6 pb-6 flex gap-3">
                        <button 
                            wire:click="cancelArchive"
                            class="flex-1 px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition"
                        >
                            Cancel
                        </button>
                        <button 
                            wire:click="archiveConversation"
                            class="flex-1 px-4 py-2 text-sm font-medium text-white bg-amber-600 hover:bg-amber-700 rounded-lg transition"
                        >
                            Archive
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
