@extends('adminlte::page')

@section('title', 'Orden de Compra ' . $purchaseOrder->code)

@section('content_header')
    <div class="d-flex justify-content-between">
        <h1><i class="fas fa-shopping-cart"></i> Orden de Compra {{ $purchaseOrder->code }}</h1>
        <div>
            <a href="{{ route('admin.purchaseOrders.pdf', $purchaseOrder) }}" class="btn btn-secondary" target="_blank">
                <i class="fas fa-file-pdf"></i> PDF
            </a>
            @if($purchaseOrder->isEditable())
                @can('ordenes_compra_editar')
                    <a href="{{ route('admin.purchaseOrders.edit', $purchaseOrder) }}" class="btn btn-primary">
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
                                    <td><strong>{{ $purchaseOrder->code }}</strong></td>
                                </tr>
                                <tr>
                                    <th>Estado:</th>
                                    <td>{!! $purchaseOrder->status_badge !!}</td>
                                </tr>
                                <tr>
                                    <th>Cotización Origen:</th>
                                    <td>
                                        @if($purchaseOrder->quote)
                                            <a href="{{ route('admin.quotations.show', $purchaseOrder->quote) }}">{{ $purchaseOrder->quote->code }}</a>
                                        @else
                                            Sin cotización
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>Creado por:</th>
                                    <td>{{ $purchaseOrder->creator->name ?? 'N/A' }}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-sm">
                                <tr>
                                    <th width="40%">Fecha Emisión:</th>
                                    <td>{{ $purchaseOrder->date_issued->format('d/m/Y') }}</td>
                                </tr>
                                <tr>
                                    <th>Fecha Entrega:</th>
                                    <td>{{ $purchaseOrder->delivery_date?->format('d/m/Y') ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>Dirección Entrega:</th>
                                    <td>{{ $purchaseOrder->delivery_address ?? '-' }}</td>
                                </tr>
                                @if($purchaseOrder->approved_by)
                                    <tr>
                                        <th>Aprobada por:</th>
                                        <td>{{ $purchaseOrder->approver->name }} el {{ $purchaseOrder->approved_at->format('d/m/Y H:i') }}</td>
                                    </tr>
                                @endif
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card card-outline card-warning mt-3">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-building"></i> Proveedor</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Nombre:</strong> {{ $purchaseOrder->supplier->name }}</p>
                            <p><strong>RIF:</strong> {{ $purchaseOrder->supplier->tax_id ?? '-' }}</p>
                            <p><strong>Contacto:</strong> {{ $purchaseOrder->supplier->contact_person ?? '-' }}</p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Email:</strong> {{ $purchaseOrder->supplier->email ?? '-' }}</p>
                            <p><strong>Teléfono:</strong> {{ $purchaseOrder->supplier->phone ?? '-' }}</p>
                            <p><strong>Dirección:</strong> {{ $purchaseOrder->supplier->address ?? '-' }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card card-outline card-success mt-3">
                <div class="card-header">
                    <h3 class="card-title">Items de la Orden ({{ $purchaseOrder->items->count() }})</h3>
                </div>
                <div class="card-body p-0">
                    <table class="table table-striped table-bordered m-0">
                        <thead class="thead-light">
                            <tr>
                                <th>#</th>
                                <th>Producto</th>
                                <th>Cantidad</th>
                                <th>Recibido</th>
                                <th>Costo Unit.</th>
                                @if($purchaseOrder->is_foreign_currency || $purchaseOrder->currency === 'Bs')
                                <th>Equiv. Bs</th>
                                @endif
                                <th>Total</th>
                                @if($purchaseOrder->status === 'issued')
                                    <th>Acción</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($purchaseOrder->items as $index => $item)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>
                                        <strong>{{ $item->product_name }}</strong>
                                        @if($item->product_code)
                                            <br><small class="text-muted">{{ $item->product_code }}</small>
                                        @endif
                                    </td>
                                    <td>{{ $item->quantity }}</td>
                                    <td>
                                        @if($item->isFullyReceived())
                                            <span class="badge badge-success">{{ $item->quantity_received }}</span>
                                        @else
                                            <span class="badge badge-warning">{{ $item->quantity_received }} / {{ $item->quantity }}</span>
                                        @endif
                                    </td>
                                    <td>{{ $purchaseOrder->currency_symbol }}{{ number_format($item->unit_cost, 2) }}</td>
                                    @if($purchaseOrder->is_foreign_currency || $purchaseOrder->currency === 'Bs')
                                    <td>Bs {{ number_format($item->equivalent_bs, 2) }}</td>
                                    @endif
                                    <td><strong>{{ $purchaseOrder->currency_symbol }}{{ number_format($item->total_cost, 2) }}</strong></td>
                                    @if($purchaseOrder->status === 'issued')
                                        <td>
                                            @if(!$item->isFullyReceived())
                                                @can('entradas_crear')
                                                    <a href="{{ route('admin.stock-in.create', ['order' => $purchaseOrder->id, 'item' => $item->id]) }}" class="btn btn-sm btn-success">
                                                        <i class="fas fa-box"></i> Recibir
                                                    </a>
                                                @endcan
                                            @else
                                                <span class="badge badge-success"><i class="fas fa-check"></i></span>
                                            @endif
                                        </td>
                                    @endif
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            @if($purchaseOrder->currency === 'Bs')
                            <tr>
                                <th colspan="5" class="text-right">Subtotal Bs (sin IVA):</th>
                                <th>Bs {{ number_format($purchaseOrder->subtotal, 2) }}</th>
                            </tr>
                            <tr>
                                <th colspan="5" class="text-right">IVA 16% Bs:</th>
                                <th class="text-danger">Bs {{ number_format($purchaseOrder->tax_amount_bs, 2) }}</th>
                            </tr>
                            <tr>
                                <th colspan="5" class="text-right">TOTAL Bs (con IVA):</th>
                                <th class="text-primary h5">Bs {{ number_format($purchaseOrder->total_bs, 2) }}</th>
                            </tr>
                            @else
                            <tr>
                                <th colspan="{{ ($purchaseOrder->is_foreign_currency || $purchaseOrder->currency === 'Bs') ? 6 : 5 }}" class="text-right">Subtotal ({{ $purchaseOrder->currency }}):</th>
                                <th>{{ $purchaseOrder->currency_symbol }}{{ number_format($purchaseOrder->subtotal, 2) }}</th>
                            </tr>
                            @if($purchaseOrder->is_foreign_currency)
                            <tr>
                                <th colspan="6" class="text-right">Subtotal Bs (sin IVA):</th>
                                <th>Bs {{ number_format($purchaseOrder->subtotal_bs, 2) }}</th>
                            </tr>
                            <tr>
                                <th colspan="6" class="text-right">IVA 16% Bs:</th>
                                <th class="text-danger">Bs {{ number_format($purchaseOrder->tax_amount_bs, 2) }}</th>
                            </tr>
                            <tr>
                                <th colspan="6" class="text-right">TOTAL Bs (con IVA):</th>
                                <th class="text-primary h5">Bs {{ number_format($purchaseOrder->total_bs, 2) }}</th>
                            </tr>
                            @endif
                            <tr>
                                <th colspan="{{ ($purchaseOrder->is_foreign_currency) ? 6 : 5 }}" class="text-right">Total ({{ $purchaseOrder->currency }}):</th>
                                <th class="text-primary h5">{{ $purchaseOrder->currency_symbol }}{{ number_format($purchaseOrder->total, 2) }}</th>
                            </tr>
                            @endif
                        </tfoot>
                    </table>
                </div>
            </div>

            @if($purchaseOrder->terms)
                <div class="card card-outline card-secondary mt-3">
                    <div class="card-header">
                        <h3 class="card-title">Términos y Condiciones</h3>
                    </div>
                    <div class="card-body">
                        {{ $purchaseOrder->terms }}
                    </div>
                </div>
            @endif

            <div class="card card-outline card-primary mt-3">
                <div class="card-header">
                    <h3 class="card-title">Acciones</h3>
                </div>
                <div class="card-body">
                    @if($purchaseOrder->status === 'draft')
                        @can('ordenes_compra_aprobar')
                            <button type="button" class="btn btn-success btn-lg" onclick="confirmAction({
                                title: 'Emitir Orden de Compra',
                                message: '¿Está seguro de EMITIR esta orden de compra?',
                                alert: 'Una vez emitida, se considerará válida y se vinculará con el proveedor.',
                                confirmBtnClass: 'btn-success',
                                onConfirm: function() {
                                    var form = document.createElement('form');
                                    form.method = 'POST';
                                    form.action = '{{ route('admin.purchaseOrders.issue', $purchaseOrder) }}';
                                    var csrfToken = document.querySelector('meta[name=&quot;csrf-token&quot;]').getAttribute('content');
                                    form.innerHTML = '<input type=&quot;hidden&quot; name=&quot;_token&quot; value=&quot;' + csrfToken + '&quot;>';
                                    document.body.appendChild(form);
                                    form.submit();
                                }
                            })">
                                <i class="fas fa-paper-plane"></i> Emitir Orden
                            </button>
                        @endcan
                        @can('ordenes_compra_eliminar')
                            <button type="button" class="btn btn-danger" onclick="confirmDelete('{{ route('admin.purchaseOrders.destroy', $purchaseOrder) }}', 'Orden de Compra {{ $purchaseOrder->code }}')">
                                <i class="fas fa-trash"></i> Eliminar
                            </button>
                        @endcan
                    @elseif($purchaseOrder->status === 'issued')
                        @can('entradas_crear')
                            <a href="{{ route('admin.stock-in.create', ['order' => $purchaseOrder->id]) }}" class="btn btn-success">
                                <i class="fas fa-truck-loading"></i> Generar Entrada de Stock
                            </a>
                        @endcan

                        @if($purchaseOrder->isFullyReceived())
                            @can('ordenes_compra_aprobar')
                                <button type="button" class="btn btn-success btn-lg" onclick="confirmAction({
                                    title: 'Completar Orden de Compra',
                                    message: '¿Está seguro de COMPLETAR esta orden de compra?',
                                    alert: 'Toda la mercancía ha sido recibida.',
                                    confirmBtnClass: 'btn-success',
                                    onConfirm: function() {
                                        var form = document.createElement('form');
                                        form.method = 'POST';
                                        form.action = '{{ route('admin.purchaseOrders.complete', $purchaseOrder) }}';
                                        var csrfToken = document.querySelector('meta[name=&quot;csrf-token&quot;]').getAttribute('content');
                                        form.innerHTML = '<input type=&quot;hidden&quot; name=&quot;_token&quot; value=&quot;' + csrfToken + '&quot;>';
                                        document.body.appendChild(form);
                                        form.submit();
                                    }
                                })">
                                    <i class="fas fa-check"></i> Completar Orden
                                </button>
                            @endcan
                        @else
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle"></i> Pendiente de recibir mercancía. Registre las entradas de stock para completar.
                            </div>
                        @endif
                        @can('ordenes_compra_anular')
                            <button type="button" class="btn btn-danger" onclick="confirmAction({
                                title: 'Cancelar Orden de Compra',
                                message: '¿Está seguro de CANCELAR esta orden de compra?',
                                alert: 'Esta acción no se puede deshacer.',
                                confirmBtnClass: 'btn-danger',
                                onConfirm: function() {
                                    var form = document.createElement('form');
                                    form.method = 'POST';
                                    form.action = '{{ route('admin.purchaseOrders.cancel', $purchaseOrder) }}';
                                    var csrfToken = document.querySelector('meta[name=&quot;csrf-token&quot;]').getAttribute('content');
                                    form.innerHTML = '<input type=&quot;hidden&quot; name=&quot;_token&quot; value=&quot;' + csrfToken + '&quot;>';
                                    document.body.appendChild(form);
                                    form.submit();
                                }
                            })">
                                <i class="fas fa-times"></i> Cancelar
                            </button>
                        @endcan
                    @elseif($purchaseOrder->status === 'completed')
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle"></i> Esta orden está completada. Toda la mercancía fue recibida.
                        </div>
                    @elseif($purchaseOrder->status === 'cancelled')
                        <div class="alert alert-danger">
                            <i class="fas fa-times-circle"></i> Esta orden fue cancelada.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-3">
        <div class="col-12">
            <a href="{{ route('admin.purchaseOrders.index') }}" class="btn btn-default">
                <i class="fas fa-arrow-left"></i> Volver al listado
            </a>
        </div>
    </div>

    @include('admin.partials.delete-confirm')
    @include('admin.partials.confirm-action')
@stop
