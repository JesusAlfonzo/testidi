@extends('adminlte::page')

@section('title', 'Crear Orden de Compra')

@section('plugins.Select2', true)
@section('plugins.Sweetalert2', true)

@section('css')
    <style>
        .card-custom {
            border-radius: 12px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);
            border: none;
            margin-bottom: 1.5rem;
        }

        .card-custom .card-header {
            background-color: #ffffff;
            border-bottom: 1px solid #f3f4f6;
            padding: 1rem 1.25rem;
            border-top-left-radius: 12px;
            border-top-right-radius: 12px;
        }

        .card-custom .card-title {
            font-size: 0.95rem;
            font-weight: 700;
            color: #1f2937;
        }

        .select2-container--default .select2-selection--single {
            height: 38px !important;
            padding-top: 4px;
            border: 1px solid #d1d5db !important;
            border-radius: 6px !important;
        }

        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 36px !important;
        }

        .form-control-custom {
            border-radius: 6px !important;
            border: 1px solid #d1d5db !important;
            height: 38px !important;
        }

        .btn-custom {
            border-radius: 6px !important;
            font-weight: 600;
            padding: 0.5rem 1.25rem;
        }

        /* Sticky sidebar styling */
        .sticky-sidebar {
            position: -webkit-sticky;
            position: sticky;
            top: 1rem;
        }

        .total-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.75rem 0;
            border-bottom: 1px dashed #e5e7eb;
            font-size: 0.875rem;
            color: #4b5563;
        }

        .total-row:last-child {
            border-bottom: none;
        }

        .total-row-grand {
            border-top: 1px solid #e5e7eb;
            padding-top: 1rem;
            margin-top: 0.5rem;
            font-size: 1.15rem;
            font-weight: 800;
            color: #111827;
        }

        .bs-equivalent-section {
            background-color: #f0fdf4;
            border-radius: 8px;
            border: 1px solid #bbf7d0;
            padding: 0.75rem;
            margin-top: 1rem;
        }

        .bs-equivalent-title {
            font-size: 0.75rem;
            font-weight: 700;
            color: #15803d;
            text-transform: uppercase;
            margin-bottom: 0.5rem;
            letter-spacing: 0.5px;
        }

        #itemsTable th {
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-weight: 700;
            color: #4b5563;
            background-color: #f9fafb;
            border-bottom: 1px solid #e5e7eb;
            padding: 10px;
        }

        #itemsTable td {
            padding: 10px;
            vertical-align: middle;
            border-bottom: 1px solid #f3f4f6;
        }

        .remove-item {
            width: 28px;
            height: 28px;
            padding: 0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 50% !important;
        }
    </style>
@stop

@section('content_header')
    <div class="d-flex justify-content-between align-items-center mb-2">
        <div>
            <h1 class="text-dark font-weight-bold" style="font-size: 1.75rem;">
                <i class="fas fa-shopping-cart mr-2 text-primary"></i> Nueva Orden de Compra
            </h1>
            <p class="text-muted mb-0">Registre una nueva orden de compra para ingresar productos al inventario.</p>
        </div>
        <a href="{{ route('admin.purchaseOrders.index') }}" class="btn btn-outline-secondary font-weight-bold btn-custom">
            <i class="fas fa-arrow-left mr-1"></i> Volver al listado
        </a>
    </div>
@stop

