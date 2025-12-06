#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Quick test to get final status summary
"""

import os
import json
from dotenv import load_dotenv
from openai import OpenAI

# Load environment variables
load_dotenv()

# Sample data for testing
sample_data = [
    {"month": "January", "sales": 15000, "expenses": 8000},
    {"month": "February", "sales": 18000, "expenses": 9000},
    {"month": "March", "sales": 22000, "expenses": 10000}
]
analysis_text = "Sales show an upward trend with 47% growth from January to March."

def quick_test():
    """Quick test of all modes"""
    results = []
    
    # Test Auto Mode
    try:
        from main import SimpleCoffeeAnalyzer
        analyzer = SimpleCoffeeAnalyzer()
        results.append(("Auto", True))
    except Exception as e:
        print(f"Auto Mode Error: {e}")
        results.append(("Auto", False))
    
    # Test DataReports Mode
    try:
        from export_manager import ExportManager
        export_manager = ExportManager()
        export_data = {
            "query_result": sample_data,
            "analysis": analysis_text,
            "question": "Sales Analysis Test"
        }
        pdf_path = export_manager.export_to_pdf_report(
            question="Sales Analysis Test",
            sql_query="SELECT * FROM sales_data",
            data=sample_data,
            analysis=analysis_text,
            filename="Sales_Report"
        )
        csv_path = export_manager.export_to_csv(sample_data, "Sales_Data")
        results.append(("DataReports", pdf_path and csv_path and os.path.exists(pdf_path) and os.path.exists(csv_path)))
    except Exception as e:
        print(f"DataReports Error: {e}")
        results.append(("DataReports", False))
    
    # Test Charts Mode
    try:
        from chart_generator import ChartGenerator
        chart_gen = ChartGenerator()
        chart_spec = chart_gen.generate_chart(sample_data, 'line', 'Sales Trend', 'month', 'sales')
        results.append(("Charts", chart_spec is not None))
    except Exception as e:
        results.append(("Charts", False))
    
    # Test Graphics Mode
    try:
        from graphics_generator import GraphicsGenerator
        graphics_gen = GraphicsGenerator()
        graphics_path = graphics_gen.generate_graphics_by_type("infographic", sample_data, analysis_text)
        results.append(("Graphics", graphics_path and os.path.exists(graphics_path)))
    except Exception as e:
        results.append(("Graphics", False))
    
    # Test Excel Mode
    try:
        import pandas as pd
        from excel_generator import ExcelGenerator
        excel_gen = ExcelGenerator()
        df_data = pd.DataFrame(sample_data)
        result = excel_gen.generate_sales_excel(df_data, analysis_text, "test_session_123")
        if isinstance(result, tuple) and len(result) == 2:
            excel_path, filename = result
            results.append(("Excel", excel_path and os.path.exists(excel_path)))
        else:
            print(f"Excel Mode Error: Unexpected result format {result}")
            results.append(("Excel", False))
    except Exception as e:
        print(f"Excel Mode Error: {e}")
        results.append(("Excel", False))
    
    # Test PPT Mode
    try:
        from ppt_generator import PPTGenerator
        ppt_gen = PPTGenerator()
        analysis_data = {
            "data": sample_data,
            "analysis": analysis_text,
            "title": "Sales Analysis"
        }
        ppt_path = ppt_gen.generate_products_services_ppt(analysis_data, "test_session_123")
        results.append(("PPT", ppt_path and os.path.exists(ppt_path)))
    except Exception as e:
        print(f"PPT Mode Error: {e}")
        results.append(("PPT", False))
    
    # Summary
    print("\n" + "="*60)
    print("FINAL STATUS SUMMARY:")
    print("="*60)
    working_modes = 0
    for mode_name, status in results:
        status_icon = "✅" if status else "❌"
        print(f"{status_icon} {mode_name}: {'WORKING' if status else 'FAILED'}")
        if status:
            working_modes += 1
    
    print(f"\nOverall Status: {working_modes}/6 modes working")
    print("="*60)

if __name__ == "__main__":
    quick_test()