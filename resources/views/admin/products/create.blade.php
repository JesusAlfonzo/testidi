@extends('adminlte::page')

@section('title', 'Crear Producto')

@section('plugins.Select2', true)

@section('css')
<style>
    .radio-card {
        border: 1px solid #ced4da;
        background-color: #fff;
        transition: all 0.2s ease-in-out;
        cursor: pointer;
    }
    .radio-card:hover {
        border-color: #adb5bd;
        background-color: #f8f9fa;
    }
    /* Individual card active state */
    #type_individual:checked + .radio-card {
        border-color: #007bff !important;
        background-color: #e8f0fe !important;
    }
    #type_individual:checked + .radio-card .radio-card-icon {
        color: #007bff !important;
    }
    /* Kit card active state */
    #type_composite_kit:checked + .radio-card {
        border-color: #6f42c1 !important;
        background-color: #f3ebfc !important;
    }
    #type_composite_kit:checked + .radio-card .radio-card-icon {
        color: #6f42c1 !important;
    }
    .btn-purple {
        background-color: #6f42c1;
        color: #fff;
        border-color: #6f42c1;
    }
    .btn-purple:hover {
        background-color: #5a32a3;
        color: #fff;
        border-color: #5a32a3;
    }
    .text-purple {
        color: #6f42c1;
    }
    .card-purple-outline {
        border-top: 3px solid #6f42c1;
    }
    .border-dashed {
        border-style: dashed !important;
    }
    label.small {
        letter-spacing: 0.5px;
    }
</style>
@stop