@section('content')
    @php
        $categories = \App\Models\Category::orderBy('name')->get();
        $units = \App\Models\Unit::orderBy('name')->get();
        $locations = \App\Models\Location::orderBy('name')->get();
        $brands = \App\Models\Brand::orderBy('name')->get();
    @endphp

    <form action="{{ route('admin.purchaseOrders.store') }}" method="POST" id="orderForm">
        @csrf

        <div class="row">
            <!-- COLUMNA PRINCIPAL (70%) -->
            <div class="col-lg-8">
                <!-- Tarjeta: Información General -->
                <div class="card card-custom">
                    <div class="card-header border-0 pb-0">
                        <h3 class="card-title text-primary"><i class="fas fa-info-circle mr-1"></i> Información General</h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <div class="form-group mb-0">
                                    <label for="code" class="text-xs font-weight-bold text-secondary mb-1">Código OC</label>
                                    <input type="text" name="code" class="form-control form-control-custom font-weight-bold" value="{{ $code }}" readonly style="background-color: #f3f4f6;">
                                </div>
                            </div>
                            <div class="col-md-5 mb-3">
                                <div class="form-group mb-0">
                                    <label for="supplier_id" class="text-xs font-weight-bold text-secondary mb-1">Proveedor (*)</label>
                                    <div class="input-group">
                                        <select name="supplier_id" id="supplier_id" class="form-control form-control-custom select2-ajax" data-placeholder="Buscar proveedor..." data-url="{{ route('admin.purchaseOrders.searchSuppliers') }}" required>
                                            <option value="">Seleccione un proveedor...</option>
                                            @foreach($suppliers as $supplier)
                                                <option value="{{ $supplier->id }}">
                                                    {{ $supplier->name }} | {{ $supplier->email ?? 'Sin email' }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <div class="input-group-append">
                                            <button type="button" id="addSupplierBtn" class="btn btn-outline-primary" title="Crear Proveedor" style="border-top-right-radius: 6px; border-bottom-right-radius: 6px;">
                                                <i class="fas fa-plus"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 mb-3">
                                <div class="form-group mb-0">
                                    <label for="date_issued" class="text-xs font-weight-bold text-secondary mb-1">Fecha Emisión (*)</label>
                                    <input type="date" name="date_issued" class="form-control form-control-custom" value="{{ old('date_issued', date('Y-m-d')) }}" required>
                                </div>
                            </div>
                        </div>

                        <div class="row border-top pt-3 mt-1">
                            <div class="col-md-4 mb-3">
                                <div class="form-group mb-0">
                                    <label for="delivery_date" class="text-xs font-weight-bold text-secondary mb-1">Fecha de Entrega</label>
                                    <input type="date" name="delivery_date" class="form-control form-control-custom" value="{{ old('delivery_date') }}">
                                </div>
                            </div>
                            <div class="col-md-8 mb-3">
                                <div class="form-group mb-0">
                                    <label for="delivery_address" class="text-xs font-weight-bold text-secondary mb-1">Dirección de Entrega</label>
                                    <input type="text" name="delivery_address" class="form-control form-control-custom" value="{{ old('delivery_address') }}" placeholder="Dirección donde recibir la mercancía">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Filtro de Categorías --}}
                @include('admin.partials.category-filter')

                <!-- Tarjeta: Items de la Orden -->
                <div class="card card-custom">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h3 class="card-title text-primary"><i class="fas fa-boxes mr-1"></i> Productos de la Orden</h3>
                        <button type="button" id="addItem" class="btn btn-sm btn-outline-primary font-weight-bold btn-custom">
                            <i class="fas fa-plus-circle mr-1"></i> Agregar Item
                        </button>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table mb-0" id="itemsTable">
                                <thead>
                                    <tr>
                                        <th style="width: 12%">Tipo</th>
                                        <th style="width: 35%">Producto / Kit</th>
                                        <th style="width: 12%">Cantidad</th>
                                        <th style="width: 12%">Costo Unit.</th>
                                        <th style="width: 9%" class="text-right">Total</th>
                                        <th style="width: 10%" class="text-center">¿Exento IVA?</th>
                                        <th style="width: 5%"></th>
                                    </tr>
                                </thead>
                                <tbody id="itemsBody">
                                    <tr>
                                        <td>
                                            <select name="items[0][item_type]" class="form-control form-control-custom type-selector" style="height: 38px !important;" onchange="toggleType(this)">
                                                <option value="product" selected>Producto</option>
                                                <option value="kit">Kit</option>
                                            </select>
                                        </td>
                                        <td>
                                            <select name="items[0][product_id]" class="form-control select2-product" required disabled>
                                                <option value="">Seleccione una categoría primero...</option>
                                                @foreach($products as $product)
                                                    <option value="{{ $product->id }}" data-category-id="{{ $product->category_id ?? 0 }}">{{ $product->name }} ({{ $product->code ?? 'S/C' }})</option>
                                                @endforeach
                                            </select>
                                            <select name="items[0][kit_id]" class="form-control select2-kit" style="display:none;">
                                                <option value="">Seleccione kit...</option>
                                                @foreach($kits as $kit)
                                                    <option value="{{ $kit->id }}">{{ $kit->name }}</option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td>
                                            <input type="number" name="items[0][quantity]" class="form-control form-control-custom item-qty" min="1" value="1" required>
                                        </td>
                                        <td>
                                            <input type="number" step="0.01" name="items[0][unit_cost]" class="form-control form-control-custom item-cost" min="0" value="0.00" required>
                                        </td>
                                        <td class="text-right align-middle font-weight-bold text-dark">
                                            <span class="item-total">0.00</span>
                                        </td>
                                        <td class="text-center align-middle">
                                            <input type="hidden" name="items[0][is_exempt]" value="0">
                                            <input type="checkbox" name="items[0][is_exempt]" value="1"
                                                   class="row-iva-switch"
                                                   style="width: 20px; height: 20px; cursor: pointer;">
                                        </td>
                                        <td class="text-center align-middle">
                                            <button type="button" class="btn btn-sm btn-outline-danger remove-item" style="display:none">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Tarjeta: Notas y Términos -->
                <div class="card card-custom">
                    <div class="card-header border-0 pb-0">
                        <h3 class="card-title text-secondary"><i class="fas fa-file-alt mr-1"></i> Términos y Notas</h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <div class="form-group mb-0">
                                    <label for="terms" class="text-xs font-weight-bold text-secondary mb-1">Términos y Condiciones</label>
                                    <textarea name="terms" id="terms" rows="3" class="form-control form-control-custom" placeholder="Condiciones de pago, garantías, tiempos de envío..." style="height: auto !important;"></textarea>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="form-group mb-0">
                                    <label for="notes" class="text-xs font-weight-bold text-secondary mb-1">Notas Internas</label>
                                    <textarea name="notes" id="notes" rows="3" class="form-control form-control-custom" placeholder="Comentarios internos (no se imprimen en el PDF)..." style="height: auto !important;"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- COLUMNA LATERAL (30%) - Configuración de Costos y Totales -->
            <div class="col-lg-4">
                <div class="sticky-sidebar">
                    <!-- Tarjeta de Totales -->
                    <div class="card card-custom bg-white">
                        <div class="card-header border-0 pb-0">
                            <h3 class="card-title text-primary"><i class="fas fa-coins mr-1"></i> Totales de la Orden</h3>
                        </div>
                        <div class="card-body">
                            <!-- Selectores de Configuración -->
                            <div class="form-group mb-3">
                                <label for="currency" class="text-xs font-weight-bold text-secondary mb-1">Moneda</label>
                                <select name="currency" id="currency" class="form-control form-control-custom select2">
                                    <option value="USD" {{ old('currency') == 'USD' ? 'selected' : '' }}>💵 USD - Dólar</option>
                                    <option value="EUR" {{ old('currency') == 'EUR' ? 'selected' : '' }}>💶 EUR - Euro</option>
                                    <option value="Bs" {{ old('currency') == 'Bs' ? 'selected' : '' }}>🇻🇪 Bs - Bolívar</option>
                                </select>
                            </div>

                            <div class="form-group mb-3" id="exchangeRateGroup">
                                <label for="exchange_rate" class="text-xs font-weight-bold text-secondary mb-1">Tasa de Cambio (Bs / divisa)</label>
                                <input type="number" step="0.0001" name="exchange_rate" id="exchangeRate" class="form-control form-control-custom font-weight-bold" value="{{ old('exchange_rate', 1) }}">
                            </div>

                            <div class="form-group mb-3">
                                <div class="custom-control custom-switch">
                                    <input type="checkbox" class="custom-control-input" id="iva_exempt" name="iva_exempt" value="1">
                                    <label class="custom-control-label text-xs font-weight-bold text-secondary" for="iva_exempt" style="cursor: pointer;">
                                        Exento de IVA (16%)
                                    </label>
                                </div>
                            </div>

                            <hr class="my-3">

                            <!-- Desglose de Totales en Moneda Original -->
                            <div class="total-row">
                                <span class="font-weight-bold text-secondary">Subtotal:</span>
                                <span id="grandTotal" class="font-weight-bold text-dark">$0.00</span>
                            </div>
                            <div class="total-row" id="rowIva">
                                <span class="font-weight-bold text-secondary">IVA (16%):</span>
                                <span id="ivaVal" class="font-weight-bold text-dark">$0.00</span>
                            </div>
                            <div class="total-row" id="rowIvaExempt" style="display: none;">
                                <span class="font-weight-bold text-secondary">IVA:</span>
                                <span class="badge badge-info">Exento</span>
                            </div>
                            <div class="total-row-grand">
                                <span>Total General:</span>
                                <span id="grandTotalFinal" class="text-primary">$0.00</span>
                            </div>

                            <!-- Equivalente en Bs (VES) - Solo si moneda != Bs -->
                            <div class="bs-equivalent-section" id="bsEquivalentSection">
                                <div class="bs-equivalent-title"><i class="fas fa-coins mr-1"></i> Equivalente en Bolívares (VES)</div>
                                <div class="total-row bg-transparent py-1 border-0">
                                    <span class="text-success font-weight-bold">Subtotal VES:</span>
                                    <span id="grandTotalBs" class="font-weight-bold text-success">Bs 0.00</span>
                                </div>
                                <div class="total-row bg-transparent py-1 border-0" id="rowIvaBs">
                                    <span class="text-success font-weight-bold">IVA VES:</span>
                                    <span id="ivaBs" class="font-weight-bold text-success">Bs 0.00</span>
                                </div>
                                <div class="total-row bg-transparent py-1 border-0 total-row-grand pt-2 mt-1 border-top" style="font-size: 1rem;">
                                    <span class="text-success">Total VES:</span>
                                    <span id="totalBs" class="text-success">Bs 0.00</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tarjeta de Acciones -->
                    <div class="card card-custom">
                        <div class="card-body d-flex flex-column p-3">
                            <button type="button" class="btn btn-primary btn-block btn-custom font-weight-bold mb-2 shadow-sm" id="saveOrderBtn">
                                <i class="fas fa-save mr-1"></i> Guardar Orden de Compra
                            </button>
                            <a href="{{ route('admin.purchaseOrders.index') }}" class="btn btn-block btn-outline-secondary font-weight-bold btn-custom">
                                <i class="fas fa-times mr-1"></i> Cancelar
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>

    <!-- Modal para crear Proveedor rápido -->
    <div class="modal fade" id="supplierModal" tabindex="-1" role="dialog" aria-labelledby="supplierModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content" style="border-radius: 12px; border: none; overflow: hidden;">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title font-weight-bold" id="supplierModalLabel"><i class="fas fa-building mr-1"></i> Crear Nuevo Proveedor</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="supplierForm">
                    <div class="modal-body p-4">
                        <div class="row">
                            <div class="col-12 col-md-6 mb-3">
                                <div class="form-group mb-0">
                                    <label for="supplier_name" class="text-xs font-weight-bold text-secondary mb-1">Nombre (*)</label>
                                    <input type="text" name="name" id="supplier_name" class="form-control form-control-custom" required>
                                </div>
                            </div>
                            <div class="col-12 col-md-6 mb-3">
                                <div class="form-group mb-0">
                                    <label for="supplier_tax_id" class="text-xs font-weight-bold text-secondary mb-1">RIF / Tax ID (*)</label>
                                    <input type="text" name="tax_id" id="supplier_tax_id" class="form-control form-control-custom" required>
                                    <small class="text-danger" id="supplierTaxIdError" style="display:none;">El RIF ya existe</small>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12 col-md-6 mb-3">
                                <div class="form-group mb-0">
                                    <label for="supplier_email" class="text-xs font-weight-bold text-secondary mb-1">Email</label>
                                    <input type="email" name="email" id="supplier_email" class="form-control form-control-custom">
                                </div>
                            </div>
                            <div class="col-12 col-md-6 mb-3">
                                <div class="form-group mb-0">
                                    <label for="supplier_phone" class="text-xs font-weight-bold text-secondary mb-1">Teléfono</label>
                                    <input type="text" name="phone" id="supplier_phone" class="form-control form-control-custom">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12">
                                <div class="form-group mb-0">
                                    <label for="supplier_address" class="text-xs font-weight-bold text-secondary mb-1">Dirección</label>
                                    <textarea name="address" id="supplier_address" rows="2" class="form-control form-control-custom" style="height: auto !important;"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-light p-3">
                        <button type="button" class="btn btn-secondary font-weight-bold btn-custom" data-dismiss="modal">Cancelar</button>
                        <button type="button" class="btn btn-primary font-weight-bold btn-custom" id="saveSupplierBtn">
                            <i class="fas fa-save mr-1"></i> Guardar Proveedor
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@stop

