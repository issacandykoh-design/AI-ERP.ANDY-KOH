#!/bin/bash

echo "========================================"
echo "Running Comprehensive Test Suite"
echo "========================================"
echo ""

echo "[1/3] Running PHPUnit Tests..."
php artisan test
if [ $? -ne 0 ]; then
    echo ""
    echo "ERROR: PHPUnit tests failed!"
    exit 1
fi
echo ""

echo "[2/3] Installing Playwright (if needed)..."
npm install
if [ $? -ne 0 ]; then
    echo "WARNING: npm install failed, continuing anyway..."
fi
echo ""

echo "[3/3] Running Playwright E2E Tests..."
npx playwright test
if [ $? -ne 0 ]; then
    echo ""
    echo "ERROR: Playwright tests failed!"
    exit 1
fi
echo ""

echo "========================================"
echo "All tests completed successfully!"
echo "========================================"

