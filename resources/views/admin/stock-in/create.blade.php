@extends('adminlte::page')

@section('title', 'Nueva Entrada de Stock')

@section('content_header')
    <h1>Registrar Nueva Entrada de Stock</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-md-12">
            @include('admin.partials.session-messages')
            
            <div class="card card-success">
                <div class="card-header">
                    <h3 class="card-title">Datos de la Transacción</h3>
                </div>

                <form action="{{ route('admin.stock-in.store') }}" method="POST" id="stockInForm">
                    @csrf
                    <div class="card-body">

                        @if($order)
                            <div class="alert alert-info">
                                <i class="fas fa-link"></i> Recibiendo desde Orden de Compra <strong>{{ $order->code }}</strong>
                                <input type="hidden" name="purchase_order_id" value="{{ $order->id }}">
                                <input type="hidden" name="supplier_id" value="{{ $order->supplier_id }}">
                            </div>
                        @endif

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="supplier_id_display">Proveedor</label>
                                    @if($order)
                                        <input type="text" class="form-control" value="{{ $order->supplier->name }}" readonly>
                                    @else
                                        <select name="supplier_id" id="supplier_id" class="form-control @error('supplier_id') is-invalid @enderror">
                                            <option value="">Seleccione un proveedor...</option>
                                            @foreach($suppliers as $id => $name)
                                                <option value="{{ $id }}" {{ old('supplier_id') == $id ? 'selected' : '' }}>{{ $name }}</option>
                                            @endforeach
                                        </select>
                                    @endif
                                    @error('supplier_id')<span class="invalid-feedback">{{ $message }}</span>@enderror
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="entry_date">Fecha de Ingreso (*)</label>
                                    <input type="date" name="entry_date" class="form-control @error('entry_date') is-invalid @enderror" value="{{ old('entry_date', \Carbon\Carbon::now()->toDateString()) }}" required>
                                    @error('entry_date')<span class="invalid-feedback">{{ $message }}</span>@enderror
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="reason">Razón del Ingreso</label>
                                    <input type="text" name="reason" class="form-control @error('reason') is-invalid @enderror" value="{{ old('reason') }}" placeholder="Ej: Compra, Donación, Ajuste">
                                    @error('reason')<span class="invalid-feedback">{{ $message }}</span>@enderror
                                </div>
                            </div>
                        </div>

                        <hr>
                        <h4><i class="fas fa-file-invoice"></i> Documentación</h4>

                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="document_type">Tipo de Documento</label>
                                    <input type="text" name="document_type" class="form-control @error('document_type') is-invalid @enderror" value="{{ old('document_type') }}" placeholder="Ej: Factura, Guía">
                                    @error('document_type')<span class="invalid-feedback">{{ $message }}</span>@enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="document_number">Número de Documento</label>
                                    <input type="text" name="document_number" class="form-control @error('document_number') is-invalid @enderror" value="{{ old('document_number') }}" placeholder="Ej: F-001-12345">
                                    @error('document_number')<span class="invalid-feedback">{{ $message }}</span>@enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="invoice_number">Número de Factura</label>
                                    <input type="text" name="invoice_number" class="form-control @error('invoice_number') is-invalid @enderror" value="{{ old('invoice_number') }}" placeholder="Número de factura">
                                    @error('invoice_number')<span class="invalid-feedback">{{ $message }}</span>@enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="delivery_note_number">Número Nota de Entrega</label>
                                    <input type="text" name="delivery_note_number" class="form-control @error('delivery_note_number') is-invalid @enderror" value="{{ old('delivery_note_number') }}" placeholder="Guía de remisión">
                                    @error('delivery_note_number')<span class="invalid-feedback">{{ $message }}</span>@enderror
                                </div>
                            </div>
                        </div>

                        <hr>
                        <div class="d-flex justify-content-between align-items-center">
                            <h4><i class="fas fa-boxes"></i> Productos</h4>
                            <button type="button" class="btn btn-primary" onclick="addItem()">
                                <i class="fas fa-plus"></i> Agregar Producto
                            </button>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-bordered table-striped" id="itemsTable">
                                <thead class="thead-light">
                                    <tr>
                                        <th width="25%">Producto</th>
                                        <th width="10%">Cantidad</th>
                                        <th width="12%">Costo Unit.</th>
                                        <th width="12%">Nro. Lote</th>
                                        <th width="12%">Fecha Venc.</th>
                                        <th width="12%">Nro. Serie</th>
                                        <th width="10%">Ubicación</th>
                                        <th width="7%"></th>
                                    </tr>
                                </thead>
                                <tbody id="itemsBody">
                                    @php $itemIndex = 0; @endphp
                                    @if(old('items'))
                                        @foreach(old('items') as $index => $item)
                                            <tr data-index="{{ $index }}">
                                                <td>
                                                    <select name="items[{{ $index }}][product_id]" class="form-control select2-product @error("items.$index.product_id") is-invalid @enderror" required>
                                                        <option value="">Seleccione...</option>
                                                        @foreach($products as $id => $name)
                                                            <option value="{{ $id }}" {{ ($item['product_id'] ?? '') == $id ? 'selected' : '' }}>{{ $name }}</option>
                                                        @endforeach
                                                    </select>
                                                </td>
                                                <td>
                                                    <input type="number" name="items[{{ $index }}][quantity]" class="form-control @error("items.$index.quantity") is-invalid @enderror" value="{{ $item['quantity'] ?? 1 }}" min="1" required>
                                                </td>
                                                <td>
                                                    <input type="number" step="0.01" name="items[{{ $index }}][unit_cost]" class="form-control @error("items.$index.unit_cost") is-invalid @enderror" value="{{ $item['unit_cost'] ?? 0 }}" min="0" required>
                                                </td>
                                                <td>
                                                    <input type="text" name="items[{{ $index }}][batch_number]" class="form-control" value="{{ $item['batch_number'] ?? '' }}" placeholder="Lote">
                                                </td>
                                                <td>
                                                    <input type="date" name="items[{{ $index }}][expiry_date]" class="form-control" value="{{ $item['expiry_date'] ?? '' }}">
                                                </td>
                                                <td>
                                                    <input type="text" name="items[{{ $index }}][serial_number]" class="form-control" value="{{ $item['serial_number'] ?? '' }}" placeholder="Serie">
                                                </td>
                                                <td>
                                                    <input type="text" name="items[{{ $index }}][warehouse_location]" class="form-control" value="{{ $item['warehouse_location'] ?? '' }}" placeholder="Ubicación">
                                                </td>
                                                <td>
                                                    <button type="button" class="btn btn-danger btn-sm" onclick="removeItem(this)">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                            @php $itemIndex = $index + 1; @endphp
                                        @endforeach
                                    @elseif($order)
                                        @foreach($order->items as $index => $orderItem)
                                            <tr data-index="{{ $index }}">
                                                <td>
                                                    <select name="items[{{ $index }}][product_id]" class="form-control select2-product" required>
                                                        <option value="{{ $orderItem->product_id }}" selected>{{ $orderItem->product->name ?? 'Producto' }}</option>
                                                    </select>
                                                </td>
                                                <td>
                                                    <input type="number" name="items[{{ $index }}][quantity]" class="form-control" value="{{ max(1, $orderItem->quantity - $orderItem->quantity_received) }}" min="1" required>
                                                    <small class="text-muted">Pend: {{ $orderItem->quantity - $orderItem->quantity_received }}</small>
                                                </td>
                                                <td>
                                                    <input type="number" step="0.01" name="items[{{ $index }}][unit_cost]" class="form-control" value="{{ $orderItem->unit_cost }}" min="0" required>
                                                </td>
                                                <td>
                                                    <input type="text" name="items[{{ $index }}][batch_number]" class="form-control" placeholder="Lote">
                                                </td>
                                                <td>
                                                    <input type="date" name="items[{{ $index }}][expiry_date]" class="form-control">
                                                </td>
                                                <td>
                                                    <input type="text" name="items[{{ $index }}][serial_number]" class="form-control" placeholder="Serie">
                                                </td>
                                                <td>
                                                    <input type="text" name="items[{{ $index }}][warehouse_location]" class="form-control" placeholder="Ubicación">
                                                </td>
                                                <td>
                                                    <button type="button" class="btn btn-danger btn-sm" onclick="removeItem(this)">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                            @php $itemIndex = $index + 1; @endphp
                                        @endforeach
                                    @else
                                        <tr data-index="0">
                                            <td>
                                                <select name="items[0][product_id]" class="form-control select2-product" required>
                                                    <option value="">Seleccione...</option>
                                                    @foreach($products as $id => $name)
                                                        <option value="{{ $id }}">{{ $name }}</option>
                                                    @endforeach
                                                </select>
                                            </td>
                                            <td>
                                                <input type="number" name="items[0][quantity]" class="form-control" value="1" min="1" required>
                                            </td>
                                            <td>
                                                <input type="number" step="0.01" name="items[0][unit_cost]" class="form-control" value="0" min="0" required>
                                            </td>
                                            <td>
                                                <input type="text" name="items[0][batch_number]" class="form-control" placeholder="Lote">
                                            </td>
                                            <td>
                                                <input type="date" name="items[0][expiry_date]" class="form-control">
                                            </td>
                                            <td>
                                                <input type="text" name="items[0][serial_number]" class="form-control" placeholder="Serie">
                                            </td>
                                            <td>
                                                <input type="text" name="items[0][warehouse_location]" class="form-control" placeholder="Ubicación">
                                            </td>
                                            <td>
                                                <button type="button" class="btn btn-danger btn-sm" onclick="removeItem(this)">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>

                        @error('items')
                            <div class="text-danger mt-2">{{ $message }}</div>
                        @enderror
                        @error('items.*')
                            <div class="text-danger mt-2">{{ $message }}</div>
                        @enderror

                    </div>

                    <div class="card-footer">
                        <button type="submit" class="btn btn-success"><i class="fas fa-arrow-alt-circle-up"></i> Registrar Entrada</button>
                        <a href="{{ route('admin.stock-in.index') }}" class="btn btn-default float-right">Cancelar</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
