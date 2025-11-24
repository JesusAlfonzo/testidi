<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use Illuminate\Http\Request;
use App\Http\Requests\StoreUpdateBrandRequest;

class BrandController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:marcas_ver')->only('index');
        $this->middleware('permission:marcas_crear')->only('create', 'store');
        $this->middleware('permission:marcas_editar')->only('edit', 'update');
        $this->middleware('permission:marcas_eliminar')->only('destroy');
    }

    public function index()
    {
        $brands = Brand::with('user')->paginate(10);
        return view('admin.brands.index', compact('brands'));
    }

    public function create()
    {
        return view('admin.brands.create');
    }

    public function store(StoreUpdateBrandRequest $request)
    {
        // ESTANDARIZACIÓN: Creación directa
        Brand::create($request->validated() + ['user_id' => auth()->id()]);

        return redirect()->route('admin.brands.index')
                         ->with('success', '✅ Marca registrada con éxito.');
    }

    public function edit(Brand $brand)
    {
        return view('admin.brands.edit', compact('brand'));
    }

    public function update(StoreUpdateBrandRequest $request, Brand $brand)
    {
        // ESTANDARIZACIÓN: Actualización directa
        $brand->update($request->validated());

        return redirect()->route('admin.brands.index')
                         ->with('success', '✅ Marca actualizada con éxito.');
    }

    public function destroy(Brand $brand)
    {
        // Se recomienda aplicar restricción si la marca está asociada a un producto.
        $brand->delete();
        return redirect()->route('admin.brands.index')
                         ->with('success', '✅ Marca eliminada con éxito.');
    }
}
