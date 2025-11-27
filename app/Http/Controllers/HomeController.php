<?php

namespace App\Http\Controllers;

use App\Models\InventoryRequest; 
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Spatie\Permission\Models\Role;

class HomeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Método principal que despacha al usuario a su dashboard correspondiente.
     */
    public function index()
    {
        $user = Auth::user();

        if ($user->hasRole('Superadmin') || $user->hasRole('Super Administrador')) {
            return $this->superAdminDashboard();
        } 
        elseif ($user->hasRole('Logistica') || $user->hasRole('Encargado Inventario')) {
            return $this->logisticaDashboard();
        } 
        elseif ($user->hasRole('Supervisor')) {
            return $this->supervisorDashboard();
        } 
        elseif ($user->hasRole('Solicitante')) {
            return $this->solicitanteDashboard($user);
        }

        // Vista de respaldo para usuarios sin rol definido
        return view('home'); 
    }

    // ----------------------------------------------------------------------
    // LÓGICA DE VISTAS POR ROL
    // ----------------------------------------------------------------------

    private function superAdminDashboard()
    {
        // Obtiene estadísticas globales de inventario y gráficos
        $data = $this->getGlobalInventoryStats();
        
        // Datos exclusivos de Superadmin (Sistema)
        $data['usersCount'] = User::count();
        $data['rolesCount'] = Role::count();
        
        return view('admin.dashboards.superadmin', $data);
    }

    private function logisticaDashboard()
    {
        // Logística necesita datos operativos de inventario
        $data = $this->getGlobalInventoryStats();
        return view('admin.dashboards.logistica', $data);
    }

    private function supervisorDashboard()
    {
        // Supervisor ve métricas similares a logística/admin
        $data = $this->getGlobalInventoryStats();
        return view('admin.dashboards.supervisor', $data);
    }

    private function solicitanteDashboard($user)
    {
        // El Solicitante SOLO ve sus propios datos. Consulta muy ligera.
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
        
        // Rellenar días vacíos con 0
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i)->format('Y-m-d');
            $record = $dailyRequests->firstWhere('date', $date);
            // Formato de etiqueta: "Lun 12"
            $lineChartLabels[] = Carbon::parse($date)->isoFormat('ddd D'); 
            $lineChartData[] = $record ? $record->count : 0;
        }

        return [
            // KPIs
            'totalProducts' => Product::where('is_active', true)->count(),
            'totalStockValue' => Product::where('is_active', true)->sum(DB::raw('stock * cost')),
            'lowStockCount' => Product::where('is_active', true)->whereColumn('stock', '<=', 'min_stock')->count(),
            'pendingRequests' => InventoryRequest::where('status', 'Pending')->count(),
            'approvedRequestsToday' => InventoryRequest::where('status', 'Approved')->whereDate('processed_at', Carbon::today())->count(),
            
            // Datos Gráfico Donut
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
        ];
    }
}