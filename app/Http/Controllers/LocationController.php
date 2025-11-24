<?php

namespace App\Http\Controllers;

use App\Models\Location;
use Illuminate\Http\Request;
use App\Http\Requests\StoreUpdateLocationRequest;

class LocationController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:ubicaciones_ver')->only('index');
        $this->middleware('permission:ubicaciones_crear')->only('create', 'store');
        $this->middleware('permission:ubicaciones_editar')->only('edit', 'update');
        $this->middleware('permission:ubicaciones_eliminar')->only('destroy');
    }

    public function index()
    {
        $locations = Location::with('user')->paginate(10);
        return view('admin.locations.index', compact('locations'));
    }

    public function create()
    {
        return view('admin.locations.create');
    }

    public function store(StoreUpdateLocationRequest $request)
    {
        // ESTANDARIZACIÓN: Creación directa
        Location::create($request->validated() + ['user_id' => auth()->id()]);

        return redirect()->route('admin.locations.index')
                         ->with('success', '✅ Ubicación creada con éxito.');
    }

    public function edit(Location $location)
    {
        return view('admin.locations.edit', compact('location'));
    }

    public function update(StoreUpdateLocationRequest $request, Location $location)
    {
        // ESTANDARIZACIÓN: Actualización directa
        $location->update($request->validated());

        return redirect()->route('admin.locations.index')
                         ->with('success', '✅ Ubicación actualizada con éxito.');
    }

    public function destroy(Location $location)
    {
        // Se recomienda aplicar restricción si la ubicación está asociada a un producto.
        $location->delete();
        return redirect()->route('admin.locations.index')
                         ->with('success', '✅ Ubicación eliminada con éxito.');
    }
}
