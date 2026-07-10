@extends('adminlte::page')

@section('title', 'Registrar Entrada de Stock')

@section('plugins.Select2', true)

@section('css')
    <style>
        /* ── Scroll Limpio para Select2 Dropdown (Receta RFQ) ────────────────── */
        .select2-results__options {
            max-height: 250px !important;
            overflow-y: auto !important;
        }
    </style>
@stop

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="text-dark font-weight-bold"><i class="fas fa-truck-loading text-success mr-2"></i> Registrar Entrada de Stock</h1>
            <p class="text-muted mb-0">Recepción de mercancía, asignación de lotes y control de trazabilidad.</p>
        </div>
        <a href="{{ route('admin.stock-in.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left mr-1"></i> Volver al listado
        </a>
    </div>
@stop

@section('content')
    <div class="container-fluid">
        @include('admin.partials.session-messages')
        
        <form action="{{ route('admin.stock-in.store') }}" method="POST" id="stockInForm">
            @csrf
            
            @if($order)
                <input type="hidden" name="purchase_order_id" value="{{ $order->id }}">
                <input type="hidden" name="supplier_id" value="{{ $order->supplier_id }}">
            @endif

            <div class="row">
                <!-- COLUMNA PRINCIPAL (IZQUIERDA - 70%) -->
                <div class="col-lg-8 col-md-12">
                    {{-- Información de la Orden de Compra si existe --}}
                    @if($order)
                        <div class="card card-outline card-info shadow-sm mb-4">
                            <div class="card-header bg-white py-3">
                                <h3 class="card-title text-info font-weight-bold mb-0">
                                    <i class="fas fa-file-contract mr-1"></i> Orden de Compra Referenciada
                                </h3>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-4">
                                        <label class="font-weight-bold text-muted mb-0">Código OC:</label>
                                        <p class="mb-0 text-dark font-weight-bold">{{ $order->code }}</p>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="font-weight-bold text-muted mb-0">Proveedor:</label>
                                        <p class="mb-0 text-dark">{{ $order->supplier->name }}</p>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="font-weight-bold text-muted mb-0">Moneda:</label>
                                        <p class="mb-0 text-dark"><span class="badge badge-info">{{ $order->currency }}</span></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    {{-- Card de Ítems / Productos --}}
                    {{-- Filtro de categorías (solo en modo sin OC precargada) --}}
                    @unless($order)
                        @include('admin.partials.category-filter')
                    @endunless

                    <div class="card card-outline card-success shadow-sm mb-4">
                        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                            <h3 class="card-title text-success font-weight-bold mb-0">
                                <i class="fas fa-boxes mr-1"></i> Ítems a Recibir
                            </h3>
                            @unless($order)
                                <button type="button" class="btn btn-sm btn-success shadow-sm" onclick="addItem()">
                                    <i class="fas fa-plus mr-1"></i> Agregar Ítem
                                </button>
                            @endunless
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover table-striped mb-0" id="itemsTable">
                                    <thead class="thead-light">
                                        <tr>
                                            <th>Producto</th>
                                            <th style="width: 100px;">Cantidad</th>
                                            <th style="width: 110px;">Costo Unit.</th>
                                            <th style="width: 120px;">Lote (*)</th>
                                            <th style="width: 140px;">Vencimiento (*)</th>
                                            <th>Ubicación (*)</th>
                                            @unless($order)
                                                <th style="width: 130px;">Estado</th>
                                            @endunless
                                            @if(!$order || $orderItem)
                                                <th style="width: 50px;"></th>
                                            @elseif(!empty($selectedItemIds))
                                                <th style="width: 50px;"></th>
                                            @else
                                                <th style="width: 50px;"></th>
                                            @endif
                                        </tr>
                                    </thead>
                                    <tbody id="itemsBody">
                                        @php $itemIndex = 0; @endphp
                                        @if(old('items'))
                                            @foreach(old('items') as $index => $item)
                                                @php 
                                                    $prodModel = \App\Models\Product::find($item['product_id'] ?? null);
                                                    $requiresSerial = $prodModel ? $prodModel->requires_serial : false;
                                                    $isPerishable = $prodModel ? $prodModel->is_perishable : false;
                                                @endphp
                                                <tr data-index="{{ $index }}" data-requires-serial="{{ $requiresSerial ? 'true' : 'false' }}" data-is-perishable="{{ $isPerishable ? 'true' : 'false' }}">
                                                    <td>
                                                        <select name="items[{{ $index }}][product_id]" class="form-control form-control-sm item-selector product-select-ajax @error("items.$index.product_id") is-invalid @enderror" required>
                                                            @if(!empty($item['product_id']))
                                                                <option value="{{ $item['product_id'] }}" selected>Producto Seleccionado</option>
                                                            @endif
                                                        </select>
                                                        @error("items.$index.product_id")<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                                                    </td>
                                                    <td>
                                                        <input type="number" name="items[{{ $index }}][quantity]" class="form-control form-control-sm item-qty @error("items.$index.quantity") is-invalid @enderror" value="{{ $item['quantity'] ?? 1 }}" min="1" required onchange="onQtyChange(this)" onkeyup="onQtyChange(this)">
                                                        @error("items.$index.quantity")<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                                                    </td>
                                                    <td>
                                                        <input type="number" step="0.01" name="items[{{ $index }}][unit_cost]" class="form-control form-control-sm @error("items.$index.unit_cost") is-invalid @enderror" value="{{ $item['unit_cost'] ?? 0 }}" min="0" required>
                                                        @error("items.$index.unit_cost")<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                                                    </td>
                                                    <td>
                                                        <input type="text" name="items[{{ $index }}][batch_number]" class="form-control form-control-sm @error("items.$index.batch_number") is-invalid @enderror" value="{{ $item['batch_number'] ?? '' }}" placeholder="Lote" required>
                                                        @error("items.$index.batch_number")<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                                                    </td>
                                                    <td>
                                                        <input type="date" name="items[{{ $index }}][expiration_date]" class="form-control form-control-sm @error("items.$index.expiration_date") is-invalid @enderror" value="{{ $item['expiration_date'] ?? '' }}" required>
                                                        @error("items.$index.expiration_date")<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                                                    </td>
                                                    <td>
                                                        <select name="items[{{ $index }}][warehouse_location]" class="form-control form-control-sm @error("items.$index.warehouse_location") is-invalid @enderror" required>
                                                            <option value="">Seleccione...</option>
                                                            @foreach($locations as $id => $name)
                                                                <option value="{{ $name }}" {{ ($item['warehouse_location'] ?? '') == $name ? 'selected' : '' }}>{{ $name }}</option>
                                                            @endforeach
                                                        </select>
                                                        @error("items.$index.warehouse_location")<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                                                    </td>
                                                    @unless($order)
                                                        <td>
                                                            <select name="items[{{ $index }}][status]" class="form-control form-control-sm status-select" onchange="toggleRejectionReason(this)">
                                                                <option value="received" {{ ($item['status'] ?? '') == 'received' ? 'selected' : '' }}>Recibido</option>
                                                                <option value="rejected" {{ ($item['status'] ?? '') == 'rejected' ? 'selected' : '' }}>Rechazado</option>
                                                            </select>
                                                            <input type="text" name="items[{{ $index }}][rejection_reason]" class="form-control form-control-sm rejection-reason mt-1" placeholder="Razón" value="{{ $item['rejection_reason'] ?? '' }}" style="display: {{ ($item['status'] ?? '') == 'rejected' ? 'block' : 'none' }};">
                                                        </td>
                                                    @endunless
                                                    <td class="text-center align-middle">
                                                        <button type="button" class="btn btn-outline-danger btn-xs" onclick="removeItem(this)">
                                                            <i class="fas fa-trash-alt"></i>
                                                        </button>
                                                    </td>
                                                </tr>
                                                @php $itemIndex = $index + 1; @endphp
                                            @endforeach
                                        @elseif($order)
                                            @if($orderItem)
                                                @php $index = 0; $pending = max(0, $orderItem->quantity - $orderItem->quantity_received); @endphp
                                                <tr data-index="{{ $index }}" data-requires-serial="{{ ($orderItem->product?->requires_serial ?? false) ? 'true' : 'false' }}" data-is-perishable="{{ ($orderItem->product?->is_perishable ?? false) ? 'true' : 'false' }}">
                                                    <td>
                                                        <input type="hidden" name="items[{{ $index }}][product_id]" value="{{ $orderItem->product_id }}">
                                                        <input type="hidden" name="items[{{ $index }}][purchase_order_item_id]" value="{{ $orderItem->id }}">
                                                         <span class="font-weight-bold">{{ $orderItem->product?->name ?? $orderItem->product_name }}</span>
                                                         <br><small class="text-secondary small">Código/SKU: <code>{{ $orderItem->product?->sku ?? $orderItem->product?->code ?? $orderItem->product_code }}</code></small>
                                                        @if($orderItem->product?->requires_serial)
                                                            <br><span class="badge badge-warning"><i class="fas fa-barcode"></i> Serializado</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <input type="number" name="items[{{ $index }}][quantity]" class="form-control form-control-sm item-qty font-weight-bold" value="{{ $pending }}" min="1" max="{{ $pending }}" required onchange="onQtyChange(this)" onkeyup="onQtyChange(this)">
                                                        <small class="text-muted d-block mt-1">Pendiente: {{ $pending }}</small>
                                                    </td>
                                                    <td>
                                                        <input type="number" step="0.01" name="items[{{ $index }}][unit_cost]" class="form-control form-control-sm text-right" value="{{ $orderItem->unit_cost }}" min="0" required>
                                                    </td>
                                                    <td>
                                                        <input type="text" name="items[{{ $index }}][batch_number]" class="form-control form-control-sm" placeholder="Lote" required>
                                                    </td>
                                                    <td>
                                                        <input type="date" name="items[{{ $index }}][expiration_date]" class="form-control form-control-sm" required>
                                                    </td>
                                                    <td>
                                                        <select name="items[{{ $index }}][warehouse_location]" class="form-control form-control-sm" required>
                                                            <option value="">Seleccione...</option>
                                                            @foreach($locations as $id => $name)
                                                                <option value="{{ $name }}">{{ $name }}</option>
                                                            @endforeach
                                                        </select>
                                                    </td>
                                                    <td class="text-center align-middle">
                                                        <button type="button" class="btn btn-outline-danger btn-xs" onclick="removeItem(this)">
                                                            <i class="fas fa-trash-alt"></i>
                                                        </button>
                                                    </td>
                                                </tr>
                                                @php $itemIndex = 1; @endphp
                                            @elseif(!empty($selectedItemIds))
                                                @foreach($order->items->whereIn('id', $selectedItemIds) as $index => $orderItem)
                                                    @php $pending = max(0, $orderItem->quantity - $orderItem->quantity_received - $orderItem->quantity_replaced); @endphp
                                                    @if($pending > 0)
                                                        <tr data-index="{{ $index }}" data-requires-serial="{{ ($orderItem->product?->requires_serial ?? false) ? 'true' : 'false' }}" >
                                                            <td>
                                                                <input type="hidden" name="items[{{ $index }}][product_id]" value="{{ $orderItem->product_id }}">
                                                                <input type="hidden" name="items[{{ $index }}][purchase_order_item_id]" value="{{ $orderItem->id }}">
                                                                <span class="font-weight-bold">{{ $orderItem->product?->name ?? $orderItem->product_name }}</span>
                                                                <br><small class="text-secondary small">Código/SKU: <code>{{ $orderItem->product?->sku ?? $orderItem->product?->code ?? $orderItem->product_code }}</code></small>
                                                                @if($orderItem->product?->requires_serial)
                                                                    <br><span class="badge badge-warning"><i class="fas fa-barcode"></i> Serializado</span>
                                                                @endif
                                                            </td>
                                                            <td>
                                                                <input type="number" name="items[{{ $index }}][quantity]" class="form-control form-control-sm item-qty font-weight-bold" value="{{ $pending }}" min="1" max="{{ $pending }}" required onchange="onQtyChange(this)" onkeyup="onQtyChange(this)">
                                                                <small class="text-muted d-block mt-1">Pendiente: {{ $pending }}</small>
                                                            </td>
                                                            <td>
                                                                <input type="number" step="0.01" name="items[{{ $index }}][unit_cost]" class="form-control form-control-sm text-right" value="{{ $orderItem->unit_cost }}" min="0" required>
                                                            </td>
                                                            <td>
                                                                <input type="text" name="items[{{ $index }}][batch_number]" class="form-control form-control-sm" placeholder="Lote" required>
                                                            </td>
                                                            <td>
                                                                <input type="date" name="items[{{ $index }}][expiration_date]" class="form-control form-control-sm" required>
                                                            </td>
                                                            <td>
                                                                <select name="items[{{ $index }}][warehouse_location]" class="form-control form-control-sm" required>
                                                                    <option value="">Seleccione...</option>
                                                                    @foreach($locations as $id => $name)
                                                                        <option value="{{ $name }}">{{ $name }}</option>
                                                                    @endforeach
                                                                </select>
                                                            </td>
                                                            <td class="text-center align-middle">
                                                                <button type="button" class="btn btn-outline-danger btn-xs" onclick="removeItem(this)">
                                                                    <i class="fas fa-trash-alt"></i>
                                                                </button>
                                                            </td>
                                                        </tr>
                                                        @php $itemIndex = $index + 1; @endphp
                                                    @endif
                                                @endforeach
                                            @else
                                                @foreach($order->items as $index => $orderItem)
                                                    @php $pending = max(0, $orderItem->quantity - $orderItem->quantity_received - $orderItem->quantity_replaced); @endphp
                                                    @if($pending > 0)
                                                        <tr data-index="{{ $index }}" data-requires-serial="{{ ($orderItem->product?->requires_serial ?? false) ? 'true' : 'false' }}" >
                                                            <td>
                                                                <input type="hidden" name="items[{{ $index }}][product_id]" value="{{ $orderItem->product_id }}">
                                                                <input type="hidden" name="items[{{ $index }}][purchase_order_item_id]" value="{{ $orderItem->id }}">
                                                                <span class="font-weight-bold">{{ $orderItem->product?->name ?? $orderItem->product_name }}</span>
                                                                <br><small class="text-secondary small">Código/SKU: <code>{{ $orderItem->product?->sku ?? $orderItem->product?->code ?? $orderItem->product_code }}</code></small>
                                                                @if($orderItem->product?->requires_serial)
                                                                    <br><span class="badge badge-warning"><i class="fas fa-barcode"></i> Serializado</span>
                                                                @endif
                                                            </td>
                                                            <td>
                                                                <input type="number" name="items[{{ $index }}][quantity]" class="form-control form-control-sm item-qty font-weight-bold" value="{{ $pending }}" min="1" max="{{ $pending }}" required onchange="onQtyChange(this)" onkeyup="onQtyChange(this)">
                                                                <small class="text-muted d-block mt-1">Pendiente: {{ $pending }}</small>
                                                            </td>
                                                            <td>
                                                                <input type="number" step="0.01" name="items[{{ $index }}][unit_cost]" class="form-control form-control-sm text-right" value="{{ $orderItem->unit_cost }}" min="0" required>
                                                            </td>
                                                            <td>
                                                                <input type="text" name="items[{{ $index }}][batch_number]" class="form-control form-control-sm" placeholder="Lote" required>
                                                            </td>
                                                            <td>
                                                                <input type="date" name="items[{{ $index }}][expiration_date]" class="form-control form-control-sm" required>
                                                            </td>
                                                            <td>
                                                                <select name="items[{{ $index }}][warehouse_location]" class="form-control form-control-sm" required>
                                                                    <option value="">Seleccione...</option>
                                                                    @foreach($locations as $id => $name)
                                                                        <option value="{{ $name }}">{{ $name }}</option>
                                                                    @endforeach
                                                                </select>
                                                            </td>
                                                            <td class="text-center align-middle">
                                                                <button type="button" class="btn btn-outline-danger btn-xs" onclick="removeItem(this)">
                                                                    <i class="fas fa-trash-alt"></i>
                                                                </button>
                                                            </td>
                                                        </tr>
                                                    @endif
                                                    @php $itemIndex = $index + 1; @endphp
                                                @endforeach
                                            @endif
                                        @else
                                            <tr data-index="0" data-requires-serial="false">
                                                <td>
                                                     <select name="items[0][product_id]" class="form-control form-control-sm select2-product @error('items.0.product_id') is-invalid @enderror" required onchange="onProductChange(this)">
                                                            <option value="">Seleccione...</option>
                                                            @foreach($products as $prod)
                                                                <option value="{{ $prod->id }}" data-requires-serial="{{ $prod->requires_serial ? 'true' : 'false' }}" data-is-perishable="{{ $prod->is_perishable ? 'true' : 'false' }}" data-category-id="{{ $prod->category_id }}">{{ $prod->name }}</option>
                                                            @endforeach
                                                        </select>
                                                </td>
                                                <td>
                                                    <input type="number" name="items[0][quantity]" class="form-control form-control-sm item-qty" value="1" min="1" required onchange="onQtyChange(this)" onkeyup="onQtyChange(this)">
                                                </td>
                                                <td>
                                                    <input type="number" step="0.01" name="items[0][unit_cost]" class="form-control form-control-sm text-right" value="0" min="0" required>
                                                </td>
                                                <td>
                                                    <input type="text" name="items[0][batch_number]" class="form-control form-control-sm" placeholder="Lote" required>
                                                </td>
                                                <td>
                                                    <input type="date" name="items[0][expiration_date]" class="form-control form-control-sm" required>
                                                </td>
                                                <td>
                                                    <select name="items[0][warehouse_location]" class="form-control form-control-sm" required>
                                                        <option value="">Seleccione...</option>
                                                        @foreach($locations as $id => $name)
                                                            <option value="{{ $name }}">{{ $name }}</option>
                                                        @endforeach
                                                    </select>
                                                </td>
                                                <td>
                                                    <select name="items[0][status]" class="form-control form-control-sm status-select" onchange="toggleRejectionReason(this)">
                                                        <option value="received">Recibido</option>
                                                        <option value="rejected">Rechazado</option>
                                                    </select>
                                                    <input type="text" name="items[0][rejection_reason]" class="form-control form-control-sm rejection-reason mt-1" placeholder="Razón rechazo" style="display:none;">
                                                </td>
                                                <td class="text-center align-middle">
                                                    <button type="button" class="btn btn-outline-danger btn-xs" onclick="removeItem(this)">
                                                        <i class="fas fa-trash-alt"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        @endif
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- COLUMNA LATERAL (DERECHA - 30%) -->
                <div class="col-lg-4 col-md-12">
                    {{-- Card de Datos de la Entrada --}}
                    <div class="card card-outline card-primary shadow-sm mb-4">
                        <div class="card-header bg-white py-3">
                            <h3 class="card-title text-primary font-weight-bold mb-0">
                                <i class="fas fa-info-circle mr-1"></i> Información General
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <label class="font-weight-bold text-muted">Proveedor</label>
                                @if($order)
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"><i class="fas fa-building"></i></span>
                                        </div>
                                        <input type="text" class="form-control bg-light" value="{{ $order->supplier->name }}" readonly>
                                    </div>
                                @else
                                    <select name="supplier_id" id="supplier_id" class="form-control item-selector supplier-select-ajax @error('supplier_id') is-invalid @enderror">
                                        @if(old('supplier_id'))
                                            <option value="{{ old('supplier_id') }}" selected>Proveedor Seleccionado</option>
                                        @endif
                                    </select>
                                    @error('supplier_id')<span class="invalid-feedback">{{ $message }}</span>@enderror
                                @endif
                            </div>

                            <div class="form-group">
                                <label for="entry_date" class="font-weight-bold text-muted">Fecha de Ingreso (*)</label>
                                <input type="date" name="entry_date" class="form-control @error('entry_date') is-invalid @enderror" value="{{ old('entry_date', \Carbon\Carbon::now()->toDateString()) }}" required>
                                @error('entry_date')<span class="invalid-feedback">{{ $message }}</span>@enderror
                            </div>

                            <div class="form-group">
                                <label for="reason" class="font-weight-bold text-muted">Razón del Ingreso (*)</label>
                                <input type="text" name="reason" class="form-control @error('reason') is-invalid @enderror" value="{{ old('reason', ($order ? 'Compra según ' . $order->code : '')) }}" placeholder="Ej: Compra, Donación, Ajuste" required>
                                @error('reason')<span class="invalid-feedback">{{ $message }}</span>@enderror
                            </div>

                            <hr>

                            <div class="form-group">
                                <label for="document_type" class="font-weight-bold text-muted">Tipo de Documento (*)</label>
                                <input type="text" name="document_type" class="form-control @error('document_type') is-invalid @enderror" value="{{ old('document_type', 'Factura') }}" placeholder="Ej: Factura, Guía" required>
                                @error('document_type')<span class="invalid-feedback">{{ $message }}</span>@enderror
                            </div>

                            <div class="form-group">
                                <label for="document_number" class="font-weight-bold text-muted">Número de Documento (*)</label>
                                <input type="text" name="document_number" class="form-control @error('document_number') is-invalid @enderror" value="{{ old('document_number') }}" placeholder="Ej: F-001-12345" required>
                                @error('document_number')<span class="invalid-feedback">{{ $message }}</span>@enderror
                            </div>

                            <div class="form-group">
                                <label for="invoice_number" class="font-weight-bold text-muted">Número de Factura</label>
                                <input type="text" name="invoice_number" class="form-control @error('invoice_number') is-invalid @enderror" value="{{ old('invoice_number') }}" placeholder="Opcional">
                                @error('invoice_number')<span class="invalid-feedback">{{ $message }}</span>@enderror
                            </div>

                            <div class="form-group">
                                <label for="delivery_note_number" class="font-weight-bold text-muted">N° Nota de Entrega / Guía</label>
                                <input type="text" name="delivery_note_number" class="form-control @error('delivery_note_number') is-invalid @enderror" value="{{ old('delivery_note_number') }}" placeholder="Opcional">
                                @error('delivery_note_number')<span class="invalid-feedback">{{ $message }}</span>@enderror
                            </div>
                        </div>
                    </div>

                    {{-- Validador de Números de Serie --}}
                    <div id="serialValidatorPanel" class="card card-outline card-warning shadow-sm" style="display: none;">
                        <div class="card-header bg-white py-3">
                            <h3 class="card-title text-warning font-weight-bold mb-0">
                                <i class="fas fa-barcode mr-1"></i> Control de Números de Serie
                            </h3>
                        </div>
                        <div class="card-body" id="serialPanelBody">
                            <!-- Los textareas dinámicos para series se inyectarán aquí -->
                        </div>
                    </div>

                    {{-- Card de Acciones --}}
                    <div class="card shadow-sm">
                        <div class="card-body p-3">
                            <button type="submit" class="btn btn-success btn-block btn-lg shadow-sm" id="submitFormBtn">
                                <i class="fas fa-save mr-1"></i> Registrar Entrada
                            </button>
                            <a href="{{ route('admin.stock-in.index') }}" class="btn btn-block btn-outline-secondary mt-2">
                                <i class="fas fa-times mr-1"></i> Cancelar
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
@stop

