@extends('adminlte::page')

@section('title', 'RFQ ' . $rfq->code)

@section('plugins.Datatables', true)
@section('plugins.Sweetalert2', true)

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1><i class="fas fa-file-invoice text-info"></i> Detalle de RFQ {{ $rfq->code }}</h1>
        <div>
            <a href="{{ route('admin.rfq.pdf', $rfq) }}" class="btn btn-sm btn-outline-secondary" target="_blank">
                <i class="fas fa-file-pdf mr-1"></i> PDF Genérico
            </a>
            @if($rfq->isEditable())
                @can('rfq_editar')
                    <a href="{{ route('admin.rfq.edit', $rfq) }}" class="btn btn-sm btn-warning">
                        <i class="fas fa-edit mr-1"></i> Editar RFQ
                    </a>
                @endcan
            @endif
        </div>
    </div>
@stop

@section('content')
    <div class="container-fluid">
        @include('admin.partials.session-messages')

        <div class="row">
            <!-- Columna Principal (Izquierda - 70%) -->
            <div class="col-lg-9 col-md-12">

                <!-- Card de Comparativa de Proveedores (Cotizaciones) -->
                <div class="card shadow-sm border-0 mb-4" style="border-radius: 12px;">
                    <div class="card-header bg-gradient-white border-bottom py-3">
                        <h3 class="card-title text-dark font-weight-bold mb-0">
                            <i class="fas fa-balance-scale text-success mr-1"></i> Comparativa de Ofertas de Proveedores
                        </h3>
                    </div>
                    <div class="card-body">
                        <!-- Buscador de proveedor para inyectar columna -->
                        <div class="row mb-4 align-items-end">
                            <div class="col-md-7 col-12">
                                <label for="supplierSelect" class="text-xs text-muted mb-1">Seleccionar Proveedor para Comparar</label>
                                <select id="supplierSelect" class="form-control select2" style="width: 100%;">
                                    <option value="">Seleccione un proveedor...</option>
                                    @foreach($suppliers as $supplier)
                                        <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-5 col-12 mt-2 mt-md-0">
                                <button type="button" class="btn btn-success btn-block shadow-sm" id="addSupplierColBtn">
                                    <i class="fas fa-plus-circle mr-1"></i> Agregar Proveedor a Comparativa
                                </button>
                            </div>
                        </div>

                        <!-- Tabla de Comparación de Ofertas -->
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover align-middle mb-0" id="comparisonTable">
                                <thead>
                                    <tr id="comparisonHeader">
                                        <th style="width: 35%; min-width: 250px;">Detalle del Ítem</th>
                                        <th class="text-center text-muted font-weight-normal py-4" id="emptyStateHeader" style="width: 65%; min-width: 200px;">
                                            <i class="fas fa-info-circle mr-1 text-info"></i> No hay ofertas registradas. Use el selector de arriba para agregar columnas de proveedores.
                                        </th>
                                    </tr>
                                </thead>
                                <tbody id="comparisonBody">
                                    @foreach($rfq->items as $index => $item)
                                        @php
                                            $productId = $item->item_type === 'kit' ? ($item->kit_id ?? $item->product_id) : $item->product_id;
                                        @endphp
                                        <tr class="rfq-item-row" data-item-id="{{ $item->id }}" data-product-id="{{ $productId }}" data-quantity="{{ $item->quantity }}">
                                            <td>
                                                @if($item->item_type === 'kit')
                                                    <strong>{{ $item->kit->name ?? 'Kit Desconocido' }}</strong>
                                                    <span class="badge badge-info ml-1">Kit</span>
                                                @else
                                                    <strong>{{ $item->product->name ?? 'Producto Desconocido' }}</strong>
                                                    <br><small class="text-muted">{{ $item->product->code ?? 'S/C' }}</small>
                                                @endif
                                                <div class="text-xs text-muted mt-1">
                                                    Cantidad: <span class="font-weight-bold text-dark">{{ $item->quantity }}</span> 
                                                    {{ $item->item_type === 'kit' ? 'kit' : ($item->product->unit->abbreviation ?? 'und') }}
                                                </div>
                                            </td>
                                            <td class="text-center text-muted empty-state-cell py-3" id="emptyStateCell-{{ $index }}">
                                                -
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr id="comparisonFooter">
                                        <th class="bg-light">Total General</th>
                                        <th class="text-center text-muted bg-light py-2" id="emptyStateFooter">-</th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Card de Productos Solicitados en RFQ (Listado Técnico) -->
                <div class="card shadow-sm border-0 mb-4" style="border-radius: 12px;">
                    <div class="card-header bg-gradient-white border-bottom py-3">
                        <h3 class="card-title text-dark font-weight-bold mb-0">
                            <i class="fas fa-boxes text-info mr-1"></i> Ítems Requeridos (Catálogo)
                        </h3>
                    </div>
                    <div class="card-body p-0">
                        <table id="productsTable" class="table table-hover align-middle mb-0">
                            <thead class="bg-light text-muted text-uppercase text-xs font-weight-bold">
                                <tr>
                                    <th style="width: 5%">#</th>
                                    <th style="width: 15%">Código</th>
                                    <th style="width: 40%">Producto / Kit</th>
                                    <th style="width: 15%">Categoría</th>
                                    <th style="width: 10%">Cantidad</th>
                                    <th style="width: 15%">Notas</th>
                                </tr>
                            </thead>
                            <tbody>
                                 @foreach($rfq->items as $index => $item)
                                     <tr>
                                         <td>{{ $index + 1 }}</td>
                                         @if($item->item_type === 'kit')
                                             <td>S/C</td>
                                             <td>
                                                 @if($item->kit)
                                                     <a href="{{ route('admin.kits.show', $item->kit_id) }}" class="font-weight-bold text-dark">
                                                         {{ $item->kit->name }}
                                                     </a>
                                                 @else
                                                     <span class="font-weight-bold text-dark">{{ $item->product->name ?? 'Kit' }}</span>
                                                 @endif
                                                 <span class="badge badge-info ml-1">Kit</span>
                                             </td>
                                             <td>Kits de Productos</td>
                                             <td>
                                                 <span class="badge badge-primary py-1 px-2">{{ $item->quantity }}</span> kit
                                             </td>
                                         @else
                                             <td>{{ $item->product->code ?? 'N/A' }}</td>
                                             <td>
                                                 @if($item->product)
                                                     <a href="{{ route('admin.products.show', $item->product) }}" class="font-weight-bold text-dark">
                                                         {{ $item->product->name }}
                                                     </a>
                                                     @if($item->product->is_kit)
                                                         <span class="badge badge-info ml-1">Kit</span>
                                                     @endif
                                                 @else
                                                     <span class="text-danger font-weight-bold">Producto Desconocido</span>
                                                 @endif
                                             </td>
                                             <td>{{ $item->product->category->name ?? 'N/A' }}</td>
                                             <td>
                                                 <span class="badge badge-primary py-1 px-2">{{ $item->quantity }}</span>
                                                 {{ $item->product->unit->abbreviation ?? 'und' }}
                                             </td>
                                         @endif
                                         <td class="text-xs text-muted">{{ $item->notes ?? '-' }}</td>
                                     </tr>
                                 @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Columna Lateral (Derecha - 30%) -->
            <div class="col-lg-3 col-md-12">
                
                <!-- Card de Datos de la RFQ -->
                <div class="card shadow-sm border-0 mb-4" style="border-radius: 12px; overflow: hidden;">
                    <div class="bg-gradient-info py-3 px-3">
                        <h3 class="card-title h6 text-white font-weight-bold mb-0">
                            <i class="fas fa-file-invoice-dollar mr-1"></i> Resumen de RFQ
                        </h3>
                    </div>
                    <div class="card-body p-3">
                        <table class="table table-sm table-borderless text-sm mb-3">
                            <tr>
                                <th class="pl-0 text-muted" style="width: 40%">Código:</th>
                                <td class="font-weight-bold">{{ $rfq->code }}</td>
                            </tr>
                            <tr>
                                <th class="pl-0 text-muted">Estatus:</th>
                                <td>{!! $rfq->status_badge !!}</td>
                            </tr>
                            @if($rfq->purchaseOrder)
                                <tr>
                                    <th class="pl-0 text-muted"><i class="fas fa-shopping-cart text-success"></i> OC Asociada:</th>
                                    <td>
                                        <a href="{{ route('admin.purchaseOrders.show', $rfq->purchaseOrder) }}" class="font-weight-bold text-success">
                                            {{ $rfq->purchaseOrder->code }}
                                        </a>
                                    </td>
                                </tr>
                            @endif
                            <tr>
                                <th class="pl-0 text-muted">Creador:</th>
                                <td>{{ $rfq->creator->name ?? 'Sistema' }}</td>
                            </tr>
                            <tr>
                                <th class="pl-0 text-muted">Fecha Creación:</th>
                                <td>{{ $rfq->created_at->format('d/m/Y') }}</td>
                            </tr>
                            <tr>
                                <th class="pl-0 text-muted">Límite Resp.:</th>
                                <td>{{ $rfq->date_required?->format('d/m/Y') ?? 'Sin fecha' }}</td>
                            </tr>
                            <tr>
                                <th class="pl-0 text-muted">Límite Entrega:</th>
                                <td>{{ $rfq->delivery_deadline?->format('d/m/Y') ?? 'Sin fecha' }}</td>
                            </tr>
                        </table>

                        @if($rfq->description)
                            <hr class="my-2">
                            <div class="text-xs">
                                <strong class="text-muted d-block">Descripción / Instrucciones:</strong>
                                <p class="text-dark mb-0 font-italic">{{ $rfq->description }}</p>
                            </div>
                        @endif

                        @if($rfq->notes)
                            <hr class="my-2">
                            <div class="text-xs">
                                <strong class="text-muted d-block">Notas Internas:</strong>
                                <p class="text-dark mb-0 font-italic">{{ $rfq->notes }}</p>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Card de Acciones de Estatus -->
                @can('rfq_enviar')
                    @if(in_array($rfq->status, ['draft', 'sent']))
                        <div class="card shadow-sm border-0 mb-4" style="border-radius: 12px;">
                            <div class="card-header bg-light border-bottom py-3">
                                <h3 class="card-title text-dark font-weight-bold mb-0 text-sm">
                                    <i class="fas fa-traffic-light text-warning mr-1"></i> Acciones de Flujo de Trabajo
                                </h3>
                            </div>
                            <div class="card-body p-3">
                                @if($rfq->status === 'draft')
                                    <p class="text-xs text-muted mb-3">Marque la solicitud como enviada para congelarla y poder cargar ofertas.</p>
                                    <button type="button" class="btn btn-success btn-block shadow-sm font-weight-bold mb-2" onclick="confirmAction({
                                        title: 'Enviar RFQ',
                                        message: '¿Está seguro de marcar esta Solicitud de Cotización como ENVIADA?',
                                        alert: 'Una vez enviada, no podrá modificarse directamente pero podrá cargar ofertas.',
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
                                        <i class="fas fa-paper-plane mr-1"></i> Marcar como Enviada
                                    </button>
                                @elseif($rfq->status === 'sent')
                                    <p class="text-xs text-muted mb-3 font-italic">La solicitud ya ha sido enviada a los proveedores. Puede convertir a Orden de Compra.</p>
                                    <button type="button" class="btn btn-success btn-block shadow-sm font-weight-bold mb-2" onclick="confirmAction({
                                        title: 'Cerrar RFQ',
                                        message: '¿Está seguro de CERRAR esta Solicitud de Cotización?',
                                        alert: 'Esto finalizará formalmente el proceso de cotización.',
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
                                        <i class="fas fa-check-circle mr-1"></i> Cerrar Proceso RFQ
                                    </button>
                                @endif

                                <button type="button" class="btn btn-outline-danger btn-block btn-sm shadow-sm" onclick="confirmAction({
                                    title: 'Cancelar RFQ',
                                    message: '¿Está seguro de CANCELAR esta Solicitud de Cotización?',
                                    alert: 'Esta acción anulará el proceso por completo y no se puede deshacer.',
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
                                    <i class="fas fa-ban mr-1"></i> Cancelar RFQ
                                </button>
                            </div>
                        </div>
                    @endif
                @endcan

                <a href="{{ route('admin.rfq.index') }}" class="btn btn-outline-secondary btn-block shadow-sm">
                    <i class="fas fa-arrow-left mr-1"></i> Volver al Listado
                </a>
            </div>
        </div>
    </div>

    @include('admin.partials.delete-confirm')
    @include('admin.partials.confirm-action')
