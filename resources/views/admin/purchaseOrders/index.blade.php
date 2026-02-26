@extends('adminlte::page')

@section('title', 'Compras | Ordenes de Compra')

@section('plugins.Datatables', true)
@section('plugins.Responsive', true)

@section('content_header')
    <div class="d-flex justify-content-between">
        <h1><i class="fas fa-shopping-cart"></i> Ordenes de Compra</h1>
        @can('ordenes_compra_crear')
            <a href="{{ route('admin.purchaseOrders.create') }}" class="btn btn-primary">
                <i class="fas fa-plus-circle"></i> Nueva OC
            </a>
        @endcan
    </div>
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            @include('admin.partials.session-messages')

            <div class="card card-outline card-info">
                <div class="card-body p-4">
                    <div class="table-responsive">
                        <table id="ordersTable" class="table table-striped table-bordered display nowrap" style="width:100%">
                            <thead>
                                <tr>
                                    <th>Código</th>
                                    <th>Proveedor</th>
                                    <th>Estado</th>
                                    <th>Total</th>
                                    <th>Fecha</th>
                                    <th>Entrega</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($orders as $order)
                                    <tr>
                                        <td><strong>{{ $order->code }}</strong></td>
                                        <td>{{ $order->supplier->name }}</td>
                                        <td>{!! $order->status_badge !!}</td>
                                        <td>${{ number_format($order->total, 2) }}</td>
                                        <td>{{ $order->date_issued?->format('d/m/Y') ?? '-' }}</td>
                                        <td>{{ $order->delivery_date?->format('d/m/Y') ?? '-' }}</td>
                                        <td>
                                            <div class="btn-group btn-group-sm" role="group">
                                                <a href="{{ route('admin.purchaseOrders.show', $order) }}" class="btn btn-default text-info" title="Ver">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="{{ route('admin.purchaseOrders.pdf', $order) }}" class="btn btn-default text-secondary" title="PDF" target="_blank">
                                                    <i class="fas fa-file-pdf"></i>
                                                </a>
                                                @if($order->isEditable())
                                                    @can('ordenes_compra_editar')
                                                        <a href="{{ route('admin.purchaseOrders.edit', $order) }}" class="btn btn-default text-primary" title="Editar">
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
            $('#ordersTable').DataTable({
                "responsive": true,
                "paging": true,
                "lengthChange": true,
                "searching": true,
                "ordering": true,
                "info": true,
                "autoWidth": false,
                "order": [[ 4, "desc" ]],
                "language": {
                    "emptyTable": "No hay ordenes de compra registradas",
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
