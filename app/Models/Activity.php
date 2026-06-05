<?php

namespace App\Models;

use Spatie\Activitylog\Models\Activity as SpatieActivity;

class Activity extends SpatieActivity
{
    /**
     * Get the formatted description in Spanish.
     */
    public function getFormattedDescriptionAttribute(): string
    {
        $causer = $this->causer?->name ?? 'El sistema';
        $subject = $this->subject;
        $description = $this->description;
        
        // Extract subject class name if exists
        $modelType = $this->subject_type ? class_basename($this->subject_type) : $this->log_name;

        // If subject is null and the model was deleted or is not loaded, we still want safety
        $subjectName = '';
        if ($subject) {
            $subjectName = $subject->name ?? ($subject->code ?? '');
        }

        switch ($modelType) {
            case 'Product':
                if ($description === 'created') {
                    $description = "registró el producto '" . ($subjectName ?: 'N/D') . "'";
                } elseif ($description === 'updated') {
                    $description = "actualizó los detalles del producto '" . ($subjectName ?: 'N/D') . "'";
                } elseif ($description === 'deleted') {
                    $description = "eliminó el producto '" . ($subjectName ?: 'N/D') . "'";
                }
                break;
            case 'Supplier':
                if ($description === 'created') {
                    $description = "registró al proveedor '" . ($subjectName ?: 'N/D') . "'";
                } elseif ($description === 'updated') {
                    $description = "actualizó la ficha del proveedor '" . ($subjectName ?: 'N/D') . "'";
                } elseif ($description === 'deleted') {
                    $description = "eliminó al proveedor '" . ($subjectName ?: 'N/D') . "'";
                }
                break;
            case 'PurchaseOrder':
                $poCode = $subject?->code ?? '';
                if ($description === 'created') {
                    $description = "creó la orden de compra '" . ($poCode ?: 'N/D') . "'";
                } elseif ($description === 'updated') {
                    $description = "actualizó la orden de compra '" . ($poCode ?: 'N/D') . "'";
                } elseif ($description === 'issued') {
                    $description = "emitió oficialmente la orden de compra '" . ($poCode ?: 'N/D') . "'";
                }
                break;
            case 'InventoryRequest':
                $reqId = $subject?->id ?? '';
                if ($description === 'created') {
                    $description = "registró una nueva solicitud de despacho (#" . ($reqId ?: 'N/D') . ")";
                } elseif ($description === 'approved') {
                    $description = "aprobó la solicitud de despacho (#" . ($reqId ?: 'N/D') . ")";
                } elseif ($description === 'rejected') {
                    $description = "rechazó la solicitud de despacho (#" . ($reqId ?: 'N/D') . ")";
                }
                break;
            case 'StockIn':
                $invoiceNum = $subject?->invoice_number ?? '';
                if ($description === 'created') {
                    $description = "registró una entrada de inventario (Factura: '" . ($invoiceNum ?: 'N/D') . "')";
                } elseif ($description === 'updated') {
                    $description = "actualizó la entrada de inventario (Factura: '" . ($invoiceNum ?: 'N/D') . "')";
                }
                break;
        }

        // Translate general Spatie terms
        if ($description === 'created') {
            $description = "creó un registro";
        } elseif ($description === 'updated') {
            $description = "actualizó un registro";
        } elseif ($description === 'deleted') {
            $description = "eliminó un registro";
        }

        // Humanize stock movement logs if they exist
        if (str_contains($description, 'Entrada de stock') || str_contains($description, 'Salida de stock') || str_contains($description, 'Ajuste de stock')) {
            return "{$causer} registró un movimiento: {$description}";
        }

        return "{$causer} {$description}";
    }
}
