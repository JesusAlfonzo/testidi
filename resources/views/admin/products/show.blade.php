@extends('adminlte::page')

@section('title', 'Producto: ' . $product->name)

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1><i class="fas fa-box"></i> Producto: <strong>{{ $product->name }}</strong></h1>
        <div>
            <a href="{{ route('admin.products.edit', $product) }}" class="btn btn-primary">
                <i class="fas fa-edit"></i> Editar
            </a>
            <a href="{{ route('admin.products.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Volver
            </a>
        </div>
    </div>
@stop

@section('content')
    @include('admin.partials.session-messages')

    <div class="row">
        <div class="col-12 col-md-4">
            <div class="card" style="border-left: 4px solid #3b82f6;">
                <div class="card-header" style="background: linear-gradient(135deg, #3b82f6 0%, #60a5fa 100%);">
                    <h3 class="card-title text-white">
                        <i class="fas fa-info-circle"></i> Información General
                    </h3>
                </div>
                <div class="card-body">
                    <table class="table table-sm">
                        <tr>
                            <th width="40%">Código:</th>
                            <td><strong>{{ $product->code ?? 'N/A' }}</strong></td>
                        </tr>
                        <tr>
                            <th>Nombre:</th>
                            <td>{{ $product->name }}</td>
                        </tr>
                        <tr>
                            <th>Categoría:</th>
                            <td>{{ $product->category->name ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <th>Marca:</th>
                            <td>{{ $product->brand->name ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <th>Unidad:</th>
                            <td>{{ $product->unit->name ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <th>Ubicación:</th>
                            <td>{{ $product->location->name ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <th>Estado:</th>
                            <td>
                                @if($product->is_active)
                                    <span class="badge badge-success">Activo</span>
                                @else
                                    <span class="badge badge-danger">Inactivo</span>
                                @endif
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-4">
            <div class="card" style="border-left: 4px solid #10b981;">
                <div class="card-header" style="background: linear-gradient(135deg, #10b981 0%, #34d399 100%);">
                    <h3 class="card-title text-white">
                        <i class="fas fa-cubes"></i> Inventario
                    </h3>
                </div>
                <div class="card-body">
                    <table class="table table-sm">
                        <tr>
                            <th width="40%">Stock Actual:</th>
                            <td>
                                <span class="badge {{ $product->stock <= $product->min_stock ? 'badge-danger' : 'badge-success' }} font-weight-bold" style="font-size: 1.1rem;">
                                    {{ $product->stock }}
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <th>Stock Mínimo:</th>
                            <td>{{ $product->min_stock }}</td>
                        </tr>
                        <tr>
                            <th>Costo:</th>
                            <td>${{ number_format($product->cost, 2) }}</td>
                        </tr>
                        <tr>
                            <th>Precio Venta:</th>
                            <td>${{ number_format($product->price, 2) }}</td>
                        </tr>
                        <tr>
                            <th>Controla Lotes:</th>
                            <td>{{ $product->track_expiry ? 'Sí' : 'No' }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-4">
            <div class="card" style="border-left: 4px solid #f59e0b;">
                <div class="card-header" style="background: linear-gradient(135deg, #f59e0b 0%, #fbbf24 100%);">
                    <h3 class="card-title text-dark">
                        <i class="fas fa-tags"></i> Lotes / Batches
                    </h3>
                </div>
                <div class="card-body">
                    @php $activeBatches = $product->batches->where('quantity', '>', 0); @endphp
                    @if($activeBatches->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered mb-0">
                                <thead class="thead-light">
                                    <tr>
                                        <th>Lote</th>
                                        <th>Serie</th>
                                        <th>Cant.</th>
                                        <th>Venc.</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($activeBatches as $batch)
                                        <tr>
                                            <td>{{ $batch->batch_number ?? '-' }}</td>
                                            <td>{{ $batch->serial_number ?? '-' }}</td>
                                            <td><span class="badge badge-info">{{ $batch->quantity }}</span></td>
                                            <td>
                                                @if($batch->expiry_date)
                                                    <span class="badge {{ \Carbon\Carbon::parse($batch->expiry_date)->isPast() ? 'badge-danger' : 'badge-warning' }}">
                                                        {{ \Carbon\Carbon::parse($batch->expiry_date)->format('d/m/Y') }}
                                                    </span>
                                                @else
                                                    -
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-muted mb-0">Sin lotes activos.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @if($product->description)
    <div class="row mt-3">
        <div class="col-12">
            <div class="card" style="border-left: 4px solid #6c757d;">
                <div class="card-header" style="background: linear-gradient(135deg, #6c757d 0%, #8a939d 100%);">
                    <h3 class="card-title text-white">
                        <i class="fas fa-align-left"></i> Descripción
                    </h3>
                </div>
                <div class="card-body">
                    <p class="mb-0">{{ $product->description }}</p>
                </div>
            </div>
        </div>
    </div>
    @endif

    <div class="row mt-3">
        <div class="col-12">
            <div class="card" style="border-left: 4px solid #8b5cf6;">
                <div class="card-header" style="background: linear-gradient(135deg, #8b5cf6 0%, #a78bfa 100%);">
                    <h3 class="card-title text-white">
                        <i class="fas fa-truck-loading"></i> Últimas Entradas de Inventario
                    </h3>
                </div>
                <div class="card-body">
                    @if($stockIns->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered mb-0">
                                <thead class="thead-light">
                                    <tr>
                                        <th>Entrada</th>
                                        <th>Fecha</th>
                                        <th>OC Vinculada</th>
                                        <th>Cantidad</th>
                                        <th>Costo Unit.</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($stockIns as $entry)
                                        @php $entryItem = $entry->items->where('product_id', $product->id)->first(); @endphp
                                        <tr>
                                            <td>
                                                <a href="{{ route('admin.stock-in.show', $entry) }}">#{{ $entry->id }}</a>
                                            </td>
                                            <td>{{ $entry->entry_date->format('d/m/Y') }}</td>
                                            <td>
                                                @if($entry->purchaseOrder)
                                                    <a href="{{ route('admin.purchaseOrders.show', $entry->purchaseOrder) }}">{{ $entry->purchaseOrder->code }}</a>
                                                @else
                                                    <span class="text-muted">N/A</span>
                                                @endif
                                            </td>
                                            <td><span class="badge badge-success">{{ $entryItem->quantity ?? 0 }}</span></td>
                                            <td>${{ number_format($entryItem->unit_cost ?? 0, 2) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-muted mb-0">Sin entradas de inventario registradas para este producto.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
@stop
