# Flujos de Trabajo del Sistema

## Tabla de Contenidos

1. [Flujo de Solicitud de Cotización (RFQ)](#1-flujo-de-solicitud-de-cotización-rfq)
2. [Flujo de Cotización](#2-flujo-de-cotización)
3. [Flujo de Orden de Compra](#3-flujo-de-orden-de-compra)
4. [Flujo de Entrada de Inventario (Stock In)](#4-flujo-de-entrada-de-inventario-stock-in)
5. [Flujo de Solicitud de Inventario](#5-flujo-de-solicitud-de-inventario)
6. [Flujo de Kits de Productos](#6-flujo-de-kits-de-productos)
7. [Gestión de Productos](#7-gestión-de-productos)
8. [Gestión de Proveedores](#8-gestión-de-proveedores)

---

## 1. Flujo de Solicitud de Cotización (RFQ)

### Descripción General

El flujo de Solicitud de Cotización (RFQ - Request For Quotation) es el punto de partida del proceso de compras. Permite a los usuarios crear solicitudes formales para obtener cotizaciones de productos específicos de los proveedores.

### Estados del RFQ

| Estado | Descripción | Color en UI |
|--------|-------------|-------------|
| `draft` | Borrador - Creado pero no enviado | Gris |
| `sent` | Enviada - Enviada a proveedores | Azul |
| `closed` | Cerrada - Proceso completado | Verde |
| `cancelled` | Cancelada - Anulada por el usuario | Rojo |

### Casos de Uso

#### 1.1 Crear RFQ en Borrador

- **Actor**: Usuario con permiso `rfq_crear`
- **Descripción**: Crear una solicitud de cotización sin enviarla inmediatamente
- **Pasos**:
  1. Acceder a "Solicitudes de Cotización" > "Nuevo"
  2. Ingresar título y descripción
  3. Establecer fecha requerida y plazo de entrega (opcionales)
  4. Agregar productos con cantidades deseadas
  5. Guardar como borrador

#### 1.2 Editar RFQ en Borrador

- **Actor**: Usuario con permiso `rfq_editar`
- **Condición**: Solo RFQs en estado `draft` pueden editase
- **Descripción**: Modificar los datos de un RFQ antes de enviarlo
- **Pasos**:
  1. Seleccionar RFQ en estado borrador
  2. Modificar título, descripción, fechas o productos
  3. Guardar cambios

#### 1.3 Enviar RFQ

- **Actor**: Usuario con permiso `rfq_enviar`
- **Condición**: Solo RFQs en estado `draft` pueden enviarse
- **Descripción**: Marcar la solicitud como enviada a proveedores
- **Pasos**:
  1. Seleccionar RFQ en borrador
  2. Hacer clic en "Enviar"
  3. El estado cambia a `sent`

#### 1.4 Cerrar RFQ

- **Actor**: Usuario con permiso `rfq_enviar`
- **Condición**: Solo RFQs en estado `sent` pueden cerrarse
- **Descripción**: Cerrar el proceso de cotización cuando ya se tienen suficientes cotizaciones
- **Pasos**:
  1. Seleccionar RFQ enviado
  2. Hacer clic en "Cerrar"
  3. El estado cambia a `closed`

#### 1.5 Cancelar RFQ

- **Actor**: Usuario con permiso `rfq_enviar`
- **Condición**: Solo RFQs en estado `draft` o `sent` pueden cancelarse
- **Descripción**: Anular una solicitud de cotización
- **Pasos**:
  1. Seleccionar RFQ en borrador o enviado
  2. Hacer clic en "Cancelar"
  3. El estado cambia a `cancelled`

#### 1.6 Eliminar RFQ

- **Actor**: Usuario con permiso `rfq_eliminar`
- **Condición**: Solo RFQs en estado `draft` pueden eliminarse
- **Descripción**: Eliminación permanente de un RFQ
- **Pasos**:
  1. Seleccionar RFQ en borrador
  2. Hacer clic en "Eliminar"
  3. Confirmar eliminación

#### 1.7 Generar PDF de RFQ

- **Actor**: Usuario con permiso `rfq_ver`
- **Descripción**: Exportar el RFQ a formato PDF para compartir con proveedores
- **Pasos**:
  1. Seleccionar cualquier RFQ
  2. Hacer clic en "PDF"
  3. Se descarga el documento PDF

### Permisos Requeridos

| Permiso | Descripción |
|---------|-------------|
| `rfq_ver` | Ver lista y detalles de RFQs |
| `rfq_crear` | Crear nuevos RFQs |
| `rfq_editar` | Editar RFQs en estado borrador |
| `rfq_eliminar` | Eliminar RFQs |
| `rfq_enviar` | Enviar, cerrar y cancelar RFQs |

---

## 2. Flujo de Cotización

### Descripción General

El flujo de Cotización permite registrar las respuestas de los proveedores a las solicitudes de cotización (RFQ). Este flujo incluye la gestión de proveedores registrados y temporales.

### Estados de la Cotización

| Estado | Descripción | Color en UI |
|--------|-------------|-------------|
| `pending` | Pendiente - Esperando revisión | Amarillo |
| `selected` | Seleccionada - Elegida para revisión | Azul |
| `approved` | Aprobada - Aprobada para generar OC | Verde |
| `rejected` | Rechazada - No aceptada | Rojo |
| `converted` | Convertida - Ya generó una OC | Púrpura |

### Casos de Uso

#### 2.1 Crear Cotización desde RFQ

- **Actor**: Usuario con permiso `cotizaciones_crear`
- **Descripción**: Registrar una cotización来自 un proveedor específico en respuesta a un RFQ
- **Pasos**:
  1. Acceder a "Cotizaciones" > "Nueva"
  2. Seleccionar RFQ relacionado (opcional)
  3. Elegir tipo de proveedor: registrado o temporal
  4. Si es proveedor registrado: seleccionar de la lista
  5. Si es proveedor temporal: ingresar nombre, email, teléfono
  6. Ingresar fecha de emisión, fecha de validez, fecha de entrega
  7. Seleccionar moneda y tipo de cambio
  8. Agregar productos con cantidad y costo unitario
  9. Guardar cotización

#### 2.2 Crear Cotización sin RFQ

- **Actor**: Usuario con permiso `cotizaciones_crear`
- **Descripción**: Crear una cotización directa sin RFQ previo
- **Pasos**:
  1. Acceder a "Cotizaciones" > "Nueva"
  2. Dejar el campo RFQ vacío
  3. Continuar con los pasos de creación normal

#### 2.3 Editar Cotización

- **Actor**: Usuario con permiso `cotizaciones_editar`
- **Condición**: Solo cotizaciones en estado `pending` pueden editase
- **Descripción**: Modificar una cotización antes de su selección
- **Pasos**:
  1. Seleccionar cotización pendiente
  2. Modificar los datos necesarios
  3. Guardar cambios

#### 2.4 Seleccionar Cotización

- **Actor**: Usuario con permiso `cotizaciones_aprobar`
- **Condición**: Solo cotizaciones en estado `pending` pueden seleccionarse
- **Descripción**: Elegir una cotización para revisión administrativa
- **Pasos**:
  1. Seleccionar cotización pendiente
  2. Hacer clic en "Seleccionar"
  3. El estado cambia a `selected`

#### 2.5 Aprobar Cotización

- **Actor**: Usuario con permiso `cotizaciones_aprobar`
- **Condición**: Solo cotizaciones en estado `selected` pueden aprobarse
- **Descripción**: Aprobar una cotización para generar orden de compra
- **Pasos**:
  1. Seleccionar cotización seleccionada
  2. Hacer clic en "Aprobar"
  3. Se registra el usuario aprobador y fecha
  4. El estado cambia a `approved`

#### 2.6 Rechazar Cotización

- **Actor**: Usuario con permiso `cotizaciones_rechazar`
- **Condición**: Solo cotizaciones en estado `pending` o `selected` pueden rechazarse
- **Descripción**: Rechazar una cotización proporcionando motivo
- **Pasos**:
  1. Seleccionar cotización pendiente o seleccionada
  2. Hacer clic en "Rechazar"
  3. Ingresar motivo de rechazo (obligatorio)
  4. Confirmar rechazo

#### 2.7 Convertir Proveedor Temporal a Registrado

- **Actor**: Usuario con permiso `cotizaciones_crear`
- **Condición**: Solo cotizaciones con proveedor temporal pueden convertirse
- **Descripción**: Registrar un proveedor temporal en el sistema
- **Pasos**:
  1. Seleccionar cotización con proveedor temporal
  2. Hacer clic en "Convertir a Proveedor"
  3. Ingresar datos adicionales (RUC, persona de contacto, dirección)
  4. Confirmar conversión

#### 2.8 Eliminar Cotización

- **Actor**: Usuario con permiso `cotizaciones_eliminar`
- **Condición**: Solo cotizaciones en estado `pending` pueden eliminarse
- **Descripción**: Eliminación permanente de una cotización
- **Pasos**:
  1. Seleccionar cotización pendiente
  2. Hacer clic en "Eliminar"
  3. Confirmar eliminación

#### 2.9 Generar PDF de Cotización

- **Actor**: Usuario con permiso `cotizaciones_ver`
- **Descripción**: Exportar la cotización a formato PDF
- **Pasos**:
  1. Seleccionar cualquier cotización
  2. Hacer clic en "PDF"
  3. Se descarga el documento PDF

### Permisos Requeridos

| Permiso | Descripción |
|---------|-------------|
| `cotizaciones_ver` | Ver lista y detalles de cotizaciones |
| `cotizaciones_crear` | Crear nuevas cotizaciones |
| `cotizaciones_editar` | Editar cotizaciones pendientes |
| `cotizaciones_eliminar` | Eliminar cotizaciones |
| `cotizaciones_aprobar` | Seleccionar y aprobar cotizaciones |
| `cotizaciones_rechazar` | Rechazar cotizaciones |

---

## 3. Flujo de Orden de Compra

### Descripción General

La Orden de Compra (OC) es el documento formal que genera un compromiso de compra con un proveedor. Puede crearse directamente o desde una cotización aprobada.

### Estados de la Orden de Compra

| Estado | Descripción | Color en UI |
|--------|-------------|-------------|
| `draft` | Borrador - Creada pero no emitida | Gris |
| `issued` | Emitida - Enviada al proveedor | Azul |
| `completed` | Completada - Entrega recibida | Verde |
| `cancelled` | Anulada - Cancelada por el usuario | Rojo |

### Casos de Uso

#### 3.1 Crear Orden de Compra desde Cotización Aprobada

- **Actor**: Usuario con permiso `ordenes_compra_crear`
- **Descripción**: Generar una OC directamente desde una cotización previamente aprobada
- **Pasos**:
  1. Acceder a "Órdenes de Compra" > "Nueva"
  2. Seleccionar cotización aprobada (estado `approved`)
  3. Los datos del proveedor y productos se cargan automáticamente
  4. Verificar/modificar fechas, moneda, tipo de cambio
  5. Guardar como borrador

#### 3.2 Crear Orden de Compra Directa

- **Actor**: Usuario con permiso `ordenes_compra_crear`
- **Descripción**: Crear una OC sin necesidad de una cotización previa
- **Pasos**:
  1. Acceder a "Órdenes de Compra" > "Nueva"
  2. Dejar el campo de cotización vacío
  3. Seleccionar proveedor registrado
  4. Ingresar fecha de emisión y fecha de entrega esperada
  5. Seleccionar moneda y tipo de cambio
  6. Agregar productos con cantidad y costo unitario
  7. Guardar como borrador

#### 3.3 Editar Orden de Compra

- **Actor**: Usuario con permiso `ordenes_compra_editar`
- **Condición**: Solo OCs en estado `draft` pueden editase
- **Descripción**: Modificar una OC antes de emitirla
- **Pasos**:
  1. Seleccionar OC en borrador
  2. Modificar los datos necesarios
  3. Guardar cambios

#### 3.4 Emitir Orden de Compra

- **Actor**: Usuario con permiso `ordenes_compra_aprobar`
- **Condición**: Solo OCs en estado `draft` pueden emitirse
- **Descripción**: Finalizar la OC y enviarla formalmente al proveedor
- **Pasos**:
  1. Seleccionar OC en borrador
  2. Hacer clic en "Emitir"
  3. Se registra el usuario emisor y fecha
  4. El estado cambia a `issued`
  5. Si tenía cotización vinculada, esta cambia a `converted`

#### 3.5 Completar Orden de Compra

- **Actor**: Usuario con permiso `ordenes_compra_aprobar`
- **Condición**: Solo OCs en estado `issued` y con todos los productos recibidos pueden completarse
- **Descripción**: Marcar la OC como completamente atendida
- **Pasos**:
  1. Seleccionar OC emitida
  2. Verificar que todos los productos tengan `quantity_received >= quantity`
  3. Hacer clic en "Completar"
  4. El estado cambia a `completed`

#### 3.6 Anular Orden de Compra

- **Actor**: Usuario con permiso `ordenes_compra_anular`
- **Condición**: No pueden anularse OCs en estado `completed`
- **Descripción**: Cancelar una OC emitada o en borrador
- **Pasos**:
  1. Seleccionar OC en borrador o emitida
  2. Hacer clic en "Anular"
  3. El estado cambia a `cancelled`
  4. Si tenía cotización vinculada, esta vuelve a `approved`

#### 3.7 Eliminar Orden de Compra

- **Actor**: Usuario con permiso `ordenes_compra_eliminar`
- **Condición**: Solo OCs en estado `draft` pueden eliminarse
- **Descripción**: Eliminación permanente de una OC
- **Pasos**:
  1. Seleccionar OC en borrador
  2. Hacer clic en "Eliminar"
  3. Confirmar eliminación

#### 3.8 Generar PDF de Orden de Compra

- **Actor**: Usuario con permiso `ordenes_compra_ver`
- **Descripción**: Exportar la OC a formato PDF para enviar al proveedor
- **Pasos**:
  1. Seleccionar cualquier OC
  2. Hacer clic en "PDF"
  3. Se descarga el documento PDF

#### 3.9 Búsqueda de Proveedores y Productos

- **Actor**: Usuario con permiso `ordenes_compra_crear`
- **Descripción**: Utilizar búsqueda dinámica para encontrar proveedores y productos
- **Pasos**:
  1. En el formulario de OC, escribir en el campo de búsqueda
  2. El sistema sugiere coincidencias en tiempo real
  3. Seleccionar de las opciones sugeridas

### Permisos Requeridos

| Permiso | Descripción |
|---------|-------------|
| `ordenes_compra_ver` | Ver lista y detalles de órdenes |
| `ordenes_compra_crear` | Crear nuevas órdenes |
| `ordenes_compra_editar` | Editar órdenes en borrador |
| `ordenes_compra_eliminar` | Eliminar órdenes |
| `ordenes_compra_aprobar` | Emitir y completar órdenes |
| `ordenes_compra_anular` | Anular órdenes |

---

## 4. Flujo de Entrada de Inventario (Stock In)

### Descripción General

El flujo de Entrada de Inventario registra la llegada de productos al almacén. Puede realizarse como parte de una Orden de Compra o como una entrada independiente (ajuste de inventario).

### Tipos de Entrada

| Tipo | Descripción |
|------|-------------|
| Desde OC | Entrada vinculada a una orden de compra |
| Ajuste | Entrada independiente (donación, hallazgo, etc.) |

### Casos de Uso

#### 4.1 Registrar Entrada desde Orden de Compra

- **Actor**: Usuario con permiso `stock_in_crear`
- **Descripción**: Registrar la recepción de productos de una OC emitida
- **Pasos**:
  1. Acceder a "Entradas de Inventario" > "Nueva"
  2. Seleccionar la orden de compra (solo OCs emitidas)
  3. Seleccionar el producto específico de la OC
  4. Ingresar la cantidad recibida
  5. Ingresar costo unitario (se actualiza el costo del producto)
  6. Seleccionar tipo de documento y número de documento
  7. Agregar observaciones
  8. Guardar entrada
  9. El stock del producto aumenta automáticamente

#### 4.2 Registrar Entrada de Ajuste

- **Actor**: Usuario con permiso `stock_in_crear`
- **Descripción**: Registrar entrada de productos sin OC (donaciones, hallazgos, etc.)
- **Pasos**:
  1. Acceder a "Entradas de Inventario" > "Nueva"
  2. Dejar el campo de orden de compra vacío
  3. Seleccionar el producto
  4. Seleccionar proveedor (opcional) o dejar como "Ajuste / N/A"
  5. Ingresar la cantidad
  6. Ingresar costo unitario
  7. Seleccionar tipo de documento
  8. Agregar observaciones
  9. Guardar entrada

#### 4.3 Eliminar Entrada de Inventario

- **Actor**: Usuario con permiso `stock_in_eliminar`
- **Descripción**: Revertir una entrada de inventario y ajustar el stock
- **Condiciones**:
  - El stock actual debe ser mayor o igual a la cantidad de la entrada
  - La entrada debe existir
- **Pasos**:
  1. Seleccionar la entrada a eliminar
  2. Confirmar eliminación
  3. El stock del producto disminuye automáticamente
  4. Si estaba vinculada a una OC, se descuenta de `quantity_received`

#### 4.4 Filtrar Entradas de Inventario

- **Actor**: Usuario con permiso `stock_in_ver`
- **Descripción**: Buscar entradas por diversos criterios
- **Filtros disponibles**:
  - Fecha desde / hasta
  - Proveedor
  - Producto

### Impacto en el Sistema

- **Stock del producto**: Se incrementa con la cantidad ingresada
- **Costo del producto**: Se actualiza al costo unitario de la entrada
- **Orden de Compra**: Se actualiza `quantity_received` en los items
- **Kardex**: Se genera un registro del movimiento
- **Evento StockUpdated**: Se dispara para notificaciones

### Permisos Requeridos

| Permiso | Descripción |
|---------|-------------|
| `stock_in_ver` | Ver lista y detalles de entradas |
| `stock_in_crear` | Registrar nuevas entradas |
| `stock_in_eliminar` | Eliminar entradas |

---

## 5. Flujo de Solicitud de Inventario

### Descripción General

Este flujo permite a los empleados solicitar productos o kits del inventario. Las solicitudes requieren aprobación de un supervisor antes de afectar el stock.

### Estados de la Solicitud

| Estado | Descripción | Color en UI |
|--------|-------------|-------------|
| `Pending` | Pendiente - Esperando aprobación | Amarillo |
| `Approved` | Aprobada - Aprobada por supervisor | Verde |
| `Rejected` | Rechazada - Rechazada por supervisor | Rojo |

### Casos de Uso

#### 5.1 Crear Solicitud de Productos

- **Actor**: Usuario autenticado
- **Descripción**: Solicitar productos del inventario
- **Pasos**:
  1. Acceder a "Solicitudes" > "Nueva"
  2. Ingresar justificación de la solicitud
  3. Indicar área de destino (opcional)
  4. Agregar productos con cantidades deseadas
  5. Enviar solicitud
  6. La solicitud queda en estado `Pending`

#### 5.2 Crear Solicitud de Kits

- **Actor**: Usuario autenticado
- **Descripción**: Solicitar kits completos del inventario
- **Pasos**:
  1. Acceder a "Solicitudes" > "Nueva"
  2. Ingresar justificación
  3. Seleccionar kit de la lista
  4. Indicar cantidad de kits
  5. El sistema muestra los componentes del kit
  6. Enviar solicitud

#### 5.3 Ver Mis Solicitudes

- **Actor**: Usuario autenticado
- **Descripción**: Consultar el estado de las solicitudes propias
- **Pasos**:
  1. Acceder a "Mis Solicitudes"
  2. Ver lista de solicitudes propias con su estado

#### 5.4 Aprobar Solicitud

- **Actor**: Usuario con permiso `solicitudes_aprobar`
- **Condición**: Solo solicitudes en estado `Pending` pueden aprobarse
- **Descripción**: Autorizar una solicitud y descontar stock
- **Validaciones**:
  - Para productos: debe haber stock disponible
  - Para kits: todos los componentes deben tener stock suficiente
- **Pasos**:
  1. Ver lista de solicitudes pendientes
  2. Seleccionar solicitud
  3. Revisar justificación y productos
  4. Hacer clic en "Aprobar"
  5. Confirmar aprobación
  6. El sistema descuenta stock automáticamente

#### 5.5 Rechazar Solicitud

- **Actor**: Usuario con permiso `solicitudes_aprobar`
- **Condición**: Solo solicitudes en estado `Pending` pueden rechazarse
- **Descripción**: Denegar una solicitud proporcionando motivo
- **Pasos**:
  1. Ver lista de solicitudes pendientes
  2. Seleccionar solicitud
  3. Hacer clic en "Rechazar"
  4. Ingresar motivo de rechazo
  5. Confirmar rechazo

#### 5.6 Ver Todas las Solicitudes (Aprobador)

- **Actor**: Usuario con permiso `solicitudes_aprobar`
- **Descripción**: Ver todas las solicitudes del sistema para gestión
- **Pasos**:
  1. Acceder a "Solicitudes"
  2. Ver lista completa con filtros por estado, fecha, solicitante

### Lógica de Descuento de Stock

#### Para Productos Simples

```
stock_producto = stock_actual - cantidad_solicitada
```

#### Para Kits

```
Para cada componente del kit:
  cantidad_total = cantidad_kits * cantidad_componente_por_kit
  stock_componente = stock_actual - cantidad_total
```

### Permisos Requeridos

| Permiso | Descripción |
|---------|-------------|
| `solicitudes_ver` | Ver lista de solicitudes |
| `solicitudes_crear` | Crear nuevas solicitudes |
| `solicitudes_aprobar` | Aprobar o rechazar solicitudes |

---

## 6. Flujo de Kits de Productos

### Descripción General

Un Kit es un conjunto predefinido de productos que se venden o entregan como una unidad. Los kits facilitan la gestión de productos compuestos.

### Casos de Uso

#### 6.1 Crear Kit

- **Actor**: Usuario con permiso `kits_crear`
- **Descripción**: Definir un nuevo kit con sus componentes
- **Pasos**:
  1. Acceder a "Kits" > "Nuevo"
  2. Ingresar nombre y código del kit
  3. Definir precio unitario
  4. Agregar productos componentes con sus cantidades
  5. Guardar kit

#### 6.2 Editar Kit

- **Actor**: Usuario con permiso `kits_editar`
- **Descripción**: Modificar componentes o precio de un kit
- **Pasos**:
  1. Seleccionar kit
  2. Modificar nombre, código, precio o componentes
  3. Guardar cambios

#### 6.3 Eliminar Kit

- **Actor**: Usuario con permiso `kits_eliminar`
- **Descripción**: Eliminación de un kit
- **Pasos**:
  1. Seleccionar kit
  2. Confirmar eliminación

#### 6.4 Solicitar Kit (en Flujo de Solicitud)

- **Actor**: Usuario autenticado
- **Descripción**: Solicitar un kit completo en el flujo de solicitudes
- **Pasos**:
  1. En "Nueva Solicitud", seleccionar "Kit" como tipo de item
  2. Elegir kit de la lista
  3. Indicar cantidad
  4. Enviar solicitud

### Componentes del Kit

- Cada kit puede tener múltiples productos
- Cada producto componente tiene una cantidad requerida
- El sistema calcula automáticamente el stock necesario al aprobar solicitudes

---

## 7. Gestión de Productos

### Descripción General

Los productos son los artículos base del sistema de inventario. Cada producto tiene información de identificación, stock, costo y categoría.

### Casos de Uso

#### 7.1 Crear Producto

- **Actor**: Usuario con permiso `productos_crear`
- **Descripción**: Registrar un nuevo producto en el sistema
- **Pasos**:
  1. Acceder a "Productos" > "Nuevo"
  2. Ingresar código, nombre, descripción
  3. Seleccionar categoría, marca, unidad
  4. Definir precio de venta
  5. Definir stock inicial (opcional)
  6. Definir stock mínimo (para alertas)
  7. Guardar producto

#### 7.2 Editar Producto

- **Actor**: Usuario con permiso `productos_editar`
- **Descripción**: Modificar información de un producto
- **Pasos**:
  1. Seleccionar producto
  2. Modificar campos necesarios
  3. Guardar cambios

#### 7.3 Eliminar Producto

- **Actor**: Usuario con permiso `productos_eliminar`
- **Descripción**: Eliminación de un producto
- **Pasos**:
  1. Seleccionar producto
  2. Confirmar eliminación

#### 7.4 Activar/Inactivar Producto

- **Actor**: Usuario con permiso `productos_editar`
- **Descripción**: Habilitar o deshabilitar un producto para transacciones
- **Pasos**:
  1. Seleccionar producto
  2. Cambiar estado de "Activo"

### Campos del Producto

| Campo | Descripción |
|-------|-------------|
| Código | Código único de identificación |
| Nombre | Nombre del producto |
| Descripción | Descripción detallada |
| Categoría | Clasificación del producto |
| Marca | Fabricante/Marca |
| Unidad | Unidad de medida |
| Costo | Costo de adquisición |
| Precio | Precio de venta |
| Stock | Cantidad actual en inventario |
| Stock Mínimo | Umbral para alertas |

---

## 8. Gestión de Proveedores

### Descripción General

Los proveedores son las entidades que suministran productos al sistema. Pueden ser empresas registradas o contactos temporales.

### Tipos de Proveedores

| Tipo | Descripción |
|------|-------------|
| Registrado | Proveedor dado de alta en el sistema |
| Temporal | Proveedor ocasional sin registro permanente |

### Casos de Uso

#### 8.1 Crear Proveedor Registrado

- **Actor**: Usuario con permiso `proveedores_crear`
- **Descripción**: Registrar un nuevo proveedor en el sistema
- **Pasos**:
  1. Acceder a "Proveedores" > "Nuevo"
  2. Ingresar nombre, RUC, email, teléfono
  3. Agregar persona de contacto (opcional)
  4. Agregar dirección
  5. Guardar proveedor

#### 8.2 Convertir Cotización a Proveedor

- **Actor**: Usuario con permiso `cotizaciones_crear`
- **Descripción**: Registrar un proveedor temporal desde una cotización
- **Pasos**:
  1. Ver cotización con proveedor temporal
  2. Hacer clic en "Convertir a Proveedor"
  3. Completar datos adicionales
  4. Confirmar conversión

#### 8.3 Editar Proveedor

- **Actor**: Usuario con permiso `proveedores_editar`
- **Descripción**: Modificar información de un proveedor
- **Pasos**:
  1. Seleccionar proveedor
  2. Modificar campos necesarios
  3. Guardar cambios

#### 8.4 Eliminar Proveedor

- **Actor**: Usuario con permiso `proveedores_eliminar`
- **Descripción**: Eliminación de un proveedor
- **Pasos**:
  1. Seleccionar proveedor
  2. Confirmar eliminación

---

## Resumen de Flujos Principales

### Flujo Completo de Compra

```
RFQ (Borrador) → RFQ (Enviada) → RFQ (Cerrada)
        ↓
Cotización (Pendiente) → Cotización (Seleccionada) → Cotización (Aprobada)
        ↓
Orden de Compra (Borrador) → Orden de Compra (Emitida)
        ↓
Entrada de Inventario (Stock In)
        ↓
Orden de Compra (Completada)
```

### Flujo de Solicitud Interna

```
Solicitud (Pendiente) → Aprobación
        ↓
Descuenta Stock (Productos o Kits)
        ↓
Solicitud (Aprobada)
```

---

## Matriz de Permisos

| Módulo | Ver | Crear | Editar | Eliminar | Aprobar | Extra |
|--------|-----|-------|--------|----------|---------|-------|
| RFQ | rfq_ver | rfq_crear | rfq_editar | rfq_eliminar | rfq_enviar | - |
| Cotizaciones | cotizaciones_ver | cotizaciones_crear | cotizaciones_editar | cotizaciones_eliminar | cotizaciones_aprobar | cotizaciones_rechazar |
| Órdenes de Compra | ordenes_compra_ver | ordenes_compra_crear | ordenes_compra_editar | ordenes_compra_eliminar | ordenes_compra_aprobar | ordenes_compra_anular |
| Stock In | stock_in_ver | stock_in_crear | - | stock_in_eliminar | - | - |
| Solicitudes | solicitudes_ver | solicitudes_crear | - | - | solicitudes_aprobar | - |
| Productos | productos_ver | productos_crear | productos_editar | productos_eliminar | - | - |
| Proveedores | proveedores_ver | proveedores_crear | proveedores_editar | proveedores_eliminar | - | - |
| Kits | kits_ver | kits_crear | kits_editar | kits_eliminar | - | - |

---

*Documento generado automáticamente desde el código del sistema.*
