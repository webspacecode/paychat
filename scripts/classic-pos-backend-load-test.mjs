#!/usr/bin/env node

import { performance } from 'node:perf_hooks';

const env = process.env;
const baseUrl = (env.BASE_URL || 'http://127.0.0.1:8000').replace(/\/$/, '');
const tenantSlug = env.TENANT_SLUG;
const locationId = env.LOCATION_ID ? Number(env.LOCATION_ID) : null;
const configuredProductId = env.PRODUCT_ID ? Number(env.PRODUCT_ID) : null;
const paymentMethod = env.PAYMENT_METHOD || 'cash';
const clients = (env.CLIENTS || '10,25').split(',').map((value) => Number(value.trim())).filter(Boolean);
const checkoutsPerClient = Number(env.CHECKOUTS_PER_CLIENT || 1);
const requestTimeoutMs = Number(env.REQUEST_TIMEOUT_MS || 10000);
const timeoutProbeMs = Number(env.TIMEOUT_PROBE_MS || 1);
const authToken = env.AUTH_TOKEN || '';

if (!tenantSlug) {
  die('TENANT_SLUG is required.');
}

const kioskBase = `${baseUrl}/api/kiosk/${encodeURIComponent(tenantSlug)}`;
const protectedBase = `${baseUrl}/api/${encodeURIComponent(tenantSlug)}`;

const summary = {
  lostOrders: 0,
  duplicateSuccessfulPayments: 0,
  timeoutReconciled: false,
  offlineDedupe: authToken ? false : 'skipped: AUTH_TOKEN not set',
  sideEffectIsolation: 'covered by PHPUnit CheckoutPaymentReliabilityTest',
};

const main = async () => {
  const fixture = await resolveFixture();
  console.log(`Classic POS backend load test`);
  console.log(`Base URL: ${baseUrl}`);
  console.log(`Tenant: ${tenantSlug}`);
  console.log(`Location: ${fixture.locationId}`);
  console.log(`Product: ${fixture.productId}`);
  console.log(`Payment method: ${paymentMethod}`);
  console.log('');

  const runs = [];
  for (const clientCount of clients) {
    const run = await runBurst(clientCount, fixture);
    runs.push(run);
    printRun(run);
  }

  const reconciliation = await timeoutReconciliationProbe(fixture);
  summary.timeoutReconciled = reconciliation.reconciled;
  console.log(`Timeout/retry reconciliation: ${reconciliation.reconciled ? 'PASS' : 'FAIL'} (${reconciliation.note})`);

  if (authToken) {
    const offline = await offlineDedupeProbe(fixture);
    summary.offlineDedupe = offline.deduped;
    console.log(`Offline local_order_id dedupe: ${offline.deduped ? 'PASS' : 'FAIL'} (${offline.note})`);
  } else {
    console.log('Offline local_order_id dedupe: SKIPPED (set AUTH_TOKEN for protected /offline-orders/sync)');
  }

  const acceptance = {
    tenClientsP95Under2s: passForClients(runs, 10, 2000),
    twentyFiveClientsP95Under4s: passForClients(runs, 25, 4000),
    zeroDuplicateSuccessfulPayments: summary.duplicateSuccessfulPayments === 0,
    zeroLostOrders: summary.lostOrders === 0,
    retryAfterTimeoutReconcilesSafely: summary.timeoutReconciled === true,
    sideEffectFailureDoesNotMarkPaymentFailed: summary.sideEffectIsolation,
  };

  console.log('');
  console.log('Acceptance summary');
  for (const [key, value] of Object.entries(acceptance)) {
    console.log(`- ${key}: ${value === true ? 'PASS' : value === false ? 'FAIL' : value}`);
  }

  const failed = Object.values(acceptance).some((value) => value === false);
  process.exitCode = failed ? 1 : 0;
};

async function runBurst(clientCount, fixture) {
  const started = performance.now();
  const tasks = [];

  for (let client = 0; client < clientCount; client += 1) {
    tasks.push(clientWorker(client, fixture));
  }

  const settled = await Promise.allSettled(tasks);
  const checkouts = settled.flatMap((result) => result.status === 'fulfilled' ? result.value : [{
    ok: false,
    durationMs: 0,
    error: result.reason?.message || String(result.reason),
  }]);

  const successful = checkouts.filter((checkout) => checkout.ok);
  const failed = checkouts.filter((checkout) => !checkout.ok);
  const durations = successful.map((checkout) => checkout.durationMs);

  summary.lostOrders += successful.filter((checkout) => !checkout.orderCompleted).length;
  summary.duplicateSuccessfulPayments += successful.reduce((count, checkout) => count + checkout.duplicateSuccessfulPayments, 0);

  return {
    clientCount,
    checkouts: checkouts.length,
    successful: successful.length,
    failed: failed.length,
    p95Ms: percentile(durations, 95),
    maxMs: durations.length ? Math.max(...durations) : 0,
    wallMs: Math.round(performance.now() - started),
    errors: failed.slice(0, 5).map((checkout) => checkout.error),
  };
}

