@extends('adminlte::page')

@section('title', 'Orden de Compra ' . $purchaseOrder->code)

@section('plugins.Datatables', true)

@section('css')
    <style>
        .invoice-card {
            border-radius: 12px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);
            border: none;
            background-color: #ffffff;
            margin-bottom: 2rem;
        }

        .invoice-header-bg {
            background-color: #f8fafc;
            border-bottom: 1px solid #e2e8f0;
            padding: 1.5rem 2rem;
            border-top-left-radius: 12px;
            border-top-right-radius: 12px;
        }

        .invoice-title {
            font-size: 1.5rem;
            font-weight: 800;
            color: #1e293b;
        }

        .invoice-subtitle {
            font-size: 0.85rem;
            color: #64748b;
        }

        .badge-premium {
            padding: 0.5em 0.85em;
            font-weight: 600;
            font-size: 0.75rem;
            border-radius: 6px;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
            display: inline-block;
        }

        .badge-secondary {
            background-color: #f3f4f6 !important;
            color: #4b5563 !important;
            border: 1px solid #e5e7eb !important;
        }

        .badge-info {
            background-color: #e0f2fe !important;
            color: #0369a1 !important;
            border: 1px solid #bae6fd !important;
        }

        .badge-warning {
            background-color: #ffedd5 !important;
            color: #ea580c !important;
            border: 1px solid #fed7aa !important;
        }

        .badge-success {
            background-color: #dcfce7 !important;
            color: #15803d !important;
            border: 1px solid #bbf7d0 !important;
        }

        .badge-danger-light {
            background-color: #fee2e2 !important;
            color: #b91c1c !important;
            border: 1px solid #fca5a5 !important;
        }

        .invoice-details-label {
            font-size: 0.75rem;
            text-transform: uppercase;
            font-weight: 700;
            color: #64748b;
            letter-spacing: 0.5px;
            margin-bottom: 0.25rem;
        }

        .invoice-details-value {
            font-size: 0.9rem;
            color: #0f172a;
            font-weight: 500;
        }

        .invoice-table th {
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-weight: 700;
            color: #475569;
            background-color: #f8fafc;
            border-bottom: 2px solid #e2e8f0;
            padding: 12px 16px;
        }

        .invoice-table td {
            padding: 14px 16px;
            vertical-align: middle !important;
            font-size: 0.875rem;
            border-bottom: 1px solid #f1f5f9;
        }

        .invoice-totals-table th {
            font-weight: 600;
            color: #475569;
            font-size: 0.85rem;
            padding: 6px 12px;
        }

        .invoice-totals-table td {
            font-size: 0.85rem;
            padding: 6px 12px;
            text-align: right;
        }

        .invoice-totals-grand {
            border-top: 1px solid #cbd5e1;
            font-size: 1.15rem !important;
            font-weight: 800 !important;
            color: #0f172a !important;
        }

        .sidebar-card {
            border-radius: 12px;
            border: 1px solid #e2e8f0;
            background-color: #ffffff;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
            margin-bottom: 1.5rem;
        }

        .sidebar-card-header {
            padding: 1rem 1.25rem;
            border-bottom: 1px solid #f1f5f9;
            background-color: #ffffff;
            border-top-left-radius: 12px;
            border-top-right-radius: 12px;
        }

        .sidebar-card-title {
            font-size: 0.85rem;
            font-weight: 700;
            text-transform: uppercase;
            color: #475569;
            letter-spacing: 0.5px;
        }

        .btn-premium {
            border-radius: 8px !important;
            font-weight: 700;
            padding: 0.6rem 1.25rem;
            box-shadow: 0 1px 2px rgba(0,0,0,0.05);
            transition: all 0.2s ease;
        }

        .btn-premium:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.08);
        }

        .timeline-compact {
            list-style: none;
            padding-left: 0;
            margin-bottom: 0;
        }

        .timeline-compact-item {
            position: relative;
            padding-left: 1.5rem;
            padding-bottom: 1rem;
        }

        .timeline-compact-item:last-child {
            padding-bottom: 0;
        }

        .timeline-compact-item::before {
            content: '';
            position: absolute;
            left: 4px;
            top: 6px;
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background-color: #8b5cf6;
        }

        .timeline-compact-item::after {
            content: '';
            position: absolute;
            left: 7px;
            top: 14px;
            bottom: 0;
            width: 2px;
            background-color: #e2e8f0;
        }

        .timeline-compact-item:last-child::after {
            display: none;
        }
    </style>
