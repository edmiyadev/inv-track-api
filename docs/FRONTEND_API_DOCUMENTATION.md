# 📘 Documentación de API para Frontend - InvTrack

## 🎯 Información General

**Base URL:** `http://your-domain.com/api`  
**Autenticación:** Laravel Sanctum (Token Bearer)  
**Formato de respuesta:** JSON  

### Headers Requeridos

```javascript
headers: {
  'Authorization': 'Bearer YOUR_ACCESS_TOKEN',
  'Content-Type': 'application/json',
  'Accept': 'application/json'
}
```

### Formato de Respuesta Estándar

#### Respuesta Exitosa
```json
{
  "status": "success",
  "message": "Mensaje descriptivo",
  "data": { ... }
}
```

#### Respuesta de Error
```json
{
  "status": "error",
  "message": "Descripción del error",
  "errors": { ... } // Solo en errores de validación
}
```

---

## 🔐 Autenticación

### 1. Login
**POST** `/api/auth/login`

**Request Body:**
```json
{
  "email": "user@example.com",
  "password": "password123"
}
```

**Response (200):**
```json
{
  "status": "success",
  "message": "User logged in successfully",
  "data": {
    "token": "1|abc123xyz...",
    "user": {
      "id": 1,
      "name": "John Doe",
      "email": "user@example.com",
      "roles": [
        {
          "id": 1,
          "name": "admin",
          "permissions": [...]
        }
      ]
    }
  }
}
```

**Uso en Frontend:**
```javascript
// Login y guardar token
async function login(email, password) {
  const response = await fetch('/api/auth/login', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ email, password })
  });
  
  const data = await response.json();
  
  if (data.status === 'success') {
    // Guardar token en localStorage o context
    localStorage.setItem('token', data.data.token);
    localStorage.setItem('user', JSON.stringify(data.data.user));
    return data.data;
  }
  
  throw new Error(data.message);
}
```

### 2. Logout
**GET** `/api/auth/logout`

**Headers:** Requiere autenticación

**Response (200):**
```json
{
  "status": "success",
  "message": "User logged out successfully"
}
```

### 3. Obtener Usuario Actual
**GET** `/api/user`

**Headers:** Requiere autenticación

**Response (200):**
```json
{
  "id": 1,
  "name": "John Doe",
  "email": "user@example.com",
  "roles": [
    {
      "id": 1,
      "name": "admin",
      "permissions": [
        { "name": "view-products" },
        { "name": "create-purchases" }
      ]
    }
  ]
}
```

---

## 📦 Gestión de Inventario (Stock)

### 🔍 Conceptos Clave

- **`inventory_stocks`**: Tabla única de verdad para el stock por producto y almacén
- **`inventory_movements`**: Registro de auditoría de todos los movimientos
- El stock **NUNCA** se modifica directamente, solo a través de movimientos

### 1. Listar Stock Actual
**GET** `/api/inventory/stocks`

**Query Parameters (opcionales):**
- `product_id`: Filtrar por producto específico
- `warehouse_id`: Filtrar por almacén específico

**Ejemplos de uso:**
```javascript
// Obtener todo el stock
GET /api/inventory/stocks

// Stock de un producto específico en todos los almacenes
GET /api/inventory/stocks?product_id=5

// Stock de un almacén específico
GET /api/inventory/stocks?warehouse_id=2

// Stock de un producto en un almacén específico
GET /api/inventory/stocks?product_id=5&warehouse_id=2
```

**Response (200):**
```json
{
  "status": "success",
  "message": "Stock retrieved successfully",
  "data": [
    {
      "id": 1,
      "product_id": 5,
      "warehouse_id": 2,
      "quantity": 150,
      "reorder_point": 20,
      "created_at": "2025-11-20T10:00:00.000000Z",
      "updated_at": "2025-11-25T15:30:00.000000Z",
      "product": {
        "id": 5,
        "name": "Laptop Dell XPS 15",
        "sku": "LAP-DELL-001",
        "description": "...",
        "unit_price": 1299.99,
        "category_id": 1
      },
      "warehouse": {
        "id": 2,
        "name": "Almacén Central",
        "location": "Ciudad de México"
      }
    }
  ]
}
```

**Implementación Frontend:**
```javascript
// Service/API para obtener stock
async function getStock(filters = {}) {
  const params = new URLSearchParams(filters).toString();
  const url = `/api/inventory/stocks${params ? '?' + params : ''}`;
  
  const response = await fetch(url, {
    headers: {
      'Authorization': `Bearer ${getToken()}`,
      'Accept': 'application/json'
    }
  });
  
  return await response.json();
}

// Uso en componente
const stocks = await getStock({ warehouse_id: 2 });

// Mostrar en tabla
stocks.data.forEach(stock => {
  console.log(`${stock.product.name}: ${stock.quantity} unidades`);
  
  // Verificar si necesita reorden
  if (stock.quantity <= stock.reorder_point) {
    console.warn(`⚠️ Stock bajo: ${stock.product.name}`);
  }
});
```

### 2. Stock por Almacén
**GET** `/api/inventory/stocks/warehouse/{warehouseId}`

**Parámetros de ruta:**
- `warehouseId`: ID del almacén

