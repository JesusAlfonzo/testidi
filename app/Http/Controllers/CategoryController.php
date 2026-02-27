<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use App\Http\Requests\StoreUpdateCategoryRequest;

class CategoryController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:categorias_ver')->only('index');
        $this->middleware('permission:categorias_crear')->only('create', 'store');
        $this->middleware('permission:categorias_editar')->only('edit', 'update');
        $this->middleware('permission:categorias_eliminar')->only('destroy');
    }

    public function index()
    {
        $categories = Category::with('user')->get();
        return view('admin.categories.index', compact('categories'));
    }

    public function create()
    {
        return view('admin.categories.create');
    }

    public function store(StoreUpdateCategoryRequest $request)
    {
        // ESTANDARIZACIÓN: Usamos validated() + el ID de usuario en una sola línea
        // Esto reemplaza la creación manual del array
        Category::create($request->validated() + ['user_id' => auth()->id()]);

        return redirect()->route('admin.categories.index')
                         ->with('success', '✅ Categoría creada con éxito.');
    }

    public function edit(Category $category)
    {
        return view('admin.categories.edit', compact('category'));
    }

    public function update(StoreUpdateCategoryRequest $request, Category $category)
    {
        // En update, simplemente pasamos lo validado directamente
        $category->update($request->validated());

        return redirect()->route('admin.categories.index')
                         ->with('success', '✅ Categoría actualizada con éxito.');
    }

    public function destroy(Category $category)
    {
        // 🛑 Importante: Considera prohibir la eliminación si la categoría está en uso por un Producto.
        // Por ahora, solo eliminamos.

        $category->delete();
        return redirect()->route('admin.categories.index')
                         ->with('success', '✅ Categoría eliminada con éxito.');
    }
}
