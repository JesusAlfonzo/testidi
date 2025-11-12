@extends('adminlte::page')

@section('title', 'Maestros | Unidades de Medida')

{{-- Plugins necesarios: DataTables, Plugins y Responsive --}}
@section('plugins.Datatables', true) 
@section('plugins.DatatablesPlugins', true) 
@section('plugins.Responsive', true) 

@section('content_header')
    <h1 class="m-0 text-dark"><i class="fas fa-ruler-combined"></i> Unidades de Medida</h1>
@stop

{{-- 🔑 AJUSTE CRÍTICO EN EL CSS (Para eliminar el padding extra del Responsive en PC) --}}
@section('css')
    <style>
        /* Ajusta la posición del botón de expansión (+) */
        table.dataTable.dtr-inline.collapsed > tbody > tr > td:first-child:before, 
        table.dataTable.dtr-inline.collapsed > tbody > tr > th:first-child:before {
            left: 4px; 
        }
        
        /* Elimina el padding izquierdo de la primera columna para evitar el scroll horizontal en PC */
        .table.dataTable.dtr-inline.collapsed > tbody > tr > td:first-child, 
        .table.dataTable.dtr-inline.collapsed > tbody > tr > th:first-child {
            padding-left: 10px !important; 
        }
    </style>
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            {{-- Mensajes de feedback --}}
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            <div class="card card-outline card-info">
                <div class="card-header">
                    <h3 class="card-title">Listado de Unidades</h3>
                    <div class="card-tools">
                        @can('unidades_crear')
                            <a href="{{ route('admin.units.create') }}" class="btn btn-primary btn-sm">
                                <i class="fas fa-plus-circle"></i> Crear Nueva Unidad
                            </a>
                        @endcan
                    </div>
                </div>

                <div class="card-body p-0">
                    {{-- table-responsive y clases de Datatables --}}
                    <div class="table-responsive">
                        <table id="unitsTable" class="table table-striped table-bordered display nowrap">
                            <thead>
                                <tr>
                                    {{-- Prioridades Altas: ID, Nombre, Acciones --}}
                                    <th style="width: 10%">ID</th>
                                    <th style="width: 35%">Nombre</th>
                                    <th style="width: 15%">Acciones</th>
                                    
                                    {{-- Prioridades Bajas --}}
                                    <th style="width: 25%">Abreviatura</th>
                                    <th style="width: 15%">Registrado Por</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($units as $unit)
                                    <tr>
                                        <td>{{ $unit->id }}</td>
                                        <td><strong>{{ $unit->name }}</strong></td>
                                        <td>
                                            <div class="btn-group btn-group-sm" role="group" aria-label="Acciones de Unidad">
                                                @can('unidades_editar')
                                                    <a href="{{ route('admin.units.edit', $unit) }}" class="btn btn-default text-primary" title="Editar">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                @endcan

                                                @can('unidades_eliminar')
                                                    <form action="{{ route('admin.units.destroy', $unit) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-default text-danger" onclick="return confirm('⚠️ ADVERTENCIA: ¿Está seguro de eliminar esta Unidad? Esto podría afectar a productos asociados.')" title="Eliminar">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                @endcan
                                            </div>
                                        </td>
                                        {{-- Columnas Ocultas en Móvil --}}
                                        <td><span class="badge badge-info">{{ $unit->abbreviation }}</span></td>
                                        <td>{{ $unit->user->name ?? 'Sistema' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center">No hay unidades de medida registradas.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div> {{-- Cierre de table-responsive --}}
                </div>

                {{-- Eliminamos el card-footer con paginación de Laravel --}}
            </div>
        </div>
    </div>
@stop

{{-- ---------------------------------------------------- --}}
{{-- Sección de Scripts para Inicializar DataTables --}}
{{-- ---------------------------------------------------- --}}
@section('js')
    <script>
        $(document).ready(function() {
            // Inicializar Datatables
            const unitsTable = $('#unitsTable').DataTable({
                "responsive": true, 
                "paging": true,
                "lengthChange": true,
                "searching": true,
                "ordering": true,
                "info": true,
                "autoWidth": false, 
                "order": [[ 1, "asc" ]], // Ordenar por la columna Nombre (índice 1) ascendente por defecto
                "language": {
                    "url": "//cdn.datatables.net/plug-ins/1.10.25/i18n/Spanish.json"
                },
                "columnDefs": [
                    { "orderable": false, "targets": [2] }, // Acciones (índice 2)
                    
                    // 🔑 PRIORIDADES MÓVIL:
                    { "responsivePriority": 1, "targets": 1 }, // Nombre
                    { "responsivePriority": 2, "targets": 2 }, // Acciones
                    { "responsivePriority": 3, "targets": 0 }, // ID
                    
                    // 🔑 Bajas prioridades: Se ocultan primero
                    { "responsivePriority": 100, "targets": [3, 4] } // Abreviatura, Registrado Por
                ]
            });
            
            // Forzar Redibujo para corregir renderizado inicial
            setTimeout(function() {
                unitsTable.columns.adjust().responsive.recalc();
            }, 500);
        });
    </script>
@endsection