<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductBatch;
use App\Events\StockUpdated;
use Illuminate\Support\Facades\DB;

class ProductFractionService
{
    protected CacheService $cacheService;

    public function __construct(CacheService $cacheService)
    {
        $this->cacheService = $cacheService;
    }

    /**
     * Desempaca/fracciona un producto empaque mayor a unidades sueltas.
     *
     * @param Product $parent
     * @param int $quantityToUnpack
     * @return array [parent_stock, child_stock]
     * @throws \Exception
     */
    public function unpack(Product $parent, int $quantityToUnpack): array
    {
        // 1. Validaciones previas
        if ($quantityToUnpack <= 0) {
            throw new \InvalidArgumentException("La cantidad a desempacar debe ser mayor a cero.");
        }

        if (!$parent->is_active) {
            throw new \Exception("El producto '{$parent->name}' está inactivo y no se puede desempacar.");
        }

        // Obtener la fracción configurada
        $fraction = $parent->childFraction;
        if (!$fraction) {
            throw new \Exception("El producto '{$parent->name}' no tiene configurado un factor de fraccionamiento.");
        }

        $child = $fraction->childProduct;
        if (!$child) {
            throw new \Exception("No se encontró el producto de unidad individual asociado.");
        }

        if (!$child->is_active) {
            throw new \Exception("El producto individual '{$child->name}' está inactivo y no se puede recibir stock.");
        }

        if ($parent->stock < $quantityToUnpack) {
            throw new \Exception("Stock insuficiente del empaque mayor '{$parent->name}'. Stock actual: {$parent->stock}, requerido: {$quantityToUnpack}.");
        }

        $conversionFactor = $fraction->conversion_factor;
        $totalChildQtyToAdd = $quantityToUnpack * $conversionFactor;

        // 2. Ejecutar transacción de base de datos
        DB::beginTransaction();
        try {
            // Consumir el stock del producto padre (maneja FIFO internamente en consumeStock)
            $consumedBatches = $parent->consumeStock($quantityToUnpack, "Fraccionamiento de producto");

            // Mapear lotes si el padre maneja lotes
            if ($parent->shouldUseFifo()) {
                foreach ($consumedBatches as $consumed) {
                    $parentBatch = ProductBatch::find($consumed['batch_id']);
                    if (!$parentBatch) {
                        continue;
                    }

                    $childQtyForThisBatch = $consumed['quantity'] * $conversionFactor;
                    $proratedCost = $parentBatch->unit_cost ? ($parentBatch->unit_cost / $conversionFactor) : 0;
                    $proratedPrice = $parentBatch->price ? ($parentBatch->price / $conversionFactor) : 0;

                    // Buscar si existe un lote equivalente en el hijo
                    $childBatch = ProductBatch::where('product_id', $child->id)
                        ->where('batch_number', $parentBatch->batch_number)
                        ->whereNull('serial_number')
                        ->where('invoice_number', $parentBatch->invoice_number)
                        ->whereDate('expiration_date', $parentBatch->expiration_date)
                        ->first();

                    if ($childBatch) {
                        $childBatch->quantity += $childQtyForThisBatch;
                        $childBatch->save();
                    } else {
                        ProductBatch::create([
                            'product_id' => $child->id,
                            'stock_in_item_id' => $parentBatch->stock_in_item_id,
                            'invoice_number' => $parentBatch->invoice_number,
                            'batch_number' => $parentBatch->batch_number,
                            'expiration_date' => $parentBatch->expiration_date,
                            'serial_number' => null,
                            'quantity' => $childQtyForThisBatch,
                            'unit_cost' => $proratedCost,
                            'price' => $proratedPrice,
                            'currency' => $parentBatch->currency,
                            'tax_status' => $parentBatch->tax_status,
                        ]);
                    }
                }
            }

            // Incrementar stock general del hijo
            $child->stock += $totalChildQtyToAdd;
            $child->save();

            // 3. Registrar auditoría/Kardex disparando los eventos
            event(new StockUpdated(
                product: $parent,
                quantity: $quantityToUnpack,
                type: 'out',
                notes: "Fraccionamiento de producto (Origen) hacia '{$child->name}'"
            ));

            event(new StockUpdated(
                product: $child,
                quantity: $totalChildQtyToAdd,
                type: 'in',
                notes: "Fraccionamiento de producto (Destino) desde '{$parent->name}'"
            ));

            // 4. Invalidar caché
            $this->cacheService->invalidateProductStock($parent->id);
            $this->cacheService->invalidateProductStock($child->id);
            $this->cacheService->invalidateProducts();

            DB::commit();

            return [
                'parent_stock' => $parent->stock,
                'child_stock' => $child->stock,
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
