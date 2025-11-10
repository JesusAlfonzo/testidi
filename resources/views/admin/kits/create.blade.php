@extends('adminlte::page')

@section('title', 'Crear Kit')

@section('content_header')
    <h1 class="m-0 text-dark">Crear Kit</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            <x-adminlte-card title="Detalles del Kit" icon="fas fa-cube" class="card-primary">
                <form action="{{ route('admin.kits.store') }}" method="POST">
                    @csrf

                    <div class="row">
                        {{-- Nombre --}}
                        <div class="col-md-6">
                            <x-adminlte-input name="name" label="Nombre del Kit" placeholder="Ej: Kit de Limpieza Avanzado" value="{{ old('name') }}" required/>
                        </div>

                        {{-- Precio --}}
                        <div class="col-md-3">
                            <x-adminlte-input name="unit_price" type="number" label="Precio Unitario" placeholder="0.00" value="{{ old('unit_price') }}" min="0" step="0.01"/>
                        </div>
                        
                        {{-- Activo --}}
                        <div class="col-md-3 mt-4">
                            <div class="form-group">
                                <label for="is_active">Estado</label>
                                <div class="custom-control custom-switch">
                                    <input type="checkbox" name="is_active" class="custom-control-input" id="is_active" checked>
                                    <label class="custom-control-label" for="is_active">Activo</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Descripción --}}
                    <x-adminlte-textarea name="description" label="Descripción" placeholder="Detalles del kit..." rows="3">{{ old('description') }}</x-adminlte-textarea>

                    {{-- -------------------------- COMPONENTES DEL KIT -------------------------- --}}
                    <h5 class="mt-4"><i class="fas fa-list"></i> Componentes</h5>
                    <div id="components-container">
                        {{-- Aquí se agregarán dinámicamente los componentes --}}
                        @if (old('components'))
                            @foreach (old('components') as $index => $component)
                                @include('admin.kits.partials.component_row', ['index' => $index, 'products' => $products, 'oldComponent' => $component])
                            @endforeach
                        @endif
                    </div>

                    <button type="button" class="btn btn-sm btn-info mt-2" id="add-component-btn"><i class="fas fa-plus"></i> Agregar Componente</button>
                    
                    <hr>

                    <x-adminlte-button class="btn-flat" type="submit" label="Guardar Kit" theme="success" icon="fas fa-lg fa-save"/>
                    <a href="{{ route('admin.kits.index') }}" class="btn btn-flat btn-default">Cancelar</a>
                </form>
            </x-adminlte-card>
        </div>
    </div>
@stop

{{-- Archivo parcial para la fila del componente (Lo crearemos a continuación) --}}
@include('admin.kits.partials.component_row_template', ['products' => $products])

@push('js')
<script>
    let componentIndex = {{ count(old('components', [])) }};
    
    // Función para agregar una fila de componente
    function addComponentRow(data = null) {
        const template = $('#component-row-template').html();
        let newRow = template.replace(/__INDEX__/g, componentIndex);

        // Si hay datos, precargar valores (útil para errores de validación)
        if (data) {
            newRow = $(newRow);
            newRow.find('select[name="components[' + componentIndex + '][product_id]"]').val(data.product_id);
            newRow.find('input[name="components[' + componentIndex + '][quantity]"]').val(data.quantity);
            $('#components-container').append(newRow);
        } else {
            $('#components-container').append(newRow);
        }

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

    // Inicializar con al menos una fila si no hay datos previos
    if (componentIndex === 0) {
        addComponentRow();
    }
</script>
@endpush