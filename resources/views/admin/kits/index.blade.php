@extends('adminlte::page')

@section('title', 'Inventario | Kits')

{{-- Plugins necesarios --}}
@section('plugins.Datatables', true) 
@section('plugins.DatatablesPlugins', true) 
@section('plugins.Responsive', true) 

@section('content_header')
    <div class="d-flex justify-content-between">
        <h1 class="m-0 text-dark"><i class="fas fa-boxes"></i> Kits de Inventario</h1>
        @can('kits_crear')
            <a href="{{ route('admin.kits.create') }}" class="btn btn-primary">
                <i class="fas fa-plus-circle"></i> Crear Nuevo Kit
            </a>
        @endcan
    </div>
@stop

{{-- Estilos para corregir visualización --}}
@section('css')
    <style>
        table.dataTable.dtr-inline.collapsed > tbody > tr > td:first-child:before, 
        table.dataTable.dtr-inline.collapsed > tbody > tr > th:first-child:before { 
            left: 4px; 
        }
        .table.dataTable.dtr-inline.collapsed > tbody > tr > td:first-child { 
            padding-left: 10px !important; 
        }
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
            <form id="filterForm" method="GET" action="{{ route('admin.kits.index') }}">
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
                <div class="card-body p-4">
                    <div class="table-responsive">
                        <table id="kitsTable" class="table table-striped table-bordered display nowrap" style="width:100%">
                            <thead>
                                <tr>
                                    <th style="width: 10%">ID</th>
                                    <th style="width: 40%">Nombre</th>
                                    <th style="width: 15%">Precio Unitario</th>
                                    <th style="width: 10%">Componentes</th>
                                    <th style="width: 10%">Activo</th>
                                    <th style="width: 15%">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
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
            var table = $('#kitsTable').DataTable({
                responsive: true, 
                processing: true,
                serverSide: true,
                paging: true, 
                lengthChange: true, 
                searching: true, 
                ordering: true, 
                info: true, 
                autoWidth: false,
                order: [[1, 'asc']],

                ajax: {
                    url: "{{ route('admin.kits.index') }}",
                    type: "GET",
                    data: function(d) {
                        d.status = $('select[name="status"]').val();
                    }
                },

                columns: [
                    { data: 'id', name: 'id', orderable: true },
                    { data: 'name', name: 'name', orderable: true },
                    { data: 'unit_price', name: 'unit_price', orderable: true },
                    { data: 'components_count', name: 'components_count', orderable: false },
                    { data: 'is_active', name: 'is_active', orderable: true },
                    { data: 'actions', name: 'actions', orderable: false, searchable: false }
                ],

                language: {
                    decimal: '',
                    emptyTable: 'No hay información disponible',
                    info: 'Mostrando _START_ a _END_ de _TOTAL_ registros',
                    infoEmpty: 'Mostrando 0 a 0 de 0 registros',
                    infoFiltered: '(Filtrado de _MAX_ total registros)',
                    lengthMenu: 'Mostrar _MENU_ registros',
                    loadingRecords: 'Cargando...',
                    processing: 'Procesando...',
                    search: 'Buscar:',
                    zeroRecords: 'Sin resultados encontrados',
                    paginate: {
                        first: 'Primero',
                        last: 'Último',
                        next: 'Siguiente',
                        previous: 'Anterior'
                    }
                },

                columnDefs: [
                    { orderable: false, targets: [3, 5] },
                    { responsivePriority: 1, targets: 1 },
                    { responsivePriority: 2, targets: 5 },
                    { responsivePriority: 3, targets: 0 },
                    { responsivePriority: 100, targets: [2, 3, 4] }
                ]
            });

            $('#filterForm').on('submit', function(e) {
                e.preventDefault();
                table.draw();
            });

            addClearFiltersButton('kitsTable', 'filterForm');
            
            setTimeout(function() { table.columns.adjust().responsive.recalc(); }, 500);
        });
    </script>
    @include('admin.partials.delete-confirm')
@endsection