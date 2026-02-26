@extends('adminlte::page')

@section('title', 'Crear Solicitud de Cotización')

@section('plugins.Select2', true)

@section('content_header')
    <h1><i class="fas fa-file-invoice"></i> Nueva Solicitud de Cotización</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">Datos de la Solicitud</h3>
                </div>

                <form action="{{ route('admin.rfq.store') }}" method="POST">
                    @csrf
                    <div class="card-body">
                        <div class="row">
                            <div class="col-12 col-md-4">
                                <div class="form-group">
                                    <label for="code">Código RFQ</label>
                                    <input type="text" name="code" class="form-control" value="{{ $code }}" readonly>
                                </div>
                            </div>
                            <div class="col-12 col-md-8">
                                <div class="form-group">
                                    <label for="title">Título / Asunto (*)</label>
                                    <input type="text" name="title" class="form-control @error('title') is-invalid @enderror" value="{{ old('title') }}" required>
                                    @error('title')<span class="invalid-feedback">{{ $message }}</span>@enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12 col-md-6">
                                <div class="form-group">
                                    <label for="date_required">Fecha Límite de Respuesta</label>
                                    <input type="date" name="date_required" class="form-control @error('date_required') is-invalid @enderror" value="{{ old('date_required') }}">
                                    @error('date_required')<span class="invalid-feedback">{{ $message }}</span>@enderror
                                </div>
                            </div>
                            <div class="col-12 col-md-6">
                                <div class="form-group">
                                    <label for="delivery_deadline">Fecha Límite de Entrega</label>
                                    <input type="date" name="delivery_deadline" class="form-control @error('delivery_deadline') is-invalid @enderror" value="{{ old('delivery_deadline') }}">
                                    @error('delivery_deadline')<span class="invalid-feedback">{{ $message }}</span>@enderror
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="description">Descripción / Instrucciones</label>
                            <textarea name="description" id="description" rows="3" class="form-control @error('description') is-invalid @enderror">{{ old('description') }}</textarea>
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
                                    <tr>
                                        <td>
                                            <select name="items[0][product_id]" class="form-control select2-product" required>
                                                <option value="">Seleccione...</option>
                                                @foreach($products as $product)
                                                    <option value="{{ $product->id }}" data-unit="{{ $product->unit->abbreviation ?? 'und' }}">
                                                        {{ $product->name }} ({{ $product->code ?? 'S/C' }})
                                                    </option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td>
                                            <input type="number" name="items[0][quantity]" class="form-control" min="1" value="1" required>
                                        </td>
                                        <td>
                                            <input type="text" name="items[0][notes]" class="form-control" placeholder="Opcional">
                                        </td>
                                        <td class="text-center align-middle">
                                            <button type="button" class="btn btn-sm btn-danger remove-item" style="display:none;">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <button type="button" id="addItem" class="btn btn-success btn-sm mt-2">
                            <i class="fas fa-plus"></i> Agregar Producto
                        </button>

                        <div class="form-group mt-4">
                            <label for="notes">Notas Internas</label>
                            <textarea name="notes" id="notes" rows="2" class="form-control @error('notes') is-invalid @enderror" placeholder="Notas visibles solo internamente">{{ old('notes') }}</textarea>
                            @error('notes')<span class="invalid-feedback">{{ $message }}</span>@enderror
                        </div>
                    </div>

                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Guardar RFQ</button>
                        <a href="{{ route('admin.rfq.index') }}" class="btn btn-default float-right">Cancelar</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
@stop

@section('js')
    <script>
        let itemIndex = 1;

        $('#addItem').click(function() {
            const row = `
                <tr>
                    <td>
                        <select name="items[${itemIndex}][product_id]" class="form-control select2-product" required>
                            <option value="">Seleccione...</option>
                            @foreach($products as $product)
                                <option value="{{ $product->id }}" data-unit="{{ $product->unit->abbreviation ?? 'und' }}">
                                    {{ $product->name }} ({{ $product->code ?? 'S/C' }})
                                </option>
                            @endforeach
                        </select>
                    </td>
                    <td>
                        <input type="number" name="items[${itemIndex}][quantity]" class="form-control" min="1" value="1" required>
                    </td>
                    <td>
                        <input type="text" name="items[${itemIndex}][notes]" class="form-control" placeholder="Opcional">
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
