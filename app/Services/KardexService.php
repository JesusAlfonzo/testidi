<?php

namespace App\Services;

use App\Models\Product;
use App\Models\StockIn;
use App\Models\InventoryRequest; // Asegúrate que este sea el nombre correcto de tu modelo
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class KardexService
{
    /**
     * Genera el reporte de movimientos (Kardex) para un producto específico.
     *
     * @param Product $product
     * @param float|null $initialStock Saldo inicial override (si es null usa $product->stock)
     * @return array
     */
    public function generateKardex(Product $product, ?float $initialStock = null)
    {
        $initialBalance = $initialStock ?? $product->stock;

        // 1. OBTENER ENTRADAS (STOCK-IN)
        $entradas = StockIn::where('product_id', $product->id)
            ->with(['supplier', 'user'])
            ->get()
            ->map(function ($entrada) {
                return [
                    'date'      => $entrada->entry_date ?? $entrada->created_at,
                    'type'      => 'ENTRADA',
                    'quantity'  => $entrada->quantity,
                    'unit_price' => $entrada->unit_cost,
                    'reference' => 'ENT-' . $entrada->id,
                    'user'      => $entrada->user->name ?? 'Sistema',
                    'notes'     => 'Prov: ' . ($entrada->supplier->name ?? 'N/A') . '. ' . $entrada->reason,
                    'timestamp' => $entrada->created_at->timestamp,
                ];
            });

        // 2. OBTENER SALIDAS (SOLICITUDES APROBADAS)
        $salidas = InventoryRequest::with(['requester', 'approver', 'items.kit'])
            ->where('status', 'Approved')
            ->get()
            ->flatMap(function ($solicitud) use ($product) {
                $movimientosDeSolicitud = [];

                foreach ($solicitud->items as $item) {
                    // CASO A: El ítem es el producto directo
                    if ($item->item_type === 'product' && $item->product_id === $product->id) {
                        $movimientosDeSolicitud[] = [
                            'date'      => $solicitud->processed_at ?? $solicitud->updated_at,
                            'type'      => 'SALIDA',
                            'quantity'  => $item->quantity_requested * -1,
                            'unit_price' => $item->unit_price_at_request,
                            'reference' => 'REQ-' . $solicitud->id,
                            'user'      => $solicitud->approver->name ?? 'Sistema',
                            'notes'     => 'Solicitud Directa. ' . Str::limit($solicitud->justification, 30),
                            'timestamp' => $solicitud->processed_at?->timestamp ?? 0,
                        ];
                    }

                    // CASO B: El ítem es un KIT que contiene el producto
                    if ($item->item_type === 'kit' && $item->kit) {
                        $componente = $item->kit->components->firstWhere('id', $product->id);

                        if ($componente) {
                            $consumoReal = $item->quantity_requested * $componente->pivot->quantity_required;

                            $movimientosDeSolicitud[] = [
                                'date'      => $solicitud->processed_at ?? $solicitud->updated_at,
                                'type'      => 'SALIDA (KIT)',
                                'quantity'  => $consumoReal * -1,
                                'unit_price' => $componente->cost,
                                'reference' => 'REQ-' . $solicitud->id,
                                'user'      => $solicitud->approver->name ?? 'Sistema',
                                'notes'     => "Parte del Kit: {$item->kit->name}. " . Str::limit($solicitud->justification, 20),
                                'timestamp' => $solicitud->processed_at?->timestamp ?? 0,
                            ];
                        }
                    }
                }

                return $movimientosDeSolicitud;
            });

        // 3. FUSIONAR, ORDENAR Y CALCULAR SALDOS
        $movimientos = $entradas->concat($salidas)->sortBy('timestamp')->values();

        if ($movimientos->isEmpty()) {
            return [[
                'date' => now(),
                'type' => 'INICIO',
                'quantity' => 0,
                'unit_price' => 0,
                'reference' => '---',
                'user' => 'Sistema',
                'notes' => 'Sin movimientos registrados',
                'balance' => $initialBalance,
            ]];
        }

        $saldoInicial = $initialBalance - $movimientos->sum('quantity');
        $kardex = [[
            'date' => $movimientos->first()['date']->copy()->subSecond(),
            'type' => 'INICIO',
            'quantity' => 0,
            'unit_price' => 0,
            'reference' => 'SALDO INICIAL',
            'user' => 'Sistema',
            'notes' => 'Saldo calculado antes de movimientos',
            'balance' => $saldoInicial,
        ]];

        $saldoAcumulado = $saldoInicial;

        foreach ($movimientos as $movimiento) {
            $saldoAcumulado += $movimiento['quantity'];
            $movimiento['balance'] = $saldoAcumulado;
            $kardex[] = $movimiento;
        }

        return $kardex;
    }
}