@section('content_header')
    <h1><i class="fas fa-box-open text-primary mr-1"></i> Crear Nuevo Ítem</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            @include('admin.partials.session-messages')

            <form id="productForm" action="{{ route('admin.products.store') }}" method="POST">
                @csrf
                
                <div class="row">
                    {{-- COLUMNA PRINCIPAL (IZQUIERDA) --}}
                    <div class="col-lg-8 col-12">
                        
                        {{-- 1. IDENTIFICACIÓN Y CLASIFICACIÓN --}}
                        <div class="card card-outline card-info shadow-sm">
                            <div class="card-header bg-white border-bottom-0 py-3">
                                <h5 class="card-title text-info font-weight-bold mb-0">
                                    <i class="fas fa-info-circle mr-1"></i> Identificación y Clasificación
                                </h5>
                            </div>
                            <div class="card-body pt-0">
                                <div class="row">
                                    {{-- Código --}}
                                    <div class="col-md-4 form-group">
                                        <label for="code" class="small font-weight-bold text-muted text-uppercase mb-1">Código/SKU (*)</label>
                                        <div class="input-group input-group-sm">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text bg-light"><i class="fas fa-barcode"></i></span>
                                            </div>
                                            <input type="text" name="code" id="code" class="form-control @error('code') is-invalid @enderror" value="{{ old('code') }}" required>
                                        </div>
                                        @error('code')<span class="invalid-feedback d-block small">{{ $message }}</span>@enderror
                                    </div>
                                    {{-- Nombre --}}
                                    <div class="col-md-8 form-group">
                                        <label for="name" class="small font-weight-bold text-muted text-uppercase mb-1">Nombre del Producto/Kit (*)</label>
                                        <div class="input-group input-group-sm">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text bg-light"><i class="fas fa-box-open"></i></span>
                                            </div>
                                            <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required>
                                        </div>
                                        @error('name')<span class="invalid-feedback d-block small">{{ $message }}</span>@enderror
                                    </div>
                                </div>

                                <div class="row">
                                    {{-- Categoría --}}
                                    <div class="col-md-4 form-group">
                                        <label for="category_id" class="small font-weight-bold text-muted text-uppercase mb-1">Categoría <span class="generic-required-marker">(*)</span></label>
                                        <select name="category_id" id="category_id" class="form-control form-control-sm select2 @error('category_id') is-invalid @enderror" required>
                                            <option value="">Seleccione...</option>
                                            @foreach($categories as $id => $name)
                                                <option value="{{ $id }}" {{ old('category_id') == $id ? 'selected' : '' }}>{{ $name }}</option>
                                            @endforeach
                                        </select>
                                        @error('category_id')<span class="invalid-feedback d-block small">{{ $message }}</span>@enderror
                                    </div>
                                    {{-- Unidad --}}
                                    <div class="col-md-4 form-group">
                                        <label for="unit_id" class="small font-weight-bold text-muted text-uppercase mb-1">Unidad de Medida (*)</label>
                                        <select name="unit_id" id="unit_id" class="form-control form-control-sm select2 @error('unit_id') is-invalid @enderror" required>
                                            <option value="">Seleccione...</option>
                                            @foreach($units as $id => $name)
                                                <option value="{{ $id }}" {{ old('unit_id') == $id ? 'selected' : '' }}>{{ $name }}</option>
                                            @endforeach
                                        </select>
                                        @error('unit_id')<span class="invalid-feedback d-block small">{{ $message }}</span>@enderror
                                    </div>
                                    {{-- Marca --}}
                                    <div class="col-md-4 form-group">
                                        <label for="brand_id" class="small font-weight-bold text-muted text-uppercase mb-1">Marca (Opcional)</label>
                                        <select name="brand_id" id="brand_id" class="form-control form-control-sm select2 @error('brand_id') is-invalid @enderror">
                                            <option value="">Ninguna...</option>
                                            @foreach($brands as $id => $name)
                                                <option value="{{ $id }}" {{ old('brand_id') == $id ? 'selected' : '' }}>{{ $name }}</option>
                                            @endforeach
                                        </select>
                                        @error('brand_id')<span class="invalid-feedback d-block small">{{ $message }}</span>@enderror
                                    </div>
                                </div>

                                <div class="row">
                                    {{-- Ubicación --}}
                                    <div class="col-md-8 form-group mb-0">
                                        <label for="location_id" class="small font-weight-bold text-muted text-uppercase mb-1">Ubicación de Almacenamiento <span class="generic-required-marker">(*)</span></label>
                                        <select name="location_id" id="location_id" class="form-control form-control-sm select2 @error('location_id') is-invalid @enderror" required>
                                            <option value="">Seleccione...</option>
                                            @foreach($locations as $id => $name)
                                                <option value="{{ $id }}" {{ old('location_id') == $id ? 'selected' : '' }}>{{ $name }}</option>
                                            @endforeach
                                        </select>
                                        @error('location_id')<span class="invalid-feedback d-block small">{{ $message }}</span>@enderror
                                    </div>
                                    {{-- Genérico switch --}}
                                    <div class="col-md-4 d-flex align-items-center form-group mb-0" style="padding-top: 1.8rem;">
                                        <div class="custom-control custom-switch">
                                            <input type="hidden" name="is_generic" value="0">
                                            <input type="checkbox" class="custom-control-input" id="is_generic" name="is_generic" value="1" {{ old('is_generic') ? 'checked' : '' }}>
                                            <label class="custom-control-label small font-weight-bold text-muted text-uppercase" for="is_generic">
                                                <i class="fas fa-tags text-info mr-1"></i> Ítem Genérico
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- 2. COSTOS Y PRECIOS --}}
                        <div class="card card-outline card-warning shadow-sm mt-3">
                            <div class="card-header bg-white border-bottom-0 py-3">
                                <h5 class="card-title text-warning font-weight-bold mb-0">
                                    <i class="fas fa-dollar-sign mr-1"></i> Costos y Precios
                                </h5>
                            </div>
                            <div class="card-body pt-0">
                                <div class="row">
                                    {{-- Costo --}}
                                    <div class="col-md-6 form-group mb-0">
                                        <label for="cost" class="small font-weight-bold text-muted text-uppercase mb-1">Costo Unitario (S/IVA) (*)</label>
                                        <div class="input-group input-group-sm">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text bg-light"><i class="fas fa-money-bill-wave"></i></span>
                                            </div>
                                            <input type="number" step="0.01" name="cost" id="cost" class="form-control @error('cost') is-invalid @enderror" value="{{ old('cost', 0.00) }}" min="0" required>
                                        </div>
                                        @error('cost')<span class="invalid-feedback d-block small">{{ $message }}</span>@enderror
                                    </div>
                                    {{-- Precio --}}
                                    <div class="col-md-6 form-group mb-0">
                                        <label for="price" class="small font-weight-bold text-muted text-uppercase mb-1">Precio de Venta Sugerido (*)</label>
                                        <div class="input-group input-group-sm">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text bg-light"><i class="fas fa-tag"></i></span>
                                            </div>
                                            <input type="number" step="0.01" name="price" id="price" class="form-control @error('price') is-invalid @enderror" value="{{ old('price', 0.00) }}" min="0" required>
                                        </div>
                                        @error('price')<span class="invalid-feedback d-block small">{{ $message }}</span>@enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- 3. REPETIDOR DE COMPONENTES DEL KIT (SECCIÓN REACTIVA) --}}
                        <div class="card card-purple-outline shadow-sm mt-3" id="components-card" style="display: none;">
                            <div class="card-header bg-white border-bottom-0 py-3">
                                <h5 class="card-title text-purple font-weight-bold mb-0">
                                    <i class="fas fa-network-wired mr-1"></i> Componentes del Kit Compuesto
                                </h5>
                            </div>
                            <div class="card-body pt-0">
                                <div class="row align-items-end mb-3">
                                    <div class="col-md-9 form-group mb-0">
                                        <label for="component_search" class="small font-weight-bold text-muted text-uppercase mb-1">Buscar y Seleccionar Componente</label>
                                        <select id="component_search" class="form-control select2" style="width: 100%">
                                            <option value="">Seleccione un producto para agregar...</option>
                                            @foreach ($products as $prod)
                                                <option value="{{ $prod->id }}" data-code="{{ $prod->code }}" data-name="{{ $prod->name }}">
                                                    {{ $prod->name }} ({{ $prod->code ?? 'Sin código' }})
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-3 mt-2 mt-md-0">
                                        <button type="button" class="btn btn-purple btn-sm btn-block py-2" id="add-component-btn">
                                            <i class="fas fa-plus mr-1"></i> Agregar
                                        </button>
                                    </div>
                                </div>

                                @error('components')
                                    <div class="alert alert-danger py-2 small mb-3">
                                        <i class="fas fa-exclamation-circle mr-1"></i> {{ $message }}
                                    </div>
                                @enderror

                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered table-striped mb-0" id="components-table" style="display: none;">
                                        <thead class="thead-light">
                                            <tr>
                                                <th class="small font-weight-bold text-muted text-uppercase">Componente</th>
                                                <th class="small font-weight-bold text-muted text-uppercase" style="width: 150px;">Código</th>
                                                <th class="small font-weight-bold text-muted text-uppercase text-center" style="width: 120px;">Cantidad</th>
                                                <th class="small font-weight-bold text-muted text-uppercase text-center" style="width: 80px;">Acción</th>
                                            </tr>
                                        </thead>
                                        <tbody id="components-container">
                                            @if (old('components'))
                                                @foreach (old('components') as $index => $component)
                                                    @php
                                                        $childProduct = $products->firstWhere('id', $component['child_id']);
                                                    @endphp
                                                    @if ($childProduct)
                                                        <tr class="component-row" data-product-id="{{ $childProduct->id }}">
                                                            <td class="align-middle">
                                                                <input type="hidden" name="components[{{ $index }}][child_id]" value="{{ $childProduct->id }}">
                                                                {{ $childProduct->name }}
                                                            </td>
                                                            <td class="align-middle">{{ $childProduct->code ?? 'N/A' }}</td>
                                                            <td>
                                                                <input type="number" name="components[{{ $index }}][quantity]" class="form-control form-control-sm component-quantity text-center py-0" value="{{ $component['quantity'] ?? 1 }}" min="1" required style="height: 28px;">
                                                            </td>
                                                            <td class="text-center align-middle">
                                                                <button type="button" class="btn btn-danger btn-xs remove-component-btn" title="Eliminar">
                                                                    <i class="fas fa-trash-alt"></i>
                                                                </button>
                                                            </td>
                                                        </tr>
                                                    @endif
                                                @endforeach
                                            @endif
                                        </tbody>
                                    </table>
                                    <div id="no-components-message" class="text-center p-3 bg-light rounded border border-dashed">
                                        <i class="fas fa-info-circle text-muted mr-1"></i>
                                        <span class="text-muted small">No hay componentes agregados. Use el selector superior para agregar ítems.</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- 4. DETALLES Y VENCIMIENTO --}}
                        <div class="card card-outline card-secondary shadow-sm mt-3">
                            <div class="card-header bg-white border-bottom-0 py-3">
                                <h5 class="card-title text-secondary font-weight-bold mb-0">
                                    <i class="fas fa-calendar-alt mr-1"></i> Detalles y Vencimiento
                                </h5>
                            </div>
                            <div class="card-body pt-0">
                                <div class="form-group">
                                    <label for="description" class="small font-weight-bold text-muted text-uppercase mb-1">Descripción del Producto (Opcional)</label>
                                    <textarea name="description" id="description" rows="2" class="form-control form-control-sm @error('description') is-invalid @enderror">{{ old('description') }}</textarea>
                                    @error('description')<span class="invalid-feedback d-block small">{{ $message }}</span>@enderror
                                </div>

                                <div class="row">
                                    {{-- Switch Producto Perecedero --}}
                                    <div class="col-md-6 d-flex align-items-center form-group mb-0">
                                        <div class="custom-control custom-switch">
                                            <input type="checkbox" class="custom-control-input" id="is_perishable" name="is_perishable" value="1" {{ old('is_perishable') ? 'checked' : '' }}>
                                            <label class="custom-control-label small font-weight-bold text-muted text-uppercase" for="is_perishable">Controlar fecha de vencimiento (Producto Perecedero)</label>
                                        </div>
                                    </div>
                                    {{-- Días Alerta --}}
                                    <div class="col-md-6 form-group mb-0">
                                        <label for="expiry_warning_days" class="small font-weight-bold text-muted text-uppercase mb-1">Alerta Vencimiento (Días)</label>
                                        <div class="input-group input-group-sm">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text bg-light"><i class="fas fa-calendar-times"></i></span>
                                            </div>
                                            <input type="number" name="expiry_warning_days" id="expiry_warning_days" class="form-control" value="{{ old('expiry_warning_days', $defaultExpiryDays) }}" min="1" max="365">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>

                    {{-- COLUMNA LATERAL (DERECHA) --}}
                    <div class="col-lg-4 col-12 mt-3 mt-lg-0">
                        
                        {{-- CARD 1: CONFIGURACIÓN Y TIPO --}}
                        <div class="card card-outline card-primary shadow-sm">
                            <div class="card-header bg-white border-bottom-0 py-3">
                                <h5 class="card-title text-primary font-weight-bold mb-0">
                                    <i class="fas fa-cog mr-1"></i> Configuración
                                </h5>
                            </div>
                            <div class="card-body pt-0">
                                {{-- Selector de Tipo --}}
                                <div class="form-group mb-3">
                                    <label class="small font-weight-bold text-muted text-uppercase mb-2">Tipo de Registro</label>
                                    
                                    {{-- Radio Individual --}}
                                    <div class="position-relative mb-2">
                                        <input type="radio" name="type" id="type_individual" value="individual" class="d-none" {{ old('type', request('type', 'individual')) === 'individual' ? 'checked' : '' }}>
                                        <label class="radio-card p-3 d-flex align-items-center rounded w-100 m-0" for="type_individual">
                                            <div class="mr-3 text-secondary radio-card-icon" style="font-size: 20px;"><i class="fas fa-cube"></i></div>
                                            <div>
                                                <span class="d-block font-weight-bold small">Producto Individual</span>
                                                <span class="text-muted small" style="font-size: 11px;">Ítem único estándar de inventario.</span>
                                            </div>
                                        </label>
                                    </div>

                                    {{-- Radio Kit --}}
                                    <div class="position-relative">
                                        <input type="radio" name="type" id="type_composite_kit" value="composite_kit" class="d-none" {{ old('type', request('type')) === 'composite_kit' ? 'checked' : '' }}>
                                        <label class="radio-card p-3 d-flex align-items-center rounded w-100 m-0" for="type_composite_kit">
                                            <div class="mr-3 text-purple radio-card-icon" style="font-size: 20px;"><i class="fas fa-cubes"></i></div>
                                            <div>
                                                <span class="d-block font-weight-bold small text-purple">Kit / Compuesto</span>
                                                <span class="text-muted small" style="font-size: 11px;">Agrupación de ítems de almacén.</span>
                                            </div>
                                        </label>
                                    </div>
                                </div>

                                <hr class="my-3">

                                {{-- Switch Serial --}}
                                <div class="form-group">
                                    <div class="custom-control custom-switch">
                                        <input type="hidden" name="requires_serial" value="0">
                                        <input type="checkbox" class="custom-control-input" id="requires_serial" name="requires_serial" value="1" {{ old('requires_serial') ? 'checked' : '' }}>
                                        <label class="custom-control-label small font-weight-bold text-muted text-uppercase" for="requires_serial">Requiere Serial</label>
                                    </div>
                                    <small class="form-text text-muted small mt-1">Exige números de serie únicos al ingresar mercancía.</small>
                                </div>

                                {{-- Switch Active --}}
                                <div class="form-group">
                                    <div class="custom-control custom-switch">
                                        <input type="hidden" name="is_active" value="0">
                                        <input type="checkbox" class="custom-control-input" id="is_active" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                                        <label class="custom-control-label small font-weight-bold text-muted text-uppercase cursor-pointer" for="is_active">Estado Activo</label>
                                    </div>
                                    <small class="form-text text-muted small mt-1">Permite usar el producto en cotizaciones o salidas.</small>
                                </div>

                                <hr class="my-3">

                                {{-- Switch Fraction Parent --}}
                                <div class="form-group mb-0">
                                    <div class="custom-control custom-switch">
                                        <input type="hidden" name="is_fraction_parent" value="0">
                                        <input type="checkbox" class="custom-control-input" id="is_fraction_parent" name="is_fraction_parent" value="1" {{ old('is_fraction_parent') ? 'checked' : '' }}>
                                        <label class="custom-control-label small font-weight-bold text-muted text-uppercase cursor-pointer" for="is_fraction_parent">Este producto es un Empaque/Caja</label>
                                    </div>
                                    <small class="form-text text-muted small mt-1">Permite desempacar este producto en unidades individuales más pequeñas.</small>
                                </div>

                                {{-- Contenedor Condicional Fraccionamiento --}}
                                <div id="fraction-fields-container" style="display: none;" class="mt-3">
                                    <div class="form-group">
                                        <label for="child_product_id" class="small font-weight-bold text-muted text-uppercase mb-1">Unidad Individual (*)</label>
                                        <select name="child_product_id" id="child_product_id" class="form-control form-control-sm select2 @error('child_product_id') is-invalid @enderror" style="width: 100%;">
                                            <option value="">Seleccione el producto hijo...</option>
                                            @foreach($products as $prod)
                                                <option value="{{ $prod->id }}" {{ old('child_product_id') == $prod->id ? 'selected' : '' }}>
                                                    {{ $prod->name }} ({{ $prod->code ?? 'N/A' }})
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('child_product_id')<span class="invalid-feedback d-block small">{{ $message }}</span>@enderror
                                    </div>

                                    <div class="form-group mb-0">
                                        <label for="conversion_factor" class="small font-weight-bold text-muted text-uppercase mb-1">Factor de Conversión (*)</label>
                                        <div class="input-group input-group-sm">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text bg-light"><i class="fas fa-calculator"></i></span>
                                            </div>
                                            <input type="number" name="conversion_factor" id="conversion_factor" class="form-control @error('conversion_factor') is-invalid @enderror" value="{{ old('conversion_factor', '1') }}" min="1">
                                        </div>
                                        @error('conversion_factor')<span class="invalid-feedback d-block small">{{ $message }}</span>@enderror
                                        <small class="form-text text-muted small mt-1">Cantidad de unidades individuales contenidas en este empaque (ej: 30, 100).</small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- CARD 2: CONTROL DE INVENTARIO --}}
                        <div class="card card-outline card-success shadow-sm mt-3">
                            <div class="card-header bg-white border-bottom-0 py-3">
                                <h5 class="card-title text-success font-weight-bold mb-0">
                                    <i class="fas fa-warehouse mr-1"></i> Control de Stock
                                </h5>
                            </div>
                            <div class="card-body pt-0">
                                {{-- Stock Inicial --}}
                                <div class="form-group">
                                    <label for="stock" class="small font-weight-bold text-muted text-uppercase mb-1">Stock Inicial (*)</label>
                                    <div class="input-group input-group-sm">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text bg-light"><i class="fas fa-boxes text-success"></i></span>
                                        </div>
                                        <input type="number" name="stock" id="stock" class="form-control @error('stock') is-invalid @enderror" value="{{ old('stock', 0) }}" min="0" required>
                                    </div>
                                    @error('stock')<span class="invalid-feedback d-block small">{{ $message }}</span>@enderror
                                </div>

                                {{-- Alerta Stock Mínimo --}}
                                <div class="form-group mb-0">
                                    <label for="min_stock" class="small font-weight-bold text-muted text-uppercase mb-1">Stock Mínimo Alerta (*)</label>
                                    <div class="input-group input-group-sm">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text bg-light"><i class="fas fa-exclamation-triangle text-danger"></i></span>
                                        </div>
                                        <input type="number" name="min_stock" id="min_stock" class="form-control @error('min_stock') is-invalid @enderror" value="{{ old('min_stock', 0) }}" min="0" required>
                                    </div>
                                    @error('min_stock')<span class="invalid-feedback d-block small">{{ $message }}</span>@enderror
                                    <small class="form-text text-muted small mt-1">Notifica cuando el stock cae bajo esta cantidad.</small>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

                {{-- FORM FOOTER ACTIONS --}}
                <div class="row mt-3 mb-5">
                    <div class="col-12">
                        <div class="card shadow-sm border-0">
                            <div class="card-body d-flex justify-content-end py-3 bg-light rounded">
                                <a href="{{ route('admin.products.index') }}" class="btn btn-secondary mr-2">
                                    <i class="fas fa-arrow-left mr-1"></i> Cancelar
                                </a>
                                <button type="submit" class="btn btn-success">
                                    <i class="fas fa-save mr-1"></i> Guardar Registro
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

            </form>
        </div>
    </div>
