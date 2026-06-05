<?php

namespace App\Http\Controllers;

use App\Models\Kit;
use App\Models\Product;
use Illuminate\Http\Request;

class KitController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Kit::class, 'kit');
    }

    /**
     * Muestra el listado de Kits.
     */
    public function index(\Illuminate\Http\Request $request)
    {
        if ($request->ajax()) {
            return $this->indexDataTables($request);
        }

        $perPage = $request->get('per_page', 15);
        if (!in_array($perPage, [15, 25, 50, 100])) {
            $perPage = 15;
        }

        if ($request->get('view_all') === 'true') {
            $kits = Kit::orderBy('name')->paginate($perPage)->appends($request->except('page'));
        } else {
            $kits = Kit::orderBy('name')->paginate($perPage);
        }
        
        return view('admin.kits.index', compact('kits', 'perPage'));
    }

    protected function indexDataTables(\Illuminate\Http\Request $request)
    {
        $query = Kit::with(['components']);

        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->where('is_active', true);
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        $start = $request->input('start', 0);
        $length = $request->input('length', 15);
        $search = $request->input('search.value', '');

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->whereRaw('LOWER(name) LIKE ?', [strtolower("%{$search}%")])
                  ->orWhereRaw('LOWER(description) LIKE ?', [strtolower("%{$search}%")]);
            });
        }

        $totalRecords = Kit::count();
        $totalFiltered = $query->count();

        $orderColumn = $request->input('order.0.column', 1);
        $orderDir = $request->input('order.0.dir', 'asc');
        $columns = [
            0 => 'id',
            1 => 'name',
            2 => 'unit_price',
            3 => null,
            4 => null,
            5 => 'is_active',
            6 => null,
        ];
        
        if (isset($columns[$orderColumn]) && $columns[$orderColumn] !== null) {
            $query->orderBy($columns[$orderColumn], $orderDir);
        }

        $kits = $query->offset($start)->limit($length)->get();

        $data = $kits->map(function ($kit) {
            $availableStock = $kit->available_stock;
            
            // Renderizar insignia de stock del kit con diseño responsivo y premium
            if ($availableStock > 5) {
                $stockBadge = '<span class="badge badge-success py-2 px-3" style="font-size: 0.9rem;"><i class="fas fa-check-circle"></i> ' . $availableStock . ' kits</span>';
            } elseif ($availableStock > 0) {
                $stockBadge = '<span class="badge badge-warning text-dark py-2 px-3" style="font-size: 0.9rem;"><i class="fas fa-exclamation-triangle"></i> ' . $availableStock . ' kits</span>';
            } else {
                $stockBadge = '<span class="badge badge-danger py-2 px-3" style="font-size: 0.9rem;"><i class="fas fa-times-circle"></i> Sin stock</span>';
            }

            // Mapear los componentes con su stock actual y cantidad requerida
            $componentsHtml = '<ul class="list-unstyled mb-0" style="font-size: 0.85rem; line-height: 1.5;">';
            if ($kit->components->isEmpty()) {
                $componentsHtml .= '<li class="text-muted"><i class="fas fa-info-circle"></i> Sin componentes</li>';
            } else {
                foreach ($kit->components as $component) {
                    $stock = $component->stock ?? 0;
                    $req = $component->pivot->quantity_required;
                    $badgeClass = $stock >= $req ? 'badge-success' : 'badge-danger';
                    
                    $componentsHtml .= sprintf(
                        '<li><span class="badge %s" style="min-width: 50px; font-weight: 500;">%d / %d</span> <span class="text-secondary">%s</span></li>',
                        $badgeClass,
                        $stock,
                        $req,
                        e($component->name)
                    );
                }
            }
            $componentsHtml .= '</ul>';

            return [
                'id' => $kit->id,
                'name' => $kit->name,
                'unit_price' => number_format($kit->unit_price, 2),
                'is_active' => $kit->is_active,
                'available_stock' => $stockBadge,
                'components_detail' => $componentsHtml,
                'actions' => view('admin.kits.partials.actions', ['kit' => $kit])->render(),
            ];
        });

        return response()->json([
            'draw' => intval($request->input('draw')),
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $totalFiltered,
            'data' => $data,
        ]);
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