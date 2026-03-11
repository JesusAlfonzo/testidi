<?php

namespace App\Http\Controllers;

use App\Models\InventoryRequest as SolicitudModel;
use App\Models\Product;
use App\Models\StockIn;
use App\Models\Category;
use App\Models\Location;
use App\Models\Brand;
use App\Models\User;
use App\Services\KardexService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Barryvdh\DomPDF\Facade\Pdf;

class ReportController extends Controller
{
    protected KardexService $kardexService;

    public function __construct(KardexService $kardexService)
    {
        $this->kardexService = $kardexService;
        $this->middleware('can:reportes_ver')->only(['stockReport', 'requestsReport']);
        $this->middleware('can:reportes_kardex')->only('kardexReport');
    }

    // =========================================================================
    // 1. REPORTE DE STOCK ACTUAL
    // =========================================================================

    public function stockReport(Request $request)
    {
        $categories = Category::pluck('name', 'id');
        $locations = Location::pluck('name', 'id');
        $brands = Brand::pluck('name', 'id');

        $query = Product::with(['unit', 'category', 'location', 'brand'])
            ->where('is_active', true)
            ->orderBy('name', 'asc');

        if ($request->filled('search')) {
            $search = strtolower($request->search);
            $query->where(function($q) use ($search) {
                $q->whereRaw('LOWER(name) LIKE ?', ["%{$search}%"])
                  ->orWhereRaw('LOWER(code) LIKE ?', ["%{$search}%"]);
            });
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }
        if ($request->filled('location_id')) {
            $query->where('location_id', $request->location_id);
        }
        if ($request->filled('brand_id')) {
            $query->where('brand_id', $request->brand_id);
        }
        if ($request->filled('stock_status')) {
            if ($request->stock_status === 'low') {
                $query->whereColumn('stock', '<=', 'min_stock');
            } elseif ($request->stock_status === 'ok') {
                $query->whereColumn('stock', '>', 'min_stock');
            } elseif ($request->stock_status === 'zero') {
                $query->where('stock', 0);
            } elseif ($request->stock_status === 'with_stock') {
                $query->where('stock', '>', 0);
            }
        }

        $products = $query->get();

        return view('admin.reports.stock', compact('products', 'categories', 'locations', 'brands'));
    }

    public function exportStockExcel(Request $request)
    {
        $fileName = 'inventario_stock_' . date('Y-m-d_H-i') . '.csv';

        $query = Product::with(['unit', 'category', 'location', 'brand'])
            ->where('is_active', true)
            ->orderBy('name', 'asc');

        if ($request->filled('search')) {
            $search = strtolower($request->search);
            $query->where(function($q) use ($search) {
                $q->whereRaw('LOWER(name) LIKE ?', ["%{$search}%"])
                  ->orWhereRaw('LOWER(code) LIKE ?', ["%{$search}%"]);
            });
        }
        if ($request->filled('category_id')) $query->where('category_id', $request->category_id);
        if ($request->filled('location_id')) $query->where('location_id', $request->location_id);
        if ($request->filled('brand_id')) $query->where('brand_id', $request->brand_id);
        if ($request->filled('stock_status')) {
            if ($request->stock_status === 'low') $query->whereColumn('stock', '<=', 'min_stock');
            elseif ($request->stock_status === 'ok') $query->whereColumn('stock', '>', 'min_stock');
            elseif ($request->stock_status === 'zero') $query->where('stock', 0);
            elseif ($request->stock_status === 'with_stock') $query->where('stock', '>', 0);
        }

        $products = $query->get();

        $headers = [
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        ];
        $columns = ['CÓDIGO', 'PRODUCTO', 'CATEGORÍA', 'MARCA', 'UBICACIÓN', 'STOCK', 'UNIDAD', 'MÍNIMO', 'COSTO', 'PRECIO', 'ESTADO'];

        $callback = function () use ($products, $columns) {
            $file = fopen('php://output', 'w');
            fputs($file, "\xEF\xBB\xBF");
            fputcsv($file, $columns);
            foreach ($products as $product) {
                $estado = $product->stock <= $product->min_stock ? 'BAJO STOCK' : 'Óptimo';
                fputcsv($file, [
                    $product->code,
                    $product->name,
                    $product->category->name ?? '',
                    $product->brand->name ?? '',
                    $product->location->name ?? '',
                    $product->stock,
                    $product->unit->abbreviation ?? '',
                    $product->min_stock,
                    number_format($product->cost, 2),
                    number_format($product->price, 2),
                    $estado
                ]);
            }
            fclose($file);
        };
        return response()->stream($callback, 200, $headers);
    }

