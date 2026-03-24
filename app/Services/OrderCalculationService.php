<?php
namespace App\Services;

class OrderCalculationService
{
    private float $ivaRate = 0.16;

    public function calculate(array $items, string $currency, float $exchangeRate = 1): array
    {
        $isBs = $currency === 'Bs';
        $subtotal = 0;
        $subtotalBs = 0;

        foreach ($items as $item) {
            $itemTotal = $item['quantity'] * $item['unit_cost'];
            $subtotal += $itemTotal;
            
            $equivalentBs = $isBs 
                ? $item['unit_cost'] 
                : $item['unit_cost'] * $exchangeRate;
            
            $subtotalBs += $equivalentBs * $item['quantity'];
        }

        $taxAmountBs = $subtotalBs * $this->ivaRate;
        $totalBs = $subtotalBs + $taxAmountBs;
        $total = $subtotal;

        return [
            'subtotal' => $subtotal,
            'tax_amount' => 0,
            'total' => $total,
            'subtotal_bs' => $subtotalBs,
            'tax_amount_bs' => $taxAmountBs,
            'total_bs' => $totalBs,
            'exchange_rate' => $isBs ? 1 : $exchangeRate,
        ];
    }

    public function calculateItemEquivalentBs(float $unitCost, string $currency, float $exchangeRate): float
    {
        $isBs = $currency === 'Bs';
        return $isBs ? $unitCost : $unitCost * $exchangeRate;
    }
}
