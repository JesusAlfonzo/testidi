<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use App\Http\Requests\StoreUpdateSupplierRequest;
use App\Services\CacheService;

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
        $perPage = $request->get('per_page', 15);
        if (!in_array($perPage, [15, 25, 50, 100])) {
            $perPage = 15;
        }

        if ($request->get('view_all') === 'true') {
            $suppliers = Supplier::with('user')->paginate(Supplier::count())->appends($request->except('page'));
        } else {
            $suppliers = Supplier::with('user')->paginate($perPage);
        }
        
        return view('admin.suppliers.index', compact('suppliers', 'perPage'));
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
        $supplier->delete();
        $this->cacheService->invalidateSuppliers();
        
        return redirect()->route('admin.suppliers.index')
                         ->with('success', 'Proveedor eliminado con éxito.');
    }
}
