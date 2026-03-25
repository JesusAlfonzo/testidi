<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use App\Http\Requests\StoreUpdateSupplierRequest;
use App\Services\CacheService;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    protected CacheService $cacheService;

    public function __construct(CacheService $cacheService)
    {
        $this->cacheService = $cacheService;
        $this->authorizeResource(Supplier::class, 'supplier');
    }

    public function index(\Illuminate\Http\Request $request)
    {
        $status = $request->get('status', 'all');
        
        $query = Supplier::with('user');
        
        if ($status === 'active') {
            $query->where('is_active', true);
        } elseif ($status === 'inactive') {
            $query->where('is_active', false);
        }
        
        $suppliers = $query->orderBy('name')->get();
        
        return view('admin.suppliers.index', compact('suppliers', 'status'));
    }

    public function create()
    {
        return view('admin.suppliers.create');
    }

    public function store(StoreUpdateSupplierRequest $request)
    {
        Supplier::create($request->validated() + ['user_id' => auth()->id()]);
        $this->cacheService->invalidateSuppliers();

        return redirect()->route('admin.suppliers.index')
                         ->with('success', 'Proveedor registrado con éxito.');
    }

    public function edit(Supplier $supplier)
    {
        return view('admin.suppliers.edit', compact('supplier'));
    }

    public function update(StoreUpdateSupplierRequest $request, Supplier $supplier)
    {
        $supplier->update($request->validated());
        $this->cacheService->invalidateSuppliers();

        return redirect()->route('admin.suppliers.index')
                         ->with('success', 'Proveedor actualizado con éxito.');
    }

    public function destroy(Supplier $supplier)
    {
        $productsCount = \App\Models\Product::where('supplier_id', $supplier->id)->count();
        
        if ($productsCount > 0) {
            return redirect()->route('admin.suppliers.index')
                             ->with('error', 'No se puede eliminar el proveedor porque tiene ' . $productsCount . ' producto(s) asociado(s).');
        }
        
        $supplier->delete();
        $this->cacheService->invalidateSuppliers();
        
        return redirect()->route('admin.suppliers.index')
                         ->with('success', 'Proveedor eliminado con éxito.');
    }

    public function quickStore(Request $request)
    {
        $request->validate([
            "name" => "required|string|max:255",
            "tax_id" => "required|string|max:50|unique:suppliers,tax_id",
            "email" => "nullable|email|max:255",
            "phone" => "nullable|string|max:50",
            "address" => "nullable|string|max:500",
            "fiscal_address" => "nullable|string|max:500",
            "contact_person" => "nullable|string|max:100",
        ]);

        $supplier = Supplier::create([
            "name" => $request->name,
            "tax_id" => $request->tax_id,
            "email" => $request->email,
            "phone" => $request->phone,
            "address" => $request->address,
            "fiscal_address" => $request->fiscal_address,
            "contact_person" => $request->contact_person ?? null,
            "is_active" => true,
            "user_id" => auth()->id(),
        ]);

        $this->cacheService->invalidateSuppliers();

        return response()->json([
            "success" => true,
            "message" => "Proveedor creado exitosamente",
            "supplier" => $supplier
        ]);
    }
}