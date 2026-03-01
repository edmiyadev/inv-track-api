# Gestión de Inventario por Almacén - Documentación API

## 📋 Resumen de la Arquitectura

El sistema usa **`inventory_stocks`** como única fuente de verdad para el stock, eliminando la redundancia de `product_warehouse`.

### Estructura:
```
inventory_stocks
├── product_id         (producto)
├── warehouse_id       (almacén)
├── quantity           (stock actual)
└── reorder_point      (punto de reorden por almacén)
```

---

## 🔄 Tipos de Movimientos de Inventario

### **IN** (Entrada) - Gestionado por `Purchases`
- Se crea automáticamente al crear una compra
- **Endpoint:** `POST /api/purchases`
- No requiere movimiento manual

### **OUT** (Salida) - Gestionado por `Sales` 
- Se creará automáticamente al crear una venta
- **Endpoint:** `POST /api/sales` (pendiente implementar)
- No requiere movimiento manual

### **TRANSFER** (Transferencia entre almacenes) ✅
```json
POST /api/inventory/movements
{
  "movement_type": "transfer",
  "origin_warehouse_id": 1,
  "destination_warehouse_id": 2,
  "items": [
    {
      "product_id": 10,
      "quantity": 30,
      "unit_price": 25.50
    }
  ],
  "notes": "Reubicación de productos"
}
```

### **ADJUSTMENT** (Ajuste de inventario) ✅
```json
POST /api/inventory/movements
{
  "movement_type": "adjustment",
  "destination_warehouse_id": 1,
  "items": [
    {
      "product_id": 10,
      "quantity": 5,  // positivo = incrementa
      "unit_price": 25.50
    }
  ],
  "notes": "Corrección por inventario físico"
}
```

---

## 📦 Endpoints de Gestión de Stock

### 1. Listar stock con filtros
```http
GET /api/inventory/stocks?product_id={id}&warehouse_id={id}
```

**Parámetros opcionales:**
- `product_id` - Filtrar por producto
- `warehouse_id` - Filtrar por almacén

### 2. Ver inventario de un almacén específico
```http
GET /api/inventory/stocks/warehouse/{warehouseId}
```

**Respuesta:**
```json
{
  "success": true,
  "data": [
    {
      "product_id": 10,
      "product_name": "Laptop Dell",
      "product_sku": "SKU-001",
      "quantity": 45,
      "reorder_point": 20,
      "needs_reorder": false,
      "status": "ok"
    }
  ]
}
```

### 3. Ver productos con bajo stock
```http
GET /api/inventory/stocks/low-stock?warehouse_id={id}
```

**Parámetros opcionales:**
- `warehouse_id` - Si se omite, muestra todos los almacenes

**Respuesta:**
```json
{
  "success": true,
  "count": 3,
  "data": [
    {
      "product_id": 15,
      "product_name": "Mouse Logitech",
      "product_sku": "SKU-002",
      "warehouse_id": 1,
      "warehouse_name": "Almacén Central",
      "quantity": 8,
      "reorder_point": 10,
      "deficit": 2
    }
  ]
}
```

### 4. Establecer punto de reorden
```http
PUT /api/inventory/stocks/reorder-point
```

**Body:**
```json
{
  "warehouse_id": 1,
  "product_id": 10,
  "reorder_point": 25
}
```

**Respuesta:**
```json
{
  "success": true,
  "message": "Reorder point updated successfully",
  "data": {
    "id": 5,
    "warehouse_id": 1,
    "product_id": 10,
    "quantity": 45,
    "reorder_point": 25
  }
}
```

---

## 📋 Endpoints de Movimientos de Inventario

### 1. Crear movimiento (transfer o adjustment)
```http
POST /api/inventory/movements
```

Ver ejemplos en sección "Tipos de Movimientos" arriba

### 2. Listar todos los movimientos
```http
GET /api/inventory/movements
```

### 3. Ver detalle de un movimiento
```http
GET /api/inventory/movements/{id}
```

---

## 🔧 Flujos de Trabajo

### 📥 Escenario 1: Nueva Compra (Entrada de mercancía)
```
1. Usuario crea compra
   POST /api/purchases
   ↓
2. PurchaseService automáticamente:
   - Crea InventoryMovement (type: "in")
   - Actualiza InventoryStock
   ↓
3. (Opcional) Ajustar reorder_point
   PUT /api/inventory/stocks/reorder-point
```

### 🔄 Escenario 2: Transferencia entre almacenes
```
1. Crear movimiento de transferencia
   POST /api/inventory/movements
   {
     "movement_type": "transfer",
     "origin_warehouse_id": 1,
     "destination_warehouse_id": 2,
     "items": [...]
   }
   ↓
2. Sistema automáticamente:
   - Decrementa stock en almacén origen
   - Incrementa stock en almacén destino
   - Registra movimiento en ledger
```

### 🔧 Escenario 3: Ajuste de inventario
```
1. Realizar conteo físico en almacén
   ↓
2. Crear ajuste
   POST /api/inventory/movements
   {
     "movement_type": "adjustment",
     "destination_warehouse_id": 1,
     "items": [
       {"product_id": 10, "quantity": 5, "unit_price": 25.50}
     ]
   }
   ↓
3. Sistema ajusta stock según cantidad (+ o -)
```

### 📊 Escenario 4: Monitoreo de Stock Bajo
```
1. Consultar productos bajo mínimo
   GET /api/inventory/stocks/low-stock
   ↓
2. Revisar productos con deficit
   ↓
3. Opciones:
   a) Crear nueva compra (Purchase)
   b) Transferir de otro almacén
   c) Ajustar reorder_point si es muy alto
```

---

## 🎯 Consultas Útiles con Eloquent

```php
// Stock total de un producto en TODOS los almacenes
$totalStock = Product::find($productId)
    ->inventoryStocks()
    ->sum('quantity');

// Stock de un producto en almacenes activos
$activeStock = Product::find($productId)
    ->inventoryStocks()
    ->whereHas('warehouse', fn($q) => $q->where('is_active', true))
    ->get();

// Productos bajo mínimo en un almacén específico
$lowStockProducts = InventoryStock::where('warehouse_id', 1)
    ->whereRaw('quantity <= reorder_point')
    ->with('product')
    ->get();

// Almacenes que tienen un producto específico
$warehouses = InventoryStock::where('product_id', $productId)
    ->with('warehouse')
    ->get();
```

---

## ⚙️ Configuración de Reorder Point

### Opciones:

1. **Por defecto**: 10 unidades (definido en migración)
2. **Manual**: Ajustar mediante endpoint PUT
3. **Por categoría**: Implementar lógica en seeders/factories

### Recomendaciones:
- Productos de alta rotación: reorder_point más alto
- Productos especiales: reorder_point personalizado por almacén
- Revisar semanalmente productos bajo mínimo

---

## 🚨 Validaciones Implementadas

1. **Stock insuficiente**: No permite sacar más de lo disponible
2. **Punto de reorden**: Debe ser >= 0
3. **Creación automática**: Si no existe registro, se crea con defaults

---

## 📊 Reportes Sugeridos

```php
// Dashboard de stock crítico
GET /api/warehouses/low-stock

// Inventario completo de un almacén
GET /api/warehouses/{id}/inventory

// Historial de movimientos
GET /api/inventory/ledger

// Stock por producto
GET /api/inventory/stocks?product_id={id}
```
