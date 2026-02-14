<?php

namespace App\Services\Accounting;

use App\Models\Accounting\TaxGroup;
use App\Models\Accounting\TaxRate;

class TaxCalculator
{
    /**
     * Calculate tax for a single amount using a specific tax rate
     */
    public static function calculate(float $amount, TaxRate $taxRate): array
    {
        $taxAmount = $taxRate->calculateTax($amount);

        return [
            'subtotal' => $amount,
            'tax_rate_id' => $taxRate->id,
            'tax_rate' => (float) $taxRate->rate,
            'tax_amount' => round($taxAmount, 2),
            'total' => round($amount + $taxAmount, 2),
        ];
    }

    /**
     * Calculate tax for a single amount using a tax rate ID
     */
    public static function calculateById(float $amount, ?int $taxRateId): array
    {
        if (!$taxRateId) {
            return [
                'subtotal' => $amount,
                'tax_rate_id' => null,
                'tax_rate' => 0,
                'tax_amount' => 0,
                'total' => $amount,
            ];
        }

        $taxRate = TaxRate::find($taxRateId);
        if (!$taxRate) {
            return [
                'subtotal' => $amount,
                'tax_rate_id' => null,
                'tax_rate' => 0,
                'tax_amount' => 0,
                'total' => $amount,
            ];
        }

        return static::calculate($amount, $taxRate);
    }

    /**
     * Calculate tax using a tax group (multiple rates)
     */
    public static function calculateWithGroup(float $amount, TaxGroup $taxGroup): array
    {
        $taxes = $taxGroup->calculateTax($amount);
        $totalTax = array_sum(array_column($taxes, 'amount'));

        return [
            'subtotal' => $amount,
            'taxes' => $taxes,
            'tax_total' => round($totalTax, 2),
            'total' => round($amount + $totalTax, 2),
        ];
    }

    /**
     * Calculate line item with quantity and unit price
     */
    public static function calculateLineItem(
        float $quantity,
        float $unitPrice,
        ?int $taxRateId = null
    ): array {
        $subtotal = round($quantity * $unitPrice, 2);
        $result = static::calculateById($subtotal, $taxRateId);

        return array_merge($result, [
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
        ]);
    }

    /**
     * Calculate totals for multiple line items
     */
    public static function calculateDocumentTotals(array $lineItems): array
    {
        $subtotal = 0;
        $taxTotal = 0;

        foreach ($lineItems as $item) {
            $subtotal += $item['subtotal'] ?? 0;
            $taxTotal += $item['tax_amount'] ?? 0;
        }

        return [
            'subtotal' => round($subtotal, 2),
            'tax_total' => round($taxTotal, 2),
            'total' => round($subtotal + $taxTotal, 2),
        ];
    }

    /**
     * Reverse calculate: Get pre-tax amount from tax-inclusive amount
     */
    public static function reverseCalculate(float $totalAmount, TaxRate $taxRate): array
    {
        if ($taxRate->type === TaxRate::TYPE_FIXED) {
            $subtotal = $totalAmount - $taxRate->rate;
            $taxAmount = (float) $taxRate->rate;
        } else {
            $rate = (float) $taxRate->rate / 100;
            $subtotal = $totalAmount / (1 + $rate);
            $taxAmount = $totalAmount - $subtotal;
        }

        return [
            'subtotal' => round($subtotal, 2),
            'tax_rate_id' => $taxRate->id,
            'tax_rate' => (float) $taxRate->rate,
            'tax_amount' => round($taxAmount, 2),
            'total' => $totalAmount,
        ];
    }

    /**
     * Get tax breakdown by rate for a document
     */
    public static function getTaxBreakdown(array $lineItems): array
    {
        $breakdown = [];

        foreach ($lineItems as $item) {
            $taxRateId = $item['tax_rate_id'] ?? null;
            if (!$taxRateId) continue;

            $key = "tax_{$taxRateId}";
            if (!isset($breakdown[$key])) {
                $taxRate = TaxRate::find($taxRateId);
                $breakdown[$key] = [
                    'tax_rate_id' => $taxRateId,
                    'tax_rate' => $taxRate?->rate ?? 0,
                    'tax_name' => $taxRate?->name ?? 'Unknown',
                    'taxable_amount' => 0,
                    'tax_amount' => 0,
                ];
            }

            $breakdown[$key]['taxable_amount'] += $item['subtotal'] ?? 0;
            $breakdown[$key]['tax_amount'] += $item['tax_amount'] ?? 0;
        }

        // Round values
        foreach ($breakdown as &$item) {
            $item['taxable_amount'] = round($item['taxable_amount'], 2);
            $item['tax_amount'] = round($item['tax_amount'], 2);
        }

        return array_values($breakdown);
    }
}
