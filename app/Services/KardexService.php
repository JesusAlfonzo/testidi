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
     * @return array
     */
    public function generateKardex(Product $product)
    {
        // 1. OBTENER ENTRADAS (STOCK-IN)
        // Buscamos todas las entradas registradas para este producto
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
        // Aquí viene la magia: debemos encontrar salidas directas DEL PRODUCTO
        // O salidas de KITS que contengan este producto.

        $salidas = InventoryRequest::with(['requester', 'approver', 'items.kit.components'])
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
                            'quantity'  => $item->quantity_requested * -1, // Negativo para restar
                            'unit_price' => $item->unit_price_at_request,
                            'reference' => 'REQ-' . $solicitud->id,
                            'user'      => $solicitud->approver->name ?? 'Sistema',
                            'notes'     => 'Solicitud Directa. ' . Str::limit($solicitud->justification, 30),
                            'timestamp' => $solicitud->processed_at->timestamp ?? 0,
                        ];
                    }

                    // CASO B: El ítem es un KIT que contiene el producto
                    if ($item->item_type === 'kit' && $item->kit) {
                        // Buscamos si nuestro producto está dentro de los componentes de este kit
                        $componente = $item->kit->components->firstWhere('id', $product->id);

                        if ($componente) {
                            // Calculamos cuánto se consumió realmente:
                            // Cantidad Kits Solicitados * Cantidad del Componente por Kit
                            $consumoReal = $item->quantity_requested * $componente->pivot->quantity_required;

                            $movimientosDeSolicitud[] = [
                                'date'      => $solicitud->processed_at ?? $solicitud->updated_at,
                                'type'      => 'SALIDA (KIT)',
                                'quantity'  => $consumoReal * -1, // Negativo
                                'unit_price' => $componente->cost, // Usamos el costo del componente
                                'reference' => 'REQ-' . $solicitud->id,
                                'user'      => $solicitud->approver->name ?? 'Sistema',
                                'notes'     => "Parte del Kit: {$item->kit->name}. " . Str::limit($solicitud->justification, 20),
                                'timestamp' => $solicitud->processed_at->timestamp ?? 0,
                            ];
                        }
                    }
                }

                return $movimientosDeSolicitud;
            });

        // 3. FUSIONAR, ORDENAR Y CALCULAR SALDOS
        $movimientos = $entradas->concat($salidas)->sortBy('timestamp')->values();

        $saldoAcumulado = 0;
        $kardex = [];

        // Si no hay movimientos, mostramos el estado inicial
        if ($movimientos->isEmpty()) {
             $kardex[] = [
                 'date' => now(),
                 'type' => 'INICIO',
                 'quantity' => 0,
                 'unit_price' => 0,
                 'reference' => '---',
                 'user' => 'Sistema',
                 'notes' => 'Sin movimientos registrados',
                 'balance' => $product->stock,
             ];
             return $kardex;
        }

        // Calcular línea por línea
        foreach ($movimientos as $movimiento) {
            $saldoAcumulado += $movimiento['quantity'];
            $movimiento['balance'] = $saldoAcumulado;
            $kardex[] = $movimiento;
        }

        return $kardex;
    }
}
