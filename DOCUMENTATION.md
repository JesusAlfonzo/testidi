# SGCI-IDI - Sistema de Gestión de Compras e Inventario

## 1. Información General

**Tipo de Aplicación:** ERP de Gestión de Compras e Inventario  
**Framework:** Laravel 12  
**Frontend:** AdminLTE 3 + Bootstrap 5 + TailwindCSS 4  
**Base de datos:** SQLite (desarrollo) / MySQL compatible  
**Testing:** Pest PHP  

---

## 2. Estructura del Proyecto

```
app/
├── Events/                    # Eventos del sistema
├── Exports/                  # Exportaciones (Excel, PDF)
├── Http/
│   ├── Controllers/         # Controladores MVC
│   ├── Requests/           # Validaciones de formularios
├── Listeners/               # Listeners de eventos
├── Models/                  # Modelos Eloquent
├── Policies/                # Políticas de autorización
├── Providers/              # Proveedores de servicios
├── Services/                # Servicios de negocio
```

---

## 3. Modelos y Tablas

### Módulos Maestros
| Modelo | Tabla | Descripción |
|--------|-------|-------------|
| Category | categories | Categorías de productos |
| Unit | units | Unidades de medida |
| Location | locations | Ubicaciones/almacenes |
| Brand | brands | Marcas de productos |
| Supplier | suppliers | Proveedores |

### Inventario
| Modelo | Tabla | Descripción |
|--------|-------|-------------|
| Product | products | Productos con stock, costos, precios |
| Kit | kits | Paquetes combos de productos |
| KitItem | kit_items | Relación N:M kit-productos |
| StockIn | stock_ins | Entradas de inventario |
| InventoryRequest | requests | Solicitudes de salida |
| RequestItem | request_items | Items de solicitudes |

### Compras
| Modelo | Tabla | Descripción |
|--------|-------|-------------|
| RequestForQuotation | request_for_quotations | RFQ (solicitudes de cotización) |
| RfqItem | rfq_items | Items de RFQ |
| PurchaseOrder | purchase_orders | Órdenes de compra |
| PurchaseOrderItem | purchase_order_items | Items de orden de compra |

### Seguridad
| Modelo | Tabla | Descripción |
|--------|-------|-------------|
| User | users | Usuarios del sistema |
| Role | roles | Roles (Spatie) |
| Permission | permissions | Permisos (Spatie) |
| ActivityLog | activity_log | Auditoría (Spatie) |

---

## 4. Flujo de Compras

```
1. RFQ (Request for Quotation)
   ├── Crear solicitud de cotización
   ├── Enviar a proveedores
   └── Estado: draft → sent → closed

2. Órden de Compra (PurchaseOrder)
   ├── Crear orden de compra
   ├── Emitir orden
   ├── Recepción de mercancía
   └── Estado: draft → issued → completed / cancelled

3. Recepción (StockIn)
   └── Ingresar mercancía al inventario
```
1. RFQ (Request for Quotation)
   ├── Crear solicitud de cotización
   ├── Enviar a proveedores
   └── Estado: draft → sent → closed

2. Órden de Compra (PurchaseOrder)
   ├── Generar orden desde cotización aprobada
   ├── Emitir orden
   ├── Receptionar merchandise
   └── Estado: draft → issued → completed / cancelled

4. Recepción (StockIn)
   └── Ingresar mercancía al inventario
```

---

## 5. Flujo de Inventario

```
Entrada (StockIn)
    ↓
Inventario (Product.stock + quantity)
    ↓
Solicitud de Salida (InventoryRequest)
    ↓
Aprobación → Descuenta stock (Product.stock - quantity)
    ↓
