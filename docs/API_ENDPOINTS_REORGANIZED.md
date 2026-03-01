# API Endpoints - Documentación Profesional

## 🔒 Protección del Centro de Verdad

**`inventory_stocks` es la única fuente de verdad** y está protegida mediante:

1. ✅ **No hay endpoints directos** que modifiquen `inventory_stocks`
2. ✅ **Todas las modificaciones pasan por `InventoryMovementService`**
3. ✅ **Cada cambio crea un registro de auditoría** en `inventory_movements`
4. ✅ **Validaciones en `InventoryStockService`** (stock insuficiente, etc.)

### Flujo de Modificación de Stock:

```
Endpoint (Controller)
    ↓
InventoryMovementService.createMovement()
    ↓ (crea registro en inventory_movements)
    ↓
InventoryStockService.adjustStock()
    ↓ (valida y actualiza)
    ↓
inventory_stocks (tabla) ← ÚNICA FUENTE DE VERDAD
```

---

## ✅ Estructura Final de Endpoints

### 📦 **Gestión de Stock** (`InventoryStockController`)

```
GET    /api/inventory/stocks                              # Listar stock (con filtros)
GET    /api/inventory/stocks/warehouse/{warehouseId}      # Stock de un almacén
GET    /api/inventory/stocks/low-stock                    # Productos bajo mínimo
PUT    /api/inventory/stocks/reorder-point                # Configurar punto de reorden
```

### 🔄 **Movimientos de Inventario** (`InventoryMovementController`)

```
POST   /api/inventory/movements                           # Crear transfer o adjustment
GET    /api/inventory/movements                           # Listar movimientos
GET    /api/inventory/movements/{id}                      # Ver detalle
```

### 💰 **Compras** (`PurchaseController` - existente)

```
POST   /api/purchases                                     # Crea automáticamente movement IN
GET    /api/purchases
GET    /api/purchases/{id}
PUT    /api/purchases/{id}
DELETE /api/purchases/{id}
```

### 🛒 **Ventas** (`SalesController` - pendiente)

```
POST   /api/sales                                         # Creará automáticamente movement OUT
```

---

## 🎯 Decisiones de Diseño

### ✅ Lo que SÍ se implementó:

1. **Stock Queries en `InventoryStockController`**
   - Listar stocks con filtros
   - Ver stock por almacén
   - Ver productos bajo mínimo
   - Configurar reorder_point

2. **Movimientos en `InventoryMovementController`**
   - Crear transfers entre almacenes
   - Crear adjustments manuales
   - Ver historial de movimientos

3. **Flujo automático en Purchases**
   - Purchase → crea automáticamente InventoryMovement (IN)
   - No requiere endpoint manual de entrada

### ❌ Lo que NO se implementó:

1. **WarehouseInventoryController** - Eliminado (redundante)
2. **Endpoints manuales de IN/OUT** - Se manejan con Purchases/Sales
3. **Rutas bajo `/warehouses/`** - Todo bajo `/inventory/`

---

## 📋 Tipos de Movimiento

| Tipo | Endpoint | Cuándo se usa |
|------|----------|---------------|
| **IN** | `POST /api/purchases` | Compras (automático) |
| **OUT** | `POST /api/sales` | Ventas (automático) |
| **TRANSFER** | `POST /api/inventory/movements` | Mover entre almacenes |
| **ADJUSTMENT** | `POST /api/inventory/movements` | Correcciones manuales |

---

## 🔧 Ejemplos de Uso

### 1. Crear una compra (entrada automática)
```bash
POST /api/purchases
{
  "supplier_id": 5,
  "warehouse_id": 1,
  "items": [
    {"product_id": 10, "quantity": 100, "unit_price": 25.50}
  ]
}
# Sistema crea automáticamente InventoryMovement (type: "in")
```

### 2. Transferir productos entre almacenes
```bash
POST /api/inventory/movements
{
  "movement_type": "transfer",
  "origin_warehouse_id": 1,
  "destination_warehouse_id": 2,
  "items": [
    {"product_id": 10, "quantity": 30, "unit_price": 25.50}
  ], 
  "notes": "Reubicación"
}
```

### 3. Ajustar inventario (corrección)
```bash
POST /api/inventory/movements
{
  "movement_type": "adjustment",
  "destination_warehouse_id": 1,
  "items": [
    {"product_id": 10, "quantity": 5, "unit_price": 25.50}
  ],
  "notes": "Corrección por inventario físico"
}
```

### 4. Ver productos con stock bajo
```bash
GET /api/inventory/stocks/low-stock?warehouse_id=1
```

### 5. Configurar punto de reorden
```bash
PUT /api/inventory/stocks/reorder-point
{
  "warehouse_id": 1,
  "product_id": 10,
  "reorder_point": 25
}
```

---

## 📊 Arquitectura de Controllers

```
InventoryStockController
├── index()              → Listar stocks
├── getByWarehouse()     → Stock por almacén
├── lowStock()           → Productos bajo mínimo
└── setReorderPoint()    → Configurar reorder_point

InventoryMovementController
├── index()              → Listar movimientos
├── show()               → Ver detalle
└── store()              → Crear transfer/adjustment

PurchaseController
├── store()              → Crea Purchase + Movement (IN)
└── ... (CRUD completo)

WarehouseController
└── ... (CRUD de almacenes)

ProductController
└── ... (CRUD de productos)
```

---

## 🚀 Ventajas de esta Reorganización

✅ **Separación clara de responsabilidades**
- Stocks → `InventoryStockController`
- Movimientos → `InventoryMovementController`
- Compras → `PurchaseController`

✅ **No duplicación de endpoints**
- Eliminado `WarehouseInventoryController`
- Todo consolidado bajo `/inventory/`

✅ **Flujos automáticos**
- Purchase crea movement IN
- Sales creará movement OUT
- Solo transfers y adjustments manuales

✅ **RESTful y consistente**
- Rutas lógicas y predecibles
- Respuestas estandarizadas
- Fácil de documentar

---

## 📝 Notas Importantes

1. **InventoryStockController** maneja CONSULTAS de stock
2. **InventoryMovementController** maneja CAMBIOS manuales (transfer/adjustment)
3. **Purchases** y **Sales** crean movimientos IN/OUT automáticamente
4. Ya no existe `WarehouseInventoryController`
5. Todas las rutas de inventario están bajo `/api/inventory/`
