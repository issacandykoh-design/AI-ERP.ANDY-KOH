#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Test database connection and examine database structure
"""

import mysql.connector
from dotenv import load_dotenv
import os

def test_database_connection():
    """Test database connection and show basic info"""
    load_dotenv()
    
    config = {
        'host': os.getenv('MYSQL_HOST', 'localhost'),
        'port': int(os.getenv('MYSQL_PORT', 3306)),
        'database': os.getenv('MYSQL_DATABASE', 'esp92032_REDACTED'),
        'user': os.getenv('MYSQL_USER', 'root'),
        'password': os.getenv('MYSQL_PASSWORD', ''),
        'charset': 'utf8mb4'
    }
    
    print('Database configuration:')
    for key, value in config.items():
        if key == 'password':
            print(f'{key}: {"***" if value else "(empty)"}')
        else:
            print(f'{key}: {value}')
    
    try:
        connection = mysql.connector.connect(**config)
        if connection.is_connected():
            print('\n✓ Database connection successful!')
            cursor = connection.cursor()
            
            # Get MySQL version
            cursor.execute('SELECT VERSION()')
            version = cursor.fetchone()
            print(f'MySQL version: {version[0]}')
            
            # Get database name
            cursor.execute('SELECT DATABASE()')
            db_name = cursor.fetchone()
            print(f'Current database: {db_name[0]}')
            
            # List all tables
            cursor.execute('SHOW TABLES')
            tables = cursor.fetchall()
            print(f'\nTotal tables found: {len(tables)}')
            print('Tables in database:')
            for table in tables[:20]:  # Show first 20 tables
                print(f'  - {table[0]}')
            if len(tables) > 20:
                print(f'  ... and {len(tables) - 20} more tables')
            
            # Check if invoices table exists
            cursor.execute("SHOW TABLES LIKE 'invoices'")
            invoices_table = cursor.fetchone()
            if invoices_table:
                print(f'\n✓ Found invoices table: {invoices_table[0]}')
                
                # Check invoices table structure
                cursor.execute('DESCRIBE invoices')
                columns = cursor.fetchall()
                print('Invoices table structure:')
                for col in columns:
                    print(f'  - {col[0]} ({col[1]})')
                
                # Check if invoices table has data
                cursor.execute('SELECT COUNT(*) FROM invoices')
                count = cursor.fetchone()
                print(f'Total records in invoices table: {count[0]}')
                
                # Check if invoices table has company_id column
                cursor.execute("SHOW COLUMNS FROM invoices LIKE 'company_id'")
                company_id_col = cursor.fetchone()
                if company_id_col:
                    print(f'✓ Found company_id column in invoices table')
                    
                    # Check company_id values
                    cursor.execute('SELECT DISTINCT company_id FROM invoices LIMIT 10')
                    company_ids = cursor.fetchall()
                    print(f'Company IDs in invoices table: {[row[0] for row in company_ids]}')
                else:
                    print('✗ No company_id column found in invoices table')
            else:
                print('\n✗ No invoices table found')
            
            cursor.close()
            connection.close()
        else:
            print('\n✗ Database connection failed!')
    except Exception as e:
        print(f'\n✗ Database connection error: {e}')

if __name__ == '__main__':
    test_database_connection()