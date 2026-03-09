@extends('adminlte::page')

@section('title', 'Inventario | Kits')

{{-- Plugins necesarios --}}
@section('plugins.Datatables', true) 
@section('plugins.DatatablesPlugins', true) 
@section('plugins.Responsive', true) 

@section('content_header')
    <div class="d-flex justify-content-between">
        <h1 class="m-0 text-dark"><i class="fas fa-boxes"></i> Kits de Inventario</h1>
        @can('kits_crear')
            <a href="{{ route('admin.kits.create') }}" class="btn btn-primary">
                <i class="fas fa-plus-circle"></i> Crear Nuevo Kit
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
        }
        /* Quitar padding extra en PC */
        .table.dataTable.dtr-inline.collapsed > tbody > tr > td:first-child { 
            padding-left: 10px !important; 
        }
    </style>
@stop

@section('content')
    
    {{-- FILTROS (Opcional, si el controlador los soporta) --}}
    <div class="card card-outline card-primary collapsed-card">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-filter"></i> Filtros</h3>
            <div class="card-tools"><button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-plus"></i></button></div>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('admin.kits.index') }}">
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Estado</label>
                            <select name="status" class="form-control">
                                <option value="">Todos</option>
                                <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Activos</option>
                                <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactivos</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary btn-block"><i class="fas fa-search"></i> Filtrar</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            @include('admin.partials.session-messages')

            <div class="card card-outline card-info">
                <div class="card-body p-4">
                    <div class="table-responsive">
                        {{-- 🔑 ID 'kitsTable', clases 'display nowrap' --}}
                        <table id="kitsTable" class="table table-striped table-bordered display nowrap" style="width:100%">
                            <thead>
                                <tr>
                                    <th style="width: 10%">ID</th>
                                    <th style="width: 40%">Nombre</th>
                                    <th style="width: 15%">Acciones</th> 
                                    <th style="width: 20%">Precio Unitario</th>
                                    <th style="width: 15%">Activo</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($kits as $kit)
                                    <tr>
                                        <td>{{ $kit->id }}</td>
                                        <td><strong>{{ $kit->name }}</strong></td>
                                        <td>
                                            <div class="btn-group btn-group-sm" role="group">
                                                @can('kits_editar')
                                                    <a href="{{ route('admin.kits.edit', $kit) }}" class="btn btn-default text-primary" title="Editar"><i class="fas fa-edit"></i></a>
                                                @endcan
                                                
                                                @can('kits_eliminar')
                                                    <button type="button" class="btn btn-default text-danger" title="Eliminar" onclick="confirmDelete('{{ route('admin.kits.destroy', $kit) }}', '{{ $kit->name }}')">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                @endcan
                                            </div>
                                        </td>
                                        <td data-order="{{ $kit->unit_price }}">${{ number_format($kit->unit_price, 2) }}</td>
                                        <td>
                                            @if($kit->is_active) <span class="badge badge-success">Sí</span> @else <span class="badge badge-danger">No</span> @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                {{-- Se elimina la paginación manual de Laravel --}}
            </div>
        </div>
    </div>
@stop

@section('js')
    <script>
        $(document).ready(function() {
            const kitsTable = $('#kitsTable').DataTable({
                "responsive": true, 
                "paging": true, 
                "lengthChange": true, 
                "searching": true, 
                "ordering": true, 
                "info": true, 
                "autoWidth": false,
                "order": [[ 1, "asc" ]], // Ordenar por Nombre (índice 1)
                
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
                    
                    // Ocultar resto en móvil
                    { "responsivePriority": 100, "targets": [3, 4] } 
                ]
            });
            
            // Ajuste de renderizado
            setTimeout(function() { kitsTable.columns.adjust().responsive.recalc(); }, 500);
        });
    </script>
    @include('admin.partials.delete-confirm')
@endsection