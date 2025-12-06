#!/usr/bin/env python3
from flask import Flask, jsonify, request
from flask_cors import CORS
import sys
import os

# Add current directory to path
sys.path.append(os.path.dirname(os.path.abspath(__file__)))

app = Flask(__name__)
CORS(app)

@app.route('/api/health')
def health():
    return jsonify({'status': 'healthy', 'message': 'Minimal server running'})

@app.route('/api/conversations')
def conversations():
    return jsonify([])

@app.route('/api/ask', methods=['POST'])
def ask():
    try:
        data = request.get_json()
        question = data.get('question', '')
        session_id = data.get('session_id', 'test-session')
        
        # Simple response without complex processing
        response = {
            'response': f'Received question: {question}',
            'session_id': session_id,
            'analysis': 'This is a test response from minimal server.',
            'has_chart': False,
            'sql_queries': [],
            'execution_results': []
        }
        
        return jsonify(response)
        
    except Exception as e:
        return jsonify({'error': str(e)}), 500

if __name__ == '__main__':
    print("Starting minimal Flask server...")
    print("Available endpoints:")
    print("  GET  /api/health")
    print("  GET  /api/conversations") 
    print("  POST /api/ask")
    print("\nServer running on http://localhost:5000")
    
    app.run(debug=True, host='0.0.0.0', port=5000)