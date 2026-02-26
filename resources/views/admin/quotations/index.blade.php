@extends('adminlte::page')

@section('title', 'Compras | Cotizaciones de Proveedores')

@section('plugins.Datatables', true)
@section('plugins.Responsive', true)

@section('content_header')
    <div class="d-flex justify-content-between">
        <h1><i class="fas fa-file-alt"></i> Cotizaciones de Proveedores</h1>
        @can('cotizaciones_crear')
            <a href="{{ route('admin.quotations.create') }}" class="btn btn-primary">
                <i class="fas fa-plus-circle"></i> Nueva Cotización
            </a>
        @endcan
    </div>
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            @include('admin.partials.session-messages')

            <div class="card card-outline card-info">
                <div class="card-header">
                    <h3 class="card-title">Filtros</h3>
                </div>
                <div class="card-body">
                    <form method="GET" class="row">
                        <div class="col-md-3">
                            <select name="status" class="form-control">
                                <option value="">Todos los estados</option>
                                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pendiente</option>
                                <option value="selected" {{ request('status') == 'selected' ? 'selected' : '' }}>Seleccionada</option>
                                <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Aprobada</option>
                                <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rechazada</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Filtrar</button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card card-outline card-info mt-3">
                <div class="card-body p-4">
                    <div class="table-responsive">
                        <table id="quotationsTable" class="table table-striped table-bordered display nowrap" style="width:100%">
                            <thead>
                                <tr>
                                    <th>Código</th>
                                    <th>Proveedor</th>
                                    <th>RFQ</th>
                                    <th>Estado</th>
                                    <th>Total</th>
                                    <th>Fecha</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($quotations as $quotation)
                                    <tr>
                                        <td><strong>{{ $quotation->code }}</strong></td>
                                        <td>
                                            @if($quotation->hasRegisteredSupplier())
                                                <span class="badge badge-success"><i class="fas fa-check"></i></span>
                                            @else
                                                <span class="badge badge-warning" title="Temporal"><i class="fas fa-clock"></i></span>
                                            @endif
                                            {{ $quotation->getSupplierDisplayName() }}
                                        </td>
                                        <td>
                                            @if($quotation->rfq)
                                                <a href="{{ route('admin.rfq.show', $quotation->rfq) }}">{{ $quotation->rfq->code }}</a>
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td>{!! $quotation->status_badge !!}</td>
                                        <td>${{ number_format($quotation->total, 2) }}</td>
                                        <td>{{ $quotation->date_issued->format('d/m/Y') }}</td>
                                        <td>
                                            <div class="btn-group btn-group-sm" role="group">
                                                <a href="{{ route('admin.quotations.show', $quotation) }}" class="btn btn-default text-info" title="Ver">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                @if($quotation->isEditable())
                                                    @can('cotizaciones_editar')
                                                        <a href="{{ route('admin.quotations.edit', $quotation) }}" class="btn btn-default text-primary" title="Editar">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                    @endcan
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
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
            $('#quotationsTable').DataTable({
                "responsive": true,
                "paging": true,
                "lengthChange": true,
                "searching": true,
                "ordering": true,
                "info": true,
                "autoWidth": false,
                "order": [[ 5, "desc" ]],
                "language": {
                    "emptyTable": "No hay cotizaciones registradas",
                    "info": "Mostrando _START_ a _END_ de _TOTAL_ registros",
                    "infoEmpty": "Mostrando 0 a 0 de 0 registros",
                    "infoFiltered": "(Filtrado de _MAX_ total registros)",
                    "lengthMenu": "Mostrar _MENU_ registros",
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
                    { "orderable": false, "targets": [6] }
                ]
            });
        });
    </script>
@endsection
