@extends('adminlte::page')

@section('title', 'Orden de Compra ' . $purchaseOrder->code)

@section('plugins.Datatables', true)

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="m-0 text-dark">
                <i class="fas fa-shopping-cart"></i> Orden de Compra <strong>{{ $purchaseOrder->code }}</strong>
                {!! $purchaseOrder->status_badge !!}
            </h1>
        </div>
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

            {{-- Información de la Orden --}}
            <div class="card" style="border-left: 4px solid #6c757d;">
                <div class="card-header" style="background: linear-gradient(135deg, #6c757d 0%, #8a939d 100%);">
                    <h3 class="card-title text-white">
                        <i class="fas fa-info-circle"></i> Información de la Orden
                    </h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-12 col-md-6">
                            <table class="table table-sm table-borderless mb-0">
                                <tr>
                                    <th width="40%"><i class="fas fa-hashtag text-secondary"></i> Código:</th>
                                    <td><strong>{{ $purchaseOrder->code }}</strong></td>
                                </tr>
                                <tr>
                                    <th><i class="fas fa-check-circle text-secondary"></i> Estado:</th>
                                    <td>{!! $purchaseOrder->status_badge !!}</td>
                                </tr>
                                @if($purchaseOrder->rfq)
                                <tr>
                                    <th><i class="fas fa-file-invoice text-secondary"></i> RFQ Origen:</th>
                                    <td>
                                        <a href="{{ route('admin.rfq.show', $purchaseOrder->rfq) }}" class="font-weight-bold">
                                            <i class="fas fa-external-link-alt"></i> {{ $purchaseOrder->rfq->code }}
                                        </a>
                                    </td>
                                </tr>
                                @endif
                                <tr>
                                    <th><i class="fas fa-user text-secondary"></i> Creado por:</th>
                                    <td>{{ $purchaseOrder->creator->name ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th><i class="fas fa-percent text-secondary"></i> IVA:</th>
                                    <td>
                                        @if($purchaseOrder->iva_exempt)
                                            <span class="badge badge-info">Exento de IVA</span>
                                        @else
                                            <span class="badge badge-secondary">16% aplicable</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th><i class="fas fa-coins text-secondary"></i> Moneda:</th>
                                    <td>
                                        {{ $purchaseOrder->currency }}
                                        @if($purchaseOrder->is_foreign_currency)
                                            <small class="text-muted">(Tasa: {{ number_format($purchaseOrder->exchange_rate, 4) }})</small>
                                        @endif
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-12 col-md-6">
                            <table class="table table-sm table-borderless mb-0">
                                <tr>
                                    <th width="40%"><i class="fas fa-calendar-alt text-secondary"></i> Fecha Emisión:</th>
                                    <td>{{ $purchaseOrder->date_issued->format('d/m/Y') }}</td>
                                </tr>
                                <tr>
                                    <th><i class="fas fa-calendar-check text-secondary"></i> Fecha Entrega:</th>
                                    <td>{{ $purchaseOrder->delivery_date?->format('d/m/Y') ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th><i class="fas fa-map-marker-alt text-secondary"></i> Dirección Entrega:</th>
                                    <td>{{ $purchaseOrder->delivery_address ?? '-' }}</td>
                                </tr>
                                @if($purchaseOrder->approved_by)
                                <tr>
                                    <th><i class="fas fa-user-check text-secondary"></i> Aprobada por:</th>
                                    <td>{{ $purchaseOrder->approver->name }} el {{ $purchaseOrder->approved_at->format('d/m/Y H:i') }}</td>
                                </tr>
                                @endif
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Proveedor --}}
            <div class="card mt-3" style="border-left: 4px solid #f59e0b;">
                <div class="card-header" style="background: linear-gradient(135deg, #f59e0b 0%, #fbbf24 100%);">
                    <h3 class="card-title text-dark">
                        <i class="fas fa-building"></i> Proveedor
                    </h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-12 col-md-6">
                            <table class="table table-sm table-borderless mb-0">
                                <tr>
                                    <th width="35%"><i class="fas fa-building text-warning"></i> Nombre:</th>
                                    <td>
                                        <a href="{{ route('admin.suppliers.show', $purchaseOrder->supplier) }}">
                                            <strong>{{ $purchaseOrder->supplier->name }}</strong>
                                        </a>
                                    </td>
                                </tr>
                                <tr>
                                    <th><i class="fas fa-id-card text-warning"></i> RIF:</th>
                                    <td>{{ $purchaseOrder->supplier->tax_id ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th><i class="fas fa-user-tie text-warning"></i> Contacto:</th>
                                    <td>{{ $purchaseOrder->supplier->contact_person ?? '-' }}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-12 col-md-6">
                            <table class="table table-sm table-borderless mb-0">
                                <tr>
                                    <th width="35%"><i class="fas fa-envelope text-warning"></i> Email:</th>
                                    <td>{{ $purchaseOrder->supplier->email ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th><i class="fas fa-phone text-warning"></i> Teléfono:</th>
                                    <td>{{ $purchaseOrder->supplier->phone ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th><i class="fas fa-map-pin text-warning"></i> Dirección:</th>
                                    <td>{{ $purchaseOrder->supplier->address ?? '-' }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Barra de acciones rápidas --}}
            <div class="row mt-3 mb-3">
                <div class="col-12 d-flex justify-content-between align-items-center">
                    <a href="{{ route('admin.purchaseOrders.index') }}" class="btn btn-default">
                        <i class="fas fa-arrow-left"></i> Volver al listado
                    </a>
                    @if($purchaseOrder->status === 'issued')
                        @can('entradas_crear')
                            <a href="{{ route('admin.stock-in.create', ['order' => $purchaseOrder->id]) }}" class="btn btn-success mr-2">
                                <i class="fas fa-truck-loading"></i> Entrada Completa
                            </a>
                            <button type="button" class="btn btn-info" id="btnReceiveSelected" onclick="receiveSelected()" disabled>
                                <i class="fas fa-check-square"></i> Recibir Seleccionados
                            </button>
                        @endcan
                    @endif
                </div>
            </div>

            {{-- Items de la Orden --}}
            <div class="card" style="border-left: 4px solid #10b981;">
                <div class="card-header" style="background: linear-gradient(135deg, #10b981 0%, #34d399 100%);">
                    <h3 class="card-title text-white">
                        <i class="fas fa-boxes"></i> Items de la Orden ({{ $purchaseOrder->items->count() }})
                    </h3>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table id="itemsTable" class="table table-striped table-bordered mb-0">
                            <thead class="thead-light">
                                <tr>
                                    <th style="width: 30px;">#</th>
                                    @if($purchaseOrder->status === 'issued')
                                    <th style="width: 30px;">
                                        <input type="checkbox" id="selectAll" onchange="toggleSelectAll(this)" title="Seleccionar todos">
                                    </th>
                                    @endif
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
                                    @php $isFullyReceived = $item->isFullyReceived(); @endphp
                                    <tr class="{{ $isFullyReceived ? 'table-success' : '' }}">
                                        <td>{{ $index + 1 }}</td>
                                        @if($purchaseOrder->status === 'issued')
                                        <td class="text-center">
                                            @if(!$isFullyReceived)
                                                <input type="checkbox" class="item-checkbox" value="{{ $item->id }}" onchange="updateReceiveButton()">
                                            @endif
                                        </td>
                                        @endif
                                        <td>
                                            @if($item->product)
                                                <a href="{{ route('admin.products.show', $item->product) }}">
                                                    <strong>{{ $item->product_name }}</strong>
                                                </a>
                                            @else
                                                <strong>{{ $item->product_name }}</strong>
                                            @endif
                                            @if($item->product_code)
                                                <br><small class="text-muted">{{ $item->product_code }}</small>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge badge-primary">{{ $item->quantity }}</span>
                                        </td>
                                        <td>
                                            @if($isFullyReceived)
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
                                            <td class="text-center">
                                                @if(!$isFullyReceived)
                                                    @can('entradas_crear')
                                                        <a href="{{ route('admin.stock-in.create', ['order' => $purchaseOrder->id, 'item' => $item->id]) }}" class="btn btn-sm btn-success" title="Recibir solo este producto">
                                                            <i class="fas fa-box"></i>
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
                        </table>
                    </div>
                </div>

                {{-- Totals Box - fuera del DataTable --}}
                <div class="card-footer bg-white">
                    <div class="row justify-content-end">
                        <div class="col-12 col-md-5">
                            <table class="table table-sm table-borderless mb-0">
                                @if($purchaseOrder->currency === 'Bs')
                                <tr>
                                    <th class="text-right">Subtotal Bs (sin IVA):</th>
                                    <th class="text-right" style="width: 140px;">Bs {{ number_format($purchaseOrder->subtotal, 2) }}</th>
                                </tr>
                                @if(!$purchaseOrder->iva_exempt)
                                <tr>
                                    <th class="text-right">IVA 16% Bs:</th>
                                    <th class="text-right text-danger">Bs {{ number_format($purchaseOrder->tax_amount_bs, 2) }}</th>
                                </tr>
                                <tr class="bg-info rounded">
                                    <th class="text-right text-white">TOTAL Bs (con IVA):</th>
                                    <th class="text-right text-white h5">Bs {{ number_format($purchaseOrder->total_bs, 2) }}</th>
                                </tr>
                                @else
                                <tr>
                                    <th class="text-right">IVA:</th>
                                    <th class="text-right"><span class="badge badge-info">Exento</span></th>
                                </tr>
                                <tr class="bg-info rounded">
                                    <th class="text-right text-white">TOTAL Bs:</th>
                                    <th class="text-right text-white h5">Bs {{ number_format($purchaseOrder->subtotal, 2) }}</th>
                                </tr>
                                @endif
                                @else
                                <tr>
                                    <th class="text-right">Subtotal ({{ $purchaseOrder->currency }}):</th>
                                    <th class="text-right" style="width: 140px;">{{ $purchaseOrder->currency_symbol }}{{ number_format($purchaseOrder->subtotal, 2) }}</th>
                                </tr>
                                @if($purchaseOrder->is_foreign_currency)
                                <tr>
                                    <th class="text-right">Subtotal Bs (sin IVA):</th>
                                    <th class="text-right">Bs {{ number_format($purchaseOrder->subtotal_bs, 2) }}</th>
                                </tr>
                                @if(!$purchaseOrder->iva_exempt)
                                <tr>
                                    <th class="text-right">IVA 16% Bs:</th>
                                    <th class="text-right text-danger">Bs {{ number_format($purchaseOrder->tax_amount_bs, 2) }}</th>
                                </tr>
                                <tr class="bg-info rounded">
                                    <th class="text-right text-white">TOTAL Bs (con IVA):</th>
                                    <th class="text-right text-white h5">Bs {{ number_format($purchaseOrder->total_bs, 2) }}</th>
                                </tr>
                                @else
                                <tr>
                                    <th class="text-right">IVA:</th>
                                    <th class="text-right"><span class="badge badge-info">Exento</span></th>
                                </tr>
                                <tr class="bg-info rounded">
                                    <th class="text-right text-white">TOTAL Bs:</th>
                                    <th class="text-right text-white h5">Bs {{ number_format($purchaseOrder->subtotal_bs, 2) }}</th>
                                </tr>
                                @endif
                                @endif
                                <tr class="bg-success rounded">
                                    <th class="text-right text-white">Total ({{ $purchaseOrder->currency }}):</th>
                                    <th class="text-right text-white h5">{{ $purchaseOrder->currency_symbol }}{{ number_format($purchaseOrder->total, 2) }}</th>
                                </tr>
                                @endif
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Entradas de Stock asociadas --}}
            @if($purchaseOrder->stockIns->count() > 0)
            <div class="card mt-3" style="border-left: 4px solid #8b5cf6;">
                <div class="card-header" style="background: linear-gradient(135deg, #8b5cf6 0%, #a78bfa 100%);">
                    <h3 class="card-title text-white">
                        <i class="fas fa-truck-loading"></i> Entradas de Stock Asociadas
                    </h3>
                </div>
                <div class="card-body p-0">
                    <table class="table table-sm table-bordered mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th>#</th>
                                <th>Fecha</th>
                                <th>Tipo Doc.</th>
                                <th>Nro. Documento</th>
                                <th>Productos</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($purchaseOrder->stockIns as $entry)
                                <tr>
                                    <td>
                                        <a href="{{ route('admin.stock-in.show', $entry) }}">{{ $entry->id }}</a>
                                    </td>
                                    <td>{{ $entry->entry_date->format('d/m/Y') }}</td>
                                    <td>{{ $entry->document_type ?? '-' }}</td>
                                    <td>{{ $entry->document_number ?? '-' }}</td>
                                    <td>
                                        @foreach($entry->items as $ei)
                                            @if($ei->product)
                                                <a href="{{ route('admin.products.show', $ei->product) }}" class="badge badge-info mr-1">
                                                    {{ $ei->product->name }} x{{ $ei->quantity }}
                                                </a>
                                            @endif
                                        @endforeach
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endif

            {{-- Términos y Condiciones --}}
            @if($purchaseOrder->terms)
                <div class="card mt-3" style="border-left: 4px solid #9ca3af;">
                    <div class="card-header" style="background: linear-gradient(135deg, #9ca3af 0%, #d1d5db 100%);">
                        <h3 class="card-title text-white">
                            <i class="fas fa-sticky-note"></i> Términos y Condiciones
                        </h3>
                    </div>
                    <div class="card-body">
                        {{ $purchaseOrder->terms }}
                    </div>
                </div>
            @endif

            {{-- Acciones --}}
            <div class="card mt-3" style="border-left: 4px solid #3b82f6;">
                <div class="card-header" style="background: linear-gradient(135deg, #3b82f6 0%, #60a5fa 100%);">
                    <h3 class="card-title text-white">
                        <i class="fas fa-cogs"></i> Acciones
                    </h3>
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
                        <p class="text-muted mb-3">
                            <i class="fas fa-info-circle text-info"></i>
                            Pendiente de recibir mercancía. Use
                            <a href="{{ route('admin.stock-in.create', ['order' => $purchaseOrder->id]) }}">Generar Entrada de Stock</a>
                            desde la barra superior.
                        </p>
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
                        <div class="alert alert-success mb-0">
                            <i class="fas fa-check-circle"></i> Esta orden está completada. Toda la mercancía fue recibida.
                        </div>
                    @elseif($purchaseOrder->status === 'cancelled')
                        <div class="alert alert-danger mb-0">
                            <i class="fas fa-times-circle"></i> Esta orden fue cancelada.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @include('admin.partials.delete-confirm')
    @include('admin.partials.confirm-action')
@stop

@section('css')
<style>
    .dataTables_wrapper > .row:first-child {
        margin-bottom: 0.75rem;
    }
    .dataTables_wrapper > .row:last-child {
        margin-top: 0.75rem;
    }
</style>
@stop

@section('js')
<script>
    $(document).ready(function() {
        $('#itemsTable').DataTable({
            "pageLength": 10,
            "lengthMenu": [[10, 25, 50], [10, 25, 50]],
            "pagingType": "simple_numbers",
            "searching": false,
            "info": false,
            "order": [[0, 'asc']],
            "language": {
                "emptyTable": "No hay items en esta orden",
                "paginate": {
                    "next": "Siguiente",
                    "previous": "Anterior"
                }
            }
        });
    });

    function toggleSelectAll(source) {
        document.querySelectorAll('.item-checkbox').forEach(function(cb) {
            cb.checked = source.checked;
        });
        updateReceiveButton();
    }

    function updateReceiveButton() {
        var checked = document.querySelectorAll('.item-checkbox:checked');
        var btn = document.getElementById('btnReceiveSelected');
        if (checked.length > 0) {
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-check-square"></i> Recibir Seleccionados (' + checked.length + ')';
        } else {
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-check-square"></i> Recibir Seleccionados';
        }
    }

    function receiveSelected() {
        var checked = document.querySelectorAll('.item-checkbox:checked');
        if (checked.length === 0) return;

        var ids = [];
        checked.forEach(function(cb) {
            ids.push(cb.value);
        });

        var params = new URLSearchParams();
        params.set('order', '{{ $purchaseOrder->id }}');
        ids.forEach(function(id) {
            params.append('items[]', id);
        });

        window.location.href = '{{ route('admin.stock-in.create') }}?' + params.toString();
    }
</script>
@stop
