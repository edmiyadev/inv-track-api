# Project Context: Inventory Management System (University Project)

## Tech Stack
- **Backend:** Laravel
- **Database:** PostgreSQL
- **Frontend:** Next.js, Tailwind CSS, Shadcn (App Router standard)

## Architecture & Business Rules
This system enforces strict accounting and inventory principles. Historical data MUST NOT be mutated.

1. **Transaction Lifecycle (Purchases & Sales):**
   - `draft`: Editable, no impact on inventory.
   - `pending`: Approved, but goods haven't physically moved.
   - `completed`: THE POINT OF NO RETURN. The transaction is locked (no UPDATE/DELETE allowed). This state triggers the creation of inventory movements.

2. **Inventory Stock Calculation:**
   - The `inventory_stocks` table is NEVER updated manually by users.
   - Stock is strictly the aggregate of `inventory_movements`.
   - The database MUST enforce a `CHECK (quantity >= 0)` constraint to prevent negative stock at the DB level.

3. **Movements Structure:**
   - `inventory_movements` uses polymorphic relations (`document_type` and `document_id`) instead of hardcoded foreign keys like `purchase_id`, allowing scalability for Sales, Adjustments, and Returns.

4. **Tax Management:**
   - Taxes are NOT dynamically calculated from a global table for historical records.
   - `purchase_items` and `sale_items` MUST snapshot `tax_percentage` and `tax_amount` at the exact moment of the transaction to protect historical data against future tax law changes.

5. **Entity Relationships:**
   - Products DO NOT belong to a single supplier. The `supplier_id` is removed from `products` and is handled at the `purchases` level.