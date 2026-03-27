@extends('adminlte::page')

@section('title', 'Crear Producto')

@section('content_header')
    <h1><i class="fas fa-box-open"></i> Crear Nuevo Producto</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card" style="border-left: 4px solid #3b82f6;">
                <div class="card-header" style="background: linear-gradient(135deg, #3b82f6 0%, #60a5fa 100%);">
                    <h3 class="card-title text-white">
                        <i class="fas fa-box"></i> Datos del Producto
                    </h3>
                </div>

                <form action="{{ route('admin.products.store') }}" method="POST">
                    @csrf
                    <div class="card-body">
                        <div class="card" style="border-left: 4px solid #06b6d4;">
                            <div class="card-header" style="background: linear-gradient(135deg, #06b6d4 0%, #22d3ee 100%);">
                                <h3 class="card-title text-white">
                                    <i class="fas fa-info-circle"></i> Identificación y Clasificación
                                </h3>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-12 col-md-4">
                                        <div class="form-group">
                                            <label for="code">Código/SKU (*)</label>
                                            <div class="input-group input-group-sm">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text bg-info text-white"><i class="fas fa-barcode"></i></span>
                                                </div>
                                                <input type="text" name="code" class="form-control @error('code') is-invalid @enderror" value="{{ old('code') }}" required>
                                            </div>
                                            @error('code')<span class="invalid-feedback">{{ $message }}</span>@enderror
                                        </div>
                                    </div>
                                    <div class="col-12 col-md-8">
                                        <div class="form-group">
                                            <label for="name">Nombre del Producto (*)</label>
                                            <div class="input-group input-group-sm">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text bg-info text-white"><i class="fas fa-box-open"></i></span>
                                                </div>
                                                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required>
                                            </div>
                                            @error('name')<span class="invalid-feedback">{{ $message }}</span>@enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-12 col-md-3">
                                        <div class="form-group">
                                            <label for="category_id">Categoría (*)</label>
                                            <select name="category_id" id="category_id" class="form-control form-control-sm @error('category_id') is-invalid @enderror" required>
                                                <option value="">Seleccione...</option>
                                                @foreach($categories as $id => $name)
                                                    <option value="{{ $id }}" {{ old('category_id') == $id ? 'selected' : '' }}>{{ $name }}</option>
                                                @endforeach
                                            </select>
                                            @error('category_id')<span class="invalid-feedback">{{ $message }}</span>@enderror
                                        </div>
                                    </div>
                                    <div class="col-12 col-md-3">
                                        <div class="form-group">
                                            <label for="unit_id">Unidad de Medida (*)</label>
                                            <select name="unit_id" id="unit_id" class="form-control form-control-sm @error('unit_id') is-invalid @enderror" required>
                                                <option value="">Seleccione...</option>
                                                @foreach($units as $id => $name)
                                                    <option value="{{ $id }}" {{ old('unit_id') == $id ? 'selected' : '' }}>{{ $name }}</option>
                                                @endforeach
                                            </select>
                                            @error('unit_id')<span class="invalid-feedback">{{ $message }}</span>@enderror
                                        </div>
                                    </div>
                                    <div class="col-12 col-md-6">
                                        <div class="form-group">
                                            <label for="brand_id">Marca (Opcional)</label>
                                            <select name="brand_id" id="brand_id" class="form-control form-control-sm @error('brand_id') is-invalid @enderror">
                                                <option value="">Ninguna...</option>
                                                @foreach($brands as $id => $name)
                                                    <option value="{{ $id }}" {{ old('brand_id') == $id ? 'selected' : '' }}>{{ $name }}</option>
                                                @endforeach
                                            </select>
                                            @error('brand_id')<span class="invalid-feedback">{{ $message }}</span>@enderror
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card" style="border-left: 4px solid #10b981;">
                            <div class="card-header" style="background: linear-gradient(135deg, #10b981 0%, #34d399 100%);">
                                <h3 class="card-title text-white">
                                    <i class="fas fa-warehouse"></i> Almacenamiento y Stock
                                </h3>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-12 col-md-6">
                                        <div class="form-group">
                                            <label for="location_id">Ubicación de Almacenamiento (*)</label>
                                            <select name="location_id" id="location_id" class="form-control form-control-sm @error('location_id') is-invalid @enderror" required>
                                                <option value="">Seleccione...</option>
                                                @foreach($locations as $id => $name)
                                                    <option value="{{ $id }}" {{ old('location_id') == $id ? 'selected' : '' }}>{{ $name }}</option>
                                                @endforeach
                                            </select>
                                            @error('location_id')<span class="invalid-feedback">{{ $message }}</span>@enderror
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <div class="form-group">
                                            <label for="stock">Stock Inicial (*)</label>
                                            <div class="input-group input-group-sm">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text bg-success text-white"><i class="fas fa-boxes"></i></span>
                                                </div>
                                                <input type="number" name="stock" class="form-control @error('stock') is-invalid @enderror" value="{{ old('stock', 0) }}" min="0" required>
                                            </div>
                                            @error('stock')<span class="invalid-feedback">{{ $message }}</span>@enderror
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <div class="form-group">
                                            <label for="min_stock">Stock Mínimo Alerta (*)</label>
                                            <div class="input-group input-group-sm">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text bg-danger text-white"><i class="fas fa-exclamation-triangle"></i></span>
                                                </div>
                                                <input type="number" name="min_stock" class="form-control @error('min_stock') is-invalid @enderror" value="{{ old('min_stock', 0) }}" min="0" required>
                                            </div>
                                            @error('min_stock')<span class="invalid-feedback">{{ $message }}</span>@enderror
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card" style="border-left: 4px solid #f59e0b;">
                            <div class="card-header" style="background: linear-gradient(135deg, #f59e0b 0%, #fbbf24 100%);">
                                <h3 class="card-title text-white">
                                    <i class="fas fa-dollar-sign"></i> Costos y Precios
                                </h3>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-12 col-md-6">
                                        <div class="form-group">
                                            <label for="cost">Costo Unitario (S/IVA) (*)</label>
                                            <div class="input-group input-group-sm">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text bg-warning text-dark"><i class="fas fa-money-bill-wave"></i></span>
                                                </div>
                                                <input type="number" step="0.01" name="cost" class="form-control @error('cost') is-invalid @enderror" value="{{ old('cost', 0.00) }}" min="0" required>
                                            </div>
                                            @error('cost')<span class="invalid-feedback">{{ $message }}</span>@enderror
                                        </div>
                                    </div>
                                    <div class="col-12 col-md-6">
                                        <div class="form-group mb-0">
                                            <label for="price">Precio de Venta Sugerido (*)</label>
                                            <div class="input-group input-group-sm">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text bg-warning text-dark"><i class="fas fa-tag"></i></span>
                                                </div>
                                                <input type="number" step="0.01" name="price" class="form-control @error('price') is-invalid @enderror" value="{{ old('price', 0.00) }}" min="0" required>
                                            </div>
                                            @error('price')<span class="invalid-feedback">{{ $message }}</span>@enderror
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card" style="border-left: 4px solid #6c757d;">
                            <div class="card-header" style="background: linear-gradient(135deg, #6c757d 0%, #8a939d 100%);">
                                <h3 class="card-title text-white">
                                    <i class="fas fa-file-alt"></i> Detalles y Estado
                                </h3>
                            </div>
                            <div class="card-body">
                                <div class="form-group">
                                    <label for="description">Descripción del Producto (Opcional)</label>
                                    <textarea name="description" id="description" rows="3" class="form-control @error('description') is-invalid @enderror">{{ old('description') }}</textarea>
                                    @error('description')<span class="invalid-feedback">{{ $message }}</span>@enderror
                                </div>

                                <div class="form-group mb-0">
                                    <div class="custom-control custom-switch">
                                        <input type="checkbox" class="custom-control-input" id="is_active" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                                        <label class="custom-control-label" for="is_active">Producto Activo</label>
                                    </div>
                                    <small class="form-text text-muted">Desactivar si el producto ya no se usa o está descontinuado.</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Guardar Producto</button>
                        <a href="{{ route('admin.products.index') }}" class="btn btn-default">Cancelar</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
@stop