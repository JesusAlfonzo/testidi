@extends('adminlte::page')

@php use App\Models\Product; @endphp

@section('title', 'Editar Kit')

@section('content_header')
    <h1 class="m-0 text-dark">Editar Kit: {{ $kit->name }}</h1>
@stop

@section('content')
    @include('admin.partials.session-messages')

    <div class="row">
        <div class="col-12">
            <form action="{{ route('admin.kits.update', $kit) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="card" style="border-left: 4px solid #f59e0b;">
                    <div class="card-header" style="background: linear-gradient(135deg, #f59e0b 0%, #fbbf24 100%);">
                        <h3 class="card-title text-white">
                            <i class="fas fa-edit"></i> Detalles del Kit
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-12 col-md-6">
                                <div class="form-group">
                                    <label for="name">Nombre del Kit (*)</label>
                                    <div class="input-group input-group-sm">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text bg-warning text-dark"><i class="fas fa-cube"></i></span>
                                        </div>
                                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" placeholder="Ej: Kit de Limpieza Avanzado" value="{{ old('name', $kit->name) }}" required>
                                    </div>
                                    @error('name')<span class="invalid-feedback">{{ $message }}</span>@enderror
                                </div>
                            </div>
                            <div class="col-12 col-md-6">
                                <div class="form-group mt-4">
                                    <label for="is_active">Estado</label>
                                    <div class="custom-control custom-switch">
                                        <input type="checkbox" name="is_active" class="custom-control-input" id="is_active" {{ old('is_active', $kit->is_active) ? 'checked' : '' }}>
                                        <label class="custom-control-label" for="is_active">Activo</label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12">
                                <div class="form-group mb-0">
                                    <label for="description">Descripción</label>
                                    <textarea name="description" class="form-control @error('description') is-invalid @enderror" rows="3" placeholder="Detalles del kit...">{{ old('description', $kit->description) }}</textarea>
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
                            @php
                                $kitComponents = old('components')
                                    ? collect(old('components'))
                                    : $kit->components->map(function ($comp) {
                                        return [
                                            'product_id' => $comp->id,
                                            'quantity' => $comp->pivot->quantity_required,
                                        ];
                                    });
                            @endphp

                            @foreach ($kitComponents as $index => $componentData)
                                @php
                                    $product = $kit->components->firstWhere('id', $componentData['product_id']) ?? Product::find($componentData['product_id']);
                                @endphp

                                <div class="row component-item component-row p-2" data-index="{{ $index }}">
                                    <div class="col-12 col-md-8">
                                        <div class="form-group mb-0">
                                            <label for="component_product_{{ $index }}">Componente</label>
                                            <select name="components[{{ $index }}][product_id]" id="component_product_{{ $index }}" class="form-control form-control-sm select2-products" required>
                                                <option value="">Seleccione un producto</option>
                                                @foreach ($products as $prod)
                                                    <option value="{{ $prod->id }}" {{ $prod->id == $componentData['product_id'] ? 'selected' : '' }}>
                                                        {{ $prod->name }} (Stock: {{ $prod->stock ?? 0 }})
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-12 col-md-3">
                                        <div class="form-group mb-0">
                                            <label for="component_quantity_{{ $index }}">Cant. Requerida</label>
                                            <input type="number" name="components[{{ $index }}][quantity]" id="component_quantity_{{ $index }}" class="form-control form-control-sm" value="{{ old('components.' . $index . '.quantity', $componentData['quantity']) }}" min="1" required>
                                        </div>
                                    </div>
                                    <div class="col-12 col-md-1 d-flex align-items-end">
                                        <button type="button" class="btn btn-danger btn-sm remove-component-btn" title="Eliminar componente">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-footer d-flex justify-content-end">
                        <a href="{{ route('admin.kits.index') }}" class="btn btn-secondary mr-2">
                            <i class="fas fa-arrow-left"></i> Cancelar
                        </a>
                        <button type="submit" class="btn btn-warning text-dark">
                            <i class="fas fa-sync-alt"></i> Actualizar Kit
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
        let componentIndex = {{ count($kitComponents) }};

        function addComponentRow() {
            const template = $('#component-row-template').html();
            let newRow = template.replace(/__INDEX__/g, componentIndex);
            $('#components-container').append(newRow);
            $('.select2-products:last').select2();
            componentIndex++;
        }

        $('#add-component-btn').on('click', function() {
            addComponentRow();
        });

        $('#components-container').on('click', '.remove-component-btn', function() {
            $(this).closest('.component-row').remove();
        });

        $('.select2-products').select2();
        
        $(document).on('select2:open', function() {
            setTimeout(function() {
                var dropdown = document.querySelector('.select2-dropdown');
                if (dropdown) {
                    dropdown.style.maxHeight = '350px';
                    dropdown.style.overflow = 'hidden';
                    var results = dropdown.querySelector('.select2-results');
                    if (results) {
                        results.style.maxHeight = '350px';
                        results.style.overflowY = 'auto';
                    }
                }
            }, 10);
        });
    </script>
@endpush