Kardex (historial de movimientos)
```

---

## 6. Rutas Principales

### Autenticación
```
/login          → Login
/register       → Registro
/password/reset → Reset password
```

### Panel Admin (/admin)
```
/admin/users           → Gestión de usuarios
/admin/roles          → Gestión de roles
/admin/categories     → Categorías
/admin/units          → Unidades
/admin/locations      → Ubicaciones
/admin/brands        → Marcas
/admin/suppliers     → Proveedores
/admin/products      → Productos
/admin/kits         → Kits
/admin/rfq          → Solicitudes de Cotización
/admin/purchaseOrders → Órdenes de Compra
/admin/stock-in     → Entradas de Stock
/admin/requests     → Solicitudes de Salida
/admin/audit-logs  → Log de Auditoría
/admin/reports/*    → Reportes
```

---

## 7. Permisos del Sistema

### Permisos de Maestros
- `categorias_ver`, `categorias_crear`, `categorias_editar`, `categorias_eliminar`
- `unidades_ver`, `unidades_crear`, `unidades_editar`, `unidades_eliminar`
- `ubicaciones_ver`, `ubicaciones_crear`, `ubicaciones_editar`, `ubicaciones_eliminar`
- `marcas_ver`, `marcas_crear`, `marcas_editar`, `marcas_eliminar`
- `proveedores_ver`, `proveedores_crear`, `proveedores_editar`, `proveedores_eliminar`

### Permisos de Inventario
- `productos_ver`, `productos_crear`, `productos_editar`, `productos_eliminar`
- `kits_ver`, `kits_crear`, `kits_editar`, `kits_eliminar`
- `entradas_ver`, `entradas_crear`, `entradas_editar`, `entradas_eliminar`
- `solicitudes_ver`, `solicitudes_crear`, `solicitudes_aprobar`

### Permisos de Compras
- `rfq_ver`, `rfq_crear`, `rfq_editar`, `rfq_eliminar`, `rfq_enviar`
- `cotizaciones_ver`, `cotizaciones_crear`, `cotizaciones_editar`, `cotizaciones_eliminar`, `cotizaciones_aprobar`, `cotizaciones_rechazar`
- `ordenes_compra_ver`, `ordenes_compra_crear`, `ordenes_compra_editar`, `ordenes_compra_eliminar`, `ordenes_compra_aprobar`, `ordenes_compra_anular`

### Permisos de Reportes
- `reportes_ver`, `reportes_stock`, `reportes_kardex`, `reportes_movimientos`
- `auditoria_ver`

---

## 8. Servicios (app/Services)

### CacheService
Optimiza consultas frecuentes con caché:

**Lectura:**
- `categories()`, `units()`, `locations()`, `brands()`, `suppliers()` - Listas de maestros
- `productsList()` - Lista de productos
- `productStock($productId)` - Stock de un producto
- `inventorySummary()` - Resumen de inventario
- `purchasesSummary()` - Resumen de compras
- `requestsSummary()` - Resumen de solicitudes

**Invalidación:**
- `invalidateCategories()`, `invalidateUnits()`, etc.
- `invalidateProductStock($productId)`
- `invalidateAll()`

### KardexService
Genera reporte de movimientos (Kardex) para un producto:
- Entradas (StockIn)
- Salidas (InventoryRequest aprobadas)
- Cálculo de saldo acumulativo

---

## 9. Events y Listeners

### Event: StockUpdated
Se dispara cuando hay movimientos de stock:

```php
event(new StockUpdated(
    product: $product,
    quantity: $quantity,
    type: 'in' | 'out' | 'adjustment',
    referenceId: $id,
    referenceType: StockIn::class | RequestModel::class,
    notes: $notes
));
```

**Listener:** StockUpdatedListener
- Registra en Spatie Activitylog
- Guarda: quantity, type, stock_before, stock_after, reference_id

---

## 10. Policies de Autorización

Cada modelo tiene su Policy:

| Policy | Métodos |
|--------|---------|
| ProductPolicy | viewAny, view, create, update, delete |
| KitPolicy | viewAny, view, create, update, delete |
| CategoryPolicy | viewAny, view, create, update, delete |
| UnitPolicy | viewAny, view, create, update, delete |
| LocationPolicy | viewAny, view, create, update, delete |
| BrandPolicy | viewAny, view, create, update, delete |
| SupplierPolicy | viewAny, view, create, update, delete |
| StockInPolicy | viewAny, view, create, update, delete |
| InventoryRequestPolicy | viewAny, view, create, update, delete, process |
| RequestForQuotationPolicy | viewAny, view, create, update, delete, markAsSent, markAsClosed, cancel |
| PurchaseOrderPolicy | viewAny, view, create, update, delete, issue, complete, cancel |

---

## 11. Tests

### Flujo de Compras (tests/Feature/Compras/)
- **FlujoCompletoTest**: RFQ → OC → Recepción
- **PermisosTest**: Validación de permisos por rol

### Roles Predefinidos
- **Superadmin**: Acceso total
- **Solicitante**: Solo solicitudes
- **Logistica**: Inventario y compras (sin usuarios)
- **Supervisor**: Reportes, sin usuarios/roles

---

## 12. Dependencias

### composer.json
| Paquete | Versión | Propósito |
|---------|---------|-----------|
| laravel/framework | ^12.0 | Core del framework |
| jeroennoten/laravel-adminlte | ^3.15 | Panel administrativo |
| spatie/laravel-permission | ^6.23 | Gestión de roles y permisos |
| spatie/laravel-activitylog | ^4.10 | Log de auditoría |
| barryvdh/laravel-dompdf | ^3.1 | Generación de PDFs |
| maatwebsite/excel | ^1.1 | Exportación a Excel |
| laravel/ui | ^4.6 | Autenticación UI |

### package.json
- Bootstrap 5.2.3
- TailwindCSS 4
- Vite 7
- Axios
- Sass
- Select2

---

## 13. Comandos Útiles

```bash
# Instalación
composer install
npm install

# Desarrollo
php artisan serve
npm run dev

# Migraciones
php artisan migrate
php artisan migrate:fresh --seed

# Limpiar caché
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Listar rutas
php artisan route:list

# Tests
php artisan test
```

---

## 14. Configuración

### principales archivos
- `.env` - Variables de entorno
- `config/database.php` - Configuración de DB
- `config/adminlte.php` - Configuración del panel admin
- `config/auth.php` - Configuración de autenticación
- `config/permission.php` - Permisos de Spatie

---

## 15. Mejores Prácticas Implementadas

1. **Separación de responsabilidades** - Lógica en Servicios
2. **Autorización granular** - Policies para cada modelo
3. **Optimización** - CacheService para consultas frecuentes
4. **Auditoría** - Eventos para tracking automático
5. **Transacciones DB** - Integridad en flujos de compra
6. **DRY** - Código reutilizable
