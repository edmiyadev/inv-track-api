# Seguridad y Protección del Sistema de Inventario

## 🔒 Centro de Verdad Protegido

### Principio Fundamental
**`inventory_stocks` es la ÚNICA fuente de verdad** para el stock de productos por almacén.

---

## 🛡️ Capas de Protección

### 1. **Arquitectura por Capas**

```
┌─────────────────────────────────────────┐
│  Controllers (Entrada HTTP)             │
│  - InventoryMovementController          │
│  - PurchaseController                   │
├─────────────────────────────────────────┤
│  FormRequests (Validación de entrada)   │
│  - CreateInventoryMovementRequest       │
│  - UpdateReorderPointRequest            │
├─────────────────────────────────────────┤
│  Services (Lógica de negocio)           │
│  - InventoryMovementService             │
│  - PurchaseService                      │
├─────────────────────────────────────────┤
│  InventoryStockService                  │
│  (Única capa que modifica stocks)       │
├─────────────────────────────────────────┤
│  inventory_stocks (Base de datos)       │
│  ⚠️  ÚNICA FUENTE DE VERDAD             │
└─────────────────────────────────────────┘
```

### 2. **No Acceso Directo**

❌ **PROHIBIDO:**
```php
// Nunca hacer esto
InventoryStock::where('id', 1)->update(['quantity' => 100]);
```

✅ **CORRECTO:**
```php
// Siempre a través de movimientos
InventoryMovementService->createMovement([
    'movement_type' => 'adjustment',
    'destination_warehouse_id' => 1,
    'items' => [...]
]);
```

### 3. **Auditoría Obligatoria**

Todo cambio en `inventory_stocks` crea automáticamente:
- Un registro en `inventory_movements` (quién, cuándo, por qué)
- Registros en `inventory_movement_items` (qué productos, cuántos)

### 4. **Validaciones en Múltiples Niveles**

#### Nivel 1: FormRequest (HTTP)
```php
CreateInventoryMovementRequest
- Validación de tipos de datos
- Existencia de productos/almacenes
- Reglas de negocio básicas
```

#### Nivel 2: Service (Lógica)
```php
InventoryMovementService
- Transacciones atómicas (todo o nada)
- Consistencia de datos
```

#### Nivel 3: InventoryStockService
```php
- Stock insuficiente
- Valores negativos
- Límites de cantidad
```

---

## 📋 Tipos de Modificación Permitidos

| Operación | Endpoint | Quien lo usa | Modifica Stock |
|-----------|----------|--------------|----------------|
| **Consultar** | `GET /api/inventory/stocks` | `InventoryStockController` | ❌ No |
| **Configurar reorder_point** | `PUT /api/inventory/stocks/reorder-point` | `InventoryStockController` | ❌ No (solo config) |
| **Entrada (Compra)** | `POST /api/purchases` | `PurchaseService` | ✅ Sí (vía movimiento IN) |
| **Salida (Venta)** | `POST /api/sales` | `SalesService` | ✅ Sí (vía movimiento OUT) |
| **Transferencia** | `POST /api/inventory/movements` | `InventoryMovementService` | ✅ Sí (vía movimiento TRANSFER) |
| **Ajuste** | `POST /api/inventory/movements` | `InventoryMovementService` | ✅ Sí (vía movimiento ADJUSTMENT) |

---

## ⚠️ Reglas de Negocio Implementadas

### 1. Stock No Puede Ser Negativo
```php
// En InventoryStockService::decrementStock()
if ($stock->quantity < $quantity) {
    throw new \Exception("Insufficient stock. Available: {$stock->quantity}, Requested: {$quantity}");
}
```

### 2. Transferencias Requieren Origen
```php
// En CreateInventoryMovementRequest
'origin_warehouse_id' => 'required_if:movement_type,transfer'
```

### 3. Creación Automática de Stock
```php
// Si no existe, se crea con valores por defecto
InventoryStock::firstOrCreate(
    ['warehouse_id' => $warehouseId, 'product_id' => $productId],
    ['quantity' => 0, 'reorder_point' => 10]
);
```

### 4. Transacciones Atómicas
```php
DB::transaction(function () {
    // 1. Crear movimiento
    // 2. Crear items
    // 3. Actualizar stock
    // Si falla algo, se revierte todo
});
```

---

## 🔍 Trazabilidad Completa

