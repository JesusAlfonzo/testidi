# Base de Datos - SGCI-IDI

## 1. Modelos y Tablas

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
| StockInItem | stock_in_items | Items de entradas |
| InventoryRequest | requests | Solicitudes de salida |
| RequestItem | request_items | Items de solicitudes |

### Compras

| Modelo | Tabla | Descripción |
|--------|-------|-------------|
| RequestForQuotation | request_for_quotations | RFQ (solicitudes de cotización) |
| RfqItem | rfq_items | Items de RFQ |
| PurchaseQuote | purchase_quotes | Cotizaciones de proveedores |
| PurchaseQuoteItem | purchase_quote_items | Items de cotización |
| PurchaseOrder | purchase_orders | Órdenes de compra |
| PurchaseOrderItem | purchase_order_items | Items de orden de compra |

### Seguridad

| Modelo | Tabla | Descripción |
|--------|-------|-------------|
| User | users | Usuarios del sistema |
| Role | roles | Roles (Spatie) |
| Permission | permissions | Permisos (Spatie) |
| ActivityLog | activity_logs | Auditoría (Spatie) |

---

## 2. Relaciones entre Modelos

```
User (1) ─── (N) Role
User (1) ─── (N) Product
User (1) ─── (N) Category
User (1) ─── (N) Supplier

Category (1) ─── (N) Product
Unit (1) ─── (N) Product
Location (1) ─── (N) Product
Brand (1) ─── (N) Product
Supplier (1) ─── (N) Product
Supplier (1) ─── (N) PurchaseOrder
Supplier (1) ─── (N) RFQ

Product (N) ─── (N) Kit (a través de KitItem)
StockIn (1) ─── (N) StockInItem
InventoryRequest (1) ─── (N) RequestItem
PurchaseOrder (1) ─── (N) PurchaseOrderItem
```

---

## 3. Campos Comunes

### Timestamps
Todos los modelos incluyen:
- `created_at` - Fecha de creación
- `updated_at` - Fecha de última modificación

### Soft Deletes
Modelos con eliminación suave:
- Product
- Category
- Unit
- Location
- Brand
- Supplier
- Kit
- PurchaseOrder
- RFQ

---

*Última actualización: 2026*
