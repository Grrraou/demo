<?php

namespace App\Livewire\Leads;

use App\Models\Lead;
use App\Models\TeamMember;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;
use Livewire\Component;

class LeadKanban extends Component
{
    public array $leads = [];
    public bool $showCreateModal = false;
    public bool $showEditModal = false;
    public ?int $editingLeadId = null;
    
    // Form fields
    public string $name = '';
    public string $email = '';
    public string $phone = '';
    public string $companyName = '';
    public ?float $value = null;
    public string $notes = '';
    public ?int $assignedTo = null;
    public string $status = Lead::STATUS_NEW;

    // Filters
    public bool $showWonLost = false;

    public function mount(): void
    {
        $this->loadLeads();
    }

    public function loadLeads(): void
    {
        $companyId = session('current_owned_company_id');
        
        if (!$companyId) {
            $this->leads = [];
            return;
        }

        $query = Lead::where('owned_company_id', $companyId)
            ->with(['assignedTo:id,name'])
            ->orderBy('position');

        if (!$this->showWonLost) {
            $query->whereNotIn('status', [Lead::STATUS_WON, Lead::STATUS_LOST]);
        }

        $allLeads = $query->get();

        // Group leads by status
        $this->leads = [];
        foreach (Lead::STATUSES as $status => $config) {
            if (!$this->showWonLost && in_array($status, [Lead::STATUS_WON, Lead::STATUS_LOST])) {
                continue;
            }
            $this->leads[$status] = $allLeads->where('status', $status)->values()->toArray();
        }
    }

    public function toggleWonLost(): void
    {
        $this->showWonLost = !$this->showWonLost;
        $this->loadLeads();
    }

    public function openCreateModal(): void
    {
        if (!Auth::user()->canEditLeads()) {
            return;
        }
        $this->resetForm();
        $this->showCreateModal = true;
    }

    public function closeCreateModal(): void
    {
        $this->showCreateModal = false;
        $this->resetForm();
    }

    public function openEditModal(int $leadId): void
    {
        if (!Auth::user()->canEditLeads()) {
            return;
        }

        $lead = Lead::find($leadId);
        if (!$lead) return;

        $this->editingLeadId = $leadId;
        $this->name = $lead->name;
        $this->email = $lead->email ?? '';
        $this->phone = $lead->phone ?? '';
        $this->companyName = $lead->company_name ?? '';
        $this->value = $lead->value;
        $this->notes = $lead->notes ?? '';
        $this->assignedTo = $lead->assigned_to;
        $this->status = $lead->status;
        $this->showEditModal = true;
    }

    public function closeEditModal(): void
    {
        $this->showEditModal = false;
        $this->editingLeadId = null;
        $this->resetForm();
    }

    public function resetForm(): void
    {
        $this->name = '';
        $this->email = '';
        $this->phone = '';
        $this->companyName = '';
        $this->value = null;
        $this->notes = '';
        $this->assignedTo = null;
        $this->status = Lead::STATUS_NEW;
    }

    public function createLead(): void
    {
        if (!Auth::user()->canEditLeads()) {
            return;
        }

        $this->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'companyName' => 'nullable|string|max:255',
            'value' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        $companyId = session('current_owned_company_id');
        if (!$companyId) return;

        // Get max position for new status
        $maxPosition = Lead::where('owned_company_id', $companyId)
            ->where('status', Lead::STATUS_NEW)
            ->max('position') ?? -1;

        Lead::create([
            'owned_company_id' => $companyId,
            'name' => $this->name,
            'email' => $this->email ?: null,
            'phone' => $this->phone ?: null,
            'company_name' => $this->companyName ?: null,
            'value' => $this->value,
            'notes' => $this->notes ?: null,
            'assigned_to' => $this->assignedTo,
            'created_by' => Auth::id(),
            'status' => Lead::STATUS_NEW,
            'position' => $maxPosition + 1,
        ]);

        $this->closeCreateModal();
        $this->loadLeads();
    }

    public function updateLead(): void
    {
        if (!Auth::user()->canEditLeads()) {
            return;
        }
        if (!$this->editingLeadId) return;

        $this->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'companyName' => 'nullable|string|max:255',
            'value' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
            'status' => 'required|string|in:' . implode(',', array_keys(Lead::STATUSES)),
        ]);

        $lead = Lead::find($this->editingLeadId);
        if (!$lead) return;

        $lead->update([
            'name' => $this->name,
            'email' => $this->email ?: null,
            'phone' => $this->phone ?: null,
            'company_name' => $this->companyName ?: null,
            'value' => $this->value,
            'notes' => $this->notes ?: null,
            'assigned_to' => $this->assignedTo,
            'status' => $this->status,
        ]);

        $this->closeEditModal();
        $this->loadLeads();
    }

    public function deleteLead(): void
    {
        if (!Auth::user()->canEditLeads()) {
            return;
        }
        if (!$this->editingLeadId) return;

        Lead::destroy($this->editingLeadId);
        $this->closeEditModal();
        $this->loadLeads();
    }

    #[On('lead-moved')]
    public function moveLead(int $leadId, string $newStatus, int $newPosition): void
    {
        if (!Auth::user()->canEditLeads()) {
            return;
        }

        $lead = Lead::find($leadId);
        if (!$lead) return;

        $companyId = session('current_owned_company_id');
        if ($lead->owned_company_id != $companyId) return;

        $oldStatus = $lead->status;
        $oldPosition = $lead->position;

        // If moving to different column
        if ($oldStatus !== $newStatus) {
            // Reorder old column
            Lead::where('owned_company_id', $companyId)
                ->where('status', $oldStatus)
                ->where('position', '>', $oldPosition)
                ->decrement('position');

            // Make room in new column
            Lead::where('owned_company_id', $companyId)
                ->where('status', $newStatus)
                ->where('position', '>=', $newPosition)
                ->increment('position');

            $lead->update([
                'status' => $newStatus,
                'position' => $newPosition,
            ]);
        } else {
            // Moving within same column
            if ($newPosition > $oldPosition) {
                Lead::where('owned_company_id', $companyId)
                    ->where('status', $newStatus)
                    ->where('position', '>', $oldPosition)
                    ->where('position', '<=', $newPosition)
                    ->decrement('position');
            } else {
                Lead::where('owned_company_id', $companyId)
                    ->where('status', $newStatus)
                    ->where('position', '>=', $newPosition)
                    ->where('position', '<', $oldPosition)
                    ->increment('position');
            }

            $lead->update(['position' => $newPosition]);
        }

        $this->loadLeads();
    }

    public function getTeamMembersProperty()
    {
        return TeamMember::orderBy('name')->get(['id', 'name']);
    }

    public function getCanEditProperty(): bool
    {
        return Auth::user()->canEditLeads();
    }

    public function render()
    {
        return view('livewire.leads.lead-kanban', [
            'statuses' => $this->showWonLost ? Lead::STATUSES : Lead::getActiveStatuses(),
            'canEdit' => $this->canEdit,
        ]);
    }
}
