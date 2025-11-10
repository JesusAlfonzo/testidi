@extends('adminlte::page')

@section('title', 'Nueva Entrada de Stock')

@section('content_header')
    <h1>Registrar Nueva Entrada de Stock</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-md-8">
            <div class="card card-success">
                <div class="card-header">
                    <h3 class="card-title">Datos de la Transacción</h3>
                </div>

                <form action="{{ route('admin.stock-in.store') }}" method="POST">
                    @csrf
                    <div class="card-body">

                        <div class="row">
                            {{-- Producto --}}
                            <div class="col-md-8">
                                <div class="form-group">
                                    <label for="product_id">Producto (*)</label>
                                    <select name="product_id" id="product_id" class="form-control @error('product_id') is-invalid @enderror" required>
                                        <option value="">Seleccione un producto...</option>
                                        @foreach($products as $id => $name)
                                            <option value="{{ $id }}" {{ old('product_id') == $id ? 'selected' : '' }}>{{ $name }}</option>
                                        @endforeach
                                    </select>
                                    @error('product_id')<span class="invalid-feedback">{{ $message }}</span>@enderror
                                </div>
                            </div>

                            {{-- Cantidad --}}
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="quantity">Cantidad a Ingresar (*)</label>
                                    <input type="number" name="quantity" class="form-control @error('quantity') is-invalid @enderror" value="{{ old('quantity', 1) }}" min="1" required>
                                    @error('quantity')<span class="invalid-feedback">{{ $message }}</span>@enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            {{-- Proveedor --}}
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="supplier_id">Proveedor (Opcional)</label>
                                    <select name="supplier_id" id="supplier_id" class="form-control @error('supplier_id') is-invalid @enderror">
                                        <option value="">Ajuste / Sin proveedor...</option>
                                        @foreach($suppliers as $id => $name)
                                            <option value="{{ $id }}" {{ old('supplier_id') == $id ? 'selected' : '' }}>{{ $name }}</option>
                                        @endforeach
                                    </select>
                                    @error('supplier_id')<span class="invalid-feedback">{{ $message }}</span>@enderror
                                </div>
                            </div>

                            {{-- Costo --}}
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="unit_cost">Costo Unitario (*)</label>
                                    <input type="number" step="0.01" name="unit_cost" class="form-control @error('unit_cost') is-invalid @enderror" value="{{ old('unit_cost', 0.00) }}" min="0" required>
                                    @error('unit_cost')<span class="invalid-feedback">{{ $message }}</span>@enderror
                                    <small class="form-text text-muted">Este costo actualizará el costo promedio/último del producto.</small>
                                </div>
                            </div>

                            {{-- Fecha --}}
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="entry_date">Fecha de Ingreso (*)</label>
                                    <input type="date" name="entry_date" class="form-control @error('entry_date') is-invalid @enderror" value="{{ old('entry_date', \Carbon\Carbon::now()->toDateString()) }}" required>
                                    @error('entry_date')<span class="invalid-feedback">{{ $message }}</span>@enderror
                                </div>
                            </div>
                        </div>

                        <hr>
                        <h4><i class="fas fa-file-invoice"></i> Documentación (Opcional)</h4>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="document_type">Tipo de Documento</label>
                                    <input type="text" name="document_type" class="form-control @error('document_type') is-invalid @enderror" value="{{ old('document_type') }}" placeholder="Ej: Factura, Guía de Remisión">
                                    @error('document_type')<span class="invalid-feedback">{{ $message }}</span>@enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="document_number">Número de Documento</label>
                                    <input type="text" name="document_number" class="form-control @error('document_number') is-invalid @enderror" value="{{ old('document_number') }}" placeholder="Ej: F-001-12345">
                                    @error('document_number')<span class="invalid-feedback">{{ $message }}</span>@enderror
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="reason">Razón del Ingreso (Opcional)</label>
                            <textarea name="reason" id="reason" rows="2" class="form-control @error('reason') is-invalid @enderror">{{ old('reason') }}</textarea>
                            @error('reason')<span class="invalid-feedback">{{ $message }}</span>@enderror
                        </div>

                    </div>

                    <div class="card-footer">
                        <button type="submit" class="btn btn-success"><i class="fas fa-arrow-alt-circle-up"></i> Registrar Entrada</button>
                        <a href="{{ route('admin.stock-in.index') }}" class="btn btn-default float-right">Cancelar</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
@stop
