<?php

namespace App\Livewire\Accounting;

use App\Models\Accounting\FiscalYear;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class FiscalYears extends Component
{
    public array $fiscalYears = [];
    public bool $showModal = false;
    public bool $isEditing = false;
    public ?int $editingFiscalYearId = null;

    // Form fields
    public string $name = '';
    public string $startDate = '';
    public string $endDate = '';
    public string $status = FiscalYear::STATUS_OPEN;

    public function mount(): void
    {
        $this->loadFiscalYears();
    }

    public function loadFiscalYears(): void
    {
        $companyId = session('current_owned_company_id');
        if (!$companyId) {
            $this->fiscalYears = [];
            return;
        }

        $this->fiscalYears = FiscalYear::where('owned_company_id', $companyId)
            ->withCount('journalEntries')
            ->orderBy('start_date', 'desc')
            ->get()
            ->toArray();
    }

    public function openCreateModal(): void
    {
        if (!Auth::user()->canAdminAccounting()) {
            return;
        }

        $this->resetForm();

        // Suggest next fiscal year dates
        $companyId = session('current_owned_company_id');
        $lastFiscalYear = FiscalYear::where('owned_company_id', $companyId)
            ->orderBy('end_date', 'desc')
            ->first();

        if ($lastFiscalYear) {
            $this->startDate = $lastFiscalYear->end_date->addDay()->toDateString();
            $this->endDate = $lastFiscalYear->end_date->addYear()->toDateString();
            $yearNumber = (int) substr($lastFiscalYear->name, -4) + 1;
            $this->name = 'FY ' . $yearNumber;
        } else {
            $this->startDate = now()->startOfYear()->toDateString();
            $this->endDate = now()->endOfYear()->toDateString();
            $this->name = 'FY ' . now()->year;
        }

        $this->showModal = true;
    }

    public function openEditModal(int $fiscalYearId): void
    {
        if (!Auth::user()->canAdminAccounting()) {
            return;
        }

        $fiscalYear = FiscalYear::find($fiscalYearId);
        if (!$fiscalYear) return;

        $this->isEditing = true;
        $this->editingFiscalYearId = $fiscalYearId;
        $this->name = $fiscalYear->name;
        $this->startDate = $fiscalYear->start_date->toDateString();
        $this->endDate = $fiscalYear->end_date->toDateString();
        $this->status = $fiscalYear->status;
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
        $this->editingFiscalYearId = null;
        $this->name = '';
        $this->startDate = '';
        $this->endDate = '';
        $this->status = FiscalYear::STATUS_OPEN;
    }

    public function saveFiscalYear(): void
    {
        if (!Auth::user()->canAdminAccounting()) {
            return;
        }

        $this->validate([
            'name' => 'required|string|max:50',
            'startDate' => 'required|date',
            'endDate' => 'required|date|after:startDate',
        ]);

        $companyId = session('current_owned_company_id');
        if (!$companyId) return;

        $data = [
            'owned_company_id' => $companyId,
            'name' => $this->name,
            'start_date' => $this->startDate,
            'end_date' => $this->endDate,
            'status' => $this->status,
        ];

        if ($this->isEditing && $this->editingFiscalYearId) {
            $fiscalYear = FiscalYear::find($this->editingFiscalYearId);
            if ($fiscalYear) {
                $fiscalYear->update($data);
            }
        } else {
            $fiscalYear = FiscalYear::create($data);
            // Generate periods for new fiscal year
            $fiscalYear->generatePeriods();
        }

        $this->closeModal();
        $this->loadFiscalYears();
    }

    public function closeFiscalYear(int $fiscalYearId): void
    {
        if (!Auth::user()->canClosePeriods()) {
            return;
        }

        $fiscalYear = FiscalYear::find($fiscalYearId);
        if ($fiscalYear && $fiscalYear->isOpen()) {
            $fiscalYear->update(['status' => FiscalYear::STATUS_CLOSED]);
            // Also close all periods
            $fiscalYear->periods()->update(['status' => 'closed']);
            $this->loadFiscalYears();
        }
    }

    public function lockFiscalYear(int $fiscalYearId): void
    {
        if (!Auth::user()->canAdminAccounting()) {
            return;
        }

        $fiscalYear = FiscalYear::find($fiscalYearId);
        if ($fiscalYear && $fiscalYear->isClosed()) {
            $fiscalYear->update(['status' => FiscalYear::STATUS_LOCKED]);
            $this->loadFiscalYears();
        }
    }

    public function reopenFiscalYear(int $fiscalYearId): void
    {
        if (!Auth::user()->canAdminAccounting()) {
            return;
        }

        $fiscalYear = FiscalYear::find($fiscalYearId);
        if ($fiscalYear && ($fiscalYear->isClosed() || $fiscalYear->isLocked())) {
            $fiscalYear->update(['status' => FiscalYear::STATUS_OPEN]);
            $fiscalYear->periods()->update(['status' => 'open']);
            $this->loadFiscalYears();
        }
    }

    public function deleteFiscalYear(int $fiscalYearId): void
    {
        if (!Auth::user()->canAdminAccounting()) {
            return;
        }

        $fiscalYear = FiscalYear::withCount('journalEntries')->find($fiscalYearId);
        if ($fiscalYear && $fiscalYear->journal_entries_count === 0) {
            $fiscalYear->periods()->delete();
            $fiscalYear->delete();
            $this->loadFiscalYears();
        }
    }

    public function getCanAdminProperty(): bool
    {
        return Auth::user()->canAdminAccounting();
    }

    public function getCanCloseProperty(): bool
    {
        return Auth::user()->canClosePeriods();
    }

    public function render()
    {
        return view('livewire.accounting.fiscal-years', [
            'statuses' => FiscalYear::STATUSES,
            'canAdmin' => $this->canAdmin,
            'canClose' => $this->canClose,
        ]);
    }
}
