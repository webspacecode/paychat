# Registration order side-effect boundary

Phase 0 does not create registration orders or alter current order behavior.

`OrderSideEffectPolicy::forOrder()` is the future centralized boundary. It currently returns the existing POS behavior for every order. A later registration orchestration service may supply a source-specific policy without adding scattered `order_type` checks.

Current side effects are triggered by order item synchronization, payment success, order completion, kitchen dispatch, table closure, loyalty award, invoice generation, reporting queries, display broadcasts, and offline synchronization.

The intended future registration policy disables inventory, KDS, tokens, dine-in, customer display, loyalty by default, and offline synchronization. Payments, invoices, and financial reporting remain enabled. No part of that future policy is active in Phase 0.

`order_type` is an unrestricted string and is used by list filters, delivery/dine-in normalization, kitchen filtering, report indexes, dashboard grouping, and offline payloads. Phase 1 should prefer an authoritative `registrations.order_id` relationship plus `pos_orders.meta.source`; it should set a new `order_type` only after those consumers have explicit source-policy coverage.
