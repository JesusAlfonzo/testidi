@extends('adminlte::page')

@section('title', 'Crear Orden de Compra')

@section('plugins.Select2', true)

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
    
    <div class="row">
        <div class="col-12">
            @include('admin.partials.session-messages')

            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">Datos de la Orden</h3>
                </div>

                <form action="{{ route('admin.purchaseOrders.store') }}" method="POST" id="orderForm">
                    @csrf
                    <div class="card-body">
                        @if($quote)
                            <div class="alert alert-success">
                                <i class="fas fa-link"></i> Generada desde cotización <strong>{{ $quote->code }}</strong>
                                <input type="hidden" name="purchase_quote_id" value="{{ $quote->id }}">
                            </div>
                        @endif

                        <div class="row">
                            <div class="col-12 col-md-4">
                                <div class="form-group">
                                    <label for="code">Código OC</label>
                                    <input type="text" name="code" class="form-control" value="{{ $code }}" readonly>
                                </div>
                            </div>
                            <div class="col-12 col-md-4">
                                <div class="form-group">
                                    <label for="supplier_id">Proveedor (*)</label>
                                    <select name="supplier_id" id="supplier_id" class="form-control select2" required>
                                        <option value="">Seleccione...</option>
                                        @foreach($suppliers as $supplier)
                                            <option value="{{ $supplier->id }}" {{ ($quote->supplier_id ?? null) == $supplier->id ? 'selected' : '' }}>
                                                {{ $supplier->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-12 col-md-4">
                                <div class="form-group">
                                    <label for="date_issued">Fecha de Emisión (*)</label>
                                    <input type="date" name="date_issued" class="form-control" value="{{ old('date_issued', date('Y-m-d')) }}" required>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12 col-md-4">
                                <div class="form-group">
                                    <label for="delivery_date">Fecha de Entrega</label>
                                    <input type="date" name="delivery_date" class="form-control" value="{{ old('delivery_date', $quote?->delivery_date?->format('Y-m-d')) }}">
                                </div>
                            </div>
                            <div class="col-12 col-md-4">
                                <div class="form-group">
                                    <label for="currency">Moneda</label>
                                    <select name="currency" class="form-control">
                                        <option value="USD" {{ old('currency', $quote?->currency) == 'USD' ? 'selected' : '' }}>USD - Dólar</option>
                                        <option value="VES" {{ old('currency', $quote?->currency) == 'VES' ? 'selected' : '' }}>VES - Bolívar</option>
                                        <option value="EUR" {{ old('currency', $quote?->currency) == 'EUR' ? 'selected' : '' }}>EUR - Euro</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-12 col-md-4">
                                <div class="form-group">
                                    <label for="exchange_rate">Tasa de Cambio</label>
                                    <input type="number" step="0.0001" name="exchange_rate" class="form-control" value="{{ old('exchange_rate', $quote?->exchange_rate ?? 1) }}">
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="delivery_address">Dirección de Entrega</label>
                            <input type="text" name="delivery_address" class="form-control" value="{{ old('delivery_address') }}" placeholder="Dirección donde recibir la mercancía">
                        </div>

                        <h4 class="mt-4"><i class="fas fa-boxes"></i> Items de la Orden</h4>
                        <hr>

                        <div class="table-responsive">
                            <table class="table table-bordered" id="itemsTable">
                                <thead class="thead-light">
                                    <tr>
                                        <th style="width: 40%">Producto (*)</th>
                                        <th style="width: 15%">Cantidad (*)</th>
                                        <th style="width: 20%">Costo Unit. (*)</th>
                                        <th style="width: 20%">Total</th>
                                        <th style="width: 5%"></th>
                                    </tr>
                                </thead>
                                <tbody id="itemsBody">
                                    @if($quote)
                                        @foreach($quote->items as $index => $item)
                                            <tr>
                                                <td>
                                                    <div class="input-group">
                                                        <select name="items[{{ $index }}][product_id]" class="form-control select2-product" required>
                                                            @foreach($products as $product)
                                                                <option value="{{ $product->id }}" {{ $item->product_id == $product->id ? 'selected' : '' }}>
                                                                    {{ $product->name }} ({{ $product->code ?? 'S/C' }})
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                        <div class="input-group-append">
                                                            <button type="button" class="btn btn-outline-primary btn-sm create-product-btn" title="Crear producto">
                                                                <i class="fas fa-plus"></i>
                                                            </button>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <input type="number" name="items[{{ $index }}][quantity]" class="form-control item-qty" min="1" value="{{ $item->quantity }}" required>
                                                </td>
                                                <td>
                                                    <input type="number" step="0.01" name="items[{{ $index }}][unit_cost]" class="form-control item-cost" min="0" value="{{ $item->unit_cost }}" required>
                                                </td>
                                                <td>
                                                    <span class="item-total">{{ number_format($item->quantity * $item->unit_cost, 2) }}</span>
                                                </td>
                                                <td class="text-center align-middle">
                                                    <button type="button" class="btn btn-sm btn-danger remove-item" style="display:none;">
                                                        <i class="fas fa-times"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        @endforeach
                                    @else
                                        <tr>
                                            <td>
                                                <div class="input-group">
                                                    <select name="items[0][product_id]" class="form-control select2-product" required>
                                                        <option value="">Seleccione...</option>
                                                        @foreach($products as $product)
                                                            <option value="{{ $product->id }}">{{ $product->name }} ({{ $product->code ?? 'S/C' }})</option>
                                                        @endforeach
                                                    </select>
                                                    <div class="input-group-append">
                                                        <button type="button" class="btn btn-outline-primary btn-sm create-product-btn" title="Crear producto">
                                                            <i class="fas fa-plus"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <input type="number" name="items[0][quantity]" class="form-control item-qty" min="1" value="1" required>
                                            </td>
                                            <td>
                                                <input type="number" step="0.01" name="items[0][unit_cost]" class="form-control item-cost" min="0" value="0" required>
                                            </td>
                                            <td><span class="item-total">0.00</span></td>
                                            <td class="text-center align-middle">
                                                <button type="button" class="btn btn-sm btn-danger remove-item" style="display:none;">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    @endif
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <th colspan="3" class="text-right">Total General:</th>
                                        <th id="grandTotal">${{ number_format($quote?->total ?? 0, 2) }}</th>
                                        <th></th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>

                        <button type="button" id="addItem" class="btn btn-success btn-sm mt-2">
                            <i class="fas fa-plus"></i> Agregar Item
                        </button>

                        <div class="form-group mt-4">
                            <label for="terms">Términos y Condiciones</label>
                            <textarea name="terms" id="terms" rows="3" class="form-control" placeholder="Condiciones de pago, garantías, etc.">{{ old('terms') }}</textarea>
                        </div>

                        <div class="form-group">
                            <label for="notes">Notas Internas</label>
                            <textarea name="notes" id="notes" rows="2" class="form-control">{{ old('notes') }}</textarea>
                        </div>
                    </div>

                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary btn-lg"><i class="fas fa-save"></i> Guardar Orden</button>
                        <a href="{{ route('admin.purchaseOrders.index') }}" class="btn btn-secondary btn-lg ml-2">Cancelar</a>
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
            $('.select2, .select2-product').select2({ theme: 'bootstrap4', width: '100%' });
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
                        <div class="input-group">
                            <select name="items[${itemIndex}][product_id]" class="form-control select2-product" required>
                                <option value="">Seleccione...</option>
                                ${productOptions}
                            </select>
                            <div class="input-group-append">
                                <button type="button" class="btn btn-outline-primary btn-sm create-product-btn" title="Crear producto">
                                    <i class="fas fa-plus"></i>
                                </button>
                            </div>
                        </div>
                    </td>
                    <td><input type="number" name="items[${itemIndex}][quantity]" class="form-control item-qty" min="1" value="1" required></td>
                    <td><input type="number" step="0.01" name="items[${itemIndex}][unit_cost]" class="form-control item-cost" min="0" value="0" required></td>
                    <td><span class="item-total">0.00</span></td>
                    <td class="text-center align-middle">
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
        });
    </script>
@endsection
