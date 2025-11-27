<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Activitylog\Models\Activity;
use App\Models\User; // Para el filtro de usuarios

class ActivityLogController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:auditoria_ver');
    }

    public function index(Request $request)
    {
        // 1. Listas para los filtros
        $users = User::orderBy('name')->pluck('name', 'id');
        
        // Lista manual de modelos monitoreados para el filtro (nombre amigable)
        $subjects = [
            'App\Models\Product' => 'Productos',
            'App\Models\InventoryRequest' => 'Solicitudes',
            'App\Models\StockIn' => 'Entradas Stock',
            'App\Models\User' => 'Usuarios',
            'App\Models\Kit' => 'Kits',
            'App\Models\Category' => 'Categorías',
            'App\Models\Supplier' => 'Proveedores',
            'App\Models\Unit' => 'Unidades',
            'App\Models\Location' => 'Ubicaciones',
            'App\Models\Brand' => 'Marcas',
        ];

        // 2. Consulta Base
        $query = Activity::with(['causer', 'subject'])
            ->orderBy('created_at', 'desc');

        // 3. Aplicar Filtros
        
        // Filtro por Usuario Responsable (causer_id)
        if ($request->filled('causer_id')) {
            $query->where('causer_id', $request->causer_id);
        }

        // Filtro por Tipo de Acción (description = created, updated, deleted)
        if ($request->filled('action_type')) {
            $query->where('description', $request->action_type);
        }

        // Filtro por Modelo Afectado (subject_type)
        if ($request->filled('subject_type')) {
            $query->where('subject_type', $request->subject_type);
        }

        // Filtro por Fechas
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // 4. Obtener resultados paginados
        // Usamos paginate para no sobrecargar la memoria, DataTables funcionará en modo cliente con esta página
        // O si prefieres ver muchos, puedes aumentar a 100 o 200.
        $activities = $query->paginate(50);

        return view('admin.audit.index', compact('activities', 'users', 'subjects'));
    }

    public function show($id)
    {
        $log = Activity::with(['causer', 'subject'])->findOrFail($id);
        return view('admin.audit.show', compact('log'));
    }
}