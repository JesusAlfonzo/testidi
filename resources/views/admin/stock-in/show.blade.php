@extends('adminlte::page')

@section('title', 'Detalle de Entrada de Stock')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1><i class="fas fa-truck-loading"></i> Detalle de Entrada de Stock</h1>
        <a href="{{ route('admin.stock-in.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Volver
        </a>
    </div>
@stop

@section('content')
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
                            <th>ID:</th>
                            <td>{{ $stockIn->id }}</td>
                        </tr>
                        <tr>
                            <th>Fecha de Ingreso:</th>
                            <td>{{ \Carbon\Carbon::parse($stockIn->entry_date)->format('d/m/Y') }}</td>
                        </tr>
                        <tr>
                            <th>Razón:</th>
                            <td>{{ $stockIn->reason ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <th>Usuario:</th>
                            <td>{{ $stockIn->user->name ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <th>Creado:</th>
                            <td>{{ $stockIn->created_at->format('d/m/Y H:i') }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-4">
            <div class="card" style="border-left: 4px solid #f59e0b;">
                <div class="card-header" style="background: linear-gradient(135deg, #f59e0b 0%, #fbbf24 100%);">
                    <h3 class="card-title text-white">
                        <i class="fas fa-building"></i> Proveedor
                    </h3>
                </div>
                <div class="card-body">
                    @if($stockIn->supplier)
                        <table class="table table-sm">
                            <tr>
                                <th>Nombre:</th>
                                <td>{{ $stockIn->supplier->name }}</td>
                            </tr>
                            <tr>
                                <th>RIF:</th>
                                <td>{{ $stockIn->supplier->rif ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th>Teléfono:</th>
                                <td>{{ $stockIn->supplier->phone ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th>Email:</th>
                                <td>{{ $stockIn->supplier->email ?? 'N/A' }}</td>
                            </tr>
                        </table>
                    @else
                        <p class="text-muted">Sin proveedor (Ajuste de inventario)</p>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-12 col-md-4">
            <div class="card" style="border-left: 4px solid #6c757d;">
                <div class="card-header" style="background: linear-gradient(135deg, #6c757d 0%, #8a939d 100%);">
                    <h3 class="card-title text-white">
                        <i class="fas fa-file-alt"></i> Documentación
                    </h3>
                </div>
                <div class="card-body">
                    <table class="table table-sm">
                        <tr>
                            <th>Tipo:</th>
                            <td>{{ $stockIn->document_type ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <th>Número:</th>
                            <td>{{ $stockIn->document_number ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <th>Factura:</th>
                            <td>{{ $stockIn->invoice_number ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <th>Nota Entrega:</th>
                            <td>{{ $stockIn->delivery_note_number ?? 'N/A' }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>

    @if($stockIn->purchaseOrder)
    <div class="row mt-3">
        <div class="col-12">
            <div class="card" style="border-left: 4px solid #8b5cf6;">
                <div class="card-header" style="background: linear-gradient(135deg, #8b5cf6 0%, #a78bfa 100%);">
                    <h3 class="card-title text-white">
                        <i class="fas fa-file-contract"></i> Orden de Compra Relacionada
                    </h3>
                </div>
                <div class="card-body">
                    <a href="{{ route('admin.purchaseOrders.show', $stockIn->purchaseOrder) }}">
                        {{ $stockIn->purchaseOrder->code }}
                    </a>
                    - {{ $stockIn->purchaseOrder->supplier->name ?? 'N/A' }}
                </div>
            </div>
        </div>
    </div>
    @endif

    <div class="row mt-3">
        <div class="col-12">
            <div class="card" style="border-left: 4px solid #10b981;">
                <div class="card-header" style="background: linear-gradient(135deg, #10b981 0%, #34d399 100%);">
                    <h3 class="card-title text-white">
                        <i class="fas fa-boxes"></i> Productos Ingresados
                    </h3>
                </div>
                <div class="card-body table-responsive p-0">
                    <table class="table table-striped table-bordered mb-0">
                        <thead class="bg-success text-white">
                            <tr>
                                <th>Producto</th>
                                <th>Código</th>
                                <th class="text-center">Cantidad</th>
                                <th class="text-right">Costo Unit.</th>
                                <th class="text-right">Total</th>
                                <th>Lote</th>
                                <th>Fecha Venc.</th>
                                <th>Nro. Serie</th>
                                <th>Ubicación</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $totalGeneral = 0; @endphp
                            @foreach($stockIn->items as $item)
                                <tr>
                                    <td>{{ $item->product->name ?? 'N/A' }}</td>
                                    <td>{{ $item->product->code ?? 'N/A' }}</td>
                                    <td class="text-center">
                                        <span class="badge badge-success">{{ $item->quantity }}</span>
                                    </td>
                                    <td class="text-right">${{ number_format($item->unit_cost, 2) }}</td>
                                    <td class="text-right">${{ number_format($item->quantity * $item->unit_cost, 2) }}</td>
                                    <td>{{ $item->batch_number ?? '-' }}</td>
                                    <td>
                                        @if($item->expiry_date)
                                            <span class="badge {{ \Carbon\Carbon::parse($item->expiry_date)->isPast() ? 'badge-danger' : 'badge-warning' }}">
                                                {{ \Carbon\Carbon::parse($item->expiry_date)->format('d/m/Y') }}
                                            </span>
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td>{{ $item->serial_number ?? '-' }}</td>
                                    <td>{{ $item->warehouse_location ?? '-' }}</td>
                                </tr>
                                @php $totalGeneral += $item->quantity * $item->unit_cost; @endphp
                            @endforeach
                            <tr class="table-success font-weight-bold">
                                <td colspan="4" class="text-right">TOTAL:</td>
                                <td class="text-right">${{ number_format($totalGeneral, 2) }}</td>
                                <td colspan="4"></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-3">
        <div class="col-12">
            <div class="form-group">
                @can('entradas_eliminar')
                    <button type="button" class="btn btn-danger float-right" onclick="confirmDelete('{{ route('admin.stock-in.destroy', $stockIn) }}', 'Entrada de Stock #{{ $stockIn->id }}')">
                        <i class="fas fa-trash"></i> Eliminar Entrada
                    </button>
                @endcan
            </div>
        </div>
    </div>
@stop

@section('js')
<script>
function confirmDelete(url, name) {
    if (confirm('¿Está seguro de eliminar la ' + name + '? Esto revertirá el stock de los productos.')) {
        var form = document.createElement('form');
        form.method = 'POST';
        form.action = url;
        form.innerHTML = '@csrf @method("DELETE")';
        document.body.appendChild(form);
        form.submit();
    }
}
</script>
@stop