**Response (200):**
```json
{
  "status": "success",
  "message": "Stock for warehouse retrieved successfully",
  "data": [
    {
      "product_id": 5,
      "product_name": "Laptop Dell XPS 15",
      "sku": "LAP-DELL-001",
      "quantity": 150,
      "reorder_point": 20,
      "status": "ok",
      "warehouse_id": 2,
      "warehouse_name": "Almacén Central"
    },
    {
      "product_id": 8,
      "product_name": "Mouse Logitech MX",
      "sku": "MOU-LOG-001",
      "quantity": 8,
      "reorder_point": 10,
      "status": "low",
      "warehouse_id": 2,
      "warehouse_name": "Almacén Central"
    }
  ]
}
```

**Estados posibles:**
- `ok`: Stock normal (quantity > reorder_point)
- `low`: Stock bajo (quantity <= reorder_point)

**Uso en Frontend:**
```javascript
// Obtener stock de un almacén
async function getWarehouseStock(warehouseId) {
  const response = await fetch(`/api/inventory/stocks/warehouse/${warehouseId}`, {
    headers: {
      'Authorization': `Bearer ${getToken()}`,
      'Accept': 'application/json'
    }
  });
  
  return await response.json();
}

// Mostrar con indicadores visuales
function renderStockStatus(stock) {
  const statusColors = {
    ok: 'green',
    low: 'red'
  };
  
  return `
    <div class="stock-item" style="border-left: 4px solid ${statusColors[stock.status]}">
      <h3>${stock.product_name}</h3>
      <p>Stock: ${stock.quantity} unidades</p>
      <p>Punto de reorden: ${stock.reorder_point}</p>
      ${stock.status === 'low' ? '<span class="badge-warning">⚠️ Stock Bajo</span>' : ''}
    </div>
  `;
}
```

### 3. Productos con Stock Bajo
**GET** `/api/inventory/stocks/low-stock`

**Query Parameters (opcionales):**
- `warehouse_id`: Filtrar por almacén específico

**Ejemplos:**
```javascript
// Productos con stock bajo en todos los almacenes
GET /api/inventory/stocks/low-stock

// Productos con stock bajo en un almacén específico
GET /api/inventory/stocks/low-stock?warehouse_id=2
```

**Response (200):**
```json
{
  "status": "success",
  "message": "Products needing reorder retrieved successfully",
  "data": [
    {
      "product_id": 8,
      "product_name": "Mouse Logitech MX",
      "sku": "MOU-LOG-001",
      "quantity": 8,
      "reorder_point": 10,
      "status": "low",
      "warehouse_id": 2,
      "warehouse_name": "Almacén Central"
    }
  ]
}
```

**Uso en Frontend:**
```javascript
// Dashboard de alertas
async function showLowStockAlerts() {
  const response = await getStock({ lowStock: true });
  
  if (response.data.length > 0) {
    // Mostrar notificación
    showNotification({
      type: 'warning',
      title: 'Stock Bajo',
      message: `${response.data.length} productos necesitan reorden`
    });
    
    // Mostrar lista
    response.data.forEach(item => {
      addToReorderList(item);
    });
  }
}

// Llamar periódicamente o al cargar dashboard
setInterval(showLowStockAlerts, 300000); // Cada 5 minutos
```

### 4. Configurar Punto de Reorden
**PUT** `/api/inventory/stocks/reorder-point`

⚠️ **Importante:** Este endpoint SOLO modifica la configuración del `reorder_point`, NO modifica las cantidades de stock.

**Request Body:**
```json
{
  "warehouse_id": 2,
  "product_id": 5,
  "reorder_point": 25
}
```

**Validaciones:**
- `warehouse_id`: Requerido, debe existir en la tabla warehouses
- `product_id`: Requerido, debe existir en la tabla products
- `reorder_point`: Requerido, número entero entre 0 y 10000

**Response (200):**
```json
{
  "status": "success",
  "message": "Reorder point updated successfully",
  "data": {
    "id": 1,
    "product_id": 5,
    "warehouse_id": 2,
    "quantity": 150,
    "reorder_point": 25,
    "updated_at": "2025-11-26T10:30:00.000000Z"
  }
}
```

**Response Error (422):**
```json
{
  "status": "error",
  "message": "Error de validación",
  "errors": {
    "reorder_point": ["El punto de reorden debe ser un número entre 0 y 10000"]
  }
}
```

**Implementación Frontend:**
```javascript
// Formulario para configurar reorder point
async function updateReorderPoint(warehouseId, productId, reorderPoint) {
  const response = await fetch('/api/inventory/stocks/reorder-point', {
    method: 'PUT',
    headers: {
      'Authorization': `Bearer ${getToken()}`,
      'Content-Type': 'application/json',
      'Accept': 'application/json'
    },
    body: JSON.stringify({
      warehouse_id: warehouseId,
      product_id: productId,
      reorder_point: reorderPoint
    })
  });
  
  const data = await response.json();
  
  if (data.status === 'success') {
    showSuccess('Punto de reorden actualizado correctamente');
    return data.data;
  } else {
    showError(data.message, data.errors);
    throw new Error(data.message);
  }
}

// Componente React ejemplo
function ReorderPointForm({ warehouse, product, currentValue }) {
  const [reorderPoint, setReorderPoint] = useState(currentValue);
  
  const handleSubmit = async (e) => {
    e.preventDefault();
    try {
      await updateReorderPoint(warehouse.id, product.id, reorderPoint);
      onSuccess();
    } catch (error) {
      console.error(error);
    }
  };
  
  return (
    <form onSubmit={handleSubmit}>
      <label>Punto de Reorden</label>
      <input 
        type="number" 
        min="0" 
        max="10000"
        value={reorderPoint}
        onChange={(e) => setReorderPoint(e.target.value)}
      />
      <button type="submit">Actualizar</button>
    </form>
  );
}
```

