@extends('adminlte::page')

@section('title', 'Detalle Solicitud #REQ-' . $request->id)

@section('plugins.Sweetalert2', true)

@section('content_header')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h1 class="text-dark font-weight-bold" style="font-size: 1.75rem;">
                Detalle de Solicitud #REQ-{{ $request->id }}
            </h1>
            <p class="text-muted mb-0">Visualice y gestione la entrega de mercancías asociadas a esta solicitud.</p>
        </div>
        <div class="d-flex">
            <a href="{{ route('admin.requests.pdf', $request->id) }}" target="_blank" class="btn btn-outline-secondary px-3 py-2 mr-2" style="border-radius: 8px;">
                <i class="fas fa-file-pdf mr-1"></i> Imprimir Acta
            </a>
            <a href="{{ route('admin.requests.index') }}" class="btn btn-secondary px-3 py-2" style="border-radius: 8px;">
                <i class="fas fa-arrow-left mr-1"></i> Volver al Listado
            </a>
        </div>
    </div>
@stop

@section('content')
    @include('admin.partials.session-messages')

    <div class="row">
        {{-- COLUMNA DETALLES (50% / 5 col) --}}
        <div class="col-lg-5">
            {{-- Información de la Solicitud --}}
            <div class="card card-custom p-3 bg-white mb-3" style="border-radius: 12px; border: 1px solid #e5e7eb; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                <h5 class="font-weight-bold text-dark mb-3"><i class="fas fa-info-circle text-info mr-2"></i> Información General</h5>
                
                <table class="table table-sm text-sm" style="background: transparent;">
                    <tbody>
                        <tr>
                            <td class="text-secondary font-weight-bold py-2" style="border: none; width: 40%">Estado:</td>
                            <td class="py-2" style="border: none;">
                                <span class="badge badge-{{ $request->status_badge_class }}">
                                    {{ $request->status_label }}
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <td class="text-secondary font-weight-bold py-2" style="border-top: 1px solid #f3f4f6;">Solicitante:</td>
                            <td class="font-weight-bold py-2" style="border-top: 1px solid #f3f4f6;">{{ $request->requester->name ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td class="text-secondary font-weight-bold py-2" style="border-top: 1px solid #f3f4f6;">Departamento/Área:</td>
                            <td class="py-2" style="border-top: 1px solid #f3f4f6;">{{ $request->destination_area ?? 'N/A' }}</td>
                        </tr>
                        @if($request->reference)
                            <tr>
                                <td class="text-secondary font-weight-bold py-2" style="border-top: 1px solid #f3f4f6;">Referencia:</td>
                                <td class="py-2" style="border-top: 1px solid #f3f4f6;">{{ $request->reference }}</td>
                            </tr>
                        @endif
                        <tr>
                            <td class="text-secondary font-weight-bold py-2" style="border-top: 1px solid #f3f4f6;">Fecha Solicitud:</td>
                            <td class="py-2" style="border-top: 1px solid #f3f4f6;">{{ optional($request->requested_at)->format('d/m/Y h:i A') }}</td>
                        </tr>
                    </tbody>
                </table>

                <hr style="border-top: 1px solid #e5e7eb; margin: 1rem 0;">
                <h6 class="font-weight-bold text-secondary text-uppercase mb-2 text-xs">Justificación</h6>
                <p class="text-muted text-sm mb-0">{{ $request->justification }}</p>

                @if ($request->status !== 'Pending')
                    <hr style="border-top: 1px solid #e5e7eb; margin: 1rem 0;">
                    <h6 class="font-weight-bold text-secondary text-uppercase mb-2 text-xs">Decisión Final</h6>
                    <table class="table table-sm text-sm mb-0" style="background: transparent;">
                        <tbody>
                            <tr>
                                <td class="text-secondary font-weight-bold py-2" style="border: none;">Procesado por:</td>
                                <td class="font-weight-bold py-2" style="border: none;">{{ $request->approver->name ?? 'Sistema' }}</td>
                            </tr>
                            <tr>
                                <td class="text-secondary font-weight-bold py-2" style="border-top: 1px solid #f3f4f6;">Fecha Procesado:</td>
                                <td class="py-2" style="border-top: 1px solid #f3f4f6;">{{ optional($request->processed_at)->format('d/m/Y h:i A') }}</td>
                            </tr>
                        </tbody>
                    </table>

                    @if ($request->rejection_reason)
                        <div class="alert alert-danger mt-3 mb-0" style="border-radius: 8px;">
                            <strong class="d-block mb-1"><i class="fas fa-exclamation-circle mr-1"></i> Motivo de Rechazo:</strong>
                            {{ $request->rejection_reason }}
                        </div>
                    @endif
                @endif
            </div>

            {{-- Panel de Acciones (Solo si está Pendiente) --}}
            @if ($request->status === 'Pending')
                @can('solicitudes_aprobar')
                    <div class="card card-custom p-3 bg-white mb-3" style="border-radius: 12px; border: 1px solid #e5e7eb; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                        <h5 class="font-weight-bold text-dark mb-3"><i class="fas fa-cog text-primary mr-2"></i> Acciones del Operador</h5>
                        <p class="text-xs text-muted mb-3">La aprobación rebajará automáticamente las existencias de productos individuales y kits en el inventario.</p>
                        
                        <button type="button" class="btn btn-success btn-block font-weight-bold py-2 mb-2 btn-approve-request-show" style="border-radius: 8px;">
                            <i class="fas fa-check-circle mr-1"></i> Aprobar Despacho
                        </button>
                        
                        <form id="approve-form" action="{{ route('admin.requests.process', $request->id) }}" method="POST" style="display: none;">
                            @csrf
                            <input type="hidden" name="action" value="approve">
                        </form>
                        
                        <button type="button" class="btn btn-danger btn-block font-weight-bold py-2 btn-reject-request-show" data-toggle="modal" data-target="#rejectModalShow" style="border-radius: 8px;">
                            <i class="fas fa-times-circle mr-1"></i> Rechazar Solicitud
                        </button>
                    </div>
                @else
                    <div class="alert alert-warning" style="border-radius: 8px;">
                        <i class="fas fa-exclamation-triangle mr-1"></i> Esta solicitud está pendiente. Su rol no posee permisos para aprobar o rechazar despachos.
                    </div>
                @endcan
            @endif
        </div>
        
        {{-- COLUMNA ÍTEMS (70% / 7 col) --}}
        <div class="col-lg-7">
            <div class="card card-custom p-3 bg-white" style="border-radius: 12px; border: 1px solid #e5e7eb; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                <h5 class="font-weight-bold text-dark mb-3"><i class="fas fa-boxes text-danger mr-2"></i> Ítems Solicitados</h5>
                
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr class="bg-light text-secondary text-xs font-weight-bold text-uppercase">
                                <th style="border: none;">Producto / Kit</th>
                                <th style="width: 20%; border: none;" class="text-center">Solicitado</th>
                                <th style="width: 25%; border: none;">Stock Actual</th>
                                <th style="width: 20%; border: none;" class="text-right">Costo Unit.</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($request->items as $item)
                                @php
                                    $product = $item->product;
                                    $isKit = $product && $product->type === 'composite_kit';
                                    $itemName = $product ? $product->name : 'Producto Eliminado';
                                    $itemCode = $product ? $product->code : 'N/A';
                                    $unitAbbr = ($product && $product->unit) ? $product->unit->abbreviation : 'unid';
                                    
                                    $stockOk = true;
                                    if ($product) {
                                        $stockOk = $product->stock >= $item->quantity_requested;
                                    }
                                @endphp

                                <tr class="{{ !$stockOk ? 'table-danger' : '' }}" style="transition: all 0.2s ease;">
                                    <td style="vertical-align: middle;">
                                        <div>
                                            @if($isKit)
                                                <i class="fas fa-cubes text-info mr-1"></i> 
                                            @else
                                                <i class="fas fa-cube text-primary mr-1"></i>
                                            @endif
                                            @if($product)
                                                <a href="{{ route('admin.products.show', $item->product_id) }}" class="text-primary font-weight-bold" style="text-decoration: none;">{{ $itemName }}</a>
                                            @else
                                                <strong class="text-dark">{{ $itemName }}</strong>
                                            @endif
                                            <small class="d-block text-muted">SKU: {{ $itemCode }}</small>
                                            
                                            @if($isKit && $product)
                                                <span class="badge badge-secondary py-1 px-2 mt-1 text-xs"><i class="fas fa-info-circle"></i> Kit ({{ $product->components->count() }} componentes)</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="text-center font-weight-bold" style="vertical-align: middle;">
                                        {{ $item->quantity_requested }} {{ $unitAbbr }}
                                    </td>
                                    <td style="vertical-align: middle;">
                                        @if($stockOk)
                                            <span class="badge badge-success py-1 px-2"><i class="fas fa-check mr-1"></i> {{ $product->stock ?? 0 }} disponible</span>
                                        @else
                                            <span class="badge badge-danger py-1 px-2"><i class="fas fa-exclamation-triangle mr-1"></i> {{ $product->stock ?? 0 }} insuficiente</span>
                                            
                                            {{-- Descomposición de Kits --}}
                                            @if($request->status === 'Pending' && isset($decomposableKits[$product->id]) && count($decomposableKits[$product->id]) > 0)
                                                <div class="mt-2">
                                                    <button type="button" class="btn btn-xs btn-warning font-weight-bold btn-decompose-kit py-1 px-2" style="border-radius: 4px;"
                                                            data-product-id="{{ $product->id }}"
                                                            data-product-name="{{ $product->name }}"
                                                            data-qty-requested="{{ $item->quantity_requested }}"
                                                            data-qty-deficient="{{ $item->quantity_requested - $product->stock }}"
                                                            data-kits="{{ json_encode($decomposableKits[$product->id]) }}">
                                                        <i class="fas fa-tools"></i> Descomponer Kit
                                                    </button>
                                                </div>
                                            @endif
                                        @endif
                                    </td>
                                    <td class="text-right font-weight-bold text-secondary" style="vertical-align: middle;">
                                        ${{ number_format($item->unit_price_at_request, 2) }}
                                    </td>
                                </tr>

                                {{-- COMPONENTES DE KITS --}}
                                @if($isKit && $product && $product->components->count())
                                    <tr>
                                        <td colspan="4" class="p-0" style="border: none;">
                                            <div class="px-4 py-3 bg-light rounded border-left" style="border-left: 4px solid #17a2b8 !important; margin: 4px 16px 12px 16px;">
                                                <strong class="text-xs text-secondary text-uppercase mb-2 d-block"><i class="fas fa-cubes text-info mr-1"></i> Desglose de Componentes del Kit:</strong>
                                                <table class="table table-sm table-bordered mt-2 mb-0 bg-white text-xs">
                                                    <thead class="thead-light">
                                                        <tr>
                                                            <th>Componente</th>
                                                            <th style="width: 25%" class="text-center">Req. Unitario</th>
                                                            <th style="width: 25%" class="text-center">Req. Total</th>
                                                            <th style="width: 25%" class="text-center">Stock</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach($product->components as $comp)
                                                            @php 
                                                                $reqQty = $comp->pivot->quantity * $item->quantity_requested;
                                                                $compStockOk = $comp->stock >= $reqQty;
                                                            @endphp
                                                            <tr class="{{ $compStockOk ? '' : 'table-danger' }}">
                                                                <td>{{ $comp->name }} ({{ $comp->code }})</td>
                                                                <td class="text-center">{{ $comp->pivot->quantity }}</td>
                                                                <td class="text-center font-weight-bold">{{ $reqQty }}</td>
                                                                <td class="text-center font-weight-bold {{ $compStockOk ? 'text-success' : 'text-danger' }}">
                                                                    {{ $comp->stock }}
                                                                    @if(!$compStockOk) <i class="fas fa-exclamation-circle"></i> @endif
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        </td>
                                    </tr>
                                @endif
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center py-3 text-muted">No hay ítems en esta solicitud.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    {{-- MODAL DE RECHAZO --}}
    @if ($request->status === 'Pending' && Gate::allows('solicitudes_aprobar'))
        <div class="modal fade" id="rejectModalShow" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content" style="border-radius: 12px;">
                    <div class="modal-header bg-danger text-white" style="border-top-left-radius: 12px; border-top-right-radius: 12px;">
                        <h5 class="modal-title font-weight-bold"><i class="fas fa-times-circle mr-1"></i> Rechazar Solicitud #REQ-{{ $request->id }}</h5>
                        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group mb-0">
                            <label for="rejection_reason_show" class="font-weight-bold text-secondary">Motivo del Rechazo <span class="text-danger">*</span></label>
                            <textarea id="rejection_reason_show" class="form-control" rows="4" style="border-radius: 8px;" placeholder="Detalle la justificación del rechazo..."></textarea>
                            <div class="invalid-feedback" id="rejection_reason_show_error">El motivo de rechazo es obligatorio.</div>
                        </div>
                    </div>
                    <div class="modal-footer justify-content-between">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal" style="border-radius: 8px;">Cancelar</button>
                        <button type="button" id="btn-confirm-reject-show" class="btn btn-danger font-weight-bold" style="border-radius: 8px;">Confirmar Rechazo</button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- MODAL DE DESCOMPOSICIÓN DE KITS --}}
    @if ($request->status === 'Pending' && Gate::allows('solicitudes_aprobar'))
        <div class="modal fade" id="decomposeModal" tabindex="-1" role="dialog" aria-labelledby="decomposeModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content" style="border-radius: 12px;">
                    <div class="modal-header bg-warning text-dark" style="border-top-left-radius: 12px; border-top-right-radius: 12px;">
                        <h5 class="modal-title font-weight-bold" id="decomposeModalLabel">
                            <i class="fas fa-tools"></i> Descomponer Kit para suplir producto
                        </h5>
                        <button type="button" class="close text-dark" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-info" style="border-radius: 8px;">
                            <i class="fas fa-info-circle"></i> <span id="decompose_helper_text"></span>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="decompose_kit_select">Seleccionar Kit Compuesto</label>
                                    <select id="decompose_kit_select" class="form-control" style="border-radius: 8px;"></select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="decompose_batch_select">Lote del Kit a Descomponer</label>
                                    <select id="decompose_batch_select" class="form-control" style="border-radius: 8px;"></select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="decompose_quantity">Cantidad de Kits a Descomponer</label>
                                    <input type="number" id="decompose_quantity" class="form-control" min="1" value="1" style="border-radius: 8px;">
                                    <small class="form-text text-muted" id="decompose_qty_helper"></small>
                                </div>
                            </div>
                        </div>

                        <div id="decompose_components_card" class="card card-outline card-secondary mt-3" style="border-radius: 8px;">
                            <div class="card-header">
                                <h3 class="card-title font-weight-bold">Componentes del Kit e Ingreso de Seriales</h3>
                            </div>
                            <div class="card-body" id="decompose_components_container">
                                <!-- JS content -->
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer justify-content-between">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal" style="border-radius: 8px;">
                            <i class="fas fa-times"></i> Cancelar
                        </button>
                        <button type="button" id="btn-confirm-decompose" class="btn btn-warning font-weight-bold" style="border-radius: 8px;">
                            <i class="fas fa-tools"></i> Confirmar Descomposición
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
@stop

