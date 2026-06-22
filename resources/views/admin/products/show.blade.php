@extends('adminlte::page')

@section('title', 'Producto: ' . $product->name)

@section('plugins.Sweetalert2', true)

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1><i class="fas fa-box"></i> Producto: <strong>{{ $product->name }}</strong></h1>
        <div>
            @if($product->childFraction)
                <button type="button" class="btn btn-warning" data-toggle="modal" data-target="#unpackModal">
                    <i class="fas fa-box-open"></i> Desempacar
                </button>
            @endif
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
                            <th>Tipo Producto:</th>
                            <td>
                                @if($product->is_generic)
                                    <span class="badge badge-secondary"><i class="fas fa-tags"></i> Genérico</span>
                                @else
                                    <span class="badge badge-primary"><i class="fas fa-microscope"></i> Estricto</span>
                                @endif
                            </td>
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
                        @if($product->childFraction)
                            <tr>
                                <th>Fraccionamiento (Hijo):</th>
                                <td>
                                    <a href="{{ route('admin.products.show', $product->childFraction->childProduct) }}">
                                        {{ $product->childFraction->childProduct->name }}
                                    </a>
                                    <span class="badge badge-info">(1 caja = {{ $product->childFraction->conversion_factor }} unidades)</span>
                                </td>
                            </tr>
                        @endif
                        @if($product->parentFraction)
                            <tr>
                                <th>Fraccionamiento (Padre):</th>
                                <td>
                                    <a href="{{ route('admin.products.show', $product->parentFraction->parentProduct) }}">
                                        {{ $product->parentFraction->parentProduct->name }}
                                    </a>
                                    <span class="badge badge-info">(1 caja = {{ $product->parentFraction->conversion_factor }} unidades)</span>
                                </td>
                            </tr>
                        @endif
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
                                                @if($batch->expiration_date)
                                                    <span class="badge {{ \Carbon\Carbon::parse($batch->expiration_date)->isPast() ? 'badge-danger' : 'badge-warning' }}">
                                                        {{ \Carbon\Carbon::parse($batch->expiration_date)->format('d/m/Y') }}
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

    {{-- Modal de Desempaque --}}
    @if($product->childFraction)
    <div class="modal fade" id="unpackModal" tabindex="-1" role="dialog" aria-labelledby="unpackModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content" style="border-radius: 12px;">
                <div class="modal-header bg-warning text-dark" style="border-top-left-radius: 12px; border-top-right-radius: 12px;">
                    <h5 class="modal-title font-weight-bold" id="unpackModalLabel">
                        <i class="fas fa-box-open"></i> Desempacar Producto
                    </h5>
                    <button type="button" class="close text-dark" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="unpackForm" action="{{ route('admin.products.unpack', $product) }}" method="POST">
                    @csrf
                    <div class="modal-body py-4">
                        <div class="alert alert-info" style="border-radius: 8px;">
                            <i class="fas fa-info-circle mr-1"></i> 
                            Este producto se desempaca en: 
                            <strong>{{ $product->childFraction->childProduct->name }}</strong>
                            con un factor de conversión de <strong>{{ $product->childFraction->conversion_factor }}</strong> unidades por empaque.
                        </div>
                        
                        <div class="form-group">
                            <label for="unpack_quantity" class="font-weight-bold">Cantidad de empaques a abrir/desempacar <span class="text-danger">*</span></label>
                            <input type="number" name="quantity" id="unpack_quantity" class="form-control" value="1" min="1" max="{{ $product->stock }}" required>
                            <small class="form-text text-muted">Stock actual disponible: <strong>{{ $product->stock }}</strong> empaques.</small>
                        </div>

                        <div class="p-3 bg-light rounded mt-3 text-center border">
                            <span class="text-muted d-block text-uppercase font-weight-bold" style="font-size: 0.75rem; letter-spacing: 0.5px;">Resumen de Operación</span>
                            <div class="mt-2" style="font-size: 1.1rem;">
                                <span class="font-weight-bold text-danger">-<span id="display_parent_qty">1</span></span> Caja(s)
                                <i class="fas fa-arrow-right mx-2 text-muted"></i>
                                <span class="font-weight-bold text-success">+<span id="display_child_qty">{{ $product->childFraction->conversion_factor }}</span></span> Unidades sueltas
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer justify-content-between">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal" style="border-radius: 8px;">
                            Cancelar
                        </button>
                        <button type="submit" class="btn btn-warning font-weight-bold" id="btnSubmitUnpack" style="border-radius: 8px;">
                            <i class="fas fa-check-circle"></i> Confirmar Desempaque
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif
@stop

@section('js')
    @if($product->childFraction)
    <script>
        $(document).ready(function() {
            const factor = {{ $product->childFraction->conversion_factor }};
            
            $('#unpack_quantity').on('input change', function() {
                const qty = parseInt($(this).val()) || 0;
                $('#display_parent_qty').text(qty);
                $('#display_child_qty').text(qty * factor);
            });

            $('#unpackForm').on('submit', function(e) {
                e.preventDefault();
                const form = $(this);
                const submitBtn = $('#btnSubmitUnpack');
                const originalHtml = submitBtn.html();

                submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Procesando...');

                $.ajax({
                    url: form.attr('action'),
                    type: 'POST',
                    data: form.serialize(),
                    success: function(response) {
                        $('#unpackModal').modal('hide');
                        Swal.fire({
                            icon: 'success',
                            title: '¡Operación Exitosa!',
                            text: response.message || 'El producto se desempacó correctamente.',
                            timer: 2000,
                            showConfirmButton: false
                        }).then(() => {
                            window.location.reload();
                        });
                    },
                    error: function(xhr) {
                        submitBtn.prop('disabled', false).html(originalHtml);
                        let errorMsg = 'No se pudo realizar la operación de desempaque.';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMsg = xhr.responseJSON.message;
                        }
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: errorMsg
                        });
                    }
                });
            });
        });
    </script>
    @endif
@stop