@stop

@section('js')
    <script>
        $(document).ready(function() {
            // Inicializar Select2 en los campos normales y en el de búsqueda de componentes
            $('.select2').select2({
                theme: 'bootstrap4'
            });

            // Ajustar visualización del select2 en cards colapsables u ocultos
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

            // CONTROL DE CAMPOS PARA PRODUCTO GENÉRICO
            function toggleGenericFields() {
                const isGeneric = $('#is_generic').is(':checked');
                
                if (isGeneric) {
                    $('#brand_id').closest('.form-group').slideUp(200);
                    $('#category_id').prop('required', false);
                    $('#location_id').prop('required', false);
                    $('.generic-required-marker').fadeOut(200);
                } else {
                    $('#brand_id').closest('.form-group').slideDown(200);
                    $('#category_id').prop('required', true);
                    $('#location_id').prop('required', true);
                    $('.generic-required-marker').fadeIn(200);
                }
            }

            toggleGenericFields();
            $('#is_generic').on('change', toggleGenericFields);

            // CONTROL DE CAMPOS PARA DESEMPAQUE/FRACCIONAMIENTO
            function toggleFractionFields() {
                const isFractionParent = $('#is_fraction_parent').is(':checked');
                if (isFractionParent) {
                    $('#fraction-fields-container').slideDown(250);
                    $('#child_product_id').prop('required', true);
                    $('#conversion_factor').prop('required', true);
                } else {
                    $('#fraction-fields-container').slideUp(250);
                    $('#child_product_id').prop('required', false);
                    $('#conversion_factor').prop('required', false);
                }
            }

            toggleFractionFields();
            $('#is_fraction_parent').on('change', toggleFractionFields);

            // CONTROL DEDICADO A TIPO DE REGISTRO (INDIVIDUAL VS KIT COMPUESTO)
            function toggleTypeSections() {
                const selectedType = $('input[name="type"]:checked').val();
                
                if (selectedType === 'composite_kit') {
                    // Mostrar sección de componentes con transición fluida
                    $('#components-card').slideDown({
                        duration: 350,
                        easing: 'swing',
                        complete: function() {
                            // Re-ajustar select2 interno si es necesario
                            $('#component_search').select2({ theme: 'bootstrap4' });
                        }
                    });
                } else {
                    // Ocultar sección de componentes con transición
                    $('#components-card').slideUp({
                        duration: 350,
                        easing: 'swing'
                    });
                }
            }

            // Detectar cambios en el tipo de producto
            $('input[name="type"]').on('change', toggleTypeSections);
            toggleTypeSections(); // Ejecución inicial

            // REPETIDOR DE COMPONENTES DEL KIT
            let componentIndex = {{ old('components') ? count(old('components')) : 0 }};

            function updateNoComponentsMessage() {
                if ($('#components-container tr.component-row').length === 0) {
                    $('#no-components-message').show();
                    $('#components-table').hide();
                } else {
                    $('#no-components-message').hide();
                    $('#components-table').show();
                }
            }

            // Ejecutar al cargar para verificar si hay componentes previos
            updateNoComponentsMessage();

            // Agregar nuevo componente
            $('#add-component-btn').on('click', function() {
                const select = $('#component_search');
                const productId = select.val();
                
                if (!productId) {
                    alert('Por favor, seleccione un producto de la lista.');
                    return;
                }

                // Validar duplicado
                let duplicate = false;
                $('#components-container tr.component-row').each(function() {
                    if ($(this).attr('data-product-id') == productId) {
                        duplicate = true;
                    }
                });

                if (duplicate) {
                    alert('Este producto ya ha sido agregado como componente del Kit.');
                    return;
                }

                const option = select.find('option:selected');
                const name = option.data('name');
                const code = option.data('code') || 'N/A';

                const rowHtml = `
                    <tr class="component-row" data-product-id="${productId}">
                        <td class="align-middle">
                            <input type="hidden" name="components[${componentIndex}][child_id]" value="${productId}">
                            ${name}
                        </td>
                        <td class="align-middle">${code}</td>
                        <td>
                            <input type="number" name="components[${componentIndex}][quantity]" class="form-control form-control-sm component-quantity text-center py-0" value="1" min="1" required style="height: 28px;">
                        </td>
                        <td class="text-center align-middle">
                            <button type="button" class="btn btn-danger btn-xs remove-component-btn" title="Eliminar">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </td>
                    </tr>
                `;

                $('#components-container').append(rowHtml);
                componentIndex++;
                updateNoComponentsMessage();

                // Resetear buscador
                select.val(null).trigger('change');
            });

            // Eliminar componente
            $('#components-container').on('click', '.remove-component-btn', function() {
                $(this).closest('tr').remove();
                updateNoComponentsMessage();
            });
        });
    </script>
@endsection