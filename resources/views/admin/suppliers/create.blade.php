@extends('adminlte::page')

@section('title', 'Crear Proveedor')

@section('plugins.Select2', true)

@section('content_header')
    <h1><i class="fas fa-building"></i> Nuevo Proveedor</h1>
@stop

@section('content')
    <form action="{{ route('admin.suppliers.store') }}" method="POST" id="supplierForm">
        @csrf

        <div class="row">
            <div class="col-12">
                <div class="card" style="border-left: 4px solid #f59e0b;">
                    <div class="card-header" style="background: linear-gradient(135deg, #f59e0b 0%, #fbbf24 100%);">
                        <h3 class="card-title text-white">
                            <i class="fas fa-building"></i> Datos del Proveedor
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-12 col-md-6">
                                <div class="form-group">
                                    <label for="name">Nombre / Razón Social (*)</label>
                                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required>
                                    @error('name')<span class="invalid-feedback">{{ $message }}</span>@enderror
                                </div>
                            </div>
                            <div class="col-12 col-md-6">
                                <div class="form-group">
                                    <label for="tax_id">RIF / ID Fiscal (*)</label>
                                    <input type="text" name="tax_id" class="form-control @error('tax_id') is-invalid @enderror" value="{{ old('tax_id') }}" placeholder="J-00000000-0">
                                    @error('tax_id')<span class="invalid-feedback">{{ $message }}</span>@enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12 col-md-6">
                                <div class="form-group">
                                    <label for="contact_person">Persona de Contacto</label>
                                    <input type="text" name="contact_person" class="form-control @error('contact_person') is-invalid @enderror" value="{{ old('contact_person') }}">
                                    @error('contact_person')<span class="invalid-feedback">{{ $message }}</span>@enderror
                                </div>
                            </div>
                            <div class="col-12 col-md-6">
                                <div class="form-group">
                                    <label for="representative_cedula">Cédula del Representante Legal</label>
                                    <input type="text" name="representative_cedula" class="form-control @error('representative_cedula') is-invalid @enderror" value="{{ old('representative_cedula') }}" placeholder="V-00000000">
                                    @error('representative_cedula')<span class="invalid-feedback">{{ $message }}</span>@enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12 col-md-6">
                                <div class="form-group">
                                    <label for="email">Email</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                        </div>
                                        <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}">
                                    </div>
                                    @error('email')<span class="invalid-feedback">{{ $message }}</span>@enderror
                                </div>
                            </div>
                            <div class="col-12 col-md-6">
                                <div class="form-group">
                                    <label for="phone">Teléfono Principal</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"><i class="fas fa-phone"></i></span>
                                        </div>
                                        <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone') }}">
                                    </div>
                                    @error('phone')<span class="invalid-feedback">{{ $message }}</span>@enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12">
                                <div class="form-group">
                                    <label for="address">Dirección</label>
                                    <textarea name="address" class="form-control @error('address') is-invalid @enderror" rows="2">{{ old('address') }}</textarea>
                                    @error('address')<span class="invalid-feedback">{{ $message }}</span>@enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12">
                                <div class="form-group">
                                    <label for="fiscal_address">Dirección Fiscal</label>
                                    <textarea name="fiscal_address" class="form-control @error('fiscal_address') is-invalid @enderror" rows="2" placeholder="Dirección fiscal para facturación">{{ old('fiscal_address') }}</textarea>
                                    @error('fiscal_address')<span class="invalid-feedback">{{ $message }}</span>@enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12 col-md-6">
                                <div class="form-group">
                                    <label for="is_active">Estado</label>
                                    <select name="is_active" class="form-control">
                                        <option value="1" {{ old('is_active', true) == '1' ? 'selected' : '' }}>Activo</option>
                                        <option value="0" {{ old('is_active', true) == '0' ? 'selected' : '' }}>Inactivo</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body d-flex justify-content-end">
                        <a href="{{ route('admin.suppliers.index') }}" class="btn btn-secondary btn-lg mr-2">
                            <i class="fas fa-times"></i> Cancelar
                        </a>
                        <button type="submit" class="btn btn-warning btn-lg text-dark">
                            <i class="fas fa-save"></i> Guardar Proveedor
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
@stop

@section('css')
    <style>
        .select2-container--default .select2-selection--single {
            height: 38px;
            padding-top: 4px;
        }
        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 38px;
        }
    </style>
@endsection