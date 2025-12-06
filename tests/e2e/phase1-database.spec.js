import { test, expect } from '@playwright/test';

test.describe('Phase 1: Database Migrations', () => {
  test.beforeEach(async ({ page }) => {
    // Navigate to a page that would trigger database queries
    await page.goto('/login');
  });

  test('should load login page without database errors', async ({ page }) => {
    // Check that page loads successfully
    await expect(page).toHaveTitle(/login/i);
    
    // Check for any console errors related to database
    const errors = [];
    page.on('console', msg => {
      if (msg.type() === 'error') {
        errors.push(msg.text());
      }
    });

    await page.waitForLoadState('networkidle');
    
    // Filter out non-database errors
    const dbErrors = errors.filter(err => 
      err.includes('database') || 
      err.includes('SQL') || 
      err.includes('connection')
    );
    
    expect(dbErrors.length).toBe(0);
  });

  test('should not have migration errors in response', async ({ page }) => {
    const response = await page.goto('/login');
    
    expect(response?.status()).toBe(200);
    
    // Check response doesn't contain migration error messages
    const content = await page.content();
    expect(content).not.toContain('migration');
    expect(content).not.toContain('table doesn\'t exist');
    expect(content).not.toContain('SQLSTATE');
  });
});

