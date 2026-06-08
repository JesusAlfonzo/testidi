@extends('adminlte::page')

@section('title', 'Detalle de Entrada de Stock #' . $stockIn->id)

@section('plugins.Sweetalert2', true)

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="text-dark font-weight-bold"><i class="fas fa-file-invoice text-primary mr-2"></i> Entrada de Stock #{{ $stockIn->id }}</h1>
            <p class="text-muted mb-0">Comprobante de ingreso de mercancía a almacén.</p>
        </div>
        <div>
            <a href="{{ route('admin.stock-in.pdf', $stockIn) }}" target="_blank" class="btn btn-outline-primary mr-2 d-print-none">
                <i class="fas fa-file-pdf mr-1"></i> Descargar PDF
            </a>
            <a href="{{ route('admin.stock-in.index') }}" class="btn btn-outline-secondary d-print-none">
                <i class="fas fa-arrow-left mr-1"></i> Volver
            </a>
        </div>
    </div>
@stop

@section('content')
    <div class="container-fluid">
        @include('admin.partials.session-messages')

        <div class="row">
            <!-- COLUMNA PRINCIPAL - DETALLE (IZQUIERDA - 70%) -->
            <div class="col-lg-8 col-md-12">
                {{-- Card de Ítems / Productos --}}
                <div class="card card-outline card-success shadow-sm mb-4">
                    <div class="card-header bg-white py-3">
                        <h3 class="card-title text-success font-weight-bold mb-0">
                            <i class="fas fa-boxes mr-1"></i> Detalle de Mercancía Recibida
                        </h3>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover table-striped mb-0">
                                <thead class="thead-light">
                                    <tr>
                                        <th>Producto</th>
                                        <th>Código</th>
                                        <th class="text-center" style="width: 80px;">Cant.</th>
                                        <th class="text-right" style="width: 120px;">Costo Unit.</th>
                                        <th class="text-center" style="width: 90px;">Moneda</th>
                                        <th class="text-center" style="width: 95px;">IVA</th>
                                        <th class="text-right" style="width: 120px;">Total</th>
                                        <th>Lote / Ubicación</th>
                                        <th class="text-center">Estado</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php $totalGeneral = 0; @endphp
                                    @foreach($stockIn->items as $item)
                                        @php
                                            $currency = $stockIn->purchaseOrder ? $stockIn->purchaseOrder->currency : 'USD';
                                            $ivaStatus = $stockIn->purchaseOrder ? ($stockIn->purchaseOrder->iva_exempt ? 'Exento' : 'Gravado') : 'Exento';
                                        @endphp
                                        <tr>
                                            <td class="align-middle">
                                                @if($item->product)
                                                    <a href="{{ route('admin.products.show', $item->product) }}" class="font-weight-bold">
                                                        {{ $item->product->name }}
                                                    </a>
                                                @else
                                                    <span class="font-weight-bold">{{ $item->product?->name ?? 'N/A' }}</span>
                                                @endif
                                                @if($item->serial_number)
                                                    <div class="mt-1">
                                                        <span class="badge badge-warning small"><i class="fas fa-barcode"></i> Series: {{ $item->serial_number }}</span>
                                                    </div>
                                                @endif
                                            </td>
                                            <td class="align-middle"><code>{{ $item->product->code ?? 'N/A' }}</code></td>
                                            <td class="text-center align-middle font-weight-bold">
                                                {{ $item->quantity }}
                                            </td>
                                            <td class="text-right align-middle text-monospace">
                                                {{ number_format($item->unit_cost, 2) }}
                                            </td>
                                            <td class="text-center align-middle">
                                                <span class="badge badge-info">{{ $currency }}</span>
                                            </td>
                                            <td class="text-center align-middle">
                                                <span class="badge {{ $ivaStatus === 'Exento' ? 'badge-secondary' : 'badge-primary' }}">{{ $ivaStatus }}</span>
                                            </td>
                                            <td class="text-right align-middle text-monospace font-weight-bold">
                                                {{ number_format($item->quantity * $item->unit_cost, 2) }}
                                            </td>
                                            <td class="align-middle small">
                                                <span class="d-block mb-1"><strong>Lote:</strong> <span class="badge badge-light border">{{ $item->batch_number ?? 'N/A' }}</span></span>
                                                @if($item->expiration_date)
                                                    <span class="d-block mb-1"><strong>Venc.:</strong> 
                                                        <span class="badge {{ \Carbon\Carbon::parse($item->expiration_date)->isPast() ? 'badge-danger' : 'badge-warning' }}">
                                                            {{ \Carbon\Carbon::parse($item->expiration_date)->format('d/m/Y') }}
                                                        </span>
                                                    </span>
                                                @endif
                                                <span class="d-block"><strong>Ubicación:</strong> <i class="fas fa-map-marker-alt text-muted mr-1"></i>{{ $item->warehouse_location ?? 'N/A' }}</span>
                                            </td>
                                            <td class="text-center align-middle">
                                                @if($item->status === 'rejected')
                                                    <span class="badge badge-danger">Rechazado</span>
                                                    @if($item->rejection_reason)
                                                        <small class="text-muted d-block mt-1">{{ $item->rejection_reason }}</small>
                                                    @endif
                                                @elseif($item->status === 'replaced')
                                                    <span class="badge badge-info">Reemplazado</span>
                                                @else
                                                    <span class="badge badge-success">Recibido</span>
                                                @endif
                                            </td>
                                        </tr>
                                        @if($item->status !== 'rejected')
                                            @php $totalGeneral += $item->quantity * $item->unit_cost; @endphp
                                        @endif
                                    @endforeach
                                    <tr class="table-success font-weight-bold">
                                        <td colspan="6" class="text-right align-middle">TOTAL RECIBIDO (NETO):</td>
                                        <td class="text-right align-middle text-monospace font-weight-bold" style="font-size: 1.1rem;">
                                            {{ $stockIn->purchaseOrder ? $stockIn->purchaseOrder->currency_symbol : '$' }}{{ number_format($totalGeneral, 2) }}
                                        </td>
                                        <td colspan="2"></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                {{-- Acciones para productos rechazados si existen --}}
                @if($stockIn->items()->where('status', 'rejected')->count() > 0 && $stockIn->type !== 'replacement')
                    <div class="card card-outline card-warning shadow-sm mb-4">
                        <div class="card-header bg-warning-light py-3">
                            <h3 class="card-title text-warning-dark font-weight-bold mb-0">
                                <i class="fas fa-exclamation-triangle mr-1"></i> Productos Rechazados en Recepción
                            </h3>
                        </div>
                        <div class="card-body">
                            <p class="mb-3 text-dark">
                                Se registraron <strong>{{ $stockIn->items()->where('status', 'rejected')->count() }}</strong> productos en estado "Rechazado". Puede iniciar el proceso de reemplazo para registrar el ingreso de la mercancía subsanada.
                            </p>
                            <a href="{{ route('admin.stock-in.create-replacement', $stockIn) }}" class="btn btn-warning shadow-sm font-weight-bold">
                                <i class="fas fa-sync-alt mr-1"></i> Registrar Reemplazo de Mercancía
                            </a>
                        </div>
                    </div>
                @endif

                {{-- Si es una entrada de reemplazo --}}
                @if($stockIn->type === 'replacement' && $stockIn->originalStockIn)
                    <div class="card card-outline card-info shadow-sm mb-4">
                        <div class="card-header bg-white py-3">
                            <h3 class="card-title text-info font-weight-bold mb-0">
                                <i class="fas fa-link mr-1"></i> Origen de Reemplazo
                            </h3>
                        </div>
                        <div class="card-body">
                            <p class="mb-0 text-dark">
                                Esta entrada corresponde a un **reemplazo de mercancía rechazada** asociada a la Entrada de Stock original 
                                <a href="{{ route('admin.stock-in.show', $stockIn->originalStockIn) }}" class="font-weight-bold text-info">#{{ $stockIn->originalStockIn->id }}</a>.
                            </p>
                        </div>
                    </div>
                @endif
            </div>

            <!-- COLUMNA LATERAL - METADATOS (DERECHA - 30%) -->
            <div class="col-lg-4 col-md-12">
                {{-- Card de Información de Documento --}}
                <div class="card card-outline card-primary shadow-sm mb-4">
                    <div class="card-header bg-white py-3">
                        <h3 class="card-title text-primary font-weight-bold mb-0">
                            <i class="fas fa-info-circle mr-1"></i> Datos de Recepción
                        </h3>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-sm table-striped m-0">
                            <tbody>
                                <tr>
                                    <th class="p-3 text-muted" style="width: 140px;">Fecha Ingreso:</th>
                                    <td class="p-3 text-dark font-weight-bold">{{ \Carbon\Carbon::parse($stockIn->entry_date)->format('d/m/Y') }}</td>
                                </tr>
                                <tr>
                                    <th class="p-3 text-muted">Tipo Documento:</th>
                                    <td class="p-3 text-dark font-weight-bold">{{ $stockIn->document_type ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th class="p-3 text-muted">N° Documento:</th>
                                    <td class="p-3"><span class="badge badge-light border font-weight-bold">{{ $stockIn->document_number ?? 'N/A' }}</span></td>
                                </tr>
                                <tr>
                                    <th class="p-3 text-muted">N° Factura:</th>
                                    <td class="p-3"><span class="badge badge-info font-weight-bold">{{ $stockIn->invoice_number ?? 'Sin factura' }}</span></td>
                                </tr>
                                <tr>
                                    <th class="p-3 text-muted">Guía / Entrega:</th>
                                    <td class="p-3 text-dark">{{ $stockIn->delivery_note_number ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th class="p-3 text-muted">Razón de Ingreso:</th>
                                    <td class="p-3 text-dark">{{ $stockIn->reason ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th class="p-3 text-muted">Registrado por:</th>
                                    <td class="p-3 text-dark"><i class="fas fa-user-edit text-muted mr-1"></i>{{ $stockIn->user->name ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th class="p-3 text-muted">Fecha Registro:</th>
                                    <td class="p-3 text-dark small">{{ $stockIn->created_at->format('d/m/Y H:i') }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Card de Proveedor --}}
                <div class="card card-outline card-secondary shadow-sm mb-4">
                    <div class="card-header bg-white py-3">
                        <h3 class="card-title text-secondary font-weight-bold mb-0">
                            <i class="fas fa-truck mr-1"></i> Proveedor
                        </h3>
                    </div>
                    <div class="card-body">
                        @if($stockIn->supplier)
                            <h5 class="font-weight-bold text-dark mb-2">{{ $stockIn->supplier->name }}</h5>
                            <p class="text-muted small mb-3"><i class="fas fa-id-card mr-1"></i> RIF: {{ $stockIn->supplier->rif ?? 'N/A' }}</p>
                            <hr class="my-2">
                            <ul class="list-unstyled mb-0 small">
                                <li class="mb-2"><i class="fas fa-phone text-muted mr-2"></i> {{ $stockIn->supplier->phone ?? 'N/A' }}</li>
                                <li><i class="fas fa-envelope text-muted mr-2"></i> {{ $stockIn->supplier->email ?? 'N/A' }}</li>
                            </ul>
                            <a href="{{ route('admin.suppliers.show', $stockIn->supplier) }}" class="btn btn-xs btn-outline-secondary btn-block mt-3 d-print-none">
                                <i class="fas fa-external-link-alt mr-1"></i> Ver Ficha Proveedor
                            </a>
                        @else
                            <div class="text-center py-3 text-muted">
                                <i class="fas fa-info-circle fa-2x mb-2 text-secondary"></i>
                                <p class="mb-0">Sin Proveedor Asignado</p>
                                <small class="text-muted">(Ajuste o ingreso directo)</small>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Card de Orden de Compra Relacionada --}}
                @if($stockIn->purchaseOrder)
                    <div class="card card-outline card-info shadow-sm mb-4">
                        <div class="card-header bg-white py-3">
                            <h3 class="card-title text-info font-weight-bold mb-0">
                                <i class="fas fa-file-contract mr-1"></i> Orden de Compra
                            </h3>
                        </div>
                        <div class="card-body">
                            <h6 class="font-weight-bold text-dark mb-2">Orden de Compra: {{ $stockIn->purchaseOrder->code }}</h6>
                            <p class="text-muted small mb-2">Fecha emisión: {{ $stockIn->purchaseOrder->date_issued->format('d/m/Y') }}</p>
                            <p class="mb-3">Monto total: <strong class="text-dark">{{ $stockIn->purchaseOrder->currency_symbol }}{{ number_format($stockIn->purchaseOrder->total, 2) }}</strong></p>
                            <a href="{{ route('admin.purchaseOrders.show', $stockIn->purchaseOrder) }}" class="btn btn-xs btn-outline-info btn-block d-print-none">
                                <i class="fas fa-eye mr-1"></i> Ver Orden de Compra
                            </a>
                        </div>
                    </div>
                @endif

                {{-- Acciones Administrativas --}}
                @can('entradas_eliminar')
                    <div class="card shadow-sm border-danger d-print-none">
                        <div class="card-body p-3">
                            <button type="button" class="btn btn-danger btn-block font-weight-bold shadow-sm" data-toggle="modal" data-target="#revertModal">
                                <i class="fas fa-undo-alt mr-1"></i> Revertir Stock
                            </button>
                            <small class="text-muted d-block text-center mt-2">Seleccione qué productos y qué cantidades desea revertir del inventario.</small>
                        </div>
                    </div>
                @endcan
            </div>
        </div>
    </div>

    {{-- Modal de Revertido Selectivo --}}
    @can('entradas_eliminar')
    <div class="modal fade" id="revertModal" tabindex="-1" role="dialog" aria-labelledby="revertModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable" role="document">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title font-weight-bold" id="revertModalLabel">
                        <i class="fas fa-undo-alt mr-1"></i> Revertir Stock - Entrada #{{ $stockIn->id }}
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Cerrar">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p class="text-muted mb-3">
                        Seleccione los productos y especifique las cantidades a revertir del inventario.
                        Esta acción <strong>no se puede deshacer</strong>.
                    </p>
                    <div class="table-responsive">
                        <table class="table table-sm table-hover table-bordered mb-0">
                            <thead class="thead-light">
                                <tr>
                                    <th class="text-center" style="width: 40px;">
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox" class="custom-control-input" id="selectAll">
                                            <label class="custom-control-label" for="selectAll"></label>
                                        </div>
                                    </th>
                                    <th>Producto</th>
                                    <th class="text-center" style="width: 80px;">Código</th>
                                    <th class="text-center" style="width: 80px;">Recibido</th>
                                    <th class="text-center" style="width: 120px;">Cant. a Revertir</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($stockIn->items as $item)
                                    @if($item->status === 'received')
                                    <tr>
                                        <td class="text-center align-middle">
                                            <div class="custom-control custom-checkbox">
                                                <input type="checkbox" class="custom-control-input revert-checkbox" id="revert-{{ $item->id }}" data-id="{{ $item->id }}" data-max="{{ $item->quantity }}">
                                                <label class="custom-control-label" for="revert-{{ $item->id }}"></label>
                                            </div>
                                        </td>
                                        <td class="align-middle">
                                            <span class="font-weight-bold">{{ $item->product->name ?? 'N/A' }}</span>
                                        </td>
                                        <td class="text-center align-middle"><code>{{ $item->product->code ?? 'N/A' }}</code></td>
                                        <td class="text-center align-middle font-weight-bold">{{ $item->quantity }}</td>
                                        <td class="text-center align-middle">
                                            <input type="number" class="form-control form-control-sm revert-quantity text-center font-weight-bold" value="{{ $item->quantity }}" min="1" max="{{ $item->quantity }}" disabled data-item-id="{{ $item->id }}" style="width: 100px; margin: 0 auto;">
                                        </td>
                                    </tr>
                                    @endif
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-danger font-weight-bold" id="btnRevertSelected">
                        <i class="fas fa-undo-alt mr-1"></i> Revertir Seleccionados
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endcan
@stop

@section('js')
<script>
    $(document).ready(function() {
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        // Select All / Deselect All
        $('#selectAll').on('change', function() {
            var isChecked = $(this).prop('checked');
            $('.revert-checkbox').each(function() {
                $(this).prop('checked', isChecked).trigger('change');
            });
        });

        // Habilitar/deshabilitar quantity cuando se checkea un item
        $(document).on('change', '.revert-checkbox', function() {
            var qtyInput = $(this).closest('tr').find('.revert-quantity');
            qtyInput.prop('disabled', !$(this).prop('checked'));
            if (!$(this).prop('checked')) {
                qtyInput.val(qtyInput.data('max'));
            }

            // Actualizar "Select All"
            var total = $('.revert-checkbox').length;
            var checked = $('.revert-checkbox:checked').length;
            $('#selectAll').prop('checked', total === checked).prop('indeterminate', checked > 0 && checked < total);
        });

        // Validar quantity no exceda el máximo
        $(document).on('input', '.revert-quantity', function() {
            var max = parseInt($(this).attr('max'));
            var val = parseInt($(this).val());
            if (val > max) {
                $(this).val(max);
            }
            if (val < 1 || isNaN(val)) {
                $(this).val(1);
            }
        });

        // Botón de revertir
        $('#btnRevertSelected').on('click', function() {
            var selectedItems = [];
            $('.revert-checkbox:checked').each(function() {
                var row = $(this).closest('tr');
                var qtyInput = row.find('.revert-quantity');
                selectedItems.push({
                    id: $(this).data('id'),
                    quantity: parseInt(qtyInput.val())
                });
            });

            if (selectedItems.length === 0) {
                Swal.fire('Selección vacía', 'Debe seleccionar al menos un producto para revertir.', 'warning');
                return;
            }

            var productNames = [];
            $('.revert-checkbox:checked').each(function() {
                productNames.push($(this).closest('tr').find('td:nth-child(2)').text().trim());
            });
            var summary = productNames.join(', ');

            Swal.fire({
                title: '¿Confirmar reversión de stock?',
                html: '<p>Se revertirán los siguientes productos:</p><p class="font-weight-bold text-danger">' + summary + '</p><p class="text-muted small">Esta acción no se puede deshacer.</p>',
                type: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sí, revertir stock',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.value === true || result.isConfirmed) {
                    var btn = $('#btnRevertSelected');
                    btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Revirtiendo...');

                    $.ajax({
                        url: '{{ route("admin.stock-in.revert-items", $stockIn) }}',
                        type: 'POST',
                        data: {
                            items: selectedItems
                        },
                        success: function(response) {
                            $('#revertModal').modal('hide');
                            Swal.fire('¡Revertido!', response.message, 'success').then(function() {
                                location.reload();
                            });
                        },
                        error: function(xhr) {
                            var msg = 'No se pudo revertir el stock. Intente de nuevo.';
                            if (xhr.responseJSON && xhr.responseJSON.message) {
                                msg = xhr.responseJSON.message;
                            }
                            Swal.fire('Error', msg, 'error');
                        },
                        complete: function() {
                            btn.prop('disabled', false).html('<i class="fas fa-undo-alt mr-1"></i> Revertir Seleccionados');
                        }
                    });
                }
            });
        });

        // Reset modal al cerrar
        $('#revertModal').on('hidden.bs.modal', function() {
            $('#selectAll').prop('checked', false);
            $('.revert-checkbox').prop('checked', false).trigger('change');
        });
    });
</script>
@stop
