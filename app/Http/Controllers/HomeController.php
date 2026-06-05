<?php

namespace App\Http\Controllers;

use App\Models\InventoryRequest; 
use App\Models\Product;
use App\Models\ProductBatch;
use App\Models\Category;
use App\Models\Brand;
use App\Models\Unit;
use App\Models\Location;
use App\Models\Supplier;
use App\Models\PurchaseOrder;
use App\Models\User;
use App\Models\RequestForQuotation;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Spatie\Permission\Models\Role;
use App\Models\Activity;

class HomeController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'can:dashboard_acceso']);
    }

    /**
     * Detects the role and renders the specialized dashboard.
     */
    public function index()
    {
        $user = Auth::user();

        // 1. Administrador General (Superadmin)
        if ($user->hasRole('Superadmin') || $user->hasRole('Super Administrador')) {
            return $this->adminDashboard();
        } 
        // 2. Administrador de Salidas (Supervisor)
        elseif ($user->hasRole('Supervisor')) {
            return $this->salidasDashboard();
        }
        // 3. Compras y Procura (Logística o Encargado)
        elseif ($user->hasRole('Logistica') || $user->hasRole('Encargado Inventario')) {
            return $this->comprasDashboard();
        } 
        // 4. Empleado / Solicitante (Personal)
        elseif ($user->hasRole('Solicitante')) {
            return $this->empleadoDashboard($user);
        }

        // Fallback
        return view('home'); 
    }

    // ----------------------------------------------------------------------
    // 1. DASHBOARD: ADMINISTRADOR GENERAL
    // ----------------------------------------------------------------------
    private function adminDashboard()
    {
        $totalProducts = Product::where('is_active', true)->count();
        $lowStockCount = Product::where('is_active', true)->whereColumn('stock', '<=', 'min_stock')->count();

        // Rendimiento de solicitudes (Aprobadas vs Rechazadas)
        $chartApproved = InventoryRequest::where('status', 'Approved')->count();
        $chartRejected = InventoryRequest::where('status', 'Rejected')->count();
        $chartPending = InventoryRequest::where('status', 'Pending')->count();

        // Alertas de vencimiento crítico (FEFO < 60 días)
        $expiringProducts = ProductBatch::where('quantity', '>', 0)
            ->whereNotNull('expiration_date')
            ->whereDate('expiration_date', '<=', Carbon::now()->addDays(60))
            ->with('product')
            ->orderBy('expiration_date', 'asc')
            ->get();
        
        $expiringCount = $expiringProducts->count();

        // Volumen mensual de Entradas (StockIn) vs Consumos/Salidas (Requests) de los últimos 6 meses
        $monthlyStats = [];
        for ($i = 5; $i >= 0; $i--) {
            $monthDate = Carbon::now()->subMonths($i);
            $monthName = $monthDate->isoFormat('MMMM');
            
            $entries = \App\Models\StockInItem::whereHas('stockIn', function($q) use ($monthDate) {
                    $q->whereMonth('entry_date', $monthDate->month)
                      ->whereYear('entry_date', $monthDate->year);
                })
                ->sum('quantity');

            $exits = \App\Models\RequestItem::whereHas('request', function($q) use ($monthDate) {
                    $q->where('status', 'Approved')
                      ->whereMonth('processed_at', $monthDate->month)
                      ->whereYear('processed_at', $monthDate->year);
                })
                ->sum('quantity_requested');

            $monthlyStats[] = [
                'month' => ucfirst($monthName),
                'entries' => (int) $entries,
                'exits' => (int) $exits,
            ];
        }

        // Feed de auditoría traducido al español mediante accesor del modelo
        $recentActivity = Activity::with(['causer', 'subject'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return view('admin.dashboards.admin', compact(
            'totalProducts',
            'lowStockCount',
            'chartApproved',
            'chartRejected',
            'chartPending',
            'expiringProducts',
            'expiringCount',
            'monthlyStats',
            'recentActivity'
        ));
    }

    // ----------------------------------------------------------------------
    // 2. DASHBOARD: ADMINISTRADOR DE SALIDAS (SUPERVISOR)
    // ----------------------------------------------------------------------
    private function salidasDashboard()
    {
        // Contador destacado de solicitudes pendientes de aprobación
        $pendingRequestsCount = InventoryRequest::where('status', 'Pending')->count();

        // Alertas de vencimiento prioritario (FEFO) ordenadas ascendentemente por expiration_date
        $expiringProducts = ProductBatch::where('quantity', '>', 0)
            ->whereNotNull('expiration_date')
            ->with('product')
            ->orderBy('expiration_date', 'asc')
            ->limit(10)
            ->get();

        // Solicitudes críticas que requieran descomposición de kits
        $criticalRequests = InventoryRequest::where('status', 'Pending')
            ->whereHas('items', function ($query) {
                $query->whereHas('product', function ($q) {
                    $q->whereColumn('stock', '<', 'request_items.quantity_requested')
                      ->whereHas('parentKits');
                });
            })
            ->with(['items.product.parentKits', 'requester'])
            ->orderBy('created_at', 'asc')
            ->get();

        return view('admin.dashboards.salidas', compact(
            'pendingRequestsCount',
            'expiringProducts',
            'criticalRequests'
        ));
    }

    // ----------------------------------------------------------------------
    // 3. DASHBOARD: COMPRAS Y PROCURA (LOGÍSTICA)
    // ----------------------------------------------------------------------
    private function comprasDashboard()
    {
        // RFQs activas (enviadas a proveedores)
        $activeRfqsCount = RequestForQuotation::where('status', 'sent')->count();

        // Órdenes de Compra (PO) pendientes por recibir mercancía física ('issued')
        $pendingPosCount = PurchaseOrder::where('status', 'issued')->count();

        // Productos que han alcanzado o cruzado su 'min_stock' para reabastecimiento
        $lowStockProducts = Product::where('is_active', true)
            ->whereColumn('stock', '<=', 'min_stock')
            ->orderBy('stock', 'asc')
            ->get();

        return view('admin.dashboards.compras', compact(
            'activeRfqsCount',
            'pendingPosCount',
            'lowStockProducts'
        ));
    }

    // ----------------------------------------------------------------------
    // 4. DASHBOARD: EMPLEADO / SOLICITANTE
    // ----------------------------------------------------------------------
    private function empleadoDashboard($user)
    {
        // Resumen de mis solicitudes creadas (Totales, Aprobadas, Pendientes, Rechazadas)
        $myRequestsCount = InventoryRequest::where('requester_id', $user->id)->count();
        $myApprovedCount = InventoryRequest::where('requester_id', $user->id)->where('status', 'Approved')->count();
        $myPendingCount = InventoryRequest::where('requester_id', $user->id)->where('status', 'Pending')->count();
        $myRejectedCount = InventoryRequest::where('requester_id', $user->id)->where('status', 'Rejected')->count();

        // Tabla minimalista con el estado en tiempo real de mis pedidos (últimos 10)
        $myRecentRequests = InventoryRequest::where('requester_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return view('admin.dashboards.empleado', compact(
            'myRequestsCount',
            'myApprovedCount',
            'myPendingCount',
            'myRejectedCount',
            'myRecentRequests'
        ));
    }
}