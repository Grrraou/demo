<div class="flex flex-col h-full" wire:poll.5s="loadMessages">
    @if($conversationId && $this->conversation)
        {{-- Header --}}
        <div class="p-4 bg-white border-b border-gray-200">
            @php
                $displayName = $this->conversation->getDisplayName(auth()->id());
            @endphp
            <div class="flex items-center justify-between">
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
                
                {{-- Meeting Button --}}
                <button 
                    wire:click="startMeeting"
                    class="inline-flex items-center gap-2 px-3 py-1.5 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition"
                    title="Join video meeting"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                    </svg>
                    Join Meeting
                </button>
            </div>
        </div>

        {{-- Meeting Notification (temporary) --}}
        @if($meetingNotification)
            <div 
                class="mx-4 mt-4"
                x-data="{ show: true }"
                x-init="setTimeout(() => { show = false; $wire.dismissMeetingNotification(); }, 30000)"
                x-show="show"
                x-transition:leave="transition ease-in duration-300"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
            >
                <div class="bg-purple-50 border border-purple-200 rounded-xl px-4 py-3">
                    <div class="flex items-center justify-between gap-3">
                        <div class="flex items-center gap-3">
                            <div class="flex-shrink-0 w-10 h-10 bg-purple-500 rounded-full flex items-center justify-center">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm text-purple-900">
                                    <span class="font-semibold">{{ $meetingNotification['starter_name'] }}</span>
                                    started a meeting
                                </p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <button 
                                wire:click="joinMeeting"
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm font-medium text-white bg-purple-600 hover:bg-purple-700 rounded-lg transition"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                                </svg>
                                Join
                            </button>
                            <button 
                                @click="show = false; $wire.dismissMeetingNotification()"
                                class="p-1 text-purple-400 hover:text-purple-600 rounded"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        {{-- Messages --}}
        <div 
            id="messages-container"
            class="flex-1 overflow-y-auto p-4 space-y-4"
            x-data="{ 
                scrollToBottom() {
                    this.$el.scrollTop = this.$el.scrollHeight;
                }
            }"
            x-init="$nextTick(() => scrollToBottom())"
            @scroll-to-bottom.window="$nextTick(() => scrollToBottom())"
        >
            @forelse($messages as $message)
                @if($message['is_system'])
                    {{-- System Message --}}
                    <div class="flex justify-center my-2">
                        <div class="inline-flex items-center gap-2 px-3 py-1 bg-gray-100 rounded-full">
                            @if($message['type'] === 'user_joined')
                                <svg class="w-3.5 h-3.5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                                </svg>
                            @elseif($message['type'] === 'user_left')
                                <svg class="w-3.5 h-3.5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                                </svg>
                            @elseif($message['type'] === 'renamed')
                                <svg class="w-3.5 h-3.5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                                </svg>
                            @else
                                <svg class="w-3.5 h-3.5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            @endif
                            <span class="text-xs text-gray-500">{{ $message['body'] }}</span>
                            <span class="text-xs text-gray-400">{{ $message['created_at'] }}</span>
                        </div>
                    </div>
                @else
                    {{-- Regular Message --}}
                    <div class="flex justify-start">
                        <div class="max-w-[70%]">
                            <p class="text-xs text-gray-500 mb-1 ml-1">
                                {{ $message['sender_name'] }}
                                @if($message['is_mine'])
                                    <span class="text-blue-500">(you)</span>
                                @endif
                            </p>
                            <div class="bg-white text-gray-900 border border-gray-200 rounded-2xl px-4 py-2 shadow-sm">
                                <p class="text-sm whitespace-pre-wrap break-words">{{ $message['body'] }}</p>
                            </div>
                            <p class="text-xs text-gray-400 mt-1 ml-1">
                                {{ $message['created_at'] }}
                            </p>
                        </div>
                    </div>
                @endif
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

    {{-- JavaScript for meeting and scroll --}}
    <script>
        document.addEventListener('livewire:init', () => {
            Livewire.on('openMeeting', ({ url }) => {
                window.open(url, '_blank', 'noopener,noreferrer');
            });
            
            Livewire.on('scrollToBottom', () => {
                window.dispatchEvent(new CustomEvent('scroll-to-bottom'));
            });
        });

        // Listen for meeting started events via Echo
        (function() {
            let currentChannel = null;
            let echoSubscription = null;
            let conversationId = @json($conversationId);

            function subscribeToMeetingEvents(convId) {
                if (!convId) return;
                
                // Wait for Echo to be available
                if (!window.Echo) {
                    console.log('[Chat] Waiting for Echo to be available...');
                    setTimeout(() => subscribeToMeetingEvents(convId), 100);
                    return;
                }
                
                // Don't re-subscribe to the same channel
                if (currentChannel === convId) return;
                
                // Unsubscribe from previous channel
                if (currentChannel) {
                    console.log('[Chat] Leaving channel:', currentChannel);
                    window.Echo.leave('conversation.' + currentChannel);
                }
                
                currentChannel = convId;
                console.log('[Chat] Subscribing to conversation channel:', convId);
                
                // Subscribe to the conversation's private channel
                echoSubscription = window.Echo.private('conversation.' + convId)
                    .subscribed(() => {
                        console.log('[Chat] Successfully subscribed to channel:', convId);
                    })
                    .error((error) => {
                        console.error('[Chat] Error subscribing to channel:', error);
                    })
                    .listen('.meeting.started', (data) => {
                        console.log('[Chat] Meeting started event received:', data);
                        // Dispatch to Livewire component
                        Livewire.dispatch('meetingStartedNotification', { data: data });
                    });
            }

            // Subscribe on page load
            if (conversationId) {
                subscribeToMeetingEvents(conversationId);
            }

            // Re-subscribe when conversation changes
            Livewire.on('conversationSelected', (params) => {
                // Livewire 3 passes params as an array with named keys
                const convId = params.conversationId;
                console.log('[Chat] Conversation selected:', convId);
                if (convId) {
                    subscribeToMeetingEvents(convId);
                }
            });
        })();
    </script>
</div>
