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
        $data = $this->getOperationalStats();
        return view('admin.dashboards.logistica', $data);
    }

    private function supervisorDashboard()
    {
        $data = $this->getOperationalStats();
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
    // HELPER: ESTADÍSTICAS OPERATIVAS (Sin datos financieros - para Logística y Supervisor)
    // ----------------------------------------------------------------------
    private function getOperationalStats()
    {
        $sevenDaysAgo = Carbon::now()->subDays(7);

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

        return [
            'totalProducts' => Product::where('is_active', true)->count(),
            'lowStockCount' => Product::where('is_active', true)->whereColumn('stock', '<=', 'min_stock')->count(),
            'pendingRequests' => InventoryRequest::where('status', 'Pending')->count(),
            'approvedRequestsToday' => InventoryRequest::where('status', 'Approved')->whereDate('processed_at', Carbon::today())->count(),
            
            'inventoryStats' => [
                'products' => Product::count(),
                'categories' => Category::count(),
                'brands' => Brand::count(),
                'units' => Unit::count(),
                'locations' => Location::count(),
                'suppliers' => Supplier::count(),
            ],
            
            'chartApproved' => InventoryRequest::where('status', 'Approved')->count(),
            'chartRejected' => InventoryRequest::where('status', 'Rejected')->count(),
            'chartPending' => InventoryRequest::where('status', 'Pending')->count(),
            
            'lineChartLabels' => $lineChartLabels,
            'lineChartData' => $lineChartData,
            
            'lowStockProducts' => Product::where('is_active', true)
                                         ->whereColumn('stock', '<=', 'min_stock')
                                         ->orderBy('stock', 'asc')
                                         ->limit(5)
                                         ->get(),
            
            // Alerta de productos por vencer
            'expiringProducts' => ProductBatch::where('quantity', '>', 0)
                ->whereDate('expiry_date', '<=', Carbon::now()->addDays(30))
                ->whereDate('expiry_date', '>=', Carbon::now())
                ->with('product')
                ->orderBy('expiry_date', 'asc')
                ->limit(5)
                ->get(),
        ];
    }

    // ----------------------------------------------------------------------
    // HELPER: ESTADÍSTICAS GLOBALES (Para Admin)
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

        // 2-5. OPTIMIZADO: Una sola consulta por modelo con CASE
        $orderStats = PurchaseOrder::select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();
        
        $rfqStats = RequestForQuotation::select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();
        
        // 6. Actividad reciente del sistema (últimos 10)
        $user = Auth::user();
        $activityQuery = Activity::with('causer');
        
        // Si no es admin, solo mostrar su propia actividad
        if (!$user->isSuperAdmin()) {
            $activityQuery->where('causer_id', $user->id);
        }
        
        $recentActivity = $activityQuery->orderBy('created_at', 'desc')->limit(10)->get();

        return [
            // KPIs Inventario
            'totalProducts' => Product::where('is_active', true)->count(),
            'totalStockValue' => Product::where('is_active', true)->sum(DB::raw('stock * cost')),
            'lowStockCount' => Product::where('is_active', true)->whereColumn('stock', '<=', 'min_stock')->count(),
            'pendingRequests' => InventoryRequest::where('status', 'Pending')->count(),
            'approvedRequestsToday' => InventoryRequest::where('status', 'Approved')->whereDate('processed_at', Carbon::today())->count(),
            
            // Stats Inventario (consultas simples, necesarias)
            'inventoryStats' => [
                'products' => Product::count(),
                'categories' => Category::count(),
                'brands' => Brand::count(),
                'units' => Unit::count(),
                'locations' => Location::count(),
                'suppliers' => Supplier::count(),
            ],
            
            // Stats Órdenes de Compra
            'orderStats' => [
                'draft' => $orderStats['draft'] ?? 0,
                'issued' => $orderStats['issued'] ?? 0,
                'received' => $orderStats['received'] ?? 0,
                'cancelled' => $orderStats['cancelled'] ?? 0,
            ],
            
            // Stats RFQs
            'rfqStats' => [
                'draft' => $rfqStats['draft'] ?? 0,
                'sent' => $rfqStats['sent'] ?? 0,
                'partial' => $rfqStats['partial'] ?? 0,
                'completed' => $rfqStats['completed'] ?? 0,
                'cancelled' => $rfqStats['cancelled'] ?? 0,
            ],
            
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

            // Alerta de productos por vencer
            'expiringProducts' => ProductBatch::where('quantity', '>', 0)
                ->whereDate('expiry_date', '<=', Carbon::now()->addDays(30))
                ->whereDate('expiry_date', '>=', Carbon::now())
                ->with('product')
                ->orderBy('expiry_date', 'asc')
                ->limit(5)
                ->get(),

            // Actividad reciente
            'recentActivity' => $recentActivity,
        ];
    }
}