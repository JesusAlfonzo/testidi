@extends('adminlte::page')

@section('title', 'Reporte de Stock Actual')

{{-- Plugins necesarios --}}
@section('plugins.Datatables', true) 
@section('plugins.DatatablesPlugins', true) 
@section('plugins.Responsive', true) 
@section('plugins.Select2', true) {{-- Carga CSS y JS de Select2 + Tema Bootstrap4 --}}

@section('content_header')
    <h1><i class="fas fa-chart-bar"></i> Reporte de Stock Actual</h1>
@stop

@section('css')
    <style>
        /* Ajustes para la tabla responsive */
        table.dataTable.dtr-inline.collapsed > tbody > tr > td:first-child:before, 
        table.dataTable.dtr-inline.collapsed > tbody > tr > th:first-child:before { left: 4px; }
        .table.dataTable.dtr-inline.collapsed > tbody > tr > td:first-child { padding-left: 10px !important; }

        /* Ajuste opcional para igualar alturas si usas tema estándar */
        .select2-container .select2-selection--single { height: 38px !important; }
        .select2-container--default .select2-selection--single .select2-selection__arrow { height: 36px !important; }
    </style>
@stop

@section('content')
    
    {{-- 🔎 FILTROS DE BÚSQUEDA --}}
    <div class="card card-outline card-primary collapsed-card">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-filter"></i> Filtros de Búsqueda</h3>
            <div class="card-tools">
                <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-plus"></i></button>
            </div>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('admin.reports.stock') }}">
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Categoría</label>
                            <select name="category_id" class="form-control select2">
                                <option value="">Todas</option>
                                @foreach($categories as $id => $name)
                                    <option value="{{ $id }}" {{ request('category_id') == $id ? 'selected' : '' }}>{{ $name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Ubicación</label>
                            <select name="location_id" class="form-control select2">
                                <option value="">Todas</option>
                                @foreach($locations as $id => $name)
                                    <option value="{{ $id }}" {{ request('location_id') == $id ? 'selected' : '' }}>{{ $name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Estado de Stock</label>
                            {{-- Aplicamos select2 aquí también para uniformidad visual --}}
                            <select name="stock_status" class="form-control select2">
                                <option value="">Todos</option>
                                <option value="low" {{ request('stock_status') == 'low' ? 'selected' : '' }}>Bajo Stock (Alerta)</option>
                                <option value="ok" {{ request('stock_status') == 'ok' ? 'selected' : '' }}>Óptimo</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <div class="form-group w-100">
                            <button type="submit" class="btn btn-primary btn-block"><i class="fas fa-search"></i> Filtrar Resultados</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- TABLA DE RESULTADOS --}}
    <div class="card card-outline card-info">
        <div class="card-header">
            <h3 class="card-title">Resultados ({{ count($products) }} productos encontrados)</h3>
            <div class="card-tools">
                {{-- Botones de Exportación --}}
                <a href="{{ route('admin.reports.stock.excel', request()->query()) }}" class="btn btn-success btn-sm" title="Descargar Excel">
                    <i class="fas fa-file-excel"></i> Excel
                </a>
                <a href="{{ route('admin.reports.stock.pdf', request()->query()) }}" class="btn btn-danger btn-sm" target="_blank" title="Ver PDF">
                    <i class="fas fa-file-pdf"></i> PDF
                </a>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table id="stockTable" class="table table-striped table-bordered display nowrap" style="width:100%">
                    <thead>
                        <tr>
                            <th>Estado</th>
                            <th>Producto</th>
                            <th>Stock Actual</th>
                            <th>Código</th>
                            <th>Unidad</th>
                            <th>Mínimo</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($products as $product)
                            @php
                                $isLow = $product->stock <= $product->min_stock;
                            @endphp
                            <tr data-stock-actual="{{ $product->stock }}">
                                <td>
                                    @if ($isLow)
                                        <span class="badge badge-danger">Bajo</span>
                                    @else
                                        <span class="badge badge-success">Óptimo</span>
                                    @endif
                                </td>
                                <td><strong>{{ $product->name }}</strong></td>
                                <td>
                                    <h4><span class="badge badge-{{ $isLow ? 'danger' : 'success' }}">{{ $product->stock }}</span></h4>
                                </td>
                                <td><span class="text-muted">{{ $product->code }}</span></td>
                                <td>{{ $product->unit->abbreviation ?? 'N/A' }}</td>
                                <td>{{ $product->min_stock }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@stop

@section('js')
    <script>
        $(document).ready(function() {
            // 🔑 MEJORA VISUAL: Configuración de Select2
            $('.select2').select2({
                theme: 'bootstrap4', // Usa el tema integrado de AdminLTE para que coincida con los inputs
                width: '100%',       // Fuerza el ancho al 100% del contenedor (evita que se vea apretado)
                placeholder: 'Seleccione una opción',
                allowClear: true
            });

            const stockTable = $('#stockTable').DataTable({
                "responsive": true, 
                "paging": true,
                "lengthChange": true,
                "searching": true,
                "ordering": true,
                "info": true,
                "autoWidth": false, 
                "order": [[ 0, "asc" ]],
                "language": { "url": "//cdn.datatables.net/plug-ins/1.10.25/i18n/Spanish.json" },
                "columnDefs": [
                    { "responsivePriority": 1, "targets": 0 },
                    { "responsivePriority": 2, "targets": 1 },
                    { "responsivePriority": 3, "targets": 2 },
                    { "responsivePriority": 100, "targets": [3, 4, 5] }
                ]
            });
            
            setTimeout(function() { stockTable.columns.adjust().responsive.recalc(); }, 500);
        });
    </script>
@endsection