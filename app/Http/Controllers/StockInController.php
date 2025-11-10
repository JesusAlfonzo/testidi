<?php

namespace App\Http\Controllers;

use App\Models\StockIn;
use App\Models\Product;
use App\Models\Supplier;
use App\Http\Requests\StoreStockInRequest;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class StockInController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:entradas_ver')->only('index');
        $this->middleware('permission:entradas_crear')->only('create', 'store');
        $this->middleware('permission:entradas_eliminar')->only('destroy');
    }

    public function index()
    {
        $stockIns = StockIn::with(['product', 'supplier', 'user'])
                            ->orderBy('entry_date', 'desc')
                            ->paginate(15);

        return view('admin.stock-in.index', compact('stockIns'));
    }

    public function create()
    {
        // Necesitamos productos y proveedores para los select
        $products = Product::where('is_active', true)->pluck('name', 'id');
        $suppliers = Supplier::pluck('name', 'id');

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
            $product = Product::lockForUpdate()->find($validatedData['product_id']); // Bloquear fila

            $newQuantity = $product->stock + $validatedData['quantity'];
            $newCost = $validatedData['unit_cost'];

            // NOTA: Se podría implementar el cálculo de Costo Promedio Ponderado (CPP) aquí.
            // Por simplicidad inicial, solo actualizaremos el costo al último costo de entrada.

            $product->stock = $newQuantity;
            $product->cost = $newCost;
            $product->save();

            // 2. Commit si todo fue exitoso
            DB::commit();

            return redirect()->route('admin.stock-in.index')
                             ->with('success', '✅ Entrada de stock registrada y producto actualizado con éxito.');

        } catch (\Exception $e) {
            // 3. Rollback si hay algún error
            DB::rollback();
            // Log::error('Error al registrar entrada de stock: ' . $e->getMessage()); // Opcional
            return redirect()->back()
                             ->withInput()
                             ->with('error', '❌ Error al procesar la entrada de stock. Intente de nuevo.');
        }
    }

    // El resto de los métodos (show, destroy) se implementarán según la necesidad
    public function destroy(StockIn $stockIn)
    {
        // La eliminación de movimientos es delicada: debe deshacer el cambio de stock
        DB::beginTransaction();
        try {
            $product = Product::lockForUpdate()->find($stockIn->product_id);

            if ($product->stock < $stockIn->quantity) {
                DB::rollback();
                return redirect()->route('admin.stock-in.index')->with('error', '❌ No se puede eliminar la entrada: El stock actual es menor a la cantidad ingresada.');
            }

            $product->stock -= $stockIn->quantity;
            $product->save();

            $stockIn->delete();

            DB::commit();
            return redirect()->route('admin.stock-in.index')->with('success', '✅ Entrada de stock eliminada y stock del producto corregido.');

        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with('error', '❌ Error al eliminar el movimiento: ' . $e->getMessage());
        }
    }

    // Omitimos show, edit, update, ya que los movimientos no se editan.
    public function show($id) { return abort(404); }
    public function edit($id) { return abort(404); }
    public function update(Request $request, $id) { return abort(404); }
}
