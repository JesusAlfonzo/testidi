@extends('adminlte::page')

@section('title', 'Editar Cotización ' . $quotation->code)

@section('plugins.Select2', true)
@section('plugins.Sweetalert2', true)

@section('content_header')
    <h1><i class="fas fa-file-alt"></i> Editar Cotización</h1>
@stop

@section('content')
    @php
        $categories = \App\Models\Category::orderBy('name')->get();
        $units = \App\Models\Unit::orderBy('name')->get();
        $locations = \App\Models\Location::orderBy('name')->get();
        $brands = \App\Models\Brand::orderBy('name')->get();
    @endphp

    

    <form action="{{ route('admin.quotations.update', $quotation) }}" method="POST" id="quotationForm">
        @csrf
        @method('PUT')

        <!-- Sección: Información General -->
        <div class="row">
            <div class="col-12">
                <div class="card" style="border-left: 4px solid #6c757d;">
                    <div class="card-header" style="background: linear-gradient(135deg, #6c757d 0%, #8a939d 100%);">
                        <h3 class="card-title text-white">
                            <i class="fas fa-info-circle"></i> Información General
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-12 col-md-3">
                                <div class="form-group mb-2">
                                    <label for="code" class="mb-1">Código</label>
                                    <div class="input-group input-group-sm">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text bg-secondary text-white"><i class="fas fa-hashtag"></i></span>
                                        </div>
                                        <input type="text" name="code" class="form-control" value="{{ $quotation->code }}" readonly>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-md-5">
                                <div class="form-group mb-2">
                                    <label for="supplier_reference" class="mb-1">Ref. Proveedor</label>
                                    <div class="input-group input-group-sm">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text bg-secondary text-white"><i class="fas fa-tag"></i></span>
                                        </div>
                                        <input type="text" name="supplier_reference" class="form-control" value="{{ old('supplier_reference', $quotation->supplier_reference) }}" placeholder="Número de cotización del proveedor">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sección: Datos del Proveedor -->
        <div class="row">
            <div class="col-12">
                <div class="card" style="border-left: 4px solid #f59e0b;">
                    <div class="card-header" style="background: linear-gradient(135deg, #f59e0b 0%, #fbbf24 100%);">
                        <h3 class="card-title text-white">
                            <i class="fas fa-building"></i> Datos del Proveedor
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-12 col-md-10">
                                <div class="form-group">
                                    <label for="supplier_id">Seleccionar Proveedor (*)</label>
                                    <select name="supplier_id" id="supplier_id" class="form-control select2" data-placeholder="Buscar proveedor..." required>
                                        <option value="">Seleccione...</option>
                                        @foreach($suppliers as $supplier)
                                            <option value="{{ $supplier->id }}" {{ $quotation->supplier_id == $supplier->id ? 'selected' : '' }}>
                                                {{ $supplier->name }} | {{ $supplier->email }} | {{ $supplier->phone ?? 'Sin teléfono' }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-12 col-md-2 d-flex align-items-end">
                                <div class="form-group w-100">
                                    <button type="button" id="addSupplierBtn" class="btn btn-warning btn-block text-dark">
                                        <i class="fas fa-plus"></i> Crear Proveedor
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-12">
                                <div class="btn-group w-100" role="group">
                                    <input type="radio" class="btn-check" name="supplier_type" id="supplierRegistered" value="registered" {{ $quotation->hasRegisteredSupplier() ? 'checked' : '' }}>
                                    <label class="btn btn-outline-dark" for="supplierRegistered">
                                        <i class="fas fa-building"></i> Proveedor Registrado
                                    </label>

                                    <input type="radio" class="btn-check" name="supplier_type" id="supplierTemp" value="temp" {{ !$quotation->hasRegisteredSupplier() ? 'checked' : '' }}>
                                    <label class="btn btn-outline-warning" for="supplierTemp">
                                        <i class="fas fa-user-plus"></i> Proveedor Temporal
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div id="registeredSupplierBlock" class="{{ !$quotation->hasRegisteredSupplier() ? 'd-none' : '' }}">
                            <div class="row">
                                <div class="col-12 col-md-10">
                                    <div class="form-group">
                                        <label for="supplier_id">Seleccionar Proveedor (*)</label>
                                        <select name="supplier_id" id="supplier_id" class="form-control select2" data-placeholder="Buscar proveedor...">
                                            <option value="">Seleccione...</option>
                                            @foreach($suppliers as $supplier)
                                                <option value="{{ $supplier->id }}" {{ $quotation->supplier_id == $supplier->id ? 'selected' : '' }}>
                                                    {{ $supplier->name }} | {{ $supplier->email }} | {{ $supplier->phone ?? 'Sin teléfono' }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-12 col-md-2 d-flex align-items-end">
                                    <div class="form-group w-100">
                                        <button type="button" id="addSupplierBtn" class="btn btn-warning btn-block text-dark">
                                            <i class="fas fa-plus"></i> Crear Proveedor
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div id="tempSupplierBlock" class="{{ $quotation->hasRegisteredSupplier() ? 'd-none' : '' }}">
                            <div class="alert alert-warning mb-3">
                                <i class="fas fa-info-circle"></i> Use esta opción si el proveedor aún no está registrado. Podrá registrarlo después.
                            </div>
                            <div class="row">
                                <div class="col-12 col-md-4">
                                    <div class="form-group">
                                        <label for="supplier_name_temp">Nombre (*)</label>
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text"><i class="fas fa-user"></i></span>
                                            </div>
                                            <input type="text" name="supplier_name_temp" class="form-control" value="{{ old('supplier_name_temp', $quotation->supplier_name_temp) }}">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12 col-md-4">
                                    <div class="form-group">
                                        <label for="supplier_email_temp">Email</label>
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                            </div>
                                            <input type="email" name="supplier_email_temp" class="form-control" value="{{ old('supplier_email_temp', $quotation->supplier_email_temp) }}">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12 col-md-4">
                                    <div class="form-group">
                                        <label for="supplier_phone_temp">Teléfono</label>
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text"><i class="fas fa-phone"></i></span>
                                            </div>
                                            <input type="text" name="supplier_phone_temp" class="form-control" value="{{ old('supplier_phone_temp', $quotation->supplier_phone_temp) }}">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sección: Fechas y Moneda -->
        <div class="row">
            <div class="col-12 col-md-8">
                <div class="card" style="border-left: 4px solid #3b82f6;">
                    <div class="card-header" style="background: linear-gradient(135deg, #3b82f6 0%, #60a5fa 100%);">
                        <h3 class="card-title text-white">
                            <i class="fas fa-calendar"></i> Fechas
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-12 col-md-4">
                                <div class="form-group mb-2">
                                    <label for="date_issued" class="mb-1">Fecha de Emisión (*)</label>
                                    <div class="input-group input-group-sm">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text bg-info text-white"><i class="fas fa-calendar"></i></span>
                                        </div>
                                        <input type="date" name="date_issued" class="form-control" value="{{ old('date_issued', $quotation->date_issued->format('Y-m-d')) }}" required>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-md-4">
                                <div class="form-group mb-2">
                                    <label for="valid_until" class="mb-1">Válido Hasta</label>
                                    <div class="input-group input-group-sm">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text bg-info text-white"><i class="fas fa-calendar-check"></i></span>
                                        </div>
                                        <input type="date" name="valid_until" class="form-control" value="{{ old('valid_until', $quotation->valid_until?->format('Y-m-d')) }}">
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-md-4">
                                <div class="form-group mb-2">
                                    <label for="delivery_date" class="mb-1">Fecha Entrega Ofertada</label>
                                    <div class="input-group input-group-sm">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text bg-info text-white"><i class="fas fa-truck"></i></span>
                                        </div>
                                        <input type="date" name="delivery_date" class="form-control" value="{{ old('delivery_date', $quotation->delivery_date?->format('Y-m-d')) }}">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-4">
                <div class="card" style="border-left: 4px solid #10b981;">
                    <div class="card-header" style="background: linear-gradient(135deg, #10b981 0%, #34d399 100%);">
                        <h3 class="card-title text-white">
                            <i class="fas fa-coins"></i> Moneda
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-12">
                                <div class="form-group mb-2">
                                    <label for="currency" class="mb-1">Moneda</label>
                                    <select name="currency" class="form-control form-control-sm select2">
                                        <option value="USD" {{ $quotation->currency == 'USD' ? 'selected' : '' }}>💵 USD - Dólar</option>
                                        <option value="EUR" {{ $quotation->currency == 'EUR' ? 'selected' : '' }}>💶 EUR - Euro</option>
                                        <option value="Bs" {{ $quotation->currency == 'Bs' ? 'selected' : '' }}>🇻🇪 Bs - Bolívar</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-group mb-0">
                                    <label for="exchange_rate" class="mb-1">Tasa de Cambio</label>
                                    <div class="input-group input-group-sm">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text bg-success text-white"><i class="fas fa-exchange-alt"></i></span>
                                        </div>
                                        <input type="number" step="0.0001" name="exchange_rate" class="form-control" value="{{ old('exchange_rate', $quotation->exchange_rate) }}">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sección: Items -->
        <div class="row">
            <div class="col-12">
                <div class="card" style="border-left: 4px solid #ef4444;">
                    <div class="card-header" style="background: linear-gradient(135deg, #ef4444 0%, #f87171 100%);">
                        <div class="d-flex justify-content-between align-items-center">
                            <h3 class="card-title text-white">
                                <i class="fas fa-boxes"></i> Items de la Cotización
                            </h3>
                            <div class="d-flex">
                                <button type="button" class="btn btn-sm btn-outline-light text-light mr-2 create-product-btn" data-toggle="modal" data-target="#productModal">
                                    <i class="fas fa-plus"></i> Crear Producto
                                </button>
                                <button type="button" id="addItem" class="btn btn-sm btn-light text-danger">
                                    <i class="fas fa-plus"></i> Agregar Item
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped mb-0" id="itemsTable">
                                <thead class="bg-light">
                                    <tr>
                                        <th style="width: 40%">Producto</th>
                                        <th style="width: 15%">Cantidad</th>
                                        <th style="width: 20%">Costo Unit.</th>
                                        <th style="width: 15%">Total</th>
                                        <th style="width: 10%"></th>
                                    </tr>
                                </thead>
                                <tbody id="itemsBody">
                                    @foreach($quotation->items as $index => $item)
                                        <tr>
                                            <td>
                                                <select name="items[{{ $index }}][product_id]" class="form-control select2-product form-control-sm" required>
                                                    <option value="">Seleccione...</option>
                                                    @foreach($products as $product)
                                                        <option value="{{ $product->id }}" data-cost="{{ $product->cost ?? 0 }}" {{ $item->product_id == $product->id ? 'selected' : '' }}>
                                                            {{ $product->name }} ({{ $product->code ?? 'S/C' }})
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </td>
                                            <td>
                                                <input type="number" name="items[{{ $index }}][quantity]" class="form-control form-control-sm item-qty" min="1" value="{{ old("items.$index.quantity", $item->quantity) }}" required>
                                            </td>
                                            <td>
                                                <input type="number" step="0.01" name="items[{{ $index }}][unit_cost]" class="form-control form-control-sm item-cost" min="0" value="{{ old("items.$index.unit_cost", $item->unit_cost) }}" required>
                                            </td>
                                            <td>
                                                <span class="item-total font-weight-bold">{{ number_format($item->quantity * $item->unit_cost, 2) }}</span>
                                            </td>
                                            <td class="text-center">
                                                <button type="button" class="btn btn-sm btn-danger remove-item" {{ $quotation->items->count() <= 1 ? 'style=display:none' : '' }}>
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot class="bg-success-light">
                                    <tr>
                                        <th colspan="3" class="text-right">TOTAL GENERAL:</th>
                                        <th class="text-right"><span id="grandTotal" class="h5 text-success">${{ number_format($quotation->total, 2) }}</span></th>
                                        <th></th>
                                    </tr>
                                    <tr class="bg-info-light">
                                        <th colspan="3" class="text-right">Subtotal Bs (sin IVA):</th>
                                        <th class="text-right"><span id="grandTotalBs" class="h5 text-info">Bs 0.00</span></th>
                                        <th></th>
                                    </tr>
                                    <tr class="bg-info-light">
                                        <th colspan="3" class="text-right">IVA 16%:</th>
                                        <th class="text-right"><span id="ivaBs" class="h5 text-info">Bs 0.00</span></th>
                                        <th></th>
                                    </tr>
                                    <tr class="bg-info">
                                        <th colspan="3" class="text-right">TOTAL Bs (con IVA):</th>
                                        <th class="text-right"><span id="totalBs" class="h5 text-white">Bs 0.00</span></th>
                                        <th></th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sección: Notas -->
        <div class="row">
            <div class="col-12">
                <div class="card" style="border-left: 4px solid #9ca3af;">
                    <div class="card-header" style="background: linear-gradient(135deg, #9ca3af 0%, #d1d5db 100%);">
                        <h3 class="card-title text-white">
                            <i class="fas fa-sticky-note"></i> Notas
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="form-group mb-0">
                            <textarea name="notes" id="notes" rows="2" class="form-control form-control-sm" placeholder="Notas adicionales">{{ old('notes', $quotation->notes) }}</textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Botones de Acción -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body d-flex justify-content-end">
                        <a href="{{ route('admin.quotations.index') }}" class="btn btn-secondary btn-lg mr-2">
                            <i class="fas fa-times"></i> Cancelar
                        </a>
                        <button type="button" class="btn btn-primary btn-lg" id="saveQuotationBtn">
                            <i class="fas fa-save"></i> Actualizar Cotización
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>

    <!-- Modal para crear Proveedor rápido -->
    <div class="modal fade" id="supplierModal" tabindex="-1" role="dialog" aria-labelledby="supplierModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header" style="background: linear-gradient(135deg, #f59e0b 0%, #fbbf24 100%);">
                    <h5 class="modal-title text-white" id="supplierModalLabel"><i class="fas fa-building"></i> Crear Nuevo Proveedor</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="supplierForm">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-12 col-md-6">
                                <div class="form-group">
                                    <label for="supplier_name">Nombre (*)</label>
                                    <input type="text" name="name" id="supplier_name" class="form-control" required>
                                </div>
                            </div>
                            <div class="col-12 col-md-6">
                                <div class="form-group">
                                    <label for="supplier_tax_id">RIF / Tax ID (*)</label>
                                    <input type="text" name="tax_id" id="supplier_tax_id" class="form-control" required>
                                    <small class="text-danger" id="supplierTaxIdError" style="display:none;">El RIF ya existe</small>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12 col-md-6">
                                <div class="form-group">
                                    <label for="supplier_email">Email</label>
                                    <input type="email" name="email" id="supplier_email" class="form-control">
                                </div>
                            </div>
                            <div class="col-12 col-md-6">
                                <div class="form-group">
                                    <label for="supplier_phone">Teléfono</label>
                                    <input type="text" name="phone" id="supplier_phone" class="form-control">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12">
                                <div class="form-group">
                                    <label for="supplier_address">Dirección</label>
                                    <textarea name="address" id="supplier_address" rows="2" class="form-control"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-warning text-dark" id="saveSupplierBtn">
                            <i class="fas fa-save"></i> Guardar Proveedor
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal para crear producto -->
    <div class="modal fade" id="productModal" tabindex="-1" role="dialog" aria-labelledby="productModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header" style="background: linear-gradient(135deg, #3b82f6 0%, #60a5fa 100%);">
                    <h5 class="modal-title text-white" id="productModalLabel"><i class="fas fa-box"></i> Crear Nuevo Producto</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="productForm">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-12 col-md-6">
                                <div class="form-group">
                                    <label for="product_code">Código/SKU (*)</label>
                                    <input type="text" name="code" id="product_code" class="form-control" required>
                                </div>
                            </div>
                            <div class="col-12 col-md-6">
                                <div class="form-group">
                                    <label for="product_name">Nombre (*)</label>
                                    <input type="text" name="name" id="product_name" class="form-control" required>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12 col-md-6">
                                <div class="form-group">
                                    <label for="product_category_id">Categoría (*)</label>
                                    <select name="category_id" id="product_category_id" class="form-control select2" required>
                                        <option value="">Seleccione...</option>
                                        @foreach($categories as $category)
                                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-12 col-md-6">
                                <div class="form-group">
                                    <label for="product_unit_id">Unidad (*)</label>
                                    <select name="unit_id" id="product_unit_id" class="form-control select2" required>
                                        <option value="">Seleccione...</option>
                                        @foreach($units as $unit)
                                            <option value="{{ $unit->id }}">{{ $unit->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12 col-md-6">
                                <div class="form-group">
                                    <label for="product_location_id">Ubicación (*)</label>
                                    <select name="location_id" id="product_location_id" class="form-control select2" required>
                                        <option value="">Seleccione...</option>
                                        @foreach($locations as $location)
                                            <option value="{{ $location->id }}">{{ $location->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-12 col-md-6">
                                <div class="form-group">
                                    <label for="product_brand_id">Marca</label>
                                    <select name="brand_id" id="product_brand_id" class="form-control select2">
                                        <option value="">Seleccione...</option>
                                        @foreach($brands as $brand)
                                            <option value="{{ $brand->id }}">{{ $brand->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary" id="saveProductBtn">
                            <i class="fas fa-save"></i> Guardar Producto
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('css')
    <style>
        .bg-success-light { background-color: #d4edda; }
        .bg-info-light { background-color: #d1ecf1; }
    </style>
@endsection

@section('js')
    <script>
        let itemIndex = {{ $quotation->items->count() }};
        let currentProductSelect = null;

        function getCurrencySymbol(currency) {
            switch(currency) {
                case 'USD': return '$';
                case 'EUR': return '€';
                case 'Bs': return 'Bs ';
                default: return currency + ' ';
            }
        }

        function calculateTotals() {
            const currency = $('select[name="currency"]').val();
            const exchangeRate = parseFloat($('input[name="exchange_rate"]').val()) || 0;
            const symbol = getCurrencySymbol(currency);
            const isBs = currency === 'Bs';
            
            let grandTotal = 0;
            let grandTotalBs = 0;
            
            $('#itemsBody tr').each(function() {
                const qty = parseFloat($(this).find('.item-qty').val()) || 0;
                const cost = parseFloat($(this).find('.item-cost').val()) || 0;
                const total = qty * cost;
                const totalBs = isBs ? total : (total * exchangeRate);
                
                $(this).find('.item-total').text(symbol + total.toFixed(2));
                grandTotal += total;
                grandTotalBs += totalBs;
            });
            
            $('#grandTotal').text(symbol + grandTotal.toFixed(2));
            
            const ivaBs = grandTotalBs * 0.16;
            const totalBs = grandTotalBs + ivaBs;
            
            $('#grandTotalBs').text('Bs ' + grandTotalBs.toFixed(2));
            $('#ivaBs').text('Bs ' + ivaBs.toFixed(2));
            $('#totalBs').text('Bs ' + totalBs.toFixed(2));
        }

        function initSelect2() {
            $('.select2').select2({
                theme: 'bootstrap4',
                width: '100%',
                allowClear: true
            });

            $('.select2-product').not('.select2-initialized').each(function() {
                $(this).addClass('select2-initialized').select2({
                    theme: 'bootstrap4',
                    width: '100%',
                    allowClear: true
                });
            });
        }
        
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

        function updateRemoveButtons() {
            const rows = $('#itemsBody tr').length;
            $('#itemsBody .remove-item').toggle(rows > 1);
        }

        function attachProductButtonEvents() {
            $('.create-product-btn').off('click').on('click', function(e) {
                e.preventDefault();
                currentProductSelect = $(this).closest('.input-group').find('.select2-product');
                $('#productModal').modal('show');
            });
        }

        const productOptions = `@foreach($products as $product)<option value="{{ $product->id }}" data-cost="{{ $product->cost ?? 0 }}">{{ $product->name }} ({{ $product->code ?? 'S/C' }})</option>@endforeach`;

        $('#addItem').click(function() {
            const row = `
                <tr>
                    <td>
                        <select name="items[${itemIndex}][product_id]" class="form-control select2-product form-control-sm" required>
                            <option value="">Seleccione...</option>
                            ${productOptions}
                        </select>
                    </td>
                    <td><input type="number" name="items[${itemIndex}][quantity]" class="form-control form-control-sm item-qty" min="1" value="1" required></td>
                    <td><input type="number" step="0.01" name="items[${itemIndex}][unit_cost]" class="form-control form-control-sm item-cost" min="0" value="0" required></td>
                    <td><span class="item-total font-weight-bold">$0.00</span></td>
                    <td class="text-center">
                        <button type="button" class="btn btn-sm btn-danger remove-item"><i class="fas fa-times"></i></button>
                    </td>
                </tr>
            `;
            $('#itemsBody').append(row);
            itemIndex++;
            // Solo inicializar el nuevo select
            $('#itemsBody').find('.select2-product').last().select2({
                theme: 'bootstrap4',
                width: '100%',
                allowClear: true
            });
            updateRemoveButtons();
        });

        $(document).on('click', '.remove-item', function() {
            $(this).closest('tr').remove();
            calculateTotals();
            updateRemoveButtons();
        });

        $(document).on('input', '.item-qty, .item-cost', calculateTotals);

        $(document).on('select2:select', '.select2-product', function(e) {
            const cost = $(this).find('option:selected').data('cost') || 0;
            $(this).closest('tr').find('.item-cost').val(cost);
            calculateTotals();
        });

        $('select[name="currency"]').change(function() {
            const currency = $(this).val();
            const exchangeInput = $('input[name="exchange_rate"]');
            if (currency === 'Bs') {
                exchangeInput.val(1).prop('readonly', true);
            } else {
                if (parseFloat(exchangeInput.val()) === 1 || exchangeInput.val() === '') {
                    exchangeInput.val('').prop('readonly', false);
                }
            }
            calculateTotals();
        });

        $('input[name="exchange_rate"]').on('input', calculateTotals);

        $(document).on('submit', '#productForm', function(e) {
            e.preventDefault();
            
            const btn = $('#saveProductBtn');
            btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Guardando...');
            
            $.ajax({
                url: '{{ route("admin.products.quick-store") }}',
                method: 'POST',
                data: $(this).serialize(),
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    $('#productModal').modal('hide');
                    $('#productForm')[0].reset();
                    $('#productModal .select2').val('').trigger('change');
                    
                    const newOption = new Option(
                        `${response.product.name} (${response.product.code})`, 
                        response.product.id, 
                        false, 
                        false
                    );
                    
                    $('.select2-product').each(function() {
                        $(this).append(newOption.cloneNode(true));
                    });
                    
                    $('.select2-product').last().val(response.product.id).trigger('change');
                    
                    Swal.fire({
                        icon: 'success',
                        title: '¡Éxito!',
                        text: response.message,
                        timer: 2000,
                        showConfirmButton: false
                    });
                },
                error: function(xhr) {
                    if (xhr.status === 422) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error de validación',
                            text: Object.values(xhr.responseJSON.errors).flat().join(', ')
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Hubo un error al guardar el producto'
                        });
                    }
                },
                complete: function() {
                    btn.prop('disabled', false).html('<i class="fas fa-save"></i> Guardar Producto');
                }
            });
        });

        // Botón para crear producto rápido
        $(document).on('click', '.create-product-btn', function(e) {
            e.preventDefault();
            currentProductSelect = null;
            $('#productModal').modal('show');
        });

        $(document).on('click', '#addSupplierBtn', function() {
            console.log('addSupplierBtn clicked');
            $("#supplierModal").modal("show");
        });

        $(document).on("submit", "#supplierForm", function(e) {
            e.preventDefault();
            console.log('Supplier form submitted');
            const btn = $("#saveSupplierBtn");
            btn.prop("disabled", true).html("<i class=\"fas fa-spinner fa-spin\"></i> Guardando...");
            $.ajax({
                url: "/admin/suppliers/quick-store",
                method: "POST",
                data: $(this).serialize(),
                headers: {"X-CSRF-TOKEN": $("meta[name=\"csrf-token\"]").attr("content")},
                success: function(response) {
                    console.log('Supplier created:', response);
                    $("#supplierModal").modal("hide");
                    $("#supplierForm")[0].reset();
                    
                    // Create the option and add to select
                    const newOption = new Option(
                        response.supplier.name + " | " + (response.supplier.email || "Sin email") + " | " + (response.supplier.phone || "Sin teléfono"),
                        response.supplier.id,
                        false,
                        false
                    );
                    
                    // Add to select and select it
                    const $supplierSelect = $("#supplier_id");
                    $supplierSelect.append(newOption);
                    $supplierSelect.val(response.supplier.id).trigger('change.select2');
                    
                    Swal.fire({
                        icon: "success",
                        title: "¡Éxito!",
                        text: response.message,
                        timer: 2000,
                        showConfirmButton: false
                    });
                },
                error: function(xhr) {
                    console.log('Supplier error status:', xhr.status);
                    console.log('Supplier error response:', xhr.responseText);
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
                    } else if(xhr.status === 419) {
                        Swal.fire({
                            icon: "error",
                            title: "Error de seguridad",
                            text: "La sesión expiró. Por favor recargue la página."
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
            initSelect2();
            updateRemoveButtons();
            calculateTotals();

            document.getElementById('saveQuotationBtn').addEventListener('click', function() {
                confirmAction({
                    title: 'Actualizar Cotización',
                    message: '¿Está seguro de actualizar esta cotización?',
                    alert: 'Verifique que todos los datos sean correctos.',
                    confirmBtnClass: 'btn-primary',
                    onConfirm: function() {
                        document.getElementById('quotationForm').submit();
                    }
                });
            });
        });
    </script>
    @include('admin.partials.confirm-action')
@endsection
