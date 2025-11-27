@extends('adminlte::page')

@section('title', 'Maestros | Categorías')

{{-- Plugins necesarios --}}
@section('plugins.Datatables', true) 
@section('plugins.DatatablesPlugins', true) 
@section('plugins.Responsive', true) 

@section('content_header')
    <div class="d-flex justify-content-between">
        <h1 class="m-0 text-dark"><i class="fas fa-layer-group"></i> Categorías de Inventario</h1>
        @can('categorias_crear')
            <a href="{{ route('admin.categories.create') }}" class="btn btn-primary">
                <i class="fas fa-plus-circle"></i> Crear Nueva Categoría
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
                    <h3 class="card-title">Listado de Categorías</h3>
                </div>

                <div class="card-body p-4">
                    <div class="table-responsive">
                        {{-- 🔑 ID 'categoriesTable', clases 'display nowrap' --}}
                        <table id="categoriesTable" class="table table-striped table-bordered display nowrap" style="width:100%">
                            <thead>
                                <tr>
                                    {{-- Prioridad Alta en Móvil --}}
                                    <th style="width: 10%">ID</th>
                                    <th style="width: 35%">Nombre</th>
                                    <th style="width: 15%">Acciones</th>
                                    
                                    {{-- Prioridad Baja en Móvil --}}
                                    <th style="width: 30%">Descripción</th>
                                    <th style="width: 10%">Registrado Por</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($categories as $category)
                                    <tr>
                                        <td>{{ $category->id }}</td>
                                        <td><strong>{{ $category->name }}</strong></td>
                                        <td>
                                            <div class="btn-group btn-group-sm" role="group">
                                                @can('categorias_editar')
                                                    <a href="{{ route('admin.categories.edit', $category) }}" class="btn btn-default text-primary" title="Editar">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                @endcan

                                                @can('categorias_eliminar')
                                                    <form action="{{ route('admin.categories.destroy', $category) }}" method="POST" style="display:inline-block;">
                                                        @csrf @method('DELETE')
                                                        <button type="submit" class="btn btn-default text-danger" onclick="return confirm('⚠️ ADVERTENCIA: ¿Está seguro de eliminar esta categoría?')" title="Eliminar">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                @endcan
                                            </div>
                                        </td>
                                        {{-- Columnas Ocultas en Móvil --}}
                                        <td>{{ Str::limit($category->description, 50) ?? 'N/A' }}</td>
                                        <td>{{ $category->user->name ?? 'Sistema' }}</td>
                                    </tr>
                                @empty
                                    {{-- DataTables maneja el vacío --}}
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
            const categoriesTable = $('#categoriesTable').DataTable({
                "responsive": true, 
                "paging": true, 
                "lengthChange": true, 
                "searching": true, 
                "ordering": true, 
                "info": true, 
                "autoWidth": false,
                "order": [[ 1, "asc" ]], // Ordenar por Nombre
                
                // 🔑 Traducción Nativa (Sin CDN)
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
            setTimeout(function() { categoriesTable.columns.adjust().responsive.recalc(); }, 500);
        });
    </script>
@endsection