---

## 🔄 Movimientos de Inventario

### 🔍 Conceptos Clave

Los movimientos son la **ÚNICA manera** de modificar el stock. Existen 4 tipos:

1. **IN (Entrada)**: Se crea automáticamente al hacer una compra
2. **OUT (Salida)**: Se creará automáticamente al hacer una venta
3. **TRANSFER (Transferencia)**: Manual, mover stock entre almacenes
4. **ADJUSTMENT (Ajuste)**: Manual, corregir discrepancias de inventario

### 1. Crear Movimiento Manual (Transferencia o Ajuste)
**POST** `/api/inventory/movements`

#### Ejemplo 1: Transferencia entre almacenes

**Request Body:**
```json
{
  "movement_type": "transfer",
  "origin_warehouse_id": 1,
  "destination_warehouse_id": 2,
  "notes": "Transferencia por reorganización de inventario",
  "items": [
    {
      "product_id": 5,
      "quantity": 20,
      "unit_price": 1299.99
    },
    {
      "product_id": 8,
      "quantity": 10,
      "unit_price": 49.99
    }
  ]
}
```

**¿Qué sucede internamente?**
1. Valida que haya suficiente stock en almacén origen
2. Crea registro de movimiento en `inventory_movements`
3. Crea items del movimiento en `inventory_movement_items`
4. **Decrementa** stock en almacén origen
5. **Incrementa** stock en almacén destino
6. Todo en una transacción (si algo falla, se revierte todo)

#### Ejemplo 2: Ajuste de inventario

**Request Body:**
```json
{
  "movement_type": "adjustment",
  "destination_warehouse_id": 2,
  "notes": "Ajuste por conteo físico - productos dañados",
  "items": [
    {
      "product_id": 5,
      "quantity": -5,
      "unit_price": 1299.99
    }
  ]
}
```

**Nota:** En ajustes, la cantidad puede ser negativa (para reducir stock) o positiva (para incrementar).

**Response (201):**
```json
{
  "status": "success",
  "message": "Movement created successfully",
  "data": {
    "id": 15,
    "movement_type": "transfer",
    "origin_warehouse_id": 1,
    "destination_warehouse_id": 2,
    "notes": "Transferencia por reorganización de inventario",
    "created_at": "2025-11-26T10:45:00.000000Z",
    "items": [
      {
        "id": 28,
        "product_id": 5,
        "quantity": 20,
        "unit_cost": 1299.99,
        "total_cost": 25999.80,
        "product": {
          "id": 5,
          "name": "Laptop Dell XPS 15",
          "sku": "LAP-DELL-001"
        }
      }
    ]
  }
}
```

**Response Error (422) - Stock Insuficiente:**
```json
{
  "status": "error",
  "message": "Insufficient stock. Available: 15, Requested: 20"
}
```

**Response Error (422) - Validación:**
```json
{
  "status": "error",
  "message": "Error de validación",
  "errors": {
    "movement_type": ["El tipo de movimiento debe ser transfer o adjustment"],
    "origin_warehouse_id": ["El almacén origen es requerido para transferencias"],
    "items": ["Debe incluir al menos un producto"]
  }
}
```

**Implementación Frontend:**
```javascript
// Servicio para crear movimientos
async function createInventoryMovement(movementData) {
  const response = await fetch('/api/inventory/movements', {
    method: 'POST',
    headers: {
      'Authorization': `Bearer ${getToken()}`,
      'Content-Type': 'application/json',
      'Accept': 'application/json'
    },
    body: JSON.stringify(movementData)
  });
  
  const data = await response.json();
  
  if (!response.ok) {
    throw new Error(data.message || 'Error al crear movimiento');
  }
  
  return data;
}

// Ejemplo: Formulario de transferencia
async function transferStock(fromWarehouse, toWarehouse, items, notes) {
  try {
    const movement = await createInventoryMovement({
      movement_type: 'transfer',
      origin_warehouse_id: fromWarehouse,
      destination_warehouse_id: toWarehouse,
      notes: notes,
      items: items.map(item => ({
        product_id: item.productId,
        quantity: item.quantity,
        unit_price: item.unitPrice
      }))
    });
    
    showSuccess('Transferencia realizada exitosamente');
    return movement.data;
    
  } catch (error) {
    showError(error.message);
    throw error;
  }
}

// Ejemplo: Ajuste de inventario
async function adjustInventory(warehouseId, adjustments, notes) {
  try {
    const movement = await createInventoryMovement({
      movement_type: 'adjustment',
      destination_warehouse_id: warehouseId,
      notes: notes,
      items: adjustments.map(adj => ({
        product_id: adj.productId,
        quantity: adj.quantityChange, // Puede ser negativo
        unit_price: adj.unitPrice
      }))
    });
    
    showSuccess('Ajuste de inventario realizado');
    return movement.data;
    
  } catch (error) {
    showError(error.message);
    throw error;
  }
}
```

