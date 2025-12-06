import { test, expect, request } from '@playwright/test';

test.describe('API Catalog v2', () => {
  test('GET catalog returns product data', async ({}) => {
    const ctx = await request.newContext({ baseURL: process.env.APP_URL });
    const res = await ctx.get('/api/products/1/catalog');
    expect(res.status()).toBe(200);
    const json = await res.json();
    expect(json.product.id).toBe(1);
    await ctx.dispose();
  });

  test('Bundle pricing and admin status/validity', async ({}) => {
    const ctx = await request.newContext({ baseURL: process.env.APP_URL });
    let res = await ctx.put('/api/products/1/bundles/1/status', { data: { is_active: true } });
    expect([200, 204]).toContain(res.status());
    res = await ctx.put('/api/products/1/bundles/1/validity', { data: { valid_from: null, valid_to: null } });
    expect([200, 204]).toContain(res.status());
    res = await ctx.post('/api/products/1/bundles/1/price', { data: { option_item_ids: [7] } });
    expect(res.status()).toBe(200);
    let json = await res.json();
    expect(Math.abs(json.final_price - 1.1) < 1e-6).toBeTruthy();
    res = await ctx.put('/api/products/1/bundles/1/status', { data: { is_active: false } });
    expect([200, 204]).toContain(res.status());
    res = await ctx.post('/api/products/1/bundles/1/price', { data: { option_item_ids: [7] } });
    expect(res.status()).toBe(404);
    res = await ctx.put('/api/products/1/bundles/1/status', { data: { is_active: true } });
    expect([200, 204]).toContain(res.status());
    res = await ctx.put('/api/products/1/bundles/1/validity', { data: { valid_from: '2030-01-01T00:00:00Z', valid_to: null } });
    expect([200, 204]).toContain(res.status());
    res = await ctx.post('/api/products/1/bundles/1/price', { data: { option_item_ids: [7] } });
    expect(res.status()).toBe(422);
    await ctx.dispose();
  });

  test('Variant generation', async ({}) => {
    const ctx = await request.newContext({ baseURL: process.env.APP_URL });
    const res = await ctx.post('/api/products/1/variants/generate');
    expect(res.status()).toBe(200);
    const json = await res.json();
    expect(typeof json.created).toBe('number');
    await ctx.dispose();
  });
});

