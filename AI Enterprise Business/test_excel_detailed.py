#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Debug Excel mode specifically with detailed error info
"""

import os
import sys
import pandas as pd
import traceback

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
        print(f"ExcelGenerator created successfully")
        
        # Convert to DataFrame
        df_data = pd.DataFrame(sample_data)
        print(f"DataFrame created: {df_data.shape}")
        print(f"DataFrame columns: {df_data.columns.tolist()}")
        print(f"DataFrame head:\n{df_data.head()}")
        
        # Try to call generate_sales_excel
        print("Calling generate_sales_excel...")
        excel_path = excel_gen.generate_sales_excel(df_data, analysis_text, "test_session_123")
        print(f"Excel path returned: {excel_path}")
        
        if excel_path:
            print(f"File exists check: {os.path.exists(excel_path)}")
            if os.path.exists(excel_path):
                file_size = os.path.getsize(excel_path)
                print(f"File size: {file_size} bytes")
                print(f"✅ Excel report generated: {excel_path}")
                return True
            else:
                print("❌ Excel generation failed - file not found")
                return False
        else:
            print("❌ Excel generation failed - no path returned")
            return False
            
    except Exception as e:
        print(f"❌ Excel mode failed: {e}")
        print("Full traceback:")
        traceback.print_exc()
        return False

if __name__ == "__main__":
    test_excel_mode()