**Componente React - Formulario de Transferencia:**
```javascript
function TransferForm() {
  const [formData, setFormData] = useState({
    originWarehouse: '',
    destinationWarehouse: '',
    notes: '',
    items: []
  });
  
  const handleAddItem = () => {
    setFormData({
      ...formData,
      items: [...formData.items, { product_id: '', quantity: 0, unit_price: 0 }]
    });
  };
  
  const handleSubmit = async (e) => {
    e.preventDefault();
    
    try {
      await createInventoryMovement({
        movement_type: 'transfer',
        origin_warehouse_id: formData.originWarehouse,
        destination_warehouse_id: formData.destinationWarehouse,
        notes: formData.notes,
        items: formData.items
      });
      
      alert('Transferencia exitosa');
      // Redirigir o limpiar formulario
      
    } catch (error) {
      alert(error.message);
    }
  };
  
  return (
    <form onSubmit={handleSubmit}>
      <select 
        value={formData.originWarehouse}
        onChange={(e) => setFormData({...formData, originWarehouse: e.target.value})}
        required
      >
        <option value="">Almacén Origen</option>
        {warehouses.map(w => <option key={w.id} value={w.id}>{w.name}</option>)}
      </select>
      
      <select 
        value={formData.destinationWarehouse}
        onChange={(e) => setFormData({...formData, destinationWarehouse: e.target.value})}
        required
      >
        <option value="">Almacén Destino</option>
        {warehouses.map(w => <option key={w.id} value={w.id}>{w.name}</option>)}
      </select>
      
      <textarea 
        placeholder="Notas"
        value={formData.notes}
        onChange={(e) => setFormData({...formData, notes: e.target.value})}
      />
      
      {/* Items */}
      <div>
        {formData.items.map((item, index) => (
          <ItemRow key={index} item={item} onChange={(newItem) => {
            const newItems = [...formData.items];
            newItems[index] = newItem;
            setFormData({...formData, items: newItems});
          }} />
        ))}
        <button type="button" onClick={handleAddItem}>+ Agregar Producto</button>
      </div>
      
      <button type="submit">Realizar Transferencia</button>
    </form>
  );
}
```

### 2. Listar Movimientos
**GET** `/api/inventory/movements`

**Response (200):**
```json
{
  "status": "success",
  "message": "Movements retrieved successfully",
  "data": [
    {
      "id": 15,
      "movement_type": "transfer",
      "origin_warehouse_id": 1,
      "destination_warehouse_id": 2,
      "notes": "Transferencia por reorganización",
      "created_at": "2025-11-26T10:45:00.000000Z",
      "origin_warehouse": {
        "id": 1,
        "name": "Almacén Principal"
      },
      "destination_warehouse": {
        "id": 2,
        "name": "Almacén Central"
      }
    },
    {
      "id": 14,
      "movement_type": "in",
      "origin_warehouse_id": null,
      "destination_warehouse_id": 2,
      "notes": "Compra #12",
      "created_at": "2025-11-25T14:30:00.000000Z"
    }
  ]
}
```

**Uso en Frontend:**
```javascript
// Historial de movimientos
async function getMovementHistory(filters = {}) {
  const response = await fetch('/api/inventory/movements', {
    headers: {
      'Authorization': `Bearer ${getToken()}`,
      'Accept': 'application/json'
    }
  });
  
  return await response.json();
}

// Mostrar en tabla con iconos según tipo
function renderMovementType(type) {
  const types = {
    in: { icon: '📥', label: 'Entrada', color: 'green' },
    out: { icon: '📤', label: 'Salida', color: 'red' },
    transfer: { icon: '🔄', label: 'Transferencia', color: 'blue' },
    adjustment: { icon: '⚙️', label: 'Ajuste', color: 'orange' }
  };
  
  const config = types[type];
  return `<span style="color: ${config.color}">${config.icon} ${config.label}</span>`;
}
```

### 3. Ver Detalle de Movimiento
**GET** `/api/inventory/movements/{id}`

**Response (200):**
```json
{
  "status": "success",
  "message": "Movement retrieved successfully",
  "data": {
    "id": 15,
    "movement_type": "transfer",
    "origin_warehouse_id": 1,
    "destination_warehouse_id": 2,
    "notes": "Transferencia por reorganización",
    "created_at": "2025-11-26T10:45:00.000000Z",
    "origin_warehouse": {
      "id": 1,
      "name": "Almacén Principal",
      "location": "Ciudad de México"
    },
    "destination_warehouse": {
      "id": 2,
      "name": "Almacén Central",
      "location": "Guadalajara"
    },
    "items": [
      {
        "id": 28,
        "inventory_movement_id": 15,
        "product_id": 5,
        "quantity": 20,
        "unit_cost": 1299.99,
        "total_cost": 25999.80,
        "product": {
          "id": 5,
          "name": "Laptop Dell XPS 15",
          "sku": "LAP-DELL-001",
          "description": "Laptop profesional 15 pulgadas"
        }
      },
      {
        "id": 29,
        "inventory_movement_id": 15,
        "product_id": 8,
        "quantity": 10,
        "unit_cost": 49.99,
        "total_cost": 499.90,
        "product": {
          "id": 8,
          "name": "Mouse Logitech MX",
          "sku": "MOU-LOG-001"
        }
      }
    ]
  }
}
```

