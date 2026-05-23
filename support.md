# PayChat POS Support & Debugging Guide

## Purpose

This document explains how to:

* debug billing failures
* trace API issues
* identify kitchen/invoice/payment failures
* use support codes
* assist pilot customers safely

This is Phase 1 lightweight observability.

The goal is:

* operational reliability
* fast debugging
* minimal architecture risk

---

# Core Concept

Every important POS/API action now has:

* `request_id`
* `support_code`
* structured logs
* frontend diagnostics

When customer reports an issue:

```text
Payment failed. Support Code: PC-A8F3K2
```

We use this code to trace:

* frontend action
* failed API
* tenant
* location
* order
* payment
* exact backend error

---

# Support Workflow

## Step 1 — Ask Customer

Always ask:

```text
1. Support Code
2. Approximate time
3. Outlet/location
4. Screenshot/video if possible
5. What they clicked
```

Example:

```text
Support Code: PC-A8F3K2
Outlet: Frozen Cafe
Time: 8:42 PM
Issue: UPI QR not showing
```

---

# Backend Debugging

## SSH into Server

```bash
ssh root@your-server-ip
```

Go to backend:

```bash
cd /var/www/paychat-api
```

---

# Find Request by Support Code

```bash
grep -R "PCR-MPI7KLTT-5Y2X3M61" storage/logs/
```

This should reveal:

* failed API
* route
* order_id
* payment_id
* tenant
* stack trace

---

# Watch Live Logs

```bash
tail -f storage/logs/laravel-$(date +%F).log
```

Useful during live testing.

---

# Important Log Searches

## Payment create failures

```bash
grep -R "payment.create.failed" storage/logs/
```

## Payment success failures

```bash
grep -R "payment.success.failed" storage/logs/
```

## Invoice failures

```bash
grep -R "invoice.generate.failed" storage/logs/
```

## Kitchen dispatch failures

```bash
grep -R "kitchen.dispatch.failed" storage/logs/
```

## Offline sync failures

```bash
grep -R "offline.sync.failed" storage/logs/
```

---

# Common Failure Examples

## Example 1 — UPI QR Failed

Possible causes:

* UPI method disabled
* backend QR generation failed
* invalid payment config
* network timeout

What to check:

* payment create log
* payment method config
* backend payment response

---

## Example 2 — Invoice Failed

Possible causes:

* invoice DB transaction failed
* order already invoiced
* invalid order state
* template rendering issue

Check:

* invoice.generate.failed logs
* order payment status
* invoice table

---

## Example 3 — Kitchen Send Failed

Possible causes:

* invalid order state
* no unsent items
* websocket/broadcast issue
* kitchen batch exception

Check:

* kitchen.dispatch.failed
* kitchen batch records
* order kitchen state

---

# Database Debugging

## Open MySQL

```bash
mysql -u root -p
```

Use tenant DB:

```sql
USE tenant_your_database;
```

---

# Useful Tables

## Orders

```sql
SELECT * FROM pos_orders
WHERE id = 123;
```

Check:

* payment_status
* status
* dining_flow
* total
* meta

---

## Payments

```sql
SELECT * FROM pos_payments
WHERE order_id = 123;
```

Check:

* status
* payment_method
* provider_ref
* amount
* meta

---

## Invoices

```sql
SELECT * FROM invoices
WHERE order_id = 123;
```

---

## Table Sessions

```sql
SELECT * FROM table_sessions
WHERE order_id = 123;
```

---

# Frontend Debugging

## Browser Diagnostics

Open browser DevTools:

```text
F12 → Application → Session Storage
```

Look for:

* diagnostics events
* request_id
* support_code
* failed API info

Stored diagnostics include:

* route/screen
* online/offline state
* browser/device
* order/payment ids
* failed API URL
* status code

---

# Important Frontend Flows

## Critical flows being tracked

* Classic checkout
* Premium checkout
* UPI QR generation
* Invoice generation
* Send to kitchen
* Offline sync
* WhatsApp invoice send

---

# Operational Rules

## VERY IMPORTANT

Billing must continue even if:

* diagnostics fail
* logging fails
* support tracking fails

Observability must NEVER block operations.

---

# Privacy Rules

Never log:

* bearer tokens
* API keys
* customer phone/email
* payment proof images
* full UPI IDs
* sensitive provider payloads

Only log:

* operational metadata
* request ids
* order/payment references
* safe error messages

---

# Pilot-Phase Philosophy

Current focus is:

```text
Operational visibility
NOT enterprise monitoring
```

We are intentionally keeping:

* low-risk
* additive
* lightweight
* easy to rollback

---

# Phase Rollout

## Phase 1 (Current)

* request_id
* support_code
* structured logs
* frontend diagnostics
* critical flow tracking

## Phase 2

* Sentry backend
* Sentry frontend
* support lookup panel

## Phase 3

* centralized logging
* metrics
* alerts
* operational dashboards

---

# Golden Rule

When customer says:

```text
“Billing failed.”
```

We should immediately know:

* which order
* which API
* which outlet
* which payment
* what exact error
* when it happened

without reproducing the issue manually.

That is the purpose of this system.

---------------------------------------

# Notes

To run migrations on specific tenants db
```
php artisan tenant:migrate-db tenant_frozen_cafe --path=database/migrations/tenant/some_migration.php
php artisan tenant:migrate-db tenant_frozen_cafe --path=database/migrations/tenant --pretend
php artisan tenant:migrate-db tenant_frozen_cafe --path=database/migrations/tenant --step
```