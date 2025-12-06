#!/bin/bash

echo "========================================"
echo "Clearing Laravel Caches and Optimizing"
echo "========================================"
echo ""

# Check if PHP is available
if ! command -v php &> /dev/null; then
    echo "PHP not found in PATH. Please ensure PHP is installed and in your PATH."
    exit 1
fi

echo "[1/6] Clearing application cache..."
php artisan cache:clear
echo ""

echo "[2/6] Clearing configuration cache..."
php artisan config:clear
echo ""

echo "[3/6] Clearing route cache..."
php artisan route:clear
echo ""

echo "[4/6] Clearing view cache..."
php artisan view:clear
echo ""

echo "[5/6] Clearing compiled class cache..."
php artisan clear-compiled
echo ""

echo "[6/6] Optimizing application..."
php artisan optimize:clear
echo ""

echo "========================================"
echo "All caches cleared successfully!"
echo "========================================"
echo ""