**Uso en Frontend:**
```javascript
// Página de detalle de movimiento
async function showMovementDetail(movementId) {
  const response = await fetch(`/api/inventory/movements/${movementId}`, {
    headers: {
      'Authorization': `Bearer ${getToken()}`,
      'Accept': 'application/json'
    }
  });
  
  const data = await response.json();
  const movement = data.data;
  
  // Renderizar detalle
  return `
    <div class="movement-detail">
      <h2>Movimiento #${movement.id}</h2>
      <p><strong>Tipo:</strong> ${movement.movement_type}</p>
      <p><strong>Fecha:</strong> ${new Date(movement.created_at).toLocaleDateString()}</p>
      
      ${movement.origin_warehouse ? `
        <p><strong>Origen:</strong> ${movement.origin_warehouse.name}</p>
      ` : ''}
      
      <p><strong>Destino:</strong> ${movement.destination_warehouse.name}</p>
      <p><strong>Notas:</strong> ${movement.notes || 'N/A'}</p>
      
      <h3>Productos</h3>
      <table>
        <thead>
          <tr>
            <th>Producto</th>
            <th>SKU</th>
            <th>Cantidad</th>
            <th>Costo Unitario</th>
            <th>Total</th>
          </tr>
        </thead>
        <tbody>
          ${movement.items.map(item => `
            <tr>
              <td>${item.product.name}</td>
              <td>${item.product.sku}</td>
              <td>${item.quantity}</td>
              <td>$${item.unit_cost.toFixed(2)}</td>
              <td>$${item.total_cost.toFixed(2)}</td>
            </tr>
          `).join('')}
        </tbody>
      </table>
      
      <p><strong>Total del Movimiento:</strong> $${
        movement.items.reduce((sum, item) => sum + item.total_cost, 0).toFixed(2)
      }</p>
    </div>
  `;
}
```

---

## 🛒 Gestión de Compras (Purchases)

### 🔍 Conceptos Clave

- Las compras crean automáticamente un movimiento de tipo **IN**
- El stock se actualiza automáticamente en el almacén destino
- Se genera auditoría completa del proceso

### 1. Crear Compra
**POST** `/api/purchases`

**Request Body:**
```json
{
  "supplier_id": 3,
  "warehouse_id": 2,
  "notes": "Compra de equipos de oficina",
  "items": [
    {
      "product_id": 5,
      "quantity": 25,
      "unit_price": 1299.99
    },
    {
      "product_id": 8,
      "quantity": 50,
      "unit_price": 49.99
    }
  ]
}
```

**Validaciones:**
- `supplier_id`: Requerido, debe existir en suppliers
- `warehouse_id`: Opcional (si no se proporciona, se usa un almacén por defecto)
- `items`: Array requerido, mínimo 1 producto
- `items.*.product_id`: Requerido, debe existir
- `items.*.quantity`: Requerido, entero mínimo 1
- `items.*.unit_price`: Requerido, número mínimo 0

**¿Qué sucede internamente?**
1. Crea el registro de compra en `purchases`
2. Crea los items en `purchase_items`
3. Calcula el `total_amount` automáticamente
4. **Crea automáticamente un movimiento IN**
5. **Incrementa el stock** en el almacén destino
6. Todo en transacción atómica

**Response (201):**
```json
{
  "status": "success",
  "message": "Purchase created successfully",
  "data": {
    "id": 12,
    "supplier_id": 3,
    "warehouse_id": 2,
    "total_amount": 34999.25,
    "purchase_date": "2025-11-26",
    "notes": "Compra de equipos de oficina",
    "created_at": "2025-11-26T11:00:00.000000Z",
    "supplier": {
      "id": 3,
      "name": "Tech Supplies Inc",
      "email": "sales@techsupplies.com",
      "phone": "555-1234"
    },
    "warehouse": {
      "id": 2,
      "name": "Almacén Central",
      "location": "Guadalajara"
    },
    "items": [
      {
        "id": 45,
        "purchase_id": 12,
        "product_id": 5,
        "quantity": 25,
        "unit_price": 1299.99,
        "total_price": 32499.75,
        "product": {
          "id": 5,
          "name": "Laptop Dell XPS 15",
          "sku": "LAP-DELL-001"
        }
      },
      {
        "id": 46,
        "purchase_id": 12,
        "product_id": 8,
        "quantity": 50,
        "unit_price": 49.99,
        "total_price": 2499.50,
        "product": {
          "id": 8,
          "name": "Mouse Logitech MX",
          "sku": "MOU-LOG-001"
        }
      }
    ]
  }
}
```