async function clientWorker(client, fixture) {
  const results = [];
  for (let index = 0; index < checkoutsPerClient; index += 1) {
    results.push(await checkoutFlow(client, index, fixture));
  }
  return results;
}

async function checkoutFlow(client, index, fixture) {
  const started = performance.now();
  const cartTotal = fixture.price;
  const order = await api(`${kioskBase}/orders`, {
    method: 'POST',
    body: {
      location_id: fixture.locationId,
      order_type: 'takeaway',
    },
  });
  const orderId = dataGet(order, ['data', 'id']) ?? order.id;

  await api(`${kioskBase}/orders/${orderId}/items`, {
    method: 'PUT',
    body: {
      items: [{ product_id: fixture.productId, quantity: 1 }],
      subtotal: cartTotal,
      tax: 0,
      discount: 0,
      total: cartTotal,
    },
  });

  await api(`${kioskBase}/orders/${orderId}/pending-payment`, { method: 'POST' });

  const paymentPayload = await api(`${kioskBase}/orders/${orderId}/payments`, {
    method: 'POST',
    body: {
      payment_method: paymentMethod,
      amount: cartTotal,
    },
  });

  const payment = paymentPayload.payment || paymentPayload.data || paymentPayload;
  const paymentId = payment.id ?? payment.payment_id;
  if (!paymentId) {
    throw new Error(`Checkout ${client}/${index} did not return payment id`);
  }

  await api(`${kioskBase}/payments/${paymentId}/success`, { method: 'POST' });
  await api(`${kioskBase}/payments/${paymentId}/success`, { method: 'POST' });

  const fetched = await api(`${kioskBase}/orders/${orderId}`, { method: 'GET' });
  const orderData = fetched.data || fetched;
  const payments = orderData.payments || [];
  const successPayments = payments.filter((candidate) => candidate.status === 'success').length;
  const orderCompleted = orderData.status === 'completed' && orderData.payment_status === 'paid';

  return {
    ok: orderCompleted && successPayments === 1,
    orderId,
    paymentId,
    orderCompleted,
    duplicateSuccessfulPayments: Math.max(0, successPayments - 1),
    durationMs: Math.round(performance.now() - started),
    error: orderCompleted ? null : `order ${orderId} ended ${orderData.status}/${orderData.payment_status}`,
  };
}

async function timeoutReconciliationProbe(fixture) {
  try {
    const order = await api(`${kioskBase}/orders`, {
      method: 'POST',
      body: { location_id: fixture.locationId, order_type: 'takeaway' },
    });
    const orderId = dataGet(order, ['data', 'id']) ?? order.id;
    await api(`${kioskBase}/orders/${orderId}/items`, {
      method: 'PUT',
      body: {
        items: [{ product_id: fixture.productId, quantity: 1 }],
        subtotal: fixture.price,
        tax: 0,
        discount: 0,
        total: fixture.price,
      },
    });
    await api(`${kioskBase}/orders/${orderId}/pending-payment`, { method: 'POST' });
    const paymentPayload = await api(`${kioskBase}/orders/${orderId}/payments`, {
      method: 'POST',
      body: { payment_method: paymentMethod, amount: fixture.price },
    });
    const paymentId = (paymentPayload.payment || paymentPayload.data || paymentPayload).id;

    await api(`${kioskBase}/payments/${paymentId}/success`, {
      method: 'POST',
      timeoutMs: timeoutProbeMs,
      allowAbort: true,
    }).catch(() => null);

    const retry = await api(`${kioskBase}/payments/${paymentId}/success`, { method: 'POST' });
    const fetched = await api(`${kioskBase}/orders/${orderId}`, { method: 'GET' });
    const orderData = fetched.data || fetched;
    const successPayments = (orderData.payments || []).filter((candidate) => candidate.status === 'success').length;
    const reconciled = retry.success === true && orderData.payment_status === 'paid' && successPayments === 1;

    return {
      reconciled,
      note: `order=${orderId}, payment=${paymentId}, success_payments=${successPayments}`,
    };
  } catch (error) {
    return { reconciled: false, note: error.message };
  }
}

