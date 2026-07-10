@extends('adminlte::page')

@section('title', 'Convertir RFQ a Orden de Compra')

@section('plugins.Select2', true)
@section('plugins.Sweetalert2', true)

{{-- ═══════════════════════════════════════════════════════════════════════════
     CSS AISLADO — Prefijo .sgci-* para evitar colisión con AdminLTE/BS4
     ═══════════════════════════════════════════════════════════════════════════ --}}
@section('css')
    <style>
        /* ── Reset de Spinners ──────────────────────────────────── */
        .sgci-form input[type="number"]::-webkit-inner-spin-button,
        .sgci-form input[type="number"]::-webkit-outer-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }
        .sgci-form input[type="number"] { -moz-appearance: textfield; }

        /* ── Variables de Diseño ────────────────────────────────── */
        :root {
            --sgci-radius: 10px;
            --sgci-primary: #4f46e5;
            --sgci-primary-light: #6366f1;
            --sgci-bg-subtle: #f8f9fc;
            --sgci-border: #e2e8f0;
            --sgci-shadow: 0 2px 8px rgba(0,0,0,0.06);
            --sgci-success-bg: #f0fdf4;
            --sgci-success-border: #bbf7d0;
            --sgci-success-text: #15803d;
        }

        /* ── Cards ──────────────────────────────────────────────── */
        .sgci-card {
            border: 1px solid var(--sgci-border);
            border-radius: var(--sgci-radius);
            box-shadow: var(--sgci-shadow);
            overflow: hidden;
            margin-bottom: 1.25rem;
        }
        .sgci-card .card-header {
            background: #fff;
            border-bottom: 1px solid var(--sgci-border);
            padding: 0.85rem 1.15rem;
        }
        .sgci-card .card-header h3 {
            font-size: 0.9rem;
            font-weight: 700;
            margin: 0;
            color: #1e293b;
        }
        .sgci-card .card-body { padding: 1.15rem; }

        /* ── Panel de Control (sidebar) ─────────────────────────── */
        .sgci-sidebar-card .sgci-sidebar-header {
            background: linear-gradient(135deg, var(--sgci-primary) 0%, var(--sgci-primary-light) 100%);
            color: #fff;
            padding: 0.9rem 1.15rem;
        }
        .sgci-sidebar-card .sgci-sidebar-header h3 {
            font-size: 0.9rem;
            font-weight: 700;
            margin: 0;
        }

        /* ── Tabla de Ítems ─────────────────────────────────────── */
        .sgci-items-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
        }
        .sgci-items-table thead th {
            background: var(--sgci-bg-subtle);
            border-bottom: 2px solid var(--sgci-border);
            font-size: 0.7rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            color: #64748b;
            padding: 0.65rem 0.75rem;
            white-space: nowrap;
        }
        .sgci-items-table tbody td {
            padding: 0.6rem 0.75rem;
            vertical-align: middle;
            border-bottom: 1px solid #f1f5f9;
        }
        .sgci-items-table tbody tr:hover { background: #fafbfe; }

        /* Contenedor con scroll para la tabla */
        .sgci-table-wrapper {
            max-height: 400px;
            overflow-y: auto;
            overflow-x: hidden;
        }
        .sgci-table-wrapper thead th {
            position: sticky;
            top: 0;
            z-index: 10;
        }

        /* ── Inputs específicos de la tabla ──────────────────────── */
        .sgci-cost-input {
            width: 100px !important;
            min-width: 100px;
            text-align: right;
            font-weight: 600;
        }

        /* ── Totales y Desglose ──────────────────────────────────── */
        .sgci-total-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.55rem 0;
            border-bottom: 1px dashed var(--sgci-border);
            font-size: 0.82rem;
            color: #4b5563;
        }
        .sgci-total-row:last-child { border-bottom: none; }
        .sgci-total-row-grand {
            border-top: 1px solid var(--sgci-border);
            padding-top: 0.75rem;
            margin-top: 0.4rem;
            font-size: 1.1rem;
            font-weight: 800;
            color: #111827;
        }

        /* ── Sección de Bolívares ───────────────────────────────── */
        .sgci-bs-section {
            background-color: var(--sgci-success-bg);
            border: 1px solid var(--sgci-success-border);
            border-radius: 8px;
            padding: 0.7rem;
            margin-top: 0.9rem;
        }
        .sgci-bs-title {
            font-size: 0.7rem;
            font-weight: 700;
            color: var(--sgci-success-text);
            text-transform: uppercase;
            margin-bottom: 0.4rem;
            letter-spacing: 0.4px;
        }

        /* ── Select2 Bootstrap 4 Override ────────────────────────── */
        .sgci-form .select2-container--bootstrap4 .select2-selection--single {
            height: 34px !important;
            padding-top: 3px;
            border-color: var(--sgci-border);
            border-radius: 6px;
        }
        .sgci-form .select2-container--bootstrap4 .select2-selection--single .select2-selection__arrow {
            height: 32px;
        }

        /* ── Labels y Badges ────────────────────────────────────── */
        .sgci-label {
            font-size: 0.72rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            color: #94a3b8;
            margin-bottom: 0.3rem;
        }
        .sgci-status-badge {
            display: block;
            text-align: center;
            padding: 0.45rem 0;
            border-radius: 6px;
            font-size: 0.82rem;
            font-weight: 700;
        }

        /* ── Validación y Switches ──────────────────────────────── */
        .sgci-form select.is-invalid + .select2-container .select2-selection {
            border-color: #dc3545 !important;
        }
        .sgci-form .custom-switch .custom-control-label::before {
            height: 1.4rem; width: 2.5rem; border-radius: 1rem;
        }
        .sgci-form .custom-switch .custom-control-label::after {
            width: calc(1.4rem - 4px); height: calc(1.4rem - 4px); border-radius: 1rem;
        }
        .sgci-form .custom-switch .custom-control-input:checked ~ .custom-control-label::after {
            transform: translateX(1.1rem);
        }

        /* ── Scroll Limpio para Select2 Dropdown ────────────────── */
        .select2-results__options {
            max-height: 250px !important;
            overflow-y: auto !important;
        }

        /* ── Alineación del Select2 en input-group ──────────────── */
        .sgci-form .input-group .select2-container {
            flex: 1 1 auto;
            width: 1% !important;
        }

        .sgci-form .input-group .select2-container .select2-selection--single {
            border-top-left-radius: 0 !important;
            border-bottom-left-radius: 0 !important;
        }

        /* ── Utilidades ───────────────────────────────────────────── */
        .sgci-text-xs { font-size: 0.72rem !important; }
        .sgci-text-sm { font-size: 0.82rem !important; }
    </style>
