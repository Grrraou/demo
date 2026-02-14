<?php

namespace App\Livewire\Accounting;

use App\Models\Accounting\Account;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class ChartOfAccounts extends Component
{
    public array $accounts = [];
    public bool $showModal = false;
    public bool $isEditing = false;
    public ?int $editingAccountId = null;

    // Form fields
    public ?int $parentId = null;
    public string $code = '';
    public string $name = '';
    public string $type = Account::TYPE_ASSET;
    public ?string $subtype = null;
    public string $description = '';
    public bool $isActive = true;

    // Filters
    public string $filterType = '';
    public string $search = '';

    public function mount(): void
    {
        $this->loadAccounts();
    }

    public function loadAccounts(): void
    {
        $companyId = session('current_owned_company_id');
        if (!$companyId) {
            $this->accounts = [];
            return;
        }

        $query = Account::where('owned_company_id', $companyId)
            ->with('children')
            ->orderBy('code');

        if ($this->filterType) {
            $query->where('type', $this->filterType);
        }

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('code', 'ilike', '%' . $this->search . '%')
                    ->orWhere('name', 'ilike', '%' . $this->search . '%');
            });
        }

        // Get root accounts (no parent) or all if searching
        if (!$this->search) {
            $query->whereNull('parent_id');
        }

        $this->accounts = $query->get()->map(fn($a) => $this->formatAccount($a))->toArray();
    }

    protected function formatAccount(Account $account, int $level = 0): array
    {
        return [
            'id' => $account->id,
            'code' => $account->code,
            'name' => $account->name,
            'type' => $account->type,
            'type_name' => $account->type_name,
            'subtype' => $account->subtype,
            'is_active' => $account->is_active,
            'is_system' => $account->is_system,
            'description' => $account->description,
            'level' => $level,
            'children' => $account->children->map(fn($c) => $this->formatAccount($c, $level + 1))->toArray(),
        ];
    }

    public function updatedSearch(): void
    {
        $this->loadAccounts();
    }

    public function updatedFilterType(): void
    {
        $this->loadAccounts();
    }

    public function openCreateModal(?int $parentId = null): void
    {
        if (!Auth::user()->canEditAccounting()) {
            return;
        }

        $this->resetForm();
        $this->parentId = $parentId;

        if ($parentId) {
            $parent = Account::find($parentId);
            if ($parent) {
                $this->type = $parent->type;
            }
        }

        $this->showModal = true;
    }

    public function openEditModal(int $accountId): void
    {
        if (!Auth::user()->canEditAccounting()) {
            return;
        }

        $account = Account::find($accountId);
        if (!$account) return;

        $this->isEditing = true;
        $this->editingAccountId = $accountId;
        $this->parentId = $account->parent_id;
        $this->code = $account->code;
        $this->name = $account->name;
        $this->type = $account->type;
        $this->subtype = $account->subtype;
        $this->description = $account->description ?? '';
        $this->isActive = $account->is_active;
        $this->showModal = true;
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->resetForm();
    }

    public function resetForm(): void
    {
        $this->isEditing = false;
        $this->editingAccountId = null;
        $this->parentId = null;
        $this->code = '';
        $this->name = '';
        $this->type = Account::TYPE_ASSET;
        $this->subtype = null;
        $this->description = '';
        $this->isActive = true;
    }

    public function saveAccount(): void
    {
        if (!Auth::user()->canEditAccounting()) {
            return;
        }

        $this->validate([
            'code' => 'required|string|max:20',
            'name' => 'required|string|max:255',
            'type' => 'required|string|in:' . implode(',', array_keys(Account::TYPES)),
            'subtype' => 'nullable|string|max:50',
            'description' => 'nullable|string',
        ]);

        $companyId = session('current_owned_company_id');
        if (!$companyId) return;

        $data = [
            'owned_company_id' => $companyId,
            'parent_id' => $this->parentId,
            'code' => $this->code,
            'name' => $this->name,
            'type' => $this->type,
            'subtype' => $this->subtype ?: null,
            'description' => $this->description ?: null,
            'is_active' => $this->isActive,
        ];

        if ($this->isEditing && $this->editingAccountId) {
            $account = Account::find($this->editingAccountId);
            if ($account && !$account->is_system) {
                $account->update($data);
            }
        } else {
            Account::create($data);
        }

        $this->closeModal();
        $this->loadAccounts();
    }

    public function deleteAccount(int $accountId): void
    {
        if (!Auth::user()->canEditAccounting()) {
            return;
        }

        $account = Account::find($accountId);
        if (!$account) return;

        // Don't delete system accounts
        if ($account->is_system) {
            return;
        }

        // Don't delete accounts with journal entries
        if ($account->journalEntryLines()->exists()) {
            session()->flash('error', 'Cannot delete account with journal entries.');
            return;
        }

        // Don't delete accounts with children
        if ($account->children()->exists()) {
            session()->flash('error', 'Cannot delete account with sub-accounts.');
            return;
        }

        $account->delete();
        $this->loadAccounts();
    }

    public function toggleActive(int $accountId): void
    {
        if (!Auth::user()->canEditAccounting()) {
            return;
        }

        $account = Account::find($accountId);
        if ($account) {
            $account->update(['is_active' => !$account->is_active]);
            $this->loadAccounts();
        }
    }

    public function getParentAccountsProperty()
    {
        $companyId = session('current_owned_company_id');
        if (!$companyId) return collect();

        return Account::where('owned_company_id', $companyId)
            ->whereNull('parent_id')
            ->where('is_active', true)
            ->orderBy('code')
            ->get(['id', 'code', 'name', 'type']);
    }

    public function getSubtypesProperty(): array
    {
        return Account::SUBTYPES[$this->type] ?? [];
    }

    public function getCanEditProperty(): bool
    {
        return Auth::user()->canEditAccounting();
    }

    public function render()
    {
        return view('livewire.accounting.chart-of-accounts', [
            'types' => Account::TYPES,
            'canEdit' => $this->canEdit,
        ]);
    }
}
