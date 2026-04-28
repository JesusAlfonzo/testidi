@extends('adminlte::page')

@section('title', 'RFQ ' . $rfq->code)

@section('content_header')
    <div class="d-flex justify-content-between">
        <h1><i class="fas fa-file-invoice"></i> RFQ {{ $rfq->code }}</h1>
        <div>
            <a href="{{ route('admin.rfq.pdf', $rfq) }}" class="btn btn-secondary" target="_blank">
                <i class="fas fa-file-pdf"></i> Ver PDF
            </a>
            @if($rfq->isEditable())
                @can('rfq_editar')
                    <a href="{{ route('admin.rfq.edit', $rfq) }}" class="btn btn-primary">
                        <i class="fas fa-edit"></i> Editar
                    </a>
                @endcan
            @endif
        </div>
    </div>
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            @include('admin.partials.session-messages')

            <div class="card card-outline card-info">
                <div class="card-header">
                    <h3 class="card-title">Información General</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-sm">
                                <tr>
                                    <th width="40%">Código:</th>
                                    <td><strong>{{ $rfq->code }}</strong></td>
                                </tr>
                                <tr>
                                    <th>Título:</th>
                                    <td>{{ $rfq->title }}</td>
                                </tr>
                                <tr>
                                    <th>Estado:</th>
                                    <td>{!! $rfq->status_badge !!}</td>
                                </tr>
                                <tr>
                                    <th>Creado por:</th>
                                    <td>{{ $rfq->creator->name ?? 'N/A' }}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-sm">
                                <tr>
                                    <th width="40%">Fecha Límite Respuesta:</th>
                                    <td>{{ $rfq->date_required?->format('d/m/Y') ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>Fecha Límite Entrega:</th>
                                    <td>{{ $rfq->delivery_deadline?->format('d/m/Y') ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>Creado:</th>
                                    <td>{{ $rfq->created_at->format('d/m/Y H:i') }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    @if($rfq->description)
                        <div class="mt-3">
                            <strong>Descripción:</strong>
                            <p class="text-muted">{{ $rfq->description }}</p>
                        </div>
                    @endif

                    @if($rfq->notes)
                        <div class="mt-2">
                            <strong>Notas Internas:</strong>
                            <p class="text-muted">{{ $rfq->notes }}</p>
                        </div>
                    @endif
                </div>
            </div>

            <div class="card card-outline card-success mt-3">
                <div class="card-header">
                    <h3 class="card-title">Productos Solicitados ({{ $rfq->items->count() }})</h3>
                </div>
                <div class="card-body p-0">
                    <table class="table table-striped table-bordered m-0">
                        <thead class="thead-light">
                            <tr>
                                <th>#</th>
                                <th>Código</th>
                                <th>Producto</th>
                                <th>Categoría</th>
                                <th>Cantidad</th>
                                <th>Notas</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($rfq->items as $index => $item)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $item->product->code ?? 'N/A' }}</td>
                                    <td><strong>{{ $item->product->name }}</strong></td>
                                    <td>{{ $item->product->category->name ?? 'N/A' }}</td>
                                    <td>
                                        <span class="badge badge-primary">{{ $item->quantity }}</span>
                                        {{ $item->product->unit->abbreviation ?? 'und' }}
                                    </td>
                                    <td>{{ $item->notes ?? '-' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            @can('rfq_enviar')
                <div class="card card-outline card-warning mt-3">
                    <div class="card-header">
                        <h3 class="card-title">Acciones de Estado</h3>
                    </div>
                    <div class="card-body">
                        @if($rfq->status === 'draft')
                            <span class="text-muted mr-3">Marcar como enviada cuando haya compartido el PDF con proveedores.</span>
                            <button type="button" class="btn btn-success" onclick="confirmAction({
                                title: 'Enviar RFQ',
                                message: '¿Está seguro de marcar esta Solicitud de Cotización como ENVIADA?',
                                alert: 'Una vez enviada, ya no podrá editarla.',
                                confirmBtnClass: 'btn-success',
                                onConfirm: function() {
                                    var form = document.createElement('form');
                                    form.method = 'POST';
                                    form.action = '{{ route('admin.rfq.mark-sent', $rfq) }}';
                                    var csrfToken = document.querySelector('meta[name=&quot;csrf-token&quot;]').getAttribute('content');
                                    form.innerHTML = '<input type=&quot;hidden&quot; name=&quot;_token&quot; value=&quot;' + csrfToken + '&quot;>';
                                    document.body.appendChild(form);
                                    form.submit();
                                }
                            })">
                                <i class="fas fa-paper-plane"></i> Marcar Enviada
                            </button>
                        @elseif($rfq->status === 'sent')
                            <button type="button" class="btn btn-success" onclick="confirmAction({
                                title: 'Cerrar RFQ',
                                message: '¿Está seguro de CERRAR esta Solicitud de Cotización?',
                                alert: 'Esto finalizará el proceso de cotización.',
                                confirmBtnClass: 'btn-success',
                                onConfirm: function() {
                                    var form = document.createElement('form');
                                    form.method = 'POST';
                                    form.action = '{{ route('admin.rfq.mark-closed', $rfq) }}';
                                    var csrfToken = document.querySelector('meta[name=&quot;csrf-token&quot;]').getAttribute('content');
                                    form.innerHTML = '<input type=&quot;hidden&quot; name=&quot;_token&quot; value=&quot;' + csrfToken + '&quot;>';
                                    document.body.appendChild(form);
                                    form.submit();
                                }
                            })">
                                <i class="fas fa-check"></i> Cerrar RFQ
                            </button>
                        @endif

                        @if(in_array($rfq->status, ['draft', 'sent']))
                            <button type="button" class="btn btn-danger" onclick="confirmAction({
                                title: 'Cancelar RFQ',
                                message: '¿Está seguro de CANCELAR esta Solicitud de Cotización?',
                                alert: 'Esta acción no se puede deshacer.',
                                confirmBtnClass: 'btn-danger',
                                onConfirm: function() {
                                    var form = document.createElement('form');
                                    form.method = 'POST';
                                    form.action = '{{ route('admin.rfq.cancel', $rfq) }}';
                                    var csrfToken = document.querySelector('meta[name=&quot;csrf-token&quot;]').getAttribute('content');
                                    form.innerHTML = '<input type=&quot;hidden&quot; name=&quot;_token&quot; value=&quot;' + csrfToken + '&quot;>';
                                    document.body.appendChild(form);
                                    form.submit();
                                }
                            })">
                                <i class="fas fa-times"></i> Cancelar
                            </button>
                        @endif
                    </div>
                </div>
            @endcan
        </div>
    </div>

    <div class="row mt-3">
        <div class="col-12">
            <a href="{{ route('admin.rfq.index') }}" class="btn btn-default">
                <i class="fas fa-arrow-left"></i> Volver al listado
            </a>
        </div>
    </div>

    @include('admin.partials.delete-confirm')
    @include('admin.partials.confirm-action')
@stop
