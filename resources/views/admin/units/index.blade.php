@extends('adminlte::page')

@section('title', 'Maestros | Unidades de Medida')

{{-- Plugins necesarios --}}
@section('plugins.Datatables', true) 
@section('plugins.DatatablesPlugins', true) 
@section('plugins.Responsive', true) 

@section('content_header')
    <div class="d-flex justify-content-between">
        <h1 class="m-0 text-dark"><i class="fas fa-ruler-combined"></i> Unidades de Medida</h1>
        @can('unidades_crear')
            <a href="{{ route('admin.units.create') }}" class="btn btn-primary">
                <i class="fas fa-plus-circle"></i> Crear Nueva Unidad
            </a>
        @endcan
    </div>
@stop

{{-- Estilos para corregir visualización --}}
@section('css')
    <style>
        /* Ajuste para el botón de expansión en móvil */
        table.dataTable.dtr-inline.collapsed > tbody > tr > td:first-child:before, 
        table.dataTable.dtr-inline.collapsed > tbody > tr > th:first-child:before { 
            left: 4px; 
            top: 50%;
            transform: translateY(-50%);
        }
        /* Quitar padding extra en PC */
        .table.dataTable.dtr-inline.collapsed > tbody > tr > td:first-child { 
            padding-left: 10px !important; 
        }
    </style>
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            @include('admin.partials.session-messages')

            <div class="card card-outline card-info">
                <div class="card-header">
                    <h3 class="card-title">Listado de Unidades</h3>
                </div>

                <div class="card-body p-4">
                    <div class="table-responsive">
                        {{-- 🔑 ID 'unitsTable', clases 'display nowrap' --}}
                        <table id="unitsTable" class="table table-striped table-bordered display nowrap" style="width:100%">
                            <thead>
                                <tr>
                                    {{-- Prioridad Alta --}}
                                    <th style="width: 10%">ID</th>
                                    <th style="width: 35%">Nombre</th>
                                    <th style="width: 15%">Acciones</th>
                                    
                                    {{-- Prioridad Baja --}}
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
                                            <div class="btn-group btn-group-sm" role="group">
                                                @can('unidades_editar')
                                                    <a href="{{ route('admin.units.edit', $unit) }}" class="btn btn-default text-primary" title="Editar">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                @endcan

                                                @can('unidades_eliminar')
                                                    <form action="{{ route('admin.units.destroy', $unit) }}" method="POST" style="display:inline-block;">
                                                        @csrf @method('DELETE')
                                                        <button type="submit" class="btn btn-default text-danger" onclick="return confirm('⚠️ ADVERTENCIA: ¿Está seguro de eliminar esta Unidad? Esto podría afectar a productos asociados.')" title="Eliminar">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                @endcan
                                            </div>
                                        </td>
                                        {{-- Columnas Secundarias --}}
                                        <td><span class="badge badge-info">{{ $unit->abbreviation }}</span></td>
                                        <td>{{ $unit->user->name ?? 'Sistema' }}</td>
                                    </tr>
                                @empty
                                    {{-- DataTables manejará el vacío --}}
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                {{-- Eliminamos la paginación manual --}}
            </div>
        </div>
    </div>
@stop

@section('js')
    <script>
        $(document).ready(function() {
            const unitsTable = $('#unitsTable').DataTable({
                "responsive": true, 
                "paging": true, 
                "lengthChange": true, 
                "searching": true, 
                "ordering": true, 
                "info": true, 
                "autoWidth": false,
                "order": [[ 1, "asc" ]], // Ordenar por Nombre
                
                // 🔑 Traducción Nativa
                "language": {
                    "decimal": "",
                    "emptyTable": "No hay información disponible",
                    "info": "Mostrando _START_ a _END_ de _TOTAL_ registros",
                    "infoEmpty": "Mostrando 0 a 0 de 0 registros",
                    "infoFiltered": "(Filtrado de _MAX_ total registros)",
                    "infoPostFix": "",
                    "thousands": ",",
                    "lengthMenu": "Mostrar _MENU_ registros",
                    "loadingRecords": "Cargando...",
                    "processing": "Procesando...",
                    "search": "Buscar:",
                    "zeroRecords": "Sin resultados encontrados",
                    "paginate": {
                        "first": "Primero",
                        "last": "Último",
                        "next": "Siguiente",
                        "previous": "Anterior"
                    }
                },

                "columnDefs": [
                    { "orderable": false, "targets": [2] }, // Acciones no ordenables
                    
                    // 🔑 PRIORIDADES MÓVIL
                    { "responsivePriority": 1, "targets": 1 }, // Nombre
                    { "responsivePriority": 2, "targets": 2 }, // Acciones
                    { "responsivePriority": 3, "targets": 0 }, // ID
                    
                    // Ocultar el resto
                    { "responsivePriority": 100, "targets": [3, 4] } 
                ]
            });
            
            // Ajuste de renderizado
            setTimeout(function() { unitsTable.columns.adjust().responsive.recalc(); }, 500);
        });
    </script>
@endsection