@stop

@section('css')
    <style>
        .bg-gradient-white {
            background: linear-gradient(180deg, #ffffff 0%, #f9fafb 100%) !important;
        }
        .truncate {
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .form-control-xs {
            height: calc(1.5em + 0.25rem + 2px);
            padding: 0.125rem 0.25rem;
            font-size: 0.75rem;
            line-height: 1.5;
            border-radius: 0.2rem;
        }
        .text-xs {
            font-size: 0.75rem !important;
        }
        .supplier-col {
            border-left: 2px solid #e9ecef !important;
        }
    </style>
@endsection

@section('js')
    <script>
        const rfqId = "{{ $rfq->id }}";
        const existingOffers = @json($rfq->supplierOffers->load('supplier', 'items'));

        $(document).ready(function() {
            // Inicializar Select2 en select de proveedores
            $('#supplierSelect').select2({
                theme: 'bootstrap4',
                width: '100%',
                placeholder: 'Seleccione un proveedor...'
            });

            // Inicializar tabla técnica básica
            $('#productsTable').DataTable({
                "pageLength": 10,
                "lengthMenu": [[10, 25, 50], [10, 25, 50]],
                "searching": false,
                "info": false,
                "order": [[0, 'asc']],
                "language": {
                    "emptyTable": "No hay productos en esta solicitud",
                    "paginate": {
                        "next": "Sig.",
                        "previous": "Ant."
                    }
                }
            });

            // Lógica de símbolos de moneda
            function getCurrencySymbol(currency) {
                if (currency === 'USD') return '$';
                if (currency === 'EUR') return '€';
                if (currency === 'Bs') return 'Bs.';
                return '$';
            }

            // Recalculación en caliente
            function recalculateColumn(supplierId) {
                const currency = $(`.col-currency-select[data-supplier-id="${supplierId}"]`).val();
                const symbol = getCurrencySymbol(currency);
                
                // Actualizar símbolos
                $(`.supplier-col-${supplierId} .currency-symbol`).text(symbol);
                
                let grandTotal = 0;
                
                // Calcular fila por fila
                $(`.supplier-col-${supplierId} .item-price-input`).each(function() {
                    const price = parseFloat($(this).val()) || 0;
                    const quantity = parseFloat($(this).data('quantity')) || 1;
                    const total = price * quantity;
                    grandTotal += total;
                    
                    $(this).closest('td').find('.row-total-label').text(total.toFixed(2));
                });
                
                // Actualizar total general
                $(`.supplier-col-${supplierId} .supplier-grand-total`).text(grandTotal.toFixed(2));
            }

            // Alternar estado vacío de la tabla
            function updateEmptyState() {
                const cols = $('#comparisonHeader th.supplier-col').length;
                if (cols > 0) {
                    $('#emptyStateHeader').hide();
                    $('.empty-state-cell').hide();
                    $('#emptyStateFooter').hide();
                } else {
                    $('#emptyStateHeader').show();
                    $('.empty-state-cell').show();
                    $('#emptyStateFooter').show();
                }
            }

            // Inyectar columna de proveedor
            function addSupplierColumn(supplierId, supplierName, offerId = null) {
                // Inyectar cabecera de columna
                const headerHtml = `
                    <th class="supplier-col supplier-col-${supplierId} text-center bg-light" data-supplier-id="${supplierId}" data-offer-id="${offerId || ''}" style="min-width: 170px;">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-xs font-weight-bold text-dark truncate" style="max-width: 90px;" title="${supplierName}">${supplierName}</span>
                            <div>
                                <button type="button" class="btn btn-xs btn-link text-primary save-offer-btn p-0 mr-1" data-supplier-id="${supplierId}" title="Guardar Oferta">
                                    <i class="fas fa-save"></i>
                                </button>
                                <button type="button" class="btn btn-xs btn-link text-danger remove-supplier-col p-0" data-supplier-id="${supplierId}">
                                    <i class="fas fa-times-circle"></i>
                                </button>
                            </div>
                        </div>
                        <div class="form-row mb-2">
                            <div class="col-6">
                                <select class="form-control form-control-xs col-currency-select" data-supplier-id="${supplierId}">
                                    <option value="USD">💵 USD</option>
                                    <option value="EUR">💶 EUR</option>
                                    <option value="Bs">🇻🇪 VES</option>
                                </select>
                            </div>
                            <div class="col-6 d-flex align-items-center justify-content-center">
                                <div class="custom-control custom-switch">
                                    <input type="checkbox" class="custom-control-input col-iva-switch" id="iva_switch_${supplierId}" data-supplier-id="${supplierId}">
                                    <label class="custom-control-label text-xs" for="iva_switch_${supplierId}" style="font-size: 9px; cursor: pointer;">Exento</label>
                                </div>
                            </div>
                        </div>
                        <button type="button" class="btn btn-xs btn-success btn-block font-weight-bold convert-po-btn" data-supplier-id="${supplierId}">
                            <i class="fas fa-shopping-cart text-xs mr-1"></i> Convertir a PO
                        </button>
                    </th>
                `;
                $('#comparisonHeader').append(headerHtml);

                // Inyectar celdas por fila
                $('#comparisonBody tr.rfq-item-row').each(function() {
                    const itemId = $(this).data('item-id');
                    const productId = $(this).data('product-id');
                    const quantity = parseFloat($(this).data('quantity')) || 1;
                    const cellHtml = `
                        <td class="supplier-col supplier-col-${supplierId} text-center align-middle" data-supplier-id="${supplierId}">
                            <div class="input-group input-group-sm mb-1">
                                <div class="input-group-prepend">
                                    <span class="input-group-text currency-symbol text-xs bg-light border-right-0 px-1" style="font-size: 10px;">$</span>
                                </div>
                                <input type="number" step="0.01" min="0" class="form-control form-control-sm border-left-0 item-price-input text-right font-weight-bold" data-item-id="${itemId}" data-product-id="${productId}" data-quantity="${quantity}" data-supplier-id="${supplierId}" value="0.00" style="padding: 0.25rem 0.4rem;">
                            </div>
                            <div class="text-right text-xs text-muted font-italic" style="font-size: 10px;">
                                Subt: <span class="currency-symbol">$</span><span class="row-total-label">0.00</span>
                            </div>
                        </td>
                    `;
                    $(this).append(cellHtml);
                });

                // Inyectar celda de pie de página
                const footerHtml = `
                    <th class="supplier-col supplier-col-${supplierId} text-center bg-light align-middle" data-supplier-id="${supplierId}">
                        <span class="text-xs text-muted font-weight-normal d-block" style="font-size: 10px;">Total Oferta</span>
                        <div class="text-success font-weight-bold mb-0" style="font-size: 14px;">
                            <span class="currency-symbol">$</span><span class="supplier-grand-total">0.00</span>
                        </div>
                    </th>
                `;
                $('#comparisonFooter').append(footerHtml);

                updateEmptyState();
                recalculateColumn(supplierId);
            }

            // Cargar Ofertas Existentes de la Base de Datos al abrir
            if (existingOffers && existingOffers.length > 0) {
                existingOffers.forEach(function(offer) {
                    addSupplierColumn(offer.supplier_id, offer.supplier.name, offer.id);
                    
                    if (offer.items && offer.items.length > 0) {
                        const firstItem = offer.items[0];
                        $(`.col-currency-select[data-supplier-id="${offer.supplier_id}"]`).val(firstItem.currency);
                        
                        const isExempt = firstItem.tax_status === 'exento';
                        $(`.col-iva-switch[data-supplier-id="${offer.supplier_id}"]`).prop('checked', isExempt);
                        
                        offer.items.forEach(function(item) {
                            const input = $(`.item-price-input[data-supplier-id="${offer.supplier_id}"][data-product-id="${item.product_id}"]`);
                            if (input.length > 0) {
                                input.val(item.unit_price);
                            }
                        });
                    }
                    recalculateColumn(offer.supplier_id);
                });
            }

            // Agregar Columna de Proveedor (Nueva)
            $('#addSupplierColBtn').on('click', function() {
                const select = $('#supplierSelect');
                const supplierId = select.val();
                const supplierName = select.find('option:selected').text();
                
                if (!supplierId) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Seleccione un Proveedor',
                        text: 'Debe seleccionar un proveedor de la lista antes de agregarlo.'
                    });
                    return;
                }
                
                // Validar duplicado
                if ($(`#comparisonHeader th.supplier-col-${supplierId}`).length > 0) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Proveedor ya agregado',
                        text: 'Este proveedor ya cuenta con una columna en la comparativa.'
                    });
                    return;
                }
                
                addSupplierColumn(supplierId, supplierName);
                select.val('').trigger('change');
            });

            // Guardar Oferta de Proveedor vía AJAX (POST)
            function saveOfferAjax(supplierId, callback = null) {
                const items = [];
                let anyPrice = false;
                
                $(`.supplier-col-${supplierId} .item-price-input`).each(function() {
                    const productId = $(this).data('product-id');
                    const price = parseFloat($(this).val()) || 0;
                    if (price > 0) anyPrice = true;
                    
                    const itemCurrency = $(`.col-currency-select[data-supplier-id="${supplierId}"]`).val();
                    const itemTax = $(`.col-iva-switch[data-supplier-id="${supplierId}"]`).is(':checked') ? 'exento' : 'gravado';
                    
                    items.push({
                        product_id: productId,
                        unit_price: price,
                        currency: itemCurrency,
                        tax_status: itemTax
                    });
                });

                if (!anyPrice) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Precios en Cero',
                        text: 'Debe ingresar al menos un precio mayor a cero para guardar la oferta.'
                    });
                    return;
                }

                const payload = {
                    supplier_id: supplierId,
                    notes: '',
                    items: items
                };

                $.ajax({
                    url: `/admin/rfq/${rfqId}/supplier-offers`,
                    method: 'POST',
                    data: payload,
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        // Guardar el offer_id devuelto en el elemento de la cabecera
                        const header = $(`#comparisonHeader th.supplier-col-${supplierId}`);
                        header.attr('data-offer-id', response.offer_id);

                        if (callback) {
                            callback(response.offer_id);
                        } else {
                            Swal.fire({
                                icon: 'success',
                                title: 'Oferta Guardada',
                                text: response.message,
                                timer: 2000,
                                showConfirmButton: false
                            });
                        }
                    },
                    error: function(xhr) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error al Guardar',
                            text: xhr.responseJSON.message || 'Hubo un error inesperado al guardar la oferta en la base de datos.'
                        });
                    }
                });
            }

            // Click en botón Guardar
            $(document).on('click', '.save-offer-btn', function() {
                const supplierId = $(this).data('supplier-id');
                saveOfferAjax(supplierId);
            });

            // Eventos Delegados para inputs de precios y monedas
            $(document).on('input', '.item-price-input', function() {
                const supplierId = $(this).data('supplier-id');
                recalculateColumn(supplierId);
            });

            $(document).on('change', '.col-currency-select', function() {
                const supplierId = $(this).data('supplier-id');
                recalculateColumn(supplierId);
            });

            // Quitar Columna de Proveedor
            $(document).on('click', '.remove-supplier-col', function() {
                const supplierId = $(this).data('supplier-id');
                $(`.supplier-col-${supplierId}`).remove();
                updateEmptyState();
            });

            // Convertir Oferta a PO vía POST Seguro con CSRF Token
            $(document).on('click', '.convert-po-btn', function() {
                const supplierId = $(this).data('supplier-id');
                
                confirmAction({
                    title: 'Convertir a Orden de Compra',
                    message: '¿Desea convertir esta oferta en una Orden de Compra (PO)?',
                    alert: 'Esto guardará la oferta actual e iniciará el formulario de PO por POST de manera segura.',
                    confirmBtnClass: 'btn-success',
                    onConfirm: function() {
                        // Guardar la oferta primero para asegurar consistencia
                        saveOfferAjax(supplierId, function(offerId) {
                            // Crear y enviar formulario dinámico por POST con CSRF Token
                            const csrfToken = $('meta[name="csrf-token"]').attr('content');
                            const form = $(`
                                <form method="POST" action="/admin/rfq/${rfqId}/convert-to-po">
                                    <input type="hidden" name="_token" value="${csrfToken}">
                                    <input type="hidden" name="rfq_supplier_offer_id" value="${offerId}">
                                </form>
                            `);
                            $('body').append(form);
                            form.submit();
                        });
                    }
                });
            });
        });
    </script>
@endsection