@stop

@section('js')
<script>
    let itemIndex = {{ $itemIndex ?? 0 }};

    function addItem() {
        const tbody = document.getElementById('itemsBody');
        const row = document.createElement('tr');
        row.setAttribute('data-index', itemIndex);
        
        const productOptions = `@foreach($products as $id => $name)<option value="{{ $id }}">{{ $name }}</option>@endforeach`;
        
        row.innerHTML = `
            <td>
                <select name="items[${itemIndex}][product_id]" class="form-control select2-product" required>
                    <option value="">Seleccione...</option>
                    ${productOptions}
                </select>
            </td>
            <td>
                <input type="number" name="items[${itemIndex}][quantity]" class="form-control" value="1" min="1" required>
            </td>
            <td>
                <input type="number" step="0.01" name="items[${itemIndex}][unit_cost]" class="form-control" value="0" min="0" required>
            </td>
            <td>
                <input type="text" name="items[${itemIndex}][batch_number]" class="form-control" placeholder="Lote">
            </td>
            <td>
                <input type="date" name="items[${itemIndex}][expiry_date]" class="form-control">
            </td>
            <td>
                <input type="text" name="items[${itemIndex}][serial_number]" class="form-control" placeholder="Serie">
            </td>
            <td>
                <input type="text" name="items[${itemIndex}][warehouse_location]" class="form-control" placeholder="Ubicación">
            </td>
            <td>
                <button type="button" class="btn btn-danger btn-sm" onclick="removeItem(this)">
                    <i class="fas fa-trash"></i>
                </button>
            </td>
        `;
        
        tbody.appendChild(row);
        
        if (typeof $ !== 'undefined' && $.fn.select2) {
            $(row).find('.select2-product').select2();
        }
        
        itemIndex++;
    }

    function removeItem(button) {
        const tbody = document.getElementById('itemsBody');
        if (tbody.rows.length > 1) {
            $(button).closest('tr').remove();
        } else {
            alert('Debe mantener al menos un producto.');
        }
    }

    $(document).ready(function() {
        $('.select2-product').select2({
            placeholder: 'Seleccione un producto',
            allowClear: true
        });

        $('#stockInForm').on('submit', function(e) {
            const itemCount = $('#itemsBody tr').length;
            if (itemCount === 0) {
                e.preventDefault();
                alert('Debe agregar al menos un producto.');
                return false;
            }
        });
    });
</script>
@stop
