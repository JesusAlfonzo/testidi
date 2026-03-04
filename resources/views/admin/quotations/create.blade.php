@extends('adminlte::page')

@section('title', 'Registrar Cotización de Proveedor')

@section('plugins.Select2', true)

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
    
    <div class="row">
        <div class="col-12">
            @include('admin.partials.session-messages')

            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">Datos de la Cotización</h3>
                </div>

                <form action="{{ route('admin.quotations.store') }}" method="POST" id="quotationForm">
                    @csrf
                    <div class="card-body">
                        <div class="row">
                            <div class="col-12 col-md-4">
                                <div class="form-group">
                                    <label for="code">Código</label>
                                    <input type="text" name="code" class="form-control" value="{{ $code }}" readonly>
                                </div>
                            </div>
                            <div class="col-12 col-md-4">
                                <div class="form-group">
                                    <label for="rfq_id">RFQ Relacionada (Opcional)</label>
                                    <select name="rfq_id" id="rfq_id" class="form-control select2">
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
                                <div class="form-group">
                                    <label for="supplier_reference">Ref. Proveedor</label>
                                    <input type="text" name="supplier_reference" class="form-control" value="{{ old('supplier_reference') }}" placeholder="Número de cotización del proveedor">
                                </div>
                            </div>
                        </div>

                        <h4><i class="fas fa-building"></i> Datos del Proveedor</h4>
                        <hr>

                        <div class="row mb-3">
                            <div class="col-12">
                                <div class="btn-group" role="group">
                                    <input type="radio" class="btn-check" name="supplier_type" id="supplierRegistered" value="registered" {{ old('supplier_type') != 'temp' ? 'checked' : '' }}>
                                    <label class="btn btn-outline-primary" for="supplierRegistered">Proveedor Registrado</label>

                                    <input type="radio" class="btn-check" name="supplier_type" id="supplierTemp" value="temp" {{ old('supplier_type') == 'temp' ? 'checked' : '' }}>
                                    <label class="btn btn-outline-warning" for="supplierTemp">Proveedor Temporal</label>
                                </div>
                            </div>
                        </div>

                        <div id="registeredSupplierBlock" class="{{ old('supplier_type') == 'temp' ? 'd-none' : '' }}">
                            <div class="row">
                                <div class="col-12 col-md-6">
                                    <div class="form-group">
                                        <label for="supplier_id">Proveedor (*)</label>
                                        <select name="supplier_id" id="supplier_id" class="form-control select2">
                                            <option value="">Seleccione...</option>
                                            @foreach($suppliers as $supplier)
                                                <option value="{{ $supplier->id }}" {{ old('supplier_id') == $supplier->id ? 'selected' : '' }}>
                                                    {{ $supplier->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div id="tempSupplierBlock" class="{{ old('supplier_type') != 'temp' ? 'd-none' : '' }}">
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle"></i> Use esta opción si el proveedor aún no está registrado. Podrá registrarlo después.
                            </div>
                            <div class="row">
                                <div class="col-12 col-md-4">
                                    <div class="form-group">
                                        <label for="supplier_name_temp">Nombre del Proveedor (*)</label>
                                        <input type="text" name="supplier_name_temp" class="form-control" value="{{ old('supplier_name_temp') }}">
                                    </div>
                                </div>
                                <div class="col-12 col-md-4">
                                    <div class="form-group">
                                        <label for="supplier_email_temp">Email</label>
                                        <input type="email" name="supplier_email_temp" class="form-control" value="{{ old('supplier_email_temp') }}">
                                    </div>
                                </div>
                                <div class="col-12 col-md-4">
                                    <div class="form-group">
                                        <label for="supplier_phone_temp">Teléfono</label>
                                        <input type="text" name="supplier_phone_temp" class="form-control" value="{{ old('supplier_phone_temp') }}">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <h4 class="mt-4"><i class="fas fa-calendar"></i> Fechas</h4>
                        <hr>

                        <div class="row">
                            <div class="col-12 col-md-4">
                                <div class="form-group">
                                    <label for="date_issued">Fecha de Emisión (*)</label>
                                    <input type="date" name="date_issued" class="form-control" value="{{ old('date_issued', date('Y-m-d')) }}" required>
                                </div>
                            </div>
                            <div class="col-12 col-md-4">
                                <div class="form-group">
                                    <label for="valid_until">Válido Hasta</label>
                                    <input type="date" name="valid_until" class="form-control" value="{{ old('valid_until') }}">
                                </div>
                            </div>
                            <div class="col-12 col-md-4">
                                <div class="form-group">
                                    <label for="delivery_date">Fecha Entrega Ofertada</label>
                                    <input type="date" name="delivery_date" class="form-control" value="{{ old('delivery_date') }}">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12 col-md-6">
                                <div class="form-group">
                                    <label for="currency">Moneda</label>
                                    <select name="currency" class="form-control" style="width: 100%">
                                        <option value="USD" {{ old('currency') == 'USD' ? 'selected' : '' }}>USD - Dólar</option>
                                        <option value="VES" {{ old('currency') == 'VES' ? 'selected' : '' }}>VES - Bolívar</option>
                                        <option value="EUR" {{ old('currency') == 'EUR' ? 'selected' : '' }}>EUR - Euro</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-12 col-md-6">
                                <div class="form-group">
                                    <label for="exchange_rate">Tasa de Cambio</label>
                                    <input type="number" step="0.0001" name="exchange_rate" class="form-control" value="{{ old('exchange_rate', 1) }}">
                                </div>
                            </div>
                        </div>

                        <h4 class="mt-4"><i class="fas fa-boxes"></i> Items de la Cotización</h4>
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
                                    @if($selectedRfq)
                                        @foreach($selectedRfq->items as $index => $item)
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
                                                    <input type="number" step="0.01" name="items[{{ $index }}][unit_cost]" class="form-control item-cost" min="0" value="0" required>
                                                </td>
                                                <td>
                                                    <span class="item-total">0.00</span>
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
                                                            <option value="{{ $product->id }}">
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
                                                <input type="number" name="items[0][quantity]" class="form-control item-qty" min="1" value="1" required>
                                            </td>
                                            <td>
                                                <input type="number" step="0.01" name="items[0][unit_cost]" class="form-control item-cost" min="0" value="0" required>
                                            </td>
                                            <td>
                                                <span class="item-total">0.00</span>
                                            </td>
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
                                        <th id="grandTotal">$0.00</th>
                                        <th></th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>

                        <button type="button" id="addItem" class="btn btn-success btn-sm mt-2">
                            <i class="fas fa-plus"></i> Agregar Item
                        </button>

                        <div class="form-group mt-4">
                            <label for="notes">Notas</label>
                            <textarea name="notes" id="notes" rows="2" class="form-control">{{ old('notes') }}</textarea>
                        </div>
                    </div>

                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary btn-lg"><i class="fas fa-save"></i> Registrar Cotización</button>
                        <a href="{{ route('admin.quotations.index') }}" class="btn btn-secondary btn-lg ml-2">Cancelar</a>
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
        let itemIndex = {{ $selectedRfq ? $selectedRfq->items->count() : 1 }};
        let currentProductSelect = null;

        function toggleSupplierBlocks() {
            const type = $('input[name="supplier_type"]:checked').val();
            if (type === 'registered') {
                $('#registeredSupplierBlock').removeClass('d-none');
                $('#tempSupplierBlock').addClass('d-none');
                $('#supplier_id').prop('required', true);
                $('#supplier_name_temp').prop('required', false);
            } else {
                $('#registeredSupplierBlock').addClass('d-none');
                $('#tempSupplierBlock').removeClass('d-none');
                $('#supplier_id').prop('required', false);
                $('#supplier_name_temp').prop('required', true);
            }
        }

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
            $('.select2, .select2-product').select2({
                theme: 'bootstrap4',
                width: '100%'
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

        $('#addItem').click(function() {
            const row = `
                <tr>
                    <td>
                        <div class="input-group">
                            <select name="items[${itemIndex}][product_id]" class="form-control select2-product" required>
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
                        <input type="number" name="items[${itemIndex}][quantity]" class="form-control item-qty" min="1" value="1" required>
                    </td>
                    <td>
                        <input type="number" step="0.01" name="items[${itemIndex}][unit_cost]" class="form-control item-cost" min="0" value="0" required>
                    </td>
                    <td><span class="item-total">0.00</span></td>
                    <td class="text-center align-middle">
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
            attachProductButtonEvents();
        });

        function attachProductButtonEvents() {
            $('.create-product-btn').off('click').on('click', function(e) {
                e.preventDefault();
                currentProductSelect = $(this).closest('.input-group').find('.select2-product');
                $('#productModal').modal('show');
            });
        }

        $(document).on('click', '.remove-item', function() {
            $(this).closest('tr').remove();
            calculateTotals();
            updateRemoveButtons();
        });

        $(document).on('input', '.item-qty, .item-cost', calculateTotals);

        $('input[name="supplier_type"]').change(toggleSupplierBlocks);

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
            toggleSupplierBlocks();
            updateRemoveButtons();
            calculateTotals();
            attachProductButtonEvents();
        });
    </script>
@endsection
