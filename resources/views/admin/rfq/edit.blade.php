@extends('adminlte::page')

@section('title', 'Editar RFQ ' . $rfq->code)

@section('plugins.Select2', true)

@section('content_header')
    <h1><i class="fas fa-file-invoice"></i> Editar Solicitud de Cotización</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">RFQ {{ $rfq->code }} - {!! $rfq->status_badge !!}</h3>
                </div>

                <form action="{{ route('admin.rfq.update', $rfq) }}" method="POST">
                    @csrf @method('PUT')
                    <div class="card-body">
                        <div class="row">
                            <div class="col-12 col-md-4">
                                <div class="form-group">
                                    <label for="code">Código RFQ</label>
                                    <input type="text" name="code" class="form-control" value="{{ $rfq->code }}" readonly>
                                </div>
                            </div>
                            <div class="col-12 col-md-8">
                                <div class="form-group">
                                    <label for="title">Título / Asunto (*)</label>
                                    <input type="text" name="title" class="form-control @error('title') is-invalid @enderror" value="{{ old('title', $rfq->title) }}" required>
                                    @error('title')<span class="invalid-feedback">{{ $message }}</span>@enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12 col-md-6">
                                <div class="form-group">
                                    <label for="date_required">Fecha Límite de Respuesta</label>
                                    <input type="date" name="date_required" class="form-control @error('date_required') is-invalid @enderror" value="{{ old('date_required', $rfq->date_required?->format('Y-m-d')) }}">
                                    @error('date_required')<span class="invalid-feedback">{{ $message }}</span>@enderror
                                </div>
                            </div>
                            <div class="col-12 col-md-6">
                                <div class="form-group">
                                    <label for="delivery_deadline">Fecha Límite de Entrega</label>
                                    <input type="date" name="delivery_deadline" class="form-control @error('delivery_deadline') is-invalid @enderror" value="{{ old('delivery_deadline', $rfq->delivery_deadline?->format('Y-m-d')) }}">
                                    @error('delivery_deadline')<span class="invalid-feedback">{{ $message }}</span>@enderror
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="description">Descripción / Instrucciones</label>
                            <textarea name="description" id="description" rows="3" class="form-control @error('description') is-invalid @enderror">{{ old('description', $rfq->description) }}</textarea>
                            @error('description')<span class="invalid-feedback">{{ $message }}</span>@enderror
                        </div>

                        <h4 class="mt-4"><i class="fas fa-boxes"></i> Productos a Cotizar</h4>
                        <hr>

                        <div class="table-responsive">
                            <table class="table table-bordered" id="itemsTable">
                                <thead class="thead-light">
                                    <tr>
                                        <th style="width: 50%">Producto (*)</th>
                                        <th style="width: 20%">Cantidad (*)</th>
                                        <th style="width: 25%">Notas</th>
                                        <th style="width: 5%"></th>
                                    </tr>
                                </thead>
                                <tbody id="itemsBody">
                                    @foreach($rfq->items as $index => $item)
                                        <tr>
                                            <td>
                                                <select name="items[{{ $index }}][product_id]" class="form-control select2-product" required>
                                                    <option value="">Seleccione...</option>
                                                    @foreach($products as $product)
                                                        <option value="{{ $product->id }}" {{ $item->product_id == $product->id ? 'selected' : '' }}>
                                                            {{ $product->name }} ({{ $product->code ?? 'S/C' }})
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </td>
                                            <td>
                                                <input type="number" name="items[{{ $index }}][quantity]" class="form-control" min="1" value="{{ old("items.$index.quantity", $item->quantity) }}" required>
                                            </td>
                                            <td>
                                                <input type="text" name="items[{{ $index }}][notes]" class="form-control" value="{{ old("items.$index.notes", $item->notes) }}">
                                            </td>
                                            <td class="text-center align-middle">
                                                <button type="button" class="btn btn-sm btn-danger remove-item" style="display:none;">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <button type="button" id="addItem" class="btn btn-success btn-sm mt-2">
                            <i class="fas fa-plus"></i> Agregar Producto
                        </button>

                        <div class="form-group mt-4">
                            <label for="notes">Notas Internas</label>
                            <textarea name="notes" id="notes" rows="2" class="form-control @error('notes') is-invalid @enderror">{{ old('notes', $rfq->notes) }}</textarea>
                            @error('notes')<span class="invalid-feedback">{{ $message }}</span>@enderror
                        </div>
                    </div>

                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Guardar Cambios</button>
                        <a href="{{ route('admin.rfq.show', $rfq) }}" class="btn btn-default float-right">Cancelar</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
@stop

@section('js')
    <script>
        let itemIndex = {{ $rfq->items->count() }};

        $('#addItem').click(function() {
            const row = `
                <tr>
                    <td>
                        <select name="items[${itemIndex}][product_id]" class="form-control select2-product" required>
                            <option value="">Seleccione...</option>
                            @foreach($products as $product)
                                <option value="{{ $product->id }}">
                                    {{ $product->name }} ({{ $product->code ?? 'S/C' }})
                                </option>
                            @endforeach
                        </select>
                    </td>
                    <td>
                        <input type="number" name="items[${itemIndex}][quantity]" class="form-control" min="1" value="1" required>
                    </td>
                    <td>
                        <input type="text" name="items[${itemIndex}][notes]" class="form-control">
                    </td>
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
            updateRemoveButtons();
        });

        function updateRemoveButtons() {
            const rows = $('#itemsBody tr').length;
            $('#itemsBody .remove-item').toggle(rows > 1);
        }

        function initSelect2() {
            $('.select2-product').select2({
                theme: 'bootstrap4',
                width: '100%'
            });
        }

        $(document).ready(function() {
            initSelect2();
            updateRemoveButtons();
        });
    </script>
@endsection
