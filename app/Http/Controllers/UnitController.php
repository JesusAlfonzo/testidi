<?php

namespace App\Http\Controllers;

use App\Models\Unit;
use App\Http\Requests\StoreUpdateUnitRequest;
use App\Services\CacheService;

class UnitController extends Controller
{
    protected CacheService $cacheService;

    public function __construct(CacheService $cacheService)
    {
        $this->cacheService = $cacheService;
        $this->authorizeResource(Unit::class, 'unit');
    }

    public function index(\Illuminate\Http\Request $request)
    {
        $perPage = $request->get('per_page', 15);
        if (!in_array($perPage, [15, 25, 50, 100])) {
            $perPage = 15;
        }

        if ($request->get('view_all') === 'true') {
            $units = Unit::with('user')->paginate($perPage)->appends($request->except('page'));
        } else {
            $units = Unit::with('user')->paginate($perPage);
        }
        
        return view('admin.units.index', compact('units', 'perPage'));
    }

    public function create()
    {
        return view('admin.units.create');
    }

    public function store(StoreUpdateUnitRequest $request)
    {
        Unit::create($request->validated() + ['user_id' => auth()->id()]);
        $this->cacheService->invalidateUnits();

        return redirect()->route('admin.units.index')
                         ->with('success', 'Unidad de medida creada con éxito.');
    }

    public function edit(Unit $unit)
    {
        return view('admin.units.edit', compact('unit'));
    }

    public function update(StoreUpdateUnitRequest $request, Unit $unit)
    {
        $unit->update($request->validated());
        $this->cacheService->invalidateUnits();

        return redirect()->route('admin.units.index')
                         ->with('success', 'Unidad de medida actualizada con éxito.');
    }

    public function destroy(Unit $unit)
    {
        $productsCount = \App\Models\Product::where('unit_id', $unit->id)->count();
        
        if ($productsCount > 0) {
            return redirect()->route('admin.units.index')
                             ->with('error', 'No se puede eliminar la unidad porque tiene ' . $productsCount . ' producto(s) asociado(s).');
        }
        
        $unit->delete();
        $this->cacheService->invalidateUnits();
        
        return redirect()->route('admin.units.index')
                         ->with('success', 'Unidad de medida eliminada con éxito.');
    }
}
