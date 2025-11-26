@extends('adminlte::page')

@section('title', 'Maestros | Marcas')

{{-- Plugins necesarios: DataTables, Plugins y Responsive --}}
@section('plugins.Datatables', true) 
@section('plugins.DatatablesPlugins', true) 
@section('plugins.Responsive', true) 

@section('content_header')
    <div class="d-flex justify-content-between">
        <h1><i class="fas fa-tag"></i> Marcas de Insumos</h1>
        @can('marcas_crear')
            <a href="{{ route('admin.brands.create') }}" class="btn btn-primary">
                <i class="fas fa-plus-circle"></i> Nueva Marca
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
                        <table id="brandsTable" class="table table-striped table-bordered display nowrap">
                            <thead>
                                <tr>
                                    {{-- Prioridad Alta en Móvil --}}
                                    <th style="width: 10%">ID</th>
                                    <th style="width: 25%">Nombre</th>
                                    <th style="width: 15%">Acciones</th>
                                    
                                    {{-- Prioridad Baja en Móvil --}}
                                    <th style="width: 25%">Sitio Web</th>
                                    <th style="width: 15%">Creado por</th>
                                    <th style="width: 10%">Fecha Creación</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($brands as $brand)
                                    <tr>
                                        <td>{{ $brand->id }}</td>
                                        <td><strong>{{ $brand->name }}</strong></td>
                                        <td>
                                            <div class="btn-group btn-group-sm" role="group" aria-label="Acciones de Marca">
                                                @can('marcas_editar')
                                                    <a href="{{ route('admin.brands.edit', $brand) }}" class="btn btn-default text-primary" title="Editar">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                @endcan

                                                @can('marcas_eliminar')
                                                    <form action="{{ route('admin.brands.destroy', $brand) }}" method="POST" style="display:inline-block;">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-default text-danger" title="Eliminar" onclick="return confirm('¿Estás seguro de que deseas eliminar esta marca?')">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                @endcan
                                            </div>
                                        </td>
                                        {{-- Columnas Ocultas en Móvil --}}
                                        <td>
                                            @if($brand->website)
                                                <a href="{{ $brand->website }}" target="_blank" rel="noopener noreferrer">
                                                    <i class="fas fa-external-link-alt"></i> Visitar
                                                </a>
                                            @else
                                                N/A
                                            @endif
                                        </td>
                                        <td>{{ $brand->user->name ?? 'N/A' }}</td>
                                        <td data-order="{{ $brand->created_at->timestamp }}">
                                            {{ $brand->created_at->format('Y-m-d') }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center">No se encontraron marcas registradas.</td>
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
            const brandsTable = $('#brandsTable').DataTable({
                "responsive": true, 
                "paging": true,
                "lengthChange": true,
                "searching": true,
                "ordering": true,
                "info": true,
                "autoWidth": false, 
                "order": [[ 1, "asc" ]], // Ordenar por la columna Nombre (índice 1) ascendente
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
                    { "responsivePriority": 100, "targets": [3, 4, 5] } // Sitio Web, Creado por, Fecha Creación
                ]
            });
            
            // Forzar Redibujo para corregir renderizado inicial
            setTimeout(function() {
                brandsTable.columns.adjust().responsive.recalc();
            }, 500);
        });
    </script>
@endsection