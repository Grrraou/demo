<div class="h-full flex flex-col bg-white">
    <!-- Header -->
    <div class="border-b px-6 py-4 flex items-center justify-between flex-shrink-0">
        <div class="flex items-center gap-4">
            <h1 class="text-xl font-semibold text-gray-900">📅 Calendar</h1>
            <div class="flex items-center gap-2">
                <button wire:click="previousMonth" class="p-2 hover:bg-gray-100 rounded-lg transition">
                    <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                </button>
                <button wire:click="goToToday" class="px-3 py-1.5 text-sm font-medium text-gray-700 hover:bg-gray-100 rounded-lg transition">
                    Today
                </button>
                <button wire:click="nextMonth" class="p-2 hover:bg-gray-100 rounded-lg transition">
                    <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </button>
                <span class="text-lg font-medium text-gray-900 ml-2">{{ $monthName }}</span>
            </div>
        </div>
        <button wire:click="openCreateModal" class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            New Event
        </button>
    </div>

    <!-- Calendar Grid -->
    <div class="flex-1 overflow-auto p-4">
        <!-- Day Headers -->
        <div class="grid grid-cols-7 mb-2">
            @foreach(['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'] as $day)
                <div class="text-center text-sm font-medium text-gray-500 py-2">{{ $day }}</div>
            @endforeach
        </div>

        <!-- Calendar Days -->
        <div class="grid grid-cols-7 gap-px bg-gray-200 rounded-lg overflow-hidden">
            @foreach($calendarDays as $day)
                <div 
                    wire:click="openCreateModal('{{ $day['date'] }}')"
                    class="min-h-[120px] bg-white p-2 cursor-pointer hover:bg-gray-50 transition {{ !$day['isCurrentMonth'] ? 'bg-gray-50' : '' }}"
                >
                    <div class="flex items-center justify-between mb-1">
                        <span class="text-sm font-medium {{ $day['isToday'] ? 'bg-blue-600 text-white w-7 h-7 rounded-full flex items-center justify-center' : ($day['isCurrentMonth'] ? 'text-gray-900' : 'text-gray-400') }}">
                            {{ $day['day'] }}
                        </span>
                    </div>
                    <div class="space-y-1">
                        @foreach(array_slice($day['events'], 0, 3) as $event)
                            <div 
                                wire:click.stop="viewEvent({{ $event['id'] }})"
                                class="text-xs px-2 py-1 rounded truncate cursor-pointer hover:opacity-80 transition"
                                style="background-color: {{ $event['color_hex'] }}20; color: {{ $event['color_hex'] }}; border-left: 3px solid {{ $event['color_hex'] }};"
                                title="{{ $event['title'] }}"
                            >
                                @if(!$event['all_day'])
                                    <span class="font-medium">{{ \Carbon\Carbon::parse($event['start_at'])->format('H:i') }}</span>
                                @endif
                                {{ $event['title'] }}
                            </div>
                        @endforeach
                        @if(count($day['events']) > 3)
                            <div class="text-xs text-gray-500 px-2">+{{ count($day['events']) - 3 }} more</div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Event Create/Edit Modal -->
    @if($showEventModal)
        <div class="fixed inset-0 bg-black/50 flex items-center justify-center z-50" wire:click.self="closeEventModal">
            <div class="bg-white rounded-xl shadow-2xl w-full max-w-lg mx-4 max-h-[90vh] overflow-y-auto">
                <div class="px-6 py-4 border-b flex items-center justify-between sticky top-0 bg-white">
                    <h2 class="text-lg font-semibold text-gray-900">{{ $isEditing ? 'Edit Event' : 'New Event' }}</h2>
                    <button wire:click="closeEventModal" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <form wire:submit="saveEvent" class="p-6 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Title *</label>
                        <input type="text" wire:model="title" class="w-full px-3 py-2 rounded-lg border border-gray-300 bg-white text-gray-900 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none" required>
                        @error('title') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                        <textarea wire:model="description" rows="2" class="w-full px-3 py-2 rounded-lg border border-gray-300 bg-white text-gray-900 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none"></textarea>
                    </div>

                    <div class="flex items-center gap-2">
                        <input type="checkbox" wire:model.live="allDay" id="allDay" class="w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                        <label for="allDay" class="text-sm text-gray-700">All day</label>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Start Date *</label>
                            <input type="date" wire:model="startDate" class="w-full px-3 py-2 rounded-lg border border-gray-300 bg-white text-gray-900 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none" required>
                        </div>
                        @if(!$allDay)
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Start Time</label>
                                <input type="time" wire:model="startTime" class="w-full px-3 py-2 rounded-lg border border-gray-300 bg-white text-gray-900 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none">
                            </div>
                        @endif
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">End Date</label>
                            <input type="date" wire:model="endDate" class="w-full px-3 py-2 rounded-lg border border-gray-300 bg-white text-gray-900 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none">
                        </div>
                        @if(!$allDay)
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">End Time</label>
                                <input type="time" wire:model="endTime" class="w-full px-3 py-2 rounded-lg border border-gray-300 bg-white text-gray-900 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none">
                            </div>
                        @endif
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Color</label>
                        <div class="flex flex-wrap gap-2">
                            @foreach($colors as $colorName => $colorHex)
                                <button 
                                    type="button"
                                    wire:click="$set('color', '{{ $colorName }}')"
                                    class="w-8 h-8 rounded-full border-2 transition {{ $color === $colorName ? 'border-gray-900 scale-110' : 'border-transparent hover:scale-105' }}"
                                    style="background-color: {{ $colorHex }}"
                                    title="{{ ucfirst($colorName) }}"
                                ></button>
                            @endforeach
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Share with</label>
                        <div class="max-h-40 overflow-y-auto border border-gray-300 rounded-lg p-2 space-y-1 bg-white">
                            @forelse($this->teamMembers as $member)
                                <label class="flex items-center gap-2 p-2 hover:bg-gray-50 rounded cursor-pointer">
                                    <input 
                                        type="checkbox" 
                                        wire:click="toggleParticipant({{ $member->id }})"
                                        {{ in_array($member->id, $selectedParticipants) ? 'checked' : '' }}
                                        class="w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                    >
                                    <span class="text-sm text-gray-700">{{ $member->name }}</span>
                                </label>
                            @empty
                                <p class="text-sm text-gray-500 p-2">No other team members</p>
                            @endforelse
                        </div>
                    </div>

                    @if(!$isEditing || !$editingEventId || !\App\Models\Event::find($editingEventId)?->conversation_id)
                        <div class="flex items-center gap-2 p-3 bg-blue-50 rounded-lg">
                            <input type="checkbox" wire:model="createConversation" id="createConversation" class="w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                            <label for="createConversation" class="text-sm text-gray-700">
                                <span class="font-medium">💬 Create a Talk conversation</span>
                                <span class="text-gray-500 block text-xs">A group chat will be created with all participants</span>
                            </label>
                        </div>
                    @else
                        <div class="p-3 bg-green-50 rounded-lg">
                            <span class="text-sm text-green-700">💬 This event has a linked conversation</span>
                        </div>
                    @endif

                    <div class="flex justify-between pt-4 border-t">
                        @if($isEditing)
                            <button type="button" wire:click="deleteEvent" wire:confirm="Are you sure you want to delete this event?" class="px-4 py-2 text-red-600 hover:bg-red-50 rounded-lg transition">
                                Delete
                            </button>
                        @else
                            <div></div>
                        @endif
                        <div class="flex gap-3">
                            <button type="button" wire:click="closeEventModal" class="px-4 py-2 text-gray-700 hover:bg-gray-100 rounded-lg transition">
                                Cancel
                            </button>
                            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                                {{ $isEditing ? 'Save Changes' : 'Create Event' }}
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <!-- Event Detail Modal -->
    @if($showEventDetail && $selectedEvent)
        <div class="fixed inset-0 bg-black/50 flex items-center justify-center z-50" wire:click.self="closeEventDetail">
            <div class="bg-white rounded-xl shadow-2xl w-full max-w-md mx-4">
                <div class="px-6 py-4 border-b flex items-center justify-between" style="border-left: 4px solid {{ $selectedEvent['color_hex'] }}">
                    <h2 class="text-lg font-semibold text-gray-900">{{ $selectedEvent['title'] }}</h2>
                    <button wire:click="closeEventDetail" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <div class="p-6 space-y-4">
                    <div class="flex items-center gap-3 text-sm text-gray-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        <span>
                            @if($selectedEvent['all_day'])
                                {{ \Carbon\Carbon::parse($selectedEvent['start_at'])->format('D, M j, Y') }}
                                @if($selectedEvent['end_at'] && \Carbon\Carbon::parse($selectedEvent['start_at'])->format('Y-m-d') !== \Carbon\Carbon::parse($selectedEvent['end_at'])->format('Y-m-d'))
                                    - {{ \Carbon\Carbon::parse($selectedEvent['end_at'])->format('D, M j, Y') }}
                                @endif
                                <span class="text-gray-400">(All day)</span>
                            @else
                                {{ \Carbon\Carbon::parse($selectedEvent['start_at'])->format('D, M j, Y \a\t H:i') }}
                                @if($selectedEvent['end_at'])
                                    - {{ \Carbon\Carbon::parse($selectedEvent['end_at'])->format('H:i') }}
                                @endif
                            @endif
                        </span>
                    </div>

                    @if($selectedEvent['description'])
                        <div class="text-sm text-gray-700">
                            {{ $selectedEvent['description'] }}
                        </div>
                    @endif

                    <div class="flex items-center gap-2 text-sm text-gray-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                        <span>Created by <span class="font-medium">{{ $selectedEvent['creator_name'] }}</span></span>
                    </div>

                    @if(count($selectedEvent['participants']) > 0)
                        <div>
                            <div class="text-sm font-medium text-gray-700 mb-2">Participants</div>
                            <div class="space-y-1">
                                @foreach($selectedEvent['participants'] as $participant)
                                    <div class="flex items-center justify-between text-sm">
                                        <span class="text-gray-700">{{ $participant['name'] }}</span>
                                        <span class="px-2 py-0.5 rounded-full text-xs
                                            {{ $participant['status'] === 'accepted' ? 'bg-green-100 text-green-700' : '' }}
                                            {{ $participant['status'] === 'declined' ? 'bg-red-100 text-red-700' : '' }}
                                            {{ $participant['status'] === 'invited' ? 'bg-yellow-100 text-yellow-700' : '' }}
                                        ">
                                            {{ ucfirst($participant['status']) }}
                                        </span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    @if($selectedEvent['conversation_id'])
                        <a href="{{ route('chat.index', ['conversation' => $selectedEvent['conversation_id']]) }}" class="flex items-center gap-2 p-3 bg-blue-50 rounded-lg text-blue-700 hover:bg-blue-100 transition">
                            <span>💬</span>
                            <span class="text-sm font-medium">Open conversation</span>
                        </a>
                    @endif

                    @if($selectedEvent['my_status'])
                        <div class="border-t pt-4">
                            <div class="text-sm font-medium text-gray-700 mb-2">Your response</div>
                            <div class="flex gap-2">
                                <button 
                                    wire:click="respondToEvent('accepted')"
                                    class="flex-1 px-3 py-2 text-sm rounded-lg transition {{ $selectedEvent['my_status'] === 'accepted' ? 'bg-green-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-green-100' }}"
                                >
                                    ✓ Accept
                                </button>
                                <button 
                                    wire:click="respondToEvent('declined')"
                                    class="flex-1 px-3 py-2 text-sm rounded-lg transition {{ $selectedEvent['my_status'] === 'declined' ? 'bg-red-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-red-100' }}"
                                >
                                    ✗ Decline
                                </button>
                            </div>
                        </div>
                    @endif

                    @if($selectedEvent['is_creator'])
                        <div class="border-t pt-4 flex justify-end">
                            <button wire:click="openEditModal({{ $selectedEvent['id'] }})" class="px-4 py-2 text-blue-600 hover:bg-blue-50 rounded-lg transition">
                                Edit Event
                            </button>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @endif
</div>
