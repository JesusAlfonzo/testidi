@extends('adminlte::page')

@section('title', 'Maestros | Crear Proveedor')

@section('plugins.Select2', true)

@section('content_header')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h1 class="text-dark font-weight-bold" style="font-size: 1.75rem;">
                <i class="fas fa-truck text-primary mr-2"></i> Crear Proveedor
            </h1>
            <p class="text-muted mb-0">Registre un nuevo proveedor externo en el catálogo de la institución.</p>
        </div>
        <a href="{{ route('admin.suppliers.index') }}" class="btn btn-secondary px-3 py-2" style="border-radius: 8px;">
            <i class="fas fa-arrow-left mr-1"></i> Volver al Listado
        </a>
    </div>
@stop

@section('content')
    @include('admin.partials.session-messages')

    <div class="row">
        <div class="col-lg-9 mx-auto">
            <div class="card p-4 bg-white shadow-sm" style="border-radius: 12px; border: 1px solid #e5e7eb;">
                <form action="{{ route('admin.suppliers.store') }}" method="POST">
                    @csrf
                    
                    {{-- Sección: Identificación --}}
                    <h6 class="font-weight-bold text-dark mb-3">
                        <i class="fas fa-id-card text-info mr-2"></i> Identificación del Proveedor
                    </h6>
                    <div class="row">
                        <div class="col-md-7 mb-3">
                            <label for="name" class="text-xs font-weight-bold text-secondary text-uppercase mb-1 d-block">
                                Nombre / Razón Social <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text bg-light" style="border-top-left-radius: 8px; border-bottom-left-radius: 8px;"><i class="fas fa-building text-muted"></i></span>
                                </div>
                                <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" 
                                       value="{{ old('name') }}" placeholder="Ej: Insumos Médicos C.A." 
                                       style="border-top-right-radius: 8px; border-bottom-right-radius: 8px;" required>
                            </div>
                            @error('name')<span class="text-danger text-xs mt-1 d-block">{{ $message }}</span>@enderror
                        </div>
                        <div class="col-md-5 mb-3">
                            <label for="tax_id" class="text-xs font-weight-bold text-secondary text-uppercase mb-1 d-block">
                                RIF / ID Fiscal <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text bg-light" style="border-top-left-radius: 8px; border-bottom-left-radius: 8px;"><i class="fas fa-id-card text-muted"></i></span>
                                </div>
                                <input type="text" name="tax_id" id="tax_id" class="form-control @error('tax_id') is-invalid @enderror" 
                                       value="{{ old('tax_id') }}" placeholder="J-00000000-0" 
                                       style="border-top-right-radius: 8px; border-bottom-right-radius: 8px;" required>
                            </div>
                            @error('tax_id')<span class="text-danger text-xs mt-1 d-block">{{ $message }}</span>@enderror
                        </div>
                    </div>

                    <hr style="border-top: 1px solid #f3f4f6; margin: 1.5rem 0;">

                    {{-- Sección: Contacto --}}
                    <h6 class="font-weight-bold text-dark mb-3">
                        <i class="fas fa-address-book text-info mr-2"></i> Información de Contacto
                    </h6>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="contact_person" class="text-xs font-weight-bold text-secondary text-uppercase mb-1 d-block">
                                Persona de Contacto
                            </label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text bg-light" style="border-top-left-radius: 8px; border-bottom-left-radius: 8px;"><i class="fas fa-user text-muted"></i></span>
                                </div>
                                <input type="text" name="contact_person" id="contact_person" class="form-control @error('contact_person') is-invalid @enderror" 
                                       value="{{ old('contact_person') }}" placeholder="Nombre del contacto directo" 
                                       style="border-top-right-radius: 8px; border-bottom-right-radius: 8px;">
                            </div>
                            @error('contact_person')<span class="text-danger text-xs mt-1 d-block">{{ $message }}</span>@enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="representative_cedula" class="text-xs font-weight-bold text-secondary text-uppercase mb-1 d-block">
                                Cédula del Representante Legal
                            </label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text bg-light" style="border-top-left-radius: 8px; border-bottom-left-radius: 8px;"><i class="fas fa-id-badge text-muted"></i></span>
                                </div>
                                <input type="text" name="representative_cedula" id="representative_cedula" class="form-control @error('representative_cedula') is-invalid @enderror" 
                                       value="{{ old('representative_cedula') }}" placeholder="V-00000000" 
                                       style="border-top-right-radius: 8px; border-bottom-right-radius: 8px;">
                            </div>
                            @error('representative_cedula')<span class="text-danger text-xs mt-1 d-block">{{ $message }}</span>@enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="email" class="text-xs font-weight-bold text-secondary text-uppercase mb-1 d-block">
                                Email de Contacto
                            </label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text bg-light" style="border-top-left-radius: 8px; border-bottom-left-radius: 8px;"><i class="fas fa-envelope text-muted"></i></span>
                                </div>
                                <input type="email" name="email" id="email" class="form-control @error('email') is-invalid @enderror" 
                                       value="{{ old('email') }}" placeholder="proveedor@ejemplo.com" 
                                       style="border-top-right-radius: 8px; border-bottom-right-radius: 8px;">
                            </div>
                            @error('email')<span class="text-danger text-xs mt-1 d-block">{{ $message }}</span>@enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="phone" class="text-xs font-weight-bold text-secondary text-uppercase mb-1 d-block">
                                Teléfono Principal
                            </label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text bg-light" style="border-top-left-radius: 8px; border-bottom-left-radius: 8px;"><i class="fas fa-phone text-muted"></i></span>
                                </div>
                                <input type="text" name="phone" id="phone" class="form-control @error('phone') is-invalid @enderror" 
                                       value="{{ old('phone') }}" placeholder="+58 412-0000000" 
                                       style="border-top-right-radius: 8px; border-bottom-right-radius: 8px;">
                            </div>
                            @error('phone')<span class="text-danger text-xs mt-1 d-block">{{ $message }}</span>@enderror
                        </div>
                    </div>

                    <hr style="border-top: 1px solid #f3f4f6; margin: 1.5rem 0;">

                    {{-- Sección: Direcciones --}}
                    <h6 class="font-weight-bold text-dark mb-3">
                        <i class="fas fa-map-marked-alt text-info mr-2"></i> Direcciones
                    </h6>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="address" class="text-xs font-weight-bold text-secondary text-uppercase mb-1 d-block">
                                Dirección Física
                            </label>
                            <textarea name="address" id="address" class="form-control @error('address') is-invalid @enderror" 
                                      rows="3" placeholder="Dirección principal del establecimiento..." 
                                      style="border-radius: 8px;">{{ old('address') }}</textarea>
                            @error('address')<span class="text-danger text-xs mt-1 d-block">{{ $message }}</span>@enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="fiscal_address" class="text-xs font-weight-bold text-secondary text-uppercase mb-1 d-block">
                                Dirección Fiscal
                            </label>
                            <textarea name="fiscal_address" id="fiscal_address" class="form-control @error('fiscal_address') is-invalid @enderror" 
                                      rows="3" placeholder="Dirección legal para facturación..." 
                                      style="border-radius: 8px;">{{ old('fiscal_address') }}</textarea>
                            @error('fiscal_address')<span class="text-danger text-xs mt-1 d-block">{{ $message }}</span>@enderror
                        </div>
                    </div>

                    <hr style="border-top: 1px solid #f3f4f6; margin: 1.5rem 0;">

                    {{-- Sección: Configuración --}}
                    <h6 class="font-weight-bold text-dark mb-3">
                        <i class="fas fa-cog text-info mr-2"></i> Configuración del Sistema
                    </h6>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="is_active" class="text-xs font-weight-bold text-secondary text-uppercase mb-1 d-block">
                                Estado del Proveedor
                            </label>
                            <select name="is_active" id="is_active" class="form-control" style="border-radius: 8px;">
                                <option value="1" {{ old('is_active', '1') == '1' ? 'selected' : '' }}>Activo</option>
                                <option value="0" {{ old('is_active') == '0' ? 'selected' : '' }}>Inactivo</option>
                            </select>
                            <small class="text-muted d-block mt-1">Los proveedores inactivos no aparecerán en las Órdenes de Compra.</small>
                        </div>
                    </div>

                    <hr style="border-top: 1px solid #e5e7eb; margin: 1.5rem 0;">

                    <div class="d-flex justify-content-between align-items-center">
                        <a href="{{ route('admin.suppliers.index') }}" class="btn btn-outline-secondary font-weight-bold px-4 py-2" style="border-radius: 8px;">
                            Cancelar
                        </a>
                        <button type="submit" class="btn btn-primary font-weight-bold px-4 py-2" style="border-radius: 8px;">
                            <i class="fas fa-save mr-1"></i> Guardar Proveedor
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@stop
