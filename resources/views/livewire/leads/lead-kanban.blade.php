<div class="h-full flex flex-col" x-data="leadKanban()">
    <!-- Header -->
    <div class="bg-white border-b px-6 py-4 flex items-center justify-between flex-shrink-0">
        <div class="flex items-center gap-4">
            <h1 class="text-xl font-semibold text-gray-900">🎯 Leads</h1>
            <label class="inline-flex items-center gap-2 text-sm text-gray-600">
                <input type="checkbox" wire:model.live="showWonLost" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                <span>Show Won/Lost</span>
            </label>
        </div>
        @if($canEdit)
            <button wire:click="openCreateModal" class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                New Lead
            </button>
        @endif
    </div>

    <!-- Kanban Board -->
    <div class="flex-1 overflow-x-auto p-6">
        <div class="flex gap-4 h-full min-w-max">
            @foreach($statuses as $status => $config)
                <div class="w-80 flex-shrink-0 flex flex-col bg-gray-100 rounded-lg">
                    <!-- Column Header -->
                    <div class="px-4 py-3 border-b border-gray-200 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="w-3 h-3 rounded-full bg-{{ $config['color'] }}-500"></span>
                            <h3 class="font-medium text-gray-900">{{ $config['label'] }}</h3>
                            <span class="text-xs text-gray-500 bg-gray-200 rounded-full px-2 py-0.5">
                                {{ count($leads[$status] ?? []) }}
                            </span>
                        </div>
                    </div>

                    <!-- Column Body (Droppable) -->
                    <div 
                        class="flex-1 p-2 space-y-2 overflow-y-auto min-h-[200px]"
                        x-on:dragover.prevent="onDragOver($event, '{{ $status }}')"
                        x-on:dragleave="onDragLeave($event)"
                        x-on:drop="onDrop($event, '{{ $status }}')"
                        data-status="{{ $status }}"
                    >
                        @forelse($leads[$status] ?? [] as $index => $lead)
                            <div 
                                class="bg-white rounded-lg shadow-sm border border-gray-200 p-3 {{ $canEdit ? 'cursor-move' : '' }} hover:shadow-md transition group"
                                draggable="{{ $canEdit ? 'true' : 'false' }}"
                                @if($canEdit)
                                x-on:dragstart="onDragStart($event, {{ $lead['id'] }}, '{{ $status }}', {{ $index }})"
                                x-on:dragend="onDragEnd($event)"
                                @endif
                                data-lead-id="{{ $lead['id'] }}"
                                data-position="{{ $index }}"
                            >
                                <div class="flex items-start justify-between gap-2">
                                    <h4 class="font-medium text-gray-900 text-sm">{{ $lead['name'] }}</h4>
                                    @if($canEdit)
                                        <button 
                                            wire:click="openEditModal({{ $lead['id'] }})"
                                            class="opacity-0 group-hover:opacity-100 p-1 text-gray-400 hover:text-gray-600 transition"
                                        >
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                                            </svg>
                                        </button>
                                    @endif
                                </div>
                                
                                @if($lead['company_name'])
                                    <p class="text-xs text-gray-500 mt-1">🏢 {{ $lead['company_name'] }}</p>
                                @endif
                                
                                <div class="mt-2 flex items-center gap-3 text-xs text-gray-500">
                                    @if($lead['email'])
                                        <span class="truncate max-w-[120px]" title="{{ $lead['email'] }}">✉️ {{ $lead['email'] }}</span>
                                    @endif
                                    @if($lead['phone'])
                                        <span>📞 {{ $lead['phone'] }}</span>
                                    @endif
                                </div>

                                @if($lead['value'])
                                    <div class="mt-2">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">
                                            💰 ${{ number_format($lead['value'], 2) }}
                                        </span>
                                    </div>
                                @endif

                                @if($lead['assigned_to'])
                                    <div class="mt-2 flex items-center gap-1">
                                        <span class="w-5 h-5 rounded-full bg-gray-300 text-[10px] flex items-center justify-center text-gray-600 font-medium">
                                            {{ strtoupper(substr($lead['assigned_to']['name'] ?? '', 0, 1)) }}
                                        </span>
                                        <span class="text-xs text-gray-500">{{ $lead['assigned_to']['name'] ?? '' }}</span>
                                    </div>
                                @endif
                            </div>
                        @empty
                            <div class="text-center py-8 text-gray-400 text-sm">
                                No leads
                            </div>
                        @endforelse

                        <!-- Drop placeholder -->
                        <div 
                            class="hidden border-2 border-dashed border-blue-400 bg-blue-50 rounded-lg h-20"
                            x-ref="placeholder-{{ $status }}"
                        ></div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Create Modal -->
    @if($showCreateModal)
        <div class="fixed inset-0 bg-black/50 flex items-center justify-center z-50" wire:click.self="closeCreateModal">
            <div class="bg-white rounded-xl shadow-2xl w-full max-w-lg mx-4">
                <div class="px-6 py-4 border-b flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-gray-900">New Lead</h2>
                    <button wire:click="closeCreateModal" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <form wire:submit="createLead" class="p-6 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Name *</label>
                        <input type="text" wire:model="name" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500" required>
                        @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                            <input type="email" wire:model="email" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                            @error('email') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
                            <input type="text" wire:model="phone" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Company</label>
                            <input type="text" wire:model="companyName" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Value ($)</label>
                            <input type="number" step="0.01" wire:model="value" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Assigned To</label>
                        <select wire:model="assignedTo" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                            <option value="">Unassigned</option>
                            @foreach($this->teamMembers as $member)
                                <option value="{{ $member->id }}">{{ $member->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                        <textarea wire:model="notes" rows="3" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"></textarea>
                    </div>

                    <div class="flex justify-end gap-3 pt-4">
                        <button type="button" wire:click="closeCreateModal" class="px-4 py-2 text-gray-700 hover:bg-gray-100 rounded-lg transition">
                            Cancel
                        </button>
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                            Create Lead
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <!-- Edit Modal -->
    @if($showEditModal)
        <div class="fixed inset-0 bg-black/50 flex items-center justify-center z-50" wire:click.self="closeEditModal">
            <div class="bg-white rounded-xl shadow-2xl w-full max-w-lg mx-4">
                <div class="px-6 py-4 border-b flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-gray-900">Edit Lead</h2>
                    <button wire:click="closeEditModal" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <form wire:submit="updateLead" class="p-6 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Name *</label>
                        <input type="text" wire:model="name" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500" required>
                        @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                            <input type="email" wire:model="email" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                            @error('email') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
                            <input type="text" wire:model="phone" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Company</label>
                            <input type="text" wire:model="companyName" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Value ($)</label>
                            <input type="number" step="0.01" wire:model="value" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                        <select wire:model="status" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                            @foreach(\App\Models\Lead::STATUSES as $statusKey => $statusConfig)
                                <option value="{{ $statusKey }}">{{ $statusConfig['label'] }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Assigned To</label>
                        <select wire:model="assignedTo" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                            <option value="">Unassigned</option>
                            @foreach($this->teamMembers as $member)
                                <option value="{{ $member->id }}">{{ $member->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                        <textarea wire:model="notes" rows="3" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"></textarea>
                    </div>

                    <div class="flex justify-between pt-4">
                        <button type="button" wire:click="deleteLead" wire:confirm="Are you sure you want to delete this lead?" class="px-4 py-2 text-red-600 hover:bg-red-50 rounded-lg transition">
                            Delete
                        </button>
                        <div class="flex gap-3">
                            <button type="button" wire:click="closeEditModal" class="px-4 py-2 text-gray-700 hover:bg-gray-100 rounded-lg transition">
                                Cancel
                            </button>
                            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                                Save Changes
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>

@script
<script>
    Alpine.data('leadKanban', () => ({
        draggingLeadId: null,
        draggingFromStatus: null,
        draggingFromPosition: null,

        onDragStart(event, leadId, status, position) {
            this.draggingLeadId = leadId;
            this.draggingFromStatus = status;
            this.draggingFromPosition = position;
            event.dataTransfer.effectAllowed = 'move';
            event.dataTransfer.setData('text/plain', leadId);
            event.target.classList.add('opacity-50');
        },

        onDragEnd(event) {
            event.target.classList.remove('opacity-50');
            this.draggingLeadId = null;
            this.draggingFromStatus = null;
            this.draggingFromPosition = null;
            
            // Hide all placeholders
            document.querySelectorAll('[x-ref^="placeholder-"]').forEach(el => {
                el.classList.add('hidden');
            });
        },

        onDragOver(event, status) {
            event.preventDefault();
            event.dataTransfer.dropEffect = 'move';
            
            const column = event.currentTarget;
            const placeholder = this.$refs['placeholder-' + status];
            
            if (placeholder) {
                placeholder.classList.remove('hidden');
                
                // Find the card being hovered over
                const cards = [...column.querySelectorAll('[data-lead-id]')];
                const mouseY = event.clientY;
                
                let insertBefore = null;
                for (const card of cards) {
                    const rect = card.getBoundingClientRect();
                    const cardMiddle = rect.top + rect.height / 2;
                    if (mouseY < cardMiddle) {
                        insertBefore = card;
                        break;
                    }
                }
                
                if (insertBefore) {
                    column.insertBefore(placeholder, insertBefore);
                } else {
                    column.appendChild(placeholder);
                }
            }
        },

        onDragLeave(event) {
            // Only hide if leaving the column entirely
            if (!event.currentTarget.contains(event.relatedTarget)) {
                const status = event.currentTarget.dataset.status;
                const placeholder = this.$refs['placeholder-' + status];
                if (placeholder) {
                    placeholder.classList.add('hidden');
                }
            }
        },

        onDrop(event, newStatus) {
            event.preventDefault();
            
            const placeholder = this.$refs['placeholder-' + newStatus];
            const column = event.currentTarget;
            
            // Calculate new position based on placeholder location
            const cards = [...column.querySelectorAll('[data-lead-id]')];
            let newPosition = 0;
            
            for (let i = 0; i < cards.length; i++) {
                if (placeholder && cards[i].compareDocumentPosition(placeholder) & Node.DOCUMENT_POSITION_FOLLOWING) {
                    newPosition = i;
                    break;
                }
                newPosition = i + 1;
            }

            // Hide placeholder
            if (placeholder) {
                placeholder.classList.add('hidden');
            }

            // Dispatch Livewire event
            if (this.draggingLeadId) {
                $wire.dispatch('lead-moved', { 
                    leadId: this.draggingLeadId, 
                    newStatus: newStatus, 
                    newPosition: newPosition 
                });
            }
        }
    }));
</script>
@endscript
