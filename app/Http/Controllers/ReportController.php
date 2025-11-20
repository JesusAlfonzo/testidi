<?php

namespace App\Http\Controllers;

use App\Models\InventoryRequest as SolicitudModel; // Alias correcto
use App\Models\Product;
// use App\Models\StockIn; // YA NO ES NECESARIO AQUÍ (Lo maneja el servicio)
// use Illuminate\Support\Facades\DB; // YA NO ES NECESARIO AQUÍ
// use Illuminate\Support\Str; // YA NO ES NECESARIO AQUÍ
use App\Services\KardexService; // 🔑 IMPORTANTE: Importar el servicio
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:reportes_ver')->only(['stockReport', 'requestsReport']);
        $this->middleware('can:reportes_kardex')->only('kardexReport');
    }

    // Muestra el reporte de stock actual
    public function stockReport()
    {
        $products = Product::with('unit')
            ->orderBy('name', 'asc')
            ->paginate(20);

        return view('admin.reports.stock', compact('products'));
    }

    // Muestra el reporte de todas las solicitudes de inventario
    public function requestsReport()
    {
        $requests = SolicitudModel::with(['requester', 'approver'])
            ->orderBy('requested_at', 'desc')
            ->paginate(20);

        return view('admin.reports.requests', compact('requests'));
    }

    /**
     * Muestra el Kardex de un producto específico.
     * La lógica compleja se delega al KardexService.
     */
    public function kardexReport(Product $product, KardexService $kardexService)
    {
        // 🔑 Delegamos la generación del array de movimientos al servicio
        $kardex = $kardexService->generateKardex($product);

        return view('admin.reports.kardex', compact('product', 'kardex'));
    }
}
