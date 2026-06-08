<?php

namespace App\Models;

use Spatie\Activitylog\Models\Activity as SpatieActivity;

class Activity extends SpatieActivity
{
    public function getHumanActionAttribute(): string
    {
        return match($this->description) {
            'created' => 'Creó',
            'updated' => 'Actualizó',
            'deleted' => 'Eliminó',
            'approved' => 'Aprobó',
            'rejected' => 'Rechazó',
            'issued' => 'Emitió',
            'completed' => 'Completó',
            default => ucfirst($this->description),
        };
    }

    public function getActionBadgeAttribute(): string
    {
        return match($this->description) {
            'created', 'approved' => 'success',
            'updated', 'issued', 'completed' => 'info',
            'deleted' => 'danger',
            'rejected' => 'warning',
            default => 'secondary',
        };
    }

    public function getModuleNameAttribute(): string
    {
        $map = [
            'App\Models\Product' => 'Inventario / Producto',
            'App\Models\Supplier' => 'Proveedores',
            'App\Models\StockIn' => 'Inventario / Entrada de Stock',
            'App\Models\InventoryRequest' => 'Solicitudes / Salida de Stock',
            'App\Models\User' => 'Seguridad / Usuario',
            'App\Models\Kit' => 'Inventario / Kit',
            'App\Models\Category' => 'Inventario / Categoría',
            'App\Models\Brand' => 'Inventario / Marca',
            'App\Models\Location' => 'Inventario / Ubicación',
            'App\Models\Unit' => 'Inventario / Unidad',
            'App\Models\PurchaseOrder' => 'Compras / Orden de Compra',
            'App\Models\PurchaseOrderItem' => 'Compras / Ítem de OC',
            'App\Models\RequestForQuotation' => 'Compras / Solicitud de Cotización',
            'App\Models\RfqItem' => 'Compras / Ítem de Cotización',
            'App\Models\Role' => 'Seguridad / Rol',
            'App\Models\Permission' => 'Seguridad / Permiso',
        ];
        return $map[$this->subject_type] ?? class_basename($this->subject_type);
    }

    public function getSubjectNameAttribute(): string
    {
        $subject = $this->subject;
        if (!$subject) {
            return '[Eliminado] #' . $this->subject_id;
        }
        return $subject->name
            ?? $subject->code
            ?? $subject->title
            ?? $subject->invoice_number
            ?? ('#' . $subject->getKey());
    }

    public function getCauserInitialsAttribute(): string
    {
        $name = $this->causer?->name ?? 'Sistema';
        $parts = explode(' ', $name);
        if (count($parts) >= 2) {
            return strtoupper(substr($parts[0], 0, 1) . substr($parts[1], 0, 1));
        }
        return strtoupper(substr($name, 0, 2));
    }

