<?php

namespace App\Http\Controllers;

use App\Models\InventoryRequest as SolicitudModel; // 🔑 CORREGIDO: Usamos el modelo renombrado (InventoryRequest)
use App\Models\Product;
use App\Models\StockIn; // 🔑 AGREGADO: Necesario para kardexReport
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB; // 🔑 AGREGADO: Necesario para consultas avanzadas
use Illuminate\Support\Str; // 🔑 AGREGADO: Necesario para Str::limit en kardexReport

class ReportController extends Controller
{
    // Muestra el reporte de stock actual
    public function stockReport()
    {
        // Traemos los productos, ordenados por nombre, y cargamos la unidad de medida
        $products = Product::with('unit')
            ->orderBy('name', 'asc')
            ->paginate(20); // Paginación para no sobrecargar la vista

        return view('admin.reports.stock', compact('products'));
    }

    // Muestra el reporte de todas las solicitudes de inventario
    public function requestsReport()
    {
        // Cargamos todas las solicitudes con las relaciones necesarias para el reporte
        $requests = SolicitudModel::with(['requester', 'approver']) // SolicitudModel ahora es InventoryRequest
            ->orderBy('requested_at', 'desc')
            ->paginate(20);

        return view('admin.reports.requests', compact('requests'));
    }

    public function kardexReport(Product $product)
    {
        // 1. OBTENER ENTRADAS (STOCK-IN)
        $entradas = StockIn::where('product_id', $product->id)
            ->with(['supplier', 'user']) // Cargar relaciones
            ->get()
            // Transformar la colección a un formato común para la fusión
            ->map(function ($entrada) {
                return [
                    'date'      => $entrada->received_at ?? $entrada->created_at,
                    'type'      => 'ENTRADA',
                    'quantity'  => $entrada->quantity_received,
                    'unit_price' => $entrada->unit_price,
                    'reference' => 'ENT-' . $entrada->id,
                    'user'      => $entrada->user->name ?? 'Sistema',
                    'notes'     => 'Proveedor: ' . ($entrada->supplier->name ?? 'N/A'),
                ];
            });


        // 2. OBTENER SALIDAS (REQUESTS APROBADAS)
        $salidas = SolicitudModel::whereHas('items', function ($query) use ($product) { // SolicitudModel
            $query->where('product_id', $product->id);
        })
        ->where('status', 'Approved') // Solo consideramos las salidas APROBADAS
        ->with('approver', 'items') // Incluimos 'items' para acceder a la relación correctamente
        ->get()
        // Transformar la colección a un formato común para la fusión
        ->flatMap(function ($solicitud) use ($product) {
            // Buscamos el ítem específico dentro de la solicitud aprobada
            $item = $solicitud->items->where('product_id', $product->id)->first();

            // Verificación de seguridad
            if (!$item) return [];

            return [
                'date'      => $solicitud->processed_at,
                'type'      => 'SALIDA',
                'quantity'  => $item->quantity_requested * -1, // Cantidad Negativa para Salida
                'unit_price' => $item->unit_price_at_request,
                'reference' => 'REQ-' . $solicitud->id,
                'user'      => $solicitud->approver->name ?? 'Sistema',
                'notes'     => 'Justificación: ' . Str::limit($solicitud->justification, 50), // Uso de Str
            ];
        });

        // 3. FUSIONAR Y ORDENAR
        $movimientos = $entradas->merge($salidas)
            ->sortBy('date') // Ordenamos cronológicamente
            ->values();

        // 4. CALCULAR SALDO ACUMULADO (Lógica para el saldo)
        // Nota: Si 'initial_stock' no existe en tu tabla 'products', se debe usar el stock actual.
        $saldoAcumulado = $product->initial_stock ?? 0;
        $kardex = [];

        // Si la tabla no está vacía, calculamos el saldo
        if ($movimientos->isNotEmpty()) {
            // Determinar el saldo inicial real antes del primer movimiento
            $saldoAcumulado = $product->stock - $movimientos->sum('quantity');

             // Insertar un registro de inicio si es necesario (opcional)
             $kardex[] = [
                'date' => $movimientos->first()['date']->subSecond(), // Fecha antes del primer movimiento
                'type' => 'INICIO',
                'quantity' => 0,
                'unit_price' => 0,
                'reference' => 'Saldo Inicial',
                'user' => 'Sistema',
                'notes' => 'Saldo antes de los registros mostrados',
                'balance' => $saldoAcumulado,
            ];

            // Recalcular el saldo a partir del primer movimiento
            foreach ($movimientos as $movimiento) {
                $saldoAcumulado += $movimiento['quantity'];
                $movimiento['balance'] = $saldoAcumulado;
                $kardex[] = $movimiento;
            }
        } else {
            // Si no hay movimientos, el único registro es el stock actual
            $kardex[] = [
                'date' => now(),
                'type' => 'INICIO',
                'quantity' => $product->stock,
                'unit_price' => 0,
                'reference' => 'STOCK ACTUAL',
                'user' => 'Sistema',
                'notes' => 'Stock actual reportado',
                'balance' => $product->stock,
            ];
        }

        return view('admin.reports.kardex', compact('product', 'kardex'));
    }
}
