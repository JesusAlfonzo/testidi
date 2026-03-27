@extends('adminlte::page')

@section('title', 'Maestros | Editar Unidad')

@section('content_header')
    <h1 class="m-0 text-dark">Editar Unidad: <strong>{{ $unit->name }}</strong> ({{ $unit->abbreviation }})</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card" style="border-left: 4px solid #f59e0b;">
                <div class="card-header" style="background: linear-gradient(135deg, #f59e0b 0%, #fbbf24 100%);">
                    <h3 class="card-title text-white">
                        <i class="fas fa-edit"></i> Actualizar Datos de Unidad
                    </h3>
                </div>

                <form action="{{ route('admin.units.update', $unit) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="card-body">
                        <div class="row">
                            <div class="col-12 col-md-6">
                                <div class="form-group">
                                    <label for="name">Nombre Completo <span class="text-danger">*</span></label>
                                    <div class="input-group input-group-sm">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text bg-warning text-dark"><i class="fas fa-weight-hanging"></i></span>
                                        </div>
                                        <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $unit->name) }}" required>
                                    </div>
                                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>
                            <div class="col-12 col-md-4">
                                <div class="form-group">
                                    <label for="abbreviation">Abreviatura <span class="text-danger">*</span></label>
                                    <div class="input-group input-group-sm">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text bg-warning text-dark"><i class="fas fa-compress-alt"></i></span>
                                        </div>
                                        <input type="text" name="abbreviation" id="abbreviation" class="form-control @error('abbreviation') is-invalid @enderror" value="{{ old('abbreviation', $unit->abbreviation) }}" required>
                                    </div>
                                    @error('abbreviation')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card-footer">
                        <button type="submit" class="btn btn-warning text-dark">
                            <i class="fas fa-sync-alt"></i> Actualizar Unidad
                        </button>
                        <a href="{{ route('admin.units.index') }}" class="btn btn-default">
                            <i class="fas fa-arrow-left"></i> Cancelar
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
@stop
