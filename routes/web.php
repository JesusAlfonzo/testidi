<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

use App\Http\Controllers\UserController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\UnitController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\BrandController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\StockInController;
use App\Http\Controllers\RequestController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\KitController;
use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\PurchaseOrdersController;
use App\Http\Controllers\RequestForQuotationController;
use App\Http\Controllers\RoleController;


/*
|--------------------------------------------------------------------------
| Rutas Web
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return view('welcome');
});

// 🔒 RUTAS DE AUTENTICACIÓN
Auth::routes(['register' => false]);

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

// --- GRUPO PRINCIPAL DE ADMINISTRACIÓN ---
Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {

    // GESTIÓN DE USUARIOS
    Route::resource('users', UserController::class);
    Route::resource('roles', RoleController::class);

    // MÓDULOS MAESTROS
    Route::resource('categories', CategoryController::class);
    Route::resource('units', UnitController::class);
    Route::resource('locations', LocationController::class);
    Route::resource('brands', BrandController::class);
    Route::resource('suppliers', SupplierController::class);
    Route::post('suppliers/quick-store', [SupplierController::class, 'quickStore'])->name('suppliers.quick-store');

    // MÓDULO INVENTARIO
    Route::resource('products', ProductController::class);
    Route::post('products/quick-store', [ProductController::class, 'quickStore'])->name('products.quick-store');
    Route::post('products/quick-store-kit', [ProductController::class, 'quickStoreKit'])->name('products.quick-store-kit');
    Route::post('products/{product}/decompose', [ProductController::class, 'decompose'])->name('products.decompose');
    Route::post('products/{product}/unpack', [ProductController::class, 'unpack'])->name('products.unpack');
    Route::get('products/search', [ProductController::class, 'search'])->name('products.search');

    // MÓDULO KITS
    Route::resource('kits', KitController::class);
    Route::post('kits/{kit}/components', [KitController::class, 'syncComponents'])->name('kits.sync_components');

    // MÓDULO RFQ (Solicitudes de Cotización)
    Route::resource('rfq', RequestForQuotationController::class);
    Route::get('rfq/{rfq}/pdf', [RequestForQuotationController::class, 'pdf'])->name('rfq.pdf');
    Route::post('rfq/{rfq}/mark-sent', [RequestForQuotationController::class, 'markAsSent'])->name('rfq.mark-sent');
    Route::post('rfq/{rfq}/mark-closed', [RequestForQuotationController::class, 'markAsClosed'])->name('rfq.mark-closed');
    Route::post('rfq/{rfq}/cancel', [RequestForQuotationController::class, 'cancel'])->name('rfq.cancel');
    Route::post('rfq/{rfq}/supplier-offers', [RequestForQuotationController::class, 'saveSupplierOffer'])->name('rfq.save-supplier-offer');
    Route::match(['get', 'post'], 'rfq/{rfq}/convert-to-po', [RequestForQuotationController::class, 'convertToPO'])->name('rfq.convert-to-po');
    Route::post('rfq/{rfq}/store-po', [RequestForQuotationController::class, 'storePOFromRFQ'])->name('rfq.store-po');

    // MÓDULO ORDENES DE COMPRAS
    Route::get('purchaseOrders/search-suppliers', [PurchaseOrdersController::class, 'searchSuppliers'])->name('purchaseOrders.searchSuppliers');
    Route::get('purchaseOrders/search-products', [PurchaseOrdersController::class, 'searchProducts'])->name('purchaseOrders.searchProducts');
    Route::resource('purchaseOrders', PurchaseOrdersController::class);
    Route::get('purchaseOrders/{purchaseOrder}/pdf', [PurchaseOrdersController::class, 'pdf'])->name('purchaseOrders.pdf');
    Route::post('purchaseOrders/{purchaseOrder}/issue', [PurchaseOrdersController::class, 'issue'])->name('purchaseOrders.issue');
    Route::post('purchaseOrders/{purchaseOrder}/complete', [PurchaseOrdersController::class, 'complete'])->name('purchaseOrders.complete');
    Route::post('purchaseOrders/{purchaseOrder}/cancel', [PurchaseOrdersController::class, 'cancel'])->name('purchaseOrders.cancel');

    // MOVIMIENTOS - ENTRADAS DE STOCK
    // Rutas personalizadas PRIMERO (antes del resource)
    Route::get('stock-in/{stockIn}/create-replacement', [StockInController::class, "createReplacement"])->name('stock-in.create-replacement');
    Route::post('stock-in/store-replacement', [StockInController::class, "storeReplacement"])->name('stock-in.store-replacement');
    Route::get('stock-in/{stockIn}/pdf', [StockInController::class, "downloadPDF"])->name('stock-in.pdf');
    Route::post('stock-in/{stockIn}/revert-items', [StockInController::class, "revertItems"])->name('stock-in.revert-items');
    // Resource routes DESPUÉS
    Route::resource('stock-in', StockInController::class);

    // MOVIMIENTOS - SOLICITUDES DE SALIDA
    Route::middleware(['solicitud_schedule'])->group(function () {
        // 1. Ruta especializada para APROBACIÓN/RECHAZO (AJAX y tradicional)
        Route::post('requests/{request}/approve', [RequestController::class, 'approve'])->name('requests.approve');
        Route::post('requests/{request}/reject', [RequestController::class, 'reject'])->name('requests.reject');
        Route::post('requests/{request}/process', [RequestController::class, 'process'])->name('requests.process');

        // 2. Ruta para PDF individual
        Route::get('requests/{request}/pdf', [RequestController::class, 'pdf'])->name('requests.pdf');
        Route::get('dispatches/{dispatch}/pdf', [RequestController::class, 'dispatchPdf'])->name('dispatches.pdf');

        // 3. Recurso principal
        Route::resource('requests', RequestController::class)
            ->except(['edit', 'update'])
            ->parameters([
                'requests' => 'request'
            ]);
    });

    // 🛡️ AUDITORÍA DEL SISTEMA (Solo Lectura)
    Route::get('audit-logs', [ActivityLogController::class, 'index'])
        ->name('audit.index')
        ->middleware('can:auditoria_ver');
    Route::get('audit-logs/{activityLog}', [ActivityLogController::class, 'show'])
        ->name('audit.show')
        ->middleware('can:auditoria_ver');

    // RUTAS DE REPORTES
    Route::group(['prefix' => 'reports', 'as' => 'reports.'], function () {

        // Generador de Reportes Dinámico
        Route::get('/', [ReportController::class, 'index'])
            ->name('index')
            ->middleware('can:reportes_ver');
        Route::post('export', [ReportController::class, 'generatePdf'])
            ->name('export')
            ->middleware('can:reportes_ver');

        // Reporte de Stock
        Route::get('stock', [ReportController::class, 'stockReport'])
            ->name('stock')
            ->middleware('can:reportes_stock');
        Route::get('stock/excel', [ReportController::class, 'exportStockExcel'])
            ->name('stock.excel')
            ->middleware('can:reportes_stock');
        Route::get('stock/pdf', [ReportController::class, 'exportStockPdf'])
            ->name('stock.pdf')
            ->middleware('can:reportes_stock');

        // Reporte de Solicitudes
        Route::get('requests', [ReportController::class, 'requestsReport'])
            ->name('requests')
            ->middleware('can:reportes_movimientos');
        Route::get('requests/excel', [ReportController::class, 'exportRequestsExcel'])
            ->name('requests.excel')
            ->middleware('can:reportes_movimientos');
        Route::get('requests/pdf', [ReportController::class, 'exportRequestsPdf'])
            ->name('requests.pdf')
            ->middleware('can:reportes_movimientos');

        // Reporte Kardex
        Route::get('kardex/{product}', [ReportController::class, 'kardexReport'])
            ->name('kardex')
            ->middleware('can:reportes_kardex');
        Route::get('kardex/{product}/excel', [ReportController::class, 'exportKardexExcel'])
            ->name('kardex.excel')
            ->middleware('can:reportes_kardex');
        Route::get('kardex/{product}/pdf', [ReportController::class, 'exportKardexPdf'])
            ->name('kardex.pdf')
            ->middleware('can:reportes_kardex');
    });
});


