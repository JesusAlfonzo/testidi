<?php

namespace App\Http\Controllers;

use App\Models\StockIn;
use App\Models\Product;
use App\Models\Supplier;
use App\Http\Requests\StoreStockInRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class StockInController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:entradas_ver')->only('index');
        $this->middleware('permission:entradas_crear')->only('create', 'store');
        $this->middleware('permission:entradas_eliminar')->only('destroy');
    }

    public function index(Request $request)
    {
        // 1. Cargar listas para los filtros (CORRECCIÓN DEL ERROR)
        $suppliers = Supplier::orderBy('name')->pluck('name', 'id');
        $products = Product::orderBy('name')->pluck('name', 'id');

        // 2. Consulta base
        $query = StockIn::with(['product', 'supplier', 'user'])->orderBy('entry_date', 'desc');

        // 3. Aplicar Filtros
        if ($request->filled('date_from')) {
            $query->whereDate('entry_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('entry_date', '<=', $request->date_to);
        }
        if ($request->filled('supplier_id')) {
            $query->where('supplier_id', $request->supplier_id);
        }
        if ($request->filled('product_id')) {
            $query->where('product_id', $request->product_id);
        }

        // Usamos get() para DataTables client-side
        $stockIns = $query->get();

        // Pasamos todas las variables a la vista
        return view('admin.stock-in.index', compact('stockIns', 'suppliers', 'products'));
    }

    public function create()
    {
        // Necesitamos productos y proveedores para los select, ordenados alfabéticamente
        $products = Product::where('is_active', true)->orderBy('name')->pluck('name', 'id');
        $suppliers = Supplier::orderBy('name')->pluck('name', 'id');

        return view('admin.stock-in.create', compact('products', 'suppliers'));
    }

    public function store(StoreStockInRequest $request)
    {
        $validatedData = $request->validated();

        // 1. Iniciar Transacción
        DB::beginTransaction();
        try {
            // A. Registrar el movimiento de entrada
            $stockIn = StockIn::create($validatedData + ['user_id' => auth()->id()]);

            // B. Obtener y actualizar el producto
            $product = Product::lockForUpdate()->find($validatedData['product_id']);

            $newQuantity = $product->stock + $validatedData['quantity'];
            $newCost = $validatedData['unit_cost'];

            // Actualizamos stock y costo
            $product->stock = $newQuantity;
            $product->cost = $newCost;
            $product->save();

            // 2. Commit
            DB::commit();

            return redirect()->route('admin.stock-in.index')
                ->with('success', '✅ Entrada de stock registrada y producto actualizado con éxito.');

        } catch (\Exception $e) {
            // 3. Rollback
            DB::rollback();
            
            return redirect()->back()
                ->withInput()
                ->with('error', '❌ Error al procesar la entrada de stock: ' . $e->getMessage());
        }
    }

    public function destroy(StockIn $stockIn)
    {
        // La eliminación de movimientos es delicada: debe deshacer el cambio de stock
        DB::beginTransaction();
        try {
            $product = Product::lockForUpdate()->find($stockIn->product_id);

            // Validar que no se deje el stock en negativo al eliminar la entrada
            if ($product->stock < $stockIn->quantity) {
                DB::rollback();
                return redirect()->route('admin.stock-in.index')
                    ->with('error', '❌ No se puede eliminar la entrada: El stock actual es menor a la cantidad ingresada (ya se consumió).');
            }

            // Revertir el stock
            $product->stock -= $stockIn->quantity;
            $product->save();

            $stockIn->delete();

            DB::commit();
            return redirect()->route('admin.stock-in.index')
                ->with('success', '✅ Entrada de stock eliminada y stock del producto corregido.');

        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with('error', '❌ Error al eliminar el movimiento: ' . $e->getMessage());
        }
    }

    // Omitimos show, edit, update
    public function show($id) { return abort(404); }
    public function edit($id) { return abort(404); }
    public function update(Request $request, $id) { return abort(404); }
}