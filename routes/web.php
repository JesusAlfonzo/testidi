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
use App\Http\Controllers\QuotationController;
use App\Http\Controllers\RequestForQuotationController;


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

    // MÓDULOS MAESTROS
    Route::resource('categories', CategoryController::class);
    Route::resource('units', UnitController::class);
    Route::resource('locations', LocationController::class);
    Route::resource('brands', BrandController::class);
    Route::resource('suppliers', SupplierController::class);

    // MÓDULO INVENTARIO
    Route::resource('products', ProductController::class);

    // MÓDULO KITS
    Route::resource('kits', KitController::class);
    Route::post('kits/{kit}/components', [KitController::class, 'syncComponents'])->name('kits.sync_components');

    // MÓDULO RFQ (Solicitudes de Cotización)
    Route::resource('rfq', RequestForQuotationController::class);
    Route::get('rfq/{rfq}/pdf', [RequestForQuotationController::class, 'pdf'])->name('rfq.pdf');
    Route::post('rfq/{rfq}/mark-sent', [RequestForQuotationController::class, 'markAsSent'])->name('rfq.mark-sent');
    Route::post('rfq/{rfq}/mark-closed', [RequestForQuotationController::class, 'markAsClosed'])->name('rfq.mark-closed');
    Route::post('rfq/{rfq}/cancel', [RequestForQuotationController::class, 'cancel'])->name('rfq.cancel');

    // MÓDULO ORDENES DE COMPRAS
    Route::resource('purchaseOrders', PurchaseOrdersController::class);
    Route::get('purchaseOrders/{purchaseOrder}/pdf', [PurchaseOrdersController::class, 'pdf'])->name('purchaseOrders.pdf');
    Route::post('purchaseOrders/{purchaseOrder}/issue', [PurchaseOrdersController::class, 'issue'])->name('purchaseOrders.issue');
    Route::post('purchaseOrders/{purchaseOrder}/complete', [PurchaseOrdersController::class, 'complete'])->name('purchaseOrders.complete');
    Route::post('purchaseOrders/{purchaseOrder}/cancel', [PurchaseOrdersController::class, 'cancel'])->name('purchaseOrders.cancel');

    // MÓDULO COTIZACIONES
    Route::resource('quotations', QuotationController::class);
    Route::get('quotations/{quotation}/pdf', [QuotationController::class, 'pdf'])->name('quotations.pdf');
    Route::post('quotations/{quotation}/select', [QuotationController::class, 'select'])->name('quotations.select');
    Route::post('quotations/{quotation}/approve', [QuotationController::class, 'approve'])->name('quotations.approve');
    Route::post('quotations/{quotation}/reject', [QuotationController::class, 'reject'])->name('quotations.reject');
    Route::post('quotations/{quotation}/convert-supplier', [QuotationController::class, 'convertToSupplier'])->name('quotations.convert-supplier');

    // MOVIMIENTOS - ENTRADAS DE STOCK
    Route::resource('stock-in', StockInController::class)->except(['edit', 'update']);

    // MOVIMIENTOS - SOLICITUDES DE SALIDA
    // 1. Ruta especializada para APROBACIÓN/RECHAZO
    Route::post('requests/{request}/process', [RequestController::class, 'process'])->name('requests.process');

    // 2. Recurso principal
    Route::resource('requests', RequestController::class)
        ->except(['edit', 'update'])
        ->parameters([
            'requests' => 'request'
        ]);

    // 🔑 NUEVA RUTA: AUDITORÍA DEL SISTEMA
    Route::get('audit-logs', [ActivityLogController::class, 'index'])
        ->name('audit.index')
        ->middleware('can:auditoria_ver'); // Requiere el permiso 'auditoria_ver'

    // RUTAS DE REPORTES
    Route::group(['prefix' => 'reports', 'as' => 'reports.'], function () {

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
            ->middleware('can:kardex_ver');
        Route::get('kardex/{product}/excel', [ReportController::class, 'exportKardexExcel'])
            ->name('kardex.excel')
            ->middleware('can:kardex_ver');
        Route::get('kardex/{product}/pdf', [ReportController::class, 'exportKardexPdf'])
            ->name('kardex.pdf')
            ->middleware('can:kardex_ver');
    });
});