@section('js')
<script>
    let currentKitsData = [];
    let targetProductId = null;
    let targetProductName = '';
    let targetQtyRequested = 0;
    let targetQtyDeficient = 0;

    function openDecomposeModal(productId, productName, qtyRequested, qtyDeficient, kitsData) {
        currentKitsData = kitsData;
        targetProductId = productId;
        targetProductName = productName;
        targetQtyRequested = qtyRequested;
        targetQtyDeficient = qtyDeficient;

        $('#btn-confirm-decompose').prop('disabled', false).html('<i class="fas fa-tools"></i> Confirmar Descomposición');
        $('#decomposeModalLabel').html('<i class="fas fa-tools"></i> Descomponer Kit para suplir <strong>' + productName + '</strong>');
        $('#decompose_helper_text').text('Faltan ' + qtyDeficient + ' unidades de "' + productName + '". Puede descomponer uno de los siguientes kits para obtener las unidades necesarias.');

        let $kitSelect = $('#decompose_kit_select');
        $kitSelect.empty();
        kitsData.forEach(function(item, index) {
            $kitSelect.append($('<option>', {
                value: index,
                text: item.kit.name + ' (Contiene ' + item.quantity_in_kit + ' unidades de este componente)'
            }));
        });

        $kitSelect.off('change').on('change', function() {
            updateKitDetails();
        });

        updateKitDetails();
        $('#decomposeModal').modal('show');
    }

    function updateKitDetails() {
        let kitIndex = $('#decompose_kit_select').val();
        if (kitIndex === null || kitIndex === undefined) return;

        let kitItem = currentKitsData[kitIndex];
        let $batchSelect = $('#decompose_batch_select');
        $batchSelect.empty();

        kitItem.batches.forEach(function(batch) {
            let label = 'Lote: ' + batch.batch_number + ' (Stock: ' + batch.quantity + ')';
            if (batch.expiration_date) {
                label += ' | Vence: ' + batch.expiration_date;
            }
            $batchSelect.append($('<option>', {
                value: batch.id,
                text: label
            }).data('quantity', batch.quantity));
        });

        let qtyInKit = kitItem.quantity_in_kit;
        let kitsNeeded = Math.ceil(targetQtyDeficient / qtyInKit);
        let selectedBatchQty = $batchSelect.find('option:selected').data('quantity') || 0;
        let defaultQty = Math.min(kitsNeeded, selectedBatchQty) || 1;

        $('#decompose_quantity').val(defaultQty);
        $('#decompose_quantity').attr('max', selectedBatchQty);

        $batchSelect.off('change').on('change', function() {
            let newMax = $(this).find('option:selected').data('quantity') || 0;
            $('#decompose_quantity').attr('max', newMax);
            if (parseInt($('#decompose_quantity').val()) > newMax) {
                $('#decompose_quantity').val(newMax);
            }
            renderComponents();
        });

        $('#decompose_quantity').off('input change').on('input change', function() {
            let val = parseInt($(this).val()) || 1;
            let max = parseInt($(this).attr('max')) || 0;
            if (val > max) $(this).val(max);
            if (val < 1) $(this).val(1);
            renderComponents();
        });

        renderComponents();
    }

    function renderComponents() {
        let kitIndex = $('#decompose_kit_select').val();
        if (kitIndex === null || kitIndex === undefined) return;

        let kitItem = currentKitsData[kitIndex];
        let decompQty = parseInt($('#decompose_quantity').val()) || 1;
        let $container = $('#decompose_components_container');
        $container.empty();

        let components = kitItem.kit.components || [];
        if (components.length === 0) {
            $container.append('<p class="text-muted">Este kit no tiene componentes asociados.</p>');
            return;
        }

        let html = '<div class="table-responsive"><table class="table table-sm table-bordered">';
        html += '<thead><tr class="bg-light"><th>Componente</th><th style="width: 25%">Cantidad a Generar</th><th>Números de Serie</th></tr></thead>';
        html += '<tbody>';

        components.forEach(function(comp) {
            let qtyInKit = comp.pivot.quantity;
            let totalToGenerate = qtyInKit * decompQty;
            let requiresSerial = comp.requires_serial === 1 || comp.requires_serial === true || comp.requires_serial === '1';

            html += '<tr>';
            html += '<td><strong>' + comp.name + '</strong><small class="d-block text-muted">Cód: ' + comp.code + '</small></td>';
            html += '<td class="font-weight-bold text-center">' + totalToGenerate + '</td>';
            html += '<td>';

            if (requiresSerial) {
                html += '<div class="serial-inputs-wrapper" data-product-id="' + comp.id + '" data-quantity="' + totalToGenerate + '" data-name="' + comp.name + '">';
                html += '<small class="text-danger d-block mb-1"><i class="fas fa-exclamation-triangle"></i> Requiere ' + totalToGenerate + ' seriales únicos:</small>';
                for (let i = 0; i < totalToGenerate; i++) {
                    html += '<input type="text" class="form-control form-control-sm serial-input mb-1" ';
                    html += 'data-product-id="' + comp.id + '" ';
                    html += 'placeholder="Serial #' + (i + 1) + '" required style="border-radius:4px;">';
                }
                html += '</div>';
            } else {
                html += '<span class="badge badge-secondary text-xs">No requiere seriales</span>';
            }

            html += '</td></tr>';
        });

        html += '</tbody></table></div>';
        $container.append(html);
    }

    $(document).ready(function() {
        // Configurar CSRF para AJAX
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        // Evento descomponer Kit
        $('.btn-decompose-kit').on('click', function() {
            let productId = $(this).data('product-id');
            let productName = $(this).data('product-name');
            let qtyRequested = $(this).data('qty-requested');
            let qtyDeficient = $(this).data('qty-deficient');
            let kitsData = $(this).data('kits');

            openDecomposeModal(productId, productName, qtyRequested, qtyDeficient, kitsData);
        });

        // Confirmar descomposición
        $('#btn-confirm-decompose').on('click', function() {
            let kitIndex = $('#decompose_kit_select').val();
            if (kitIndex === null || kitIndex === undefined) return;

            let kitItem = currentKitsData[kitIndex];
            let kitId = kitItem.kit.id;
            let batchId = $('#decompose_batch_select').val();
            let quantity = parseInt($('#decompose_quantity').val()) || 0;

            if (!batchId) {
                Swal.fire({ icon: 'error', title: 'Error', text: 'Debe seleccionar un lote del kit a descomponer.' });
                return;
            }

            if (quantity < 1) {
                Swal.fire({ icon: 'error', title: 'Error', text: 'La cantidad a descomponer debe ser al menos 1.' });
                return;
            }

            let serials = {};
            let hasError = false;
            let errorMessage = '';

            $('.serial-inputs-wrapper').each(function() {
                let $wrapper = $(this);
                let productId = $wrapper.data('product-id');
                let expectedQty = parseInt($wrapper.data('quantity'));
                let compName = $wrapper.data('name');

                let compSerials = [];
                $wrapper.find('.serial-input').each(function() {
                    let val = $(this).val().trim();
                    if (val) compSerials.push(val);
                });

                if (compSerials.length < expectedQty) {
                    hasError = true;
                    errorMessage = 'Debe ingresar todos los seriales para "' + compName + '".';
                    return false;
                }

                let uniqueSerials = [...new Set(compSerials)];
                if (uniqueSerials.length < compSerials.length) {
                    hasError = true;
                    errorMessage = 'Los seriales para "' + compName + '" no pueden contener duplicados.';
                    return false;
                }

                serials[productId] = compSerials;
            });

            if (hasError) {
                Swal.fire({ icon: 'error', title: 'Error de Validación', text: errorMessage });
                return;
            }

            Swal.fire({
                title: '¿Confirmar Descomposición?',
                text: 'Se descompondrán ' + quantity + ' unidades del kit "' + kitItem.kit.name + '" para suplir stock.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Sí, descomponer',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    $('#btn-confirm-decompose').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Procesando...');

                    $.ajax({
                        url: '/admin/products/' + kitId + '/decompose',
                        method: 'POST',
                        data: {
                            batch_id: batchId,
                            quantity: quantity,
                            serials: serials
                        },
                        success: function(response) {
                            if (response.success) {
                                Swal.fire({
                                    icon: 'success',
                                    title: '¡Éxito!',
                                    text: response.message,
                                    timer: 2000,
                                    showConfirmButton: false
                                }).then(() => {
                                    location.reload();
                                });
                            } else {
                                Swal.fire({ icon: 'error', title: 'Error', text: response.message });
                                $('#btn-confirm-decompose').prop('disabled', false).html('<i class="fas fa-tools"></i> Confirmar Descomposición');
                            }
                        },
                        error: function(xhr) {
                            let msg = 'Hubo un error al procesar la descomposición.';
                            if (xhr.responseJSON && xhr.responseJSON.message) {
                                msg = xhr.responseJSON.message;
                            }
                            Swal.fire({ icon: 'error', title: 'Error', text: msg });
                            $('#btn-confirm-decompose').prop('disabled', false).html('<i class="fas fa-tools"></i> Confirmar Descomposición');
                        }
                    });
                }
            });
        });

        // 🌟 BOTÓN DE APROBACIÓN POR FORM SUBMIT
        $('.btn-approve-request-show').on('click', function() {
            Swal.fire({
                title: '¿Aprobar Despacho de Almacén?',
                text: 'Esta acción actualizará el stock de todos los ítems solicitados de forma inmediata.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#28a745',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sí, aprobar despacho',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Procesando...',
                        html: 'Por favor espere mientras se reduce el stock del inventario.',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });
                    document.getElementById('approve-form').submit();
                }
            });
        });

        // 🌟 BOTÓN DE CONFIRMACIÓN DE RECHAZO POR AJAX
        $('#btn-confirm-reject-show').on('click', function() {
            var reason = $('#rejection_reason_show').val().trim();
            if (!reason) {
                $('#rejection_reason_show').addClass('is-invalid');
                return;
            }

            $('#rejection_reason_show').removeClass('is-invalid');
            var btn = $(this);
            btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Procesando...');

            $.ajax({
                url: '{{ route('admin.requests.reject', $request->id) }}',
                type: 'POST',
                data: {
                    rejection_reason: reason
                },
                success: function(response) {
                    $('#rejectModalShow').modal('hide');
                    btn.prop('disabled', false).text('Confirmar Rechazo');
                    
                    Swal.fire({
                        icon: 'warning',
                        title: 'Rechazada',
                        text: response.message,
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => {
                        location.reload();
                    });
                },
                error: function(xhr) {
                    $('#rejectModalShow').modal('hide');
                    btn.prop('disabled', false).text('Confirmar Rechazo');
                    
                    var msg = 'No se pudo rechazar la solicitud.';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        msg = xhr.responseJSON.message;
                    }
                    Swal.fire('Error', msg, 'error');
                }
            });
        });
    });
</script>
@stop