@endsection

@section('content_header')
    <div class="d-flex justify-content-between align-items-center mb-2">
        <div>
            <h1 class="text-dark font-weight-bold" style="font-size: 1.55rem;">
                <i class="fas fa-shopping-cart text-primary mr-2"></i> Convertir RFQ {{ $rfq->code }} a OC
            </h1>
            <p class="text-muted mb-0 sgci-text-sm">Genere la orden de compra precargando los datos negociados con el proveedor.</p>
        </div>
        <a href="{{ route('admin.rfq.show', $rfq) }}" class="btn btn-sm btn-outline-secondary font-weight-bold">
            <i class="fas fa-arrow-left mr-1"></i> Volver a la RFQ
        </a>
    </div>
@stop

@section('content')
    @php
        $selectedSupplierId = old('supplier_id', isset($offer) ? $offer->supplier_id : request('supplier_id'));
        
        $offerCurrency = 'USD';
        $offerIvaExempt = false;
        if (isset($offer) && $offer->items->isNotEmpty()) {
            $offerCurrency = $offer->items->first()->currency;
            $offerIvaExempt = $offer->items->contains(function($item) {
                return $item->tax_status === 'exento';
            });
        } else {
            $offerCurrency = request('currency', 'USD');
            $offerIvaExempt = request('iva_exempt') == '1';
        }
        $selectedCurrency = old('currency', $offerCurrency);
        $isIvaExempt = old('iva_exempt', $offerIvaExempt);
    @endphp

    <div class="container-fluid sgci-form">
        @include('admin.partials.session-messages')

        <form action="{{ route('admin.rfq.store-po', $rfq) }}" method="POST" id="orderForm">
            @csrf

            <div class="row">
                <!-- COLUMNA PRINCIPAL (70%) -->
                <div class="col-lg-8">
                    <!-- Tarjeta: Información RFQ Referencia -->
                    <div class="sgci-card card" style="background-color: #f8fafc; border: 1px solid #e2e8f0; box-shadow: none;">
                        <div class="card-body py-3">
                            <div class="row sgci-text-sm">
                                <div class="col-md-3"><strong>Código RFQ:</strong> <span class="text-primary font-weight-bold">{{ $rfq->code }}</span></div>
                                <div class="col-md-3"><strong>Título:</strong> {{ $rfq->title ?? 'S/T' }}</div>
                                <div class="col-md-3"><strong>Estado RFQ:</strong> {!! $rfq->status_badge !!}</div>
                                <div class="col-md-3"><strong>Fecha Límite:</strong> {{ $rfq->date_required?->format('d/m/Y') ?? 'N/A' }}</div>
                            </div>
                        </div>
                    </div>

                    <!-- Tarjeta: Información General de la OC -->
                    <div class="sgci-card card">
                        <div class="card-header">
                            <h3><i class="fas fa-info-circle mr-1" style="color:var(--sgci-primary);"></i> Datos de la Orden de Compra</h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <div class="form-group mb-0">
                                        <label for="code" class="sgci-label">Código OC</label>
                                        <div class="input-group input-group-sm">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text bg-light"><i class="fas fa-hashtag text-muted"></i></span>
                                            </div>
                                            <input type="text" name="code" class="form-control bg-light font-weight-bold" value="{{ $code }}" readonly>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-5 mb-3">
                                    <div class="form-group mb-0">
                                        <label for="supplier_id" class="sgci-label">Proveedor <span class="text-danger">*</span></label>
                                        <div class="input-group input-group-sm">
                                            <div class="input-group-prepend">
                                                <button type="button" id="addSupplierBtn" class="btn btn-outline-primary" title="Crear Proveedor Rápido" style="border-top-left-radius: 6px; border-bottom-left-radius: 6px;">
                                                    <i class="fas fa-plus"></i>
                                                </button>
                                            </div>
                                            <select name="supplier_id" id="supplier_id" class="form-control select2-ajax" data-placeholder="Buscar proveedor..." data-url="{{ route('admin.purchaseOrders.searchSuppliers') }}" required>
                                                <option value="">Seleccione un proveedor...</option>
                                                @foreach($suppliers as $supplier)
                                                    <option value="{{ $supplier->id }}" {{ $selectedSupplierId == $supplier->id ? 'selected' : '' }}>
                                                        {{ $supplier->name }} | {{ $supplier->email ?? 'Sin email' }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <div class="form-group mb-0">
                                        <label for="date_issued" class="sgci-label">Fecha Emisión <span class="text-danger">*</span></label>
                                        <div class="input-group input-group-sm">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text bg-light"><i class="fas fa-calendar-alt text-muted"></i></span>
                                            </div>
                                            <input type="date" name="date_issued" id="date_issued" class="form-control" value="{{ old('date_issued', date('Y-m-d')) }}" required>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row border-top pt-3 mt-1">
                                <div class="col-md-4 mb-3">
                                    <div class="form-group mb-0">
                                        <label for="delivery_date" class="sgci-label">Fecha de Entrega</label>
                                        <div class="input-group input-group-sm">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text bg-light"><i class="fas fa-calendar-check text-muted"></i></span>
                                            </div>
                                            <input type="date" name="delivery_date" id="delivery_date" class="form-control" value="{{ old('delivery_date') }}">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-8 mb-3">
                                    <div class="form-group mb-0">
                                        <label for="delivery_address" class="sgci-label">Dirección de Entrega</label>
                                        <div class="input-group input-group-sm">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text bg-light"><i class="fas fa-map-marker-alt text-muted"></i></span>
                                            </div>
                                            <input type="text" name="delivery_address" id="delivery_address" class="form-control" value="{{ old('delivery_address') }}" placeholder="Dirección donde recibir la mercancía">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tarjeta: Items de la RFQ -->
                    <div class="sgci-card card">
                        <div class="card-header">
                            <h3><i class="fas fa-boxes mr-1" style="color:var(--sgci-primary);"></i> Ítems Cotizados</h3>
                        </div>
                        <div class="card-body p-0">
                            <div class="sgci-table-wrapper">
                                <table class="sgci-items-table table-hover" id="itemsTable">
                                    <thead>
                                        <tr>
                                            <th style="width: 40%">Producto / Kit</th>
                                            <th style="width: 15%" class="text-center">Cantidad</th>
                                            <th style="width: 20%">Costo Unit. <span class="text-danger">*</span></th>
                                            <th style="width: 15%" class="text-right">Total</th>
                                            <th style="width: 10%" class="text-center">¿Exento?</th>
                                        </tr>
                                    </thead>
                                    <tbody id="itemsBody">
                                        @foreach($rfq->items as $index => $item)
                                            <tr class="item-row">
                                                <td>
                                                    <input type="hidden" name="items[{{ $index }}][item_type]" value="{{ $item->item_type }}">
                                                    @if($item->item_type === 'kit')
                                                        <input type="hidden" name="items[{{ $index }}][kit_id]" value="{{ $item->kit_id }}">
                                                        <strong>{{ $item->kit->name ?? 'N/A' }}</strong>
                                                        <span class="badge badge-info ml-1 sgci-text-xs">Kit</span>
                                                    @else
                                                        <input type="hidden" name="items[{{ $index }}][product_id]" value="{{ $item->product_id }}">
                                                        <strong>{{ $item->product->name ?? 'N/A' }}</strong><br>
                                                        <small class="text-muted">{{ $item->product->code ?? 'S/C' }}</small>
                                                    @endif
                                                </td>
                                                <td>
                                                    @php
                                                        $reqQty = request("items.$index.quantity", $item->quantity);
                                                        $baseUnit = $item->item_type === 'kit' ? 'und' : ($item->product->unit->abbreviation ?? 'und');
                                                    @endphp
                                                    <div class="input-group input-group-sm">
                                                        <input type="number" name="items[{{ $index }}][quantity_uom]" class="form-control sgci-qty-input row-quantity-uom" min="1" value="{{ $reqQty }}" required>
                                                        <input type="hidden" name="items[{{ $index }}][quantity]" class="row-quantity-base item-qty" value="{{ $reqQty }}">
                                                        <input type="hidden" class="row-base-unit-abbr" value="{{ $baseUnit }}">
                                                        <div class="input-group-append">
                                                            <select name="items[{{ $index }}][uom_id]" class="form-control sgci-uom-select row-uom-selector" {{ $item->item_type === 'kit' ? 'disabled' : '' }}>
                                                                <option value="{{ $item->product->unit_id ?? '' }}" data-factor="1.0" selected>{{ $item->item_type === 'kit' ? 'und' : ($item->product->unit->abbreviation ?? 'und') }}</option>
                                                                @if($item->item_type === 'product' && $item->product && $item->product->conversions)
                                                                    @foreach($item->product->conversions as $conv)
                                                                        @if($conv->id != $item->product->unit_id)
                                                                            <option value="{{ $conv->id }}" data-factor="{{ $conv->factor }}">{{ $conv->name }}</option>
                                                                        @endif
                                                                    @endforeach
                                                                @endif
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <small class="text-muted d-block mt-1 row-uom-label" style="font-size: 0.75rem; font-weight: 600; text-align: left;"></small>
                                                </td>
                                                <td>
                                                    @php
                                                        $productId = $item->item_type === 'kit' ? ($item->kit_id ?? $item->product_id) : $item->product_id;
                                                        $itemOffer = isset($offer) ? $offer->items->firstWhere('product_id', $productId) : null;
                                                        $defaultCost = $itemOffer ? $itemOffer->unit_price : request('costs.' . $item->id, old("items.$index.unit_cost", 0));
                                                    @endphp
                                                    <input type="number" step="0.01" name="items[{{ $index }}][unit_cost_uom]" class="form-control form-control-sm sgci-cost-input row-cost-uom item-cost" min="0" value="{{ old("items.$index.unit_cost_uom", $defaultCost) }}" required>
                                                    <input type="hidden" name="items[{{ $index }}][unit_cost]" class="row-cost-base" value="{{ old("items.$index.unit_cost", $defaultCost) }}">
                                                </td>
                                                <td class="text-right align-middle font-weight-bold text-dark">
                                                    <span class="item-total">0.00</span>
                                                </td>
                                                <td class="text-center align-middle">
                                                    <input type="hidden" name="items[{{ $index }}][is_exempt]" value="0">
                                                    <div style="display: flex; justify-content: center; align-items: center;">
                                                        <input type="checkbox" name="items[{{ $index }}][is_exempt]" value="1" class="row-iva-switch" 
                                                               {{ old("items.$index.is_exempt", request("items.$index.is_exempt", request("pre_exempt.$index")) == "1" ? true : $item->is_exempt) ? 'checked' : '' }} 
                                                               style="width: 18px; height: 18px; cursor: pointer;">
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Tarjeta: Notas y Términos -->
                    <div class="sgci-card card">
                        <div class="card-header">
                            <h3><i class="fas fa-file-alt mr-1" style="color:var(--sgci-primary);"></i> Términos y Notas</h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <div class="form-group mb-0">
                                        <label for="terms" class="sgci-label">Términos y Condiciones</label>
                                        <textarea name="terms" id="terms" rows="3" class="form-control form-control-sm" placeholder="Condiciones de pago, garantías, tiempos de envío...">{{ old('terms') }}</textarea>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <div class="form-group mb-0">
                                        <label for="notes" class="sgci-label">Notas Internas</label>
                                        <textarea name="notes" id="notes" rows="3" class="form-control form-control-sm" placeholder="Comentarios internos (no se imprimen en el PDF)...">{{ old('notes') }}</textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- COLUMNA LATERAL (30%) - Configuración de Costos y Totales -->
                <div class="col-lg-4">
                    <div class="sgci-card card sgci-sidebar-card" style="position:sticky;top:70px;">
                        <div class="sgci-sidebar-header">
                            <h3><i class="fas fa-coins mr-1"></i> Totales y Control</h3>
                        </div>
                        <div class="card-body">
                            <!-- Selectores de Configuración -->
                            <div class="form-group mb-3">
                                <label for="currency" class="sgci-label">Moneda</label>
                                <select name="currency" id="currency" class="form-control form-control-sm select2" style="width:100%;">
                                    <option value="USD" {{ $selectedCurrency == 'USD' ? 'selected' : '' }}>💵 USD - Dólar</option>
                                    <option value="EUR" {{ $selectedCurrency == 'EUR' ? 'selected' : '' }}>💶 EUR - Euro</option>
                                    <option value="Bs" {{ $selectedCurrency == 'Bs' ? 'selected' : '' }}>🇻🇪 Bs - Bolívar</option>
                                </select>
                            </div>

                            <div class="form-group mb-3" id="exchangeRateGroup">
                                <label for="exchangeRate" class="sgci-label">Tasa de Cambio (Bs / divisa)</label>
                                <div class="input-group input-group-sm">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text bg-light"><i class="fas fa-sync text-muted"></i></span>
                                    </div>
                                    <input type="number" step="0.0001" name="exchange_rate" id="exchangeRate" class="form-control font-weight-bold" value="{{ old('exchange_rate', 1) }}">
                                </div>
                            </div>

                            <div class="form-group mb-3">
                                <div class="custom-control custom-switch">
                                    <input type="checkbox" class="custom-control-input" id="iva_exempt" name="iva_exempt" value="1" {{ $isIvaExempt ? 'checked' : '' }}>
                                    <label class="custom-control-label sgci-text-xs font-weight-bold text-secondary" for="iva_exempt" style="cursor: pointer;">
                                        Toda la Orden Exenta de IVA
                                    </label>
                                </div>
                            </div>

                            <hr class="my-3 border-light">

                            <!-- Desglose de Totales en Moneda Original -->
                            <div class="sgci-total-row">
                                <span class="font-weight-bold text-secondary">Subtotal:</span>
                                <span id="grandTotal" class="font-weight-bold text-dark">$0.00</span>
                            </div>
                            <div class="sgci-total-row" id="rowIva">
                                <span class="font-weight-bold text-secondary">IVA (16%):</span>
                                <span id="ivaVal" class="font-weight-bold text-dark">$0.00</span>
                            </div>
                            <div class="sgci-total-row" id="rowIvaExempt" style="display: none;">
                                <span class="font-weight-bold text-secondary">IVA:</span>
                                <span class="badge badge-info">Exento</span>
                            </div>
                            <div class="sgci-total-row-grand">
                                <span>Total General:</span>
                                <span id="grandTotalFinal" class="text-primary">$0.00</span>
                            </div>

                            <!-- Equivalente en Bs (VES) - Solo si moneda != Bs -->
                            <div class="sgci-bs-section" id="bsEquivalentSection">
                                <div class="sgci-bs-title"><i class="fas fa-coins mr-1"></i> Equivalente en Bolívares (VES)</div>
                                <div class="sgci-total-row bg-transparent py-1 border-0">
                                    <span class="text-success font-weight-bold sgci-text-xs">Subtotal VES:</span>
                                    <span id="grandTotalBs" class="font-weight-bold text-success sgci-text-xs">Bs 0.00</span>
                                </div>
                                <div class="sgci-total-row bg-transparent py-1 border-0" id="rowIvaBs">
                                    <span class="text-success font-weight-bold sgci-text-xs">IVA VES:</span>
                                    <span id="ivaBs" class="font-weight-bold text-success sgci-text-xs">Bs 0.00</span>
                                </div>
                                <div class="sgci-total-row bg-transparent py-1 border-0 sgci-total-row-grand pt-2 mt-1 border-top" style="font-size: 1rem;">
                                    <span class="text-success font-weight-bold">Total VES:</span>
                                    <span id="totalBs" class="text-success font-weight-bold">Bs 0.00</span>
                                </div>
                            </div>

                            <hr class="my-3 border-light">

                            <!-- Botones de Accion -->
                            <button type="button" class="btn btn-primary btn-block shadow-sm mb-2" id="saveOrderBtn">
                                <i class="fas fa-save mr-1"></i> Crear Orden de Compra
                            </button>
                            <a href="{{ route('admin.rfq.show', $rfq) }}" class="btn btn-block btn-outline-danger btn-sm">
                                <i class="fas fa-times mr-1"></i> Cancelar
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <!-- Modal para crear Proveedor rápido -->
    <div class="modal fade" id="supplierModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content sgci-card" style="border-radius:var(--sgci-radius);">
                <div class="modal-header" style="background:linear-gradient(135deg,var(--sgci-primary),var(--sgci-primary-light));color:#fff;padding:.85rem 1.15rem;">
                    <h5 class="modal-title font-weight-bold" style="font-size:.95rem;"><i class="fas fa-building mr-1"></i> Crear Nuevo Proveedor</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="supplierForm">
                    <div class="modal-body p-4">
                        <div class="row">
                            <div class="col-12 col-md-6 mb-3">
                                <div class="form-group mb-0">
                                    <label class="sgci-label">Nombre Comercial / Razón Social (*)</label>
                                    <input type="text" name="name" id="supplier_name" class="form-control form-control-sm" required>
                                </div>
                            </div>
                            <div class="col-12 col-md-6 mb-3">
                                <div class="form-group mb-0">
                                    <label class="sgci-label">RIF / Tax ID (*)</label>
                                    <input type="text" name="tax_id" id="supplier_tax_id" class="form-control form-control-sm" required>
                                    <small class="text-danger sgci-text-xs" id="supplierTaxIdError" style="display:none;">El RIF ya existe</small>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12 col-md-6 mb-3">
                                <div class="form-group mb-0">
                                    <label class="sgci-label">Email</label>
                                    <input type="email" name="email" id="supplier_email" class="form-control form-control-sm">
                                </div>
                            </div>
                            <div class="col-12 col-md-6 mb-3">
                                <div class="form-group mb-0">
                                    <label class="sgci-label">Teléfono</label>
                                    <input type="text" name="phone" id="supplier_phone" class="form-control form-control-sm">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12">
                                <div class="form-group mb-0">
                                    <label class="sgci-label">Dirección Física</label>
                                    <textarea name="address" id="supplier_address" rows="2" class="form-control form-control-sm"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-light p-3">
                        <button type="button" class="btn btn-sm btn-outline-secondary font-weight-bold" data-dismiss="modal">Cancelar</button>
                        <button type="button" class="btn btn-sm btn-primary font-weight-bold" id="saveSupplierBtn">
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

        $('#itemsBody tr.item-row').each(function() {
            const qtyUom = parseFloat($(this).find('.row-quantity-uom').val()) || 0;
            const costUom = parseFloat($(this).find('.row-cost-uom').val()) || 0;
            const total = qtyUom * costUom;

            $(this).find('.item-total').text(total.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
            subtotal += total;

            let rowExempt = isIvaExempt;
            if (!rowExempt) {
                const rowSwitch = $(this).find('.row-iva-switch');
                if (rowSwitch.length && rowSwitch.is(':checkbox')) {
                    rowExempt = rowSwitch.is(':checked');
                }
            }

            if (!rowExempt) {
                taxableSubtotal += total;
            }
        });

        $('#grandTotal').text(symbol + subtotal.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}));

        const ivaVal = taxableSubtotal * 0.16;
        const grandTotalFinal = subtotal + ivaVal;

        $('#ivaVal').text(symbol + ivaVal.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
        $('#grandTotalFinal').text(symbol + grandTotalFinal.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}));

        if (ivaVal === 0) {
            $('#rowIva').hide();
            $('#rowIvaExempt').show();
            $('#rowIvaBs').hide();
        } else {
            $('#rowIva').show();
            $('#rowIvaExempt').hide();
            $('#rowIvaBs').show();
        }

        if (!isBs) {
            const subtotalBs = subtotal * exchangeRate;
            const ivaBs = ivaVal * exchangeRate;
            const totalBs = subtotalBs + ivaBs;

            $('#grandTotalBs').text('Bs ' + subtotalBs.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
            $('#ivaBs').text('Bs ' + ivaBs.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
            $('#totalBs').text('Bs ' + totalBs.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
        }
    }

    function recalcRowBase($row) {
        const qtyUom = parseFloat($row.find('.row-quantity-uom').val()) || 0;
        const costUom = parseFloat($row.find('.row-cost-uom').val()) || 0;
        const factor = parseFloat($row.find('.row-uom-selector option:selected').data('factor')) || 1.0;
        const baseUnit = $row.find('.row-base-unit-abbr').val() || 'und';

        const qtyBase = Math.round(qtyUom * factor);
        const costBase = factor > 0 ? (costUom / factor) : costUom;

        $row.find('.row-quantity-base').val(qtyBase);
        $row.find('.row-cost-base').val(costBase.toFixed(4));
        $row.find('.row-uom-label').text(`Equivale a ${qtyBase} ${baseUnit}`);
    }

    $(document).on('change', '.row-uom-selector', function() {
        const $row = $(this).closest('tr');
        recalcRowBase($row);
        calculateTotals();
    });

    $(document).on('input change', '.row-quantity-uom, .item-cost, .row-iva-switch, #iva_exempt, #currency, #exchangeRate', function() {
        const $row = $(this).closest('tr');
        if ($row.length) recalcRowBase($row);
        calculateTotals();
    });

    function initSelect2() {
        if ($('#currency').hasClass('select2-hidden-accessible')) {
            $('#currency').select2('destroy');
        }
        $('#currency').select2({
            theme: 'bootstrap4',
            width: '100%',
            minimumResultsForSearch: Infinity
        });

        $('.select2-ajax').each(function() {
            if ($(this).hasClass('select2-hidden-accessible')) {
                $(this).select2('destroy');
            }
            $(this).select2({
                theme: 'bootstrap4',
                width: '100%',
                allowClear: true,
                placeholder: $(this).data('placeholder') || 'Buscar...',
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
    }

    $(document).on('select2:open', function() {
        setTimeout(function() {
            const dropdown = document.querySelector('.select2-dropdown');
            if (dropdown) {
                dropdown.style.maxHeight = '350px';
                dropdown.style.overflow = 'hidden';
                const results = dropdown.querySelector('.select2-results');
                if (results) {
                    results.style.maxHeight = '350px';
                    results.style.overflowY = 'auto';
                }
            }
        }, 10);
    });

    $('#saveOrderBtn').on('click', function(e) {
        e.preventDefault();
        const $form = $('#orderForm');

        if (!$form[0].checkValidity()) {
            $form[0].reportValidity();
            return;
        }

        Swal.fire({
            type: 'question',
            title: 'Convertir RFQ a Orden de Compra',
            text: '¿Está seguro de generar esta orden de compra desde la RFQ?',
            showCancelButton: true,
            confirmButtonText: '<i class="fas fa-check mr-1"></i> Sí, crear orden',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#28a745',
        }).then(function(result) {
            if (result.isConfirmed || result.value) {
                $('#saveOrderBtn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Procesando...');
                HTMLFormElement.prototype.submit.call($form[0]);
            }
        });
    });

    $('#addSupplierBtn').on('click', function() {
        $('#supplierModal').modal('show');
    });

    $('#saveSupplierBtn').on('click', function(e) {
        e.preventDefault();
        const btn = $(this);
        const form = $('#supplierForm')[0];

        if (!form.checkValidity()) {
            form.reportValidity();
            return;
        }

        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Guardando...');

        $.ajax({
            url: '{{ route("admin.suppliers.quick-store") }}',
            method: 'POST',
            data: $('#supplierForm').serialize(),
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            success: function(response) {
                $('#supplierModal').modal('hide');
                $('#supplierForm')[0].reset();
                const newOption = new Option(
                    response.supplier.name + ' | ' + (response.supplier.email || 'Sin email'),
                    response.supplier.id, false, true
                );
                $('#supplier_id').append(newOption).trigger('change');
                Swal.fire({ type: 'success', title: '¡Éxito!', text: response.message, timer: 2000, showConfirmButton: false });
            },
            error: function(xhr) {
                if (xhr.status === 422) {
                    Swal.fire({ type: 'error', title: 'Error de validación', text: 'Verifique los campos del proveedor.' });
                } else {
                    Swal.fire({ type: 'error', title: 'Error', text: 'Hubo un error al guardar el proveedor.' });
                }
            },
            complete: function() {
                btn.prop('disabled', false).html('<i class="fas fa-save"></i> Guardar Proveedor');
            }
        });
    });

    $('#supplierModal').on('hidden.bs.modal', function() {
        $('#supplierForm')[0].reset();
        $('#supplierTaxIdError').hide();
    });

    $(function() {
        initSelect2();
        calculateTotals();

        // ═══════════════════════════════════════════════════════════════════════
        // DISPARADOR INICIAL (CONVERSIÓN UOM)
        // ═══════════════════════════════════════════════════════════════════════
        $('#itemsBody tr.item-row').each(function() {
            recalcRowBase($(this));
        });
    });
</script>
@endsection