### Cada cambio de stock registra:

```sql
inventory_movements
├── id
├── movement_type (in/out/transfer/adjustment)
├── origin_warehouse_id
├── destination_warehouse_id
├── notes
└── created_at (timestamp)

inventory_movement_items
├── id
├── inventory_movement_id (FK)
├── product_id
├── quantity
├── unit_cost
└── total_cost
```

### Consultas de Auditoría

```php
// ¿Quién modificó este stock?
InventoryMovement::with('items')
    ->whereHas('items', fn($q) => $q->where('product_id', $productId))
    ->where('destination_warehouse_id', $warehouseId)
    ->orderBy('created_at', 'desc')
    ->get();

// Historial completo de un producto
InventoryMovementItem::where('product_id', $productId)
    ->with('inventoryMovement')
    ->orderBy('created_at', 'desc')
    ->get();
```

---

## 🚨 Puntos Críticos de Seguridad

### ✅ LO QUE ESTÁ PROTEGIDO:

1. **inventory_stocks.quantity** - Solo modificable vía `InventoryStockService`
2. **Transacciones atómicas** - Rollback automático en caso de error
3. **Validación de stock** - No permite cantidades negativas
4. **Auditoría obligatoria** - Siempre se registra el movimiento

### ⚠️ CONSIDERACIONES ADICIONALES:

1. **Permisos de usuario** - Implementar mediante `Policies` de Laravel
2. **Límites de cantidad** - Actualmente 10,000 para reorder_point
3. **Concurrencia** - Laravel usa locks de BD en transacciones
4. **Soft deletes** - Considerar si se requiere no eliminar movimientos

---

## 📊 Validación de Integridad

### Query para verificar consistencia:

```sql
-- Verificar que el stock coincide con la suma de movimientos
SELECT 
    p.name,
    w.name as warehouse,
    ist.quantity as stock_actual,
    (
        SELECT COALESCE(SUM(
            CASE 
                WHEN im.movement_type = 'in' THEN imi.quantity
                WHEN im.movement_type = 'out' THEN -imi.quantity
                WHEN im.movement_type = 'transfer' THEN 
                    CASE 
                        WHEN im.destination_warehouse_id = ist.warehouse_id THEN imi.quantity
                        ELSE -imi.quantity
                    END
                ELSE 0
            END
        ), 0)
        FROM inventory_movement_items imi
        JOIN inventory_movements im ON im.id = imi.inventory_movement_id
        WHERE imi.product_id = ist.product_id
        AND (im.origin_warehouse_id = ist.warehouse_id 
             OR im.destination_warehouse_id = ist.warehouse_id)
    ) as stock_calculado
FROM inventory_stocks ist
JOIN products p ON p.id = ist.product_id
JOIN warehouses w ON w.id = ist.warehouse_id
HAVING stock_actual != stock_calculado;
```

---

## 🎯 Recomendaciones para Producción

1. **Agregar políticas de autorización**
   ```php
   Gate::define('create-movement', function ($user) {
       return $user->hasPermission('inventory.movements.create');
   });
   ```

2. **Logging de acciones críticas**
   ```php
   Log::info('Stock adjusted', [
       'user_id' => auth()->id(),
       'product_id' => $productId,
       'warehouse_id' => $warehouseId,
       'quantity' => $quantity,
       'type' => $movementType
   ]);
   ```

3. **Rate limiting en endpoints críticos**
   ```php
   Route::post('movements', [...])->middleware('throttle:10,1');
   ```

4. **Backup periódico de inventory_stocks**
   - Snapshot diario antes de operaciones
   - Permite rollback en caso de error crítico

5. **Alertas de stock crítico**
   - Notificaciones cuando quantity <= reorder_point
   - Dashboard en tiempo real

---

## ✅ Checklist de Seguridad

- [x] Centro de verdad único (`inventory_stocks`)
- [x] No acceso directo a la tabla
- [x] Validaciones en múltiples capas
- [x] Auditoría obligatoria
- [x] Transacciones atómicas
- [x] Validación de stock insuficiente
- [x] FormRequests con mensajes personalizados
- [x] Type hints en todos los métodos
- [x] Manejo de excepciones consistente
- [x] Documentación clara del flujo
- [ ] Políticas de autorización (pendiente)
- [ ] Logging centralizado (pendiente)
- [ ] Rate limiting (pendiente)
