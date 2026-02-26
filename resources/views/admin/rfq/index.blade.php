@extends('adminlte::page')

@section('title', 'Compras | Solicitudes de Cotización')

@section('plugins.Datatables', true)
@section('plugins.Responsive', true)

@section('content_header')
    <div class="d-flex justify-content-between">
        <h1><i class="fas fa-file-invoice"></i> Solicitudes de Cotización (RFQ)</h1>
        @can('rfq_crear')
            <a href="{{ route('admin.rfq.create') }}" class="btn btn-primary">
                <i class="fas fa-plus-circle"></i> Nueva RFQ
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
                        <table id="rfqTable" class="table table-striped table-bordered display nowrap" style="width:100%">
                            <thead>
                                <tr>
                                    <th style="width: 15%">Código</th>
                                    <th style="width: 30%">Título</th>
                                    <th style="width: 15%">Estado</th>
                                    <th style="width: 15%">Fecha Límite</th>
                                    <th style="width: 10%">Items</th>
                                    <th style="width: 15%">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($rfqs as $rfq)
                                    <tr>
                                        <td><strong>{{ $rfq->code }}</strong></td>
                                        <td>{{ $rfq->title }}</td>
                                        <td>{!! $rfq->status_badge !!}</td>
                                        <td>{{ $rfq->date_required?->format('d/m/Y') ?? '-' }}</td>
                                        <td><span class="badge badge-info">{{ $rfq->items->count() }}</span></td>
                                        <td>
                                            <div class="btn-group btn-group-sm" role="group">
                                                <a href="{{ route('admin.rfq.show', $rfq) }}" class="btn btn-default text-info" title="Ver">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="{{ route('admin.rfq.pdf', $rfq) }}" class="btn btn-default text-secondary" title="PDF" target="_blank">
                                                    <i class="fas fa-file-pdf"></i>
                                                </a>
                                                @if($rfq->isEditable())
                                                    @can('rfq_editar')
                                                        <a href="{{ route('admin.rfq.edit', $rfq) }}" class="btn btn-default text-primary" title="Editar">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                    @endcan
                                                    @can('rfq_eliminar')
                                                        <form action="{{ route('admin.rfq.destroy', $rfq) }}" method="POST" style="display:inline-block;">
                                                            @csrf @method('DELETE')
                                                            <button type="submit" class="btn btn-default text-danger" title="Eliminar" onclick="return confirm('¿Eliminar esta RFQ?')">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        </form>
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
            $('#rfqTable').DataTable({
                "responsive": true,
                "paging": true,
                "lengthChange": true,
                "searching": true,
                "ordering": true,
                "info": true,
                "autoWidth": false,
                "order": [[ 0, "desc" ]],
                "language": {
                    "decimal": "",
                    "emptyTable": "No hay RFQ registradas",
                    "info": "Mostrando _START_ a _END_ de _TOTAL_ registros",
                    "infoEmpty": "Mostrando 0 a 0 de 0 registros",
                    "infoFiltered": "(Filtrado de _MAX_ total registros)",
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
                    { "orderable": false, "targets": [5] }
                ]
            });
        });
    </script>
@endsection
