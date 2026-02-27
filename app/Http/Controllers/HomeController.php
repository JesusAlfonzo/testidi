<?php

namespace App\Http\Controllers;

use App\Models\InventoryRequest; 
use App\Models\Product;
use App\Models\Category;
use App\Models\Brand;
use App\Models\Unit;
use App\Models\Location;
use App\Models\Supplier;
use App\Models\PurchaseQuote;
use App\Models\PurchaseOrder;
use App\Models\User;
use App\Models\RequestForQuotation;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Spatie\Permission\Models\Role;
use Spatie\Activitylog\Models\Activity;

class HomeController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'can:dashboard_acceso']);
    }

    /**
     * Método principal que despacha al usuario a su dashboard correspondiente.
     */
    public function index()
    {
        $user = Auth::user();

        // 1. Superadmin (Vista Global + Sistema)
        if ($user->hasRole('Superadmin') || $user->hasRole('Super Administrador')) {
            return $this->superAdminDashboard();
        } 
        // 2. Logística (Vista Operativa de Inventario)
        elseif ($user->hasRole('Logistica') || $user->hasRole('Encargado Inventario')) {
            return $this->logisticaDashboard();
        } 
        // 3. Supervisor (Vista de Auditoría/Métricas)
        elseif ($user->hasRole('Supervisor')) {
            return $this->supervisorDashboard();
        } 
        // 4. Solicitante (Vista Personal)
        elseif ($user->hasRole('Solicitante')) {
            return $this->solicitanteDashboard($user);
        }

        // 5. Fallback: Vista de respaldo para usuarios sin rol definido
        return view('home'); 
    }

    // ----------------------------------------------------------------------
    // LÓGICA DE VISTAS POR ROL
    // ----------------------------------------------------------------------

    private function superAdminDashboard()
    {
        $data = $this->getGlobalInventoryStats();
        
        $data['usersCount'] = User::count();
        $data['rolesCount'] = Role::count();
        
        return view('admin.dashboards.superadmin', $data);
    }

    private function logisticaDashboard()
    {
        $data = $this->getGlobalInventoryStats();
        return view('admin.dashboards.logistica', $data);
    }

    private function supervisorDashboard()
    {
        $data = $this->getGlobalInventoryStats();
        return view('admin.dashboards.supervisor', $data);
    }

    private function solicitanteDashboard($user)
    {
        $myPendingCount = InventoryRequest::where('requester_id', $user->id)->where('status', 'Pending')->count();
        $myApprovedCount = InventoryRequest::where('requester_id', $user->id)->where('status', 'Approved')->count();
        $myRejectedCount = InventoryRequest::where('requester_id', $user->id)->where('status', 'Rejected')->count();
        
        $myRecentRequests = InventoryRequest::where('requester_id', $user->id)
                                            ->orderBy('created_at', 'desc')
                                            ->take(5)
                                            ->get();

        return view('admin.dashboards.solicitante', compact(
            'myPendingCount', 
            'myApprovedCount', 
            'myRejectedCount', 
            'myRecentRequests'
        ));
    }

    // ----------------------------------------------------------------------
    // HELPER: ESTADÍSTICAS GLOBALES (Para Admin, Logística, Supervisor)
    // ----------------------------------------------------------------------
    private function getGlobalInventoryStats()
    {
        $sevenDaysAgo = Carbon::now()->subDays(7);

        // 1. Datos para Gráfico de Líneas (Solicitudes últimos 7 días)
        $dailyRequests = InventoryRequest::select(
                DB::raw('DATE(requested_at) as date'), 
                DB::raw('count(*) as count')
            )
            ->where('requested_at', '>=', $sevenDaysAgo)
            ->groupBy('date')
            ->orderBy('date')
            ->get();
            
        $lineChartLabels = [];
        $lineChartData = [];
        
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i)->format('Y-m-d');
            $record = $dailyRequests->firstWhere('date', $date);
            $lineChartLabels[] = Carbon::parse($date)->isoFormat('ddd D'); 
            $lineChartData[] = $record ? $record->count : 0;
        }

        // 2. Gráfico de Cotizaciones (últimos 30 días)
        $quoteStats = [
            'pending' => PurchaseQuote::where('status', 'pending')->count(),
            'selected' => PurchaseQuote::where('status', 'selected')->count(),
            'approved' => PurchaseQuote::where('status', 'approved')->count(),
            'rejected' => PurchaseQuote::where('status', 'rejected')->count(),
            'converted' => PurchaseQuote::where('status', 'converted')->count(),
        ];

        // 3. Gráfico de Órdenes de Compra
        $orderStats = [
            'draft' => PurchaseOrder::where('status', 'draft')->count(),
            'issued' => PurchaseOrder::where('status', 'issued')->count(),
            'received' => PurchaseOrder::where('status', 'received')->count(),
            'cancelled' => PurchaseOrder::where('status', 'cancelled')->count(),
        ];

        // 4. RFQ stats
        $rfqStats = [
            'draft' => RequestForQuotation::where('status', 'draft')->count(),
            'sent' => RequestForQuotation::where('status', 'sent')->count(),
            'partial' => RequestForQuotation::where('status', 'partial')->count(),
            'completed' => RequestForQuotation::where('status', 'completed')->count(),
            'cancelled' => RequestForQuotation::where('status', 'cancelled')->count(),
        ];

        // 5. Estadísticas de Inventario
        $inventoryStats = [
            'products' => Product::count(),
            'categories' => Category::count(),
            'brands' => Brand::count(),
            'units' => Unit::count(),
            'locations' => Location::count(),
            'suppliers' => Supplier::count(),
        ];

        // 6. Actividad reciente del sistema (últimos 10)
        $recentActivity = Activity::with('causer')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return [
            // KPIs Inventario
            'totalProducts' => Product::where('is_active', true)->count(),
            'totalStockValue' => Product::where('is_active', true)->sum(DB::raw('stock * cost')),
            'lowStockCount' => Product::where('is_active', true)->whereColumn('stock', '<=', 'min_stock')->count(),
            'pendingRequests' => InventoryRequest::where('status', 'Pending')->count(),
            'approvedRequestsToday' => InventoryRequest::where('status', 'Approved')->whereDate('processed_at', Carbon::today())->count(),
            
            // Stats Inventario
            'inventoryStats' => $inventoryStats,
            
            // Stats Cotizaciones
            'quoteStats' => $quoteStats,
            
            // Stats Órdenes de Compra
            'orderStats' => $orderStats,
            
            // Stats RFQs
            'rfqStats' => $rfqStats,
            
            // Datos Gráfico Donut Solicitudes
            'chartApproved' => InventoryRequest::where('status', 'Approved')->count(),
            'chartRejected' => InventoryRequest::where('status', 'Rejected')->count(),
            'chartPending' => InventoryRequest::where('status', 'Pending')->count(),
            
            // Datos Gráfico Línea
            'lineChartLabels' => $lineChartLabels,
            'lineChartData' => $lineChartData,
            
            // Tabla Top 5 Stock Bajo
            'lowStockProducts' => Product::where('is_active', true)
                                         ->whereColumn('stock', '<=', 'min_stock')
                                         ->orderBy('stock', 'asc')
                                         ->limit(5)
                                         ->get(),

            // Actividad reciente
            'recentActivity' => $recentActivity,
        ];
    }
}