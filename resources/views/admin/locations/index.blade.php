@extends('adminlte::page')

@section('title', 'Maestros | Ubicaciones')

{{-- Plugins necesarios: DataTables, Plugins y Responsive --}}
@section('plugins.Datatables', true) 
@section('plugins.DatatablesPlugins', true) 
@section('plugins.Responsive', true) 

@section('content_header')
    <div class="d-flex justify-content-between">
        <h1><i class="fas fa-warehouse"></i> Ubicaciones de Inventario</h1>
        @can('ubicaciones_crear')
            <a href="{{ route('admin.locations.create') }}" class="btn btn-primary">
                <i class="fas fa-plus-circle"></i> Nueva Ubicación
            </a>
        @endcan
    </div>
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
            @include('admin.partials.session-messages')

            <div class="card card-outline card-info">
                <div class="card-body p-4">
                    {{-- table-responsive y clases de Datatables --}}
                    <div class="table-responsive">
                        <table id="locationsTable" class="table table-striped table-bordered display nowrap">
                            <thead>
                                <tr>
                                    {{-- Prioridad Alta en Móvil --}}
                                    <th style="width: 10%">ID</th>
                                    <th style="width: 30%">Nombre</th>
                                    <th style="width: 15%">Acciones</th>
                                    
                                    {{-- Prioridad Baja en Móvil --}}
                                    <th style="width: 25%">Detalles</th>
                                    <th style="width: 10%">Creado por</th>
                                    <th style="width: 10%">Fecha Creación</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($locations as $location)
                                    <tr>
                                        <td>{{ $location->id }}</td>
                                        <td><strong>{{ $location->name }}</strong></td>
                                        <td>
                                            <div class="btn-group btn-group-sm" role="group" aria-label="Acciones de Ubicación">
                                                @can('ubicaciones_editar')
                                                    <a href="{{ route('admin.locations.edit', $location) }}" class="btn btn-default text-primary" title="Editar">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                @endcan

                                                @can('ubicaciones_eliminar')
                                                    <form action="{{ route('admin.locations.destroy', $location) }}" method="POST" style="display:inline-block;">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-default text-danger" title="Eliminar" onclick="return confirm('¿Estás seguro de que deseas eliminar esta ubicación? Se recomienda solo si no tiene productos asociados.')">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                @endcan
                                            </div>
                                        </td>
                                        {{-- Columnas Ocultas en Móvil --}}
                                        <td>{{ Str::limit($location->details, 50) ?? 'N/A' }}</td>
                                        <td>{{ $location->user->name ?? 'N/A' }}</td>
                                        <td data-order="{{ $location->created_at->timestamp }}">
                                            {{ $location->created_at->format('Y-m-d') }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center">No se encontraron ubicaciones registradas.</td>
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
            const locationsTable = $('#locationsTable').DataTable({
                "responsive": true, 
                "paging": true,
                "lengthChange": true,
                "searching": true,
                "ordering": true,
                "info": true,
                "autoWidth": false, 
                "order": [[ 5, "desc" ]], // Ordenar por la columna Fecha Creación (índice 5)
                "language": {
                    "url": "//cdn.datatables.net/plug-ins/1.10.25/i18n/Spanish.json"
                },
                "columnDefs": [
                    { "orderable": false, "targets": [2] }, // Acciones (índice 2)
                    { "type": "date", "targets": 5 }, // Fecha Creación (índice 5)

                    // 🔑 PRIORIDADES MÓVIL:
                    { "responsivePriority": 1, "targets": 1 }, // Nombre
                    { "responsivePriority": 2, "targets": 2 }, // Acciones
                    { "responsivePriority": 3, "targets": 0 }, // ID
                    
                    // 🔑 Bajas prioridades: Se ocultan primero
                    { "responsivePriority": 100, "targets": [3, 4, 5] } // Detalles, Creado por, Fecha Creación
                ]
            });
            
            // Forzar Redibujo para corregir renderizado inicial
            setTimeout(function() {
                locationsTable.columns.adjust().responsive.recalc();
            }, 500);
        });
    </script>
@endsection