@stop

@section('content_header')
    <div class="d-flex justify-content-between align-items-center mb-2">
        <div>
            <h1 class="text-dark font-weight-bold" style="font-size: 1.75rem;">
                <i class="fas fa-file-invoice mr-2 text-primary"></i> Detalle de Orden de Compra
            </h1>
            <p class="text-muted mb-0">Visualice y gestione la información completa de esta adquisición.</p>
        </div>
        <div class="d-flex">
            <a href="{{ route('admin.purchaseOrders.pdf', $purchaseOrder) }}" class="btn btn-outline-secondary font-weight-bold mr-2 btn-premium" target="_blank">
                <i class="fas fa-file-pdf mr-1"></i> Descargar PDF
            </a>
            @if($purchaseOrder->isEditable())
                @can('ordenes_compra_editar')
                    <a href="{{ route('admin.purchaseOrders.edit', $purchaseOrder) }}" class="btn btn-primary font-weight-bold btn-premium">
                        <i class="fas fa-edit mr-1"></i> Editar Orden
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
        </div>
    </div>

    <div class="row">
        <!-- COLUMNA IZQUIERDA (70%) - Formato Factura Corporativa -->
        <div class="col-lg-8">
            <div class="card invoice-card">
                <!-- Encabezado de Factura -->
                <div class="invoice-header-bg d-flex justify-content-between align-items-start">
                    <div>
                        <div class="invoice-title">ORDEN DE COMPRA</div>
                        <div class="invoice-subtitle">{{ $purchaseOrder->code }}</div>
                    </div>
                    <div class="text-right">
                        <span class="badge-premium badge-{{ $purchaseOrder->status === 'draft' ? 'secondary' : ($purchaseOrder->status === 'completed' ? 'success' : ($purchaseOrder->status === 'cancelled' ? 'danger-light' : ($purchaseOrder->isPartiallyReceived() ? 'warning text-dark' : 'info'))) }}">
                            {{ $purchaseOrder->status === 'draft' ? 'Borrador' : ($purchaseOrder->status === 'completed' ? 'Cerrada / Recibida' : ($purchaseOrder->status === 'cancelled' ? 'Anulada' : ($purchaseOrder->isPartiallyReceived() ? 'Parcialmente Recibida' : 'Emitida'))) }}
                        </span>
                    </div>
                </div>

                <div class="card-body p-4">
                    <!-- Datos de Partes (Emisor / Proveedor) -->
                    <div class="row mb-4">
                        <div class="col-md-6 mb-3 mb-md-0">
                            <div class="invoice-details-label">De:</div>
                            <div class="font-weight-bold text-dark" style="font-size: 1rem;">Inmunología Asociación Civil</div>
                            <div class="text-muted text-xs">
                                Departamento de Compras y Suministros<br>
                                RIF: J-30710739-1<br>
                                Caracas, Venezuela
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="invoice-details-label">Proveedor:</div>
                            <div class="font-weight-bold text-dark" style="font-size: 1rem;">
                                <a href="{{ route('admin.suppliers.show', $purchaseOrder->supplier) }}" class="text-primary">
                                    {{ $purchaseOrder->supplier->name }}
                                </a>
                            </div>
                            <div class="text-muted text-xs">
                                RIF: {{ $purchaseOrder->supplier->tax_id ?? 'N/A' }}<br>
                                Email: {{ $purchaseOrder->supplier->email ?? 'N/A' }}<br>
                                Tlf: {{ $purchaseOrder->supplier->phone ?? 'N/A' }}<br>
                                Dir: {{ $purchaseOrder->supplier->address ?? 'N/A' }}
                            </div>
                        </div>
                    </div>

                    <!-- Datos de Despacho y Fechas -->
                    <div class="row border-top border-bottom py-3 mb-4 bg-light" style="border-radius: 8px;">
                        <div class="col-6 col-md-3">
                            <div class="invoice-details-label">Fecha Emisión</div>
                            <div class="invoice-details-value">{{ $purchaseOrder->date_issued->format('d/m/Y') }}</div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="invoice-details-label">Fecha Entrega</div>
                            <div class="invoice-details-value">{{ $purchaseOrder->delivery_date?->format('d/m/Y') ?? 'Por definir' }}</div>
                        </div>
                        <div class="col-12 col-md-6 mt-2 mt-md-0">
                            <div class="invoice-details-label">Dirección de Entrega</div>
                            <div class="invoice-details-value text-truncate" title="{{ $purchaseOrder->delivery_address ?? 'N/A' }}">
                                {{ $purchaseOrder->delivery_address ?? 'Retiro en almacén de origen' }}
                            </div>
                        </div>
                    </div>

                    <!-- Tabla de Items -->
                    <div class="invoice-details-label mb-2"><i class="fas fa-boxes text-primary mr-1"></i> Productos Solicitados</div>
                    <div class="table-responsive mb-4" style="max-height: 400px; overflow-y: auto; overflow-x: hidden;">
                        <table class="table invoice-table">
                            <thead class="position-sticky" style="top: 0; z-index: 10; background-color: #f8fafc;">
                                <tr>
                                    <th style="width: 5%">#</th>
                                    <th style="width: 35%">Producto / Kit</th>
                                    <th style="width: 10%" class="text-center">Cant.</th>
                                    <th style="width: 10%" class="text-center">Recibido</th>
                                    <th style="width: 10%" class="text-center">IVA</th>
                                    <th style="width: 15%" class="text-right">Costo Unit.</th>
                                    <th style="width: 15%" class="text-right">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($purchaseOrder->items as $index => $item)
                                    @php $isFully = $item->isFullyReceived(); @endphp
                                    <tr class="{{ $isFully ? 'table-success-light' : '' }}">
                                        <td>{{ $index + 1 }}</td>
                                        <td>
                                            @if($item->item_type === 'kit' && $item->kit)
                                                <a href="{{ route('admin.kits.show', $item->kit) }}" class="font-weight-bold text-dark">
                                                    {{ $item->product_name }}
                                                </a>
                                                <span class="badge badge-info ml-1" style="font-size: 0.65rem; padding: 2px 6px;">Kit</span>
                                            @elseif($item->product)
                                                <a href="{{ route('admin.products.show', $item->product) }}" class="font-weight-bold text-dark">
                                                    {{ $item->product_name }}
                                                </a>
                                            @else
                                                <strong class="text-dark">{{ $item->product_name }}</strong>
                                            @endif
                                            @if($item->product_code)
                                                <br><small class="text-muted text-xs">{{ $item->product_code }}</small>
                                            @endif
                                        </td>
                                        <td class="text-center font-weight-bold text-secondary">
                                            {{ $item->quantity_uom ?? $item->quantity }}
                                            <small class="text-muted">{{ $item->uom->abbreviation ?? ($item->product->unit->abbreviation ?? 'und') }}</small>
                                        </td>
                                        <td class="text-center">
                                            @if($isFully)
                                                <span class="badge badge-success" style="padding: 3px 8px;"><i class="fas fa-check mr-1"></i> {{ $item->quantity_received }}</span>
                                            @else
                                                <span class="badge badge-warning text-dark" style="padding: 3px 8px; font-weight: 600;">
                                                    {{ $item->quantity_received }} / {{ $item->quantity }}
                                                </span>
                                            @endif
                                            <small class="text-muted ml-1">{{ $item->product->unit->abbreviation ?? 'und' }}</small>
                                        </td>
                                        <td class="text-center">
                                            @if($item->is_exempt)
                                                <span class="badge badge-secondary" style="font-size: 0.75rem;">Exento</span>
                                            @else
                                                <small class="text-muted font-weight-bold">16%</small>
                                            @endif
                                        </td>
                                        <td class="text-right">
                                            {{ $purchaseOrder->currency_symbol }} {{ number_format($item->unit_cost_uom ?? $item->unit_cost, 2) }}
                                            @if($item->uom)
                                                <small class="text-muted">/ {{ $item->uom->abbreviation }}</small>
                                            @endif
                                        </td>
                                        <td class="text-right font-weight-bold text-dark">{{ $purchaseOrder->currency_symbol }} {{ number_format($item->total_cost, 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Desglose de Totales -->
                    <div class="row justify-content-end">
                        <div class="col-md-6 col-lg-5">
                            <table class="table table-borderless invoice-totals-table mb-0">
                                <tr>
                                    <th>Subtotal ({{ $purchaseOrder->currency }}):</th>
                                    <td>{{ $purchaseOrder->currency_symbol }} {{ number_format($purchaseOrder->subtotal, 2) }}</td>
                                </tr>
                                @if(!$purchaseOrder->iva_exempt)
                                    <tr>
                                        <th>IVA (16%):</th>
                                        <td class="text-danger">{{ $purchaseOrder->currency_symbol }} {{ number_format($purchaseOrder->tax_amount, 2) }}</td>
                                    </tr>
                                @else
                                    <tr>
                                        <th>IVA:</th>
                                        <td><span class="badge badge-info">Exento</span></td>
                                    </tr>
                                @endif
                                <tr class="invoice-totals-grand">
                                    <th>Total ({{ $purchaseOrder->currency }}):</th>
                                    <td class="text-primary font-weight-bold">{{ $purchaseOrder->currency_symbol }} {{ number_format($purchaseOrder->total, 2) }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Términos y Notas -->
            @if($purchaseOrder->terms || $purchaseOrder->notes)
                <div class="card invoice-card p-4">
                    <div class="row">
                        @if($purchaseOrder->terms)
                            <div class="col-md-6 mb-3 mb-md-0">
                                <div class="invoice-details-label"><i class="fas fa-sticky-note text-secondary mr-1"></i> Términos de Pago</div>
                                <div class="text-muted text-xs" style="white-space: pre-line;">{{ $purchaseOrder->terms }}</div>
                            </div>
                        @endif
                        @if($purchaseOrder->notes)
                            <div class="col-md-6">
                                <div class="invoice-details-label"><i class="fas fa-comment-alt text-secondary mr-1"></i> Comentarios Internos</div>
                                <div class="text-muted text-xs" style="white-space: pre-line;">{{ $purchaseOrder->notes }}</div>
                            </div>
                        @endif
                    </div>
                </div>
            @endif
        </div>

        <!-- COLUMNA DERECHA (30%) - Resumen de Estado, Acciones y Auditoría -->
        <div class="col-lg-4">
            <!-- Tarjeta de Acciones y Control -->
            <div class="card sidebar-card">
                <div class="sidebar-card-header">
                    <span class="sidebar-card-title"><i class="fas fa-cog mr-1"></i> Control de Operaciones</span>
                </div>
                <div class="card-body p-3">
                    @if($purchaseOrder->status === 'issued')
                        @can('entradas_crear')
                            <!-- Botón de Acción Operativa Destacado para Recepción -->
                            <a href="{{ route('admin.stock-in.create', ['order' => $purchaseOrder->id]) }}" class="btn btn-success btn-block btn-premium py-3 mb-3 shadow">
                                <i class="fas fa-truck-loading mr-2"></i> Iniciar Recepción de Mercancía
                            </a>
                        @endcan
                    @endif

                    @if($purchaseOrder->status === 'draft')
                        @can('ordenes_compra_aprobar')
                            <button type="button" class="btn btn-success btn-block btn-premium mb-2" onclick="confirmAction({
                                title: 'Emitir Orden de Compra',
                                message: '¿Está seguro de EMITIR esta orden de compra?',
                                alert: 'Una vez emitida, la orden es vinculante y se habilitará la recepción de productos.',
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
                                <i class="fas fa-paper-plane mr-1"></i> Emitir Orden de Compra
                            </button>
                        @endcan
                        @can('ordenes_compra_eliminar')
                            <button type="button" class="btn btn-outline-danger btn-block btn-premium mb-2" onclick="confirmDelete('{{ route('admin.purchaseOrders.destroy', $purchaseOrder) }}', 'Orden de Compra {{ $purchaseOrder->code }}')">
                                <i class="fas fa-trash mr-1"></i> Eliminar Orden
                            </button>
                        @endcan
                    @elseif($purchaseOrder->status === 'issued')
                        @can('ordenes_compra_anular')
                            <button type="button" class="btn btn-outline-danger btn-block btn-premium mb-2" onclick="confirmAction({
                                title: 'Cancelar Orden de Compra',
                                message: '¿Está seguro de CANCELAR esta orden de compra?',
                                alert: 'Esta acción anulará la orden y no se podrá recibir más mercancía.',
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
                                <i class="fas fa-times-circle mr-1"></i> Cancelar / Anular Orden
                            </button>
                        @endcan
                    @elseif($purchaseOrder->status === 'completed')
                        <div class="alert alert-success border-0 mb-0 shadow-sm" style="border-radius: 8px;">
                            <i class="fas fa-check-circle mr-1"></i> <strong>Orden Completada</strong><br>
                            Todos los ítems solicitados fueron recibidos en el inventario.
                        </div>
                    @elseif($purchaseOrder->status === 'cancelled')
                        <div class="alert alert-danger border-0 mb-0 shadow-sm" style="border-radius: 8px;">
                            <i class="fas fa-times-circle mr-1"></i> <strong>Orden Cancelada</strong><br>
                            Esta orden de compra fue cancelada administrativamente.
                        </div>
                    @endif
                </div>
            </div>

            <!-- Tarjeta de Metadatos / Auditoría -->
            <div class="card sidebar-card">
                <div class="sidebar-card-header">
                    <span class="sidebar-card-title"><i class="fas fa-info-circle mr-1"></i> Datos Financieros y Auditoría</span>
                </div>
                <div class="card-body p-3 text-xs">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Moneda global:</span>
                        <span class="font-weight-bold text-dark">{{ $purchaseOrder->currency }}</span>
                    </div>
                    @if($purchaseOrder->is_foreign_currency)
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Tasa de cambio:</span>
                            <span class="font-weight-bold text-dark">Bs {{ number_format($purchaseOrder->exchange_rate, 4) }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2 border-top pt-2">
                            <span class="text-muted font-weight-bold">Subtotal VES:</span>
                            <span class="font-weight-bold text-dark">Bs {{ number_format($purchaseOrder->subtotal_bs, 2) }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted font-weight-bold">IVA VES:</span>
                            <span class="font-weight-bold text-danger">Bs {{ number_format($purchaseOrder->tax_amount_bs, 2) }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2" style="font-size: 0.8rem;">
                            <span class="text-success font-weight-bold">Total VES:</span>
                            <span class="font-weight-bold text-success">Bs {{ number_format($purchaseOrder->total_bs, 2) }}</span>
                        </div>
                    @endif

                    <div class="border-top pt-2 mt-2">
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-muted">Creador por:</span>
                            <span class="text-dark">{{ $purchaseOrder->creator->name ?? 'Sistema' }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-muted">Fecha creación:</span>
                            <span class="text-dark">{{ $purchaseOrder->created_at->format('d/m/Y H:i') }}</span>
                        </div>
                        @if($purchaseOrder->approved_by)
                            <div class="d-flex justify-content-between mb-1 border-top pt-2 mt-1">
                                <span class="text-muted">Aprobada por:</span>
                                <span class="text-dark">{{ $purchaseOrder->approver->name }}</span>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span class="text-muted">Fecha aprobación:</span>
                                <span class="text-dark">{{ $purchaseOrder->approved_at->format('d/m/Y H:i') }}</span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Tarjeta de Entradas de Stock asociadas -->
            @if($purchaseOrder->stockIns->count() > 0)
                <div class="card sidebar-card">
                    <div class="sidebar-card-header">
                        <span class="sidebar-card-title"><i class="fas fa-truck-loading mr-1"></i> Recepciones de Inventario</span>
                    </div>
                    <div class="card-body p-3">
                        <ul class="timeline-compact">
                            @foreach($purchaseOrder->stockIns as $entry)
                                <li class="timeline-compact-item">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <a href="{{ route('admin.stock-in.show', $entry) }}" class="font-weight-bold text-primary text-xs">
                                            Entrada #{{ $entry->id }}
                                        </a>
                                        <span class="text-xxs text-muted">{{ $entry->entry_date->format('d/m/Y') }}</span>
                                    </div>
                                    <div class="text-xxs text-secondary mt-1">
                                        Doc: {{ $entry->document_type ?? 'Ajuste' }} #{{ $entry->document_number ?? '-' }}
                                    </div>
                                    <div class="mt-1">
                                        @foreach($entry->items as $ei)
                                            @if($ei->product)
                                                <span class="badge badge-secondary py-0 px-1 text-xxs font-weight-normal mb-1">
                                                    {{ $ei->product->name }} ({{ $ei->quantity }})
                                                </span>
                                            @endif
                                        @endforeach
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif
        </div>
    </div>
@stop

@section('js')
    <script>
        $(document).ready(function() {
            // Confirmaciones locales de acciones
        });
    </script>
    @include('admin.partials.delete-confirm')
    @include('admin.partials.confirm-action')
@endsection
