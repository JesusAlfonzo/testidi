<?php

namespace App\Http\Controllers;

use App\Models\StockIn;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
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
        $suppliers = Supplier::orderBy('name')->pluck('name', 'id');
        $products = Product::orderBy('name')->pluck('name', 'id');

        $query = StockIn::with(['product', 'supplier', 'user', 'purchaseOrder'])->orderBy('entry_date', 'desc');

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

        $stockIns = $query->get();

        return view('admin.stock-in.index', compact('stockIns', 'suppliers', 'products'));
    }

    public function create(Request $request)
    {
        $products = Product::where('is_active', true)->orderBy('name')->pluck('name', 'id');
        $suppliers = Supplier::orderBy('name')->pluck('name', 'id');

        $order = null;
        $orderItem = null;

        if ($request->filled('order')) {
            $order = PurchaseOrder::with(['supplier', 'items.product'])
                ->where('status', 'issued')
                ->find($request->order);

            if ($order && $request->filled('item')) {
                $orderItem = $order->items->where('id', $request->item)->where('quantity_received', '<', \DB::raw('quantity'))->first();
            }
        }

        return view('admin.stock-in.create', compact('products', 'suppliers', 'order', 'orderItem'));
    }

    public function store(StoreStockInRequest $request)
    {
        $validatedData = $request->validated();

        DB::beginTransaction();
        try {
            $stockIn = StockIn::create($validatedData + ['user_id' => auth()->id()]);

            $product = Product::lockForUpdate()->find($validatedData['product_id']);

            $newQuantity = $product->stock + $validatedData['quantity'];
            $newCost = $validatedData['unit_cost'];

            $product->stock = $newQuantity;
            $product->cost = $newCost;
            $product->save();

            if (!empty($validatedData['purchase_order_id'])) {
                $order = PurchaseOrder::find($validatedData['purchase_order_id']);
                $orderItem = $order->items()
                    ->where('product_id', $validatedData['product_id'])
                    ->first();

                if ($orderItem) {
                    $orderItem->quantity_received += $validatedData['quantity'];
                    $orderItem->save();
                }
            }

            DB::commit();

            return redirect()->route('admin.stock-in.index')
                ->with('success', 'Entrada de stock registrada correctamente.');

        } catch (\Exception $e) {
            DB::rollback();

            return redirect()->back()
                ->withInput()
                ->with('error', 'Error al procesar la entrada de stock: ' . $e->getMessage());
        }
    }

    public function destroy(StockIn $stockIn)
    {
        DB::beginTransaction();
        try {
            $product = Product::lockForUpdate()->find($stockIn->product_id);

            if ($product->stock < $stockIn->quantity) {
                DB::rollback();
                return redirect()->route('admin.stock-in.index')
                    ->with('error', 'No se puede eliminar la entrada: El stock actual es menor a la cantidad ingresada.');
            }

            $product->stock -= $stockIn->quantity;
            $product->save();

            if ($stockIn->purchase_order_id) {
                $orderItem = PurchaseOrderItem::where('purchase_order_id', $stockIn->purchase_order_id)
                    ->where('product_id', $stockIn->product_id)
                    ->first();

                if ($orderItem) {
                    $orderItem->quantity_received -= $stockIn->quantity;
                    if ($orderItem->quantity_received < 0) {
                        $orderItem->quantity_received = 0;
                    }
                    $orderItem->save();
                }
            }

            $stockIn->delete();

            DB::commit();
            return redirect()->route('admin.stock-in.index')
                ->with('success', 'Entrada de stock eliminada y stock del producto corregido.');

        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with('error', 'Error al eliminar el movimiento: ' . $e->getMessage());
        }
    }

    public function show($id) { return abort(404); }
    public function edit($id) { return abort(404); }
    public function update(Request $request, $id) { return abort(404); }
}
