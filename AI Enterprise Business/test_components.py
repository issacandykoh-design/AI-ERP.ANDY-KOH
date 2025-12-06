#!/usr/bin/env python3
import sys
import traceback
import time

def test_component(name, test_func):
    """Test a component and report results"""
    print(f"\n{'='*50}")
    print(f"Testing: {name}")
    print('='*50)
    
    try:
        start_time = time.time()
        result = test_func()
        end_time = time.time()
        print(f"✅ {name} - SUCCESS ({end_time - start_time:.2f}s)")
        return True
    except Exception as e:
        print(f"❌ {name} - FAILED: {e}")
        traceback.print_exc()
        return False

def test_basic_imports():
    """Test basic Python imports"""
    from flask import Flask, request, jsonify
    from flask_cors import CORS
    return "Basic imports successful"

def test_main_mysql():
    """Test main_mysql import"""
    import main_mysql
    return "main_mysql imported"

def test_multi_sql_flow():
    """Test multi_sql_flow import"""
    import multi_sql_flow
    return "multi_sql_flow imported"

def test_analyzer_class():
    """Test E3MySQLAnalyzer class import"""
    from main_mysql import E3MySQLAnalyzer
    return "E3MySQLAnalyzer class imported"

def test_analyzer_creation():
    """Test E3MySQLAnalyzer instance creation"""
    from main_mysql import E3MySQLAnalyzer
    analyzer = E3MySQLAnalyzer()
    return f"E3MySQLAnalyzer created: {type(analyzer)}"

def test_chart_generator():
    """Test ChartGenerator"""
    from chart_generator import ChartGenerator
    chart_gen = ChartGenerator()
    return f"ChartGenerator created: {type(chart_gen)}"

def test_other_imports():
    """Test other module imports"""
    import excel_templates
    import data_reports_templates
    import excel_generator
    return "Other modules imported successfully"

def main():
    print("=== Component Testing ===")
    print(f"Python version: {sys.version}")
    
    tests = [
        ("Basic Imports", test_basic_imports),
        ("main_mysql Module", test_main_mysql),
        ("multi_sql_flow Module", test_multi_sql_flow),
        ("E3MySQLAnalyzer Class", test_analyzer_class),
        ("E3MySQLAnalyzer Creation", test_analyzer_creation),
        ("ChartGenerator", test_chart_generator),
        ("Other Imports", test_other_imports),
    ]
    
    results = []
    for name, test_func in tests:
        success = test_component(name, test_func)
        results.append((name, success))
        
        if not success:
            print(f"\n🛑 Stopping at failed component: {name}")
            break
    
    print(f"\n{'='*50}")
    print("SUMMARY")
    print('='*50)
    
    for name, success in results:
        status = "✅ PASS" if success else "❌ FAIL"
        print(f"{status} - {name}")
    
    total_tests = len(results)
    passed_tests = sum(1 for _, success in results if success)
    print(f"\nResults: {passed_tests}/{total_tests} tests passed")

if __name__ == "__main__":
    main()