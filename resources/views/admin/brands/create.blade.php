@extends('adminlte::page')

@section('title', 'Crear Marca')

@section('content_header')
    <h1>Crear Nueva Marca</h1>
@stop

@section('content')
    @include('admin.partials.session-messages')

    <div class="row">
        <div class="col-12">
            <div class="card" style="border-left: 4px solid #10b981;">
                <div class="card-header" style="background: linear-gradient(135deg, #10b981 0%, #34d399 100%);">
                    <h3 class="card-title text-white">
                        <i class="fas fa-star"></i> Datos de la Marca
                    </h3>
                </div>
                <form action="{{ route('admin.brands.store') }}" method="POST">
                    @csrf
                    <div class="card-body">

                        {{-- Información Principal --}}
                        <div class="card" style="border-left: 4px solid #10b981;">
                            <div class="card-header" style="background: linear-gradient(135deg, #10b981 0%, #34d399 100%);">
                                <h3 class="card-title text-white">
                                    <i class="fas fa-info-circle"></i> Información Principal
                                </h3>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-12 col-md-8">
                                        <div class="form-group">
                                            <label for="name">Nombre <span class="text-danger">*</span></label>
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text bg-success text-white"><i class="fas fa-tag"></i></span>
                                                </div>
                                                <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" placeholder="Ej: Siemens, Baxter, Dell" required>
                                            </div>
                                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                            <small class="form-text text-muted">Nombre de la marca tal como se mostrará en los productos.</small>
                                        </div>
                                    </div>
                                    <div class="col-12 col-md-4">
                                        <div class="form-group">
                                            <label>Vista Previa</label>
                                            <div class="p-3 border rounded bg-light text-center">
                                                <span id="previewBadge" class="badge badge-success" style="font-size: 1rem; padding: 0.5rem 1rem;">
                                                    <i class="fas fa-star"></i> <span id="previewText">Nombre</span>
                                                </span>
                                                <small class="d-block text-muted mt-2">Así se verá la marca</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Sitio Web --}}
                        <div class="card" style="border-left: 4px solid #06b6d4;">
                            <div class="card-header" style="background: linear-gradient(135deg, #06b6d4 0%, #22d3ee 100%);">
                                <h3 class="card-title text-white">
                                    <i class="fas fa-globe"></i> Sitio Web
                                </h3>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-12 col-md-8">
                                        <div class="form-group mb-0">
                                            <label for="website">Sitio Web <span class="text-muted">(Opcional)</span></label>
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text bg-info text-white"><i class="fas fa-link"></i></span>
                                                </div>
                                                <input type="url" name="website" id="website" class="form-control @error('website') is-invalid @enderror" value="{{ old('website') }}" placeholder="Ej: https://www.marca.com">
                                            </div>
                                            @error('website')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                            <small class="form-text text-muted">URL del sitio oficial de la marca para referencia.</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Información del Registro --}}
                        <div class="card mb-0" style="border-left: 4px solid #6c757d;">
                            <div class="card-header" style="background: linear-gradient(135deg, #6c757d 0%, #8a939d 100%);">
                                <h3 class="card-title text-white">
                                    <i class="fas fa-clipboard-list"></i> Información del Registro
                                </h3>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-12 col-md-4">
                                        <p class="mb-1"><strong><i class="fas fa-user"></i> Creado por:</strong></p>
                                        <p class="text-muted">{{ auth()->user()->name }}</p>
                                    </div>
                                    <div class="col-12 col-md-4">
                                        <p class="mb-1"><strong><i class="fas fa-calendar"></i> Fecha:</strong></p>
                                        <p class="text-muted">{{ now()->format('d/m/Y H:i') }}</p>
                                    </div>
                                    <div class="col-12 col-md-4">
                                        <p class="mb-1"><strong><i class="fas fa-check-circle"></i> Estado:</strong></p>
                                        <span class="badge badge-success"><i class="fas fa-check"></i> Activo</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-save"></i> Guardar Marca
                        </button>
                        <a href="{{ route('admin.brands.index') }}" class="btn btn-default">
                            <i class="fas fa-arrow-left"></i> Volver al Listado
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
@stop

@section('js')
<script>
    document.getElementById('name').addEventListener('keyup', function() {
        document.getElementById('previewText').textContent = this.value || 'Nombre';
    });
</script>
@stop
