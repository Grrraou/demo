<?php

namespace App\Livewire\Accounting;

use App\Models\Accounting\FiscalYear;
use App\Services\Accounting\TrialBalanceService;
use Livewire\Component;

class TrialBalance extends Component
{
    public string $asOfDate = '';
    public ?int $fiscalYearId = null;
    public array $report = [];

    public function mount(): void
    {
        $this->asOfDate = now()->toDateString();
        $this->loadReport();
    }

    public function loadReport(): void
    {
        $companyId = session('current_owned_company_id');
        if (!$companyId) {
            $this->report = [];
            return;
        }

        $this->report = TrialBalanceService::generate(
            $companyId,
            $this->asOfDate,
            $this->fiscalYearId
        );
    }

    public function updatedAsOfDate(): void
    {
        $this->loadReport();
    }

    public function updatedFiscalYearId(): void
    {
        $this->loadReport();
    }

    public function getFiscalYearsProperty()
    {
        $companyId = session('current_owned_company_id');
        if (!$companyId) return collect();

        return FiscalYear::where('owned_company_id', $companyId)
            ->orderBy('start_date', 'desc')
            ->get();
    }

    public function render()
    {
        return view('livewire.accounting.trial-balance');
    }
}
