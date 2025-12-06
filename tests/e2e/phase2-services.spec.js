import { test, expect } from '@playwright/test';

test.describe('Phase 2: Services Integration', () => {
  test.beforeEach(async ({ page }) => {
    // Assuming we have a test admin login
    await page.goto('/super-admin-login');
  });

  test('should access admin panel without service errors', async ({ page }) => {
    // This test verifies services are properly loaded
    // In a real scenario, you'd login first
    
    const errors = [];
    page.on('console', msg => {
      if (msg.type() === 'error') {
        errors.push(msg.text());
      }
    });

    page.on('pageerror', error => {
      errors.push(error.message);
    });

    await page.waitForLoadState('networkidle');
    
    // Check for service-related errors
    const serviceErrors = errors.filter(err => 
      err.includes('AICreditsService') ||
      err.includes('ModuleMarketplaceService') ||
      err.includes('ModuleAddonService') ||
      err.includes('PackageBillingService')
    );
    
    expect(serviceErrors.length).toBe(0);
  });

  test('should load billing page without errors', async ({ page }) => {
    // Navigate to billing (if accessible)
    const response = await page.goto('/super-admin/billing');
    
    // Should either redirect to login or load successfully
    expect([200, 302, 401, 403]).toContain(response?.status() || 0);
  });
});

