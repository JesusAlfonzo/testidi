<?php

namespace App\Http\Controllers;

use App\Models\Kit;
use App\Models\Product;
use Illuminate\Http\Request;

class KitController extends Controller
{

    public function __construct()
    {
        // Protege el controlador con los nuevos permisos
        $this->middleware('can:kits_ver')->only(['index', 'show']);
        $this->middleware('can:kits_crear')->only(['create', 'store']);
        $this->middleware('can:kits_editar')->only(['edit', 'update']);
        $this->middleware('can:kits_eliminar')->only('destroy');
    }

    /**
     * Muestra el listado de Kits.
     */
    public function index()
    {
        $kits = Kit::orderBy('name')->get();
        return view('admin.kits.index', compact('kits'));
    }

    /**
     * Muestra el formulario para crear un nuevo Kit.
     */
    public function create()
    {
        $products = Product::where('is_active', true)->orderBy('name')->get(['id', 'name']);
        return view('admin.kits.create', compact('products'));
    }

    /**
     * Almacena un Kit recién creado en la base de datos.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:kits,name',
            'unit_price' => 'nullable|numeric|min:0',
            'components' => 'required|array|min:1',
            'components.*.product_id' => 'required|exists:products,id',
            'components.*.quantity' => 'required|integer|min:1',
        ]);

        $kit = Kit::create([
            'name' => $request->name,
            'description' => $request->description,
            'unit_price' => $request->unit_price ?? 0,
            'is_active' => $request->has('is_active'),
        ]);

        $kitComponents = collect($request->components)->mapWithKeys(function ($component) {
            return [$component['product_id'] => ['quantity_required' => $component['quantity']]];
        })->toArray();
        
        $kit->components()->attach($kitComponents);

        return redirect()->route('admin.kits.index')->with('success', 'Kit creado exitosamente.');
    }

    /**
     * Muestra un Kit específico.
     */
    public function show(Kit $kit)
    {
        // Cargamos los componentes con el modelo de Producto
        $kit->load('components');
        return view('admin.kits.show', compact('kit'));
    }

    /**
     * Muestra el formulario para editar un Kit.
     */
    public function edit(Kit $kit)
    {
        $products = Product::where('is_active', true)->orderBy('name')->get(['id', 'name']);
        $currentComponents = $kit->components->pluck('pivot.quantity_required', 'id')->toArray();

        return view('admin.kits.edit', compact('kit', 'products', 'currentComponents'));
    }

    /**
     * Actualiza un Kit en la base de datos.
     */
    public function update(Request $request, Kit $kit)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:kits,name,' . $kit->id,
            'unit_price' => 'nullable|numeric|min:0',
            'components' => 'required|array|min:1',
            'components.*.product_id' => 'required|exists:products,id',
            'components.*.quantity' => 'required|integer|min:1',
        ]);

        $kit->update([
            'name' => $request->name,
            'description' => $request->description,
            'unit_price' => $request->unit_price ?? 0,
            'is_active' => $request->has('is_active'),
        ]);

        $kitComponents = collect($request->components)->mapWithKeys(function ($component) {
            return [$component['product_id'] => ['quantity_required' => $component['quantity']]];
        })->toArray();
        
        $kit->components()->sync($kitComponents); // Usamos sync para actualizar o eliminar

        return redirect()->route('admin.kits.index')->with('success', 'Kit actualizado exitosamente.');
    }

    /**
     * Elimina un Kit de la base de datos.
     */
    public function destroy(Kit $kit)
    {
        $kit->delete();
        return redirect()->route('admin.kits.index')->with('success', 'Kit eliminado exitosamente.');
    }
}