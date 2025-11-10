<?php

namespace App\Http\Controllers;

use App\Models\Unit;
use Illuminate\Http\Request;
use App\Http\Requests\StoreUpdateUnitRequest;

class UnitController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:unidades_ver')->only('index');
        $this->middleware('permission:unidades_crear')->only('create', 'store');
        $this->middleware('permission:unidades_editar')->only('edit', 'update');
        $this->middleware('permission:unidades_eliminar')->only('destroy');
    }

    public function index()
    {
        $units = Unit::with('user')->paginate(10);
        return view('admin.units.index', compact('units'));
    }

    public function create()
    {
        return view('admin.units.create');
    }

    public function store(StoreUpdateUnitRequest $request)
    {
        $validatedData = $request->validated();

        Unit::create($validatedData + ['user_id' => auth()->id()]);

        return redirect()->route('admin.units.index')
                         ->with('success', '✅ Unidad de medida creada con éxito.');
    }

    public function edit(Unit $unit)
    {
        return view('admin.units.edit', compact('unit'));
    }

    public function update(StoreUpdateUnitRequest $request, Unit $unit)
    {
        $unit->update($request->validated());

        return redirect()->route('admin.units.index')
                         ->with('success', '✅ Unidad de medida actualizada con éxito.');
    }

    public function destroy(Unit $unit)
    {
        // NOTA: Se recomienda aplicar restricción si la unidad está asociada a un producto.
        $unit->delete();
        return redirect()->route('admin.units.index')
                         ->with('success', '✅ Unidad de medida eliminada con éxito.');
    }
}
