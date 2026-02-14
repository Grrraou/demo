<div class="flex flex-col h-full" wire:poll.10s>
    {{-- Header --}}
    <div class="p-4 border-b border-gray-200">
        <div class="flex items-center justify-between">
            <h2 class="text-lg font-semibold text-gray-900">Conversations</h2>
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

    {{-- Conversation List --}}
    <div class="flex-1 overflow-y-auto">
        @forelse($this->conversations as $conversation)
            @php
                $displayName = $conversation->getDisplayName(auth()->id());
                $unreadCount = $conversation->unreadCountFor(auth()->id());
            @endphp
            <button
                wire:click="selectConversation({{ $conversation->id }})"
                class="w-full p-4 text-left hover:bg-gray-50 border-b border-gray-100 transition {{ $activeConversationId === $conversation->id ? 'bg-blue-50 border-l-4 border-l-blue-500' : '' }}"
            >
                <div class="flex items-start justify-between gap-2">
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2">
                            <span class="font-medium text-gray-900 truncate">{{ $displayName }}</span>
                            @if($conversation->type === 'group')
                                <span class="text-xs text-gray-500 bg-gray-100 px-1.5 py-0.5 rounded">Group</span>
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
                                @foreach($this->availableUsers->whereIn('id', $selectedUsers) as $user)
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
</div>
