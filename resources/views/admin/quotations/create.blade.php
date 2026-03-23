@extends('adminlte::page')

@section('title', 'Registrar Cotización de Proveedor')

@section('plugins.Select2', true)
@section('plugins.Sweetalert2', true)

@section('content_header')
    <h1><i class="fas fa-file-alt"></i> Registrar Cotización de Proveedor</h1>
@stop

@section('content')
    @php
        $categories = \App\Models\Category::orderBy('name')->get();
        $units = \App\Models\Unit::orderBy('name')->get();
        $locations = \App\Models\Location::orderBy('name')->get();
        $brands = \App\Models\Brand::orderBy('name')->get();
    @endphp

    <form action="{{ route('admin.quotations.store') }}" method="POST" id="quotationForm">
        @csrf
        
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
                                        <input type="text" name="code" class="form-control" value="{{ $code }}" readonly>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-md-5">
                                <div class="form-group mb-2">
                                    <label for="rfq_id" class="mb-1">RFQ Relacionada</label>
                                    <select name="rfq_id" id="rfq_id" class="form-control form-control-sm select2" data-placeholder="Seleccione RFQ" style="width: 100%;">
                                        <option value="">Ninguna</option>
                                        @foreach($rfqs as $rfq)
                                            <option value="{{ $rfq->id }}" {{ ($selectedRfq->id ?? null) == $rfq->id ? 'selected' : '' }}>
                                                {{ $rfq->code }} - {{ $rfq->title }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-12 col-md-4">
                                <div class="form-group mb-2">
                                    <label for="supplier_reference" class="mb-1">Ref. Proveedor</label>
                                    <div class="input-group input-group-sm">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text bg-secondary text-white"><i class="fas fa-tag"></i></span>
                                        </div>
                                        <input type="text" name="supplier_reference" class="form-control" value="{{ old('supplier_reference') }}" placeholder="Número de cotización del proveedor">
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
                                            <option value="{{ $supplier->id }}" {{ old('supplier_id') == $supplier->id ? 'selected' : '' }}>
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

        <!-- Sección: Fechas y Moneda -->
        <div class="row">
            <div class="col-12 col-md-8">
                <div class="card" style="border-left: 4px solid #3b82f6;">
                    <div class="card-header" style="background: linear-gradient(135deg, #3b82f6 0%, #60a5fa 100%);">
                        <h3 class="card-title text-white">
                            <i class="fas fa-calendar-alt"></i> Fechas
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
                                        <input type="date" name="date_issued" class="form-control" value="{{ old('date_issued', date('Y-m-d')) }}" required>
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
                                        <input type="date" name="valid_until" class="form-control" value="{{ old('valid_until') }}">
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
                                        <input type="date" name="delivery_date" class="form-control" value="{{ old('delivery_date') }}">
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
                                <div class="form-group">
                                    <label for="currency">Moneda</label>
                                    <select name="currency" class="form-control select2">
                                        <option value="USD" {{ old('currency') == 'USD' ? 'selected' : '' }}>💵 USD - Dólar</option>
                                        <option value="EUR" {{ old('currency') == 'EUR' ? 'selected' : '' }}>💶 EUR - Euro</option>
                                        <option value="Bs" {{ old('currency') == 'Bs' ? 'selected' : '' }}>🇻🇪 Bs - Bolívar</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-group">
                                    <label for="exchange_rate">Tasa de Cambio</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"><i class="fas fa-exchange-alt"></i></span>
                                        </div>
                                        <input type="number" step="0.0001" name="exchange_rate" class="form-control" value="{{ old('exchange_rate', 1) }}">
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
                                        <th style="width: 35%">Producto</th>
                                        <th style="width: 15%">Cantidad</th>
                                        <th style="width: 18%">Costo Unit.</th>
                                        <th style="width: 17%">Total</th>
                                        <th style="width: 5%"></th>
                                    </tr>
                                </thead>
                                <tbody id="itemsBody">
                                    @if($selectedRfq)
                                        @foreach($selectedRfq->items as $index => $item)
                                            <tr>
                                                <td>
                                                    <select name="items[{{ $index }}][product_id]" class="form-control select2-product" required>
                                                        @foreach($products as $product)
                                                            <option value="{{ $product->id }}" data-cost="{{ $product->cost ?? 0 }}" {{ $item->product_id == $product->id ? 'selected' : '' }}>
                                                                {{ $product->name }} ({{ $product->code ?? 'S/C' }})
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </td>
                                                <td>
                                                    <input type="number" name="items[{{ $index }}][quantity]" class="form-control item-qty" min="1" value="{{ $item->quantity }}" required>
                                                </td>
                                                <td>
                                                    <input type="number" step="0.01" name="items[{{ $index }}][unit_cost]" class="form-control item-cost" min="0" value="{{ $item->unit_cost ?? 0 }}" required>
                                                </td>
                                                <td class="text-right">
                                                    <span class="item-total font-weight-bold">{{ number_format(($item->quantity * ($item->unit_cost ?? 0)), 2) }}</span>
                                                </td>
                                                <td class="text-center">
                                                    <button type="button" class="btn btn-sm btn-danger remove-item" {{ $selectedRfq->items->count() <= 1 ? 'style=display:none' : '' }}>
                                                        <i class="fas fa-times"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        @endforeach
                                    @else
                                        <tr>
                                            <td>
                                                <select name="items[0][product_id]" class="form-control select2-product" required>
                                                    <option value="">Seleccione...</option>
                                                    @foreach($products as $product)
                                                        <option value="{{ $product->id }}" data-cost="{{ $product->cost ?? 0 }}">
                                                            {{ $product->name }} ({{ $product->code ?? 'S/C' }})
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </td>
                                            <td>
                                                <input type="number" name="items[0][quantity]" class="form-control item-qty" min="1" value="1" required>
                                            </td>
                                            <td>
                                                <input type="number" step="0.01" name="items[0][unit_cost]" class="form-control item-cost" min="0" value="0" required>
                                            </td>
                                            <td class="text-right">
                                                <span class="item-total font-weight-bold">0.00</span>
                                            </td>
                                            <td class="text-center">
                                                <button type="button" class="btn btn-sm btn-danger remove-item" style="display:none;">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    @endif
                                </tbody>
                                <tfoot class="bg-success-light">
                                    <tr>
                                        <th colspan="3" class="text-right">TOTAL GENERAL:</th>
                                        <th class="text-right"><span id="grandTotal" class="h5 text-success">$0.00</span></th>
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
                                    <tr class="bg-info-light">
                                        <th colspan="3" class="text-right">TOTAL Bs (con IVA):</th>
                                        <th class="text-right"><span id="totalBs" class="h5 text-primary">Bs 0.00</span></th>
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
                            <i class="fas fa-sticky-note"></i> Notas Adicionales
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="form-group mb-0">
                            <textarea name="notes" id="notes" rows="3" class="form-control" placeholder="Agregue notas o comentarios adicionales sobre esta cotización...">{{ old('notes') }}</textarea>
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
                            <i class="fas fa-save"></i> Registrar Cotización
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
                <div class="modal-header bg-primary">
                    <h5 class="modal-title" id="productModalLabel"><i class="fas fa-box"></i> Crear Nuevo Producto</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
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
                                    <small class="text-danger" id="codeError" style="display:none;">El código ya existe</small>
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
                        <div class="row">
                            <div class="col-12">
                                <div class="form-group">
                                    <label for="product_description">Descripción</label>
                                    <textarea name="description" id="product_description" rows="2" class="form-control"></textarea>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12 col-md-4">
                                <div class="form-group">
                                    <label for="product_cost">Costo</label>
                                    <input type="number" step="0.01" name="cost" id="product_cost" class="form-control" value="0" min="0">
                                </div>
                            </div>
                            <div class="col-12 col-md-4">
                                <div class="form-group">
                                    <label for="product_price">Precio</label>
                                    <input type="number" step="0.01" name="price" id="product_price" class="form-control" value="0" min="0">
                                </div>
                            </div>
                            <div class="col-12 col-md-4">
                                <div class="form-group">
                                    <label for="product_min_stock">Stock Mínimo</label>
                                    <input type="number" name="min_stock" id="product_min_stock" class="form-control" value="0" min="0">
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
@stop

@section('css')
    <style>
        .select2-container--default .select2-selection--single {
            height: 34px;
            padding-top: 4px;
        }
        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 34px;
        }
        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 24px;
        }
        .form-group-sm .select2-container--default .select2-selection--single {
            height: 31px;
            padding-top: 2px;
        }
        .form-group-sm .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 31px;
        }
        .bg-success-light {
            background-color: #d4edda;
        }
        .item-total {
            font-size: 1.1em;
        }
    </style>
