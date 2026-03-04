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

    public function index()
    {
        $units = Unit::with('user')->get();
        return view('admin.units.index', compact('units'));
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
        $unit->delete();
        $this->cacheService->invalidateUnits();
        
        return redirect()->route('admin.units.index')
                         ->with('success', 'Unidad de medida eliminada con éxito.');
    }
}