**Implementación Frontend:**
```javascript
// Servicio para crear compra
async function createPurchase(purchaseData) {
  const response = await fetch('/api/purchases', {
    method: 'POST',
    headers: {
      'Authorization': `Bearer ${getToken()}`,
      'Content-Type': 'application/json',
      'Accept': 'application/json'
    },
    body: JSON.stringify(purchaseData)
  });
  
  const data = await response.json();
  
  if (!response.ok) {
    throw new Error(data.message || 'Error al crear compra');
  }
  
  return data;
}

// Ejemplo de uso
async function submitPurchaseOrder(supplierId, warehouseId, items, notes) {
  try {
    const purchase = await createPurchase({
      supplier_id: supplierId,
      warehouse_id: warehouseId,
      notes: notes,
      items: items.map(item => ({
        product_id: item.productId,
        quantity: item.quantity,
        unit_price: item.unitPrice
      }))
    });
    
    showSuccess(`Compra #${purchase.data.id} creada exitosamente`);
    showSuccess(`Stock actualizado automáticamente en ${purchase.data.warehouse.name}`);
    
    return purchase.data;
    
  } catch (error) {
    showError(error.message);
    throw error;
  }
}
```

**Componente React - Formulario de Compra:**
```javascript
function PurchaseForm() {
  const [formData, setFormData] = useState({
    supplier_id: '',
    warehouse_id: '',
    notes: '',
    items: []
  });
  
  const [products, setProducts] = useState([]);
  const [suppliers, setSuppliers] = useState([]);
  const [warehouses, setWarehouses] = useState([]);
  
  useEffect(() => {
    // Cargar catálogos
    loadProducts();
    loadSuppliers();
    loadWarehouses();
  }, []);
  
  const handleAddItem = () => {
    setFormData({
      ...formData,
      items: [...formData.items, { product_id: '', quantity: 1, unit_price: 0 }]
    });
  };
  
  const handleRemoveItem = (index) => {
    const newItems = formData.items.filter((_, i) => i !== index);
    setFormData({ ...formData, items: newItems });
  };
  
  const handleItemChange = (index, field, value) => {
    const newItems = [...formData.items];
    newItems[index][field] = value;
    
    // Si cambia el producto, actualizar precio automáticamente
    if (field === 'product_id') {
      const product = products.find(p => p.id === parseInt(value));
      if (product) {
        newItems[index].unit_price = product.unit_price;
      }
    }
    
    setFormData({ ...formData, items: newItems });
  };
  
  const calculateTotal = () => {
    return formData.items.reduce((sum, item) => 
      sum + (item.quantity * item.unit_price), 0
    ).toFixed(2);
  };
  
  const handleSubmit = async (e) => {
    e.preventDefault();
    
    if (formData.items.length === 0) {
      alert('Debe agregar al menos un producto');
      return;
    }
    
    try {
      const purchase = await createPurchase(formData);
      alert(`Compra creada exitosamente. Total: $${purchase.data.total_amount}`);
      // Redirigir o limpiar formulario
      navigate(`/purchases/${purchase.data.id}`);
    } catch (error) {
      alert(error.message);
    }
  };
  
  return (
    <form onSubmit={handleSubmit} className="purchase-form">
      <h2>Nueva Compra</h2>
      
      <div className="form-group">
        <label>Proveedor *</label>
        <select 
          value={formData.supplier_id}
          onChange={(e) => setFormData({...formData, supplier_id: e.target.value})}
          required
        >
          <option value="">Seleccionar proveedor</option>
          {suppliers.map(s => (
            <option key={s.id} value={s.id}>{s.name}</option>
          ))}
        </select>
      </div>
      
      <div className="form-group">
        <label>Almacén Destino *</label>
        <select 
          value={formData.warehouse_id}
          onChange={(e) => setFormData({...formData, warehouse_id: e.target.value})}
          required
        >
          <option value="">Seleccionar almacén</option>
          {warehouses.map(w => (
            <option key={w.id} value={w.id}>{w.name}</option>
          ))}
        </select>
      </div>
      
      <div className="form-group">
        <label>Notas</label>
        <textarea 
          value={formData.notes}
          onChange={(e) => setFormData({...formData, notes: e.target.value})}
          rows="3"
          placeholder="Notas adicionales sobre la compra"
        />
      </div>
      
      <div className="items-section">
        <h3>Productos</h3>
        
        {formData.items.map((item, index) => (
          <div key={index} className="item-row">
            <select 
              value={item.product_id}
              onChange={(e) => handleItemChange(index, 'product_id', e.target.value)}
              required
            >
              <option value="">Seleccionar producto</option>
              {products.map(p => (
                <option key={p.id} value={p.id}>{p.name} - {p.sku}</option>
              ))}
            </select>
            
            <input 
              type="number"
              placeholder="Cantidad"
              min="1"
              value={item.quantity}
              onChange={(e) => handleItemChange(index, 'quantity', e.target.value)}
              required
            />
            
            <input 
              type="number"
              placeholder="Precio Unitario"
              min="0"
              step="0.01"
              value={item.unit_price}
              onChange={(e) => handleItemChange(index, 'unit_price', e.target.value)}
              required
            />
            
            <span className="item-total">
              ${(item.quantity * item.unit_price).toFixed(2)}
            </span>
            
            <button 
              type="button" 
              onClick={() => handleRemoveItem(index)}
              className="btn-remove"
            >
              ✕
            </button>
          </div>
        ))}
        
        <button type="button" onClick={handleAddItem} className="btn-add">
          + Agregar Producto
        </button>
      </div>
      
      <div className="form-footer">
        <div className="total">
          <strong>Total de la Compra: ${calculateTotal()}</strong>
        </div>
        <button type="submit" className="btn-primary">
          Crear Compra
        </button>
      </div>
    </form>
  );
}
```

### 2. Listar Compras
**GET** `/api/purchases`

**Response (200):**
```json
{
  "status": "success",
  "message": "Purchases retrieved successfully",
  "data": [
    {
      "id": 12,
      "supplier_id": 3,
      "warehouse_id": 2,
      "total_amount": 34999.25,
      "purchase_date": "2025-11-26",
      "notes": "Compra de equipos de oficina",
      "created_at": "2025-11-26T11:00:00.000000Z",
      "supplier": {
        "id": 3,
        "name": "Tech Supplies Inc"
      },
      "warehouse": {
        "id": 2,
        "name": "Almacén Central"
      }
    }
  ]
}
```

### 3. Ver Detalle de Compra
**GET** `/api/purchases/{id}`

**Response (200):**
```json
{
  "status": "success",
  "message": "Purchase retrieved successfully",
  "data": {
    "id": 12,
    "supplier_id": 3,
    "warehouse_id": 2,
    "total_amount": 34999.25,
    "purchase_date": "2025-11-26",
    "notes": "Compra de equipos de oficina",
    "created_at": "2025-11-26T11:00:00.000000Z",
    "supplier": {
      "id": 3,
      "name": "Tech Supplies Inc",
      "email": "sales@techsupplies.com",
      "phone": "555-1234",
      "address": "123 Tech Street"
    },
    "warehouse": {
      "id": 2,
      "name": "Almacén Central",
      "location": "Guadalajara"
    },
    "items": [
      {
        "id": 45,
        "product_id": 5,
        "quantity": 25,
        "unit_price": 1299.99,
        "total_price": 32499.75,
        "product": {
          "id": 5,
          "name": "Laptop Dell XPS 15",
          "sku": "LAP-DELL-001",
          "description": "Laptop profesional 15 pulgadas"
        }
      }
    ]
  }
}
```

### 4. Actualizar Compra
**PUT** `/api/purchases/{id}`

**Request Body:** (Mismo formato que crear)

**Response (200):**
```json
{
  "status": "success",
  "message": "Purchase updated successfully",
  "data": { ... }
}
```

### 5. Eliminar Compra
**DELETE** `/api/purchases/{id}`

**Response (200):**
```json
{
  "status": "success",
  "message": "Purchase deleted successfully"
}
```

---

## 📊 Ejemplo de Flujo Completo

### Escenario: Recibir una compra y revisar stock

```javascript
// 1. Crear la compra
const purchase = await createPurchase({
  supplier_id: 3,
  warehouse_id: 2,
  notes: "Compra mensual de inventario",
  items: [
    { product_id: 5, quantity: 25, unit_price: 1299.99 },
    { product_id: 8, quantity: 50, unit_price: 49.99 }
  ]
});

