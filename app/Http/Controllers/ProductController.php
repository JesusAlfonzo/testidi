<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use App\Models\Unit;
use App\Models\Location;
use App\Models\Brand;
use App\Http\Requests\StoreUpdateProductRequest; 
use App\Services\CacheService;
use App\Models\ProductBatch;
use Illuminate\Support\Facades\DB;
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
        // Si es petición AJAX de DataTables, devolver JSON
        if ($request->ajax()) {
            return $this->indexDataTables($request);
        }

        $categories = $this->cacheService->categories();
        $locations = $this->cacheService->locations();
        $brands = $this->cacheService->brands();

        $perPage = $request->get('per_page', 15);
        if (!in_array($perPage, [15, 25, 50, 100])) {
            $perPage = 15;
        }

        $query = Product::with(['category', 'unit', 'location', 'brand', 'components'])
            ->when($request->type, function ($q) use ($request) {
                return $q->where('type', $request->type);
            });
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

        if ($request->get('view_all') === 'true') {
            $products = $query->paginate($perPage)->appends($request->except('page'));
        } else {
            $products = $query->paginate($perPage)->appends($request->except('per_page'));
        }

        return view('admin.products.index', compact('products', 'categories', 'locations', 'brands', 'perPage'));
    }

    protected function indexDataTables(Request $request)
    {
        $query = Product::with(['category', 'unit', 'location', 'brand', 'components'])
            ->when($request->type, function ($q) use ($request) {
                return $q->where('type', $request->type);
            });
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
            if ($request->created_on_the_fly === 'yes') $query->where('created_on_the_fly', true);
            elseif ($request->created_on_the_fly === 'no') $query->where('created_on_the_fly', false);
        }

        // DataTables parameters
        $start = $request->input('start', 0);
        $length = $request->input('length', 15);
        $search = $request->input('search.value', '');
        $orderColumn = $request->input('order.0.column', 0);
        $orderDir = $request->input('order.0.dir', 'asc');

        // Búsqueda global (case-insensitive)
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->whereRaw('LOWER(name) LIKE ?', [strtolower("%{$search}%")])
                  ->orWhereRaw('LOWER(code) LIKE ?', [strtolower("%{$search}%")]);
            });
        }

        // Total records (sin filtros)
        $totalRecords = Product::count();

        // Total filtered
        $totalFiltered = $query->count();

        // Ordenamiento
        $columns = ['id', 'name', 'stock', 'code', 'category_id', 'location_id', 'is_active', 'created_on_the_fly'];
        if (isset($columns[$orderColumn])) {
            $query->orderBy($columns[$orderColumn], $orderDir);
        }

        // Paginación
        $products = $query->offset($start)->limit($length)->get();

        $data = $products->map(function ($product) {
            return [
                'id' => $product->id,
                'name' => $product->name,
                'type' => $product->type,
                'components' => $product->type === 'composite_kit' ? $product->components->map(function($comp) {
                    return [
                        'name' => $comp->name,
                        'code' => $comp->code,
                        'quantity' => $comp->pivot->quantity,
                    ];
                }) : [],
                'stock' => $product->stock,
                'stock_class' => $product->stock <= $product->min_stock ? 'badge-danger' : 'badge-success',
                'actions' => view('admin.products.partials.actions', ['product' => $product])->render(),
                'code' => $product->code ?? 'N/A',
                'category' => $product->category->name ?? 'N/A',
                'location' => $product->location->name ?? 'N/A',
                'cost' => number_format($product->cost, 2),
                'price' => number_format($product->price, 2),
                'is_active' => $product->is_active,
                'created_on_the_fly' => $product->created_on_the_fly,
                'is_generic' => $product->is_generic,
            ];
        });

        return response()->json([
            'draw' => intval($request->input('draw')),
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $totalFiltered,
            'data' => $data
        ]);
    }

    public function show(Product $product)
    {
        $product->load(['category', 'unit', 'location', 'brand', 'batches']);
        $stockIns = \App\Models\StockIn::whereHas('items', function ($q) use ($product) {
            $q->where('product_id', $product->id);
        })->with('purchaseOrder')->latest()->limit(20)->get();
        return view('admin.products.show', compact('product', 'stockIns'));
    }

    public function create() { 
        $categories = Category::pluck('name', 'id');
        $units = Unit::pluck('name', 'id');
        $locations = Location::pluck('name', 'id');
        $brands = Brand::pluck('name', 'id');
        $products = Product::where('is_active', true)->orderBy('name')->get(['id', 'name', 'code']);
        $defaultExpiryDays = 30;
        return view('admin.products.create', compact('categories', 'units', 'locations', 'brands', 'products', 'defaultExpiryDays'));
    }
    public function store(StoreUpdateProductRequest $request) {
        $validatedData = $request->validated();
        $validatedData['is_generic'] = $request->boolean('is_generic');
        $validatedData['requires_serial'] = $request->boolean('requires_serial');
        if (empty($validatedData['brand_id'])) $validatedData['brand_id'] = null;
        if (empty($validatedData['category_id'])) $validatedData['category_id'] = null;
        if (empty($validatedData['location_id'])) $validatedData['location_id'] = null;
        
        $validatedData['is_perishable'] = $request->boolean('is_perishable');
        $validatedData['track_expiry'] = $validatedData['is_perishable'];
        $validatedData['type'] = $validatedData['type'] ?? 'individual';
        if ($validatedData['type'] === 'composite_kit') {
            $validatedData['is_kit'] = true;
        } else {
            $validatedData['is_kit'] = false;
        }
        
        DB::beginTransaction();
        try {
            $product = Product::create($validatedData + ['user_id' => auth()->id()]);
            
            if ($product->type === 'composite_kit' && $request->has('components')) {
                foreach ($request->input('components') as $comp) {
                    if (!empty($comp['child_id'])) {
                        $product->components()->attach($comp['child_id'], ['quantity' => $comp['quantity']]);
                    }
                }
            }

            if ($request->boolean('is_fraction_parent')) {
                $product->childFraction()->create([
                    'child_product_id' => $request->input('child_product_id'),
                    'conversion_factor' => $request->input('conversion_factor'),
                ]);
            }

            if ($request->has('conversions')) {
                foreach ($request->input('conversions') as $conv) {
                    if (!empty($conv['uom_id']) && !empty($conv['conversion_factor'])) {
                        $product->uomConversions()->create([
                            'uom_id' => $conv['uom_id'],
                            'conversion_factor' => $conv['conversion_factor'],
                        ]);
                    }
                }
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->with('error', 'Error al crear el producto: ' . $e->getMessage());
        }

        $this->cacheService->invalidateProducts();
        return redirect()->route('admin.products.index')->with('success', 'Producto registrado con éxito.');
    }

    public function edit(Product $product) {
        $categories = $this->cacheService->categories();
        $units = $this->cacheService->units();
        $locations = $this->cacheService->locations();
        $brands = $this->cacheService->brands();
        $product->load(['components', 'childFraction']);
        $products = Product::where('is_active', true)->where('id', '!=', $product->id)->orderBy('name')->get(['id', 'name', 'code']);
        $defaultExpiryDays = 30;
        return view('admin.products.edit', compact('product', 'categories', 'units', 'locations', 'brands', 'products', 'defaultExpiryDays'));
    }

    public function update(StoreUpdateProductRequest $request, Product $product) {
        $validatedData = $request->validated();
        $validatedData['is_generic'] = $request->boolean('is_generic');
        $validatedData['requires_serial'] = $request->boolean('requires_serial');
        if (empty($validatedData['brand_id'])) $validatedData['brand_id'] = null;
        if (empty($validatedData['category_id'])) $validatedData['category_id'] = null;
        if (empty($validatedData['location_id'])) $validatedData['location_id'] = null;
        
        $validatedData['is_perishable'] = $request->boolean('is_perishable');
        $validatedData['track_expiry'] = $validatedData['is_perishable'];
        $validatedData['type'] = $validatedData['type'] ?? 'individual';
        if ($validatedData['type'] === 'composite_kit') {
            $validatedData['is_kit'] = true;
        } else {
            $validatedData['is_kit'] = false;
        }

        DB::beginTransaction();
        try {
            $product->update($validatedData);

            if ($product->type === 'composite_kit' && $request->has('components')) {
                $syncData = [];
                foreach ($request->input('components') as $comp) {
                    if (!empty($comp['child_id'])) {
                        $syncData[$comp['child_id']] = ['quantity' => $comp['quantity']];
                    }
                }
                $product->components()->sync($syncData);
            } else {
                $product->components()->detach();
            }

            if ($request->boolean('is_fraction_parent')) {
                $product->childFraction()->updateOrCreate(
                    ['parent_product_id' => $product->id],
                    [
                        'child_product_id' => $request->input('child_product_id'),
                        'conversion_factor' => $request->input('conversion_factor'),
                    ]
                );
            } else {
                $product->childFraction()->delete();
            }

            if ($request->has('conversions')) {
                $product->uomConversions()->delete();
                foreach ($request->input('conversions') as $conv) {
                    if (!empty($conv['uom_id']) && !empty($conv['conversion_factor'])) {
                        $product->uomConversions()->create([
                            'uom_id' => $conv['uom_id'],
                            'conversion_factor' => $conv['conversion_factor'],
                        ]);
                    }
                }
            } else {
                $product->uomConversions()->delete();
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->with('error', 'Error al actualizar el producto: ' . $e->getMessage());
        }

        $this->cacheService->invalidateProducts();
        return redirect()->route('admin.products.index')->with('success', 'Producto actualizado con éxito.');
    }
    public function destroy(Product $product) {
        if ($product->hasTransactionalHistory()) {
            $product->is_active = false;
            $product->save();
            $this->cacheService->invalidateProducts();

            if (request()->ajax() || request()->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'El producto posee historial de movimientos en el almacén; ha sido inactivado de forma segura para preservar la integridad de las auditorías.'
                ]);
            }

            return redirect()->route('admin.products.index')
                ->with('success', 'El producto posee historial de movimientos en el almacén; ha sido inactivado de forma segura para preservar la integridad de las auditorías.');
        }

        $product->delete();
        $this->cacheService->invalidateProducts();

        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Producto eliminado con éxito.'
            ]);
        }

        return redirect()->route('admin.products.index')->with('success', 'Producto eliminado con éxito.');
    }

    public function quickStore(Request $request)
    {
        $isGeneric = $request->boolean('is_generic');

        $rules = [
            'code' => 'nullable|string|max:255|unique:products,code',
            'name' => 'required|string|max:255',
            'is_generic' => 'nullable|boolean',
            'unit_id' => 'required|exists:units,id',
            'brand_id' => 'nullable|exists:brands,id',
            'description' => 'nullable|string',
            'cost' => 'nullable|numeric|min:0',
            'price' => 'nullable|numeric|min:0',
            'min_stock' => 'nullable|integer|min:0',
        ];

        // Campos condicionales según is_generic
        if (!$isGeneric) {
            $rules['category_id'] = 'required|exists:categories,id';
            $rules['location_id'] = 'required|exists:locations,id';
        } else {
            $rules['category_id'] = 'nullable|exists:categories,id';
            $rules['location_id'] = 'nullable|exists:locations,id';
        }

        $request->validate($rules);

        $product = Product::create([
            'code' => $request->code,
            'name' => $request->name,
            'is_generic' => $isGeneric,
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

    
    public function quickStoreKit(Request $request)
    {
        $isGeneric = $request->boolean('is_generic');

        $rules = [
            "code" => "nullable|string|max:255|unique:products,code",
            "name" => "required|string|max:255",
            "is_generic" => "nullable|boolean",
            "unit_id" => "required|exists:units,id",
            "brand_id" => "nullable|exists:brands,id",
            "cost" => "nullable|numeric|min:0",
            'components' => 'required|array|min:1',
            'components.*.child_id' => 'required|exists:products,id',
            'components.*.quantity' => 'required|integer|min:1',
        ];

        if (!$isGeneric) {
            $rules['category_id'] = 'required|exists:categories,id';
            $rules['location_id'] = 'required|exists:locations,id';
        } else {
            $rules['category_id'] = 'nullable|exists:categories,id';
            $rules['location_id'] = 'nullable|exists:locations,id';
        }

        $request->validate($rules);

        DB::beginTransaction();
        try {
            $product = Product::create([
                "code" => $request->code,
                "name" => $request->name,
                "is_generic" => $isGeneric,
                "category_id" => $request->category_id,
                "unit_id" => $request->unit_id,
                "location_id" => $request->location_id,
                "brand_id" => $request->brand_id,
                "cost" => $request->cost ?? 0,
                "price" => 0,
                "stock" => 0,
                "min_stock" => 0,
                "is_active" => true,
                "is_kit" => true,
                "type" => "composite_kit",
                "created_on_the_fly" => true,
                "user_id" => auth()->id(),
            ]);

            foreach ($request->input('components') as $comp) {
                if (!empty($comp['child_id'])) {
                    $product->components()->attach($comp['child_id'], ['quantity' => $comp['quantity']]);
                }
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }

        $product->load(["category", "unit", "location", "brand", "components"]);

        return response()->json([
            "success" => true,
            "message" => "Kit creado exitosamente",
            "product" => $product
        ]);
    }

    public function decompose(Request $request, Product $product)
    {
        if ($product->type !== 'composite_kit') {
            return response()->json(['success' => false, 'message' => 'El producto no es un kit compuesto.'], 422);
        }

        $request->validate([
            'batch_id' => 'required|exists:product_batches,id',
            'quantity' => 'required|integer|min:1',
            'serials' => 'nullable|array', // child_id => [serial1, serial2, ...]
        ]);

        $batchId = $request->input('batch_id');
        $quantityToDecompose = (int) $request->input('quantity');

        $parentBatch = ProductBatch::where('product_id', $product->id)->find($batchId);

        if (!$parentBatch || $parentBatch->quantity < $quantityToDecompose) {
            return response()->json([
                'success' => false,
                'message' => 'Lote no encontrado o stock insuficiente del Kit para descomponer.'
            ], 422);
        }

        $components = $product->components;
        if ($components->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'El Kit compuesto no tiene componentes definidos.'
            ], 422);
        }

        // Validar seriales si algún hijo requiere serial
        $serialsInput = $request->input('serials', []);
        foreach ($components as $comp) {
            if ($comp->requires_serial) {
                $totalChildQty = $quantityToDecompose * $comp->pivot->quantity;
                $compSerials = $serialsInput[$comp->id] ?? [];
                
                // Limpiar vacíos
                $compSerials = array_filter(array_map('trim', $compSerials));
                
                if (count($compSerials) < $totalChildQty) {
                    return response()->json([
                        'success' => false,
                        'message' => "El componente '{$comp->name}' requiere número de serie. Por favor, ingrese {$totalChildQty} seriales únicos."
                    ], 422);
                }
                
                if (count(array_unique($compSerials)) < $totalChildQty) {
                    return response()->json([
                        'success' => false,
                        'message' => "Los seriales provistos para '{$comp->name}' contienen duplicados."
                    ], 422);
                }
                
                $existingCount = ProductBatch::where('product_id', $comp->id)
                    ->whereIn('serial_number', $compSerials)
                    ->count();
                if ($existingCount > 0) {
                    return response()->json([
                        'success' => false,
                        'message' => "Uno o más de los números de serie ingresados para '{$comp->name}' ya existen en el inventario."
                    ], 422);
                }
            }
        }

        DB::beginTransaction();
        try {
            // 1. Reducir stock del lote del Kit
            $parentBatch->quantity -= $quantityToDecompose;
            if ($parentBatch->quantity <= 0) {
                $parentBatch->delete();
            } else {
                $parentBatch->save();
            }

            // Reducir stock general del Kit
            $product->stock -= $quantityToDecompose;
            $product->save();

            // Evento de actualización de stock para el Kit
            event(new \App\Events\StockUpdated(
                product: $product,
                quantity: $quantityToDecompose,
                type: 'out',
                notes: "Descomposición de Kit: {$quantityToDecompose} unidades desagrupadas"
            ));

            // 2. Generar stock de componentes hijos
            foreach ($components as $comp) {
                $childQty = $quantityToDecompose * $comp->pivot->quantity;
                $proratedCost = $parentBatch->unit_cost ? ($parentBatch->unit_cost / $comp->pivot->quantity) : 0;
                $proratedPrice = $parentBatch->price ? ($parentBatch->price / $comp->pivot->quantity) : 0;

                $compSerials = array_filter(array_map('trim', $serialsInput[$comp->id] ?? []));

                if ($comp->requires_serial) {
                    foreach ($compSerials as $serial) {
                        ProductBatch::create([
                            'product_id' => $comp->id,
                            'stock_in_item_id' => $parentBatch->stock_in_item_id,
                            'invoice_number' => $parentBatch->invoice_number,
                            'batch_number' => $parentBatch->batch_number,
                            'expiration_date' => $parentBatch->expiration_date,
                            'serial_number' => $serial,
                            'quantity' => 1,
                            'unit_cost' => $proratedCost,
                            'price' => $proratedPrice,
                            'currency' => $parentBatch->currency,
                            'tax_status' => $parentBatch->tax_status,
                        ]);
                    }
                } else {
                    $childBatch = ProductBatch::where('product_id', $comp->id)
                        ->where('batch_number', $parentBatch->batch_number)
                        ->whereNull('serial_number')
                        ->where('invoice_number', $parentBatch->invoice_number)
                        ->whereDate('expiration_date', $parentBatch->expiration_date)
                        ->first();

                    if ($childBatch) {
                        $childBatch->quantity += $childQty;
                        $childBatch->save();
                    } else {
                        ProductBatch::create([
                            'product_id' => $comp->id,
                            'stock_in_item_id' => $parentBatch->stock_in_item_id,
                            'invoice_number' => $parentBatch->invoice_number,
                            'batch_number' => $parentBatch->batch_number,
                            'expiration_date' => $parentBatch->expiration_date,
                            'serial_number' => null,
                            'quantity' => $childQty,
                            'unit_cost' => $proratedCost,
                            'price' => $proratedPrice,
                            'currency' => $parentBatch->currency,
                            'tax_status' => $parentBatch->tax_status,
                        ]);
                    }
                }

                $comp->stock += $childQty;
                $comp->save();

                event(new \App\Events\StockUpdated(
                    product: $comp,
                    quantity: $childQty,
                    type: 'in',
                    notes: "Ingreso por descomposición del Kit: {$product->name}"
                ));

                $this->cacheService->invalidateProductStock($comp->id);
            }

            $this->cacheService->invalidateProductStock($product->id);
            $this->cacheService->invalidateProducts();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'El Kit compuesto se descompuso exitosamente.'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al procesar la descomposición: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Desempaca/fracciona un empaque mayor a unidades individuales.
     */
    public function unpack(Request $request, Product $product, \App\Services\ProductFractionService $fractionService)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1',
        ]);

        $quantityToUnpack = (int) $request->input('quantity');

        try {
            $result = $fractionService->unpack($product, $quantityToUnpack);

            return response()->json([
                'success' => true,
                'message' => "Se desempacaron {$quantityToUnpack} empaque(s) de '{$product->name}' con éxito.",
                'parent_stock' => $result['parent_stock'],
                'child_stock' => $result['child_stock'],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al desempacar: ' . $e->getMessage()
            ], 422);
        }
    }

    public function search(Request $request)
    {
        $search = $request->get('search', '');
        
        $products = Product::with(['category', 'unit', 'location', 'brand', 'uomConversions.uom'])
            ->where('is_active', true)
            ->where(function($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%");
            })
            ->limit(20)
            ->get();

        return response()->json($products);
    }

    public function searchAjax(Request $request)
    {
        try {
            $search = $request->get('q', '');
            $categoryId = $request->get('category_id');

            // Eager loading de 'unit' y 'uomConversions.uom'
            $query = Product::with(['unit', 'uomConversions.uom'])
                ->where('is_active', true);

            if ($categoryId) {
                $query->where('category_id', $categoryId);
            }

            if ($search) {
                $query->where(function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('code', 'like', "%{$search}%");
                });
            }

            $products = $query->orderBy('name')->paginate(15);

            return response()->json([
                'results' => $products->map(function($product) {
                    $unitId = $product->unit_id;
                    $unitName = $product->unit?->name ?? 'Unidad';
                    $unitAbbr = $product->unit?->abbreviation ?? 'und';

                    $conversions = collect([
                        [
                            'id' => $unitId,
                            'name' => $unitName,
                            'factor' => 1.0
                        ]
                    ]);

                    if ($product->uomConversions) {
                        foreach ($product->uomConversions as $conv) {
                            if ($conv->uom_id && $conv->uom_id != $unitId) {
                                $conversions->push([
                                    'id' => $conv->uom_id,
                                    'name' => $conv->uom?->name ?? 'Conversión',
                                    'factor' => (float) ($conv->conversion_factor ?? 1.0)
                                ]);
                            }
                        }
                    }

                    return [
                        'id' => $product->id,
                        'text' => $product->name . ' (' . ($product->code ?? 'S/C') . ')',
                        'name' => $product->name,
                        'unit' => $unitAbbr,
                        'unitId' => $unitId,
                        'unitName' => $unitName,
                        'categoryId' => $product->category_id,
                        'conversions' => $conversions->toArray(),
                        'requires_serial' => $product->requires_serial,
                        'is_perishable' => $product->is_perishable,
                    ];
                }),
                'pagination' => [
                    'more' => $products->hasMorePages()
                ]
            ]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error en ProductController@searchAjax: ' . $e->getMessage(), [
                'exception' => $e
            ]);

            return response()->json([
                'error' => 'Internal Server Error',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}