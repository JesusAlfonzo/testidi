<?php
namespace App\Http\Controllers;

use App\Models\InventoryRequest as SolicitudModel;
use App\Models\Product;
use App\Models\StockIn;
use App\Models\StockInItem;
use App\Models\RequestItem;
use App\Models\Category;
use App\Models\Location;
use App\Models\Brand;
use App\Models\User;
use App\Services\KardexService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Barryvdh\DomPDF\Facade\Pdf;
use Spatie\Activitylog\Models\Activity;

class ReportController extends Controller
{
    protected KardexService $kardexService;

    public function __construct(KardexService $kardexService)
    {
        $this->kardexService = $kardexService;
        $this->middleware('can:reportes_stock')->only(['stockReport', 'exportStockExcel', 'exportStockPdf']);
        $this->middleware('can:reportes_movimientos')->only(['requestsReport', 'exportRequestsExcel', 'exportRequestsPdf']);
        $this->middleware('can:reportes_kardex')->only(['kardexReport', 'exportKardexExcel', 'exportKardexPdf']);
        $this->middleware('can:reportes_ver')->only(['index', 'generatePdf']);
    }

    // =========================================================================
    // 1. REPORTE DE STOCK ACTUAL
    // =========================================================================

    public function stockReport(Request $request)
    {
        $request->validate([
            'search' => 'nullable|string|max:255',
            'category_id' => 'nullable|integer|exists:categories,id',
            'location_id' => 'nullable|integer|exists:locations,id',
            'brand_id' => 'nullable|integer|exists:brands,id',
            'stock_status' => 'nullable|string|in:low,ok,zero,with_stock',
        ]);

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
        try {
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
        } catch (\Exception $e) {
            return back()->with('error', 'Error al generar el PDF: ' . $e->getMessage());
        }
    }

    // =========================================================================
    // 2. REPORTE DE SOLICITUDES (MOVIMIENTOS)
    // =========================================================================

