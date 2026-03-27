# Flujo de Inventario - SGCI-IDI

## 1. Entrada de Inventario (Stock In)

### Descripción
El proceso de entrada de inventario registra la recepción de productos en el sistema, ya sea por compras, donaciones, ajustes o transferencias.

### Flujo
```
1. Orden de Compra (aprobada)
         ↓
2. Recepción de mercancía
         ↓
3. Registrar entrada (Stock In)
         ↓
4. Actualizar stock de productos
```

### Estados
| Campo | Descripción |
|-------|-------------|
| entry_date | Fecha de ingreso |
| reason | Razón del ingreso (compra, donación, ajuste) |
| document_type | Tipo de documento (factura, guía) |
| document_number | Número de documento |
| supplier_id | Proveedor (opcional) |

### Permisos Requeridos
- `entradas_ver` - Ver entradas
- `entradas_crear` - Crear entradas
- `entradas_editar` - Editar entradas
- `entradas_eliminar` - Eliminar entradas

---

## 2. Solicitudes de Salida

### Descripción
Las solicitudes de salida permiten a los usuarios solicitar productos del inventario para uso interno.

### Flujo
```
1. Crear solicitud
         ↓
2. Aprobación (Supervisor/Admin)
         ↓
3. Entrega de productos
         ↓
4. Actualizar stock (salida)
```

### Estados
| Estado | Descripción | Color |
|--------|-------------|-------|
| pending | Pendiente de aprobación | Amarillo |
| approved | Aprobada | Verde |
| rejected | Rechazada | Rojo |
| fulfilled | Completada | Azul |

### Permisos Requeridos
- `solicitudes_ver` - Ver solicitudes
- `solicitudes_crear` - Crear solicitudes
- `solicitudes_aprobar` - Aprobar/rechazar solicitudes

---

## 3. Gestión de Productos

### Descripción
Los productos son los elementos básicos del inventario. Cada producto tiene información de categorización, stock, costos y precios.

### Campos Principales
| Campo | Descripción |
|-------|-------------|
| name | Nombre del producto |
| code | Código/SKU único |
| category_id | Categoría |
| unit_id | Unidad de medida |
| brand_id | Marca (opcional) |
| location_id | Ubicación de almacenamiento |
| stock | Cantidad actual |
| min_stock | Stock mínimo para alerta |
| cost | Costo unitario sin IVA |
| price | Precio de venta sugerido |
| is_active | Estado activo/inactivo |

### Permisos Requeridos
- `productos_ver` - Ver productos
- `productos_crear` - Crear productos
- `productos_editar` - Editar productos
- `productos_eliminar` - Eliminar productos

---

## 4. Kits de Productos

### Descripción
Los kits son combinaciones de productos que se venden o gestionan juntos como una unidad.

### Estructura
```
Kit
├── name - Nombre del kit
├── description - Descripción
├── unit_price - Precio unitario
├── is_active - Estado
└── components (N:N)
    ├── product_id
    └── quantity_required - Cantidad necesaria
```

### Permisos Requeridos
- `kits_ver` - Ver kits
- `kits_crear` - Crear kits
- `kits_editar` - Editar kits
- `kits_eliminar` - Eliminar kits

---

## 5. Módulos Maestros

### Descripción
Los módulos maestros contienen la información base del sistema que se utiliza en otros módulos.

| Módulo | Descripción |
|--------|-------------|
| Categorías | Clasificación de productos |
| Unidades | Unidades de medida (kg, lt, unid) |
| Ubicaciones | Lugares de almacenamiento |
| Marcas | Marcas de productos |
| Proveedores | Empresas proveedoras |

### Permisos Comunes
- `{modulo}_ver` - Ver
- `{modulo}_crear` - Crear
- `{modulo}_editar` - Editar
- `{modulo}_eliminar` - Eliminar

---

*Última actualización: 2026*
