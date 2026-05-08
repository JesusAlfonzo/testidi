@extends('adminlte::page')

@section('title', 'Editar Proveedor')

@section('plugins.Select2', true)

@section('content_header')
    <h1><i class="fas fa-building"></i> Editar Proveedor: <strong>{{ $supplier->name }}</strong></h1>
@stop

@section('content')
    @include('admin.partials.session-messages')

    <form action="{{ route('admin.suppliers.update', $supplier) }}" method="POST" id="supplierForm">
        @csrf
        @method('PUT')

        <div class="row">
            <div class="col-12">
                <div class="card" style="border-left: 4px solid #f59e0b;">
                    <div class="card-header" style="background: linear-gradient(135deg, #f59e0b 0%, #fbbf24 100%);">
                        <h3 class="card-title text-dark">
                            <i class="fas fa-building"></i> Datos del Proveedor
                        </h3>
                    </div>
                    <div class="card-body">

                        {{-- Identificación --}}
                        <div class="card" style="border-left: 4px solid #f59e0b;">
                            <div class="card-header" style="background: linear-gradient(135deg, #f59e0b 0%, #fbbf24 100%);">
                                <h3 class="card-title text-dark">
                                    <i class="fas fa-id-card"></i> Identificación
                                </h3>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-12 col-md-6">
                                        <div class="form-group">
                                            <label for="name">Nombre / Razón Social <span class="text-danger">*</span></label>
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text bg-warning text-dark"><i class="fas fa-building"></i></span>
                                                </div>
                                                <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $supplier->name) }}" placeholder="Ej: Insumos Médicos C.A." required>
                                            </div>
                                            @error('name')<span class="invalid-feedback">{{ $message }}</span>@enderror
                                            <small class="form-text text-muted">Nombre legal o razón social del proveedor.</small>
                                        </div>
                                    </div>
                                    <div class="col-12 col-md-4">
                                        <div class="form-group">
                                            <label for="tax_id">RIF / ID Fiscal <span class="text-danger">*</span></label>
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text bg-warning text-dark"><i class="fas fa-id-card"></i></span>
                                                </div>
                                                <input type="text" name="tax_id" id="tax_id" class="form-control @error('tax_id') is-invalid @enderror" value="{{ old('tax_id', $supplier->tax_id) }}" placeholder="J-00000000-0">
                                            </div>
                                            @error('tax_id')<span class="invalid-feedback">{{ $message }}</span>@enderror
                                            <small class="form-text text-muted">Registro de Información Fiscal.</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Contacto --}}
                        <div class="card" style="border-left: 4px solid #06b6d4;">
                            <div class="card-header" style="background: linear-gradient(135deg, #06b6d4 0%, #22d3ee 100%);">
                                <h3 class="card-title text-white">
                                    <i class="fas fa-address-book"></i> Contacto
                                </h3>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-12 col-md-6">
                                        <div class="form-group">
                                            <label for="contact_person">Persona de Contacto</label>
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text bg-info text-white"><i class="fas fa-user"></i></span>
                                                </div>
                                                <input type="text" name="contact_person" id="contact_person" class="form-control @error('contact_person') is-invalid @enderror" value="{{ old('contact_person', $supplier->contact_person) }}" placeholder="Nombre del contacto directo">
                                            </div>
                                            @error('contact_person')<span class="invalid-feedback">{{ $message }}</span>@enderror
                                        </div>
                                    </div>
                                    <div class="col-12 col-md-6">
                                        <div class="form-group">
                                            <label for="representative_cedula">Cédula del Representante Legal</label>
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text bg-info text-white"><i class="fas fa-id-badge"></i></span>
                                                </div>
                                                <input type="text" name="representative_cedula" id="representative_cedula" class="form-control @error('representative_cedula') is-invalid @enderror" value="{{ old('representative_cedula', $supplier->representative_cedula) }}" placeholder="V-00000000">
                                            </div>
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
                                                    <span class="input-group-text bg-info text-white"><i class="fas fa-envelope"></i></span>
                                                </div>
                                                <input type="email" name="email" id="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $supplier->email) }}" placeholder="proveedor@ejemplo.com">
                                            </div>
                                            @error('email')<span class="invalid-feedback">{{ $message }}</span>@enderror
                                        </div>
                                    </div>
                                    <div class="col-12 col-md-6">
                                        <div class="form-group">
                                            <label for="phone">Teléfono Principal</label>
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text bg-info text-white"><i class="fas fa-phone"></i></span>
                                                </div>
                                                <input type="text" name="phone" id="phone" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone', $supplier->phone) }}" placeholder="+58 412-0000000">
                                            </div>
                                            @error('phone')<span class="invalid-feedback">{{ $message }}</span>@enderror
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Direcciones --}}
                        <div class="card" style="border-left: 4px solid #10b981;">
                            <div class="card-header" style="background: linear-gradient(135deg, #10b981 0%, #34d399 100%);">
                                <h3 class="card-title text-white">
                                    <i class="fas fa-map-marked-alt"></i> Direcciones
                                </h3>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-12 col-md-6">
                                        <div class="form-group">
                                            <label for="address">Dirección</label>
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text bg-success text-white"><i class="fas fa-map-pin"></i></span>
                                                </div>
                                                <textarea name="address" id="address" class="form-control @error('address') is-invalid @enderror" rows="3" placeholder="Dirección física principal del proveedor.">{{ old('address', $supplier->address) }}</textarea>
                                            </div>
                                            @error('address')<span class="invalid-feedback">{{ $message }}</span>@enderror
                                        </div>
                                    </div>
                                    <div class="col-12 col-md-6">
                                        <div class="form-group">
                                            <label for="fiscal_address">Dirección Fiscal</label>
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text bg-success text-white"><i class="fas fa-file-invoice"></i></span>
                                                </div>
                                                <textarea name="fiscal_address" id="fiscal_address" class="form-control @error('fiscal_address') is-invalid @enderror" rows="3" placeholder="Dirección fiscal para emisión de facturas.">{{ old('fiscal_address', $supplier->fiscal_address) }}</textarea>
                                            </div>
                                            @error('fiscal_address')<span class="invalid-feedback">{{ $message }}</span>@enderror
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Configuración --}}
                        <div class="card" style="border-left: 4px solid #6c757d;">
                            <div class="card-header" style="background: linear-gradient(135deg, #6c757d 0%, #8a939d 100%);">
                                <h3 class="card-title text-white">
                                    <i class="fas fa-cog"></i> Configuración
                                </h3>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-12 col-md-6">
                                        <div class="form-group mb-0">
                                            <label for="is_active">Estado</label>
                                            <select name="is_active" id="is_active" class="form-control">
                                                <option value="1" {{ old('is_active', $supplier->is_active ?? true) == '1' || old('is_active', $supplier->is_active ?? true) === true ? 'selected' : '' }}>Activo</option>
                                                <option value="0" {{ old('is_active', $supplier->is_active ?? true) == '0' || old('is_active', $supplier->is_active ?? true) === false ? 'selected' : '' }}>Inactivo</option>
                                            </select>
                                            <small class="form-text text-muted">Un proveedor inactivo no estará disponible en nuevas órdenes de compra.</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Información del Registro --}}
                        <div class="card mb-0" style="border-left: 4px solid #8b5cf6;">
                            <div class="card-header" style="background: linear-gradient(135deg, #8b5cf6 0%, #a78bfa 100%);">
                                <h3 class="card-title text-white">
                                    <i class="fas fa-clipboard-list"></i> Información del Registro
                                </h3>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-12 col-md-4">
                                        <p class="mb-1"><strong><i class="fas fa-user"></i> Creado por:</strong></p>
                                        <p class="text-muted">{{ $supplier->user->name ?? 'N/A' }}</p>
                                    </div>
                                    <div class="col-12 col-md-4">
                                        <p class="mb-1"><strong><i class="fas fa-calendar"></i> Fecha de creación:</strong></p>
                                        <p class="text-muted">{{ $supplier->created_at->format('d/m/Y H:i') }}</p>
                                    </div>
                                    <div class="col-12 col-md-4">
                                        <p class="mb-1"><strong><i class="fas fa-clock"></i> Última actualización:</strong></p>
                                        <p class="text-muted">{{ $supplier->updated_at->format('d/m/Y H:i') }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                    <div class="card-footer">
                        <a href="{{ route('admin.suppliers.index') }}" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Cancelar
                        </a>
                        <button type="submit" class="btn btn-warning text-dark">
                            <i class="fas fa-sync-alt"></i> Actualizar Proveedor
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
@stop
