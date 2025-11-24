@extends('adminlte::page')

@section('title', 'Inventario | Productos')

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
        table.dataTable.dtr-inline.collapsed > tbody > tr > td:first-child:before, 
        table.dataTable.dtr-inline.collapsed > tbody > tr > th:first-child:before { left: 4px; }
        .table.dataTable.dtr-inline.collapsed > tbody > tr > td:first-child { padding-left: 10px !important; }
    </style>
@stop

@section('content')
    
    {{-- 🔎 FILTROS --}}
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
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table id="productsTable" class="table table-striped table-bordered display nowrap" style="width:100%">
                            <thead>
                                <tr>
                                    <th style="width: 20%">Nombre</th>
                                    <th style="width: 10%">Stock</th>
                                    <th style="width: 15%">Acciones</th> 
                                    <th style="width: 10%">Código</th>
                                    <th style="width: 15%">Categoría</th>
                                    <th style="width: 10%">Ubicación</th>
                                    <th>Costo/Precio</th>
                                    <th style="width: 10%">Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($products as $product)
                                    <tr>
                                        <td><strong>{{ $product->name }}</strong></td>
                                        <td data-order="{{ $product->stock ?? 0 }}">
                                            <span class="badge {{ $product->stock <= $product->min_stock ? 'badge-danger' : 'badge-success' }}">
                                                {{ $product->stock }} {{ $product->unit->abbreviation ?? 'unid' }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm" role="group">
                                                @can('productos_editar')
                                                    <a href="{{ route('admin.products.edit', $product) }}" class="btn btn-default text-primary" title="Editar"><i class="fas fa-edit"></i></a>
                                                @endcan
                                                @can('kardex_ver')
                                                    <a href="{{ route('admin.reports.kardex', $product->id) }}" class="btn btn-default text-info" title="Ver Kardex"><i class="fas fa-history"></i></a>
                                                @endcan
                                                @can('productos_eliminar')
                                                    <form action="{{ route('admin.products.destroy', $product) }}" method="POST" style="display:inline-block;">
                                                        @csrf @method('DELETE')
                                                        <button type="submit" class="btn btn-default text-danger" title="Eliminar" onclick="return confirm('¿Seguro de eliminar este producto?')"><i class="fas fa-trash"></i></button>
                                                    </form>
                                                @endcan
                                            </div>
                                        </td>
                                        <td><strong>{{ $product->code ?? 'N/A' }}</strong></td>
                                        <td><span class="badge badge-secondary">{{ $product->category->name ?? 'N/A' }}</span></td>
                                        <td>{{ $product->location->name ?? 'N/A' }}</td>
                                        <td><small class="d-block text-muted">C: ${{ number_format($product->cost, 2) }}</small> P: **${{ number_format($product->price, 2) }}**</td>
                                        <td>
                                            <span class="badge badge-{{ $product->is_active ? 'info' : 'secondary' }}">
                                                {{ $product->is_active ? 'Activo' : 'Inactivo' }}
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="8" class="text-center">No se encontraron productos.</td></tr>
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
            $('.select2').select2({ theme: 'bootstrap4' });

            const productsTable = $('#productsTable').DataTable({
                "responsive": true, "paging": true, "lengthChange": true, "searching": true, "ordering": true, "info": true, "autoWidth": false,
                "order": [[ 0, "asc" ]],
                "language": { "url": "//cdn.datatables.net/plug-ins/1.10.25/i18n/Spanish.json" },
                "columnDefs": [
                    { "orderable": false, "targets": [2, 6] },
                    { "responsivePriority": 1, "targets": 0 },
                    { "responsivePriority": 2, "targets": 2 },
                    { "responsivePriority": 3, "targets": 1 },
                    { "responsivePriority": 100, "targets": [3, 4, 5, 6, 7] }
                ]
            });
            setTimeout(function() { productsTable.columns.adjust().responsive.recalc(); }, 500);
        });
    </script>
@endsection