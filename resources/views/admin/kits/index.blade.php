@extends('adminlte::page')

@section('title', 'Inventario | Kits')

@section('plugins.Datatables', true) 
@section('plugins.DatatablesPlugins', true) 
@section('plugins.Responsive', true) 

@section('content_header')
    <div class="d-flex justify-content-between">
        <h1 class="m-0 text-dark"><i class="fas fa-boxes"></i> Kits de Inventario</h1>
        <a href="{{ route('admin.kits.create') }}" class="btn btn-primary">
            <i class="fas fa-plus-circle"></i> Crear Nuevo Kit
        </a>
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
                <div class="card-body p-0">
                    <div class="table-responsive">
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
                                                <a href="{{ route('admin.kits.edit', $kit) }}" class="btn btn-default text-primary" title="Editar"><i class="fas fa-edit"></i></a>
                                                <form action="{{ route('admin.kits.destroy', $kit) }}" method="POST" style="display:inline;" onsubmit="return confirm('¿Eliminar Kit?');">
                                                    @csrf @method('DELETE')
                                                    <button type="submit" class="btn btn-default text-danger" title="Eliminar"><i class="fas fa-trash"></i></button>
                                                </form>
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
            </div>
        </div>
    </div>
@stop

@section('js')
    <script>
        $(document).ready(function() {
            const kitsTable = $('#kitsTable').DataTable({
                "responsive": true, "paging": true, "lengthChange": true, "searching": true, "ordering": true, "info": true, "autoWidth": false,
                "order": [[ 1, "asc" ]],
                "language": { "url": "//cdn.datatables.net/plug-ins/1.10.25/i18n/Spanish.json" },
                "columnDefs": [
                    { "orderable": false, "targets": [2] },
                    { "responsivePriority": 1, "targets": 1 }, { "responsivePriority": 2, "targets": 2 }, { "responsivePriority": 3, "targets": 0 }, { "responsivePriority": 100, "targets": [3, 4] }
                ]
            });
            setTimeout(function() { kitsTable.columns.adjust().responsive.recalc(); }, 500);
        });
    </script>
@endsection