#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Debug Excel mode specifically
"""

import os
import sys

# Sample data for testing
sample_data = [
    {"month": "January", "sales": 15000, "expenses": 8000},
    {"month": "February", "sales": 18000, "expenses": 9000},
    {"month": "March", "sales": 22000, "expenses": 10000}
]
analysis_text = "Sales show an upward trend with 47% growth from January to March."

def test_excel_mode():
    """Test Excel Mode"""
    print("Testing Excel Mode...")
    try:
        from excel_generator import ExcelGenerator
        excel_gen = ExcelGenerator()
        print(f"ExcelGenerator created: {excel_gen}")
        
        # Check available methods
        methods = [method for method in dir(excel_gen) if not method.startswith('_')]
        print(f"Available methods: {methods}")
        
        # Try to call generate_sales_excel
        print("Calling generate_sales_excel...")
        excel_path = excel_gen.generate_sales_excel(sample_data, analysis_text, "test_session_123")
        print(f"Excel path returned: {excel_path}")
        
        if excel_path and os.path.exists(excel_path):
            print(f"✅ Excel report generated: {excel_path}")
            return True
        else:
            print("❌ Excel generation failed - file not found")
            return False
            
    except Exception as e:
        import traceback
        print(f"❌ Excel mode failed: {e}")
        print("Full traceback:")
        traceback.print_exc()
        return False

if __name__ == "__main__":
    test_excel_mode()