<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Http\Requests\StoreUpdateBrandRequest;
use App\Services\CacheService;

class BrandController extends Controller
{
    protected CacheService $cacheService;

    public function __construct(CacheService $cacheService)
    {
        $this->cacheService = $cacheService;
        $this->authorizeResource(Brand::class, 'brand');
    }

    public function index(\Illuminate\Http\Request $request)
    {
        $perPage = $request->get('per_page', 15);
        if (!in_array($perPage, [15, 25, 50, 100])) {
            $perPage = 15;
        }

        if ($request->get('view_all') === 'true') {
            $brands = Brand::with('user')->paginate(Brand::count())->appends($request->except('page'));
        } else {
            $brands = Brand::with('user')->paginate($perPage);
        }
        
        return view('admin.brands.index', compact('brands', 'perPage'));
    }

    public function create()
    {
        return view('admin.brands.create');
    }

    public function store(StoreUpdateBrandRequest $request)
    {
        Brand::create($request->validated() + ['user_id' => auth()->id()]);
        $this->cacheService->invalidateBrands();

        return redirect()->route('admin.brands.index')
                         ->with('success', 'Marca registrada con éxito.');
    }

    public function edit(Brand $brand)
    {
        return view('admin.brands.edit', compact('brand'));
    }

    public function update(StoreUpdateBrandRequest $request, Brand $brand)
    {
        $brand->update($request->validated());
        $this->cacheService->invalidateBrands();

        return redirect()->route('admin.brands.index')
                         ->with('success', 'Marca actualizada con éxito.');
    }

    public function destroy(Brand $brand)
    {
        $brand->delete();
        $this->cacheService->invalidateBrands();
        
        return redirect()->route('admin.brands.index')
                         ->with('success', 'Marca eliminada con éxito.');
    }
}
