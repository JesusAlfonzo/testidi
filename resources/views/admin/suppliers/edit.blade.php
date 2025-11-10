@extends('adminlte::page')

@section('title', 'Editar Proveedor')

@section('content_header')
    <h1>Editar Proveedor: {{ $supplier->name }}</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-md-8">
            <div class="card card-warning">
                <div class="card-header">
                    <h3 class="card-title">Modificar Datos</h3>
                </div>

                <form action="{{ route('admin.suppliers.update', $supplier) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="card-body">
                         <div class="row">
                            {{-- Columna 1 --}}
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="name">Nombre / Razón Social (*)</label>
                                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" id="name" value="{{ old('name', $supplier->name) }}" required>
                                    @error('name')<span class="invalid-feedback">{{ $message }}</span>@enderror
                                </div>

                                <div class="form-group">
                                    <label for="tax_id">ID Fiscal (RUC/NIT)</label>
                                    <input type="text" name="tax_id" class="form-control @error('tax_id') is-invalid @enderror" id="tax_id" value="{{ old('tax_id', $supplier->tax_id) }}">
                                    @error('tax_id')<span class="invalid-feedback">{{ $message }}</span>@enderror
                                </div>
                            </div>

                            {{-- Columna 2 --}}
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="contact_person">Persona de Contacto</label>
                                    <input type="text" name="contact_person" class="form-control @error('contact_person') is-invalid @enderror" id="contact_person" value="{{ old('contact_person', $supplier->contact_person) }}">
                                    @error('contact_person')<span class="invalid-feedback">{{ $message }}</span>@enderror
                                </div>
                                <div class="form-group">
                                    <label for="phone">Teléfono</label>
                                    <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror" id="phone" value="{{ old('phone', $supplier->phone) }}">
                                    @error('phone')<span class="invalid-feedback">{{ $message }}</span>@enderror
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" id="email" value="{{ old('email', $supplier->email) }}">
                            @error('email')<span class="invalid-feedback">{{ $message }}</span>@enderror
                        </div>

                        <div class="form-group">
                            <label for="address">Dirección</label>
                            <textarea name="address" class="form-control @error('address') is-invalid @enderror" id="address" rows="2">{{ old('address', $supplier->address) }}</textarea>
                            @error('address')<span class="invalid-feedback">{{ $message }}</span>@enderror
                        </div>
                    </div>

                    <div class="card-footer">
                        <button type="submit" class="btn btn-warning"><i class="fas fa-sync-alt"></i> Actualizar Proveedor</button>
                        <a href="{{ route('admin.suppliers.index') }}" class="btn btn-default float-right">Cancelar</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
@stop
