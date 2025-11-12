@extends('adminlte::page')

@section('title', 'Crear Producto')

@section('content_header')
    <h1><i class="fas fa-box-open"></i> Crear Nuevo Producto</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">Datos del Producto</h3>
                </div>

                <form action="{{ route('admin.products.store') }}" method="POST">
                    @csrf
                    <div class="card-body">

                        <h4><i class="fas fa-info-circle"></i> Identificación y Clasificación</h4>
                        <hr>
                        <div class="row">
                            {{-- Código/SKU: col-12 en móvil, col-md-4 en PC --}}
                            <div class="col-12 col-md-4">
                                <div class="form-group">
                                    <label for="code">Código/SKU (*)</label>
                                    <input type="text" name="code" class="form-control @error('code') is-invalid @enderror" value="{{ old('code') }}" required>
                                    @error('code')<span class="invalid-feedback">{{ $message }}</span>@enderror
                                </div>
                            </div>
                            {{-- Nombre: col-12 en móvil, col-md-8 en PC --}}
                            <div class="col-12 col-md-8">
                                <div class="form-group">
                                    <label for="name">Nombre del Producto (*)</label>
                                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required>
                                    @error('name')<span class="invalid-feedback">{{ $message }}</span>@enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            {{-- Categoría: col-12 en móvil, col-md-3 en PC --}}
                            <div class="col-12 col-md-3">
                                <div class="form-group">
                                    <label for="category_id">Categoría (*)</label>
                                    <select name="category_id" id="category_id" class="form-control @error('category_id') is-invalid @enderror" required>
                                        <option value="">Seleccione...</option>
                                        @foreach($categories as $id => $name)
                                            <option value="{{ $id }}" {{ old('category_id') == $id ? 'selected' : '' }}>{{ $name }}</option>
                                        @endforeach
                                    </select>
                                    @error('category_id')<span class="invalid-feedback">{{ $message }}</span>@enderror
                                </div>
                            </div>
                            {{-- Unidad: col-12 en móvil, col-md-3 en PC --}}
                            <div class="col-12 col-md-3">
                                <div class="form-group">
                                    <label for="unit_id">Unidad de Medida (*)</label>
                                    <select name="unit_id" id="unit_id" class="form-control @error('unit_id') is-invalid @enderror" required>
                                        <option value="">Seleccione...</option>
                                        @foreach($units as $id => $name)
                                            <option value="{{ $id }}" {{ old('unit_id') == $id ? 'selected' : '' }}>{{ $name }}</option>
                                        @endforeach
                                    </select>
                                    @error('unit_id')<span class="invalid-feedback">{{ $message }}</span>@enderror
                                </div>
                            </div>
                            {{-- Marca: col-12 en móvil, col-md-6 en PC --}}
                            <div class="col-12 col-md-6">
                                <div class="form-group">
                                    <label for="brand_id">Marca (Opcional)</label>
                                    <select name="brand_id" id="brand_id" class="form-control @error('brand_id') is-invalid @enderror">
                                        <option value="">Ninguna...</option>
                                        @foreach($brands as $id => $name)
                                            <option value="{{ $id }}" {{ old('brand_id') == $id ? 'selected' : '' }}>{{ $name }}</option>
                                        @endforeach
                                    </select>
                                    @error('brand_id')<span class="invalid-feedback">{{ $message }}</span>@enderror
                                </div>
                            </div>
                        </div>

                        <h4 class="mt-4"><i class="fas fa-warehouse"></i> Almacenamiento y Stock</h4>
                        <hr>
                        <div class="row">
                            {{-- Ubicación: col-12 en móvil, col-md-6 en PC --}}
                            <div class="col-12 col-md-6">
                                <div class="form-group">
                                    <label for="location_id">Ubicación de Almacenamiento (*)</label>
                                    <select name="location_id" id="location_id" class="form-control @error('location_id') is-invalid @enderror" required>
                                        <option value="">Seleccione...</option>
                                        @foreach($locations as $id => $name)
                                            <option value="{{ $id }}" {{ old('location_id') == $id ? 'selected' : '' }}>{{ $name }}</option>
                                        @endforeach
                                    </select>
                                    @error('location_id')<span class="invalid-feedback">{{ $message }}</span>@enderror
                                </div>
                            </div>
                            {{-- Stock Inicial: col-6 en móvil, col-md-3 en PC --}}
                            <div class="col-6 col-md-3">
                                <div class="form-group">
                                    <label for="stock">Stock Inicial (*)</label>
                                    <input type="number" name="stock" class="form-control @error('stock') is-invalid @enderror" value="{{ old('stock', 0) }}" min="0" required>
                                    @error('stock')<span class="invalid-feedback">{{ $message }}</span>@enderror
                                </div>
                            </div>
                            {{-- Stock Mínimo: col-6 en móvil, col-md-3 en PC --}}
                            <div class="col-6 col-md-3">
                                <div class="form-group">
                                    <label for="min_stock">Stock Mínimo Alerta (*)</label>
                                    <input type="number" name="min_stock" class="form-control @error('min_stock') is-invalid @enderror" value="{{ old('min_stock', 0) }}" min="0" required>
                                    @error('min_stock')<span class="invalid-feedback">{{ $message }}</span>@enderror
                                </div>
                            </div>
                        </div>

                        <h4 class="mt-4"><i class="fas fa-dollar-sign"></i> Costos y Precios</h4>
                        <hr>
                        <div class="row">
                            {{-- Costo Unitario: col-12 en móvil, col-md-6 en PC --}}
                            <div class="col-12 col-md-6">
                                <div class="form-group">
                                    <label for="cost">Costo Unitario (S/IVA) (*)</label>
                                    <input type="number" step="0.01" name="cost" class="form-control @error('cost') is-invalid @enderror" value="{{ old('cost', 0.00) }}" min="0" required>
                                    @error('cost')<span class="invalid-feedback">{{ $message }}</span>@enderror
                                </div>
                            </div>
                            {{-- Precio Venta: col-12 en móvil, col-md-6 en PC --}}
                            <div class="col-12 col-md-6">
                                <div class="form-group">
                                    <label for="price">Precio de Venta Sugerido (*)</label>
                                    <input type="number" step="0.01" name="price" class="form-control @error('price') is-invalid @enderror" value="{{ old('price', 0.00) }}" min="0" required>
                                    @error('price')<span class="invalid-feedback">{{ $message }}</span>@enderror
                                </div>
                            </div>
                        </div>

                        <h4 class="mt-4"><i class="fas fa-file-alt"></i> Detalles y Estado</h4>
                        <hr>
                        <div class="form-group">
                            <label for="description">Descripción del Producto (Opcional)</label>
                            <textarea name="description" id="description" rows="3" class="form-control @error('description') is-invalid @enderror">{{ old('description') }}</textarea>
                            @error('description')<span class="invalid-feedback">{{ $message }}</span>@enderror
                        </div>

                        <div class="form-group">
                            <div class="custom-control custom-switch">
                                <input type="checkbox" class="custom-control-input" id="is_active" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                                <label class="custom-control-label" for="is_active">Producto Activo</label>
                            </div>
                            <small class="form-text text-muted">Desactivar si el producto ya no se usa o está descontinuado.</small>
                        </div>
                    </div>

                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Guardar Producto</button>
                        <a href="{{ route('admin.products.index') }}" class="btn btn-default float-right">Cancelar</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
@stop