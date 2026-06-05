@extends('adminlte::page')

@section('title', 'Detalle Solicitud #' . $request->id)

@section('content_header')
    <h1>
        Detalle de Solicitud #REQ-{{ $request->id }}
        {{-- 🔑 USO DEL ACCESOR: Muestra "Pendiente", "Aprobada" o "Rechazada" en español --}}
        <span class="badge badge-{{ $request->status_badge_class }}">
            {{ $request->status_label }}
        </span>
    </h1>
@stop

@section('content')
    @include('admin.partials.session-messages')

    <div class="row">
        {{-- --------------------------------- COLUMNA DE DETALLES --------------------------------- --}}
        <div class="col-md-5">
            <div class="card card-info card-outline">
                <div class="card-header">
                    <h3 class="card-title">Información General</h3>
                </div>
                <div class="card-body">
                    <p><strong>Solicitante:</strong> {{ $request->requester->name ?? 'N/A' }}</p>
                    
                    {{-- 🔑 PROTECCIÓN DE FECHAS NULAS --}}
                    <p><strong>Fecha Solicitud:</strong> {{ optional($request->requested_at)->format('d/m/Y h:i A') }}</p>
                    
                    <p><strong>Ubicación/Área Destino:</strong> {{ $request->destination_area ?? 'N/A' }}</p>
                    
                    @if($request->reference)
                        <p><strong>Referencia:</strong> {{ $request->reference }}</p>
                    @endif

                    <hr>
                    <h4>Justificación</h4>
                    <p>{{ $request->justification }}</p>

                    @if ($request->status !== 'Pending')
                        <hr>
                        <h4>Decisión Final</h4>
                        <p><strong>Procesado por:</strong> {{ $request->approver->name ?? 'Sistema' }}</p>
                        <p><strong>Fecha Procesado:</strong> {{ optional($request->processed_at)->format('d/m/Y h:i A') }}</p>
                        
                        {{-- 🔑 USO DEL ACCESOR DE ESTADO --}}
                        <p><strong>Resolución:</strong> 
                            <span class="text-{{ $request->status_badge_class }} font-weight-bold">
                                {{ strtoupper($request->status_label) }}
                            </span>
                        </p>

                        @if ($request->rejection_reason)
                            <div class="alert alert-danger">
                                <strong>Motivo de Rechazo:</strong> {{ $request->rejection_reason }}
                            </div>
                        @endif
                    @endif
                </div>
            </div>

            {{-- --------------------------------- BOTONES DE ACCIÓN (SOLO PENDIENTE) --------------------------------- --}}
            @if ($request->status === 'Pending')
                @can('solicitudes_aprobar')
                    <div class="card card-primary">
                        <div class="card-header">
                            <h3 class="card-title">Acción de Aprobación</h3>
                        </div>
                        <div class="card-body">
                            <p class="text-danger">¡Atención! La aprobación de esta solicitud ajustará el stock de los productos.</p>
                            
                            {{-- BOTÓN APROBAR CON MODAL --}}
                            <button type="button" class="btn btn-success btn-lg btn-block mb-3" onclick="confirmAction({
                                title: 'Aprobar Solicitud',
                                message: '¿Está seguro de APROBAR esta solicitud de inventario?',
                                alert: 'Se reducirá el stock de los productos solicitados. Esta acción no se puede deshacer.',
                                confirmBtnClass: 'btn-success',
                                onConfirm: function() {
                                    var form = document.createElement('form');
                                    form.method = 'POST';
                                    form.action = '{{ route('admin.requests.process', ['request' => $request->id]) }}';
                                    var csrfToken = document.querySelector('meta[name=&quot;csrf-token&quot;]').getAttribute('content');
                                    form.innerHTML = '<input type=&quot;hidden&quot; name=&quot;_token&quot; value=&quot;' + csrfToken + '&quot;><input type=&quot;hidden&quot; name=&quot;action&quot; value=&quot;approve&quot;>';
                                    document.body.appendChild(form);
                                    form.submit();
                                }
                            })">
                                <i class="fas fa-check-circle"></i> Aprobar Solicitud
                            </button>
                            
                            {{-- BOTÓN PARA ACTIVAR EL RECHAZO (MODAL) --}}
                            <button type="button" class="btn btn-danger btn-lg btn-block" data-toggle="modal" data-target="#rejectModal">
                                <i class="fas fa-times-circle"></i> Rechazar
                            </button>
                        </div>
                    </div>
                @else
                    <div class="alert alert-warning">
                        Esta solicitud está pendiente de aprobación. Su rol no tiene permisos para tomar decisiones.
                    </div>
                @endcan
            @endif
        </div>
        
        {{-- --------------------------------- COLUMNA DE ÍTEMS --------------------------------- --}}
        <div class="col-md-7">
            <div class="card card-secondary card-outline">
                <div class="card-header">
                    <h3 class="card-title">Ítems Solicitados</h3>
                </div>
                <div class="card-body p-0">
                    <table class="table table-sm table-striped">
                        <thead>
                            <tr>
                                <th>Producto / Kit</th>
                                <th style="width: 15%">Solicitado</th>
                                <th style="width: 20%">Stock Actual</th>
                                <th style="width: 15%">Costo Unit.</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($request->items as $item)
                                {{-- Lógica para determinar estado de stock y nombres --}}
                                @php
                                    $product = $item->product;
                                    $isKit = $product && $product->type === 'composite_kit';
                                    $itemName = $product ? $product->name : 'Producto Eliminado';
                                    $itemCode = $product ? $product->code : 'N/A';
                                    $unitAbbr = ($product && $product->unit) ? $product->unit->abbreviation : 'unid';
                                    
                                    // Verificación rápida de stock
                                    $stockOk = true;
                                    if ($product) {
                                        $stockOk = $product->stock >= $item->quantity_requested;
                                    }
                                @endphp

                                <tr class="{{ !$stockOk ? 'table-danger' : '' }}">
                                    <td>
                                        @if($isKit)
                                            <i class="fas fa-cubes text-info"></i> 
                                        @else
                                            <i class="fas fa-cube text-primary"></i>
                                        @endif
                                        <strong>{{ $itemName }}</strong>
                                        <small class="d-block text-muted">{{ $itemCode }}</small>
                                        
                                        @if($isKit && $product)
                                            <small class="text-muted">Contiene: {{ $product->components->count() }} componentes</small>
                                        @endif
                                    </td>
                                    <td class="text-center font-weight-bold">
                                        {{ $item->quantity_requested }} {{ $unitAbbr }}
                                    </td>
                                    <td>
                                        @if($stockOk)
                                            <span class="badge badge-success">{{ $product->stock ?? 0 }} (OK)</span>
                                        @else
                                            <span class="badge badge-danger">{{ $product->stock ?? 0 }} (Falta)</span>
                                            
                                            {{-- Mostrar opción de descomposición de Kits --}}
                                            @if($request->status === 'Pending' && isset($decomposableKits[$product->id]) && count($decomposableKits[$product->id]) > 0)
                                                <div class="mt-1">
                                                    <button type="button" class="btn btn-xs btn-warning font-weight-bold btn-decompose-kit" 
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
                                    <td class="text-right">${{ number_format($item->unit_price_at_request, 2) }}</td>
                                </tr>

                                {{-- DESGLOSE DE COMPONENTES SI ES UN KIT COMPUESTO --}}
                                @if($isKit && $product && $product->components->count())
                                    <tr>
                                        <td colspan="4" class="bg-light p-0">
                                            <div class="px-4 py-2">
                                                <strong class="text-xs text-muted text-uppercase">Componentes del Kit:</strong>
                                                <ul class="list-unstyled text-sm mt-1 mb-0">
                                                    @foreach($product->components as $comp)
                                                        @php 
                                                            $reqQty = $comp->pivot->quantity * $item->quantity_requested;
                                                            $compStockOk = $comp->stock >= $reqQty;
                                                        @endphp
                                                        <li class="d-flex justify-content-between border-bottom pb-1 mb-1 {{ $compStockOk ? '' : 'text-danger font-weight-bold' }}">
                                                            <span>
                                                                <i class="fas fa-angle-right text-muted mr-1"></i> {{ $comp->name }}
                                                            </span>
                                                            <span>
                                                                Req: {{ $reqQty }} | Stock: {{ $comp->stock }}
                                                                @if(!$compStockOk) <i class="fas fa-exclamation-circle"></i> @endif
                                                            </span>
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            </div>
                                        </td>
                                    </tr>
                                @endif

                            @empty
                                <tr>
                                    <td colspan="4" class="text-center py-3">No hay ítems registrados para esta solicitud.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="card-footer">
                    <a href="{{ route('admin.requests.index') }}" class="btn btn-default">Volver al Listado</a>
                </div>
            </div>
        </div>
    </div>
    
    {{-- --------------------------------- MODAL DE RECHAZO --------------------------------- --}}
    @if ($request->status === 'Pending' && Gate::allows('solicitudes_aprobar'))
        <div class="modal fade" id="rejectModal" tabindex="-1" role="dialog" aria-labelledby="rejectModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <form action="{{ route('admin.requests.process', ['request' => $request->id]) }}" method="POST">
                        @csrf
                        <input type="hidden" name="action" value="reject">
                        <div class="modal-header bg-danger">
                            <h5 class="modal-title" id="rejectModalLabel">Rechazar Solicitud #REQ-{{ $request->id }}</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <div class="form-group">
                                <label for="rejection_reason">Motivo del Rechazo</label>
                                <textarea name="rejection_reason" id="rejection_reason" class="form-control" rows="3" required placeholder="Explique brevemente por qué se rechaza la solicitud."></textarea>
                            </div>
                        </div>
                        <div class="modal-footer justify-content-between">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn btn-danger">Confirmar Rechazo</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    {{-- --------------------------------- MODAL DE DESCOMPOSICIÓN DE KITS --------------------------------- --}}
    @if ($request->status === 'Pending' && Gate::allows('solicitudes_aprobar'))
        <div class="modal fade" id="decomposeModal" tabindex="-1" role="dialog" aria-labelledby="decomposeModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header bg-warning text-dark">
                        <h5 class="modal-title font-weight-bold" id="decomposeModalLabel">
                            <i class="fas fa-tools"></i> Descomponer Kit para suplir producto
                        </h5>
                        <button type="button" class="close text-dark" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> <span id="decompose_helper_text"></span>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="decompose_kit_select">Seleccionar Kit Compuesto</label>
                                    <select id="decompose_kit_select" class="form-control"></select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="decompose_batch_select">Lote del Kit a Descomponer</label>
                                    <select id="decompose_batch_select" class="form-control"></select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="decompose_quantity">Cantidad de Kits a Descomponer</label>
                                    <input type="number" id="decompose_quantity" class="form-control" min="1" value="1">
                                    <small class="form-text text-muted" id="decompose_qty_helper"></small>
                                </div>
                            </div>
                        </div>

                        <div id="decompose_components_card" class="card card-outline card-secondary mt-3">
                            <div class="card-header">
                                <h3 class="card-title font-weight-bold">Componentes del Kit e Ingreso de Seriales</h3>
                            </div>
                            <div class="card-body" id="decompose_components_container">
                                <!-- Se renderiza dinámicamente con JavaScript -->
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer justify-content-between">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">
                            <i class="fas fa-times"></i> Cancelar
                        </button>
                        <button type="button" id="btn-confirm-decompose" class="btn btn-warning font-weight-bold">
                            <i class="fas fa-tools"></i> Confirmar Descomposición
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @include('admin.partials.delete-confirm')
    @include('admin.partials.confirm-action')
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

        // Reset errors and button state
        $('#btn-confirm-decompose').prop('disabled', false).html('<i class="fas fa-tools"></i> Confirmar Descomposición');

        // Set labels
        $('#decomposeModalLabel').html('<i class="fas fa-tools"></i> Descomponer Kit para suplir <strong>' + productName + '</strong>');
        $('#decompose_helper_text').text('Faltan ' + qtyDeficient + ' unidades de "' + productName + '". Puede descomponer uno de los siguientes kits para obtener las unidades necesarias.');

        // Populate kit select
        let $kitSelect = $('#decompose_kit_select');
        $kitSelect.empty();
        kitsData.forEach(function(item, index) {
            $kitSelect.append($('<option>', {
                value: index,
                text: item.kit.name + ' (Contiene ' + item.quantity_in_kit + ' unidades de este componente)'
            }));
        });

        // Trigger change event to populate batches and components
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
            if (batch.invoice_number) {
                label += ' | Fac: ' + batch.invoice_number;
            }
            $batchSelect.append($('<option>', {
                value: batch.id,
                text: label
            }).data('quantity', batch.quantity));
        });

        // Calculate suggested kits to decompose
        let qtyInKit = kitItem.quantity_in_kit;
        let kitsNeeded = Math.ceil(targetQtyDeficient / qtyInKit);
        
        let selectedBatchQty = $batchSelect.find('option:selected').data('quantity') || 0;
        let defaultQty = Math.min(kitsNeeded, selectedBatchQty) || 1;

        $('#decompose_quantity').val(defaultQty);
        $('#decompose_quantity').attr('max', selectedBatchQty);

        // When batch changes, update quantity max and re-render
        $batchSelect.off('change').on('change', function() {
            let newMax = $(this).find('option:selected').data('quantity') || 0;
            $('#decompose_quantity').attr('max', newMax);
            if (parseInt($('#decompose_quantity').val()) > newMax) {
                $('#decompose_quantity').val(newMax);
            }
            renderComponents();
        });

        // When quantity changes, re-render
        $('#decompose_quantity').off('input change').on('input change', function() {
            let val = parseInt($(this).val()) || 1;
            let max = parseInt($(this).attr('max')) || 0;
            if (val > max) {
                $(this).val(max);
            }
            if (val < 1) {
                $(this).val(1);
            }
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
        html += '<thead><tr><th>Componente</th><th style="width: 25%">Cantidad a Generar</th><th>Números de Serie</th></tr></thead>';
        html += '<tbody>';

        components.forEach(function(comp) {
            let qtyInKit = comp.pivot.quantity;
            let totalToGenerate = qtyInKit * decompQty;
            let requiresSerial = comp.requires_serial === 1 || comp.requires_serial === true || comp.requires_serial === '1';

            html += '<tr>';
            html += '<td>';
            html += '<strong>' + comp.name + '</strong>';
            html += '<small class="d-block text-muted">Cód: ' + comp.code + '</small>';
            html += '</td>';
            html += '<td class="font-weight-bold text-center">' + totalToGenerate + '</td>';
            html += '<td>';

            if (requiresSerial) {
                html += '<div class="serial-inputs-wrapper" data-product-id="' + comp.id + '" data-quantity="' + totalToGenerate + '" data-name="' + comp.name + '">';
                html += '<small class="text-danger d-block mb-1"><i class="fas fa-exclamation-triangle"></i> Requiere ' + totalToGenerate + ' números de serie únicos:</small>';
                for (let i = 0; i < totalToGenerate; i++) {
                    html += '<input type="text" class="form-control form-control-sm serial-input mb-1" ';
                    html += 'data-product-id="' + comp.id + '" ';
                    html += 'placeholder="Serial #' + (i + 1) + '" required>';
                }
                html += '</div>';
            } else {
                html += '<span class="badge badge-secondary">No requiere seriales (se registrarán como NULL)</span>';
            }

            html += '</td>';
            html += '</tr>';
        });

        html += '</tbody></table></div>';
        $container.append(html);
    }

    $(document).ready(function() {
        $('.btn-decompose-kit').on('click', function() {
            let productId = $(this).data('product-id');
            let productName = $(this).data('product-name');
            let qtyRequested = $(this).data('qty-requested');
            let qtyDeficient = $(this).data('qty-deficient');
            let kitsData = $(this).data('kits');

            openDecomposeModal(productId, productName, qtyRequested, qtyDeficient, kitsData);
        });

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

            // Validar seriales
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
                    if (val) {
                        compSerials.push(val);
                    }
                });

                if (compSerials.length < expectedQty) {
                    hasError = true;
                    errorMessage = 'Debe ingresar todos los números de serie para "' + compName + '" (esperados: ' + expectedQty + ', ingresados: ' + compSerials.length + ').';
                    return false; // break loop
                }

                // Validar duplicados locales
                let uniqueSerials = [...new Set(compSerials)];
                if (uniqueSerials.length < compSerials.length) {
                    hasError = true;
                    errorMessage = 'Los números de serie ingresados para "' + compName + '" no pueden contener duplicados.';
                    return false; // break loop
                }

                serials[productId] = compSerials;
            });

            if (hasError) {
                Swal.fire({ icon: 'error', title: 'Error de Validación', text: errorMessage });
                return;
            }

            // Confirmar acción
            Swal.fire({
                title: '¿Confirmar Descomposición?',
                text: 'Se descompondrán ' + quantity + ' unidades del kit "' + kitItem.kit.name + '". Esto actualizará el stock de sus componentes.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Sí, descomponer',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Disable button and show spinner
                    $('#btn-confirm-decompose').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Procesando...');

                    $.ajax({
                        url: '/admin/products/' + kitId + '/decompose',
                        method: 'POST',
                        data: {
                            _token: $('meta[name="csrf-token"]').attr('content'),
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
    });
</script>
@stop