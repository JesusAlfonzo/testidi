@extends('adminlte::page')

@section('title', 'Convertir RFQ a Orden de Compra')

@section('plugins.Select2', true)
@section('plugins.Sweetalert2', true)

@section('content')
    @include('admin.partials.session-messages')

    @php
        $categories = \App\Models\Category::orderBy('name')->get();
        $units = \App\Models\Unit::orderBy('name')->get();
        $locations = \App\Models\Location::orderBy('name')->get();
        $brands = \App\Models\Brand::orderBy('name')->get();
    @endphp

    <form action="{{ route('admin.rfq.store-po', $rfq) }}" method="POST" id="orderForm">
        @csrf

        <!-- Información de la RFQ -->
        <div class="row">
            <div class="col-12">
                <div class="card bg-light mb-3">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-info-circle"></i> Información de la RFQ</h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3"><strong>Código:</strong> {{ $rfq->code }}</div>
                            <div class="col-md-3"><strong>Título:</strong> {{ $rfq->title }}</div>
                            <div class="col-md-3"><strong>Estado:</strong> {!! $rfq->status_badge !!}</div>
                            <div class="col-md-3"><strong>Fecha Requerida:</strong> {{ $rfq->date_required?->format('d/m/Y') ?? 'N/A' }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sección: Información General de la OC -->
        <div class="row">
            <div class="col-12">
                <div class="card" style="border-left: 4px solid #6c757d;">
                    <div class="card-header" style="background: linear-gradient(135deg, #6c757d 0%, #8a939d 100%);">
                        <h3 class="card-title text-white">
                            <i class="fas fa-info-circle"></i> Información General de la OC
                        </h3>
                    </div>
                    <div class="card-body">
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
                            <div class="col-12 col-md-4">
                                <div class="form-group mb-2">
                                    <label for="supplier_id" class="mb-1">Proveedor (*)</label>
                                    <select name="supplier_id" id="supplier_id" class="form-control form-control-sm select2-ajax" data-placeholder="Buscar proveedor..." data-url="{{ route('admin.purchaseOrders.searchSuppliers') }}" required>
                                        <option value="">Seleccione un proveedor...</option>
                                        @foreach($suppliers as $supplier)
                                            <option value="{{ $supplier->id }}" {{ old('supplier_id') == $supplier->id ? 'selected' : '' }}>{{ $supplier->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-12 col-md-2">
                                <div class="form-group mb-2">
                                    <label for="date_issued" class="mb-1">Fecha Emisión (*)</label>
                                    <input type="date" name="date_issued" class="form-control form-control-sm" value="{{ old('date_issued', date('Y-m-d')) }}" required>
                                </div>
                            </div>
                            <div class="col-12 col-md-3">
                                <div class="form-group mb-2">
                                    <label for="delivery_date" class="mb-1">Fecha Entrega</label>
                                    <input type="date" name="delivery_date" class="form-control form-control-sm" value="{{ old('delivery_date') }}">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12 col-md-4">
                                <div class="form-group mb-2">
                                    <label for="delivery_address" class="mb-1">Dirección Entrega</label>
                                    <input type="text" name="delivery_address" class="form-control form-control-sm" value="{{ old('delivery_address') }}" placeholder="Dirección donde recibir la mercancía">
                                </div>
                            </div>
                            <div class="col-12 col-md-2">
                                <div class="form-group mb-2">
                                    <label for="currency" class="mb-1">Moneda (*)</label>
                                    <select name="currency" id="currency" class="form-control form-control-sm" required>
                                        <option value="USD" {{ old('currency', 'USD') == 'USD' ? 'selected' : '' }}>💵 USD - Dólar</option>
                                        <option value="EUR" {{ old('currency') == 'EUR' ? 'selected' : '' }}>💶 EUR - Euro</option>
                                        <option value="Bs" {{ old('currency') == 'Bs' ? 'selected' : '' }}>🇻🇪 Bs - Bolívar</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-12 col-md-2">
                                <div class="form-group mb-2">
                                    <label for="exchange_rate" class="mb-1">Tasa Cambio (*)</label>
                                    <input type="number" step="0.0001" name="exchange_rate" id="exchangeRate" class="form-control form-control-sm" value="{{ old('exchange_rate', 1) }}" required>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sección: Productos de la RFQ -->
        <div class="row">
            <div class="col-12">
                <div class="card" style="border-left: 4px solid #28a745;">
                    <div class="card-header" style="background: linear-gradient(135deg, #28a745 0%, #34d058 100%);">
                        <h3 class="card-title text-white">
                            <i class="fas fa-boxes"></i> Productos de la RFQ
                        </h3>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-sm table-hover mb-0" id="itemsTable">
                                <thead>
                                    <tr>
                                        <th style="width: 35%">Producto</th>
                                        <th style="width: 15%">Cantidad</th>
                                        <th style="width: 18%">Costo Unit. (*)</th>
                                        <th style="width: 17%">Total</th>
                                        <th style="width: 15%">Equivalente Bs</th>
                                    </tr>
                                </thead>
                                <tbody id="itemsBody">
                                    @foreach($rfq->items as $index => $item)
                                        <tr>
                                            <td>
                                                <input type="hidden" name="items[{{ $index }}][product_id]" value="{{ $item->product_id }}">
                                                <strong>{{ $item->product->name ?? 'N/A' }}</strong><br>
                                                <small class="text-muted">{{ $item->product->code ?? 'S/C' }}</small>
                                            </td>
                                            <td>
                                                <input type="hidden" name="items[{{ $index }}][quantity]" value="{{ $item->quantity }}">
                                                {{ $item->quantity }} {{ $item->product->unit->abbreviation ?? 'und' }}
                                            </td>
                                            <td>
                                                <input type="number" step="0.01" name="items[{{ $index }}][unit_cost]" class="form-control form-control-sm item-cost" min="0" value="{{ old("items.$index.unit_cost", 0) }}" required>
                                            </td>
                                            <td class="text-right">
                                                <span class="item-total font-weight-bold">0.00</span>
                                            </td>
                                            <td class="text-right">
                                                <span class="item-bs">0.00</span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot class="bg-success-light">
                                    <tr>
                                        <th colspan="3" class="text-right">TOTAL GENERAL:</th>
                                        <th class="text-right"><span id="grandTotal" class="h5 text-success">$0.00</span></th>
                                        <th class="text-right"><span id="grandTotalBs" class="h5 text-success">Bs 0.00</span></th>
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
                    <div class="card-body d-flex justify-content-end">
                        <a href="{{ route('admin.rfq.show', $rfq) }}" class="btn btn-secondary btn-lg mr-2">
                            <i class="fas fa-times"></i> Cancelar
                        </a>
                        <button type="button" class="btn btn-success btn-lg" id="saveOrderBtn">
                            <i class="fas fa-shopping-cart"></i> Crear Orden de Compra
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
@endsection

@section('js')
<script>
$(function() {
    function calculateTotals() {
        let grandTotal = 0;
        let currency = $('#currency').val();
        let exchangeRate = parseFloat($('#exchangeRate').val()) || 1;

        $('.item-cost').each(function() {
            let row = $(this).closest('tr');
            let qty = parseFloat(row.find('input[name$="[quantity]"]').val()) || 0;
            let cost = parseFloat($(this).val()) || 0;
            let total = qty * cost;
            grandTotal += total;

            row.find('.item-total').text(currency + ' ' + total.toFixed(2));
            row.find('.item-bs').text('Bs ' + (total * exchangeRate).toFixed(2));
        });

        $('#grandTotal').text(currency + ' ' + grandTotal.toFixed(2));
        $('#grandTotalBs').text('Bs ' + (grandTotal * exchangeRate).toFixed(2));
    }

    $(document).on('input', '.item-cost, #exchangeRate', calculateTotals);
    $('#currency').change(calculateTotals);
    calculateTotals();

    $('#saveOrderBtn').on('click', function(e) {
        e.preventDefault();
        if (confirm('¿Crear Orden de Compra basada en RFQ {{ $rfq->code }}?')) {
            $('#orderForm')[0].submit();
        }
    });
});
</script>
@endsection
