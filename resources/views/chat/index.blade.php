@extends('layouts.app')

@section('title', 'Talk')

@section('content')
<div class="flex h-[calc(100vh-4rem)]">
    {{-- Conversation List Sidebar --}}
    <div class="w-80 border-r border-gray-200 bg-white flex-shrink-0">
        @livewire('chat.conversation-list', ['activeConversationId' => $activeConversation?->id])
    </div>

    {{-- Message Panel --}}
    <div class="flex-1 bg-gray-50">
        @livewire('chat.message-panel', ['conversationId' => $activeConversation?->id])
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Auto-scroll to bottom when new messages arrive
    document.addEventListener('livewire:navigated', () => {
        const container = document.getElementById('messages-container');
        if (container) {
            container.scrollTop = container.scrollHeight;
        }
    });
</script>
@endpush
