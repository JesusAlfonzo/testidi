@extends('adminlte::page')

@section('title', 'Crear Solicitud de Cotización')

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

        /* ── Input de cantidad ───────────────────────────────────── */
        .sgci-qty-input {
            width: 72px !important;
            min-width: 72px;
            text-align: center;
            font-weight: 600;
        }
        .sgci-uom-select {
            min-width: 78px;
            max-width: 110px;
            font-size: 0.8rem;
            border-top-left-radius: 0 !important;
            border-bottom-left-radius: 0 !important;
        }

        /* ── Select2 ────────────────────────────────────────────── */
        .sgci-form .select2-container--bootstrap4 .select2-selection--single {
            height: 34px !important;
            padding-top: 3px;
            border-color: var(--sgci-border);
            border-radius: 6px;
        }
        .sgci-form .select2-container--bootstrap4 .select2-selection--single .select2-selection__arrow {
            height: 32px;
        }
        .sgci-form .item-selector + .select2-container { width: 100% !important; }

        /* ── Filtro de Categoría (override del partial) ──────────── */
        .sgci-filter-bar {
            border-left: 3px solid var(--sgci-primary-light) !important;
            border-radius: var(--sgci-radius);
            box-shadow: var(--sgci-shadow);
            padding: 0.55rem 1rem;
            margin-bottom: 1.25rem;
            background: #fff;
        }

        /* ── Labels ─────────────────────────────────────────────── */
        .sgci-label {
            font-size: 0.72rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            color: #94a3b8;
            margin-bottom: 0.3rem;
        }

        /* ── Badge de Estado ─────────────────────────────────────── */
        .sgci-status-badge {
            display: block;
            text-align: center;
            padding: 0.45rem 0;
            border-radius: 6px;
            font-size: 0.82rem;
            font-weight: 700;
        }

        /* ── Validación visual ────────────────────────────────────── */
        .sgci-form select.is-invalid + .select2-container .select2-selection {
            border-color: #dc3545 !important;
        }

        /* ── Switch Custom ────────────────────────────────────────── */
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
        <h1 class="mb-0" style="font-size:1.35rem;">
            <i class="fas fa-file-invoice" style="color:var(--sgci-primary);"></i>
            Nueva Solicitud de Cotización (SDC)
        </h1>
        <a href="{{ route('admin.rfq.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="fas fa-arrow-left mr-1"></i> Volver
        </a>
    </div>
@stop

{{-- ═══════════════════════════════════════════════════════════════════════════
     CONTENT
     ═══════════════════════════════════════════════════════════════════════════ --}}
