<?php

namespace App\Livewire\Accounting;

use App\Models\Accounting\Journal;
use App\Models\Accounting\JournalEntry;
use App\Services\Accounting\JournalService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class JournalEntries extends Component
{
    use WithPagination;

    public string $search = '';
    public string $filterStatus = '';
    public string $filterJournal = '';
    public ?string $filterDateFrom = null;
    public ?string $filterDateTo = null;

    public ?int $viewingEntryId = null;
    public bool $showViewModal = false;

    protected $queryString = [
        'search' => ['except' => ''],
        'filterStatus' => ['except' => ''],
        'filterJournal' => ['except' => ''],
    ];

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedFilterStatus(): void
    {
        $this->resetPage();
    }

    public function updatedFilterJournal(): void
    {
        $this->resetPage();
    }

    public function viewEntry(int $entryId): void
    {
        $this->viewingEntryId = $entryId;
        $this->showViewModal = true;
    }

    public function closeViewModal(): void
    {
        $this->showViewModal = false;
        $this->viewingEntryId = null;
    }

    public function postEntry(int $entryId): void
    {
        if (!Auth::user()->canEditAccounting()) {
            return;
        }

        $entry = JournalEntry::find($entryId);
        if ($entry && $entry->canBePosted()) {
            JournalService::postEntry($entry, Auth::id());
        }
    }

    public function reverseEntry(int $entryId): void
    {
        if (!Auth::user()->canEditAccounting()) {
            return;
        }

        $entry = JournalEntry::find($entryId);
        if ($entry && $entry->canBeReversed()) {
            JournalService::reverseEntry($entry, Auth::id());
        }
    }

    public function deleteEntry(int $entryId): void
    {
        if (!Auth::user()->canEditAccounting()) {
            return;
        }

        $entry = JournalEntry::find($entryId);
        if ($entry && $entry->canBeEdited()) {
            JournalService::deleteEntry($entry);
        }
    }

    public function getJournalsProperty()
    {
        $companyId = session('current_owned_company_id');
        if (!$companyId) return collect();

        return Journal::where('owned_company_id', $companyId)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
    }

    public function getViewingEntryProperty()
    {
        if (!$this->viewingEntryId) return null;

        return JournalEntry::with(['lines.account', 'journal', 'fiscalYear', 'createdBy', 'postedBy'])
            ->find($this->viewingEntryId);
    }

    public function getCanEditProperty(): bool
    {
        return Auth::user()->canEditAccounting();
    }

    public function render()
    {
        $companyId = session('current_owned_company_id');
        
        $query = JournalEntry::where('owned_company_id', $companyId)
            ->with(['journal', 'createdBy', 'lines'])
            ->orderBy('entry_date', 'desc')
            ->orderBy('created_at', 'desc');

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('entry_number', 'ilike', '%' . $this->search . '%')
                    ->orWhere('reference', 'ilike', '%' . $this->search . '%')
                    ->orWhere('description', 'ilike', '%' . $this->search . '%');
            });
        }

        if ($this->filterStatus) {
            $query->where('status', $this->filterStatus);
        }

        if ($this->filterJournal) {
            $query->where('journal_id', $this->filterJournal);
        }

        if ($this->filterDateFrom) {
            $query->where('entry_date', '>=', $this->filterDateFrom);
        }

        if ($this->filterDateTo) {
            $query->where('entry_date', '<=', $this->filterDateTo);
        }

        return view('livewire.accounting.journal-entries', [
            'entries' => $query->paginate(20),
            'statuses' => JournalEntry::STATUSES,
            'canEdit' => $this->canEdit,
        ]);
    }
}
