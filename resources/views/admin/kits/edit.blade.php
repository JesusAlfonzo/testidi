@extends('adminlte::page')

{{-- 🔑 CORRECCIÓN: Declaración 'use' movida al ámbito global --}}
@php use App\Models\Product; @endphp

@section('title', 'Editar Kit')

@section('content_header')
    <h1 class="m-0 text-dark">Editar Kit: {{ $kit->name }}</h1>
@stop

@section('content')
    @include('admin.partials.session-messages')

    <div class="row">
        <div class="col-12">
            <x-adminlte-card title="Detalles del Kit" icon="fas fa-cubes" class="card-warning">
                <form action="{{ route('admin.kits.update', $kit) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="row">
                        {{-- Nombre --}}
                        <div class="col-md-6">
                            <x-adminlte-input name="name" label="Nombre del Kit"
                                placeholder="Ej: Kit de Limpieza Avanzado" value="{{ old('name', $kit->name) }}" required />
                        </div>

                        {{-- Precio --}}
                        <div class="col-md-3">
                            <x-adminlte-input name="unit_price" type="number" label="Precio Unitario" placeholder="0.00"
                                value="{{ old('unit_price', $kit->unit_price) }}" min="0" step="0.01" />
                        </div>

                        {{-- Activo --}}
                        <div class="col-md-3 mt-4">
                            <div class="form-group">
                                <label for="is_active">Estado</label>
                                <div class="custom-control custom-switch">
                                    <input type="checkbox" name="is_active" class="custom-control-input" id="is_active"
                                        {{ old('is_active', $kit->is_active) ? 'checked' : '' }}>
                                    <label class="custom-control-label" for="is_active">Activo</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Descripción --}}
                    <x-adminlte-textarea name="description" label="Descripción" placeholder="Detalles del kit..."
                        rows="3">{{ old('description', $kit->description) }}</x-adminlte-textarea>

                    {{-- -------------------------- COMPONENTES DEL KIT -------------------------- --}}
                    <h5 class="mt-4"><i class="fas fa-list"></i> Componentes</h5>
                    <div id="components-container">
                        @php
                            // Preparamos los datos: si hay old('components'), usamos esa estructura de array simple.
                            // Si no hay old, mapeamos los objetos de la relación a arrays para consistencia.
                            $kitComponents = old('components')
                                ? collect(old('components')) // Si hay old, ya es una colección de arrays
                                : $kit->components->map(function ($comp) { // Si no hay old, mapeamos los objetos a arrays
                                    return [
                                        'product_id' => $comp->id,
                                        'quantity' => $comp->pivot->quantity_required,
                                    ];
                                });
                        @endphp

                        @foreach ($kitComponents as $index => $componentData)
                            {{-- 🔑 Para el select: buscamos el producto para tener el nombre y stock actualizado --}}
                            @php
                                // NOTA: Ya no necesitamos el 'use' aquí.
                                // Buscamos el producto en la colección original o directamente en DB
                                $product = $kit->components->firstWhere('id', $componentData['product_id']) ?? Product::find($componentData['product_id']);
                            @endphp

                            <div class="row component-item component-row" data-index="{{ $index }}">
                                <div class="col-md-8">
                                    <div class="form-group">
                                        <label for="component_product_{{ $index }}">Componente</label>
                                        <select name="components[{{ $index }}][product_id]"
                                            id="component_product_{{ $index }}" class="form-control select2-products"
                                            required>
                                            <option value="{{ $componentData['product_id'] }}" selected>
                                                {{ $product->name ?? 'Producto Eliminado' }} (Stock: {{ $product->stock ?? 0 }})
                                            </option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <x-adminlte-input name="components[{{ $index }}][quantity]" type="number"
                                        label="Cant. Requerida"
                                        value="{{ old('components.' . $index . '.quantity', $componentData['quantity']) }}"
                                        min="1" required />
                                </div>
                                <div class="col-md-1 d-flex align-items-center">
                                    <button type="button" class="btn btn-danger btn-sm mt-3 remove-component-btn" title="Eliminar componente">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <button type="button" class="btn btn-sm btn-info mt-2" id="add-component-btn"><i
                            class="fas fa-plus"></i> Agregar Componente</button>

                    <hr>

                    <x-adminlte-button class="btn-flat" type="submit" label="Actualizar Kit" theme="warning"
                        icon="fas fa-lg fa-save" />
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
            
            // Re-inicializar SELECT2 para el nuevo campo si es necesario
            $('.select2-products:last').select2();
            
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

        // Inicializar select2 en los selectores existentes:
        $('.select2-products').select2();
    </script>
@endpush