@section('content')
    @php
        // Datos auxiliares para el modal de creación rápida
        $units      = \App\Models\Unit::orderBy('name')->get();
        $locations  = \App\Models\Location::orderBy('name')->get();
        $brands     = \App\Models\Brand::orderBy('name')->get();
    @endphp

    <div class="container-fluid sgci-form">
        @include('admin.partials.session-messages')

        <form action="{{ route('admin.rfq.store') }}" method="POST" id="rfqForm">
            @csrf

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
                                        <label class="sgci-label">Código Correlativo</label>
                                        <div class="input-group input-group-sm">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text bg-light"><i class="fas fa-hashtag text-muted"></i></span>
                                            </div>
                                            <input type="text" name="code" class="form-control bg-light font-weight-bold" value="{{ $code }}" readonly>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group mb-3">
                                        <label class="sgci-label">Fecha de Emisión</label>
                                        <div class="input-group input-group-sm">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text bg-light"><i class="fas fa-calendar-alt text-muted"></i></span>
                                            </div>
                                            <input type="text" class="form-control bg-light" value="{{ date('d/m/Y') }}" disabled>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group mb-3">
                                        <label class="sgci-label">Departamento</label>
                                        <div class="input-group input-group-sm">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text bg-light"><i class="fas fa-building text-muted"></i></span>
                                            </div>
                                            <input type="text" class="form-control bg-light" value="Compras y Logística" disabled>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group mb-3">
                                <label for="title" class="sgci-label">Título / Asunto <span class="text-danger">*</span></label>
                                <input type="text" name="title" id="title" class="form-control form-control-sm @error('title') is-invalid @enderror" value="{{ old('title') }}" placeholder="Ej. Adquisición de repuestos para planta eléctrica" required>
                                @error('title')<span class="invalid-feedback sgci-text-xs">{{ $message }}</span>@enderror
                            </div>
                            <div class="form-group mb-0">
                                <label for="description" class="sgci-label">Descripción General</label>
                                <textarea name="description" id="description" rows="2" class="form-control form-control-sm" placeholder="Instrucciones para los proveedores (opcional)">{{ old('description') }}</textarea>
                            </div>
                        </div>
                    </div>

                    {{-- FILTRO DE CATEGORÍA ──────────────────────────────────── --}}
                    @include('admin.partials.category-filter')

                    {{-- CARD 2: Ítems a Cotizar ─────────────────────────────── --}}
                    <div class="sgci-card card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h3><i class="fas fa-list-ul mr-1" style="color:var(--sgci-primary);"></i> Ítems a Cotizar</h3>
                            <div>
                                <button type="button" class="btn btn-xs btn-primary shadow-sm mr-1" id="quickNewItemBtn">
                                    <i class="fas fa-plus-circle"></i> + Nuevo Ítem
                                </button>
                                <button type="button" class="btn btn-xs btn-success shadow-sm" id="addItemRowBtn">
                                    <i class="fas fa-plus"></i> Añadir Fila
                                </button>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="sgci-items-table" id="itemsTable">
                                    <thead>
                                        <tr>
                                            <th style="width:42%;">Producto / Kit <span class="text-danger">*</span></th>
                                            <th style="width:8%;" class="text-center">¿Exento?</th>
                                            <th style="width:22%;">Cantidad <span class="text-danger">*</span></th>
                                            <th style="width:22%;">Notas / Especificaciones</th>
                                            <th style="width:6%;" class="text-center"><i class="fas fa-cog"></i></th>
                                        </tr>
                                    </thead>
                                    <tbody id="itemsBody">
                                        {{-- Fila inicial (índice 0) --}}
                                        <tr class="item-row" data-index="0">
                                            <td>
                                                <input type="hidden" name="items[0][item_type]" class="row-item-type" value="product">
                                                <input type="hidden" name="items[0][product_id]" class="row-product-id" value="">
                                                <input type="hidden" name="items[0][kit_id]" class="row-kit-id" value="">
                                                <select class="form-control form-control-sm item-selector" required disabled>
                                                    <option value="">Seleccione una categoría primero...</option>
                                                </select>
                                            </td>
                                            <td class="text-center">
                                                <input type="hidden" name="items[0][is_exempt]" value="0">
                                                <input type="checkbox" name="items[0][is_exempt]" value="1" class="row-iva-switch" style="width:18px;height:18px;cursor:pointer;">
                                            </td>
                                            <td>
                                                <div class="input-group input-group-sm">
                                                    <input type="number" name="items[0][quantity_uom]" class="form-control sgci-qty-input row-quantity-uom" min="1" value="1" required>
                                                    <input type="hidden" name="items[0][quantity]" class="row-quantity-base" value="1">
                                                    <input type="hidden" class="row-base-unit-abbr" value="und">
                                                    <div class="input-group-append">
                                                        <select name="items[0][uom_id]" class="form-control sgci-uom-select row-uom-selector" disabled>
                                                            <option value="" data-factor="1.0">und</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <small class="text-muted d-block mt-1 row-uom-label" style="font-size: 0.75rem; font-weight: 600; text-align: left;"></small>
                                            </td>
                                            <td>
                                                <input type="text" name="items[0][notes]" class="form-control form-control-sm" placeholder="Especificaciones...">
                                            </td>
                                            <td class="text-center">
                                                <button type="button" class="btn btn-sm btn-outline-danger remove-item" style="display:none;" title="Eliminar">
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="card-footer bg-white border-top py-2 d-flex justify-content-between align-items-center">
                            <span class="sgci-text-xs text-muted">Agregue al menos un ítem al listado.</span>
                            <span class="sgci-text-sm font-weight-bold text-dark">Total: <span id="totalItemsCount" class="font-weight-bold" style="color:var(--sgci-primary);">1</span></span>
                        </div>
                    </div>

                    {{-- CARD 3: Notas Internas ──────────────────────────────── --}}
                    <div class="sgci-card card">
                        <div class="card-header">
                            <h3><i class="fas fa-comment-dots mr-1" style="color:var(--sgci-primary);"></i> Notas Internas</h3>
                        </div>
                        <div class="card-body">
                            <textarea name="notes" id="notes" rows="2" class="form-control form-control-sm" placeholder="Notas visibles solo para administradores (no se incluyen en el PDF)">{{ old('notes') }}</textarea>
                        </div>
                    </div>

                </div>

                {{-- ══════════════ COLUMNA LATERAL (Derecha) ══════════════ --}}
                <div class="col-lg-3 col-12">
                    <div class="sgci-card card sgci-sidebar-card" style="position:sticky;top:70px;">
                        <div class="sgci-sidebar-header">
                            <h3><i class="fas fa-sliders-h mr-1"></i> Panel de Control</h3>
                        </div>
                        <div class="card-body">
                            {{-- Estado --}}
                            <div class="mb-3">
                                <label class="sgci-label d-block">Estatus SDC</label>
                                <span class="sgci-status-badge badge badge-secondary shadow-sm">
                                    <i class="fas fa-file-alt mr-1"></i> BORRADOR
                                </span>
                            </div>

                            {{-- Prioridad --}}
                            <div class="form-group mb-3">
                                <label for="priority" class="sgci-label">Prioridad</label>
                                <select name="priority" id="priority" class="form-control form-control-sm select2" style="width:100%;">
                                    <option value="baja" selected>🟢 Baja</option>
                                    <option value="media">🟡 Media</option>
                                    <option value="alta">🔴 Alta</option>
                                </select>
                            </div>

                            <hr class="my-3">

                            {{-- Fechas --}}
                            <div class="form-group mb-3">
                                <label for="date_required" class="sgci-label">F. Límite de Respuesta</label>
                                <div class="input-group input-group-sm">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text bg-light"><i class="fas fa-calendar-times text-danger"></i></span>
                                    </div>
                                    <input type="date" name="date_required" id="date_required" class="form-control" value="{{ old('date_required') }}">
                                </div>
                            </div>
                            <div class="form-group mb-4">
                                <label for="delivery_deadline" class="sgci-label">F. Límite de Entrega</label>
                                <div class="input-group input-group-sm">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text bg-light"><i class="fas fa-truck-loading" style="color:var(--sgci-primary);"></i></span>
                                    </div>
                                    <input type="date" name="delivery_deadline" id="delivery_deadline" class="form-control" value="{{ old('delivery_deadline') }}">
                                </div>
                            </div>

                            {{-- Botones --}}
                            <button type="button" class="btn btn-primary btn-block shadow-sm mb-2" id="saveRfqBtn">
                                <i class="fas fa-save mr-1"></i> Guardar Borrador
                            </button>
                            <button type="button" class="btn btn-outline-secondary btn-block btn-sm mb-3" disabled title="Guarde primero para descargar PDF">
                                <i class="fas fa-file-pdf mr-1"></i> Generar PDF
                            </button>
                            <a href="{{ route('admin.rfq.index') }}" class="btn btn-outline-danger btn-sm btn-block">
                                <i class="fas fa-times mr-1"></i> Cancelar
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

    {{-- ═══════════════════════════════════════════════════════════════════════
         MODAL: Creación Rápida de Producto/Kit
         ═══════════════════════════════════════════════════════════════════════ --}}
    <div class="modal fade" id="quickItemModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content sgci-card" style="border-radius:var(--sgci-radius);">
                <div class="modal-header" style="background:linear-gradient(135deg,var(--sgci-primary),var(--sgci-primary-light));color:#fff;padding:.85rem 1.15rem;">
                    <h5 class="modal-title font-weight-bold" style="font-size:.95rem;">
                        <i class="fas fa-plus-circle mr-1"></i> Crear Nuevo Ítem Rápido
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <form id="quickItemForm">
                    <div class="modal-body py-4">
                        {{-- Toggle Kit --}}
                        <div class="text-center mb-4">
                            <div class="custom-control custom-switch d-inline-block">
                                <input type="checkbox" class="custom-control-input" id="modal_is_kit" name="is_kit" value="1">
                                <label class="custom-control-label font-weight-bold" for="modal_is_kit">
                                    <i class="fas fa-cubes text-info"></i> Definir como Kit / Compuesto
                                </label>
                            </div>
                            <div class="sgci-text-xs text-muted mt-1">Marque si desea asociar múltiples componentes hijos.</div>
                        </div>

                        <div class="row">
                            {{-- Columna Izquierda --}}
                            <div class="col-md-6 border-right">
                                <h6 class="font-weight-bold mb-3 border-bottom pb-2" style="color:var(--sgci-primary);font-size:.85rem;">Información Básica</h6>
                                <div class="form-group">
                                    <label class="sgci-label">Código/SKU <span class="text-danger">*</span></label>
                                    <input type="text" name="code" id="modal_code" class="form-control form-control-sm" required placeholder="Ej. PROD-0023">
                                    <small class="text-danger sgci-text-xs" id="modalCodeError" style="display:none;">El código ya existe.</small>
                                </div>
                                <div class="form-group">
                                    <label class="sgci-label">Nombre <span class="text-danger">*</span></label>
                                    <input type="text" name="name" id="modal_name" class="form-control form-control-sm" required placeholder="Ej. Válvula de compresión">
                                </div>
                                <div class="form-group">
                                    <label class="sgci-label">Unidad de Medida <span class="text-danger">*</span></label>
                                    <select name="unit_id" id="modal_unit_id" class="form-control select2" required style="width:100%;">
                                        <option value="">Seleccione...</option>
                                        @foreach($units as $unit)
                                            <option value="{{ $unit->id }}">{{ $unit->name }} ({{ $unit->abbreviation }})</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label class="sgci-label">Categoría <span class="modal-generic-marker text-danger">*</span></label>
                                    <select name="category_id" id="modal_category_id" class="form-control select2" required style="width:100%;">
                                        <option value="">Seleccione...</option>
                                        @foreach($categories as $category)
                                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group mb-0">
                                    <label class="sgci-label">Ubicación <span class="modal-generic-marker text-danger">*</span></label>
                                    <select name="location_id" id="modal_location_id" class="form-control select2" required style="width:100%;">
                                        <option value="">Seleccione...</option>
                                        @foreach($locations as $location)
                                            <option value="{{ $location->id }}">{{ $location->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            {{-- Columna Derecha --}}
                            <div class="col-md-6">
                                <h6 class="font-weight-bold mb-3 border-bottom pb-2" style="color:var(--sgci-primary);font-size:.85rem;">Configuraciones</h6>
                                <div class="form-group mb-2">
                                    <label class="sgci-label">Marca</label>
                                    <select name="brand_id" id="modal_brand_id" class="form-control select2" style="width:100%;">
                                        <option value="">Seleccione...</option>
                                        @foreach($brands as $brand)
                                            <option value="{{ $brand->id }}">{{ $brand->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group mb-3">
                                    <div class="custom-control custom-switch mt-2">
                                        <input type="hidden" name="is_generic" value="0">
                                        <input type="checkbox" class="custom-control-input" id="modal_is_generic" name="is_generic" value="1">
                                        <label class="custom-control-label sgci-text-xs font-weight-bold" for="modal_is_generic">
                                            <i class="fas fa-cubes text-info mr-1"></i> Producto Genérico
                                        </label>
                                    </div>
                                    <small class="form-text text-muted sgci-text-xs">Marca, categoría y ubicación serán opcionales.</small>
                                </div>

                                {{-- Detalles de Producto Individual --}}
                                <div id="productDetailsSection">
                                    <div class="row">
                                        <div class="col-6">
                                            <div class="form-group">
                                                <label class="sgci-label">Costo ($)</label>
                                                <input type="number" step="0.01" name="cost" id="modal_cost" class="form-control form-control-sm" value="0.00" min="0">
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="form-group">
                                                <label class="sgci-label">Precio Venta ($)</label>
                                                <input type="number" step="0.01" name="price" id="modal_price" class="form-control form-control-sm" value="0.00" min="0">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="sgci-label">Stock Mínimo</label>
                                        <input type="number" name="min_stock" id="modal_min_stock" class="form-control form-control-sm" value="0" min="0">
                                    </div>
                                    <div class="form-group">
                                        <label class="sgci-label">Descripción</label>
                                        <textarea name="description" id="modal_description" rows="2" class="form-control form-control-sm" placeholder="Opcional"></textarea>
                                    </div>
                                </div>

                                {{-- Detalles de Kit --}}
                                <div id="kitDetailsSection" style="display:none;">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <label class="sgci-label mb-0"><i class="fas fa-boxes text-warning mr-1"></i> Componentes del Kit</label>
                                        <button type="button" class="btn btn-xs btn-outline-warning" id="addModalComponentBtn">
                                            <i class="fas fa-plus"></i> Componente
                                        </button>
                                    </div>
                                    <div style="max-height:200px;overflow-y:auto;" class="border rounded p-2 bg-light mb-2">
                                        <table class="table table-sm table-borderless mb-0" id="modalComponentsTable">
                                            <thead>
                                                <tr class="sgci-text-xs text-muted border-bottom">
                                                    <th>Item</th>
                                                    <th style="width:90px;">Cant.</th>
                                                    <th style="width:35px;"></th>
                                                </tr>
                                            </thead>
                                            <tbody id="modalComponentsBody"></tbody>
                                        </table>
                                        <div id="noComponentsPlaceholder" class="text-center text-muted sgci-text-xs py-3">
                                            No hay componentes añadidos.
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="sgci-label">Costo Estimado del Kit ($)</label>
                                        <input type="number" step="0.01" name="cost" id="modal_kit_cost" class="form-control form-control-sm" value="0.00" min="0">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-sm btn-outline-secondary" data-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-sm btn-primary" id="saveQuickItemBtn">
                            <i class="fas fa-save"></i> Guardar Ítem
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Fuente de datos oculta para autocompletar componentes de kit --}}
    <div style="display:none;" id="modal_simple_products_source">
        <option value="">Seleccione...</option>
        @foreach($products as $product)
            @if(!$product->is_kit)
                <option value="{{ $product->id }}">{{ $product->name }} ({{ $product->code ?? 'S/C' }})</option>
            @endif
        @endforeach
    </div>
@stop

{{-- ═══════════════════════════════════════════════════════════════════════════
     JAVASCRIPT — Organizado en secciones funcionales
     ═══════════════════════════════════════════════════════════════════════════ --}}
@section('js')
    <script>
    (function() {
        'use strict';

        let itemIndex = 1;            // Siguiente índice de fila
        let selectedCategoryId = null; // Categoría activa del filtro
        let activeRowForModal = null;  // Fila que abrió el modal de creación rápida

        // ═══════════════════════════════════════════════════════════════════════
        // 2. CASCADA: Categoría → Producto (AJAX) → UoM
        // ═══════════════════════════════════════════════════════════════════════

        function safeDestroySelect2($el) {
            if ($el.hasClass('select2-hidden-accessible')) {
                $el.select2('destroy');
            }
        }

        /**
         * Inicializa Select2 con soporte AJAX y Paginación para Productos en SDC.
         */
        function initItemSelect2($select, enabled) {
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

            // Preservar la opción seleccionada si ya existe
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
                placeholder: 'Seleccione producto o kit...',
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
                            pagination: {
                                more: data.pagination.more
                            }
                        };
                    },
                    cache: true
                }
            });

            // Registrar listeners
            $select.off('select2:select.sgci').on('select2:select.sgci', function(e) {
                const data = e.params.data;
                const $r = $(this).closest('tr');
                $r.find('.row-product-id').val(data.id);
                $r.data('product-data', data);
                updateRowUom($r, data);
            });

            $select.off('select2:clear.sgci').on('select2:clear.sgci', function() {
                const $r = $(this).closest('tr');
                $r.find('.row-product-id').val('');
                $r.removeData('product-data');
                updateRowUom($r, null);
            });
        }

        /**
         * Aplica la cascada de categoría a todos los selectores de productos individuales.
         */
        function cascadeApply() {
            const enabled = !!selectedCategoryId;
            const hint = document.getElementById('categoryFilterHint');

            $('#itemsBody tr.item-row').each(function() {
                const $select = $(this).find('.item-selector');
                initItemSelect2($select, enabled);
            });

            if (hint) {
                hint.innerHTML = enabled
                    ? '<i class="fas fa-check-circle text-success"></i> Mostrando productos de la categoría seleccionada.'
                    : '<i class="fas fa-info-circle"></i> Seleccione una categoría para habilitar el selector de productos.';
            }
        }

        // ═══════════════════════════════════════════════════════════════════════
        // 3. UoM — Poblado dinámico del selector de Unidad de Medida
        // ═══════════════════════════════════════════════════════════════════════

        function updateRowUom($row, product) {
            const isKit = $row.find('.row-item-type').val() === 'kit';
            const $uom = $row.find('.row-uom-selector');

            if (isKit) {
                $uom.html('<option value="" data-factor="1.0">und</option>').val('').prop('disabled', true);
                recalcBase($row);
                return;
            }

            product = product || $row.data('product-data');
            if (product) {
                const unitId = product.unitId || product.unit_id;
                const unitAbbr = product.unit;
                const conversions = product.conversions || [];

                let html = `<option value="${unitId}" data-factor="1.0" selected>${unitAbbr}</option>`;
                conversions.forEach(c => {
                    if (c.id != unitId) {
                        html += `<option value="${c.id}" data-factor="${c.factor}">${c.name}</option>`;
                    }
                });
                $row.find('.row-base-unit-abbr').val(product.unit || 'und');
                $uom.html(html).prop('disabled', false);
            } else {
                $row.find('.row-base-unit-abbr').val('und');
                $uom.html('<option value="" data-factor="1.0">und</option>').val('').prop('disabled', true);
            }
            recalcBase($row);

            recalcBase($row);
        }

        // ═══════════════════════════════════════════════════════════════════════
        // 4. CÁLCULOS — Conversión de cantidad UoM → cantidad base
        // ═══════════════════════════════════════════════════════════════════════

        function recalcBase($row) {
            const qtyUom = parseFloat($row.find('.row-quantity-uom').val()) || 0;
            const factor = parseFloat($row.find('.row-uom-selector option:selected').data('factor')) || 1.0;
            const baseUnit = $row.find('.row-base-unit-abbr').val() || 'und';
            const totalBase = Math.round(qtyUom * factor);
            
            $row.find('.row-quantity-base').val(totalBase);
            $row.find('.row-uom-label').text(`Equivale a ${totalBase} ${baseUnit}`);
        }

        // ═══════════════════════════════════════════════════════════════════════
        // 5. TABLA DINÁMICA — Agregar y eliminar filas de ítems
        // ═══════════════════════════════════════════════════════════════════════

        function addItemRow() {
            if (!selectedCategoryId) {
                Swal.fire({ icon: 'info', title: 'Seleccione una categoría', text: 'Elija primero una categoría en el filtro para agregar filas.' });
                return;
            }

            const html = `
                <tr class="item-row" data-index="${itemIndex}">
                    <td>
                        <input type="hidden" name="items[${itemIndex}][item_type]" class="row-item-type" value="product">
                        <input type="hidden" name="items[${itemIndex}][product_id]" class="row-product-id" value="">
                        <input type="hidden" name="items[${itemIndex}][kit_id]" class="row-kit-id" value="">
                        <select class="form-control form-control-sm item-selector" required>
                            <option value=""></option>
                        </select>
                    </td>
                    <td class="text-center">
                        <input type="hidden" name="items[${itemIndex}][is_exempt]" value="0">
                        <input type="checkbox" name="items[${itemIndex}][is_exempt]" value="1" class="row-iva-switch" style="width:18px;height:18px;cursor:pointer;">
                    </td>
                    <td>
                        <div class="input-group input-group-sm">
                            <input type="number" name="items[${itemIndex}][quantity_uom]" class="form-control sgci-qty-input row-quantity-uom" min="1" value="1" required>
                            <input type="hidden" name="items[${itemIndex}][quantity]" class="row-quantity-base" value="1">
                            <div class="input-group-append">
                                <select name="items[${itemIndex}][uom_id]" class="form-control sgci-uom-select row-uom-selector" disabled>
                                    <option value="" data-factor="1.0">und</option>
                                </select>
                            </div>
                        </div>
                    </td>
                    <td>
                        <input type="text" name="items[${itemIndex}][notes]" class="form-control form-control-sm" placeholder="Especificaciones...">
                    </td>
                    <td class="text-center">
                        <button type="button" class="btn btn-sm btn-outline-danger remove-item" title="Eliminar">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                    </td>
                </tr>
            `;

            $('#itemsBody').append(html);
            const $newRow = $('#itemsBody tr.item-row:last-child');
            initItemSelect2($newRow.find('.item-selector'), true);
            itemIndex++;
            refreshUI();
        }

        function refreshUI() {
            const count = $('#itemsBody tr.item-row').length;
            $('#itemsBody tr.item-row .remove-item').toggle(count > 1);
            $('#totalItemsCount').text(count);
        }

        // ═══════════════════════════════════════════════════════════════════════
        // 6. MODAL PRODUCTO RÁPIDO — AJAX para crear productos/kits al vuelo
        // ═══════════════════════════════════════════════════════════════════════

        function toggleModalKitFields() {
            const isKit = $('#modal_is_kit').is(':checked');
            if (isKit) {
                $('#productDetailsSection').slideUp(200);
                $('#kitDetailsSection').slideDown(200);
                $('#modal_price').prop('required', false);
            } else {
                $('#kitDetailsSection').slideUp(200);
                $('#productDetailsSection').slideDown(200);
            }
        }

        function toggleModalGenericFields() {
            const isGeneric = $('#modal_is_generic').is(':checked');
            if (isGeneric) {
                $('#modal_brand_id').closest('.form-group').slideUp(200);
                $('#modal_category_id, #modal_location_id').closest('.form-group').find('select').prop('required', false);
                $('.modal-generic-marker').fadeOut(200);
            } else {
                $('#modal_brand_id').closest('.form-group').slideDown(200);
                $('#modal_category_id, #modal_location_id').closest('.form-group').find('select').prop('required', true);
                $('.modal-generic-marker').fadeIn(200);
            }
        }

        let modalComponentIndex = 0;

        function addModalComponentRow() {
            const options = $('#modal_simple_products_source').html();
            const row = `
                <tr class="modal-comp-row">
                    <td><select name="components[${modalComponentIndex}][child_id]" class="form-control form-control-sm select2-modal-comp" required>${options}</select></td>
                    <td><input type="number" name="components[${modalComponentIndex}][quantity]" class="form-control form-control-sm text-center" min="1" value="1" required></td>
                    <td class="text-center"><button type="button" class="btn btn-sm btn-link text-danger remove-modal-comp"><i class="fas fa-times-circle"></i></button></td>
                </tr>
            `;
            $('#modalComponentsBody').append(row);
            $('#modalComponentsBody tr:last-child .select2-modal-comp').select2({
                theme: 'bootstrap4', width: '100%', dropdownParent: $('#quickItemModal')
            });
            modalComponentIndex++;
            $('#noComponentsPlaceholder').hide();
        }

        // ═══════════════════════════════════════════════════════════════════════
        // 7. INICIALIZACIÓN — document.ready
        // ═══════════════════════════════════════════════════════════════════════

        $(document).ready(function() {

            // -- Inicializar Select2 en los selects simples del sidebar y modal --
            $('.select2').each(function() {
                if (!$(this).hasClass('select2-hidden-accessible')) {
                    $(this).select2({ theme: 'bootstrap4', width: '100%', allowClear: true });
                }
            });

            // -- Cascada: estado inicial (selector deshabilitado) --
            cascadeApply();

            // -- Cascada: reaccionar a cambio de categoría --
            $('#categoryFilter').on('change', function() {
                selectedCategoryId = $(this).val() || null;
                cascadeApply();
            });

            // -- Tabla: agregar fila --
            $('#addItemRowBtn').on('click', addItemRow);

            // -- Tabla: eliminar fila (delegación) --
            $(document).on('click', '.remove-item', function() {
                $(this).closest('tr').remove();
                refreshUI();
            });

            // -- UoM: recalcular al cambiar selector o cantidad --
            $(document).on('change', '.row-uom-selector', function() {
                recalcBase($(this).closest('tr'));
            });
            $(document).on('input change', '.row-quantity-uom', function() {
                recalcBase($(this).closest('tr'));
            });

            // -- Modal: eventos de toggle y componentes --
            $('#modal_is_kit').on('change', toggleModalKitFields);
            $('#modal_is_generic').on('change', toggleModalGenericFields);
            $('#addModalComponentBtn').on('click', addModalComponentRow);

            $(document).on('click', '.remove-modal-comp', function() {
                $(this).closest('tr').remove();
                if ($('#modalComponentsBody tr').length === 0) $('#noComponentsPlaceholder').show();
            });

            // -- Modal: resetear al cerrar --
            $('#quickItemModal').on('hidden.bs.modal', function() {
                $('#quickItemForm')[0].reset();
                $('#modalCodeError').hide();
                $('#quickItemModal .select2').val('').trigger('change');
                $('#modalComponentsBody').empty();
                $('#noComponentsPlaceholder').show();
                $('#modal_is_kit').prop('checked', false).trigger('change');
                $('#modal_is_generic').prop('checked', false).trigger('change');
                activeRowForModal = null;
            });

            // -- Modal: validar código duplicado --
            $('#modal_code').on('blur', function() {
                const code = $(this).val();
                if (code) {
                    $.get('{{ route("admin.products.search") }}', { search: code }, function(products) {
                        const exists = products.some(p => p.code && p.code.toLowerCase() === code.toLowerCase());
                        $('#modalCodeError').toggle(exists);
                    });
                }
            });

            // -- Modal: abrir y rastrear fila activa --
            $('#quickNewItemBtn').on('click', function() {
                const $lastRow = $('#itemsBody tr.item-row:last-child');
                const $lastSelect = $lastRow.find('.item-selector');
                if (!$lastSelect.val()) {
                    activeRowForModal = $lastRow;
                } else {
                    addItemRow();
                    activeRowForModal = $('#itemsBody tr.item-row:last-child');
                }
                $('#quickItemModal').modal('show');
            });

            // -- Modal: guardar ítem rápido (AJAX) --
            $('#quickItemForm').on('submit', function(e) {
                e.preventDefault();
                const isKit = $('#modal_is_kit').is(':checked');
                const url = isKit
                    ? '{{ route("admin.products.quick-store-kit") }}'
                    : '{{ route("admin.products.quick-store") }}';
                const $btn = $('#saveQuickItemBtn');
                $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Guardando...');

                $.ajax({
                    url: url,
                    method: 'POST',
                    data: $(this).serialize(),
                    headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                    success: function(response) {
                        $('#quickItemModal').modal('hide');
                        const prod = response.product;
                        const badge = isKit ? ' [Kit]' : '';
                        
                        // Formatear los datos para Select2 AJAX estructurados
                        const select2Format = {
                            id: prod.id,
                            text: prod.name + ' (' + (prod.code ?? 'S/C') + ')' + badge,
                            name: prod.name,
                            unit: prod.unit ? prod.unit.abbreviation : 'und',
                            unitId: prod.unit_id,
                            unitName: prod.unit ? prod.unit.name : 'Unidad',
                            categoryId: prod.category_id,
                            conversions: [{
                                id: prod.unit_id,
                                name: prod.unit ? prod.unit.name : 'Unidad',
                                factor: 1.0
                            }]
                        };

                        if (!isKit) {
                            $('#modal_simple_products_source').append(`<option value="${prod.id}">${prod.name} (${prod.code ?? 'S/C'})</option>`);
                        }

                        // Auto-seleccionar en la fila activa inyectando la opción físicamente y asignando sus datos
                        if (activeRowForModal) {
                            const $sel = activeRowForModal.find('.item-selector');
                            $sel.empty();
                            const newOption = new Option(select2Format.text, select2Format.id, true, true);
                            $sel.append(newOption).trigger('change');
                            activeRowForModal.find('.row-product-id').val(select2Format.id);
                            activeRowForModal.data('product-data', select2Format);
                            updateRowUom(activeRowForModal, select2Format);
                        }

                        Swal.fire({ icon: 'success', title: '¡Creado!', text: response.message, timer: 2000, showConfirmButton: false });
                    },
                    error: function(xhr) {
                        if (xhr.status === 422) {
                            const errors = xhr.responseJSON.errors || {};
                            if (errors.code) {
                                $('#modalCodeError').show();
                                Swal.fire({ icon: 'warning', title: 'Código Duplicado', text: 'El código ya está asignado a otro producto.' });
                            } else {
                                Swal.fire({ icon: 'error', title: 'Error de Validación', text: xhr.responseJSON.message || 'Complete los campos obligatorios.' });
                            }
                        } else {
                            Swal.fire({ icon: 'error', title: 'Error', text: 'Error inesperado al guardar el ítem.' });
                        }
                    },
                    complete: function() {
                        $btn.prop('disabled', false).html('<i class="fas fa-save"></i> Guardar Ítem');
                    }
                });
            });

            // ═══════════════════════════════════════════════════════════════════
            // 8. SUBMIT — Validación de frontend y confirmación con SweetAlert
            // ═══════════════════════════════════════════════════════════════════

            $('#saveRfqBtn').on('click', function(e) {
                e.preventDefault();
                let valid = true;

                // Validar título
                if ($('#title').val().trim() === '') {
                    $('#title').addClass('is-invalid');
                    valid = false;
                } else {
                    $('#title').removeClass('is-invalid');
                }

                // Validar que cada fila tenga un producto/kit seleccionado
                $('.item-selector').each(function() {
                    if (!$(this).val()) {
                        $(this).addClass('is-invalid');
                        valid = false;
                    } else {
                        $(this).removeClass('is-invalid');
                    }
                });

                if (!valid) {
                    Swal.fire({ icon: 'error', title: 'Campos requeridos vacíos', text: 'Complete todos los campos obligatorios del formulario.' });
                    return;
                }

                confirmAction({
                    title: 'Crear Solicitud de Cotización',
                    message: '¿Está seguro de registrar esta Solicitud de Cotización (SDC)?',
                    alert: 'Se creará en estado BORRADOR y podrá ser modificada posteriormente.',
                    confirmBtnClass: 'btn-primary',
                    onConfirm: function() {
                        $('#rfqForm').submit();
                    }
                });
            });
        });

    })(); // Fin IIFE
    </script>
    @include('admin.partials.confirm-action')
@endsection
