<div class="flex flex-col h-full">
    @if($conversationId && $this->conversation)
        {{-- Header --}}
        <div class="p-4 bg-white border-b border-gray-200">
            @php
                $otherParticipants = $this->conversation->participants->where('id', '!=', auth()->id());
                $displayName = $this->conversation->type === 'direct' 
                    ? $otherParticipants->first()?->name ?? 'Unknown'
                    : $otherParticipants->pluck('name')->join(', ');
            @endphp
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-blue-500 rounded-full flex items-center justify-center text-white font-semibold">
                    {{ strtoupper(substr($displayName, 0, 1)) }}
                </div>
                <div>
                    <h3 class="font-semibold text-gray-900">{{ $displayName }}</h3>
                    <p class="text-xs text-gray-500">
                        @if($this->conversation->type === 'group')
                            {{ $this->conversation->participants->count() }} participants
                        @elseif($this->conversation->type === 'entity')
                            {{ ucfirst($this->conversation->entity_type) }} #{{ $this->conversation->entity_id }}
                        @else
                            Direct message
                        @endif
                    </p>
                </div>
            </div>
        </div>

        {{-- Messages --}}
        <div 
            id="messages-container"
            class="flex-1 overflow-y-auto p-4 space-y-4"
            x-data
            x-init="$nextTick(() => $el.scrollTop = $el.scrollHeight)"
            x-effect="$el.scrollTop = $el.scrollHeight"
        >
            @forelse($messages as $message)
                <div class="flex {{ $message['is_mine'] ? 'justify-end' : 'justify-start' }}">
                    <div class="max-w-[70%] {{ $message['is_mine'] ? 'order-2' : 'order-1' }}">
                        @if(!$message['is_mine'])
                            <p class="text-xs text-gray-500 mb-1 ml-1">{{ $message['sender_name'] }}</p>
                        @endif
                        <div class="{{ $message['is_mine'] ? 'bg-blue-500 text-white' : 'bg-white text-gray-900 border border-gray-200' }} rounded-2xl px-4 py-2 shadow-sm">
                            <p class="text-sm whitespace-pre-wrap break-words">{{ $message['body'] }}</p>
                        </div>
                        <p class="text-xs text-gray-400 mt-1 {{ $message['is_mine'] ? 'text-right mr-1' : 'ml-1' }}">
                            {{ $message['created_at'] }}
                        </p>
                    </div>
                </div>
            @empty
                <div class="flex items-center justify-center h-full">
                    <div class="text-center text-gray-500">
                        <svg class="w-16 h-16 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                        </svg>
                        <p>No messages yet</p>
                        <p class="text-sm mt-1">Send a message to start the conversation</p>
                    </div>
                </div>
            @endforelse
        </div>

        {{-- Message Input --}}
        <div class="p-4 bg-white border-t border-gray-200">
            <form wire:submit="sendMessage" class="flex items-end gap-3">
                <div class="flex-1">
                    <textarea
                        wire:model="newMessage"
                        placeholder="Type a message..."
                        rows="1"
                        class="w-full px-4 py-2 border border-gray-300 rounded-xl resize-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        x-data
                        x-on:keydown.enter.prevent="if (!$event.shiftKey) { $wire.sendMessage(); }"
                        x-on:input="$el.style.height = 'auto'; $el.style.height = Math.min($el.scrollHeight, 120) + 'px'"
                    ></textarea>
                </div>
                <button 
                    type="submit"
                    class="p-2 bg-blue-500 text-white rounded-xl hover:bg-blue-600 transition disabled:opacity-50"
                    wire:loading.attr="disabled"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                    </svg>
                </button>
            </form>
        </div>
    @else
        {{-- No conversation selected --}}
        <div class="flex-1 flex items-center justify-center">
            <div class="text-center text-gray-500">
                <svg class="w-24 h-24 mx-auto mb-6 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M17 8h2a2 2 0 012 2v6a2 2 0 01-2 2h-2v4l-4-4H9a1.994 1.994 0 01-1.414-.586m0 0L11 14h4a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2v4l.586-.586z"/>
                </svg>
                <h3 class="text-xl font-medium text-gray-700 mb-2">Welcome to Talk</h3>
                <p class="text-sm">Select a conversation or start a new one</p>
            </div>
        </div>
    @endif
</div>
