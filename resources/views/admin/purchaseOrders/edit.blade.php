@extends('adminlte::page')

@section('title', 'Editar Orden de Compra ' . $purchaseOrder->code)

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
        .sgci-qty-input {
            width: 65px !important;
            min-width: 65px;
            text-align: center;
            font-weight: 600;
        }
        .sgci-cost-input {
            width: 90px !important;
            min-width: 90px;
            text-align: right;
            font-weight: 600;
        }
        .sgci-uom-select {
            min-width: 70px;
            max-width: 95px;
            font-size: 0.8rem;
            border-top-left-radius: 0 !important;
            border-bottom-left-radius: 0 !important;
        }
        .sgci-type-select {
            width: 95px !important;
            min-width: 95px;
            font-size: 0.8rem;
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
        .sgci-form .item-selector + .select2-container,
        .sgci-form .kit-selector + .select2-container { width: 100% !important; }

        /* ── Filtro de Categoría ─────────────────────────────────── */
        .sgci-filter-bar {
            border-left: 3px solid var(--sgci-primary-light) !important;
            border-radius: var(--sgci-radius);
            box-shadow: var(--sgci-shadow);
            padding: 0.55rem 1rem;
            margin-bottom: 1.25rem;
            background: #fff;
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

{{-- ═══════════════════════════════════════════════════════════════════════════
     HEADER
     ═══════════════════════════════════════════════════════════════════════════ --}}
@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="mb-0 text-dark font-weight-bold" style="font-size: 1.55rem;">
            <i class="fas fa-edit mr-1" style="color:var(--sgci-primary);"></i>
            Editar Orden de Compra: {{ $purchaseOrder->code }}
        </h1>
        <div>
            <a href="{{ route('admin.purchaseOrders.show', $purchaseOrder) }}" class="btn btn-sm btn-outline-secondary font-weight-bold mr-1">
                <i class="fas fa-eye mr-1"></i> Ver Orden
            </a>
            <a href="{{ route('admin.purchaseOrders.index') }}" class="btn btn-sm btn-outline-secondary font-weight-bold">
                <i class="fas fa-arrow-left mr-1"></i> Volver al listado
            </a>
        </div>
    </div>
@stop

{{-- ═══════════════════════════════════════════════════════════════════════════
     CONTENT
     ═══════════════════════════════════════════════════════════════════════════ --}}
@section('content')
    @php
        $categories = \App\Models\Category::orderBy('name')->get();
        $units      = \App\Models\Unit::orderBy('name')->get();
        $locations  = \App\Models\Location::orderBy('name')->get();
        $brands     = \App\Models\Brand::orderBy('name')->get();
    @endphp

    <div class="container-fluid sgci-form">
        @include('admin.partials.session-messages')

        <form action="{{ route('admin.purchaseOrders.update', $purchaseOrder) }}" method="POST" id="orderForm">
            @csrf
            @method('PUT')

            <div class="row">
                {{-- ══════════════ COLUMNA PRINCIPAL (Izquierda) ══════════════ --}}
                <div class="col-lg-9 col-12">

                    {{-- CARD 1: Información General ──────────────────────────── --}}
                    <div class="sgci-card card">
                        <div class="card-header">
                            <h3><i class="fas fa-info-circle mr-1" style="color:var(--sgci-primary);"></i> Información General</h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group mb-3">
                                        <label class="sgci-label">Código OC</label>
                                        <div class="input-group input-group-sm">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text bg-light"><i class="fas fa-hashtag text-muted"></i></span>
                                            </div>
                                            <input type="text" name="code" class="form-control bg-light font-weight-bold" value="{{ $purchaseOrder->code }}" readonly>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-5">
                                    <div class="form-group mb-3">
                                        <label for="supplier_id" class="sgci-label">Proveedor <span class="text-danger">*</span></label>
                                        <div class="input-group input-group-sm">
                                            <div class="input-group-prepend">
                                                <button type="button" id="addSupplierBtn" class="btn btn-outline-primary" title="Crear Proveedor Rápido" style="border-top-left-radius: 6px; border-bottom-left-radius: 6px;">
                                                    <i class="fas fa-plus"></i>
                                                </button>
                                            </div>
                                            <select name="supplier_id" id="supplier_id" class="form-control select2-ajax" data-placeholder="Buscar proveedor..." required>
                                                <option value="">Seleccione un proveedor...</option>
                                                @foreach($suppliers as $supplier)
                                                    <option value="{{ $supplier->id }}" {{ old('supplier_id', $purchaseOrder->supplier_id) == $supplier->id ? 'selected' : '' }}>
                                                        {{ $supplier->name }} | {{ $supplier->email ?? 'Sin email' }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group mb-3">
                                        <label for="date_issued" class="sgci-label">Fecha de Emisión <span class="text-danger">*</span></label>
                                        <div class="input-group input-group-sm">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text bg-light"><i class="fas fa-calendar-alt text-muted"></i></span>
                                            </div>
                                            <input type="date" name="date_issued" id="date_issued" class="form-control" value="{{ old('date_issued', $purchaseOrder->date_issued->format('Y-m-d')) }}" required>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row border-top pt-3 mt-1">
                                <div class="col-md-4">
                                    <div class="form-group mb-3">
                                        <label for="delivery_date" class="sgci-label">Fecha de Entrega Estimada</label>
                                        <div class="input-group input-group-sm">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text bg-light"><i class="fas fa-calendar-check text-muted"></i></span>
                                            </div>
                                            <input type="date" name="delivery_date" id="delivery_date" class="form-control" value="{{ old('delivery_date', $purchaseOrder->delivery_date?->format('Y-m-d')) }}">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-8">
                                    <div class="form-group mb-3">
                                        <label for="delivery_address" class="sgci-label">Dirección de Entrega</label>
                                        <div class="input-group input-group-sm">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text bg-light"><i class="fas fa-map-marker-alt text-muted"></i></span>
                                            </div>
                                            <input type="text" name="delivery_address" id="delivery_address" class="form-control" value="{{ old('delivery_address', $purchaseOrder->delivery_address) }}" placeholder="Dirección de entrega de la mercancía (opcional)">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- FILTRO DE CATEGORÍA ──────────────────────────────────── --}}
                    @include('admin.partials.category-filter')

                    {{-- CARD 2: Productos de la Orden ────────────────────────── --}}
                    <div class="sgci-card card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h3><i class="fas fa-boxes mr-1" style="color:var(--sgci-primary);"></i> Productos de la Orden</h3>
                            <button type="button" class="btn btn-xs btn-success shadow-sm" id="addItemRowBtn">
                                <i class="fas fa-plus"></i> Añadir Ítem
                            </button>
                        </div>
                        <div class="card-body p-0">
                            <div class="sgci-table-wrapper">
                                <table class="sgci-items-table" id="itemsTable">
                                    <thead>
                                        <tr>
                                            <th style="width:12%;">Tipo</th>
                                            <th style="width:33%;">Producto / Kit <span class="text-danger">*</span></th>
                                            <th style="width:20%;">Cantidad <span class="text-danger">*</span></th>
                                            <th style="width:15%;">Costo Unit. <span class="text-danger">*</span></th>
                                            <th style="width:10%;" class="text-right">Total</th>
                                            <th style="width:6%;" class="text-center">¿Exento?</th>
                                            <th style="width:4%;" class="text-center"><i class="fas fa-cog"></i></th>
                                        </tr>
                                    </thead>
                                    <tbody id="itemsBody">
                                        @foreach($purchaseOrder->items as $index => $item)
                                            <tr class="item-row" data-index="{{ $index }}" @if($item->item_type === 'product' && $item->product) data-product-data="{{ json_encode(['id' => $item->product->id, 'text' => $item->product->name . ' (' . ($item->product->code ?? 'S/C') . ')', 'uoms' => $item->product->uoms ? $item->product->uoms->map(function($u){return ['uom_id'=>$u->pivot->uom_id,'factor'=>$u->pivot->factor];})->toArray() : []]) }}" @endif>
                                                <td>
                                                    <select name="items[{{ $index }}][item_type]" class="form-control form-control-sm sgci-type-select row-item-type">
                                                        <option value="product" {{ $item->item_type === 'product' ? 'selected' : '' }}>Producto</option>
                                                        <option value="kit" {{ $item->item_type === 'kit' ? 'selected' : '' }}>Kit</option>
                                                    </select>
                                                </td>
                                                <td>
                                                    {{-- Selector para Producto --}}
                                                    <div class="product-selector-wrapper" style="{{ $item->item_type === 'product' ? '' : 'display:none;' }}">
                                                        <input type="hidden" name="items[{{ $index }}][product_id]" class="row-product-id" value="{{ $item->product_id }}">
                                                        <select class="form-control form-control-sm item-selector" {{ $item->item_type === 'product' ? 'required' : '' }}>
                                                            @if($item->item_type === 'product' && $item->product)
                                                                <option value="{{ $item->product_id }}" selected>{{ $item->product->name }} ({{ $item->product->code ?? 'S/C' }})</option>
                                                            @else
                                                                <option value="">Seleccione producto...</option>
                                                            @endif
                                                        </select>
                                                    </div>
                                                    {{-- Selector para Kit --}}
                                                    <div class="kit-selector-wrapper" style="{{ $item->item_type === 'kit' ? '' : 'display:none;' }}">
                                                        <input type="hidden" name="items[{{ $index }}][kit_id]" class="row-kit-id" value="{{ $item->kit_id }}">
                                                        <select class="form-control form-control-sm kit-selector" {{ $item->item_type === 'kit' ? 'required' : '' }}>
                                                            <option value="">Seleccione un kit...</option>
                                                            @foreach($kits as $kit)
                                                                <option value="{{ $kit->id }}" {{ $item->kit_id == $kit->id ? 'selected' : '' }}>{{ $kit->name }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="input-group input-group-sm">
                                                        <input type="number" name="items[{{ $index }}][quantity_uom]" class="form-control sgci-qty-input row-quantity-uom" min="0.01" step="0.01" value="{{ old("items.$index.quantity_uom", $item->quantity_uom ?? $item->quantity) }}" required>
                                                        <input type="hidden" name="items[{{ $index }}][quantity]" class="row-quantity-base" value="{{ old("items.$index.quantity", $item->quantity) }}">
                                                        <div class="input-group-append">
                                                            <select name="items[{{ $index }}][uom_id]" class="form-control sgci-uom-select row-uom-selector">
                                                                @if($item->item_type === 'product' && $item->product)
                                                                    <option value="" data-factor="1.0" {{ !$item->uom_id ? 'selected' : '' }}>{{ $item->product->unit->abbreviation ?? 'und' }}</option>
                                                                    @foreach($item->product->uoms ?? [] as $uom)
                                                                        <option value="{{ $uom->id }}" data-factor="{{ $uom->pivot->factor }}" {{ $item->uom_id == $uom->id ? 'selected' : '' }}>{{ $uom->abbreviation }}</option>
                                                                    @endforeach
                                                                @else
                                                                    <option value="" data-factor="1.0" selected>und</option>
                                                                @endif
                                                            </select>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <input type="number" step="0.01" name="items[{{ $index }}][unit_cost_uom]" class="form-control form-control-sm sgci-cost-input row-cost-uom" min="0" value="{{ old("items.$index.unit_cost_uom", $item->unit_cost_uom ?? $item->unit_cost) }}" required>
                                                    <input type="hidden" name="items[{{ $index }}][unit_cost]" class="row-cost-base" value="{{ old("items.$index.unit_cost", $item->unit_cost) }}">
                                                </td>
                                                <td class="text-right align-middle font-weight-bold text-dark sgci-text-sm">
                                                    <span class="row-total-label">0.00</span>
                                                </td>
                                                <td class="text-center align-middle">
                                                    <input type="hidden" name="items[{{ $index }}][is_exempt]" value="0">
                                                    <div style="display: flex; justify-content: center; align-items: center;">
                                                        <input type="checkbox" name="items[{{ $index }}][is_exempt]" value="1" class="row-iva-switch" {{ $item->is_exempt ? 'checked' : '' }} style="width:18px;height:18px;cursor:pointer;">
                                                    </div>
                                                </td>
                                                <td class="text-center">
                                                    <button type="button" class="btn btn-sm btn-outline-danger remove-item" style="{{ $purchaseOrder->items->count() > 1 ? '' : 'display:none;' }}" title="Eliminar">
                                                        <i class="fas fa-trash-alt"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="card-footer bg-white border-top py-2 d-flex justify-content-between align-items-center">
                            <span class="sgci-text-xs text-muted">Modifique los productos y cantidades.</span>
                            <span class="sgci-text-sm font-weight-bold text-dark">Total ítems: <span id="totalItemsCount" class="font-weight-bold" style="color:var(--sgci-primary);">{{ $purchaseOrder->items->count() }}</span></span>
                        </div>
                    </div>

                    {{-- CARD 3: Términos y Notas ──────────────────────────────── --}}
                    <div class="sgci-card card">
                        <div class="card-header">
                            <h3><i class="fas fa-file-alt mr-1" style="color:var(--sgci-primary);"></i> Términos y Notas</h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-0">
                                        <label for="terms" class="sgci-label">Términos y Condiciones</label>
                                        <textarea name="terms" id="terms" rows="3" class="form-control form-control-sm" placeholder="Condiciones de pago, tiempo de entrega, garantías, etc. (se incluyen en el PDF)">{{ old('terms', $purchaseOrder->terms) }}</textarea>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-0">
                                        <label for="notes" class="sgci-label">Notas Internas</label>
                                        <textarea name="notes" id="notes" rows="3" class="form-control form-control-sm" placeholder="Notas de uso interno del departamento (no visibles en el PDF)">{{ old('notes', $purchaseOrder->notes) }}</textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

                {{-- ══════════════ COLUMNA LATERAL (Derecha) ══════════════ --}}
                <div class="col-lg-3 col-12">
                    <div class="sgci-card card sgci-sidebar-card" style="position:sticky;top:70px;">
                        <div class="sgci-sidebar-header">
                            <h3><i class="fas fa-coins mr-1"></i> Totales y Control</h3>
                        </div>
                        <div class="card-body">
                            {{-- Estatus --}}
                            <div class="mb-3">
                                <label class="sgci-label d-block">Estatus ODC</label>
                                <span class="sgci-status-badge badge badge-secondary shadow-sm">
                                    <i class="fas fa-file-invoice-dollar mr-1"></i> {!! strip_tags($purchaseOrder->status_badge) !!}
                                </span>
                            </div>

                            {{-- Configuración Financiera --}}
                            <div class="form-group mb-3">
                                <label for="currency" class="sgci-label">Moneda de Pago</label>
                                <select name="currency" id="currency" class="form-control form-control-sm select2" style="width:100%;">
                                    <option value="USD" {{ old('currency', $purchaseOrder->currency) == 'USD' ? 'selected' : '' }}>💵 USD - Dólar</option>
                                    <option value="EUR" {{ old('currency', $purchaseOrder->currency) == 'EUR' ? 'selected' : '' }}>💶 EUR - Euro</option>
                                    <option value="Bs" {{ old('currency', $purchaseOrder->currency) == 'Bs' ? 'selected' : '' }}>🇻🇪 Bs - Bolívar</option>
                                </select>
                            </div>

                            <div class="form-group mb-3" id="exchangeRateGroup">
                                <label for="exchangeRate" class="sgci-label">Tasa de Cambio (Bs / Divisa)</label>
                                <div class="input-group input-group-sm">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text bg-light"><i class="fas fa-sync text-muted"></i></span>
                                    </div>
                                    <input type="number" step="0.0001" name="exchange_rate" id="exchangeRate" class="form-control font-weight-bold" value="{{ old('exchange_rate', $purchaseOrder->exchange_rate) }}">
                                </div>
                            </div>

                            <div class="form-group mb-3">
                                <div class="custom-control custom-switch">
                                    <input type="checkbox" class="custom-control-input" id="iva_exempt" name="iva_exempt" value="1" {{ old('iva_exempt', $purchaseOrder->iva_exempt) ? 'checked' : '' }}>
                                    <label class="custom-control-label sgci-text-xs font-weight-bold text-secondary" for="iva_exempt" style="cursor:pointer;">
                                        Toda la Orden Exenta de IVA
                                    </label>
                                </div>
                            </div>

                            <hr class="my-3">

                            {{-- Desglose Totales Moneda Original --}}
                            <div class="sgci-total-row">
                                <span class="font-weight-bold text-secondary">Subtotal:</span>
                                <span id="grandTotal" class="font-weight-bold text-dark">$0.00</span>
                            </div>
                            <div class="sgci-total-row" id="rowIva">
                                <span class="font-weight-bold text-secondary">IVA (16%):</span>
                                <span id="ivaVal" class="font-weight-bold text-dark">$0.00</span>
                            </div>
                            <div class="sgci-total-row" id="rowIvaExempt" style="display:none;">
                                <span class="font-weight-bold text-secondary">IVA:</span>
                                <span class="badge badge-info">Exento</span>
                            </div>
                            <div class="sgci-total-row-grand">
                                <span class="sgci-text-sm">Total Orden:</span>
                                <span id="grandTotalFinal" class="text-primary">$0.00</span>
                            </div>

                            {{-- Equivalencia en Bolívares (VES) --}}
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
                                <div class="sgci-total-row bg-transparent py-1 border-0 sgci-total-row-grand pt-2 mt-1 border-top" style="font-size:0.95rem;">
                                    <span class="text-success font-weight-bold">Total VES:</span>
                                    <span id="totalBs" class="text-success font-weight-bold">Bs 0.00</span>
                                </div>
                            </div>

                            <hr class="my-3">

                            {{-- Botones de Acción --}}
                            <button type="submit" class="btn btn-primary btn-block shadow-sm mb-2" id="saveOrderBtn">
                                <i class="fas fa-save mr-1"></i> Actualizar Orden
                            </button>
                            <a href="{{ route('admin.purchaseOrders.show', $purchaseOrder) }}" class="btn btn-outline-danger btn-sm btn-block">
                                <i class="fas fa-times mr-1"></i> Cancelar
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

    {{-- ═══════════════════════════════════════════════════════════════════════
         MODAL: Creación Rápida de Proveedor
         ═══════════════════════════════════════════════════════════════════════ --}}
    <div class="modal fade" id="supplierModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content sgci-card" style="border-radius:var(--sgci-radius);">
                <div class="modal-header" style="background:linear-gradient(135deg,var(--sgci-primary),var(--sgci-primary-light));color:#fff;padding:.85rem 1.15rem;">
                    <h5 class="modal-title font-weight-bold" style="font-size:.95rem;">
                        <i class="fas fa-building mr-1"></i> Crear Nuevo Proveedor Rápido
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <form id="supplierForm">
                    <div class="modal-body py-4">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label class="sgci-label">Nombre Comercial / Razón Social <span class="text-danger">*</span></label>
                                    <input type="text" name="name" id="supplier_name" class="form-control form-control-sm" required placeholder="Ej. Corporación Médica C.A.">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label class="sgci-label">RIF / Tax ID <span class="text-danger">*</span></label>
                                    <input type="text" name="tax_id" id="supplier_tax_id" class="form-control form-control-sm" required placeholder="Ej. J-12345678-9">
                                    <small class="text-danger sgci-text-xs" id="supplierTaxIdError" style="display:none;">El RIF ya está registrado.</small>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label class="sgci-label">Email de Contacto</label>
                                    <input type="email" name="email" id="supplier_email" class="form-control form-control-sm" placeholder="Ej. ventas@proveedor.com">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label class="sgci-label">Teléfono</label>
                                    <input type="text" name="phone" id="supplier_phone" class="form-control form-control-sm" placeholder="Ej. 0212-5551234">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12">
                                <div class="form-group mb-0">
                                    <label class="sgci-label">Dirección Física</label>
                                    <textarea name="address" id="supplier_address" rows="2" class="form-control form-control-sm" placeholder="Dirección completa del proveedor..."></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-sm btn-outline-secondary" data-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-sm btn-primary" id="saveSupplierBtn">
                            <i class="fas fa-save"></i> Guardar Proveedor
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@stop

{{-- ═══════════════════════════════════════════════════════════════════════════
     JAVASCRIPT — Organizado en secciones funcionales
     ═══════════════════════════════════════════════════════════════════════════ --}}
@section('js')
    <script>
    (function() {
        'use strict';

        // ═══════════════════════════════════════════════════════════════════════
        // 1. DATA LAYER
        // ═══════════════════════════════════════════════════════════════════════

        const ALL_KITS = [
            @foreach($kits as $kit)
            { id: {{ $kit->id }}, name: @json($kit->name . ' [Kit]') },
            @endforeach
            @foreach($products as $product)
            @if($product->is_kit)
            { id: {{ $product->id }}, name: @json($product->name . ' (' . ($product->code ?? 'S/C') . ') [Kit]') },
            @endif
            @endforeach
        ];

        let itemIndex = {{ max($purchaseOrder->items->keys()->toArray()) + 1 }}; 
        let selectedCategoryId = null;

        // ═══════════════════════════════════════════════════════════════════════
        // 2. CASCADA: Categoría → Producto (AJAX) → UoM
        // ═══════════════════════════════════════════════════════════════════════

        function safeDestroySelect2($el) {
            if ($el.hasClass('select2-hidden-accessible')) {
                $el.select2('destroy');
            }
        }

        function initProductSelect2($select, enabled) {
            const currentVal = $select.val();
            const $row = $select.closest('tr');
            const productData = $row.data('product-data');

            safeDestroySelect2($select);
            $select.empty();

            $select.prop('disabled', !enabled);

            if (!enabled) {
                $select.html('<option value="">Seleccione una categoría primero...</option>');
                return;
            }

            if (currentVal && productData) {
                const optionText = productData.text || `${productData.name} (${productData.code || 'S/C'})`;
                const newOption = new Option(optionText, currentVal, true, true);
                $select.append(newOption);
            } else {
                $select.html('<option value=""></option>');
            }

            $select.select2({
                theme: 'bootstrap4',
                width: '100%',
                allowClear: true,
                placeholder: 'Seleccione un producto...',
                ajax: {
                    url: '{{ route("admin.products.search-ajax") }}',
                    dataType: 'json',
                    delay: 250,
                    data: function (params) {
                        return {
                            q: params.term,
                            page: params.page || 1,
                            category_id: selectedCategoryId
                        };
                    },
                    processResults: function (data, params) {
                        params.page = params.page || 1;
                        return {
                            results: data.results,
                            pagination: { more: data.pagination.more }
                        };
                    },
                    cache: true
                }
            });

            $select.off('select2:select.sgci').on('select2:select.sgci', function(e) {
                const data = e.params.data;
                const $r = $(this).closest('tr');
                $r.find('.row-product-id').val(data.id);
                $r.data('product-data', data);
                updateRowUom($r, data);
                calculateTotals();
            });

            $select.off('select2:clear.sgci').on('select2:clear.sgci', function() {
                const $r = $(this).closest('tr');
                $r.find('.row-product-id').val('');
                $r.removeData('product-data');
                updateRowUom($r, null);
                calculateTotals();
            });
        }

        function initKitSelect2($select) {
            $select.select2({
                theme: 'bootstrap4',
                width: '100%',
                allowClear: true,
                placeholder: 'Seleccione un kit...'
            });

            $select.off('change.sgci').on('change.sgci', function() {
                const $row = $(this).closest('tr');
                const val = $(this).val();
                $row.find('.row-kit-id').val(val);
                updateRowUom($row, null);
                calculateTotals();
            });
        }

        function cascadeApply() {
            const enabled = !!selectedCategoryId;
            const hint = document.getElementById('categoryFilterHint');

            $('#itemsBody tr.item-row').each(function() {
                const $typeSel = $(this).find('.row-item-type');
                if ($typeSel.val() !== 'product') return;

                const $select = $(this).find('.item-selector');
                initProductSelect2($select, enabled);
            });

            if (hint) {
                hint.style.display = enabled ? 'none' : 'block';
            }
        }

        function updateRowUom($row, data) {
            const $uomSelect = $row.find('.row-uom-selector');
            $uomSelect.empty();

            if (!data) {
                $uomSelect.append('<option value="" data-factor="1.0">und</option>');
                $uomSelect.prop('disabled', true);
                recalcRowBase($row);
                return;
            }

            const isKit = $row.find('.row-item-type').val() === 'kit';
            if (isKit) {
                $uomSelect.append('<option value="" data-factor="1.0">kit</option>');
                $uomSelect.prop('disabled', true);
                recalcRowBase($row);
                return;
            }

            $uomSelect.append(`<option value="" data-factor="1.0">${data.base_uom || 'und'}</option>`);

            if (data.uoms && data.uoms.length > 0) {
                data.uoms.forEach(u => {
                    const factorStr = u.pivot ? u.pivot.factor : u.factor;
                    $uomSelect.append(`<option value="${u.id || u.uom_id}" data-factor="${factorStr}">${u.abbreviation || u.text || 'UoM'}</option>`);
                });
                $uomSelect.prop('disabled', false);
            } else {
                $uomSelect.prop('disabled', true);
            }

            recalcRowBase($row);
        }

        function recalcRowBase($row) {
            const qtyUom = parseFloat($row.find('.row-quantity-uom').val()) || 0;
            const costUom = parseFloat($row.find('.row-cost-uom').val()) || 0;
            
            const $uomOpt = $row.find('.row-uom-selector option:selected');
            const factor = parseFloat($uomOpt.data('factor')) || 1.0;

            const qtyBase = qtyUom * factor;
            const costBase = costUom / factor;

            $row.find('.row-quantity-base').val(qtyBase);
            $row.find('.row-cost-base').val(costBase);
        }

        // ═══════════════════════════════════════════════════════════════════════
        // 3. CÁLCULO DE TOTALES
        // ═══════════════════════════════════════════════════════════════════════

        function getCurrencySymbol(c) {
            if (c === 'USD') return '$ ';
            if (c === 'EUR') return '€ ';
            if (c === 'Bs') return 'Bs ';
            return c + ' ';
        }

        function calculateTotals() {
            const currency = $('#currency').val() || 'USD';
            const rate = parseFloat($('#exchangeRate').val()) || 1.0;
            const sym = getCurrencySymbol(currency);
            const globalExempt = $('#iva_exempt').is(':checked');

            let subtotal = 0;
            let taxableSubtotal = 0;

            $('#itemsBody tr.item-row').each(function() {
                recalcRowBase($(this));
                
                const qUom = parseFloat($(this).find('.row-quantity-uom').val()) || 0;
                const cUom = parseFloat($(this).find('.row-cost-uom').val()) || 0;
                const totalRow = qUom * cUom;
                
                $(this).find('.row-total-label').text(totalRow.toLocaleString('en-US', {minimumFractionDigits:2, maximumFractionDigits:2}));
                subtotal += totalRow;

                let rowExempt = globalExempt;
                if (!rowExempt) {
                    rowExempt = $(this).find('.row-iva-switch').is(':checked');
                }

                if (!rowExempt) {
                    taxableSubtotal += totalRow;
                }
            });

            const iva = taxableSubtotal * 0.16;
            const total = subtotal + iva;

            $('#grandTotal').text(sym + subtotal.toLocaleString('en-US', {minimumFractionDigits:2}));
            $('#ivaVal').text(sym + iva.toLocaleString('en-US', {minimumFractionDigits:2}));
            $('#grandTotalFinal').text(sym + total.toLocaleString('en-US', {minimumFractionDigits:2}));

            if (iva === 0) {
                $('#rowIva').hide();
                $('#rowIvaExempt').show();
            } else {
                $('#rowIva').show();
                $('#rowIvaExempt').hide();
            }

            if (currency === 'Bs' || currency === 'VES') {
                $('#exchangeRateGroup, #bsEquivalentSection').hide();
            } else {
                $('#exchangeRateGroup, #bsEquivalentSection').show();
                $('#grandTotalBs').text('Bs ' + (subtotal * rate).toLocaleString('en-US', {minimumFractionDigits:2}));
                $('#ivaBs').text('Bs ' + (iva * rate).toLocaleString('en-US', {minimumFractionDigits:2}));
                $('#totalBs').text('Bs ' + (total * rate).toLocaleString('en-US', {minimumFractionDigits:2}));
            }
        }

        // ═══════════════════════════════════════════════════════════════════════
        // 4. EVENTOS DOM
        // ═══════════════════════════════════════════════════════════════════════

        $(document).on('click', '.category-btn', function() {
            $('.category-btn').removeClass('active');
            $(this).addClass('active');
            selectedCategoryId = $(this).data('id') || null;
            cascadeApply();
        });

        $(document).on('change', '.row-item-type', function() {
            const $row = $(this).closest('tr');
            const type = $(this).val();

            if (type === 'product') {
                $row.find('.kit-selector-wrapper').hide();
                $row.find('.kit-selector').prop('required', false).val('').trigger('change.sgci');
                $row.find('.row-kit-id').val('');

                $row.find('.product-selector-wrapper').show();
                $row.find('.item-selector').prop('required', true);
                initProductSelect2($row.find('.item-selector'), !!selectedCategoryId);
                updateRowUom($row, $row.data('product-data'));
            } else {
                $row.find('.product-selector-wrapper').hide();
                safeDestroySelect2($row.find('.item-selector'));
                $row.find('.item-selector').prop('required', false).empty();
                $row.find('.row-product-id').val('');
                $row.removeData('product-data');

                $row.find('.kit-selector-wrapper').show();
                $row.find('.kit-selector').prop('required', true);
                updateRowUom($row, null);
            }
            calculateTotals();
        });

        $('#addItemRowBtn').on('click', function() {
            const i = itemIndex++;
            const kitOpts = '<option value="">Seleccione un kit...</option>' + ALL_KITS.map(k => `<option value="${k.id}">${k.name}</option>`).join('');

            const rowHtml = `
                <tr class="item-row" data-index="${i}">
                    <td>
                        <select name="items[${i}][item_type]" class="form-control form-control-sm sgci-type-select row-item-type">
                            <option value="product" selected>Producto</option>
                            <option value="kit">Kit</option>
                        </select>
                    </td>
                    <td>
                        <div class="product-selector-wrapper">
                            <input type="hidden" name="items[${i}][product_id]" class="row-product-id" value="">
                            <select class="form-control form-control-sm item-selector" required disabled>
                                <option value="">Seleccione categoría...</option>
                            </select>
                        </div>
                        <div class="kit-selector-wrapper" style="display:none;">
                            <input type="hidden" name="items[${i}][kit_id]" class="row-kit-id" value="">
                            <select class="form-control form-control-sm kit-selector">${kitOpts}</select>
                        </div>
                    </td>
                    <td>
                        <div class="input-group input-group-sm">
                            <input type="number" name="items[${i}][quantity_uom]" class="form-control sgci-qty-input row-quantity-uom" min="0.01" step="0.01" value="1" required>
                            <input type="hidden" name="items[${i}][quantity]" class="row-quantity-base" value="1">
                            <div class="input-group-append">
                                <select name="items[${i}][uom_id]" class="form-control sgci-uom-select row-uom-selector" disabled>
                                    <option value="" data-factor="1.0">und</option>
                                </select>
                            </div>
                        </div>
                    </td>
                    <td>
                        <input type="number" step="0.01" name="items[${i}][unit_cost_uom]" class="form-control form-control-sm sgci-cost-input row-cost-uom" min="0" value="0.00" required>
                        <input type="hidden" name="items[${i}][unit_cost]" class="row-cost-base" value="0.00">
                    </td>
                    <td class="text-right align-middle font-weight-bold text-dark sgci-text-sm">
                        <span class="row-total-label">0.00</span>
                    </td>
                    <td class="text-center align-middle">
                        <input type="hidden" name="items[${i}][is_exempt]" value="0">
                        <input type="checkbox" name="items[${i}][is_exempt]" value="1" class="row-iva-switch" style="width:18px;height:18px;cursor:pointer;">
                    </td>
                    <td class="text-center">
                        <button type="button" class="btn btn-sm btn-outline-danger remove-item" title="Eliminar"><i class="fas fa-trash-alt"></i></button>
                    </td>
                </tr>
            `;

            $('#itemsBody').append(rowHtml);
            const $newRow = $('#itemsBody tr.item-row').last();
            
            initProductSelect2($newRow.find('.item-selector'), !!selectedCategoryId);
            initKitSelect2($newRow.find('.kit-selector'));
            updateRemoveButtons();
            
            const totalCount = $('#itemsBody tr.item-row').length;
            $('#totalItemsCount').text(totalCount);
        });

        $(document).on('click', '.remove-item', function() {
            $(this).closest('tr').remove();
            calculateTotals();
            updateRemoveButtons();
            $('#totalItemsCount').text($('#itemsBody tr.item-row').length);
        });

        $(document).on('input change', '.row-quantity-uom, .row-cost-uom, .row-uom-selector, .row-iva-switch, #iva_exempt, #currency, #exchangeRate', function() {
            calculateTotals();
        });

        function updateRemoveButtons() {
            const rows = $('#itemsBody tr.item-row').length;
            $('.remove-item').toggle(rows > 1);
        }

        // ═══════════════════════════════════════════════════════════════════════
        // 5. INICIALIZADORES GLOBALES
        // ═══════════════════════════════════════════════════════════════════════

        function initGlobalSelect2() {
            $('#currency').select2({ theme: 'bootstrap4', width: '100%', minimumResultsForSearch: Infinity });
            
            $('#supplier_id').select2({
                theme: 'bootstrap4', width: '100%', allowClear: true,
                placeholder: 'Buscar proveedor...',
                ajax: {
                    url: '{{ route("admin.purchaseOrders.searchSuppliers") }}',
                    dataType: 'json', delay: 250,
                    processResults: function(data) { return { results: data.results }; },
                    cache: true
                }
            });

            // Init selectores de tabla precargada
            $('#itemsBody tr.item-row').each(function() {
                const $row = $(this);
                const type = $row.find('.row-item-type').val();
                
                if (type === 'product') {
                    // Simular que el filtro general permite inicializar porque venían de BD
                    initProductSelect2($row.find('.item-selector'), true); 
                } else {
                    initKitSelect2($row.find('.kit-selector'));
                }
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

        // ═══════════════════════════════════════════════════════════════════════
        // 6. CONTROL DE ENVÍO
        // ═══════════════════════════════════════════════════════════════════════

        $('#orderForm').on('submit', function(e) {
            e.preventDefault();
            const $form = $(this);
            
            const btn = $('#saveOrderBtn');
            const icon = btn.find('i').attr('class');
            const text = btn.text();
            
            Swal.fire({
                type: 'question',
                title: 'Actualizar Orden de Compra',
                text: '¿Está seguro de actualizar los datos de esta Orden de Compra?',
                showCancelButton: true,
                confirmButtonColor: '#28a745',
                cancelButtonColor: '#6c757d',
                confirmButtonText: '<i class="fas fa-check mr-1"></i> Sí, actualizar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed || result.value) {
                    btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Procesando...');
                    HTMLFormElement.prototype.submit.call($form[0]);
                }
            });
        });

        // Modal proveedor
        $('#addSupplierBtn').on('click', function() { $('#supplierModal').modal('show'); });
        
        $('#supplierForm').on('submit', function(e) {
            e.preventDefault();
            const btn = $('#saveSupplierBtn');
            btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Guardando...');
            
            $.ajax({
                url: '{{ route("admin.suppliers.quick-store") }}',
                method: 'POST',
                data: $(this).serialize(),
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
                    btn.prop('disabled', false).html('<i class="fas fa-save mr-1"></i> Guardar Proveedor');
                }
            });
        });

        $('#supplierModal').on('hidden.bs.modal', function() {
            $('#supplierForm')[0].reset();
            $('#supplierTaxIdError').hide();
        });

        // ═══════════════════════════════════════════════════════════════════════
        // INIT
        // ═══════════════════════════════════════════════════════════════════════
        $(function() {
            initGlobalSelect2();
            calculateTotals();
            updateRemoveButtons();
        });
    })();
    </script>
@endsection
