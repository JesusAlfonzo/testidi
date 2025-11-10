@extends('adminlte::page')

@section('title', 'Editar Kit')

@section('content_header')
    <h1 class="m-0 text-dark">Editar Kit: {{ $kit->name }}</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            <x-adminlte-card title="Detalles del Kit" icon="fas fa-cube" class="card-warning">
                <form action="{{ route('admin.kits.update', $kit) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="row">
                        {{-- Nombre --}}
                        <div class="col-md-6">
                            <x-adminlte-input name="name" label="Nombre del Kit" placeholder="Ej: Kit de Limpieza Avanzado" value="{{ old('name', $kit->name) }}" required/>
                        </div>

                        {{-- Precio --}}
                        <div class="col-md-3">
                            <x-adminlte-input name="unit_price" type="number" label="Precio Unitario" placeholder="0.00" value="{{ old('unit_price', $kit->unit_price) }}" min="0" step="0.01"/>
                        </div>
                        
                        {{-- Activo --}}
                        <div class="col-md-3 mt-4">
                            <div class="form-group">
                                <label for="is_active">Estado</label>
                                <div class="custom-control custom-switch">
                                    <input type="checkbox" name="is_active" class="custom-control-input" id="is_active" {{ old('is_active', $kit->is_active) ? 'checked' : '' }}>
                                    <label class="custom-control-label" for="is_active">Activo</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Descripción --}}
                    <x-adminlte-textarea name="description" label="Descripción" placeholder="Detalles del kit..." rows="3">{{ old('description', $kit->description) }}</x-adminlte-textarea>

                    {{-- -------------------------- COMPONENTES DEL KIT -------------------------- --}}
                    <h5 class="mt-4"><i class="fas fa-list"></i> Componentes</h5>
                    <div id="components-container">
                        @php
                            // Usar old() en caso de error de validación, si no, usar los componentes actuales del kit
                            $kitComponents = old('components') 
                                ? collect(old('components')) 
                                : $kit->components->map(function($comp) {
                                    return [
                                        'product_id' => $comp->id,
                                        'quantity' => $comp->pivot->quantity_required,
                                    ];
                                });
                        @endphp
                        
                        @foreach ($kitComponents as $index => $component)
                            <div class="row component-row border border-light rounded p-2 mb-2 bg-light">
                                <div class="col-md-8">
                                    <div class="form-group">
                                        <label for="product_{{ $index }}">Producto</label>
                                        <select name="components[{{ $index }}][product_id]" class="form-control select2" required>
                                            <option value="">Seleccione un producto</option>
                                            @foreach ($products as $product)
                                                <option value="{{ $product->id }}" {{ ($component['product_id'] == $product->id) ? 'selected' : '' }}>
                                                    {{ $product->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <x-adminlte-input name="components[{{ $index }}][quantity]" type="number" label="Cant. Requerida" value="{{ $component['quantity'] }}" min="1" required/>
                                </div>
                                <div class="col-md-1 d-flex align-items-center">
                                    <button type="button" class="btn btn-danger btn-sm mt-3 remove-component-btn" title="Eliminar componente">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <button type="button" class="btn btn-sm btn-info mt-2" id="add-component-btn"><i class="fas fa-plus"></i> Agregar Componente</button>
                    
                    <hr>

                    <x-adminlte-button class="btn-flat" type="submit" label="Actualizar Kit" theme="warning" icon="fas fa-lg fa-save"/>
                    <a href="{{ route('admin.kits.index') }}" class="btn btn-flat btn-default">Cancelar</a>
                </form>
            </x-adminlte-card>
        </div>
    </div>
@stop

{{-- Incluimos el template de la fila para poder agregar nuevos componentes --}}
@include('admin.kits.partials.component_row_template', ['products' => $products])

@push('js')
<script>
    // Inicializar el índice basado en los componentes ya cargados
    let componentIndex = {{ count($kitComponents) }};
    
    // Función para agregar una fila de componente
    function addComponentRow() {
        const template = $('#component-row-template').html();
        let newRow = template.replace(/__INDEX__/g, componentIndex);
        $('#components-container').append(newRow);
        componentIndex++;
    }

    // Listener para el botón de agregar
    $('#add-component-btn').on('click', function() {
        addComponentRow();
    });

    // Listener para el botón de eliminar (delegado)
    $('#components-container').on('click', '.remove-component-btn', function() {
        $(this).closest('.component-row').remove();
    });
    
    // Si usas select2, debes inicializarlo en los selectores existentes:
    $('.select2').select2();
</script>
@endpush