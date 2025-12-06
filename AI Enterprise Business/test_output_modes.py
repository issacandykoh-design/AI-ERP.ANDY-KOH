#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Test script for all 6 output modes with Deepseek API
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

def test_output_modes():
    """Test all 6 output modes"""
    
    # Sample data for testing
    sample_data = [
        {"month": "January", "sales": 15000, "expenses": 8000},
        {"month": "February", "sales": 18000, "expenses": 9000},
        {"month": "March", "sales": 22000, "expenses": 10000}
    ]
    
    analysis_text = "Sales show an upward trend with 47% growth from January to March."
    
    print("\n=== Testing Output Modes ===")
    
    # Test 1: Auto Mode
    print("\n1. Testing Auto Mode...")
    try:
        from main import SimpleCoffeeAnalyzer
        analyzer = SimpleCoffeeAnalyzer()
        print("✅ Auto mode initialization successful")
    except Exception as e:
        print(f"❌ Auto mode failed: {e}")
    
    # Test 2: DataReports Mode
    print("\n2. Testing DataReports Mode...")
    try:
        from export_manager import ExportManager
        export_manager = ExportManager()
        # Test PDF generation
        pdf_path = export_manager.generate_pdf_report(sample_data, analysis_text, "Sales Report")
        if pdf_path and os.path.exists(pdf_path):
            print(f"✅ PDF report generated: {pdf_path}")
        else:
            print("❌ PDF report generation failed")
        
        # Test CSV generation
        csv_path = export_manager.generate_csv_report(sample_data, "Sales Data")
        if csv_path and os.path.exists(csv_path):
            print(f"✅ CSV report generated: {csv_path}")
        else:
            print("❌ CSV report generation failed")
            
    except Exception as e:
        print(f"❌ DataReports mode failed: {e}")
    
    # Test 3: Charts Mode
    print("\n3. Testing Charts Mode...")
    try:
        from chart_generator import ChartGenerator
        chart_gen = ChartGenerator()
        chart_path = chart_gen.generate_chart_by_type("line", sample_data, "Sales Trend")
        if chart_path and os.path.exists(chart_path):
            print(f"✅ Chart generated: {chart_path}")
        else:
            print("❌ Chart generation failed")
    except Exception as e:
        print(f"❌ Charts mode failed: {e}")
    
    # Test 4: Graphics Mode
    print("\n4. Testing Graphics Mode...")
    try:
        from graphics_generator import GraphicsGenerator
        graphics_gen = GraphicsGenerator()
        graphics_path = graphics_gen.generate_graphics_by_type("infographic", sample_data, analysis_text)
        if graphics_path and os.path.exists(graphics_path):
            print(f"✅ Graphics generated: {graphics_path}")
        else:
            print("❌ Graphics generation failed")
    except Exception as e:
        print(f"❌ Graphics mode failed: {e}")
    
    # Test 5: Excel Mode
    print("\n5. Testing Excel Mode...")
    try:
        from excel_generator import ExcelGenerator
        excel_gen = ExcelGenerator()
        excel_path = excel_gen.generate_sales_excel(sample_data, analysis_text)
        if excel_path and os.path.exists(excel_path):
            print(f"✅ Excel report generated: {excel_path}")
        else:
            print("❌ Excel generation failed")
    except Exception as e:
        print(f"❌ Excel mode failed: {e}")
    
    # Test 6: PPT Mode
    print("\n6. Testing PPT Mode...")
    try:
        from ppt_generator import PPTGenerator
        ppt_gen = PPTGenerator()
        ppt_path = ppt_gen.generate_products_ppt(sample_data, analysis_text)
        if ppt_path and os.path.exists(ppt_path):
            print(f"✅ PPT generated: {ppt_path}")
        else:
            print("❌ PPT generation failed")
    except Exception as e:
        print(f"❌ PPT mode failed: {e}")

def main():
    """Main test function"""
    print("=== Craveva AI Enterprise Business - Output Modes Test ===")
    
    # Test Deepseek connection first
    if not test_deepseek_connection():
        print("Cannot proceed without Deepseek API connection")
        return
    
    # Test all output modes
    test_output_modes()
    
    print("\n=== Test Complete ===")

if __name__ == "__main__":
    main()