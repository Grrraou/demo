<?php

namespace App\Livewire\Accounting;

use App\Models\Accounting\Account;
use App\Models\Accounting\TaxRate;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class TaxRates extends Component
{
    public array $taxRates = [];
    public bool $showModal = false;
    public bool $isEditing = false;
    public ?int $editingTaxRateId = null;

    // Form fields
    public string $name = '';
    public string $code = '';
    public float $rate = 0;
    public string $type = TaxRate::TYPE_PERCENTAGE;
    public bool $isCompound = false;
    public bool $isRecoverable = true;
    public bool $isActive = true;
    public ?int $accountId = null;

    public function mount(): void
    {
        $this->loadTaxRates();
    }

    public function loadTaxRates(): void
    {
        $companyId = session('current_owned_company_id');
        if (!$companyId) {
            $this->taxRates = [];
            return;
        }

        $this->taxRates = TaxRate::where('owned_company_id', $companyId)
            ->with('account:id,code,name')
            ->orderBy('name')
            ->get()
            ->toArray();
    }

    public function openCreateModal(): void
    {
        if (!Auth::user()->canEditAccounting()) {
            return;
        }

        $this->resetForm();
        $this->showModal = true;
    }

    public function openEditModal(int $taxRateId): void
    {
        if (!Auth::user()->canEditAccounting()) {
            return;
        }

        $taxRate = TaxRate::find($taxRateId);
        if (!$taxRate) return;

        $this->isEditing = true;
        $this->editingTaxRateId = $taxRateId;
        $this->name = $taxRate->name;
        $this->code = $taxRate->code;
        $this->rate = (float) $taxRate->rate;
        $this->type = $taxRate->type;
        $this->isCompound = $taxRate->is_compound;
        $this->isRecoverable = $taxRate->is_recoverable;
        $this->isActive = $taxRate->is_active;
        $this->accountId = $taxRate->account_id;
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
        $this->editingTaxRateId = null;
        $this->name = '';
        $this->code = '';
        $this->rate = 0;
        $this->type = TaxRate::TYPE_PERCENTAGE;
        $this->isCompound = false;
        $this->isRecoverable = true;
        $this->isActive = true;
        $this->accountId = null;
    }

    public function saveTaxRate(): void
    {
        if (!Auth::user()->canEditAccounting()) {
            return;
        }

        $this->validate([
            'name' => 'required|string|max:100',
            'code' => 'required|string|max:20',
            'rate' => 'required|numeric|min:0|max:100',
            'type' => 'required|string|in:' . implode(',', array_keys(TaxRate::TYPES)),
        ]);

        $companyId = session('current_owned_company_id');
        if (!$companyId) return;

        $data = [
            'owned_company_id' => $companyId,
            'name' => $this->name,
            'code' => $this->code,
            'rate' => $this->rate,
            'type' => $this->type,
            'is_compound' => $this->isCompound,
            'is_recoverable' => $this->isRecoverable,
            'is_active' => $this->isActive,
            'account_id' => $this->accountId,
        ];

        if ($this->isEditing && $this->editingTaxRateId) {
            $taxRate = TaxRate::find($this->editingTaxRateId);
            if ($taxRate) {
                $taxRate->update($data);
            }
        } else {
            TaxRate::create($data);
        }

        $this->closeModal();
        $this->loadTaxRates();
    }

    public function deleteTaxRate(int $taxRateId): void
    {
        if (!Auth::user()->canEditAccounting()) {
            return;
        }

        $taxRate = TaxRate::find($taxRateId);
        if ($taxRate) {
            $taxRate->delete();
            $this->loadTaxRates();
        }
    }

    public function toggleActive(int $taxRateId): void
    {
        if (!Auth::user()->canEditAccounting()) {
            return;
        }

        $taxRate = TaxRate::find($taxRateId);
        if ($taxRate) {
            $taxRate->update(['is_active' => !$taxRate->is_active]);
            $this->loadTaxRates();
        }
    }

    public function getTaxAccountsProperty()
    {
        $companyId = session('current_owned_company_id');
        if (!$companyId) return collect();

        return Account::where('owned_company_id', $companyId)
            ->where('type', Account::TYPE_LIABILITY)
            ->where('subtype', 'tax_payable')
            ->where('is_active', true)
            ->orderBy('code')
            ->get(['id', 'code', 'name']);
    }

    public function getCanEditProperty(): bool
    {
        return Auth::user()->canEditAccounting();
    }

    public function render()
    {
        return view('livewire.accounting.tax-rates', [
            'types' => TaxRate::TYPES,
            'canEdit' => $this->canEdit,
        ]);
    }
}
