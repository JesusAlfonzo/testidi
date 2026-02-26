@extends('adminlte::page')

@section('title', 'Editar Orden de Compra')

@section('plugins.Select2', true)

@section('content_header')
    <h1><i class="fas fa-shopping-cart"></i> Editar Orden de Compra {{ $purchaseOrder->code }}</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            @include('admin.partials.session-messages')

            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">Datos de la Orden - {!! $purchaseOrder->status_badge !!}</h3>
                </div>

                <form action="{{ route('admin.purchaseOrders.update', $purchaseOrder) }}" method="POST" id="orderForm">
                    @csrf @method('PUT')
                    <div class="card-body">
                        <div class="row">
                            <div class="col-12 col-md-4">
                                <div class="form-group">
                                    <label for="code">Código OC</label>
                                    <input type="text" name="code" class="form-control" value="{{ $purchaseOrder->code }}" readonly>
                                </div>
                            </div>
                            <div class="col-12 col-md-4">
                                <div class="form-group">
                                    <label for="supplier_id">Proveedor (*)</label>
                                    <select name="supplier_id" id="supplier_id" class="form-control select2" required>
                                        <option value="">Seleccione...</option>
                                        @foreach($suppliers as $supplier)
                                            <option value="{{ $supplier->id }}" {{ $purchaseOrder->supplier_id == $supplier->id ? 'selected' : '' }}>
                                                {{ $supplier->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-12 col-md-4">
                                <div class="form-group">
                                    <label for="date_issued">Fecha de Emisión (*)</label>
                                    <input type="date" name="date_issued" class="form-control" value="{{ old('date_issued', $purchaseOrder->date_issued->format('Y-m-d')) }}" required>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12 col-md-4">
                                <div class="form-group">
                                    <label for="delivery_date">Fecha de Entrega</label>
                                    <input type="date" name="delivery_date" class="form-control" value="{{ old('delivery_date', $purchaseOrder->delivery_date?->format('Y-m-d')) }}">
                                </div>
                            </div>
                            <div class="col-12 col-md-4">
                                <div class="form-group">
                                    <label for="currency">Moneda</label>
                                    <select name="currency" class="form-control">
                                        <option value="USD" {{ $purchaseOrder->currency == 'USD' ? 'selected' : '' }}>USD - Dólar</option>
                                        <option value="VES" {{ $purchaseOrder->currency == 'VES' ? 'selected' : '' }}>VES - Bolívar</option>
                                        <option value="EUR" {{ $purchaseOrder->currency == 'EUR' ? 'selected' : '' }}>EUR - Euro</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-12 col-md-4">
                                <div class="form-group">
                                    <label for="exchange_rate">Tasa de Cambio</label>
                                    <input type="number" step="0.0001" name="exchange_rate" class="form-control" value="{{ old('exchange_rate', $purchaseOrder->exchange_rate) }}">
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="delivery_address">Dirección de Entrega</label>
                            <input type="text" name="delivery_address" class="form-control" value="{{ old('delivery_address', $purchaseOrder->delivery_address) }}" placeholder="Dirección donde recibir la mercancía">
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
                                    @foreach($purchaseOrder->items as $index => $item)
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
                                        <th id="grandTotal">${{ number_format($purchaseOrder->total, 2) }}</th>
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
                            <textarea name="terms" id="terms" rows="3" class="form-control" placeholder="Condiciones de pago, garantías, etc.">{{ old('terms', $purchaseOrder->terms) }}</textarea>
                        </div>

                        <div class="form-group">
                            <label for="notes">Notas Internas</label>
                            <textarea name="notes" id="notes" rows="2" class="form-control">{{ old('notes', $purchaseOrder->notes) }}</textarea>
                        </div>
                    </div>

                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary btn-lg"><i class="fas fa-save"></i> Guardar Cambios</button>
                        <a href="{{ route('admin.purchaseOrders.show', $purchaseOrder) }}" class="btn btn-secondary btn-lg ml-2">Cancelar</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
@stop

@section('js')
    <script>
        let itemIndex = {{ $purchaseOrder->items->count() }};

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
        });

        $(document).on('click', '.remove-item', function() {
            $(this).closest('tr').remove();
            calculateTotals();
            updateRemoveButtons();
        });

        $(document).on('input', '.item-qty, .item-cost', calculateTotals);

        $(document).ready(function() {
            initSelect2();
            updateRemoveButtons();
            calculateTotals();
        });
    </script>
@endsection