    public static function fieldLabel(string $key): string
    {
        $labels = [
            'name' => 'Nombre',
            'code' => 'Código',
            'description' => 'Descripción',
            'stock' => 'Stock',
            'min_stock' => 'Stock Mínimo',
            'max_stock' => 'Stock Máximo',
            'unit_cost' => 'Costo Unitario',
            'cost' => 'Costo',
            'price' => 'Precio',
            'quantity' => 'Cantidad',
            'is_active' => 'Estado (Activo/Inactivo)',
            'is_generic' => 'Producto Genérico',
            'requires_serial' => 'Requiere Serial',
            'type' => 'Tipo',
            'status' => 'Estado',
            'currency' => 'Moneda',
            'iva_exempt' => 'Exento de IVA',
            'email' => 'Correo Electrónico',
            'phone' => 'Teléfono',
            'rif' => 'RIF',
            'address' => 'Dirección',
            'batch_number' => 'Número de Lote',
            'expiration_date' => 'Fecha de Vencimiento',
            'serial_number' => 'Número de Serie',
            'warehouse_location' => 'Ubicación en Almacén',
            'category_id' => 'Categoría',
            'supplier_id' => 'Proveedor',
            'product_id' => 'Producto',
            'location_id' => 'Ubicación Física',
            'brand_id' => 'Marca',
            'unit_id' => 'Unidad de Medida',
            'document_type' => 'Tipo de Documento',
            'document_number' => 'Número de Documento',
            'invoice_number' => 'Número de Factura',
            'delivery_note_number' => 'Número de Guía',
            'entry_date' => 'Fecha de Ingreso',
            'reason' => 'Razón / Motivo',
            'purchase_order_id' => 'Orden de Compra',
            'user_id' => 'Usuario Responsable',
            'causer_id' => 'Realizado Por',
            'notes' => 'Notas',
            'rejection_reason' => 'Motivo de Rechazo',
            'quantity_received' => 'Cantidad Recibida',
            'quantity_rejected' => 'Cantidad Rechazada',
            'unit_price' => 'Precio Unitario',
            'subtotal' => 'Subtotal',
            'total' => 'Total',
            'tax' => 'Impuesto',
            'discount' => 'Descuento',
            'date_issued' => 'Fecha de Emisión',
            'date_delivery' => 'Fecha de Entrega',
            'date_required' => 'Fecha Requerida',
            'delivered_at' => 'Fecha de Entrega',
            'created_at' => 'Fecha de Creación',
            'updated_at' => 'Fecha de Actualización',
            'password' => 'Contraseña',
            'is_approved' => 'Aprobado',
        ];
        return $labels[$key] ?? ucfirst(str_replace('_', ' ', $key));
    }

    public static function fieldValue(string $key, mixed $value): string
    {
        if (is_null($value)) {
            return '<span class="text-muted">— (Vacío)</span>';
        }
        if (is_bool($value) || in_array($value, [0, 1], true)) {
            if ($key === 'is_active' || $key === 'is_approved' || str_starts_with($key, 'is_')) {
                return $value ? '<span class="badge badge-success">Sí</span>' : '<span class="badge badge-secondary">No</span>';
            }
            return $value ? 'Sí' : 'No';
        }
        if (in_array($key, ['iva_exempt', 'requires_serial', 'is_generic'])) {
            return $value ? '<span class="badge badge-success">Sí</span>' : '<span class="badge badge-secondary">No</span>';
        }
        if (in_array($key, ['entry_date', 'date_issued', 'expiration_date', 'date_required', 'date_delivery', 'delivered_at'])) {
            if ($value instanceof \Carbon\Carbon || strtotime($value)) {
                return date('d/m/Y', strtotime($value));
            }
            return $value;
        }
        if (in_array($key, ['created_at', 'updated_at'])) {
            if ($value instanceof \Carbon\Carbon || strtotime($value)) {
                return date('d/m/Y H:i', strtotime($value));
            }
            return $value;
        }
        if (in_array($key, ['currency'])) {
            return strtoupper($value);
        }
        if (in_array($key, ['unit_cost', 'cost', 'price', 'unit_price', 'total', 'subtotal', 'tax', 'discount'])) {
            return '$ ' . number_format((float) $value, 2);
        }
        if (in_array($key, ['stock', 'quantity', 'min_stock', 'max_stock', 'quantity_received', 'quantity_rejected'])) {
            return (string) $value;
        }
        return e($value);
    }

    public function getPropertiesDiffAttribute(): array
    {
        $attributes = $this->properties['attributes'] ?? [];
        $old = $this->properties['old'] ?? [];

        $changes = [];
        $allKeys = array_unique(array_merge(array_keys($attributes), array_keys($old)));

        foreach ($allKeys as $key) {
            $oldVal = $old[$key] ?? null;
            $newVal = $attributes[$key] ?? null;

            if ($oldVal !== $newVal) {
                $changes[] = [
                    'field' => $key,
                    'label' => self::fieldLabel($key),
                    'old' => $oldVal,
                    'new' => $newVal,
                    'old_html' => self::fieldValue($key, $oldVal),
                    'new_html' => self::fieldValue($key, $newVal),
                ];
            }
        }

        return $changes;
    }
}
