@extends('adminlte::page')

@section('title', 'Crear Kit')

@section('content_header')
    <h1 class="m-0 text-dark">Crear Kit</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            <form action="{{ route('admin.kits.store') }}" method="POST">
                @csrf

                <div class="card" style="border-left: 4px solid #8b5cf6;">
                    <div class="card-header" style="background: linear-gradient(135deg, #8b5cf6 0%, #a78bfa 100%);">
                        <h3 class="card-title text-white">
                            <i class="fas fa-cubes"></i> Detalles del Kit
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-12 col-md-6">
                                <div class="form-group">
                                    <label for="name">Nombre del Kit (*)</label>
                                    <div class="input-group input-group-sm">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text bg-purple text-white"><i class="fas fa-cube"></i></span>
                                        </div>
                                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" placeholder="Ej: Kit de Limpieza Avanzado" value="{{ old('name') }}" required>
                                    </div>
                                    @error('name')<span class="invalid-feedback">{{ $message }}</span>@enderror
                                </div>
                            </div>
                            <div class="col-12 col-md-6">
                                <div class="form-group mt-4">
                                    <label for="is_active">Estado</label>
                                    <div class="custom-control custom-switch">
                                        <input type="checkbox" name="is_active" class="custom-control-input" id="is_active" checked>
                                        <label class="custom-control-label" for="is_active">Activo</label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12">
                                <div class="form-group mb-0">
                                    <label for="description">Descripción</label>
                                    <textarea name="description" class="form-control @error('description') is-invalid @enderror" rows="3" placeholder="Detalles del kit...">{{ old('description') }}</textarea>
                                    @error('description')<span class="invalid-feedback">{{ $message }}</span>@enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card" style="border-left: 4px solid #06b6d4;">
                    <div class="card-header" style="background: linear-gradient(135deg, #06b6d4 0%, #22d3ee 100%);">
                        <div class="d-flex justify-content-between align-items-center">
                            <h3 class="card-title text-white">
                                <i class="fas fa-list"></i> Componentes
                            </h3>
                            <button type="button" class="btn btn-sm btn-outline-light text-light" id="add-component-btn">
                                <i class="fas fa-plus"></i> Agregar Componente
                            </button>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div id="components-container">
                            @if (old('components'))
                                @foreach (old('components') as $index => $component)
                                    @include('admin.kits.partials.component_row', ['index' => $index, 'products' => $products, 'oldComponent' => $component])
                                @endforeach
                            @endif
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-footer d-flex justify-content-end">
                        <a href="{{ route('admin.kits.index') }}" class="btn btn-secondary mr-2">
                            <i class="fas fa-arrow-left"></i> Cancelar
                        </a>
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-save"></i> Guardar Kit
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@stop

@include('admin.kits.partials.component_row_template', ['products' => $products])

@push('js')
<script>
    let componentIndex = {{ count(old('components', [])) }};
    
    function addComponentRow(data = null) {
        const template = $('#component-row-template').html();
        let newRow = template.replace(/__INDEX__/g, componentIndex);

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

    $('#add-component-btn').on('click', function() {
        addComponentRow();
    });

    $('#components-container').on('click', '.remove-component-btn', function() {
        $(this).closest('.component-row').remove();
    });

    if (componentIndex === 0) {
        addComponentRow();
    }
</script>
@endpush