@section('js')
    <script>
        let itemIndex = 1;

        // ─── ESTADO DEL FILTRO DE CATEGORÍAS (Orden de Compra) ────────────────────
        const allPoProducts = [
            @foreach($products as $product)
            {
                id: {{ $product->id }},
                name: '{{ addslashes($product->name) }} ({{ $product->code ?? 'S/C' }})',
                categoryId: {{ $product->category_id ?? 'null' }}
            },
            @endforeach
        ];

        let poSelectedCategoryId = null;

        function poFilteredProducts() {
            if (!poSelectedCategoryId) return [];
            return allPoProducts.filter(p => p.categoryId == poSelectedCategoryId);
        }

        function buildPoProductOptionsHtml() {
            const products = poFilteredProducts();
            if (products.length === 0) {
                return '<option value="">— Sin productos en esta categoría —</option>';
            }
            let html = '<option value="">Seleccione producto...</option>';
            products.forEach(p => {
                html += `<option value="${p.id}" data-category-id="${p.categoryId}">${p.name}</option>`;
            });
            return html;
        }

        function applyPoFilter() {
            const enabled = !!poSelectedCategoryId;
            const hint = document.getElementById('categoryFilterHint');

            $('#itemsBody tr').each(function() {
                const select = $(this).find('.select2-product:visible');
                if (select.length === 0) return;

                const currentVal = select.val();
                if (select.data('select2')) select.select2('destroy');
                select.html(buildPoProductOptionsHtml());

                // Preservar selección aunque no esté en la categoría nueva
                if (currentVal && !poFilteredProducts().find(p => p.id == currentVal)) {
                    const orphan = allPoProducts.find(p => p.id == currentVal);
                    if (orphan) {
                        select.prepend(`<option value="${orphan.id}" data-category-id="${orphan.categoryId}" selected>[${orphan.name}]</option>`);
                        select.val(orphan.id);
                    }
                } else {
                    select.val(currentVal || '');
                }

                select.prop('disabled', !enabled).select2({
                    theme: 'bootstrap4',
                    width: '100%',
                    allowClear: true
                });
            });

            if (hint) {
                hint.innerHTML = enabled
                    ? `<i class="fas fa-check-circle text-success"></i> Mostrando productos de la categoría seleccionada.`
                    : `<i class="fas fa-info-circle"></i> Seleccione una categoría para habilitar el selector de productos.`;
            }
        }
        // ─────────────────────────────────────────────────────────────────────────────

        function getCurrencySymbol(currency) {
            switch(currency) {
                case 'USD': return '$ ';
                case 'EUR': return '€ ';
                case 'Bs': return 'Bs ';
                default: return currency + ' ';
            }
        }

        function calculateTotals() {
            const currency = $('#currency').val();
            const exchangeRate = parseFloat($('#exchangeRate').val()) || 1;
            const symbol = getCurrencySymbol(currency);
            const isBs = currency === 'Bs' || currency === 'VES';
            const isIvaExempt = $('#iva_exempt').is(':checked');

            // Mostrar/ocultar input de tasa de cambio de forma elegante (condicional)
            if (isBs) {
                $('#exchangeRateGroup').slideUp(200);
                $('#exchangeRate').val(1);
                $('#bsEquivalentSection').slideUp(200);
            } else {
                $('#exchangeRateGroup').slideDown(200);
                $('#bsEquivalentSection').slideDown(200);
            }
            
            let subtotal = 0;
            let taxableSubtotal = 0;
            
            // Computar el array de filas activas en el ciclo de vida de la tabla
            $('#itemsBody tr').each(function() {
                const qty = parseFloat($(this).find('.item-qty').val()) || 0;
                const cost = parseFloat($(this).find('.item-cost').val()) || 0;
                const total = qty * cost;
                
                $(this).find('.item-total').text(total.toFixed(2));
                subtotal += total;

                // Determinar exención de IVA a nivel de fila (si existiera switch/select de fila)
                let rowExempt = isIvaExempt;
                if (!rowExempt) {
                    const rowSwitch = $(this).find('.row-iva-switch, .item-tax-status, .item-iva');
                    if (rowSwitch.length) {
                        if (rowSwitch.is(':checkbox')) {
                            rowExempt = rowSwitch.is(':checked');
                        } else {
                            rowExempt = rowSwitch.val() === 'exento';
                        }
                    }
                }

                if (!rowExempt) {
                    taxableSubtotal += total;
                }
            });
            
            $('#grandTotal').text(symbol + subtotal.toFixed(2));
            
            // IVA calculations
            const ivaVal = taxableSubtotal * 0.16;
            const grandTotalFinal = subtotal + ivaVal;
            
            $('#ivaVal').text(symbol + ivaVal.toFixed(2));
            $('#grandTotalFinal').text(symbol + grandTotalFinal.toFixed(2));
            
            if (ivaVal === 0) {
                $('#rowIva').hide();
                $('#rowIvaExempt').show();
                $('#rowIvaBs').hide();
            } else {
                $('#rowIva').show();
                $('#rowIvaExempt').hide();
                $('#rowIvaBs').show();
            }

            // Equivalente en Bs (VES)
            if (!isBs) {
                const subtotalBs = subtotal * exchangeRate;
                const ivaBs = ivaVal * exchangeRate;
                const totalBs = subtotalBs + ivaBs;

                $('#grandTotalBs').text('Bs ' + subtotalBs.toFixed(2));
                $('#ivaBs').text('Bs ' + ivaBs.toFixed(2));
                $('#totalBs').text('Bs ' + totalBs.toFixed(2));
            }
        }

        // Registro de eventos para cálculos dinámicos y reactivos (incluyendo switches de fila y general)
        $(document).on('input change', '.item-qty, .item-cost, .row-iva-switch, .item-tax-status, .item-iva, #iva_exempt, #currency, #exchangeRate', function() {
            calculateTotals();
        });

        function initSelect2() {
            $('.select2').select2({
                theme: 'bootstrap4',
                width: '100%',
                allowClear: true
            });

            $('.select2-ajax').not('.select2initialized').each(function() {
                $(this).addClass('select2initialized').select2({
                    theme: 'bootstrap4',
                    width: '100%',
                    allowClear: true,
                    minimumInputLength: 0,
                    ajax: {
                        url: $(this).data('url'),
                        dataType: 'json',
                        delay: 250,
                        processResults: function(data) {
                            return { results: data.results };
                        },
                        cache: true
                    }
                });
            });
            
            $('.select2-product:visible').each(function() {
                if (!$(this).data('select2')) {
                    $(this).select2({
                        theme: 'bootstrap4',
                        width: '100%',
                        allowClear: true
                    });
                }
            });
            
            $('.select2-kit:visible').each(function() {
                if (!$(this).data('select2')) {
                    $(this).select2({
                        theme: 'bootstrap4',
                        width: '100%',
                        allowClear: true
                    });
                }
            });
        }

        function toggleType(select) {
            const row = select.closest('tr');
            const productSelect = row.querySelector('.select2-product');
            const kitSelect = row.querySelector('.select2-kit');
            
            if (select.value === 'product') {
                productSelect.style.display = 'block';
                productSelect.required = true;
                kitSelect.style.display = 'none';
                kitSelect.required = false;
                kitSelect.value = '';
                
                if (typeof $ !== 'undefined' && $.fn.select2) {
                    if ($(kitSelect).data('select2')) {
                        $(kitSelect).select2('destroy');
                    }
                    $(productSelect).select2({
                        theme: 'bootstrap4',
                        width: '100%',
                        allowClear: true
                    });
                }
            } else {
                productSelect.style.display = 'none';
                productSelect.required = false;
                productSelect.value = '';
                kitSelect.style.display = 'block';
                kitSelect.style.width = '100%';
                kitSelect.required = true;
                
                if (typeof $ !== 'undefined' && $.fn.select2) {
                    if ($(productSelect).data('select2')) {
                        $(productSelect).select2('destroy');
                    }
                    $(kitSelect).select2({
                        theme: 'bootstrap4',
                        width: '100%',
                        allowClear: true
                    });
                }
            }
            calculateTotals();
        }

        function updateRemoveButtons() {
            const rows = $('#itemsBody tr').length;
            $('#itemsBody .remove-item').toggle(rows > 1);
        }

        $('#addItem').click(function() {
            if (!poSelectedCategoryId) {
                Swal.fire({
                    icon: 'info',
                    title: 'Seleccione una categoría',
                    text: 'Elija primero una categoría en el filtro para poder agregar productos.'
                });
                return;
            }

            const productOptions = buildPoProductOptionsHtml();
            const kitOptions = `@foreach($kits as $kit)<option value="{{ $kit->id }}">{{ $kit->name }}</option>@endforeach`;
            const row = `
                <tr>
                    <td>
                        <select name="items[${itemIndex}][item_type]" class="form-control form-control-custom type-selector" style="height: 38px !important;" onchange="toggleType(this)">
                            <option value="product" selected>Producto</option>
                            <option value="kit">Kit</option>
                        </select>
                    </td>
                    <td>
                        <select name="items[${itemIndex}][product_id]" class="form-control select2-product" required>
                            ${productOptions}
                        </select>
                        <select name="items[${itemIndex}][kit_id]" class="form-control select2-kit" style="display:none;">
                            <option value="">Seleccione kit...</option>
                            ${kitOptions}
                        </select>
                    </td>
                    <td><input type="number" name="items[${itemIndex}][quantity]" class="form-control form-control-custom item-qty" min="1" value="1" required></td>
                    <td><input type="number" step="0.01" name="items[${itemIndex}][unit_cost]" class="form-control form-control-custom item-cost" min="0" value="0.00" required></td>
                    <td class="text-right align-middle font-weight-bold text-dark"><span class="item-total">0.00</span></td>
                    <td class="text-center align-middle">
                        <input type="hidden" name="items[${itemIndex}][is_exempt]" value="0">
                        <input type="checkbox" name="items[${itemIndex}][is_exempt]" value="1"
                               class="row-iva-switch"
                               style="width: 20px; height: 20px; cursor: pointer;">
                    </td>
                    <td class="text-center align-middle">
                        <button type="button" class="btn btn-sm btn-outline-danger remove-item"><i class="fas fa-trash-alt"></i></button>
                    </td>
                </tr>
            `;
            $('#itemsBody').append(row);
            itemIndex++;
            initSelect2();
            updateRemoveButtons();
            calculateTotals();
        });

        // Evento obligatorio de eliminación de fila (recalcula totales de forma infalible)
        $(document).on('click', '.remove-item', function() {
            $(this).closest('tr').remove();
            calculateTotals();
            updateRemoveButtons();
        });

        $(document).on('select2:open', function() {
            setTimeout(function() {
                var dropdown = document.querySelector('.select2-dropdown');
                if (dropdown) {
                    dropdown.style.maxHeight = '350px';
                    dropdown.style.overflow = 'hidden';
                    var results = dropdown.querySelector('.select2-results');
                    if (results) {
                        results.style.maxHeight = '350px';
                        results.style.overflowY = 'auto';
                    }
                }
            }, 10);
        });

        // Botón para abrir modal de proveedor
        $(document).on('click', '#addSupplierBtn', function() {
            $("#supplierModal").modal("show");
        });

        // Guardar proveedor AJAX
        $(document).on('click', '#saveSupplierBtn', function(e) {
            e.preventDefault();
            e.stopPropagation();
            const btn = $(this);
            btn.prop("disabled", true).html("<i class=\"fas fa-spinner fa-spin\"></i> Guardando...");
            
            $.ajax({
                url: "{{ route('admin.suppliers.quick-store') }}",
                method: "POST",
                data: $("#supplierForm").serialize(),
                headers: {"X-CSRF-TOKEN": $("meta[name=\"csrf-token\"]").attr("content")},
                success: function(response) {
                    $("#supplierModal").modal("hide");
                    $("#supplierForm")[0].reset();
                    
                    const newOption = new Option(
                        response.supplier.name + " | " + (response.supplier.email || "Sin email"),
                        response.supplier.id,
                        false,
                        false
                    );
                    
                    const $supplierSelect = $("#supplier_id");
                    $supplierSelect.append(newOption);
                    $supplierSelect.val(response.supplier.id).trigger('change');
                    
                    Swal.fire({
                        icon: "success",
                        title: "¡Éxito!",
                        text: response.message,
                        timer: 2000,
                        showConfirmButton: false
                    });
                },
                error: function(xhr) {
                    if(xhr.status === 422) {
                        const errors = xhr.responseJSON ? xhr.responseJSON.errors : {};
                        if(errors.tax_id) {
                            $("#supplierTaxIdError").show();
                        }
                        Swal.fire({
                            icon: "error",
                            title: "Error de validación",
                            text: Object.values(errors).flat().join(', ')
                        });
                    } else {
                        Swal.fire({
                            icon: "error",
                            title: "Error",
                            text: "Error " + xhr.status + ": No se pudo guardar el proveedor"
                        });
                    }
                },
                complete: function() {
                    btn.prop("disabled", false).html("<i class=\"fas fa-save\"></i> Guardar Proveedor");
                }
            });
        });

        $("#supplierModal").on("hidden.bs.modal", function() {
            $("#supplierForm")[0].reset();
            $("#supplierTaxIdError").hide();
        });

        $(document).ready(function() {
            // Filtro de categorías: estado inicial deshabilitado
            applyPoFilter();
            $('#categoryFilter').on('change', function() {
                poSelectedCategoryId = $(this).val() || null;
                applyPoFilter();
            });

            initSelect2();
            updateRemoveButtons();
            calculateTotals();

            // Modal de confirmación para guardar orden
            document.getElementById('saveOrderBtn').addEventListener('click', function() {
                confirmAction({
                    title: 'Crear Orden de Compra',
                    message: '¿Está seguro de crear esta orden de compra?',
                    alert: 'Verifique que todos los datos y productos sean correctos antes de continuar.',
                    confirmBtnClass: 'btn-primary',
                    onConfirm: function() {
                        document.getElementById('orderForm').submit();
                    }
                });
            });
        });
    </script>
    @include('admin.partials.confirm-action')
@endsection
