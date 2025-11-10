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
        $categories = Category::with('user')->paginate(10);
        return view('admin.categories.index', compact('categories'));
    }

    public function create()
    {
        return view('admin.categories.create');
    }

    public function store(StoreUpdateCategoryRequest $request)
    {
        $validatedData = $request->validated();

        Category::create([
            'name' => $validatedData['name'],
            'description' => $validatedData['description'],
            'user_id' => auth()->id(), // Asignar el usuario logueado como creador
        ]);

        return redirect()->route('admin.categories.index')
                         ->with('success', '✅ Categoría creada con éxito.');
    }

    public function edit(Category $category)
    {
        return view('admin.categories.edit', compact('category'));
    }

    public function update(StoreUpdateCategoryRequest $request, Category $category)
    {
        $validatedData = $request->validated();

        // El 'user_id' no se actualiza, solo el nombre y la descripción
        $category->update($validatedData);

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
