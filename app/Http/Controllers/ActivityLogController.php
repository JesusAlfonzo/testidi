<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Activitylog\Models\Activity; // Modelo nativo del paquete

class ActivityLogController extends Controller
{
    public function __construct()
    {
        // Solo el Super Admin o Auditor debería ver esto
        $this->middleware('can:auditoria_ver');
    }

    public function index()
    {
        // Listado ordenado cronológicamente
        $activities = Activity::with(['causer', 'subject'])
            ->orderBy('created_at', 'desc')
            ->paginate(50); // Usamos paginación de Laravel + DataTables en cliente

        return view('admin.audit.index', compact('activities'));
    }

    public function show($id)
    {
        $log = Activity::with(['causer', 'subject'])->findOrFail($id);
        
        return view('admin.audit.show', compact('log'));
    }
}