console.log(`✅ Compra #${purchase.data.id} creada`);
console.log(`💰 Total: $${purchase.data.total_amount}`);

// 2. Verificar que el stock se actualizó
const stock = await getStock({ 
  warehouse_id: 2, 
  product_id: 5 
});

console.log(`📦 Stock actual: ${stock.data[0].quantity} unidades`);

// 3. Revisar el movimiento que se creó automáticamente
const movements = await fetch('/api/inventory/movements').then(r => r.json());
const lastMovement = movements.data[0];

console.log(`📥 Movimiento ${lastMovement.movement_type} creado automáticamente`);

// 4. Verificar productos con stock bajo
const lowStock = await fetch('/api/inventory/stocks/low-stock?warehouse_id=2')
  .then(r => r.json());

if (lowStock.data.length > 0) {
  console.log(`⚠️ ${lowStock.data.length} productos necesitan reorden`);
  lowStock.data.forEach(item => {
    console.log(`- ${item.product_name}: ${item.quantity}/${item.reorder_point}`);
  });
}
```

---

## 🔧 Manejo de Errores

### Códigos de Estado HTTP

- **200**: Operación exitosa (GET, PUT, DELETE)
- **201**: Recurso creado exitosamente (POST)
- **400**: Error en la solicitud
- **401**: No autenticado (token inválido o faltante)
- **403**: No autorizado (sin permisos)
- **404**: Recurso no encontrado
- **422**: Error de validación
- **500**: Error del servidor

### Implementación de Manejo de Errores

```javascript
// Función helper para manejar respuestas
async function apiRequest(url, options = {}) {
  const defaultOptions = {
    headers: {
      'Authorization': `Bearer ${getToken()}`,
      'Content-Type': 'application/json',
      'Accept': 'application/json'
    }
  };
  
  const response = await fetch(url, { ...defaultOptions, ...options });
  const data = await response.json();
  
  if (!response.ok) {
    // Error de validación
    if (response.status === 422 && data.errors) {
      const errorMessages = Object.values(data.errors).flat().join(', ');
      throw new ValidationError(errorMessages, data.errors);
    }
    
    // Error de autenticación
    if (response.status === 401) {
      // Redirigir al login
      logout();
      throw new AuthError('Sesión expirada');
    }
    
    // Otros errores
    throw new ApiError(data.message || 'Error en la solicitud', response.status);
  }
  
  return data;
}

// Clases de error personalizadas
class ApiError extends Error {
  constructor(message, status) {
    super(message);
    this.status = status;
    this.name = 'ApiError';
  }
}

class ValidationError extends ApiError {
  constructor(message, errors) {
    super(message, 422);
    this.errors = errors;
    this.name = 'ValidationError';
  }
}

