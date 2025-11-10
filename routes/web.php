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

Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

// Grupo principal de rutas de administración, autenticadas y con prefijo 'admin'
Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {

    // GESTIÓN DE USUARIOS
    Route::resource('users', UserController::class);

    // MÓDULOS MAESTROS (CRUD BÁSICO)
    Route::resource('categories', CategoryController::class);
    Route::resource('units', UnitController::class);
    Route::resource('locations', LocationController::class);
    Route::resource('brands', BrandController::class);
    Route::resource('suppliers', SupplierController::class);

    // MÓDULO INVENTARIO
    Route::resource('products', ProductController::class);

    // MOVIMIENTOS - ENTRADAS DE STOCK
    Route::resource('stock-in', StockInController::class)->except(['edit', 'update']);

    // 🔑 CLAVE: Ruta especializada para APROBACIÓN/RECHAZO
    // Esta ruta personalizada debe ir ANTES del Route::resource
    // Usamos el parámetro singular {request} para Route Model Binding
    Route::post('requests/{request}/process', [RequestController::class, 'process'])->name('requests.process');

    // MOVIMIENTOS - SOLICITUDES DE INVENTARIO (SALIDAS CON APROBACIÓN)
    // Se define el parámetro singular 'request' para que coincida con la ruta process y el controlador
    Route::resource('requests', RequestController::class)
        ->except(['edit', 'update'])
        ->parameters([
            'requests' => 'request'
        ]);

    // RUTAS DE REPORTES
    Route::group(['prefix' => 'reports', 'as' => 'reports.'], function () {

        // Reporte de Stock Actual
        Route::get('stock', [ReportController::class, 'stockReport'])
            ->name('stock')
            ->middleware('can:reportes_stock');

        // Reporte de Solicitudes/Movimientos
        Route::get('requests', [ReportController::class, 'requestsReport'])
            ->name('requests')
            ->middleware('can:reportes_movimientos');

        // Reporte Kardex
        Route::get('kardex/{product}', [ReportController::class, 'kardexReport'])
            ->name('kardex')
            ->middleware('can:kardex_ver');

    });
});
