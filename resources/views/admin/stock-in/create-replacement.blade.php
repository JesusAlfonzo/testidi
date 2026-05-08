@extends('adminlte::page')

@section('title', 'Registrar Reemplazo de Productos')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1><i class="fas fa-sync-alt"></i> Registrar Reemplazo de Productos</h1>
        <a href="{{ route('admin.stock-in.show', $originalStockIn) }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Volver
        </a>
    </div>
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            @include('admin.partials.session-messages')
            
            <form action="{{ route('admin.stock-in.store-replacement') }}" method="POST" id="replacementForm">
                @csrf
                <input type="hidden" name="original_stock_in_id" value="{{ $originalStockIn->id }}">
                <input type="hidden" name="purchase_order_id" value="{{ $originalStockIn->purchase_order_id }}">
                <input type="hidden" name="supplier_id" value="{{ $originalStockIn->supplier_id }}">

                <div class="card" style="border-left: 4px solid #f59e0b;">
                    <div class="card-header" style="background: linear-gradient(135deg, #f59e0b 0%, #fbbf24 100%);">
                        <h3 class="card-title text-white">
                            <i class="fas fa-info-circle"></i> Información de la Entrada Original
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3">
                                <strong>Entrada #{{ $originalStockIn->id }}</strong><br>
                                <small class="text-muted">{{ \Carbon\Carbon::parse($originalStockIn->entry_date)->format('d/m/Y') }}</small>
                            </div>
                            <div class="col-md-3">
                                <strong>Proveedor:</strong><br>
                                {{ $originalStockIn->supplier->name ?? 'N/A' }}
                            </div>
                            <div class="col-md-3">
                                <strong>Orden de Compra:</strong><br>
                                @if($originalStockIn->purchaseOrder)
                                    {{ $originalStockIn->purchaseOrder->code }}
                                @else
                                    N/A
                                @endif
                            </div>
                            <div class="col-md-3">
                                <strong>Razón:</strong><br>
                                {{ $originalStockIn->reason ?? 'N/A' }}
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card" style="border-left: 4px solid #10b981;">
                    <div class="card-header" style="background: linear-gradient(135deg, #10b981 0%, #34d399 100%);">
                        <h3 class="card-title text-white">
                            <i class="fas fa-calendar"></i> Datos de la Transacción
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-12 col-md-6">
                                <div class="form-group">
                                    <label for="entry_date">Fecha de Ingreso (*)</label>
                                    <div class="input-group input-group-sm">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text bg-info text-white"><i class="fas fa-calendar"></i></span>
                                        </div>
                                        <input type="date" name="entry_date" class="form-control @error('entry_date') is-invalid @enderror" value="{{ old('entry_date', \Carbon\Carbon::now()->toDateString()) }}" required>
                                    </div>
                                    @error('entry_date')<span class="invalid-feedback">{{ $message }}</span>@enderror
                                </div>
                            </div>

                            <div class="col-12 col-md-6">
                                <div class="form-group">
                                    <label for="reason">Razón del Reemplazo</label>
                                    <div class="input-group input-group-sm">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text bg-info text-white"><i class="fas fa-question-circle"></i></span>
                                        </div>
                                        <input type="text" name="reason" class="form-control @error('reason') is-invalid @enderror" value="{{ old('reason', 'Reemplazo de productos rechazados') }}" placeholder="Ej: Reemplazo por productos defectuosos">
                                    </div>
                                    @error('reason')<span class="invalid-feedback">{{ $message }}</span>@enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card" style="border-left: 4px solid #6c757d;">
                    <div class="card-header" style="background: linear-gradient(135deg, #6c757d 0%, #8a939d 100%);">
                        <h3 class="card-title text-white">
                            <i class="fas fa-file-invoice"></i> Documentación
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-12 col-md-3">
                                <div class="form-group">
                                    <label for="document_type">Tipo de Documento</label>
                                    <input type="text" name="document_type" class="form-control form-control-sm" value="{{ old('document_type') }}" placeholder="Ej: Factura, Guía">
                                </div>
                            </div>
                            <div class="col-12 col-md-3">
                                <div class="form-group">
                                    <label for="document_number">Número de Documento</label>
                                    <input type="text" name="document_number" class="form-control form-control-sm" value="{{ old('document_number') }}">
                                </div>
                            </div>
                            <div class="col-12 col-md-3">
                                <div class="form-group">
                                    <label for="invoice_number">Número de Factura</label>
                                    <input type="text" name="invoice_number" class="form-control form-control-sm" value="{{ old('invoice_number') }}">
                                </div>
                            </div>
                            <div class="col-12 col-md-3">
                                <div class="form-group">
                                    <label for="delivery_note_number">Nota de Entrega</label>
                                    <input type="text" name="delivery_note_number" class="form-control form-control-sm" value="{{ old('delivery_note_number') }}">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card" style="border-left: 4px solid #3b82f6;">
                    <div class="card-header" style="background: linear-gradient(135deg, #3b82f6 0%, #60a5fa 100%);">
                        <h3 class="card-title text-white">
                            <i class="fas fa-boxes"></i> Productos Rechazados a Reemplazar
                        </h3>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped mb-0">
                                <thead class="bg-info text-white">
                                    <tr>
                                        <th>Producto</th>
                                        <th>Cantidad Rechazada</th>
                                        <th>Cantidad a Reemplazar</th>
                                        <th>Costo Unit.</th>
                                        <th>Nro. Lote</th>
                                        <th>Fecha Venc.</th>
                                        <th>Nro. Serie</th>
                                        <th>Ubicación</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($rejectedItems as $index => $rejectedItem)
                                        <tr>
                                            <td>
                                                <input type="hidden" name="items[{{ $index }}][product_id]" value="{{ $rejectedItem->product_id }}">
                                                <input type="hidden" name="items[{{ $index }}][replaced_item_id]" value="{{ $rejectedItem->id }}">
                                                {{ $rejectedItem->product->name ?? 'N/A' }}
                                                <br><small class="text-muted">{{ $rejectedItem->product->code ?? '' }}</small>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge badge-danger">{{ $rejectedItem->quantity }}</span>
                                                @if($rejectedItem->rejection_reason)
                                                    <br><small class="text-muted">{{ $rejectedItem->rejection_reason }}</small>
                                                @endif
                                            </td>
                                            <td>
                                                <input type="number" name="items[{{ $index }}][quantity]" class="form-control form-control-sm" value="{{ $rejectedItem->quantity }}" min="1" max="{{ $rejectedItem->quantity }}" required>
                                            </td>
                                            <td>
                                                <input type="number" step="0.01" name="items[{{ $index }}][unit_cost]" class="form-control form-control-sm" value="{{ $rejectedItem->unit_cost }}" min="0" required>
                                            </td>
                                            <td>
                                                <input type="text" name="items[{{ $index }}][batch_number]" class="form-control form-control-sm" placeholder="Lote">
                                            </td>
                                            <td>
                                                <input type="date" name="items[{{ $index }}][expiry_date]" class="form-control form-control-sm">
                                            </td>
                                            <td>
                                                <input type="text" name="items[{{ $index }}][serial_number]" class="form-control form-control-sm" placeholder="Serie">
                                            </td>
                                            <td>
                                                <select name="items[{{ $index }}][warehouse_location]" class="form-control form-control-sm">
                                                    <option value="">Seleccione...</option>
                                                    @foreach($locations as $name)
                                                        <option value="{{ $name }}">{{ $name }}</option>
                                                    @endforeach
                                                </select>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @error('items')
                            <div class="text-danger mt-2 p-2">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="card">
                    <div class="card-footer d-flex justify-content-end">
                        <a href="{{ route('admin.stock-in.show', $originalStockIn) }}" class="btn btn-secondary mr-2">
                            <i class="fas fa-times"></i> Cancelar
                        </a>
                        <button type="submit" class="btn btn-warning">
                            <i class="fas fa-sync-alt"></i> Registrar Reemplazo
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@stop

@section('js')
<script>
    $(document).ready(function() {
        $('#replacementForm').on('submit', function(e) {
            const itemCount = $('input[name^="items"]').length;
            if (itemCount === 0) {
                e.preventDefault();
                alert('No hay productos para reemplazar.');
                return false;
            }
        });
    });
</script>
@stop
