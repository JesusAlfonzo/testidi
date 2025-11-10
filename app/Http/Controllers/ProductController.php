<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use App\Models\Unit;
use App\Models\Location;
use App\Models\Brand;
use App\Http\Requests\StoreUpdateProductRequest;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:productos_ver')->only('index');
        $this->middleware('permission:productos_crear')->only('create', 'store');
        $this->middleware('permission:productos_editar')->only('edit', 'update');
        $this->middleware('permission:productos_eliminar')->only('destroy');
    }

    public function index()
    {
        $products = Product::with(['category', 'unit', 'location', 'brand'])->paginate(15);
        return view('admin.products.index', compact('products'));
    }

    public function create()
    {
        // Obtener datos para los SELECT
        $categories = Category::pluck('name', 'id');
        $units = Unit::pluck('name', 'id');
        $locations = Location::pluck('name', 'id');
        $brands = Brand::pluck('name', 'id'); // Marcas es opcional, pero pasamos la lista

        return view('admin.products.create', compact('categories', 'units', 'locations', 'brands'));
    }

    public function store(StoreUpdateProductRequest $request)
    {
        $validatedData = $request->validated();

        // Si brand_id es nulo, lo eliminamos para evitar errores de tipo si es cadena vacía
        if (empty($validatedData['brand_id'])) {
            unset($validatedData['brand_id']);
        }

        Product::create($validatedData + ['user_id' => auth()->id()]);

        return redirect()->route('admin.products.index')
                         ->with('success', '✅ Producto registrado con éxito.');
    }

    public function edit(Product $product)
    {
        // Obtener datos para los SELECT
        $categories = Category::pluck('name', 'id');
        $units = Unit::pluck('name', 'id');
        $locations = Location::pluck('name', 'id');
        $brands = Brand::pluck('name', 'id');

        return view('admin.products.edit', compact('product', 'categories', 'units', 'locations', 'brands'));
    }

    public function update(StoreUpdateProductRequest $request, Product $product)
    {
        $validatedData = $request->validated();

        if (empty($validatedData['brand_id'])) {
            $validatedData['brand_id'] = null; // Asegurar que se guarde como NULL en BD si está vacío
        }

        $product->update($validatedData);

        return redirect()->route('admin.products.index')
                         ->with('success', '✅ Producto actualizado con éxito.');
    }

    public function destroy(Product $product)
    {
        // En una aplicación real, se debería verificar si el producto tiene movimientos (entradas/salidas)
        // antes de permitir su eliminación para mantener la integridad histórica.
        $product->delete();
        return redirect()->route('admin.products.index')
                         ->with('success', '✅ Producto eliminado con éxito.');
    }
}
