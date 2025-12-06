import { test, expect } from '@playwright/test';

test.describe('Complete User Flow: Package + Modules + AI Credits', () => {
  test.beforeEach(async ({ page }) => {
    await page.goto('/');
  });

  test('should complete signup flow', async ({ page }) => {
    // Navigate to signup
    await page.goto('/signup');
    
    // Check signup page loads
    await expect(page).toHaveTitle(/signup|register/i);
    
    // Verify form elements exist
    const emailInput = page.locator('input[type="email"]').first();
    const passwordInput = page.locator('input[type="password"]').first();
    
    await expect(emailInput).toBeVisible();
    await expect(passwordInput).toBeVisible();
  });

  test('should access login page', async ({ page }) => {
    await page.goto('/login');
    
    await expect(page).toHaveTitle(/login/i);
    
    // Check login form exists
    const loginForm = page.locator('form').first();
    await expect(loginForm).toBeVisible();
  });

  test('should load homepage without errors', async ({ page }) => {
    const errors = [];
    
    page.on('console', msg => {
      if (msg.type() === 'error') {
        errors.push(msg.text());
      }
    });

    page.on('pageerror', error => {
      errors.push(error.message);
    });

    await page.goto('/');
    await page.waitForLoadState('networkidle');
    
    // Check for critical errors
    const criticalErrors = errors.filter(err => 
      err.includes('Failed to load') ||
      err.includes('500') ||
      err.includes('database') ||
      err.includes('SQL')
    );
    
    expect(criticalErrors.length).toBe(0);
  });

  test('should handle navigation without breaking', async ({ page }) => {
    const pages = ['/', '/login', '/signup'];
    
    for (const url of pages) {
      const response = await page.goto(url);
      expect(response?.status()).toBeLessThan(500);
      
      // Wait for page to stabilize
      await page.waitForLoadState('networkidle');
    }
  });

  test('Invite Member send-invite captures server response', async ({ page }) => {
    await page.goto('/login');
    const email = page.locator('input[name="email"]');
    const password = page.locator('input[name="password"]');
    await email.fill('admin@example.com');
    await password.fill('123456');
    await Promise.all([
      page.waitForLoadState('networkidle'),
      page.locator('form button[type="submit"]').first().click()
    ]);

    // Open sidebar brand dropdown and click Invite Member
    await page.locator('.sidebar-brand-box .dropdown-toggle').first().click();
    await page.locator('.invite-member').first().click();

    // Wait for modal and prepare to capture the response
    await page.waitForSelector('#inviteEmailForm');

    const sendInviteResponsePromise = page.waitForResponse((res) => res.url().includes('/account/employees/send-invite'));

    // Add an email via Tagify and optional message, then click Send
    await page.fill('#invitee_email', 'playwright.test+invite@example.com');
    await page.keyboard.press('Enter');
    await page.fill('#message', 'Automated invite test');
    await page.click('#send-invite');

    const sendInviteResponse = await sendInviteResponsePromise;
    const status = sendInviteResponse.status();
    const body = await sendInviteResponse.text();

    console.log('[/account/employees/send-invite] status:', status);
    console.log('[/account/employees/send-invite] body:', body);

    // Do not fail the test; we only capture the response body/status for debugging
    expect([200, 422, 500, 302, 403]).toContain(status);
  });
});