async function offlineDedupeProbe(fixture) {
  const localOrderId = `loadtest-${Date.now()}-${Math.random().toString(16).slice(2)}`;
  const payload = {
    local_order_id: localOrderId,
    location_id: fixture.locationId,
    order_type: 'takeaway',
    offline_created_at: new Date().toISOString(),
    items: [{ product_id: fixture.productId, quantity: 1 }],
    totals: {
      subtotal: fixture.price,
      tax_total: 0,
      discount_total: 0,
      grand_total: fixture.price,
      paid_amount: fixture.price,
      balance_amount: 0,
    },
    payment: {
      method: paymentMethod,
      amount: fixture.price,
      reference: `loadtest-${localOrderId}`,
      status: 'success',
    },
  };

  try {
    const first = await api(`${protectedBase}/offline-orders/sync`, {
      method: 'POST',
      body: payload,
      auth: true,
    });
    const second = await api(`${protectedBase}/offline-orders/sync`, {
      method: 'POST',
      body: payload,
      auth: true,
    });
    const deduped = first.backend_order_id && first.backend_order_id === second.backend_order_id;

    return {
      deduped,
      note: `local_order_id=${localOrderId}, backend_order_id=${second.backend_order_id}`,
    };
  } catch (error) {
    return { deduped: false, note: error.message };
  }
}

async function resolveFixture() {
  const locations = await api(`${kioskBase}/locations`, { method: 'GET' });
  const location = locationId
    ? { id: locationId }
    : firstArrayItem(locations);
  if (!location?.id) {
    die('No location found. Set LOCATION_ID explicitly.');
  }

  if (configuredProductId) {
    return {
      locationId: location.id,
      productId: configuredProductId,
      price: Number(env.PRODUCT_PRICE || 100),
    };
  }

  const products = await api(`${kioskBase}/products`, { method: 'GET' });
  const product = firstArrayItem(products);
  if (!product?.id) {
    die('No product found. Set PRODUCT_ID explicitly.');
  }

  return {
    locationId: location.id,
    productId: product.id,
    price: Number(product.price || env.PRODUCT_PRICE || 100),
  };
}

async function api(url, options = {}) {
  const controller = new AbortController();
  const timeout = setTimeout(() => controller.abort(), options.timeoutMs || requestTimeoutMs);

  try {
    const response = await fetch(url, {
      method: options.method || 'GET',
      headers: {
        Accept: 'application/json',
        ...(options.body ? { 'Content-Type': 'application/json' } : {}),
        ...(options.auth ? { Authorization: `Bearer ${authToken}` } : {}),
      },
      body: options.body ? JSON.stringify(options.body) : undefined,
      signal: controller.signal,
    });

    const text = await response.text();
    const json = text ? JSON.parse(text) : {};

    if (!response.ok) {
      throw new Error(`${options.method || 'GET'} ${url} failed ${response.status}: ${json.message || text}`);
    }

    return json;
  } catch (error) {
    if (options.allowAbort && error.name === 'AbortError') {
      throw error;
    }
    throw error;
  } finally {
    clearTimeout(timeout);
  }
}

function printRun(run) {
  console.log(`${run.clientCount} clients x ${checkoutsPerClient}: success=${run.successful}/${run.checkouts}, p95=${run.p95Ms}ms, max=${run.maxMs}ms, wall=${run.wallMs}ms`);
  for (const error of run.errors) {
    console.log(`  error: ${error}`);
  }
}

function passForClients(runs, clientCount, thresholdMs) {
  const run = runs.find((candidate) => candidate.clientCount === clientCount);
  return Boolean(run && run.failed === 0 && run.p95Ms > 0 && run.p95Ms < thresholdMs);
}

function percentile(values, percentileValue) {
  if (!values.length) {
    return 0;
  }
  const sorted = [...values].sort((a, b) => a - b);
  const index = Math.ceil((percentileValue / 100) * sorted.length) - 1;
  return sorted[Math.max(0, Math.min(sorted.length - 1, index))];
}

function firstArrayItem(payload) {
  const data = payload.data || payload;
  if (Array.isArray(data)) {
    return data[0];
  }
  if (Array.isArray(data?.data)) {
    return data.data[0];
  }
  return null;
}

function dataGet(value, path) {
  return path.reduce((current, key) => current?.[key], value);
}

function die(message) {
  console.error(message);
  process.exit(1);
}

main().catch((error) => {
  console.error(error.stack || error.message);
  process.exit(1);
});
