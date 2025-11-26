@extends('adminlte::page')

@section('title', 'Maestros | Categorías')

{{-- Plugins necesarios: DataTables, Plugins y Responsive --}}
@section('plugins.Datatables', true) 
@section('plugins.DatatablesPlugins', true) 
@section('plugins.Responsive', true) 

@section('content_header')
    <h1 class="m-0 text-dark"><i class="fas fa-layer-group"></i> Categorías de Inventario</h1>
@stop

{{-- 🔑 AJUSTE CRÍTICO EN EL CSS (Para eliminar el padding extra del Responsive en PC) --}}
@section('css')
    <style>
        /* La clase dtr-details es el div que contiene la información expandida en móvil. */
        /* Eliminamos el padding izquierdo que genera el espacio extra en desktop cuando no hay expansión. */
        table.dataTable.dtr-inline.collapsed > tbody > tr > td:first-child:before, 
        table.dataTable.dtr-inline.collapsed > tbody > tr > th:first-child:before {
            left: 4px; /* Movemos el botón de expansión un poco para que no quede pegado */
        }
        
        /* 🔑 Solución principal: Eliminar el padding forzado por Datatables en la primera columna */
        /* Esto elimina el desplazamiento horizontal en desktop */
        .table.dataTable.dtr-inline.collapsed > tbody > tr > td:first-child, 
        .table.dataTable.dtr-inline.collapsed > tbody > tr > th:first-child {
            padding-left: 10px !important; /* Ajustar este valor a un tamaño pequeño si no lo quieres a 0 */
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
                    <h3 class="card-title">Listado de Categorías</h3>
                    <div class="card-tools">
                        @can('categorias_crear')
                            <a href="{{ route('admin.categories.create') }}" class="btn btn-primary btn-sm">
                                <i class="fas fa-plus-circle"></i> Crear Nueva Categoría
                            </a>
                        @endcan
                    </div>
                </div>

                <div class="card-body p-4">
                    {{-- 🔑 table-responsive y clases de Datatables (Se eliminó style="width:100%") --}}
                    <div class="table-responsive">
                        <table id="categoriesTable" class="table table-striped table-bordered display nowrap">
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
                                            <div class="btn-group btn-group-sm" role="group" aria-label="Acciones de Categoría">
                                                @can('categorias_editar')
                                                    <a href="{{ route('admin.categories.edit', $category) }}" class="btn btn-default text-primary" title="Editar">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                @endcan

                                                @can('categorias_eliminar')
                                                    <form action="{{ route('admin.categories.destroy', $category) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-default text-danger" onclick="return confirm('⚠️ ADVERTENCIA: ¿Está seguro de eliminar esta categoría? Esto podría afectar a productos asociados.')" title="Eliminar">
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
                                    <tr>
                                        <td colspan="5" class="text-center">No hay categorías registradas.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div> {{-- Cierre de table-responsive --}}
                </div>
            </div>
        </div>
    </div>
@stop

@section('js')
    <script>
        $(document).ready(function() {
            // Inicializar Datatables
            const categoriesTable = $('#categoriesTable').DataTable({
                "responsive": true, 
                "paging": true,
                "lengthChange": true,
                "searching": true,
                "ordering": true,
                "info": true,
                "autoWidth": false, // 🔑 Importante: Dejar que Datatables decida el ancho
                "order": [[ 1, "asc" ]], 
                "language": {
                    "url": "//cdn.datatables.net/plug-ins/1.10.25/i18n/Spanish.json"
                },
                "columnDefs": [
                    { "orderable": false, "targets": [2] }, // Acciones (índice 2)
                    
                    // PRIORIDADES MÓVIL:
                    { "responsivePriority": 1, "targets": 1 }, // Nombre
                    { "responsivePriority": 2, "targets": 2 }, // Acciones
                    { "responsivePriority": 3, "targets": 0 }, // ID
                    
                    // Bajas prioridades:
                    { "responsivePriority": 100, "targets": [3, 4] } // Descripción, Registrado Por
                ]
            });
            
            // Forzar Redibujo para corregir renderizado inicial
            setTimeout(function() {
                categoriesTable.columns.adjust().responsive.recalc();
            }, 500);
        });
    </script>
@endsection