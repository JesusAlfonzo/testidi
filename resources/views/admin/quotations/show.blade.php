@extends('adminlte::page')

@section('title', 'Cotización ' . $quotation->code)

@section('content_header')
    <div class="d-flex justify-content-between">
        <h1><i class="fas fa-file-alt"></i> Cotización {{ $quotation->code }}</h1>
        <div>
            <a href="{{ route('admin.quotations.pdf', $quotation) }}" class="btn btn-secondary" target="_blank">
                <i class="fas fa-file-pdf"></i> PDF
            </a>
            @if($quotation->isEditable())
                @can('cotizaciones_editar')
                    <a href="{{ route('admin.quotations.edit', $quotation) }}" class="btn btn-primary">
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
                                    <td><strong>{{ $quotation->code }}</strong></td>
                                </tr>
                                <tr>
                                    <th>Estado:</th>
                                    <td>{!! $quotation->status_badge !!}</td>
                                </tr>
                                <tr>
                                    <th>RFQ Relacionada:</th>
                                    <td>
                                        @if($quotation->rfq)
                                            <a href="{{ route('admin.rfq.show', $quotation->rfq) }}">{{ $quotation->rfq->code }}</a>
                                        @else
                                            Sin RFQ
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>Ref. Proveedor:</th>
                                    <td>{{ $quotation->supplier_reference ?? '-' }}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-sm">
                                <tr>
                                    <th width="40%">Fecha Emisión:</th>
                                    <td>{{ $quotation->date_issued->format('d/m/Y') }}</td>
                                </tr>
                                <tr>
                                    <th>Válido hasta:</th>
                                    <td>{{ $quotation->valid_until?->format('d/m/Y') ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>Entrega ofertada:</th>
                                    <td>{{ $quotation->delivery_date?->format('d/m/Y') ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>Registrado por:</th>
                                    <td>{{ $quotation->user->name ?? 'N/A' }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card card-outline card-warning mt-3">
                <div class="card-header">
                    <h3 class="card-title">
                        @if($quotation->hasRegisteredSupplier())
                            <i class="fas fa-check-circle text-success"></i>
                        @else
                            <i class="fas fa-clock text-warning"></i>
                        @endif
                        Proveedor
                    </h3>
                </div>
                <div class="card-body">
                    @if($quotation->hasRegisteredSupplier())
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Nombre:</strong> {{ $quotation->supplier->name }}</p>
                                <p><strong>RIF:</strong> {{ $quotation->supplier->tax_id ?? '-' }}</p>
                                <p><strong>Contacto:</strong> {{ $quotation->supplier->contact_person ?? '-' }}</p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Email:</strong> {{ $quotation->supplier->email ?? '-' }}</p>
                                <p><strong>Teléfono:</strong> {{ $quotation->supplier->phone ?? '-' }}</p>
                                <p><strong>Dirección:</strong> {{ $quotation->supplier->address ?? '-' }}</p>
                            </div>
                        </div>
                    @else
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle"></i> Este proveedor es <strong>temporal</strong> (no está registrado en el sistema).
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <p><strong>Nombre:</strong> {{ $quotation->supplier_name_temp }}</p>
                            </div>
                            <div class="col-md-4">
                                <p><strong>Email:</strong> {{ $quotation->supplier_email_temp ?? '-' }}</p>
                            </div>
                            <div class="col-md-4">
                                <p><strong>Teléfono:</strong> {{ $quotation->supplier_phone_temp ?? '-' }}</p>
                            </div>
                        </div>

                        @can('proveedores_crear')
                            <button type="button" class="btn btn-success mt-3" data-toggle="modal" data-target="#convertSupplierModal">
                                <i class="fas fa-user-plus"></i> Registrar este Proveedor
                            </button>
                        @endcan
                    @endif
                </div>
            </div>

            <div class="card card-outline card-success mt-3">
                <div class="card-header">
                    <h3 class="card-title">Items Cotizados ({{ $quotation->items->count() }})</h3>
                </div>
                <div class="card-body p-0">
                    <table class="table table-striped table-bordered m-0">
                        <thead class="thead-light">
                            <tr>
                                <th>#</th>
                                <th>Producto</th>
                                <th>Cantidad</th>
                                <th>Costo Unit.</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($quotation->items as $index => $item)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>
                                        <strong>{{ $item->product_name }}</strong>
                                        @if($item->product)
                                            <br><small class="text-muted">{{ $item->product->code ?? 'S/C' }}</small>
                                        @endif
                                    </td>
                                    <td>{{ $item->quantity }}</td>
                                    <td>${{ number_format($item->unit_cost, 2) }}</td>
                                    <td><strong>${{ number_format($item->total_cost, 2) }}</strong></td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <th colspan="4" class="text-right">Subtotal:</th>
                                <th>${{ number_format($quotation->subtotal, 2) }}</th>
                            </tr>
                            <tr>
                                <th colspan="4" class="text-right">Total ({{ $quotation->currency }}):</th>
                                <th class="text-primary h5">${{ number_format($quotation->total, 2) }}</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            @if($quotation->rejection_reason)
                <div class="alert alert-danger mt-3">
                    <strong>Motivo de rechazo:</strong> {{ $quotation->rejection_reason }}
                </div>
            @endif

            @if($quotation->approved_by)
                <div class="alert alert-success mt-3">
                    <i class="fas fa-check-circle"></i> Aprobada por <strong>{{ $quotation->approver->name }}</strong> el {{ $quotation->approved_at->format('d/m/Y H:i') }}
                </div>
            @endif

            <div class="card card-outline card-primary mt-3">
                <div class="card-header">
                    <h3 class="card-title">Acciones</h3>
                </div>
                <div class="card-body">
                    @if($quotation->status === 'pending')
                        @can('cotizaciones_aprobar')
                            <form action="{{ route('admin.quotations.select', $quotation) }}" method="POST" style="display:inline;">
                                @csrf
                                <button type="submit" class="btn btn-info" onclick="return confirm('¿Seleccionar esta cotización para revisión administrativa?')">
                                    <i class="fas fa-star"></i> Seleccionar para Aprobación
                                </button>
                            </form>
                        @endcan
                        @can('cotizaciones_rechazar')
                            <button type="button" class="btn btn-danger" data-toggle="modal" data-target="#rejectModal">
                                <i class="fas fa-times"></i> Rechazar
                            </button>
                        @endcan
                    @elseif($quotation->status === 'selected')
                        @can('cotizaciones_aprobar')
                            <form action="{{ route('admin.quotations.approve', $quotation) }}" method="POST" style="display:inline;">
                                @csrf
                                <button type="submit" class="btn btn-success btn-lg" onclick="return confirm('¿Aprobar esta cotización?')">
                                    <i class="fas fa-check"></i> Aprobar Cotización
                                </button>
                            </form>
                        @endcan
                        @can('cotizaciones_rechazar')
                            <button type="button" class="btn btn-danger" data-toggle="modal" data-target="#rejectModal">
                                <i class="fas fa-times"></i> Rechazar
                            </button>
                        @endcan
                    @elseif($quotation->status === 'approved')
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle"></i> Esta cotización está aprobada y lista para generar una Orden de Compra.
                        </div>
                        @can('ordenes_compra_crear')
                            <a href="{{ route('admin.purchaseOrders.create', ['quote' => $quotation->id]) }}" class="btn btn-primary btn-lg">
                                <i class="fas fa-shopping-cart"></i> Generar Orden de Compra
                            </a>
                        @endcan
                    @endif
                </div>
            </div>
        </div>
    </div>

    @if(! $quotation->hasRegisteredSupplier())
        @can('proveedores_crear')
            <div class="modal fade" id="convertSupplierModal" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <form action="{{ route('admin.quotations.convert-supplier', $quotation) }}" method="POST">
                            @csrf
                            <div class="modal-header">
                                <h5 class="modal-title">Registrar Proveedor</h5>
                                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                            </div>
                            <div class="modal-body">
                                <p>Se creará un nuevo proveedor con los datos temporales:</p>
                                <div class="form-group">
                                    <label>Nombre</label>
                                    <input type="text" class="form-control" value="{{ $quotation->supplier_name_temp }}" readonly>
                                </div>
                                <div class="form-group">
                                    <label>RIF / NIT</label>
                                    <input type="text" name="tax_id" class="form-control">
                                </div>
                                <div class="form-group">
                                    <label>Persona de Contacto</label>
                                    <input type="text" name="contact_person" class="form-control">
                                </div>
                                <div class="form-group">
                                    <label>Dirección</label>
                                    <textarea name="address" class="form-control" rows="2"></textarea>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                                <button type="submit" class="btn btn-success">Registrar Proveedor</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @endcan
    @endif

    @can('cotizaciones_rechazar')
        <div class="modal fade" id="rejectModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form action="{{ route('admin.quotations.reject', $quotation) }}" method="POST">
                        @csrf
                        <div class="modal-header">
                            <h5 class="modal-title text-danger">Rechazar Cotización</h5>
                            <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                        </div>
                        <div class="modal-body">
                            <div class="form-group">
                                <label>Motivo del rechazo (*)</label>
                                <textarea name="rejection_reason" class="form-control" rows="3" required placeholder="Explique el motivo del rechazo..."></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn btn-danger">Rechazar Cotización</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endcan

    <div class="row mt-3">
        <div class="col-12">
            <a href="{{ route('admin.quotations.index') }}" class="btn btn-default">
                <i class="fas fa-arrow-left"></i> Volver al listado
            </a>
        </div>
    </div>
@stop
