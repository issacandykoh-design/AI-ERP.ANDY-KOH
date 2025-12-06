#!/usr/bin/env python3
import sys
import traceback

print("=== Debug Server Startup ===")
print(f"Python version: {sys.version}")
print(f"Working directory: {sys.path[0]}")

try:
    print("\n1. Testing basic imports...")
    from flask import Flask, request, jsonify
    print("   ✅ Flask imported")
    
    from flask_cors import CORS
    print("   ✅ CORS imported")
    
    print("\n2. Testing main_mysql import...")
    import main_mysql
    print("   ✅ main_mysql imported")
    
    print("\n3. Testing multi_sql_flow import...")
    import multi_sql_flow
    print("   ✅ multi_sql_flow imported")
    
    print("\n4. Testing analyzer creation...")
    from main_mysql import E3MySQLAnalyzer
    print("   ✅ E3MySQLAnalyzer class imported")
    
    print("   Creating analyzer instance...")
    analyzer = E3MySQLAnalyzer()
    print("   ✅ E3MySQLAnalyzer created")
    
    print("\n5. Testing chart generator import...")
    from chart_generator import ChartGenerator
    chart_generator = ChartGenerator()
    print("   ✅ ChartGenerator created")
    
    print("\n6. Testing other imports...")
    import excel_templates
    print("   ✅ excel_templates imported")
    
    import data_reports_templates
    print("   ✅ data_reports_templates imported")
    
    import excel_generator
    print("   ✅ excel_generator imported")
    
    print("\n7. Testing Flask app creation...")
    app = Flask(__name__)
    CORS(app)
    print("   ✅ Flask app created")
    
    @app.route('/api/health')
    def health():
        return jsonify({'status': 'ok'})
    
    @app.route('/api/conversations')
    def conversations():
        return jsonify([])
    
    print("\n8. Starting Flask server...")
    print("   Server should start on http://localhost:5000")
    app.run(debug=True, host='0.0.0.0', port=5000)
    
except Exception as e:
    print(f"\n❌ Error: {e}")
    print("\nFull traceback:")
    traceback.print_exc()