@extends('adminlte::page')

@section('title', 'Editar Cotización')

@section('plugins.Select2', true)

@section('content_header')
    <h1><i class="fas fa-file-alt"></i> Editar Cotización {{ $quotation->code }}</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            @include('admin.partials.session-messages')

            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">Datos de la Cotización - {!! $quotation->status_badge !!}</h3>
                </div>

                <form action="{{ route('admin.quotations.update', $quotation) }}" method="POST" id="quotationForm">
                    @csrf @method('PUT')
                    <div class="card-body">
                        <div class="row">
                            <div class="col-12 col-md-4">
                                <div class="form-group">
                                    <label for="code">Código</label>
                                    <input type="text" name="code" class="form-control" value="{{ $quotation->code }}" readonly>
                                </div>
                            </div>
                            <div class="col-12 col-md-4">
                                <div class="form-group">
                                    <label for="supplier_reference">Ref. Proveedor</label>
                                    <input type="text" name="supplier_reference" class="form-control" value="{{ old('supplier_reference', $quotation->supplier_reference) }}">
                                </div>
                            </div>
                        </div>

                        <h4><i class="fas fa-building"></i> Datos del Proveedor</h4>
                        <hr>

                        <div class="row mb-3">
                            <div class="col-12">
                                <div class="btn-group" role="group">
                                    <input type="radio" class="btn-check" name="supplier_type" id="supplierRegistered" value="registered" {{ $quotation->hasRegisteredSupplier() ? 'checked' : '' }}>
                                    <label class="btn btn-outline-primary" for="supplierRegistered">Proveedor Registrado</label>

                                    <input type="radio" class="btn-check" name="supplier_type" id="supplierTemp" value="temp" {{ ! $quotation->hasRegisteredSupplier() ? 'checked' : '' }}>
                                    <label class="btn btn-outline-warning" for="supplierTemp">Proveedor Temporal</label>
                                </div>
                            </div>
                        </div>

                        <div id="registeredSupplierBlock" class="{{ ! $quotation->hasRegisteredSupplier() ? 'd-none' : '' }}">
                            <div class="row">
                                <div class="col-12 col-md-6">
                                    <div class="form-group">
                                        <label for="supplier_id">Proveedor (*)</label>
                                        <select name="supplier_id" id="supplier_id" class="form-control select2">
                                            <option value="">Seleccione...</option>
                                            @foreach($suppliers as $supplier)
                                                <option value="{{ $supplier->id }}" {{ $quotation->supplier_id == $supplier->id ? 'selected' : '' }}>
                                                    {{ $supplier->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div id="tempSupplierBlock" class="{{ $quotation->hasRegisteredSupplier() ? 'd-none' : '' }}">
                            <div class="row">
                                <div class="col-12 col-md-4">
                                    <div class="form-group">
                                        <label for="supplier_name_temp">Nombre del Proveedor (*)</label>
                                        <input type="text" name="supplier_name_temp" class="form-control" value="{{ old('supplier_name_temp', $quotation->supplier_name_temp) }}">
                                    </div>
                                </div>
                                <div class="col-12 col-md-4">
                                    <div class="form-group">
                                        <label for="supplier_email_temp">Email</label>
                                        <input type="email" name="supplier_email_temp" class="form-control" value="{{ old('supplier_email_temp', $quotation->supplier_email_temp) }}">
                                    </div>
                                </div>
                                <div class="col-12 col-md-4">
                                    <div class="form-group">
                                        <label for="supplier_phone_temp">Teléfono</label>
                                        <input type="text" name="supplier_phone_temp" class="form-control" value="{{ old('supplier_phone_temp', $quotation->supplier_phone_temp) }}">
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
                                    <input type="date" name="date_issued" class="form-control" value="{{ old('date_issued', $quotation->date_issued->format('Y-m-d')) }}" required>
                                </div>
                            </div>
                            <div class="col-12 col-md-4">
                                <div class="form-group">
                                    <label for="valid_until">Válido Hasta</label>
                                    <input type="date" name="valid_until" class="form-control" value="{{ old('valid_until', $quotation->valid_until?->format('Y-m-d')) }}">
                                </div>
                            </div>
                            <div class="col-12 col-md-4">
                                <div class="form-group">
                                    <label for="delivery_date">Fecha Entrega Ofertada</label>
                                    <input type="date" name="delivery_date" class="form-control" value="{{ old('delivery_date', $quotation->delivery_date?->format('Y-m-d')) }}">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12 col-md-6">
                                <div class="form-group">
                                    <label for="currency">Moneda</label>
                                    <select name="currency" class="form-control" style="width: 100%">
                                        <option value="USD" {{ $quotation->currency == 'USD' ? 'selected' : '' }}>USD - Dólar</option>
                                        <option value="VES" {{ $quotation->currency == 'VES' ? 'selected' : '' }}>VES - Bolívar</option>
                                        <option value="EUR" {{ $quotation->currency == 'EUR' ? 'selected' : '' }}>EUR - Euro</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-12 col-md-6">
                                <div class="form-group">
                                    <label for="exchange_rate">Tasa de Cambio</label>
                                    <input type="number" step="0.0001" name="exchange_rate" class="form-control" value="{{ old('exchange_rate', $quotation->exchange_rate) }}">
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
                                    @foreach($quotation->items as $index => $item)
                                        <tr>
                                            <td>
                                                <select name="items[{{ $index }}][product_id]" class="form-control select2-product" required>
                                                    @foreach($products as $product)
                                                        <option value="{{ $product->id }}" {{ $item->product_id == $product->id ? 'selected' : '' }}>
                                                            {{ $product->name }} ({{ $product->code ?? 'S/C' }})
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </td>
                                            <td>
                                                <input type="number" name="items[{{ $index }}][quantity]" class="form-control item-qty" min="1" value="{{ old("items.$index.quantity", $item->quantity) }}" required>
                                            </td>
                                            <td>
                                                <input type="number" step="0.01" name="items[{{ $index }}][unit_cost]" class="form-control item-cost" min="0" value="{{ old("items.$index.unit_cost", $item->unit_cost) }}" required>
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
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <th colspan="3" class="text-right">Total General:</th>
                                        <th id="grandTotal">${{ number_format($quotation->total, 2) }}</th>
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
                            <textarea name="notes" id="notes" rows="2" class="form-control">{{ old('notes', $quotation->notes) }}</textarea>
                        </div>
                    </div>

                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary btn-lg"><i class="fas fa-save"></i> Guardar Cambios</button>
                        <a href="{{ route('admin.quotations.show', $quotation) }}" class="btn btn-secondary btn-lg ml-2">Cancelar</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
@stop

@section('js')
    <script>
        let itemIndex = {{ $quotation->items->count() }};

        function toggleSupplierBlocks() {
            const type = $('input[name="supplier_type"]:checked').val();
            if (type === 'registered') {
                $('#registeredSupplierBlock').removeClass('d-none');
                $('#tempSupplierBlock').addClass('d-none');
            } else {
                $('#registeredSupplierBlock').addClass('d-none');
                $('#tempSupplierBlock').removeClass('d-none');
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

        const productOptions = `@foreach($products as $product)<option value="{{ $product->id }}">{{ $product->name }} ({{ $product->code ?? 'S/C' }})</option>@endforeach`;

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
        });

        $(document).on('click', '.remove-item', function() {
            $(this).closest('tr').remove();
            calculateTotals();
            updateRemoveButtons();
        });

        $(document).on('input', '.item-qty, .item-cost', calculateTotals);

        $('input[name="supplier_type"]').change(toggleSupplierBlocks);

        $(document).ready(function() {
            initSelect2();
            toggleSupplierBlocks();
            updateRemoveButtons();
            calculateTotals();
        });
    </script>
@endsection
