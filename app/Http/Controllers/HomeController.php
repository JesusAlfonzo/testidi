<?php

namespace App\Http\Controllers;

use App\Models\InventoryRequest; // 🔑 Usamos el nuevo modelo renombrado
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class HomeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        // Define el periodo para el gráfico (Últimos 7 días)
        $sevenDaysAgo = Carbon::now()->subDays(7);

        // 1. Obtener Métricas de Inventario
        $totalProducts = Product::where('is_active', true)->count();
        $totalStockValue = Product::where('is_active', true)
                                  ->sum(DB::raw('stock * cost'));
        $lowStockCount = Product::where('is_active', true)
                                  ->whereColumn('stock', '<=', 'min_stock')
                                  ->count();

        // 2. Obtener Métricas de Solicitudes
        $pendingRequests = InventoryRequest::where('status', 'Pending')->count();
        $approvedRequestsToday = InventoryRequest::where('status', 'Approved')
                                                ->whereDate('processed_at', Carbon::today())
                                                ->count();

        // 3. Métricas para el Gráfico (Últimos 7 días)
        $chartApproved = InventoryRequest::where('status', 'Approved')
                                            ->where('processed_at', '>=', $sevenDaysAgo)
                                            ->count();
        $chartRejected = InventoryRequest::where('status', 'Rejected')
                                            ->where('processed_at', '>=', $sevenDaysAgo)
                                            ->count();
        $chartPending = $pendingRequests; // Mantenemos las pendientes totales, que suelen ser pocas

        // 4. Pasar las métricas a la vista
        return view('home', compact(
            'totalProducts',
            'totalStockValue',
            'lowStockCount',
            'pendingRequests',
            'approvedRequestsToday',
            'chartApproved',
            'chartRejected',
            'chartPending'
        ));
    }
}
