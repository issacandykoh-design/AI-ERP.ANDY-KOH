#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Comprehensive test for all 6 output modes with Deepseek API
"""

import os
import sys
import json
from dotenv import load_dotenv
from openai import OpenAI

# Load environment variables
load_dotenv()

def test_deepseek_connection():
    """Test basic Deepseek API connection"""
    try:
        client = OpenAI(
            api_key=os.getenv('DEEPSEEK_API_KEY'),
            base_url=os.getenv('DEEPSEEK_BASE_URL', 'https://api.deepseek.com')
        )
        
        response = client.chat.completions.create(
            model="deepseek-chat",
            messages=[
                {"role": "system", "content": "You are a helpful assistant."},
                {"role": "user", "content": "Hello, can you hear me?"}
            ]
        )
        
        print("✅ Deepseek API connection successful")
        print(f"Response: {response.choices[0].message.content}")
        return True
    except Exception as e:
        print(f"❌ Deepseek API connection failed: {e}")
        return False

def test_auto_mode():
    """Test Auto Mode"""
    print("\n1. Testing Auto Mode...")
    try:
        from main import SimpleCoffeeAnalyzer
        analyzer = SimpleCoffeeAnalyzer()
        print("✅ Auto mode initialization successful")
        return True
    except Exception as e:
        print(f"❌ Auto mode failed: {e}")
        return False

def test_datareports_mode():
    """Test DataReports Mode"""
    print("\n2. Testing DataReports Mode...")
    try:
        from export_manager import ExportManager
        export_manager = ExportManager()
        
        # Test PDF generation
        try:
            pdf_path = export_manager.export_to_pdf_report(
                question="Sales Analysis Test",
                sql_query="SELECT * FROM sales_data",
                data=sample_data,
                analysis=analysis_text,
                filename="Sales_Report"
            )
            if pdf_path and os.path.exists(pdf_path):
                print(f"✅ PDF report generated: {pdf_path}")
            else:
                print("❌ PDF report generation failed")
        except Exception as e:
            print(f"❌ PDF generation failed: {e}")
        
        # Test CSV generation
        try:
            csv_path = export_manager.export_to_csv(sample_data, "Sales_Data")
            if csv_path and os.path.exists(csv_path):
                print(f"✅ CSV report generated: {csv_path}")
            else:
                print("❌ CSV report generation failed")
        except Exception as e:
            print(f"❌ CSV generation failed: {e}")
            
        return True
    except Exception as e:
        print(f"❌ DataReports mode failed: {e}")
        return False

def test_charts_mode():
    """Test Charts Mode"""
    print("\n3. Testing Charts Mode...")
    try:
        from chart_generator import ChartGenerator
        chart_gen = ChartGenerator()
        chart_spec = chart_gen.generate_chart(sample_data, 'line', 'Sales Trend', 'month', 'sales')
        if chart_spec:
            print("✅ Chart specification generated successfully")
            print(f"Chart type: {chart_spec.get('mark', {}).get('type', 'unknown')}")
            return True
        else:
            print("❌ Chart generation failed")
            return False
    except Exception as e:
        print(f"❌ Charts mode failed: {e}")
        return False

def test_graphics_mode():
    """Test Graphics Mode"""
    print("\n4. Testing Graphics Mode...")
    try:
        from graphics_generator import GraphicsGenerator
        graphics_gen = GraphicsGenerator()
        graphics_path = graphics_gen.generate_graphics_by_type("infographic", sample_data, analysis_text)
        if graphics_path and os.path.exists(graphics_path):
            print(f"✅ Graphics generated: {graphics_path}")
            return True
        else:
            print("❌ Graphics generation failed")
            return False
    except Exception as e:
        print(f"❌ Graphics mode failed: {e}")
        return False

def test_excel_mode():
    """Test Excel Mode"""
    print("\n5. Testing Excel Mode...")
    try:
        import pandas as pd
        from excel_generator import ExcelGenerator
        excel_gen = ExcelGenerator()
        # Convert sample data to DataFrame for proper Excel generation
        df_data = pd.DataFrame(sample_data)
        result = excel_gen.generate_sales_excel(df_data, analysis_text, "test_session_123")
        # The method returns (filepath, filename) tuple
        if isinstance(result, tuple) and len(result) == 2:
            excel_path, filename = result
            if excel_path and os.path.exists(excel_path):
                print(f"✅ Excel report generated: {excel_path}")
                return True
            else:
                print("❌ Excel generation failed - file not found")
                return False
        else:
            print(f"❌ Excel generation failed - unexpected return format: {result}")
            return False
    except Exception as e:
        print(f"❌ Excel mode failed: {e}")
        return False

def test_ppt_mode():
    """Test PPT Mode"""
    print("\n6. Testing PPT Mode...")
    try:
        from ppt_generator import PPTGenerator
        ppt_gen = PPTGenerator()
        # Convert sample data to analysis format expected by PPT generator
        analysis_data = {
            "data": sample_data,
            "analysis": analysis_text,
            "title": "Sales Analysis"
        }
        ppt_path = ppt_gen.generate_products_services_ppt(analysis_data, "test_session_123")
        if ppt_path and os.path.exists(ppt_path):
            print(f"✅ PPT generated: {ppt_path}")
            return True
        else:
            print("❌ PPT generation failed")
            return False
    except Exception as e:
        print(f"❌ PPT mode failed: {e}")
        return False

def main():
    """Main test function"""
    print("=== Craveva AI Enterprise Business - Comprehensive Output Modes Test ===")
    
    # Sample data for testing
    global sample_data, analysis_text
    sample_data = [
        {"month": "January", "sales": 15000, "expenses": 8000},
        {"month": "February", "sales": 18000, "expenses": 9000},
        {"month": "March", "sales": 22000, "expenses": 10000}
    ]
    analysis_text = "Sales show an upward trend with 47% growth from January to March."
    
    # Test Deepseek connection first
    if not test_deepseek_connection():
        print("Cannot proceed without Deepseek API connection")
        return
    
    # Test all output modes
    results = []
    results.append(("Auto", test_auto_mode()))
    results.append(("DataReports", test_datareports_mode()))
    results.append(("Charts", test_charts_mode()))
    results.append(("Graphics", test_graphics_mode()))
    results.append(("Excel", test_excel_mode()))
    results.append(("PPT", test_ppt_mode()))
    
    # Summary
    print("\n" + "="*60)
    print("SUMMARY OF ALL OUTPUT MODES:")
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
    main()