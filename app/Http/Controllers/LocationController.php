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

    public function index()
    {
        $locations = Location::with('user')->get();
        return view('admin.locations.index', compact('locations'));
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
        $location->delete();
        $this->cacheService->invalidateLocations();
        
        return redirect()->route('admin.locations.index')
                         ->with('success', 'Ubicación eliminada con éxito.');
    }
}
