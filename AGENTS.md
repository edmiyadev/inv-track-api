# AI Agent Instructions

When generating code, creating migrations, or suggesting logic, adhere strictly to the following roles and patterns:

## 1. Database Agent Rules
- **Migrations:** Always include `down()` methods. Use database-level constraints (foreign keys on delete cascade/restrict, CHECK constraints for positive inventory).
- **Polymorphism:** Use Laravel's `$table->morphs('document')` for the `inventory_movements` table to handle purchases, sales, and adjustments.

## 2. Backend (Laravel) Agent Rules
- **Fat Models, Skinny Controllers:** Keep controllers clean. Delegate complex business logic to Service classes (e.g., `InventoryMovementService`, `CheckoutService`).
- **Transactions:** Any operation that changes a document status to `completed` and generates an `inventory_movement` MUST be wrapped in a `DB::transaction()`. If the movement fails, the status change must roll back.
- **Immutability Guard:** If a model has a `completed` status, override the `delete()` and `update()` methods or use Observers to throw exceptions if mutation is attempted.

## 3. Frontend Agent Rules
- Disabling UI: If a fetched Purchase or Sale object has `status === 'completed'`, the UI MUST automatically hide or disable "Edit", "Delete", and "Add Item" buttons.
- State Management: Use tools like Tanstack Query to manage caching efficiently. Invalidate the inventory cache immediately after a transaction is marked as completed.