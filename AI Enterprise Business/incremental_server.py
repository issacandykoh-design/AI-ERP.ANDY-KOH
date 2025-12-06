#!/usr/bin/env python3
from flask import Flask, jsonify, request
from flask_cors import CORS
import sys
import os
import traceback

# Add current directory to path
sys.path.append(os.path.dirname(os.path.abspath(__file__)))

app = Flask(__name__)
CORS(app)

# Global variables for components
analyzer = None
chart_generator = None

def initialize_components():
    """Initialize components one by one"""
    global analyzer, chart_generator
    
    try:
        print("Step 1: Importing main_mysql...")
        import main_mysql
        print("✅ main_mysql imported")
        
        print("Step 2: Importing multi_sql_flow...")
        import multi_sql_flow
        print("✅ multi_sql_flow imported")
        
        print("Step 3: Creating E3MySQLAnalyzer...")
        from main_mysql import E3MySQLAnalyzer
        analyzer = E3MySQLAnalyzer()
        print("✅ E3MySQLAnalyzer created")
        
        print("Step 4: Creating ChartGenerator...")
        from chart_generator import ChartGenerator
        chart_generator = ChartGenerator()
        print("✅ ChartGenerator created")
        
        print("Step 5: Testing other imports...")
        import excel_templates
        import data_reports_templates
        import excel_generator
        print("✅ Other modules imported")
        
        return True
        
    except Exception as e:
        print(f"❌ Component initialization failed: {e}")
        traceback.print_exc()
        return False

@app.route('/api/health')
def health():
    return jsonify({
        'status': 'healthy', 
        'message': 'Incremental server running',
        'analyzer_ready': analyzer is not None,
        'chart_generator_ready': chart_generator is not None
    })

@app.route('/api/conversations')
def conversations():
    return jsonify([])

@app.route('/api/ask', methods=['POST'])
def ask():
    try:
        data = request.get_json()
        question = data.get('question', '')
        session_id = data.get('session_id', 'test-session')
        
        if analyzer is None:
            return jsonify({
                'error': 'Analyzer not initialized',
                'response': f'Question received: {question}',
                'session_id': session_id
            }), 500
        
        # Try to use the actual analyzer
        try:
            print(f"Processing question with analyzer: {question}")
            # Simple test without full v2 flow
            response = {
                'response': f'Processed with analyzer: {question}',
                'session_id': session_id,
                'analysis': 'Analyzer is working correctly.',
                'has_chart': False,
                'sql_queries': [],
                'execution_results': []
            }
            return jsonify(response)
            
        except Exception as e:
            print(f"Analyzer error: {e}")
            return jsonify({
                'error': f'Analyzer error: {str(e)}',
                'response': f'Question received: {question}',
                'session_id': session_id
            }), 500
        
    except Exception as e:
        return jsonify({'error': str(e)}), 500

if __name__ == '__main__':
    print("Starting incremental Flask server...")
    print("Initializing components...")
    
    success = initialize_components()
    
    if success:
        print("\n✅ All components initialized successfully!")
    else:
        print("\n❌ Component initialization failed, but server will still start")
    
    print("\nAvailable endpoints:")
    print("  GET  /api/health")
    print("  GET  /api/conversations") 
    print("  POST /api/ask")
    print("\nServer running on http://localhost:5000")
    
    app.run(debug=True, host='0.0.0.0', port=5000)