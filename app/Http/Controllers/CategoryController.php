<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Http\Requests\StoreUpdateCategoryRequest;
use App\Services\CacheService;

class CategoryController extends Controller
{
    protected CacheService $cacheService;

    public function __construct(CacheService $cacheService)
    {
        $this->cacheService = $cacheService;
        $this->authorizeResource(Category::class, 'category');
    }

    public function index(\Illuminate\Http\Request $request)
    {
        $perPage = $request->get('per_page', 15);
        if (!in_array($perPage, [15, 25, 50, 100])) {
            $perPage = 15;
        }

        if ($request->get('view_all') === 'true') {
            $categories = Category::with('user')->paginate(Category::count())->appends($request->except('page'));
        } else {
            $categories = Category::with('user')->paginate($perPage);
        }
        
        return view('admin.categories.index', compact('categories', 'perPage'));
    }

    public function create()
    {
        return view('admin.categories.create');
    }

    public function store(StoreUpdateCategoryRequest $request)
    {
        Category::create($request->validated() + ['user_id' => auth()->id()]);
        $this->cacheService->invalidateCategories();

        return redirect()->route('admin.categories.index')
                         ->with('success', 'Categoría creada con éxito.');
    }

    public function edit(Category $category)
    {
        return view('admin.categories.edit', compact('category'));
    }

    public function update(StoreUpdateCategoryRequest $request, Category $category)
    {
        $category->update($request->validated());
        $this->cacheService->invalidateCategories();

        return redirect()->route('admin.categories.index')
                         ->with('success', 'Categoría actualizada con éxito.');
    }

    public function destroy(Category $category)
    {
        $category->delete();
        $this->cacheService->invalidateCategories();
        
        return redirect()->route('admin.categories.index')
                         ->with('success', 'Categoría eliminada con éxito.');
    }
}
