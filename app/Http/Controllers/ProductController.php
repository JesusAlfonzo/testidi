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

    public function index(Request $request)
    {
        // Cargar listas para los filtros
        $categories = Category::pluck('name', 'id');
        $locations = Location::pluck('name', 'id');
        $brands = Brand::pluck('name', 'id');

        // Consulta base
        $query = Product::with(['category', 'unit', 'location', 'brand']);

        // --- APLICAR FILTROS ---
        if ($request->filled('category_id')) $query->where('category_id', $request->category_id);
        if ($request->filled('location_id')) $query->where('location_id', $request->location_id);
        if ($request->filled('brand_id')) $query->where('brand_id', $request->brand_id);
        if ($request->filled('status')) {
            if ($request->status === 'active') $query->where('is_active', true);
            elseif ($request->status === 'inactive') $query->where('is_active', false);
        }
        if ($request->filled('stock_status')) {
            if ($request->stock_status === 'low') $query->whereColumn('stock', '<=', 'min_stock');
        }

        // Usamos get() para que DataTables maneje la paginación en cliente
        $products = $query->get();

        return view('admin.products.index', compact('products', 'categories', 'locations', 'brands'));
    }

    // ... (resto de métodos create, store, edit, update, destroy se mantienen IGUAL) ...
    public function create() { 
        $categories = Category::pluck('name', 'id');
        $units = Unit::pluck('name', 'id');
        $locations = Location::pluck('name', 'id');
        $brands = Brand::pluck('name', 'id');
        return view('admin.products.create', compact('categories', 'units', 'locations', 'brands'));
    }

    public function store(StoreUpdateProductRequest $request) {
        $validatedData = $request->validated();
        if (empty($validatedData['brand_id'])) unset($validatedData['brand_id']);
        Product::create($validatedData + ['user_id' => auth()->id()]);
        return redirect()->route('admin.products.index')->with('success', '✅ Producto registrado con éxito.');
    }

    public function edit(Product $product) {
        $categories = Category::pluck('name', 'id');
        $units = Unit::pluck('name', 'id');
        $locations = Location::pluck('name', 'id');
        $brands = Brand::pluck('name', 'id');
        return view('admin.products.edit', compact('product', 'categories', 'units', 'locations', 'brands'));
    }

    public function update(StoreUpdateProductRequest $request, Product $product) {
        $validatedData = $request->validated();
        if (empty($validatedData['brand_id'])) $validatedData['brand_id'] = null;
        $product->update($validatedData);
        return redirect()->route('admin.products.index')->with('success', '✅ Producto actualizado con éxito.');
    }

    public function destroy(Product $product) {
        $product->delete();
        return redirect()->route('admin.products.index')->with('success', '✅ Producto eliminado con éxito.');
    }
}