<?php

namespace App\Http\Controllers;

use App\Models\Location;
use App\Http\Requests\StoreUpdateLocationRequest;
use App\Services\CacheService;

class LocationController extends Controller
{
    protected CacheService $cacheService;

    public function __construct(CacheService $cacheService)
    {
        $this->cacheService = $cacheService;
        $this->authorizeResource(Location::class, 'location');
    }

    public function index(\Illuminate\Http\Request $request)
    {
        $perPage = $request->get('per_page', 15);
        if (!in_array($perPage, [15, 25, 50, 100])) {
            $perPage = 15;
        }

        if ($request->get('view_all') === 'true') {
            $locations = Location::with('user')->paginate($perPage)->appends($request->except('page'));
        } else {
            $locations = Location::with('user')->paginate($perPage);
        }
        
        return view('admin.locations.index', compact('locations', 'perPage'));
    }

    public function create()
    {
        return view('admin.locations.create');
    }

    public function store(StoreUpdateLocationRequest $request)
    {
        Location::create($request->validated() + ['user_id' => auth()->id()]);
        $this->cacheService->invalidateLocations();

        return redirect()->route('admin.locations.index')
                         ->with('success', 'Ubicación creada con éxito.');
    }

    public function edit(Location $location)
    {
        return view('admin.locations.edit', compact('location'));
    }

    public function update(StoreUpdateLocationRequest $request, Location $location)
    {
        $location->update($request->validated());
        $this->cacheService->invalidateLocations();

        return redirect()->route('admin.locations.index')
                         ->with('success', 'Ubicación actualizada con éxito.');
    }

    public function destroy(Location $location)
    {
        $productsCount = \App\Models\Product::where('location_id', $location->id)->count();
        
        if ($productsCount > 0) {
            return redirect()->route('admin.locations.index')
                             ->with('error', 'No se puede eliminar la ubicación porque tiene ' . $productsCount . ' producto(s) asociado(s).');
        }
        
        $location->delete();
        $this->cacheService->invalidateLocations();
        
        return redirect()->route('admin.locations.index')
                         ->with('success', 'Ubicación eliminada con éxito.');
    }
}