@section('js')
<script>
    let itemIndex = {{ $itemIndex ?? 0 }};

    // ─── ESTADO DEL FILTRO DE CATEGORÍAS (solo aplica en modo sin OC) ──────────
    const allProducts = [
        @foreach($products as $prod)
        {
            id: {{ $prod->id }},
            text: '{{ addslashes($prod->name) }}',
            requiresSerial: {{ $prod->requires_serial ? 'true' : 'false' }},
            isPerishable: {{ $prod->is_perishable ? 'true' : 'false' }},
            categoryId: {{ $prod->category_id ?? 'null' }}
        },
        @endforeach
    ];

    let selectedCategoryId = null;

    function filteredProducts() {
        if (!selectedCategoryId) return [];
        return allProducts.filter(p => p.categoryId == selectedCategoryId);
    }

    function buildStockInOptions() {
        const products = filteredProducts();
        if (products.length === 0) {
            return '<option value="">— Sin productos en esta categoría —</option>';
        }
        let html = '<option value="">Seleccione...</option>';
        products.forEach(p => {
            html += `<option value="${p.id}" data-requires-serial="${p.requiresSerial}" data-is-perishable="${p.isPerishable}" data-category-id="${p.categoryId}">${p.text}</option>`;
        });
        return html;
    }

    function applyStockInFilter() {
        const enabled = !!selectedCategoryId;
        const hint = document.getElementById('categoryFilterHint');

        $('#itemsBody tr').each(function() {
            const select = $(this).find('.select2-product');
            if (select.length === 0) return; // fila de OC precargada (texto fijo)

            const currentVal = select.val();
            if (select.data('select2')) select.select2('destroy');
            select.html(buildStockInOptions());

            // Preservar selección aunque no esté en la categoría nueva
            if (currentVal && !filteredProducts().find(p => p.id == currentVal)) {
                const orphan = allProducts.find(p => p.id == currentVal);
                if (orphan) {
                    select.prepend(`<option value="${orphan.id}" data-requires-serial="${orphan.requiresSerial}" data-is-perishable="${orphan.isPerishable}" selected>[${orphan.text}]</option>`);
                    select.val(orphan.id);
                }
            } else {
                select.val(currentVal || '');
            }

            select.prop('disabled', !enabled).select2({
                theme: 'bootstrap4',
                placeholder: enabled ? 'Seleccione un producto' : 'Seleccione una categoría primero'
            });
        });

        if (hint) {
            hint.innerHTML = enabled
                ? `<i class="fas fa-check-circle text-success"></i> Mostrando productos de la categoría seleccionada.`
                : `<i class="fas fa-info-circle"></i> Seleccione una categoría para habilitar el selector de productos.`;
        }
    }
    // ─────────────────────────────────────────────────────────────────────────────

    function addItem() {
        // En modo sin OC, requerir categoría seleccionada
        @unless($order)
        if (!selectedCategoryId) {
            alert('Seleccione primero una categoría en el filtro para agregar productos.');
            return;
        }
        @endunless

        const tbody = document.getElementById('itemsBody');
        const row = document.createElement('tr');
        row.setAttribute('data-index', itemIndex);
        row.setAttribute('data-requires-serial', 'false');

        let locationOptions = '<option value="">Seleccione...</option>';
        @foreach($locations as $id => $name)
            locationOptions += '<option value="{{ $name }}">{{ $name }}</option>';
        @endforeach
        
        let cols = `
            <td>
                <select name="items[${itemIndex}][product_id]" class="form-control form-control-sm item-selector product-select-ajax" required>
                </select>
            </td>
            <td>
                <input type="number" name="items[${itemIndex}][quantity]" class="form-control form-control-sm item-qty" value="1" min="1" required onchange="onQtyChange(this)" onkeyup="onQtyChange(this)">
            </td>
            <td>
                <input type="number" step="0.01" name="items[${itemIndex}][unit_cost]" class="form-control form-control-sm text-right" value="0" min="0" required>
            </td>
            <td>
                <input type="text" name="items[${itemIndex}][batch_number]" class="form-control form-control-sm" placeholder="Lote" required>
            </td>
            <td>
                <input type="date" name="items[${itemIndex}][expiration_date]" class="form-control form-control-sm" required>
            </td>
            <td>
                <select name="items[${itemIndex}][warehouse_location]" class="form-control form-control-sm" required>
                    ${locationOptions}
                </select>
            </td>
            <td>
                <select name="items[${itemIndex}][status]" class="form-control form-control-sm status-select" onchange="toggleRejectionReason(this)">
                    <option value="received">Recibido</option>
                    <option value="rejected">Rechazado</option>
                </select>
                <input type="text" name="items[${itemIndex}][rejection_reason]" class="form-control form-control-sm rejection-reason mt-1" placeholder="Razón" style="display:none;">
            </td>
            <td class="text-center align-middle">
                <button type="button" class="btn btn-outline-danger btn-xs" onclick="removeItem(this)">
                    <i class="fas fa-trash-alt"></i>
                </button>
            </td>`;

        row.innerHTML = cols;
        tbody.appendChild(row);
        
        // Inicializar select2 en el nuevo row usando AJAX
        initProductSelect2($(row).find('.product-select-ajax'));
        
        // La fecha de vencimiento inicia habilitada y opcional
        $(row).find('input[type="date"]').prop('disabled', false).prop('required', false);
        
        itemIndex++;
        updateSerialPanel();
    }

    function removeItem(button) {
        const tbody = document.getElementById('itemsBody');
        if (tbody.rows.length > 1) {
            $(button).closest('tr').remove();
            updateSerialPanel();
        } else {
            alert('Debe mantener al menos un producto.');
        }
    }

    function toggleRejectionReason(select) {
        const row = select.closest('tr');
        const reasonInput = row.querySelector('.rejection-reason');
        if (select.value === 'rejected') {
            reasonInput.style.display = 'block';
            reasonInput.required = true;
        } else {
            reasonInput.style.display = 'none';
            reasonInput.required = false;
            reasonInput.value = '';
        }
    }

    // onProductChange removido; manejado vía select2:select en AJAX

    function onQtyChange(input) {
        updateSerialPanel();
    }

    // Actualiza dinámicamente el panel derecho de series
    function updateSerialPanel() {
        const panel = $('#serialValidatorPanel');
        const body = $('#serialPanelBody');
        let hasSerializedItems = false;

        // Guardar valores existentes de series para no borrarlos
        let existingValues = {};
        $('.serial-textarea').each(function() {
            let idx = $(this).data('index');
            existingValues[idx] = $(this).val();
        });

        // Limpiar el body del panel
        body.empty();

        // Buscar filas que requieren series
        $('#itemsBody tr').each(function() {
            const tr = $(this);
            const idx = tr.data('index');
            const requiresSerial = tr.attr('data-requires-serial') === 'true';
            
            if (requiresSerial) {
                hasSerializedItems = true;
                
                // Obtener nombre del producto
                let productName = '';
                const select = tr.find('.product-select-ajax');
                if (select.length > 0) {
                    productName = select.find('option:selected').text() || 'Producto Seleccionado';
                } else {
                    // Es un texto si viene de PO precargado
                    productName = tr.find('span.font-weight-bold').first().text();
                }

                // Obtener cantidad
                const qtyInput = tr.find('.item-qty');
                const qty = parseInt(qtyInput.val()) || 0;
                
                // Cargar valor previo si existía
                const prevVal = existingValues[idx] || '';

                let blockHtml = `
                    <div class="card card-outline card-warning shadow-none border mb-3 serial-block" data-index="${idx}">
                        <div class="card-header py-2 bg-light">
                            <h6 class="card-title text-dark font-weight-bold mb-0" style="font-size: 0.9rem;">${productName}</h6>
                        </div>
                        <div class="card-body p-2">
                            <label class="small text-muted font-weight-bold">Ingrese ${qty} número(s) de serie (uno por línea o separados por comas):</label>
                            <textarea name="items[${idx}][serial_number]" class="form-control form-control-sm serial-textarea" 
                                data-index="${idx}" rows="3" placeholder="Ej: SN123, SN124..." required>${prevVal}</textarea>
                            <div class="invalid-feedback serial-feedback-${idx} font-weight-bold"></div>
                            <div class="mt-1 d-flex justify-content-between">
                                <small class="text-muted">Series ingresadas: <span class="serial-count font-weight-bold text-success">0</span></small>
                                <small class="text-muted">Requerido: <span class="serial-target font-weight-bold text-primary">${qty}</span></small>
                            </div>
                        </div>
                    </div>`;
                
                body.append(blockHtml);
            }
        });

        if (hasSerializedItems) {
            panel.show();
            // Ejecutar validación inicial
            validateSerials();
        } else {
            panel.hide();
        }
    }

    // Valida y cuenta los números de serie en caliente
    function validateSerials() {
        let allValid = true;
        $('.serial-block').each(function() {
            const block = $(this);
            const idx = block.data('index');
            const qtyInput = $(`tr[data-index="${idx}"] .item-qty`);
            const qty = parseInt(qtyInput.val()) || 0;
            
            const textarea = block.find('.serial-textarea');
            const value = textarea.val() || '';
            
            // Dividir por saltos de línea o comas
            const serials = value.split(/[\n,]+/).map(s => s.trim()).filter(s => s !== '');
            const count = serials.length;
            
            block.find('.serial-count').text(count);
            block.find('.serial-target').text(qty);
            
            const feedback = block.find(`.serial-feedback-${idx}`);
            
            if (count !== qty) {
                textarea.addClass('is-invalid');
                feedback.text(`La cantidad de series (${count}) debe ser exactamente igual a la cantidad a recibir (${qty}).`);
                allValid = false;
            } else {
                textarea.removeClass('is-invalid');
                feedback.text('');
            }
        });
        return allValid;
    }

    function initializeExpirationFields() {
        $('#itemsBody tr').each(function() {
            const tr = $(this);
            let isPerishable = tr.attr('data-is-perishable') === 'true';
            const expDateInput = tr.find('input[type="date"]');
            if (expDateInput.length > 0) {
                expDateInput.prop('disabled', false); // Siempre habilitado para permitir edición
                if (isPerishable) {
                    expDateInput.prop('required', true).removeClass('bg-light');
                } else {
                    expDateInput.prop('required', false).removeClass('bg-light');
                }
            }
        });
    }

    // 1. Destructor seguro (Receta RFQ)
    function safeDestroySelect2($el) {
        if ($el.hasClass('select2-hidden-accessible')) {
            $el.select2('destroy');
        }
    }

    // 2. Inicializador AJAX para Proveedores
    function initSupplierSelect2($select) {
        safeDestroySelect2($select);
        $select.empty();
        
        if (!$select.find('option').length) {
            $select.html('<option value=""></option>');
        }

        $select.select2({
            theme: 'bootstrap4',
            width: '100%',
            allowClear: true,
            placeholder: 'Seleccione un proveedor...',
            ajax: {
                url: '{{ route("admin.suppliers.search-ajax") }}',
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    return { q: params.term, page: params.page || 1 };
                },
                processResults: function (data) {
                    return {
                        results: data.results,
                        pagination: { more: data.pagination ? data.pagination.more : false }
                    };
                },
                cache: true
            }
        });
    }

    // 3. Inicializador AJAX para Productos (Clon de RFQ)
    function initProductSelect2($select) {
        safeDestroySelect2($select);
        $select.empty();
        
        if (!$select.find('option').length) {
            $select.html('<option value=""></option>');
        }

        $select.select2({
            theme: 'bootstrap4',
            width: '100%',
            allowClear: true,
            placeholder: 'Seleccione un producto...',
            ajax: {
                url: '{{ route("admin.products.search-ajax") }}',
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    return { 
                        q: params.term, 
                        page: params.page || 1,
                        category_id: typeof selectedCategoryId !== 'undefined' ? selectedCategoryId : null 
                    };
                },
                processResults: function (data) {
                    return {
                        results: data.results,
                        pagination: { more: data.pagination.more }
                    };
                },
                cache: true
            }
        }).on('select2:select', function(e) {
            // Reemplazo del antiguo "onProductChange()" leyendo datos en tiempo real de AJAX
            const data = e.params.data;
            const row = $(this).closest('tr');
            
            // Control de Seriales
            const requiresSerial = data.requires_serial === true || data.requires_serial === 'true';
            row.attr('data-requires-serial', requiresSerial ? 'true' : 'false');
            if (typeof updateSerialPanel === 'function') updateSerialPanel();

            // Control de Vencimiento
            const isPerishable = data.is_perishable === true || data.is_perishable === 'true';
            row.attr('data-is-perishable', isPerishable ? 'true' : 'false');
            const expDateInput = row.find('input[type="date"]');
            if (expDateInput.length > 0) {
                expDateInput.prop('disabled', false);
                if (isPerishable) {
                    expDateInput.prop('required', true).removeClass('bg-light');
                } else {
                    expDateInput.prop('required', false).removeClass('bg-light');
                }
            }
        }).on('select2:clear', function() {
            const row = $(this).closest('tr');
            row.attr('data-requires-serial', 'false');
            row.attr('data-is-perishable', 'false');
            if (typeof updateSerialPanel === 'function') updateSerialPanel();
            row.find('input[type="date"]').prop('required', false).removeClass('bg-light');
        });
    }

    $(document).ready(function() {
        // Inicializar genéricos (si queda alguno)
        $('.select2').not('.supplier-select-ajax, .product-select-ajax').select2({
            theme: 'bootstrap4'
        });

        // 4. Disparadores AJAX
        if($('.supplier-select-ajax').length) {
            initSupplierSelect2($('.supplier-select-ajax'));
        }
        
        $('.product-select-ajax').each(function() {
            initProductSelect2($(this));
        });

        // Filtro de categorías (solo en modo sin OC)
        @unless($order)
        applyStockInFilter(); // estado inicial: selector deshabilitado
        $('#categoryFilter').on('change', function() {
            selectedCategoryId = $(this).val() || null;
            applyStockInFilter();
        });
        @endunless

        // Escuchar cambios en las series ingresadas
        $(document).on('keyup change', '.serial-textarea', function() {
            validateSerials();
        });

        // Ejecutar panel inicial por si hay productos serializados precargados
        updateSerialPanel();

        // Inicializar campos de vencimiento según perecederos
        initializeExpirationFields();

        // Validar el formulario al enviar
        $('#stockInForm').on('submit', function(e) {
            const itemCount = $('#itemsBody tr').length;
            if (itemCount === 0) {
                e.preventDefault();
                alert('Debe agregar al menos un producto.');
                return false;
            }

            // Validar series antes de enviar
            if ($('#serialValidatorPanel').is(':visible')) {
                const serialsValid = validateSerials();
                if (!serialsValid) {
                    e.preventDefault();
                    Swal.fire({
                        icon: 'error',
                        title: 'Validación de Series',
                        text: 'Por favor, corrija los números de serie ingresados. Deben coincidir exactamente con las cantidades indicadas.',
                        confirmButtonColor: '#3085d6'
                    });
                    return false;
                }
            }
        });
    });
</script>
@stop
