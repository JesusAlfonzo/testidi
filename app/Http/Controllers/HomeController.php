<?php

namespace App\Http\Controllers;

use App\Models\InventoryRequest; // 🔑 Modelo correcto
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        // Define el periodo para el gráfico (Últimos 7 días)
        $sevenDaysAgo = Carbon::now()->subDays(7);

        // --- 1. KPIs DE TARJETAS (Lógica original adaptada) ---
        $totalStockValue = Product::where('is_active', true)
                            ->sum(DB::raw('stock * cost')); // Usando tu campo 'cost'

        $lowStockCount = Product::where('is_active', true)
                            ->whereColumn('stock', '<=', 'min_stock')
                            ->count();

        $pendingRequests = InventoryRequest::where('status', 'Pending')->count(); // Usando 'Pending'

        $approvedRequestsToday = InventoryRequest::where('status', 'Approved') // Usando 'Approved'
                                    ->whereDate('processed_at', Carbon::today()) // Usando 'processed_at'
                                    ->count();

        // --- 2. DATOS GRÁFICO DONUT (Lógica original adaptada) ---
        $chartApproved = InventoryRequest::where('status', 'Approved')
                                ->where('processed_at', '>=', $sevenDaysAgo)
                                ->count();
        $chartRejected = InventoryRequest::where('status', 'Rejected') // Usando 'Rejected'
                                ->where('processed_at', '>=', $sevenDaysAgo)
                                ->count();
        // Mantenemos tu lógica de mostrar todas las pendientes en el gráfico
        $chartPending = $pendingRequests; 

        // --- 3. DATOS NUEVOS PARA EL DASHBOARD MODERNO ---

        // A. Tabla de Stock Bajo (Top 5)
        $lowStockProducts = Product::where('is_active', true)
            ->whereColumn('stock', '<=', 'min_stock')
            ->orderBy('stock', 'asc')
            ->limit(5)
            ->get();

        // B. Gráfico de Líneas (Tendencia de solicitudes CREADAS en 7 días)
        $lineChartData = [];
        $lineChartLabels = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $lineChartLabels[] = $date->isoFormat('ddd'); // 'Lun', 'Mar', 'Mié'
            // Contamos solicitudes CREADAS en esa fecha
            $lineChartData[] = InventoryRequest::whereDate('created_at', $date)->count();
        }

        // C. Feed de Actividad Reciente (Últimas 5 procesadas)
        $recentProcessedRequests = InventoryRequest::whereIn('status', ['Approved', 'Rejected'])
            // ✨ CORRECCIÓN: Cambiamos 'user' por 'requester' para que coincida con el modelo
            ->with('requester') 
            ->latest('processed_at') // Ordenar por 'processed_at'
            ->limit(5)
            ->get();

        // --- 4. Pasar todas las métricas a la vista 'home' ---
        return view('home', compact(
            'totalStockValue',
            'lowStockCount',
            'pendingRequests',
            'approvedRequestsToday',
            'chartApproved',
            'chartRejected',
            'chartPending',
            'lowStockProducts',        // <-- NUEVA
            'lineChartLabels',         // <-- NUEVA
            'lineChartData',           // <-- NUEVA
            'recentProcessedRequests'  // <-- NUEVA
        ));
    }
}