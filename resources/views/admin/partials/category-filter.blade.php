{{--
    Partial: category-filter.blade.php
    Uso: @include('admin.partials.category-filter', ['filterTarget' => 'product-selector-id'])
    
    Variables requeridas en el contexto padre:
        - $categories : Colección de Category (id, name)
    
    Genera:
        - Un <select> con id="categoryFilter" y las categorías disponibles.
        - Emite el evento JS global window.sgciCategoryFilterReady para que
          cada formulario adjunte su propia lógica de filtrado.
--}}
<div class="card shadow-sm border-0 mb-3" style="border-radius: 10px; border-left: 3px solid #6366f1 !important;">
    <div class="card-body py-2 px-3">
        <div class="d-flex align-items-center flex-wrap" style="gap: 0.75rem;">
            <label for="categoryFilter" class="mb-0 text-xs font-weight-bold text-secondary text-uppercase text-nowrap" style="letter-spacing: 0.5px;">
                <i class="fas fa-filter text-indigo mr-1" style="color: #6366f1;"></i> Filtrar por Categoría:
            </label>
            <select id="categoryFilter" class="form-control form-control-sm" style="max-width: 280px; border-radius: 6px;">
                <option value="">— Todas las categorías —</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                @endforeach
            </select>
            <span class="text-xs text-muted ml-1" id="categoryFilterHint">
                <i class="fas fa-info-circle"></i>
                Seleccione una categoría para habilitar el selector de productos.
            </span>
        </div>
    </div>
</div>
