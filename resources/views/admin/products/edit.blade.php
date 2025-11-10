@extends('adminlte::page')

@section('title', 'Editar Producto')

@section('content_header')
    <h1>Editar Producto: <strong>{{ $product->name }}</strong></h1>
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card card-warning">
                <div class="card-header">
                    <h3 class="card-title">Modificar Producto</h3>
                </div>

                <form action="{{ route('admin.products.update', $product) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="card-body">

                        <h4><i class="fas fa-info-circle"></i> Identificación y Clasificación</h4>
                        <hr>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="code">Código/SKU (*)</label>
                                    <input type="text" name="code" class="form-control @error('code') is-invalid @enderror" value="{{ old('code', $product->code) }}" required>
                                    @error('code')<span class="invalid-feedback">{{ $message }}</span>@enderror
                                </div>
                            </div>
                            <div class="col-md-8">
                                <div class="form-group">
                                    <label for="name">Nombre del Producto (*)</label>
                                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $product->name) }}" required>
                                    @error('name')<span class="invalid-feedback">{{ $message }}</span>@enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="category_id">Categoría (*)</label>
                                    <select name="category_id" id="category_id" class="form-control @error('category_id') is-invalid @enderror" required>
                                        <option value="">Seleccione...</option>
                                        @foreach($categories as $id => $name)
                                            <option value="{{ $id }}" {{ old('category_id', $product->category_id) == $id ? 'selected' : '' }}>{{ $name }}</option>
                                        @endforeach
                                    </select>
                                    @error('category_id')<span class="invalid-feedback">{{ $message }}</span>@enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="unit_id">Unidad de Medida (*)</label>
                                    <select name="unit_id" id="unit_id" class="form-control @error('unit_id') is-invalid @enderror" required>
                                        <option value="">Seleccione...</option>
                                        @foreach($units as $id => $name)
                                            <option value="{{ $id }}" {{ old('unit_id', $product->unit_id) == $id ? 'selected' : '' }}>{{ $name }}</option>
                                        @endforeach
                                    </select>
                                    @error('unit_id')<span class="invalid-feedback">{{ $message }}</span>@enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="brand_id">Marca (Opcional)</label>
                                    <select name="brand_id" id="brand_id" class="form-control @error('brand_id') is-invalid @enderror">
                                        <option value="">Ninguna...</option>
                                        @foreach($brands as $id => $name)
                                            <option value="{{ $id }}" {{ old('brand_id', $product->brand_id) == $id ? 'selected' : '' }}>{{ $name }}</option>
                                        @endforeach
                                    </select>
                                    @error('brand_id')<span class="invalid-feedback">{{ $message }}</span>@enderror
                                </div>
                            </div>
                        </div>

                        <h4 class="mt-4"><i class="fas fa-warehouse"></i> Almacenamiento y Stock</h4>
                        <hr>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="location_id">Ubicación de Almacenamiento (*)</label>
                                    <select name="location_id" id="location_id" class="form-control @error('location_id') is-invalid @enderror" required>
                                        <option value="">Seleccione...</option>
                                        @foreach($locations as $id => $name)
                                            <option value="{{ $id }}" {{ old('location_id', $product->location_id) == $id ? 'selected' : '' }}>{{ $name }}</option>
                                        @endforeach
                                    </select>
                                    @error('location_id')<span class="invalid-feedback">{{ $message }}</span>@enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="stock">Stock Actual (*)</label>
                                    <input type="number" name="stock" class="form-control @error('stock') is-invalid @enderror" value="{{ old('stock', $product->stock) }}" min="0" required>
                                    @error('stock')<span class="invalid-feedback">{{ $message }}</span>@enderror
                                    <small class="form-text text-danger">⚠️ **Nota:** El stock se maneja con movimientos. Solo ajustar aquí si es necesario.</small>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="min_stock">Stock Mínimo Alerta (*)</label>
                                    <input type="number" name="min_stock" class="form-control @error('min_stock') is-invalid @enderror" value="{{ old('min_stock', $product->min_stock) }}" min="0" required>
                                    @error('min_stock')<span class="invalid-feedback">{{ $message }}</span>@enderror
                                </div>
                            </div>
                        </div>

                        <h4 class="mt-4"><i class="fas fa-dollar-sign"></i> Costos y Precios</h4>
                        <hr>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="cost">Costo Unitario (S/IVA) (*)</label>
                                    <input type="number" step="0.01" name="cost" class="form-control @error('cost') is-invalid @enderror" value="{{ old('cost', $product->cost) }}" min="0" required>
                                    @error('cost')<span class="invalid-feedback">{{ $message }}</span>@enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="price">Precio de Venta Sugerido (*)</label>
                                    <input type="number" step="0.01" name="price" class="form-control @error('price') is-invalid @enderror" value="{{ old('price', $product->price) }}" min="0" required>
                                    @error('price')<span class="invalid-feedback">{{ $message }}</span>@enderror
                                </div>
                            </div>
                        </div>

                        <h4 class="mt-4"><i class="fas fa-file-alt"></i> Detalles y Estado</h4>
                        <hr>
                        <div class="form-group">
                            <label for="description">Descripción del Producto (Opcional)</label>
                            <textarea name="description" id="description" rows="3" class="form-control @error('description') is-invalid @enderror">{{ old('description', $product->description) }}</textarea>
                            @error('description')<span class="invalid-feedback">{{ $message }}</span>@enderror
                        </div>

                        <div class="form-group">
                            <div class="custom-control custom-switch">
                                @php
                                    $isChecked = old('is_active', $product->is_active);
                                @endphp
                                <input type="checkbox" class="custom-control-input" id="is_active" name="is_active" value="1" {{ $isChecked ? 'checked' : '' }}>
                                <label class="custom-control-label" for="is_active">Producto Activo</label>
                            </div>
                            <small class="form-text text-muted">Desactivar si el producto ya no se usa o está descontinuado.</small>
                        </div>
                    </div>

                    <div class="card-footer">
                        <button type="submit" class="btn btn-warning"><i class="fas fa-sync-alt"></i> Actualizar Producto</button>
                        <a href="{{ route('admin.products.index') }}" class="btn btn-default float-right">Cancelar</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
@stop