    public function requestsReport(Request $request)
    {
        $request->validate([
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date|after_or_equal:date_from',
            'status' => 'nullable|string|in:Pending,Approved,Rejected',
            'requester_id' => 'nullable|integer|exists:users,id',
        ]);

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
        try {
            $query = SolicitudModel::with(['requester', 'approver'])
                ->orderBy('requested_at', 'desc');

            if ($request->filled('date_from')) $query->whereDate('requested_at', '>=', $request->date_from);
            if ($request->filled('date_to')) $query->whereDate('requested_at', '<=', $request->date_to);
            if ($request->filled('status')) $query->where('status', $request->status);
            if ($request->filled('requester_id')) $query->where('requester_id', $request->requester_id);

            $requests = $query->get();
            $pdf = Pdf::loadView('admin.reports.pdf.requests', compact('requests'))->setPaper('a4', 'landscape');
            return $pdf->stream('reporte_solicitudes_' . date('Y-m-d') . '.pdf');
        } catch (\Exception $e) {
            return back()->with('error', 'Error al generar el PDF: ' . $e->getMessage());
        }
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

    // =========================================================================
    // 4. GENERADOR DE REPORTES DINÁMICO (NUEVO)
    // =========================================================================

    public function index(Request $request)
    {
        $products = Product::where('is_active', true)->orderBy('name')->get(['id', 'name', 'code']);
        $users = User::orderBy('name')->get(['id', 'name']);
        $categories = Category::orderBy('name')->get(['id', 'name']);
        $locations = Location::orderBy('name')->get(['id', 'name']);
        $brands = Brand::orderBy('name')->get(['id', 'name']);
        
        $data = null;
        $totals = null;
        $filters = $request->all();

        if ($request->filled('report_type')) {
            $request->validate([
                'report_type' => 'required|string|in:inventario,entradas,salidas,fraccionamientos',
                'fecha_inicio' => 'nullable|date',
                'fecha_fin' => 'nullable|date|after_or_equal:fecha_inicio',
                'product_id' => 'nullable|integer|exists:products,id',
                'batch_number' => 'nullable|string|max:255',
                'user_id' => 'nullable|integer|exists:users,id',
                
                // Nuevos filtros
                'stock_operator' => 'nullable|string|in:>,<,=,>=,<=',
                'stock_value' => 'nullable|integer|min:0',
                'expiry_from' => 'nullable|date',
                'expiry_to' => 'nullable|date|after_or_equal:expiry_from',
                'location_id' => 'nullable|integer|exists:locations,id',
                'is_active' => 'nullable|string|in:all,active,inactive',
                'origin' => 'nullable|string|in:all,standard,on_the_fly',
            ]);

            $query = $this->buildReportQuery($filters['report_type'], $filters);
            $data = $query->get();
            $totals = $this->calculateTotals($filters['report_type'], $data);
        }

        return view('admin.reports.index', compact('products', 'users', 'categories', 'locations', 'brands', 'data', 'filters', 'totals'));
    }

    public function generatePdf(Request $request)
    {
        $validated = $request->validate([
            'report_type' => 'required|string|in:inventario,entradas,salidas,fraccionamientos',
            'fecha_inicio' => 'nullable|date',
            'fecha_fin' => 'nullable|date|after_or_equal:fecha_inicio',
            'product_id' => 'nullable|integer|exists:products,id',
            'batch_number' => 'nullable|string|max:255',
            'user_id' => 'nullable|integer|exists:users,id',
            
            // Nuevos filtros
            'stock_operator' => 'nullable|string|in:>,<,=,>=,<=',
            'stock_value' => 'nullable|integer|min:0',
            'expiry_from' => 'nullable|date',
            'expiry_to' => 'nullable|date|after_or_equal:expiry_from',
            'location_id' => 'nullable|integer|exists:locations,id',
            'is_active' => 'nullable|string|in:all,active,inactive',
            'origin' => 'nullable|string|in:all,standard,on_the_fly',
        ]);

        $query = $this->buildReportQuery($validated['report_type'], $validated);
        $data = $query->get();
        $totals = $this->calculateTotals($validated['report_type'], $data);

        $reportTitle = match ($validated['report_type']) {
            'inventario' => 'Inventario Actual de Insumos',
            'entradas' => 'Historial de Entradas de Almacén',
            'salidas' => 'Historial de Salidas / Despachos',
            'fraccionamientos' => 'Registro de Movimientos por Fraccionamiento',
            default => 'Reporte de Almacén',
        };

        $pdf = Pdf::loadView('admin.reports.pdf-template', [
            'data' => $data,
            'report_type' => $validated['report_type'],
            'report_title' => $reportTitle,
            'filters' => $validated,
            'totals' => $totals,
            'generated_at' => now()->format('d/m/Y H:i:s'),
        ]);

        $pdf->setPaper('a4', 'landscape');
        
        return $pdf->stream('reporte_' . $validated['report_type'] . '_' . date('Ymd_His') . '.pdf');
    }

    protected function calculateTotals(string $type, $data): array
    {
        $totals = [
            'count' => $data->count(),
            'sum_quantity' => 0,
            'sum_amount' => 0,
        ];

        if ($type === 'inventario') {
            $totals['sum_quantity'] = $data->sum('stock');
            $totals['sum_amount'] = $data->sum(function ($prod) {
                return $prod->stock * $prod->cost;
            });
        } elseif ($type === 'entradas') {
            $totals['sum_quantity'] = $data->sum('quantity');
            $totals['sum_amount'] = $data->sum(function ($item) {
                return $item->quantity * $item->unit_cost;
            });
        } elseif ($type === 'salidas') {
            $totals['sum_quantity'] = $data->sum('quantity_requested');
            $totals['sum_amount'] = $data->sum(function ($item) {
                return $item->quantity_requested * ($item->unit_price_at_request ?? 0);
            });
        } elseif ($type === 'fraccionamientos') {
            $totals['sum_quantity'] = $data->sum(function ($activity) {
                return $activity->properties['quantity'] ?? 0;
            });
        }

        return $totals;
    }

    protected function buildReportQuery(string $type, array $filters)
    {
        switch ($type) {
            case 'inventario':
                $query = Product::with(['unit', 'category', 'location', 'brand']);

                // Filtro: estado (is_active)
                $query->when(isset($filters['is_active']) && $filters['is_active'] !== 'all', function ($q) use ($filters) {
                    return $q->where('is_active', $filters['is_active'] === 'active');
                }, function ($q) use ($filters) {
                    return $q->when(empty($filters['is_active']), function ($subQ) {
                        return $subQ->where('is_active', true);
                    });
                });

                // Filtro: product_id
                $query->when(!empty($filters['product_id']), function ($q) use ($filters) {
                    return $q->where('id', $filters['product_id']);
                });

                // Filtro: user_id
                $query->when(!empty($filters['user_id']), function ($q) use ($filters) {
                    return $q->where('user_id', $filters['user_id']);
                });

                // Filtro: location_id
                $query->when(!empty($filters['location_id']), function ($q) use ($filters) {
                    return $q->where('location_id', $filters['location_id']);
                });

                // Filtro: batch_number
                $query->when(!empty($filters['batch_number']), function ($q) use ($filters) {
                    return $q->whereHas('batches', function ($subQ) use ($filters) {
                        $subQ->where('batch_number', 'like', '%' . $filters['batch_number'] . '%');
                    });
                });

                // Filtro: fecha_inicio
                $query->when(!empty($filters['fecha_inicio']), function ($q) use ($filters) {
                    return $q->whereDate('created_at', '>=', $filters['fecha_inicio']);
                });

                // Filtro: fecha_fin
                $query->when(!empty($filters['fecha_fin']), function ($q) use ($filters) {
                    return $q->whereDate('created_at', '<=', $filters['fecha_fin']);
                });

                // Filtro: stock_operator y stock_value
                $query->when(!empty($filters['stock_operator']) && isset($filters['stock_value']) && $filters['stock_value'] !== '', function ($q) use ($filters) {
                    return $q->where('stock', $filters['stock_operator'], $filters['stock_value']);
                });

                // Filtro: rango de vencimiento (expiry_from y expiry_to)
                $query->when(!empty($filters['expiry_from']), function ($q) use ($filters) {
                    return $q->whereHas('batches', function ($subQ) use ($filters) {
                        $subQ->whereDate('expiration_date', '>=', $filters['expiry_from']);
                    });
                });
                $query->when(!empty($filters['expiry_to']), function ($q) use ($filters) {
                    return $q->whereHas('batches', function ($subQ) use ($filters) {
                        $subQ->whereDate('expiration_date', '<=', $filters['expiry_to']);
                    });
                });

                // Filtro: origen (created_on_the_fly)
                $query->when(!empty($filters['origin']) && $filters['origin'] !== 'all', function ($q) use ($filters) {
                    return $q->where('created_on_the_fly', $filters['origin'] === 'on_the_fly');
                });

                return $query->orderBy('name', 'asc');

            case 'entradas':
                $query = StockInItem::with(['stockIn.supplier', 'stockIn.user', 'product.unit', 'product.category'])
                    ->whereHas('stockIn', function ($q) use ($filters) {
                        if (!empty($filters['fecha_inicio'])) {
                            $q->whereDate('entry_date', '>=', $filters['fecha_inicio']);
                        }
                        if (!empty($filters['fecha_fin'])) {
                            $q->whereDate('entry_date', '<=', $filters['fecha_fin']);
                        }
                        if (!empty($filters['user_id'])) {
                            $q->where('user_id', $filters['user_id']);
                        }
                    });

                if (!empty($filters['product_id'])) {
                    $query->where('product_id', $filters['product_id']);
                }
                if (!empty($filters['batch_number'])) {
                    $query->where('batch_number', 'like', '%' . $filters['batch_number'] . '%');
                }
                
                return $query->orderBy('created_at', 'desc');

            case 'salidas':
                $query = RequestItem::with(['request.requester', 'request.approver', 'product.unit', 'kit'])
                    ->whereHas('request', function ($q) use ($filters) {
                        $q->where('status', 'Approved');
                        if (!empty($filters['fecha_inicio'])) {
                            $q->whereDate('processed_at', '>=', $filters['fecha_inicio']);
                        }
                        if (!empty($filters['fecha_fin'])) {
                            $q->whereDate('processed_at', '<=', $filters['fecha_fin']);
                        }
                        if (!empty($filters['user_id'])) {
                            $q->where('requester_id', $filters['user_id']);
                        }
                    });

                if (!empty($filters['product_id'])) {
                    $query->where(function ($q) use ($filters) {
                        $q->where('product_id', $filters['product_id'])
                          ->orWhereHas('kit.components', function ($qk) use ($filters) {
                              $qk->where('product_id', $filters['product_id']);
                          });
                    });
                }
                if (!empty($filters['batch_number'])) {
                    $query->whereHas('product.batches', function ($q) use ($filters) {
                        $q->where('batch_number', 'like', '%' . $filters['batch_number'] . '%');
                    });
                }
                
                return $query->orderBy('created_at', 'desc');

            case 'fraccionamientos':
                $query = Activity::where(function ($q) {
                        $q->where('description', 'like', '%Fraccionamiento%')
                          ->orWhere('description', 'like', '%fraccionamiento%');
                    })
                    ->with(['causer', 'subject']);

                if (!empty($filters['fecha_inicio'])) {
                    $query->whereDate('created_at', '>=', $filters['fecha_inicio']);
                }
                if (!empty($filters['fecha_fin'])) {
                    $query->whereDate('created_at', '<=', $filters['fecha_fin']);
                }
                if (!empty($filters['product_id'])) {
                    $query->where('subject_type', Product::class)
                          ->where('subject_id', $filters['product_id']);
                }
                if (!empty($filters['user_id'])) {
                    $query->where('causer_id', $filters['user_id']);
                }
                if (!empty($filters['batch_number'])) {
                    $query->where('description', 'like', '%' . $filters['batch_number'] . '%');
                }
                
                return $query->orderBy('created_at', 'desc');

            default:
                throw new \InvalidArgumentException("Tipo de reporte no soportado.");
        }
    }
}
