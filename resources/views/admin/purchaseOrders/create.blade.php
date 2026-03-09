@extends('adminlte::page')

@section('title', 'Crear Orden de Compra')

@section('plugins.Select2', true)
@section('plugins.Sweetalert2', true)

@section('content_header')
    <h1><i class="fas fa-shopping-cart"></i> Nueva Orden de Compra</h1>
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
                        @if($quote)
                            <div class="alert alert-success mb-3">
                                <i class="fas fa-link"></i> Generada desde cotización <strong>{{ $quote->code }}</strong>
                                <input type="hidden" name="purchase_quote_id" value="{{ $quote->id }}">
                            </div>
                        @endif

                        <div class="row">
                            <div class="col-12 col-md-3">
                                <div class="form-group mb-2">
                                    <label for="code" class="mb-1">Código OC</label>
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
                                    <label for="supplier_id" class="mb-1">Proveedor (*)</label>
                                    <select name="supplier_id" id="supplier_id" class="form-control form-control-sm select2-ajax" data-placeholder="Buscar proveedor..." data-url="{{ route('admin.purchaseOrders.searchSuppliers') }}" required>
                                        @if($quote && $quote->supplier)
                                            <option value="{{ $quote->supplier->id }}" selected>{{ $quote->supplier->name }} | {{ $quote->supplier->email ?? 'Sin email' }}</option>
                                        @endif
                                    </select>
                                </div>
                            </div>
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
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sección: Entrega y Moneda -->
        <div class="row">
            <div class="col-12 col-md-8">
                <div class="card" style="border-left: 4px solid #3b82f6;">
                    <div class="card-header" style="background: linear-gradient(135deg, #3b82f6 0%, #60a5fa 100%);">
                        <h3 class="card-title text-white">
                            <i class="fas fa-truck"></i> Entrega
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-12 col-md-6">
                                <div class="form-group mb-2">
                                    <label for="delivery_date" class="mb-1">Fecha de Entrega</label>
                                    <div class="input-group input-group-sm">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text bg-info text-white"><i class="fas fa-calendar-check"></i></span>
                                        </div>
                                        <input type="date" name="delivery_date" class="form-control" value="{{ old('delivery_date', $quote?->delivery_date?->format('Y-m-d')) }}">
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-md-6">
                                <div class="form-group mb-2">
                                    <label for="delivery_address" class="mb-1">Dirección de Entrega</label>
                                    <div class="input-group input-group-sm">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text bg-info text-white"><i class="fas fa-map-marker-alt"></i></span>
                                        </div>
                                        <input type="text" name="delivery_address" class="form-control" value="{{ old('delivery_address') }}" placeholder="Dirección donde recibir la mercancía">
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
                                        <option value="USD" {{ old('currency', $quote?->currency) == 'USD' ? 'selected' : '' }}>💵 USD - Dólar</option>
                                        <option value="VES" {{ old('currency', $quote?->currency) == 'VES' ? 'selected' : '' }}>🇻🇪 VES - Bolívar</option>
                                        <option value="EUR" {{ old('currency', $quote?->currency) == 'EUR' ? 'selected' : '' }}>💶 EUR - Euro</option>
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
                                        <input type="number" step="0.0001" name="exchange_rate" class="form-control" value="{{ old('exchange_rate', $quote?->exchange_rate ?? 1) }}">
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
                                <i class="fas fa-boxes"></i> Items de la Orden
                            </h3>
                            <button type="button" id="addItem" class="btn btn-sm btn-light text-danger">
                                <i class="fas fa-plus"></i> Agregar Item
                            </button>
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
                                    @if($quote)
                                        @foreach($quote->items as $index => $item)
                                            <tr>
                                                <td>
                                                    <select name="items[{{ $index }}][product_id]" class="form-control form-control-sm select2-product-ajax" data-url="{{ route('admin.purchaseOrders.searchProducts') }}" required>
                                                        <option value="{{ $item->product_id }}" selected>{{ $item->product->name }} ({{ $item->product->code ?? 'S/C' }})</option>
                                                    </select>
                                                </td>
                                                <td>
                                                    <input type="number" name="items[{{ $index }}][quantity]" class="form-control form-control-sm item-qty" min="1" value="{{ $item->quantity }}" required>
                                                </td>
                                                <td>
                                                    <input type="number" step="0.01" name="items[{{ $index }}][unit_cost]" class="form-control form-control-sm item-cost" min="0" value="{{ $item->unit_cost }}" required>
                                                </td>
                                                <td class="text-right">
                                                    <span class="item-total font-weight-bold">{{ number_format($item->quantity * $item->unit_cost, 2) }}</span>
                                                </td>
                                                <td class="text-center">
                                                    <button type="button" class="btn btn-sm btn-danger remove-item" {{ $quote->items->count() <= 1 ? 'style=display:none' : '' }}>
                                                        <i class="fas fa-times"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        @endforeach
                                    @else
                                        <tr>
                                            <td>
                                                <select name="items[0][product_id]" class="form-control form-control-sm select2-product-ajax" data-url="{{ route('admin.purchaseOrders.searchProducts') }}" required>
                                                    <option value="">Buscar producto...</option>
                                                </select>
                                            </td>
                                            <td>
                                                <input type="number" name="items[0][quantity]" class="form-control form-control-sm item-qty" min="1" value="1" required>
                                            </td>
                                            <td>
                                                <input type="number" step="0.01" name="items[0][unit_cost]" class="form-control form-control-sm item-cost" min="0" value="0" required>
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
                                        <th class="text-right"><span id="grandTotal" class="h5 text-success">${{ number_format($quote?->total ?? 0, 2) }}</span></th>
                                        <th></th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sección: Notas y Términos -->
        <div class="row">
            <div class="col-12 col-md-6">
                <div class="card" style="border-left: 4px solid #9ca3af;">
                    <div class="card-header" style="background: linear-gradient(135deg, #9ca3af 0%, #d1d5db 100%);">
                        <h3 class="card-title text-white">
                            <i class="fas fa-sticky-note"></i> Términos y Condiciones
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="form-group mb-0">
                            <textarea name="terms" id="terms" rows="3" class="form-control form-control-sm" placeholder="Condiciones de pago, garantías, etc.">{{ old('terms') }}</textarea>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-6">
                <div class="card" style="border-left: 4px solid #9ca3af;">
                    <div class="card-header" style="background: linear-gradient(135deg, #9ca3af 0%, #d1d5db 100%);">
                        <h3 class="card-title text-white">
                            <i class="fas fa-sticky-note"></i> Notas Internas
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="form-group mb-0">
                            <textarea name="notes" id="notes" rows="3" class="form-control form-control-sm">{{ old('notes') }}</textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Botones de Acción -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body d-flex justify-content-between">
                        <a href="{{ route('admin.purchaseOrders.index') }}" class="btn btn-secondary btn-lg">
                            <i class="fas fa-times"></i> Cancelar
                        </a>
                        <button type="button" class="btn btn-primary btn-lg" id="saveOrderBtn">
                            <i class="fas fa-save"></i> Guardar Orden
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>

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
        .bg-success-light {
            background-color: #d4edda;
        }
    </style>
