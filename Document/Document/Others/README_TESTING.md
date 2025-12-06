# Comprehensive Testing Guide

This project includes comprehensive tests for all phases of the Module Marketplace and AI Credits system.

## Test Structure

### PHPUnit Tests (Backend)
- **Phase 1 Tests**: Database migrations, Models, Relationships
- **Phase 2 Tests**: All Services (AICreditsService, ModuleMarketplaceService, etc.)
- **Phase 3 Tests**: Controllers and API endpoints (to be created)

### Playwright Tests (E2E)
- **Phase 1 E2E**: Database migration verification
- **Phase 2 E2E**: Service integration checks
- **Complete User Flow**: End-to-end user journeys

## Running Tests

### Install Dependencies

```bash
# Install PHP dependencies (if not already done)
composer install

# Install Playwright
npm install
npx playwright install
```

### Run PHPUnit Tests

```bash
# Run all tests
php artisan test

# Run specific test suite
php artisan test --filter Phase1
php artisan test --filter Phase2
php artisan test --filter AICreditsService

# Run with coverage
php artisan test --coverage
```

### Run Playwright Tests

```bash
# Run all E2E tests
npm run test:e2e

# Run with UI mode (interactive)
npm run test:e2e:ui

# Run in headed mode (see browser)
npm run test:e2e:headed

# Debug mode
npm run test:e2e:debug
```

## Test Coverage

### Phase 1: Database & Models ✅
- ✅ All migration tables exist
- ✅ Table columns are correct
- ✅ Model CRUD operations
- ✅ Model relationships work
- ✅ Company → AI Credits relationship
- ✅ Company → Module Subscriptions relationship
- ✅ Package → AI Credits relationship

### Phase 2: Services ✅
- ✅ AICreditsService: Balance management, purchases, promotions
- ✅ ModuleMarketplaceService: Module browsing, access checking
- ✅ ModuleAddonService: Add-on management, dependencies
- ✅ AICreditPromotionService: Promotion eligibility
- ✅ AIUsageTrackingService: Usage logging, statistics
- ✅ PackageBillingService: Unified billing calculations

### Phase 3: Controllers (Pending)
- ⏳ ModuleMarketplaceController
- ⏳ ModuleAddonController
- ⏳ AICreditsController
- ⏳ BillingController updates

### E2E Tests ✅
- ✅ Database migration verification
- ✅ Service integration checks
- ✅ Complete user flows
- ✅ Error handling

## Test Database

Tests use a separate test database. Configure in `phpunit.xml`:

```xml
<server name="DB_DATABASE" value="craveva_test"/>
```

Or use SQLite in-memory:

```xml
<server name="DB_CONNECTION" value="sqlite"/>
<server name="DB_DATABASE" value=":memory:"/>
```

## Continuous Integration

Tests are designed to run in CI/CD pipelines:

```yaml
# Example GitHub Actions
- name: Run PHPUnit Tests
  run: php artisan test

- name: Run Playwright Tests
  run: npm run test:e2e
```

## Writing New Tests

### PHPUnit Test Template

```php
<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MyFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_my_feature_works(): void
    {
        // Arrange
        // Act
        // Assert
    }
}
```

### Playwright Test Template

```javascript
import { test, expect } from '@playwright/test';

test.describe('My Feature', () => {
  test('should work correctly', async ({ page }) => {
    await page.goto('/my-page');
    await expect(page).toHaveTitle(/My Page/);
  });
});
```

## Troubleshooting

### Tests Failing?

1. **Database Issues**: Make sure test database exists and migrations are run
   ```bash
   php artisan migrate --database=testing
   ```

2. **Playwright Issues**: Reinstall browsers
   ```bash
   npx playwright install
   ```

3. **Service Errors**: Check that all services are properly instantiated
   - Verify dependency injection
   - Check service constructors

### Common Issues

- **"Table doesn't exist"**: Run migrations for test database
- **"Class not found"**: Run `composer dump-autoload`
- **"Playwright timeout"**: Increase timeout in `playwright.config.js`

## Next Steps

1. ✅ Phase 1 & 2 tests are complete
2. ⏳ Create Phase 3 controller tests
3. ⏳ Add more E2E scenarios
4. ⏳ Add performance tests
5. ⏳ Add integration tests for payment gateways

