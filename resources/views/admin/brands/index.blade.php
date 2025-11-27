@extends('adminlte::page')

@section('title', 'Maestros | Marcas')

{{-- Plugins necesarios --}}
@section('plugins.Datatables', true) 
@section('plugins.DatatablesPlugins', true) 
@section('plugins.Responsive', true) 

@section('content_header')
    <div class="d-flex justify-content-between">
        <h1 class="m-0 text-dark"><i class="fas fa-tag"></i> Marcas de Insumos</h1>
        @can('marcas_crear')
            <a href="{{ route('admin.brands.create') }}" class="btn btn-primary">
                <i class="fas fa-plus-circle"></i> Nueva Marca
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
                    <h3 class="card-title">Listado de Marcas</h3>
                </div>

                <div class="card-body p-4">
                    <div class="table-responsive">
                        {{-- 🔑 ID 'brandsTable', clases 'display nowrap' --}}
                        <table id="brandsTable" class="table table-striped table-bordered display nowrap" style="width:100%">
                            <thead>
                                <tr>
                                    {{-- Prioridad Alta --}}
                                    <th style="width: 10%">ID</th>
                                    <th style="width: 30%">Nombre</th>
                                    <th style="width: 15%">Acciones</th>
                                    
                                    {{-- Prioridad Baja --}}
                                    <th style="width: 25%">Sitio Web</th>
                                    <th style="width: 10%">Registrado Por</th>
                                    <th style="width: 10%">Fecha Creación</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($brands as $brand)
                                    <tr>
                                        <td>{{ $brand->id }}</td>
                                        <td><strong>{{ $brand->name }}</strong></td>
                                        <td>
                                            <div class="btn-group btn-group-sm" role="group">
                                                @can('marcas_editar')
                                                    <a href="{{ route('admin.brands.edit', $brand) }}" class="btn btn-default text-primary" title="Editar">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                @endcan

                                                @can('marcas_eliminar')
                                                    <form action="{{ route('admin.brands.destroy', $brand) }}" method="POST" style="display:inline-block;">
                                                        @csrf @method('DELETE')
                                                        <button type="submit" class="btn btn-default text-danger" onclick="return confirm('¿Estás seguro de que deseas eliminar esta marca?')" title="Eliminar">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                @endcan
                                            </div>
                                        </td>
                                        
                                        {{-- Columnas Secundarias --}}
                                        <td>
                                            @if($brand->website)
                                                <a href="{{ $brand->website }}" target="_blank" rel="noopener noreferrer">
                                                    <i class="fas fa-external-link-alt"></i> Visitar
                                                </a>
                                            @else
                                                N/A
                                            @endif
                                        </td>
                                        <td>{{ $brand->user->name ?? 'Sistema' }}</td>
                                        <td data-order="{{ $brand->created_at->timestamp }}">
                                            {{ $brand->created_at->format('Y-m-d') }}
                                        </td>
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
            const brandsTable = $('#brandsTable').DataTable({
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
                    { "type": "date", "targets": 5 },       // Fecha
                    
                    // 🔑 PRIORIDADES MÓVIL
                    { "responsivePriority": 1, "targets": 1 }, // Nombre
                    { "responsivePriority": 2, "targets": 2 }, // Acciones
                    { "responsivePriority": 3, "targets": 0 }, // ID
                    
                    // Ocultar el resto
                    { "responsivePriority": 100, "targets": [3, 4, 5] } 
                ]
            });
            
            // Ajuste de renderizado
            setTimeout(function() { brandsTable.columns.adjust().responsive.recalc(); }, 500);
        });
    </script>
@endsection