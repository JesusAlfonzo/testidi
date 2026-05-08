@extends('adminlte::page')

@section('title', 'Maestros | Crear Unidad')

@section('content_header')
    <h1 class="m-0 text-dark">Crear Nueva Unidad de Medida</h1>
@stop

@section('content')
    @include('admin.partials.session-messages')

    <div class="row">
        <div class="col-12">
            <div class="card" style="border-left: 4px solid #06b6d4;">
                <div class="card-header" style="background: linear-gradient(135deg, #06b6d4 0%, #22d3ee 100%);">
                    <h3 class="card-title text-white">
                        <i class="fas fa-ruler"></i> Detalle de la Unidad
                    </h3>
                </div>

                <form action="{{ route('admin.units.store') }}" method="POST">
                    @csrf
                    <div class="card-body">

                        {{-- Información Principal --}}
                        <div class="card" style="border-left: 4px solid #06b6d4;">
                            <div class="card-header" style="background: linear-gradient(135deg, #06b6d4 0%, #22d3ee 100%);">
                                <h3 class="card-title text-white">
                                    <i class="fas fa-info-circle"></i> Información Principal
                                </h3>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-12 col-md-6">
                                        <div class="form-group">
                                            <label for="name">Nombre Completo <span class="text-danger">*</span></label>
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text bg-info text-white"><i class="fas fa-weight-hanging"></i></span>
                                                </div>
                                                <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" placeholder="Ej: Kilogramo, Litro, Unidad" required>
                                            </div>
                                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                            <small class="form-text text-muted">Nombre completo de la unidad de medida.</small>
                                        </div>
                                    </div>
                                    <div class="col-12 col-md-4">
                                        <div class="form-group">
                                            <label for="abbreviation">Abreviatura <span class="text-danger">*</span></label>
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text bg-info text-white"><i class="fas fa-compress-alt"></i></span>
                                                </div>
                                                <input type="text" name="abbreviation" id="abbreviation" class="form-control @error('abbreviation') is-invalid @enderror" value="{{ old('abbreviation') }}" placeholder="Ej: Kg, Lt, U" required>
                                            </div>
                                            @error('abbreviation')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                            <small class="form-text text-muted">Versión corta para mostrar en listados.</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Vista Previa --}}
                        <div class="card" style="border-left: 4px solid #8b5cf6;">
                            <div class="card-header" style="background: linear-gradient(135deg, #8b5cf6 0%, #a78bfa 100%);">
                                <h3 class="card-title text-white">
                                    <i class="fas fa-eye"></i> Vista Previa
                                </h3>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-12">
                                        <div class="p-3 border rounded bg-light text-center">
                                            <span id="previewBadge" class="badge badge-info" style="font-size: 1.2rem; padding: 0.5rem 1.2rem;">
                                                <i class="fas fa-ruler"></i> <span id="previewAbbr">Abreviatura</span>
                                            </span>
                                            <small class="d-block text-muted mt-2">Así se verá la abreviatura en los listados del sistema.</small>
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
                        <button type="submit" class="btn btn-info">
                            <i class="fas fa-save"></i> Guardar Unidad
                        </button>
                        <a href="{{ route('admin.units.index') }}" class="btn btn-default">
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
    document.getElementById('abbreviation').addEventListener('keyup', function() {
        document.getElementById('previewAbbr').textContent = this.value || 'Abreviatura';
    });
</script>
@stop
