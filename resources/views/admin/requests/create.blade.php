@extends('adminlte::page')

@section('title', 'Crear Solicitud de Salida')

@section('content_header')
    <h1>Crear Nueva Solicitud de Salida</h1>
@stop

@section('content')
    @include('admin.partials.session-messages')

    <div class="row">
        <div class="col-md-8">
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">Detalles de la Solicitud</h3>
                </div>
                <form action="{{ route('admin.requests.store') }}" method="POST">
                    @csrf
                    <div class="card-body">

                        {{-- Información de la Cabecera --}}
                        <div class="form-group">
                            <label for="justification">Justificación de la Solicitud <span class="text-danger">*</span></label>
                            <textarea name="justification" id="justification" class="form-control @error('justification') is-invalid @enderror" rows="3" required>{{ old('justification') }}</textarea>
                            @error('justification')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <h5 class="mt-4"><i class="fas fa-box"></i> Productos a Solicitar</h5>
                        <hr>

                        {{-- Contenedor Dinámico de Ítems --}}
                        <div id="items-container">
                            @if (old('items'))
                                {{-- Si hay datos antiguos (error de validación), renderiza las filas previamente ingresadas --}}
                                @foreach (old('items') as $index => $item)
                                    @include('admin.requests.partials.item-row', ['index' => $index, 'products' => $products, 'item' => $item])
                                @endforeach
                            @endif
                            {{-- La lógica JS se encargará de añadir la primera fila si 'old' está vacío --}}
                        </div>

                        <button type="button" class="btn btn-info btn-sm mt-3" id="add-item-btn">
                            <i class="fas fa-plus"></i> Añadir Producto
                        </button>

                        @error('items')
                            <div class="alert alert-danger mt-3">{{ $message }}</div>
                        @enderror

                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-paper-plane"></i> Enviar Solicitud
                        </button>
                        <a href="{{ route('admin.requests.index') }}" class="btn btn-secondary">Cancelar</a>
                    </div>
                </form>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card card-info card-outline">
                <div class="card-header"><h3 class="card-title">Nota Importante</h3></div>
                <div class="card-body">
                    <p>La solicitud será enviada al estado **Pendiente** y deberá ser aprobada por un administrador para que el stock sea afectado.</p>
                    <dl>
                        <dt>Solicitante:</dt>
                        <dd>{{ Auth::user()->name }}</dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>
@stop

{{-- Estilos necesarios para Select2 --}}
@section('css')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css">
@stop

{{-- Scripts para Select2 y la lógica dinámica de la tabla --}}
@section('js')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.full.min.js"></script>
    <script>
        $(document).ready(function() {
            // Inicializar Select2 en las filas existentes (si las hay)
            $('.select2-product').select2({
                placeholder: "Seleccione un producto",
                allowClear: true,
                width: '100%',
                dropdownAutoWidth: true,
            });

            // Contador para el índice de los nuevos ítems
            let itemIndex = $('#items-container').children().length;

            // Inicialización de Select2 para una fila
            function initializeSelect2(selector) {
                $(selector).select2({
                    placeholder: "Seleccione un producto",
                    allowClear: true,
                    width: '100%',
                    dropdownAutoWidth: true,
                });
            }

            // 1. Manejar la adición de una nueva fila
            $('#add-item-btn').on('click', function() {
                // Generamos el HTML del parcial en el JS. Esto evita una llamada AJAX.
                let newRowHtml = `
                    <div class="row item-row mb-2 border-bottom pb-2 pt-2" data-index="${itemIndex}">
                        <div class="col-md-7">
                            <div class="form-group">
                                <label for="items_${itemIndex}_product_id">Producto</label>
                                <select name="items[${itemIndex}][product_id]"
                                        id="items_${itemIndex}_product_id"
                                        class="form-control form-control-sm select2-product"
                                        required>
                                    <option value="">Seleccione un producto</option>
                                    {{-- Iterar sobre la colección de productos de PHP --}}
                                    @foreach ($products as $id => $name)
                                        <option value="{{ $id }}">{{ $name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="items_${itemIndex}_quantity">Cantidad</label>
                                <input type="number"
                                       name="items[${itemIndex}][quantity]"
                                       id="items_${itemIndex}_quantity"
                                       class="form-control form-control-sm"
                                       value="1"
                                       min="1"
                                       required>
                            </div>
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <div class="form-group">
                                <button type="button" class="btn btn-danger btn-sm remove-item-btn">
                                    <i class="fas fa-trash"></i> Eliminar
                                </button>
                            </div>
                        </div>
                    </div>
                `;

                $('#items-container').append(newRowHtml);

                // Inicializar Select2 en el nuevo elemento
                initializeSelect2(`#items_${itemIndex}_product_id`);

                itemIndex++;
            });

            // 2. Manejar la eliminación de una fila (usa delegación)
            $('#items-container').on('click', '.remove-item-btn', function() {
                $(this).closest('.item-row').remove();
            });

            // 3. Añadir la primera fila si no hay ítems (y no hay old input)
            if (itemIndex === 0 && !@json(old('items'))) {
                $('#add-item-btn').click();
            }
        });
    </script>
@stop
