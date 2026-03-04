@extends('adminlte::page')

@section('title', 'Inventario | Productos')

{{-- Plugins necesarios --}}
@section('plugins.Datatables', true) 
@section('plugins.DatatablesPlugins', true) 
@section('plugins.Responsive', true) 
@section('plugins.Select2', true)

@section('content_header')
    <div class="d-flex justify-content-between">
        <h1><i class="fas fa-box-open"></i> Listado de Productos</h1>
        @can('productos_crear')
            <a href="{{ route('admin.products.create') }}" class="btn btn-primary">
                <i class="fas fa-plus-circle"></i> Agregar Producto
            </a>
        @endcan
    </div>
@stop

@section('css')
    <style>
        /* Ajustes para el botón de expansión en móvil */
        table.dataTable.dtr-inline.collapsed > tbody > tr > td:first-child:before, 
        table.dataTable.dtr-inline.collapsed > tbody > tr > th:first-child:before { 
            left: 4px; 
        }
        /* Quitar padding extra en PC para alinear con el header */
        .table.dataTable.dtr-inline.collapsed > tbody > tr > td:first-child { 
            padding-left: 10px !important; 
        }
    </style>
@stop

@section('content')
    
    {{-- 🔎 FILTROS AVANZADOS --}}
    <div class="card card-outline card-primary collapsed-card">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-filter"></i> Filtros Avanzados</h3>
            <div class="card-tools">
                <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-plus"></i></button>
            </div>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('admin.products.index') }}">
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
                    <div class="col-md-2">
                        <div class="form-group">
                            <label>Estado</label>
                            <select name="status" class="form-control">
                                <option value="">Todos</option>
                                <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Activos</option>
                                <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactivos</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label>Alerta Stock</label>
                            <select name="stock_status" class="form-control">
                                <option value="">Cualquiera</option>
                                <option value="low" {{ request('stock_status') == 'low' ? 'selected' : '' }}>Bajo Stock</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label>Origen</label>
                            <select name="created_on_the_fly" class="form-control">
                                <option value="">Todos</option>
                                <option value="yes" {{ request('created_on_the_fly') == 'yes' ? 'selected' : '' }}>Creado sobre la marcha</option>
                                <option value="no" {{ request('created_on_the_fly') == 'no' ? 'selected' : '' }}>Registrado normalmente</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <div class="form-group w-100">
                            <button type="submit" class="btn btn-primary btn-block"><i class="fas fa-search"></i> Buscar</button>
                        </div>
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
                        {{-- 🔑 ID 'productsTable', clases 'display nowrap' y width 100% --}}
                        <table id="productsTable" class="table table-striped table-bordered display nowrap" style="width:100%">
                            <thead>
                                <tr>
                                    {{-- Prioridades Altas (Visibles en Móvil) --}}
                                    <th style="width: 25%">Nombre</th>
                                    <th style="width: 15%">Stock</th>
                                    <th style="width: 15%">Acciones</th> 

                                    {{-- Prioridades Bajas (Ocultas en Móvil) --}}
                                    <th style="width: 10%">Código</th>
                                    <th style="width: 15%">Categoría</th>
                                    <th style="width: 10%">Ubicación</th>
                                    <th>Costo/Precio</th>
                                    <th style="width: 10%">Estado</th>
                                    <th style="width: 8%">Origen</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($products as $product)
                                    <tr>
                                        <td><strong>{{ $product->name }}</strong></td>
                                        
                                        {{-- Stock con lógica de color --}}
                                        <td data-order="{{ $product->stock ?? 0 }}">
                                            <span class="badge {{ $product->stock <= $product->min_stock ? 'badge-danger' : 'badge-success' }}">
                                                {{ $product->stock }} {{ $product->unit->abbreviation ?? 'unid' }}
                                            </span>
                                        </td>

                                        {{-- Acciones Agrupadas --}}
                                        <td>
                                            <div class="btn-group btn-group-sm" role="group">
                                                @can('productos_editar')
                                                    <a href="{{ route('admin.products.edit', $product) }}" class="btn btn-default text-primary" title="Editar">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                @endcan
                                                @can('kardex_ver')
                                                    <a href="{{ route('admin.reports.kardex', $product->id) }}" class="btn btn-default text-info" title="Ver Kardex">
                                                        <i class="fas fa-history"></i>
                                                    </a>
                                                @endcan
                                                @can('productos_eliminar')
                                                    <form action="{{ route('admin.products.destroy', $product) }}" method="POST" style="display:inline-block;">
                                                        @csrf @method('DELETE')
                                                        <button type="submit" class="btn btn-default text-danger" title="Eliminar" onclick="return confirm('¿Seguro de eliminar este producto? Se recomienda solo si no tiene movimientos históricos.')">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                @endcan
                                            </div>
                                        </td>

                                        {{-- Columnas Secundarias --}}
                                        <td><strong>{{ $product->code ?? 'N/A' }}</strong></td>
                                        <td><span class="badge badge-secondary">{{ $product->category->name ?? 'N/A' }}</span></td>
                                        <td>{{ $product->location->name ?? 'N/A' }}</td>
                                        <td>
                                            <small class="d-block text-muted">C: ${{ number_format($product->cost, 2) }}</small> 
                                            P: <strong>${{ number_format($product->price, 2) }}</strong>
                                        </td>
                                        <td>
                                            <span class="badge badge-{{ $product->is_active ? 'info' : 'secondary' }}">
                                                {{ $product->is_active ? 'Activo' : 'Inactivo' }}
                                            </span>
                                        </td>
                                        <td>
                                            @if($product->created_on_the_fly)
                                                <span class="badge badge-warning" title="Creado desde Cotización/RFQ/OC">
                                                    <i class="fas fa-bolt"></i> Rápido
                                                </span>
                                            @else
                                                <span class="badge badge-secondary">Normal</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    {{-- El mensaje de vacío lo maneja Datatables, pero dejamos esto por estructura --}}
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

@section('js')
    <script>
        $(document).ready(function() {
            // Inicializar Select2 para los filtros
            $('.select2').select2({ theme: 'bootstrap4' });

            // Inicializar DataTables
            const productsTable = $('#productsTable').DataTable({
                "responsive": true, 
                "paging": true, 
                "lengthChange": true, 
                "searching": true, 
                "ordering": true, 
                "info": true, 
                "autoWidth": false,
                "order": [[ 0, "asc" ]], // Ordenar por Nombre (índice 0) ascendente

                // 🔑 TRADUCCIÓN NATIVA (Sin CDN externo)
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
                    { "orderable": false, "targets": [2, 6] }, // No ordenar Acciones ni Costos complejos
                    
                    // 🔑 PRIORIDADES PARA MÓVIL:
                    { "responsivePriority": 1, "targets": 0 }, // Nombre (Siempre visible)
                    { "responsivePriority": 2, "targets": 2 }, // Acciones (Siempre visible)
                    { "responsivePriority": 3, "targets": 1 }, // Stock (Visible si cabe)
                    
                    // Ocultar el resto inmediatamente en móvil (se verán al expandir)
                    { "responsivePriority": 100, "targets": [3, 4, 5, 6, 7, 8] } 
                ]
            });
            
            // Ajuste de renderizado para AdminLTE
            setTimeout(function() { 
                productsTable.columns.adjust().responsive.recalc(); 
            }, 500);
        });
    </script>
@endsection