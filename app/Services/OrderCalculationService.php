<?php
namespace App\Services;

class OrderCalculationService
{
    private float $ivaRate = 0.16;

    public function calculate(array $items, string $currency, float $exchangeRate = 1, bool $ivaExempt = false): array
    {
        $isBs = $currency === 'Bs';
        $subtotal = 0;
        $subtotalBs = 0;
        $taxableSubtotal = 0;
        $taxableSubtotalBs = 0;

        foreach ($items as $item) {
            $itemTotal = $item['quantity'] * $item['unit_cost'];
            $subtotal += $itemTotal;
            
            $equivalentBs = $isBs 
                ? $item['unit_cost'] 
                : $item['unit_cost'] * $exchangeRate;
            
            $itemTotalBs = $equivalentBs * $item['quantity'];
            $subtotalBs += $itemTotalBs;

            // Determinar si el ítem es exento de IVA de forma individual o general
            $itemExempt = $ivaExempt 
                || (isset($item['is_exempt']) && ($item['is_exempt'] == 1 || $item['is_exempt'] === true))
                || (isset($item['tax_status']) && $item['tax_status'] === 'exento');

            if (!$itemExempt) {
                $taxableSubtotal += $itemTotal;
                $taxableSubtotalBs += $itemTotalBs;
            }
        }

        $taxAmount = $taxableSubtotal * $this->ivaRate;
        $taxAmountBs = $taxableSubtotalBs * $this->ivaRate;
        
        $total = $subtotal + $taxAmount;
        $totalBs = $subtotalBs + $taxAmountBs;

        return [
            'subtotal' => $subtotal,
            'tax_amount' => $taxAmount,
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
