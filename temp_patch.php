<?php
$content = file_get_contents('resources/views/admin/quotations/create.blade.php');

// Agregar botón + después del select de proveedor
$old = '<div class="form-group">
                                        <label for="supplier_id">Seleccionar Proveedor (*)</label>
                                        <select name="supplier_id" id="supplier_id" class="form-control select2" data-placeholder="Buscar proveedor...">
                                            <option value="">Seleccione...</option>
                                            @foreach($suppliers as $supplier)
                                                <option value="{{ $supplier->id }}" {{ old('supplier_id') == $supplier->id ? 'selected' : '' }}>
                                                    {{ $supplier->name }} | {{ $supplier->email }} | {{ $supplier->phone ?? 'Sin teléfono' }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>';

$new = '<div class="form-group">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <label for="supplier_id">Seleccionar Proveedor (*)</label>
                                            <button type="button" class="btn btn-sm btn-primary" id="addSupplierBtn">
                                                <i class="fas fa-plus"></i> Nuevo Proveedor
                                            </button>
                                        </div>
                                        <select name="supplier_id" id="supplier_id" class="form-control select2" data-placeholder="Buscar proveedor...">
                                            <option value="">Seleccione...</option>
                                            @foreach($suppliers as $supplier)
                                                <option value="{{ $supplier->id }}" {{ old('supplier_id') == $supplier->id ? 'selected' : '' }}>
                                                    {{ $supplier->name }} | {{ $supplier->email }} | {{ $supplier->phone ?? 'Sin teléfono' }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>';

$content = str_replace($old, $new, $content);

// Ahora agregar el modal al final, antes de @stop
$modalSupplier = '

    <!-- Modal para crear Proveedor rápido -->
    <div class="modal fade" id="supplierModal" tabindex="-1" role="dialog" aria-labelledby="supplierModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header" style="background: linear-gradient(135deg, #f59e0b 0%, #fbbf24 100%);">
                    <h5 class="modal-title text-white" id="supplierModalLabel"><i class="fas fa-building"></i> Crear Nuevo Proveedor</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="supplierForm">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-12 col-md-6">
                                <div class="form-group">
                                    <label for="supplier_name">Nombre (*)</label>
                                    <input type="text" name="name" id="supplier_name" class="form-control" required>
                                </div>
                            </div>
                            <div class="col-12 col-md-6">
                                <div class="form-group">
                                    <label for="supplier_rif">RIF (*)</label>
                                    <input type="text" name="rif" id="supplier_rif" class="form-control" required>
                                    <small class="text-danger" id="supplierRifError" style="display:none;">El RIF ya existe</small>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12 col-md-6">
                                <div class="form-group">
                                    <label for="supplier_email">Email</label>
                                    <input type="email" name="email" id="supplier_email" class="form-control">
                                </div>
                            </div>
                            <div class="col-12 col-md-6">
                                <div class="form-group">
                                    <label for="supplier_phone">Teléfono</label>
                                    <input type="text" name="phone" id="supplier_phone" class="form-control">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12">
                                <div class="form-group">
                                    <label for="supplier_address">Dirección</label>
                                    <textarea name="address" id="supplier_address" rows="2" class="form-control"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary" id="saveSupplierBtn">
                            <i class="fas fa-save"></i> Guardar Proveedor
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@stop';

$content = str_replace('@stop', $modalSupplier, $content);

file_put_contents('resources/views/admin/quotations/create.blade.php', $content);
echo "Done";