    public function exportStockPdf(Request $request)
    {
        $query = Product::with(['unit', 'category', 'location', 'brand'])
            ->where('is_active', true)
            ->orderBy('name', 'asc');

        if ($request->filled('search')) {
            $search = strtolower($request->search);
            $query->where(function($q) use ($search) {
                $q->whereRaw('LOWER(name) LIKE ?', ["%{$search}%"])
                  ->orWhereRaw('LOWER(code) LIKE ?', ["%{$search}%"]);
            });
        }
        if ($request->filled('category_id')) $query->where('category_id', $request->category_id);
        if ($request->filled('location_id')) $query->where('location_id', $request->location_id);
        if ($request->filled('brand_id')) $query->where('brand_id', $request->brand_id);
        if ($request->filled('stock_status')) {
            if ($request->stock_status === 'low') $query->whereColumn('stock', '<=', 'min_stock');
            elseif ($request->stock_status === 'ok') $query->whereColumn('stock', '>', 'min_stock');
            elseif ($request->stock_status === 'zero') $query->where('stock', 0);
            elseif ($request->stock_status === 'with_stock') $query->where('stock', '>', 0);
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
        // 🔑 CORRECCIÓN: Cargar lista de usuarios para el filtro
        $requesters = User::pluck('name', 'id');

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
        // 🔑 CORRECCIÓN: Aplicar filtro de solicitante
        if ($request->filled('requester_id')) {
            $query->where('requester_id', $request->requester_id);
        }

        $requests = $query->get();

        // 🔑 CORRECCIÓN: Pasar $requesters a la vista
        return view('admin.reports.requests', compact('requests', 'requesters'));
    }

    public function exportRequestsExcel(Request $request)
    {
        $fileName = 'reporte_solicitudes_' . date('Y-m-d_H-i') . '.csv';

        $query = SolicitudModel::with(['requester', 'approver'])
            ->orderBy('requested_at', 'desc');

        if ($request->filled('date_from')) $query->whereDate('requested_at', '>=', $request->date_from);
        if ($request->filled('date_to')) $query->whereDate('requested_at', '<=', $request->date_to);
        if ($request->filled('status')) $query->where('status', $request->status);
        // 🔑 CORRECCIÓN: Filtro de solicitante en exportación
        if ($request->filled('requester_id')) $query->where('requester_id', $request->requester_id);

        $requests = $query->get();

        $headers = [
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        ];
        $columns = ['ID', 'ESTADO', 'SOLICITANTE', 'FECHA SOLICITUD', 'JUSTIFICACIÓN', 'PROCESADO POR', 'FECHA PROCESO', 'RAZÓN RECHAZO'];

        $callback = function () use ($requests, $columns) {
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
        // 🔑 CORRECCIÓN: Filtro de solicitante en exportación
        if ($request->filled('requester_id')) $query->where('requester_id', $request->requester_id);

        $requests = $query->get();
        $pdf = Pdf::loadView('admin.reports.pdf.requests', compact('requests'))->setPaper('a4', 'landscape');
        return $pdf->stream('reporte_solicitudes_' . date('Y-m-d') . '.pdf');
    }

    // =========================================================================
    // 3. KARDEX (HISTORIAL POR PRODUCTO)
    // =========================================================================

    public function kardexReport(Product $product)
    {
        $kardex = $this->kardexService->generateKardex($product);
        return view('admin.reports.kardex', compact('product', 'kardex'));
    }

    public function exportKardexExcel(Product $product)
    {
        $kardex = $this->kardexService->generateKardex($product);
        $fileName = 'kardex_' . Str::slug($product->name) . '_' . date('Y-m-d') . '.csv';

        $headers = [
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        ];
        $columns = ['FECHA', 'TIPO', 'REFERENCIA', 'NOTAS', 'CANTIDAD', 'SALDO', 'USUARIO'];

        $callback = function () use ($kardex, $columns) {
            $file = fopen('php://output', 'w');
            fputs($file, "\xEF\xBB\xBF");
            fputcsv($file, $columns);
            foreach ($kardex as $mov) {
                fputcsv($file, [
                    \Carbon\Carbon::parse($mov['date'])->format('Y-m-d H:i'),
                    $mov['type'],
                    $mov['reference'],
                    $mov['notes'],
                    $mov['quantity'],
                    $mov['balance'],
                    $mov['user']
                ]);
            }
            fclose($file);
        };
        return response()->stream($callback, 200, $headers);
    }

    public function exportKardexPdf(Product $product)
    {
        $kardex = $this->kardexService->generateKardex($product);
        $pdf = Pdf::loadView('admin.reports.pdf.kardex', compact('product', 'kardex'));
        return $pdf->stream('kardex_' . Str::slug($product->name) . '.pdf');
    }
}
