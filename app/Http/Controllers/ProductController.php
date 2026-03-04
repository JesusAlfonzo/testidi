<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use App\Models\Unit;
use App\Models\Location;
use App\Models\Brand;
use App\Http\Requests\StoreUpdateProductRequest; 
use App\Services\CacheService;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    protected CacheService $cacheService;

    public function __construct(CacheService $cacheService)
    {
        $this->cacheService = $cacheService;
        $this->authorizeResource(Product::class, 'product');
    }

    public function index(Request $request)
    {
        $categories = $this->cacheService->categories();
        $locations = $this->cacheService->locations();
        $brands = $this->cacheService->brands();

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
        if ($request->filled('created_on_the_fly')) {
            if ($request->created_on_the_fly === 'yes') {
                $query->where('created_on_the_fly', true);
            } elseif ($request->created_on_the_fly === 'no') {
                $query->where('created_on_the_fly', false);
            }
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
        $this->cacheService->invalidateProducts();
        return redirect()->route('admin.products.index')->with('success', 'Producto registrado con éxito.');
    }

    public function edit(Product $product) {
        $categories = $this->cacheService->categories();
        $units = $this->cacheService->units();
        $locations = $this->cacheService->locations();
        $brands = $this->cacheService->brands();
        return view('admin.products.edit', compact('product', 'categories', 'units', 'locations', 'brands'));
    }

    public function update(StoreUpdateProductRequest $request, Product $product) {
        $validatedData = $request->validated();
        if (empty($validatedData['brand_id'])) $validatedData['brand_id'] = null;
        $product->update($validatedData);
        $this->cacheService->invalidateProducts();
        return redirect()->route('admin.products.index')->with('success', 'Producto actualizado con éxito.');
    }

    public function destroy(Product $product) {
        $product->delete();
        $this->cacheService->invalidateProducts();
        return redirect()->route('admin.products.index')->with('success', 'Producto eliminado con éxito.');
    }

    public function quickStore(Request $request)
    {
        $request->validate([
            'code' => 'required|string|max:255|unique:products,code',
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'unit_id' => 'required|exists:units,id',
            'location_id' => 'required|exists:locations,id',
            'brand_id' => 'nullable|exists:brands,id',
            'description' => 'nullable|string',
            'cost' => 'nullable|numeric|min:0',
            'price' => 'nullable|numeric|min:0',
            'min_stock' => 'nullable|integer|min:0',
        ]);

        $product = Product::create([
            'code' => $request->code,
            'name' => $request->name,
            'category_id' => $request->category_id,
            'unit_id' => $request->unit_id,
            'location_id' => $request->location_id,
            'brand_id' => $request->brand_id,
            'description' => $request->description,
            'cost' => $request->cost ?? 0,
            'price' => $request->price ?? 0,
            'stock' => 0,
            'min_stock' => $request->min_stock ?? 0,
            'is_active' => true,
            'created_on_the_fly' => true,
            'user_id' => auth()->id(),
        ]);

        $product->load(['category', 'unit', 'location', 'brand']);

        return response()->json([
            'success' => true,
            'message' => 'Producto creado exitosamente',
            'product' => $product
        ]);
    }

    public function search(Request $request)
    {
        $search = $request->get('search', '');
        
        $products = Product::with(['category', 'unit', 'location', 'brand'])
            ->where('is_active', true)
            ->where(function($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%");
            })
            ->limit(20)
            ->get();

        return response()->json($products);
    }
}