#!/usr/bin/env python3
import traceback
import sys
import os

print("=== Starting Debug Test ===")
print(f"Python version: {sys.version}")
print(f"Current directory: {os.getcwd()}")
print(f"Python path: {sys.path}")

try:
    print("Attempting to import main_mysql...")
    import main_mysql
    print("✅ Import successful!")
    
    print("Creating E3MySQLAnalyzer instance...")
    analyzer = main_mysql.E3MySQLAnalyzer()
    print("✅ E3MySQLAnalyzer created successfully!")
    
except Exception as e:
    print(f"❌ Error: {e}")
    traceback.print_exc()
    sys.exit(1)

print("=== Debug Test Completed ===")