@endsection

@section('js')
    <script>
        let itemIndex = {{ $quote ? $quote->items->count() : 1 }};
        let currentProductSelect = null;

        function calculateTotals() {
            let grandTotal = 0;
            $('#itemsBody tr').each(function() {
                const qty = parseFloat($(this).find('.item-qty').val()) || 0;
                const cost = parseFloat($(this).find('.item-cost').val()) || 0;
                const total = qty * cost;
                $(this).find('.item-total').text(total.toFixed(2));
                grandTotal += total;
            });
            $('#grandTotal').text('$' + grandTotal.toFixed(2));
        }

        function initSelect2() {
            // Select2 normal (proveedores)
            $('.select2').select2({
                theme: 'bootstrap4',
                width: '100%',
                allowClear: true
            });

            // Select2 AJAX para proveedores
            $('.select2-ajax').each(function() {
                if (!$(this).hasClass('select2initialized')) {
                    $(this).select2({
                        theme: 'bootstrap4',
                        width: '100%',
                        allowClear: true,
                        ajax: {
                            url: $(this).data('url'),
                            dataType: 'json',
                            delay: 250,
                            processResults: function(data) {
                                return {
                                    results: data.results
                                };
                            },
                            cache: true
                        }
                    }).addClass('select2initialized');
                }
            });

            // Select2 AJAX para productos
            $('.select2-product-ajax').each(function() {
                if (!$(this).hasClass('select2initialized')) {
                    $(this).select2({
                        theme: 'bootstrap4',
                        width: '100%',
                        allowClear: true,
                        ajax: {
                            url: $(this).data('url'),
                            dataType: 'json',
                            delay: 250,
                            processResults: function(data) {
                                return {
                                    results: data.results
                                };
                            },
                            cache: true
                        }
                    }).addClass('select2initialized');
                }
            });
        }

        function updateRemoveButtons() {
            const rows = $('#itemsBody tr').length;
            $('#itemsBody .remove-item').toggle(rows > 1);
        }

        function addProductOption(product) {
            const newOption = new Option(
                `${product.name} (${product.code})`, 
                product.id, 
                false, 
                false
            );
            return newOption;
        }

        function refreshProductSelects() {
            $.get('{{ route("admin.products.search") }}', function(products) {
                $('.select2-product').each(function() {
                    const currentVal = $(this).val();
                    $(this).empty();
                    $(this).append('<option value="">Seleccione...</option>');
                    products.forEach(function(product) {
                        $(this).append(addProductOption(product));
                    }, $(this));
                    $(this).val(currentVal).trigger('change');
                });
            });
        }

        function attachProductButtonEvents() {
            $('.create-product-btn').off('click').on('click', function(e) {
                e.preventDefault();
                currentProductSelect = $(this).closest('.input-group').find('.select2-product');
                $('#productModal').modal('show');
            });
        }

        const productOptions = `@foreach($products as $product)<option value="{{ $product->id }}">{{ $product->name }} ({{ $product->code ?? 'S/C' }})</option>@endforeach`;

        $('#addItem').click(function() {
            const row = `
                <tr>
                    <td>
                        <select name="items[${itemIndex}][product_id]" class="form-control form-control-sm select2-product" required>
                            <option value="">Seleccione...</option>
                            ${productOptions}
                        </select>
                    </td>
                    <td><input type="number" name="items[${itemIndex}][quantity]" class="form-control form-control-sm item-qty" min="1" value="1" required></td>
                    <td><input type="number" step="0.01" name="items[${itemIndex}][unit_cost]" class="form-control form-control-sm item-cost" min="0" value="0" required></td>
                    <td class="text-right"><span class="item-total font-weight-bold">0.00</span></td>
                    <td class="text-center">
                        <button type="button" class="btn btn-sm btn-danger remove-item"><i class="fas fa-times"></i></button>
                    </td>
                </tr>
            `;
            $('#itemsBody').append(row);
            itemIndex++;
            initSelect2();
            updateRemoveButtons();
            attachProductButtonEvents();
        });

        $(document).on('click', '.remove-item', function() {
            $(this).closest('tr').remove();
            calculateTotals();
            updateRemoveButtons();
        });

        $(document).on('input', '.item-qty, .item-cost', calculateTotals);

        $('#productForm').on('submit', function(e) {
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
                    
                    if (currentProductSelect) {
                        const newOption = new Option(
                            `${response.product.name} (${response.product.code})`, 
                            response.product.id, 
                            true, 
                            true
                        );
                        currentProductSelect.append(newOption).trigger('change');
                    }
                    
                    refreshProductSelects();
                    
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

        $(document).ready(function() {
            initSelect2();
            updateRemoveButtons();
            calculateTotals();
            attachProductButtonEvents();

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
