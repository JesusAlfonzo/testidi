@extends('adminlte::page')

@section('title', 'Entradas de Stock')

@section('plugins.Datatables', true) 
@section('plugins.DatatablesPlugins', true) 
@section('plugins.Responsive', true) 
@section('plugins.Select2', true)

@section('content_header')
    <div class="d-flex justify-content-between">
        <h1><i class="fas fa-arrow-alt-circle-up"></i> Entradas de Stock</h1>
        @can('entradas_crear')
            <a href="{{ route('admin.stock-in.create') }}" class="btn btn-primary">
                <i class="fas fa-plus-circle"></i> Nueva Entrada
            </a>
        @endcan
    </div>
@stop

@section('css')
    <style>
        table.dataTable.dtr-inline.collapsed > tbody > tr > td:first-child:before, table.dataTable.dtr-inline.collapsed > tbody > tr > th:first-child:before { left: 4px; }
        .table.dataTable.dtr-inline.collapsed > tbody > tr > td:first-child { padding-left: 10px !important; }
    </style>
@stop

@section('content')
    {{-- FILTROS --}}
    <div class="card card-outline card-primary collapsed-card">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-filter"></i> Filtros</h3>
            <div class="card-tools"><button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-plus"></i></button></div>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('admin.stock-in.index') }}">
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Desde</label>
                            <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Hasta</label>
                            <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Producto</label>
                            <select name="product_id" class="form-control select2">
                                <option value="">Todos</option>
                                @foreach($products as $id => $name)
                                    <option value="{{ $id }}" {{ request('product_id') == $id ? 'selected' : '' }}>{{ $name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary btn-block"><i class="fas fa-search"></i> Filtrar</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            @include('admin.partials.session-messages')

            <div class="card card-outline card-success">
                <div class="card-body p-4">
                    <div class="table-responsive">
                        <table id="stockInTable" class="table table-striped table-bordered display nowrap" style="width:100%">
                            <thead>
                                <tr>
                                    <th style="width: 15%">Fecha</th>
                                    <th style="width: 25%">Producto</th>
                                    <th style="width: 10%">Cantidad</th>
                                    <th style="width: 10%">Acciones</th>
                                    <th>Costo Unit.</th>
                                    <th>Proveedor</th>
                                    <th>Documento</th>
                                    <th>Registrado por</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($stockIns as $in)
                                    <tr>
                                        <td data-order="{{ $in->entry_date->timestamp }}"><strong>{{ $in->entry_date->format('Y-m-d') }}</strong></td>
                                        <td>{{ $in->product->code }} - {{ $in->product->name }}</td>
                                        <td><span class="badge badge-success">{{ $in->quantity }} {{ $in->product->unit->abbreviation }}</span></td>
                                        <td>
                                            @can('entradas_eliminar')
                                                <form action="{{ route('admin.stock-in.destroy', $in) }}" method="POST">
                                                    @csrf @method('DELETE')
                                                    <button type="submit" class="btn btn-xs btn-default text-danger" title="Eliminar y Corregir Stock" onclick="return confirm('⚠️ ¡ADVERTENCIA! ¿Estás seguro de eliminar esta entrada? Esto DEVOLVERÁ el stock del producto a su estado anterior.')">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            @endcan
                                        </td>
                                        <td>${{ number_format($in->unit_cost, 2) }}</td>
                                        <td>{{ $in->supplier->name ?? 'Ajuste / N/A' }}</td>
                                        <td>{{ $in->document_type }} <small class="text-muted">{{ $in->document_number }}</small></td>
                                        <td>{{ $in->user->name }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="8" class="text-center">No hay entradas registradas.</td></tr>
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

            const stockInTable = $('#stockInTable').DataTable({
                "responsive": true, "paging": true, "lengthChange": true, "searching": true, "ordering": true, "info": true, "autoWidth": false,
                "order": [[ 0, "desc" ]],
                "language": { "url": "//cdn.datatables.net/plug-ins/1.10.25/i18n/Spanish.json" },
                "columnDefs": [
                    { "orderable": false, "targets": [3] }, { "type": "date", "targets": 0 },
                    { "responsivePriority": 1, "targets": 0 }, { "responsivePriority": 2, "targets": 1 }, { "responsivePriority": 3, "targets": 2 }, { "responsivePriority": 4, "targets": 3 }, { "responsivePriority": 100, "targets": [4, 5, 6, 7] }
                ]
            });
            setTimeout(function() { stockInTable.columns.adjust().responsive.recalc(); }, 500);
        });
    </script>
@endsection