@endsection

@section('js')
    <script>
        console.log('Test at start of script');
        
        let itemIndex = {{ $selectedRfq ? $selectedRfq->items->count() : 1 }};
        let currentProductSelect = null;

        function updateRemoveButtons() {
            const rows = $('#itemsBody tr').length;
            $('#itemsBody .remove-item').toggle(rows > 1);
        }

        function initSelect2() {
            $('.select2').select2({
                theme: 'bootstrap4',
                width: '100%',
                allowClear: true
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

        function calculateTotals() {
            const currency = $('select[name="currency"]').val();
            const exchangeRate = parseFloat($('input[name="exchange_rate"]').val()) || 0;
            const symbol = currency === 'USD' ? '$' : (currency === 'EUR' ? '€' : 'Bs ');
            const isBs = currency === 'Bs';
            
            let grandTotal = 0;
            let grandTotalBs = 0;
            
            $('#itemsBody > tr').each(function() {
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
            const totalBsFinal = grandTotalBs + ivaBs;
            
            $('#grandTotalBs').text('Bs ' + grandTotalBs.toFixed(2));
            $('#ivaBs').text('Bs ' + ivaBs.toFixed(2));
            $('#totalBs').text('Bs ' + totalBsFinal.toFixed(2));
        }

        const productOptions = `@foreach($products as $product)<option value="{{ $product->id }}" data-cost="{{ $product->cost ?? 0 }}">{{ $product->name }} ({{ $product->code ?? 'S/C' }})</option>@endforeach`;

        $('#addItem').click(function() {
            const row = `
                <tr>
                    <td>
                        <select name="items[${itemIndex}][product_id]" class="form-control select2-product" required>
                            <option value="">Seleccione...</option>
                            ${productOptions}
                        </select>
                    </td>
                    <td>
                        <input type="number" name="items[${itemIndex}][quantity]" class="form-control item-qty" min="1" value="1" required>
                    </td>
                    <td>
                        <input type="number" step="0.01" name="items[${itemIndex}][unit_cost]" class="form-control item-cost" min="0" value="0" required>
                    </td>
                    <td class="text-right">
                        <span class="item-total font-weight-bold">0.00</span>
                    </td>
                    <td class="text-center">
                        <button type="button" class="btn btn-sm btn-danger remove-item">
                            <i class="fas fa-times"></i>
                        </button>
                    </td>
                </tr>
            `;
            $('#itemsBody').append(row);
            itemIndex++;
            initSelect2();
            updateRemoveButtons();
        });

        $(document).on('select2:select', '.select2-product', function(e) {
            const cost = $(this).find('option:selected').data('cost') || 0;
            $(this).closest('tr').find('.item-cost').val(cost);
            calculateTotals();
        });

        $(document).on('click', '.remove-item', function() {
            $(this).closest('tr').remove();
            calculateTotals();
            updateRemoveButtons();
        });

        $(document).on('input', '.item-qty, .item-cost', calculateTotals);

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
                        const errors = xhr.responseJSON.errors;
                        if (errors.code) {
                            $('#codeError').show();
                        }
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

        $('#productModal').on('hidden.bs.modal', function() {
            $('#productForm')[0].reset();
            $('#codeError').hide();
            $('#productModal .select2').val('').trigger('change');
        });

        $('#product_code').on('blur', function() {
            const code = $(this).val();
            if (code) {
                $.get('{{ route("admin.products.search") }}', { search: code }, function(products) {
                    const exists = products.some(p => p.code.toLowerCase() === code.toLowerCase());
                    $('#codeError').toggle(exists);
                });
            }
        });

        
        // Botón para abrir modal de proveedor
        $(document).on('click', '#addSupplierBtn', function() {
            console.log('addSupplierBtn clicked');
            $("#supplierModal").modal("show");
        });

        // Click en botón guardar proveedor (alternativa al submit)
        $(document).on('click', '#saveSupplierBtn', function(e) {
            e.preventDefault();
            e.stopPropagation();
            console.log('Save supplier button clicked');
            const btn = $(this);
            btn.prop("disabled", true).html("<i class=\"fas fa-spinner fa-spin\"></i> Guardando...");
            
            const formData = $("#supplierForm").serialize();
            console.log('Form data:', formData);
            
            $.ajax({
                url: "/admin/suppliers/quick-store",
                method: "POST",
                data: formData,
                headers: {"X-CSRF-TOKEN": $("meta[name=\"csrf-token\"]").attr("content")},
                success: function(response) {
                    console.log('Supplier created:', response);
                    $("#supplierModal").modal("hide");
                    $("#supplierForm")[0].reset();
                    
                    const newOption = new Option(
                        response.supplier.name + " | " + (response.supplier.email || "Sin email") + " | " + (response.supplier.phone || "Sin teléfono"),
                        response.supplier.id,
                        false,
                        false
                    );
                    
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

        // Prevenir submit normal del formulario de proveedor
        $(document).on("submit", "#supplierForm", function(e) {
            e.preventDefault();
            $("#saveSupplierBtn").click();
        });

        $("#supplierModal").on("hidden.bs.modal", function() {
            $("#supplierForm")[0].reset();
            $("#supplierTaxIdError").hide();
        });

        $("#supplier_tax_id").on("blur", function() {
            const taxId = $(this).val();
            if(taxId) {
                $.get("/admin/suppliers", { search: taxId }, function(data) {
                    $("#supplierTaxIdError").hide();
                });
            }
        });

$(document).ready(function() {
            initSelect2();
            updateRemoveButtons();
            calculateTotals();

            // Modal de confirmación para guardar cotización
            document.getElementById('saveQuotationBtn').addEventListener('click', function() {
                confirmAction({
                    title: 'Crear Cotización',
                    message: '¿Está seguro de registrar esta cotización?',
                    alert: 'Verifique que todos los datos, productos y precios sean correctos.',
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
