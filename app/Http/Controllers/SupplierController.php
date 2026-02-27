<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use App\Http\Requests\StoreUpdateSupplierRequest;

class SupplierController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:proveedores_ver')->only('index');
        $this->middleware('permission:proveedores_crear')->only('create', 'store');
        $this->middleware('permission:proveedores_editar')->only('edit', 'update');
        $this->middleware('permission:proveedores_eliminar')->only('destroy');
    }

    public function index()
    {
        $suppliers = Supplier::with('user')->get();
        return view('admin.suppliers.index', compact('suppliers'));
    }

    public function create()
    {
        return view('admin.suppliers.create');
    }

    public function store(StoreUpdateSupplierRequest $request)
    {
        // ESTANDARIZACIÓN: Creación directa
        Supplier::create($request->validated() + ['user_id' => auth()->id()]);

        return redirect()->route('admin.suppliers.index')
                         ->with('success', '✅ Proveedor registrado con éxito.');
    }

    public function edit(Supplier $supplier)
    {
        return view('admin.suppliers.edit', compact('supplier'));
    }

    public function update(StoreUpdateSupplierRequest $request, Supplier $supplier)
    {
        // ESTANDARIZACIÓN: Actualización directa
        $supplier->update($request->validated());

        return redirect()->route('admin.suppliers.index')
                         ->with('success', '✅ Proveedor actualizado con éxito.');
    }

    public function destroy(Supplier $supplier)
    {
        // NOTA: Se recomienda aplicar restricción si el proveedor está asociado a un producto.
        $supplier->delete();
        return redirect()->route('admin.suppliers.index')
                         ->with('success', '✅ Proveedor eliminado con éxito.');
    }
}