class AuthError extends ApiError {
  constructor(message) {
    super(message, 401);
    this.name = 'AuthError';
  }
}

// Uso con manejo de errores
try {
  const stock = await apiRequest('/api/inventory/stocks');
  renderStockTable(stock.data);
  
} catch (error) {
  if (error instanceof ValidationError) {
    showValidationErrors(error.errors);
  } else if (error instanceof AuthError) {
    redirectToLogin();
  } else {
    showError(error.message);
  }
}
```

---

## 🎨 Componentes UI Recomendados

### 1. Dashboard de Stock

```javascript
function InventoryDashboard() {
  const [stats, setStats] = useState({
    totalProducts: 0,
    lowStockCount: 0,
    totalValue: 0
  });
  
  useEffect(() => {
    loadDashboardData();
  }, []);
  
  async function loadDashboardData() {
    const [allStock, lowStock] = await Promise.all([
      apiRequest('/api/inventory/stocks'),
      apiRequest('/api/inventory/stocks/low-stock')
    ]);
    
    setStats({
      totalProducts: allStock.data.length,
      lowStockCount: lowStock.data.length,
      totalValue: allStock.data.reduce((sum, item) => 
        sum + (item.quantity * item.product.unit_price), 0
      )
    });
  }
  
  return (
    <div className="dashboard">
      <div className="stats-grid">
        <StatCard 
          title="Total Productos" 
          value={stats.totalProducts}
          icon="📦"
        />
        <StatCard 
          title="Stock Bajo" 
          value={stats.lowStockCount}
          icon="⚠️"
          alert={stats.lowStockCount > 0}
        />
        <StatCard 
          title="Valor Inventario" 
          value={`$${stats.totalValue.toFixed(2)}`}
          icon="💰"
        />
      </div>
      
      <LowStockAlert count={stats.lowStockCount} />
      <RecentMovements />
    </div>
  );
}
```

### 2. Tabla de Stock con Filtros

```javascript
function StockTable() {
  const [stocks, setStocks] = useState([]);
  const [filters, setFilters] = useState({
    warehouse_id: '',
    product_id: '',
    lowStock: false
  });
  
  useEffect(() => {
    loadStock();
  }, [filters]);
  
  async function loadStock() {
    const params = new URLSearchParams();
    if (filters.warehouse_id) params.append('warehouse_id', filters.warehouse_id);
    if (filters.product_id) params.append('product_id', filters.product_id);
    
    const url = filters.lowStock 
      ? '/api/inventory/stocks/low-stock'
      : '/api/inventory/stocks';
    
    const data = await apiRequest(`${url}?${params}`);
    setStocks(data.data);
  }
  
  return (
    <div>
      <div className="filters">
        <select onChange={(e) => setFilters({...filters, warehouse_id: e.target.value})}>
          <option value="">Todos los almacenes</option>
          {/* opciones */}
        </select>
        
        <label>
          <input 
            type="checkbox"
            checked={filters.lowStock}
            onChange={(e) => setFilters({...filters, lowStock: e.target.checked})}
          />
          Solo stock bajo
        </label>
      </div>
      
      <table>
        <thead>
          <tr>
            <th>Producto</th>
            <th>SKU</th>
            <th>Almacén</th>
            <th>Cantidad</th>
            <th>Punto Reorden</th>
            <th>Estado</th>
          </tr>
        </thead>
        <tbody>
          {stocks.map(stock => (
            <tr key={stock.id} className={stock.status === 'low' ? 'alert' : ''}>
              <td>{stock.product.name}</td>
              <td>{stock.product.sku}</td>
              <td>{stock.warehouse.name}</td>
              <td>{stock.quantity}</td>
              <td>{stock.reorder_point}</td>
              <td>
                {stock.quantity <= stock.reorder_point ? (
                  <span className="badge-warning">⚠️ Bajo</span>
                ) : (
                  <span className="badge-success">✓ OK</span>
                )}
              </td>
            </tr>
          ))}
        </tbody>
      </table>
    </div>
  );
}
```

---

## 📝 Notas Importantes

### ✅ Mejores Prácticas

1. **Siempre validar en frontend antes de enviar** - Aunque el backend valida, mejora la UX
2. **Mostrar feedback inmediato** - Usar loading states y mensajes de éxito/error
3. **Manejar errores de red** - Implementar retry logic para errores temporales
4. **Cachear catálogos** - Productos, almacenes y proveedores no cambian frecuentemente
5. **Actualizar UI después de operaciones** - Refrescar listas después de crear/editar

### ⚠️ Consideraciones de Seguridad

1. **Nunca exponer el token** - Guardarlo de forma segura (localStorage con precaución)
2. **Implementar timeout de sesión** - Logout automático después de inactividad
3. **Validar permisos en UI** - Ocultar opciones según roles del usuario
4. **HTTPS obligatorio en producción**

### 🚀 Optimizaciones

1. **Paginación** - Para listas grandes, implementar paginación
2. **Debounce en búsquedas** - Evitar múltiples requests en filtros en tiempo real
3. **Lazy loading** - Cargar datos bajo demanda
4. **Optimistic UI** - Actualizar UI antes de confirmación del servidor

---

## 📞 Contacto y Soporte

Para dudas o problemas con la API, contactar al equipo de backend.

**Versión del Documento:** 1.0  
**Última Actualización:** 26 de Noviembre, 2025
