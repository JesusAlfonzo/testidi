@extends('adminlte::page')

@section('title', 'Reporte de Stock Actual')

{{-- Plugins necesarios: DataTables, Plugins y Responsive --}}
@section('plugins.Datatables', true) 
@section('plugins.DatatablesPlugins', true) 
@section('plugins.Responsive', true) 

@section('content_header')
    <h1><i class="fas fa-chart-bar"></i> Reporte de Stock Actual</h1>
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
    <div class="card card-outline card-info">
        <div class="card-header">
            <h3 class="card-title">Listado de Productos y Stock</h3>
            <div class="card-tools">
                {{-- Se recomienda usar un botón de Datatables para exportar, pero se mantiene la estructura por ahora --}}
                <a href="#" class="btn btn-tool btn-sm disabled">
                    <i class="fas fa-download"></i> Exportar a Excel (Próximamente con Datatables)
                </a>
            </div>
        </div>
        <div class="card-body p-0">
            {{-- table-responsive y clases de Datatables --}}
            <div class="table-responsive">
                <table id="stockTable" class="table table-striped table-bordered display nowrap">
                    <thead>
                        <tr>
                            <th>Estado</th> {{-- Columna de prioridad más alta --}}
                            <th>Producto</th>
                            <th>Stock Actual</th>
                            <th>Código</th>
                            <th>Unidad</th>
                            <th>Stock Mínimo</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($products as $product)
                            @php
                                // Línea 58: Aquí es donde estaba el caracter invisible. Se ha limpiado.
                                $isLow = $product->stock < $product->minimum_stock;
                            @endphp
                            <tr data-stock-actual="{{ $product->stock }}" data-min-stock="{{ $product->minimum_stock }}">
                                <td>
                                    @if ($isLow)
                                        <span class="badge badge-danger"><i class="fas fa-exclamation-triangle"></i> Bajo</span>
                                    @else
                                        <span class="badge badge-success">Óptimo</span>
                                    @endif
                                </td>
                                <td><strong>{{ $product->name }}</strong></td>
                                <td>
                                    <h4>
                                        <span class="badge badge-{{ $isLow ? 'danger' : 'success' }}">
                                            {{ $product->stock }}
                                        </span>
                                    </h4>
                                </td>
                                <td><span class="text-muted">{{ $product->code }}</span></td>
                                <td>{{ $product->unit->abbreviation ?? 'N/A' }}</td>
                                <td>{{ $product->minimum_stock }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center">No hay productos con stock para mostrar.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div> {{-- Cierre de table-responsive --}}
        </div>
        {{-- Eliminamos el card-footer con paginación de Laravel --}}
    </div>
@stop

{{-- ---------------------------------------------------- --}}
{{-- Sección de Scripts para Inicializar DataTables --}}
{{-- ---------------------------------------------------- --}}
@section('js')
    <script>
        $(document).ready(function() {
            // Inicializar Datatables
            const stockTable = $('#stockTable').DataTable({
                "responsive": true, 
                "paging": true,
                "lengthChange": true,
                "searching": true,
                "ordering": true,
                "info": true,
                "autoWidth": false, 
                "order": [[ 0, "asc" ]], // Ordenar por la columna de Estado (índice 0)
                "language": {
                    "url": "//cdn.datatables.net/plug-ins/1.10.25/i18n/Spanish.json"
                },
                "columnDefs": [
                    { 
                        // Columna 2 (Stock Actual) es numérica y contiene HTML,
                        // por lo que debemos indicarle cómo ordenar. Usaremos el valor del badge (el número).
                        "targets": 2, 
                        "render": function(data, type, row) {
                            if (type === 'sort' || type === 'type') {
                                // Extrae el número del stock dentro de la etiqueta <span> para ordenar
                                return $('<div>').html(data).find('span').text().trim();
                            }
                            return data;
                        }
                    },
                    {
                        // Columna 0 (Estado) es la que indica si está Bajo/Óptimo. 
                        // Usaremos la columna 2 (Stock Actual) para ordenar por número.
                        "targets": 0,
                        "orderData": 2 
                    },
                    // 🔑 PRIORIDADES MÓVIL:
                    { "responsivePriority": 1, "targets": 0 }, // Estado (Alerta)
                    { "responsivePriority": 2, "targets": 1 }, // Producto
                    { "responsivePriority": 3, "targets": 2 }, // Stock Actual
                    
                    // 🔑 Bajas prioridades: Se ocultan primero
                    { "responsivePriority": 100, "targets": [3, 4, 5] } // Código, Unidad, Stock Mínimo
                ]
            });
            
            // Forzar Redibujo para corregir renderizado inicial
            setTimeout(function() {
                stockTable.columns.adjust().responsive.recalc();
            }, 500);
        });
    </script>
@endsection