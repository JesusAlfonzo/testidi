<?php

namespace App\Http\Controllers;

use App\Models\InventoryRequest as SolicitudModel; // Alias correcto para el modelo renombrado
use App\Models\Product;
use App\Models\StockIn;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Barryvdh\DomPDF\Facade\Pdf; // Solo usamos PDF librería, Excel es nativo

class ReportController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:reportes_ver')->only(['stockReport', 'requestsReport']);
        $this->middleware('can:reportes_kardex')->only('kardexReport');
    }

    // =========================================================================
    // 1. REPORTE DE STOCK ACTUAL
    // =========================================================================

    public function stockReport(Request $request)
    {
        $categories = \App\Models\Category::pluck('name', 'id');
        $locations = \App\Models\Location::pluck('name', 'id');

        $query = Product::with(['unit', 'category', 'location'])
            ->where('is_active', true)
            ->orderBy('name', 'asc');

        // Aplicar Filtros
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }
        if ($request->filled('location_id')) {
            $query->where('location_id', $request->location_id);
        }
        if ($request->filled('stock_status')) {
            if ($request->stock_status === 'low') {
                $query->whereColumn('stock', '<=', 'min_stock');
            } elseif ($request->stock_status === 'ok') {
                $query->whereColumn('stock', '>', 'min_stock');
            }
        }

        $products = $query->get();

        return view('admin.reports.stock', compact('products', 'categories', 'locations'));
    }

    /**
     * Exporta el stock actual a Excel (CSV Nativo)
     */
    public function exportStockExcel(Request $request)
    {
        $fileName = 'inventario_stock_' . date('Y-m-d_H-i') . '.csv';
        
        // Reutilizamos la lógica de filtrado
        $query = Product::with(['unit', 'category', 'location'])
            ->where('is_active', true)
            ->orderBy('name', 'asc');

        if ($request->filled('category_id')) $query->where('category_id', $request->category_id);
        if ($request->filled('location_id')) $query->where('location_id', $request->location_id);
        if ($request->filled('stock_status')) {
            if ($request->stock_status === 'low') $query->whereColumn('stock', '<=', 'min_stock');
            elseif ($request->stock_status === 'ok') $query->whereColumn('stock', '>', 'min_stock');
        }

        $products = $query->get();

        $headers = [
            "Content-type" => "text/csv", "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma" => "no-cache", "Cache-Control" => "must-revalidate, post-check=0, pre-check=0", "Expires" => "0"
        ];
        $columns = ['CÓDIGO', 'PRODUCTO', 'CATEGORÍA', 'UBICACIÓN', 'STOCK', 'UNIDAD', 'MÍNIMO', 'COSTO', 'PRECIO', 'ESTADO'];

        $callback = function() use($products, $columns) {
            $file = fopen('php://output', 'w');
            fputs($file, "\xEF\xBB\xBF"); // BOM para caracteres especiales
            fputcsv($file, $columns);
            foreach ($products as $product) {
                $estado = $product->stock <= $product->min_stock ? 'BAJO STOCK' : 'Óptimo';
                fputcsv($file, [
                    $product->code, $product->name, $product->category->name ?? '', $product->location->name ?? '',
                    $product->stock, $product->unit->abbreviation ?? '', $product->min_stock,
                    number_format($product->cost, 2), number_format($product->price, 2), $estado
                ]);
            }
            fclose($file);
        };
        return response()->stream($callback, 200, $headers);
    }

    public function exportStockPdf(Request $request)
    {
        $query = Product::with(['unit', 'category', 'location'])
            ->where('is_active', true)
            ->orderBy('name', 'asc');

        if ($request->filled('category_id')) $query->where('category_id', $request->category_id);
        if ($request->filled('location_id')) $query->where('location_id', $request->location_id);
        if ($request->filled('stock_status')) {
            if ($request->stock_status === 'low') $query->whereColumn('stock', '<=', 'min_stock');
            elseif ($request->stock_status === 'ok') $query->whereColumn('stock', '>', 'min_stock');
        }
        
        $products = $query->get();
        $pdf = Pdf::loadView('admin.reports.pdf.stock', compact('products'));
        return $pdf->stream('reporte_stock_' . date('Y-m-d') . '.pdf');
    }

    // =========================================================================
    // 2. REPORTE DE SOLICITUDES (MOVIMIENTOS)
    // =========================================================================

    public function requestsReport(Request $request)
    {
        $query = SolicitudModel::with(['requester', 'approver'])
            ->orderBy('requested_at', 'desc');

        // Aplicar Filtros
        if ($request->filled('date_from')) {
            $query->whereDate('requested_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('requested_at', '<=', $request->date_to);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $requests = $query->get();

        return view('admin.reports.requests', compact('requests'));
    }

    public function exportRequestsExcel(Request $request)
    {
        $fileName = 'reporte_solicitudes_' . date('Y-m-d_H-i') . '.csv';
        
        $query = SolicitudModel::with(['requester', 'approver'])
            ->orderBy('requested_at', 'desc');

        if ($request->filled('date_from')) $query->whereDate('requested_at', '>=', $request->date_from);
        if ($request->filled('date_to')) $query->whereDate('requested_at', '<=', $request->date_to);
        if ($request->filled('status')) $query->where('status', $request->status);

        $requests = $query->get();

        $headers = [
            "Content-type" => "text/csv", "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma" => "no-cache", "Cache-Control" => "must-revalidate, post-check=0, pre-check=0", "Expires" => "0"
        ];
        $columns = ['ID', 'ESTADO', 'SOLICITANTE', 'FECHA SOLICITUD', 'JUSTIFICACIÓN', 'PROCESADO POR', 'FECHA PROCESO', 'RAZÓN RECHAZO'];

        $callback = function() use($requests, $columns) {
            $file = fopen('php://output', 'w');
            fputs($file, "\xEF\xBB\xBF"); 
            fputcsv($file, $columns);
            foreach ($requests as $req) {
                fputcsv($file, [
                    'REQ-' . $req->id,
                    $req->status,
                    $req->requester->name ?? 'N/A',
                    $req->requested_at ? $req->requested_at->format('Y-m-d H:i') : '-',
                    $req->justification,
                    $req->approver->name ?? 'Pendiente',
                    $req->processed_at ? $req->processed_at->format('Y-m-d H:i') : '-',
                    $req->rejection_reason ?? '-'
                ]);
            }
            fclose($file);
        };
        return response()->stream($callback, 200, $headers);
    }

    public function exportRequestsPdf(Request $request)
    {
        $query = SolicitudModel::with(['requester', 'approver'])
            ->orderBy('requested_at', 'desc');

        if ($request->filled('date_from')) $query->whereDate('requested_at', '>=', $request->date_from);
        if ($request->filled('date_to')) $query->whereDate('requested_at', '<=', $request->date_to);
        if ($request->filled('status')) $query->where('status', $request->status);

        $requests = $query->get();
        $pdf = Pdf::loadView('admin.reports.pdf.requests', compact('requests'))
                  ->setPaper('a4', 'landscape');
        
        return $pdf->stream('reporte_solicitudes_' . date('Y-m-d') . '.pdf');
    }

    // =========================================================================
    // 3. KARDEX (HISTORIAL POR PRODUCTO)
    // =========================================================================

    /**
     * Lógica centralizada para calcular el Kardex.
     * Unifica entradas y salidas en una sola línea de tiempo.
     */
    private function getKardexData(Product $product)
    {
        // A. Entradas
        $entradas = StockIn::where('product_id', $product->id)
            ->with(['supplier', 'user'])->get()
            ->map(function ($entrada) {
                return [
                    'date'      => $entrada->entry_date ?? $entrada->created_at,
                    'type'      => 'ENTRADA',
                    'quantity'  => $entrada->quantity,
                    'unit_price' => $entrada->unit_cost,
                    'reference' => 'ENT-' . $entrada->id,
                    'user'      => $entrada->user->name ?? 'Sistema',
                    'notes'     => 'Prov: ' . ($entrada->supplier->name ?? 'N/A'),
                    'timestamp' => $entrada->created_at->timestamp,
                ];
            });

        // B. Salidas (Solicitudes Aprobadas)
        $salidas = SolicitudModel::where('status', 'Approved')
            ->with(['approver', 'items.kit.components'])->get()
            ->flatMap(function ($solicitud) use ($product) {
                $movimientos = [];
                foreach ($solicitud->items as $item) {
                    // B1. Salida Directa
                    if ($item->item_type === 'product' && $item->product_id === $product->id) {
                        $movimientos[] = [
                            'date'      => $solicitud->processed_at,
                            'type'      => 'SALIDA',
                            'quantity'  => $item->quantity_requested * -1,
                            'unit_price' => $item->unit_price_at_request,
                            'reference' => 'REQ-' . $solicitud->id,
                            'user'      => $solicitud->approver->name ?? 'Sistema',
                            'notes'     => 'Justif: ' . Str::limit($solicitud->justification, 50),
                            'timestamp' => $solicitud->processed_at->timestamp,
                        ];
                    }
                    // B2. Salida por Kit
                    if ($item->item_type === 'kit' && $item->kit) {
                        $componente = $item->kit->components->firstWhere('id', $product->id);
                        if ($componente) {
                            $total = $item->quantity_requested * $componente->pivot->quantity_required;
                            $movimientos[] = [
                                'date'      => $solicitud->processed_at,
                                'type'      => 'SALIDA (KIT)',
                                'quantity'  => $total * -1,
                                'unit_price' => $componente->cost,
                                'reference' => 'REQ-' . $solicitud->id,
                                'user'      => $solicitud->approver->name ?? 'Sistema',
                                'notes'     => "Kit: {$item->kit->name}",
                                'timestamp' => $solicitud->processed_at->timestamp,
                            ];
                        }
                    }
                }
                return $movimientos;
            });

        // C. Unir, Ordenar y Saldos
        $movimientos = $entradas->concat($salidas)->sortBy('timestamp')->values();
        
        $saldoAcumulado = $product->initial_stock ?? 0;
        $kardex = [];

        if ($movimientos->isNotEmpty()) {
            // Saldo inicial calculado
            $saldoAcumulado = $product->stock - $movimientos->sum('quantity');
             $kardex[] = [
                 'date' => $movimientos->first()['date']->copy()->subSecond(),
                 'type' => 'INICIO',
                 'quantity' => 0,
                 'unit_price' => 0,
                 'reference' => 'SALDO INICIAL',
                 'user' => 'Sistema',
                 'notes' => 'Saldo calculado antes de movimientos',
                 'balance' => $saldoAcumulado,
             ];
            foreach ($movimientos as $movimiento) {
                $saldoAcumulado += $movimiento['quantity'];
                $movimiento['balance'] = $saldoAcumulado;
                $kardex[] = $movimiento;
            }
        } else {
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
        
        return $kardex;
    }

    public function kardexReport(Product $product)
    {
        $kardex = $this->getKardexData($product);
        return view('admin.reports.kardex', compact('product', 'kardex'));
    }

    public function exportKardexExcel(Product $product)
    {
        $kardex = $this->getKardexData($product);
        $fileName = 'kardex_' . Str::slug($product->name) . '_' . date('Y-m-d') . '.csv';
        
        $headers = [
            "Content-type" => "text/csv", "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma" => "no-cache", "Cache-Control" => "must-revalidate, post-check=0, pre-check=0", "Expires" => "0"
        ];
        $columns = ['FECHA', 'TIPO', 'REFERENCIA', 'NOTAS', 'CANTIDAD', 'SALDO', 'USUARIO'];

        $callback = function() use($kardex, $columns) {
            $file = fopen('php://output', 'w');
            fputs($file, "\xEF\xBB\xBF"); 
            fputcsv($file, $columns);
            foreach ($kardex as $mov) {
                fputcsv($file, [
                    \Carbon\Carbon::parse($mov['date'])->format('Y-m-d H:i'),
                    $mov['type'], $mov['reference'], $mov['notes'],
                    $mov['quantity'], $mov['balance'], $mov['user']
                ]);
            }
            fclose($file);
        };
        return response()->stream($callback, 200, $headers);
    }

    public function exportKardexPdf(Product $product)
    {
        $kardex = $this->getKardexData($product);
        $pdf = Pdf::loadView('admin.reports.pdf.kardex', compact('product', 'kardex'));
        return $pdf->stream('kardex_' . Str::slug($product->name) . '.pdf');
    }
}