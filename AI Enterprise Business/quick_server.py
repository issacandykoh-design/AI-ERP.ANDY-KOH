#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Quick server for testing - bypasses Vanna training initialization
"""

import os
import sys
from flask import Flask, request, jsonify, Response
from flask_cors import CORS
import json
from datetime import datetime

# Add the current directory to Python path
current_dir = os.path.dirname(os.path.abspath(__file__))
sys.path.insert(0, current_dir)

print("🔄 Starting quick server...")

# Import multi_sql_flow functions and analyzer for v2 endpoints
try:
    from multi_sql_flow import ask_and_analyze_v2, ask_and_analyze_v2_stream, generate_sql_multi_decompose
    print("✅ Successfully imported multi_sql_flow functions")
    
    # Try to import and initialize analyzer (without Vanna training)
    from analyzer_no_training import E3MySQLAnalyzerNoTraining
    print("✅ Successfully imported E3MySQLAnalyzerNoTraining")
    
    # Create analyzer instance
    analyzer = E3MySQLAnalyzerNoTraining()
    print("✅ Successfully initialized analyzer")
    multi_sql_flow_available = True
except Exception as e:
    print(f"❌ Failed to import multi_sql_flow or analyzer: {e}")
    multi_sql_flow_available = False
    analyzer = None

# Create Flask app
app = Flask(__name__)
CORS(app)

print("✅ Flask app created with CORS")

@app.route('/api/health', methods=['GET'])
def health_check():
    """Health check endpoint"""
    return jsonify({
        'status': 'healthy',
        'timestamp': datetime.now().isoformat(),
        'multi_sql_flow_available': multi_sql_flow_available
    })

@app.route('/api/conversations', methods=['GET'])
def get_conversations():
    """Get conversation list"""
    # Return empty array for consistency with frontend expectations
    return jsonify([])

@app.route('/api/ask', methods=['POST'])
def ask_question():
    """Ask question endpoint - using MultiSQLFlow if available"""
    try:
        data = request.get_json()
        question = data.get('question', '')
        
        if not question:
            return jsonify({'error': 'Question is required'}), 400
        
        if multi_sql_flow_available and analyzer:
            # Use multi_sql_flow functions for v2 processing
            try:
                query_data, analysis, session_id = ask_and_analyze_v2(
                    analyzer=analyzer,
                    question=question,
                    create_session=True
                )
                
                # Format response similar to web_api.py
                result = {
                    'answer': analysis,
                    'sql': getattr(analyzer, 'last_sql_list', [''])[0] if hasattr(analyzer, 'last_sql_list') else '',
                    'data': query_data.to_dict('records') if query_data is not None else [],
                    'conversation_id': session_id,
                    'chart_config': None  # Chart generation would be handled separately
                }
                return jsonify(result)
            except Exception as e:
                return jsonify({
                    'error': f'Multi-SQL flow processing failed: {str(e)}',
                    'question': question
                }), 500
        else:
            # Fallback response
            return jsonify({
                'answer': f'Quick server received question: {question}',
                'sql': 'SELECT 1 as test',
                'data': [{'test': 1}],
                'chart_config': None,
                'conversation_id': data.get('conversation_id', 'test_conv')
            })
            
    except Exception as e:
        return jsonify({'error': f'Failed to process question: {str(e)}'}), 500

@app.route('/api/ask/stream', methods=['GET'])
def ask_question_stream():
    """Streaming ask question endpoint using Server-Sent Events (SSE)"""
    try:
        question = request.args.get('question', '').strip()
        session_id = request.args.get('session_id', '').strip() or None
        
        if not question:
            return jsonify({'error': 'Question is required'}), 400
        
        # Helper to format SSE with explicit event type
        def sse(event_name, data):
            return f"event: {event_name}\n" + f"data: {json.dumps(data)}\n\n"

        def generate():
            if multi_sql_flow_available and analyzer:
                try:
                    # Use MultiSQLFlow streaming
                    done_sent = False
                    final_session_id = None
                    for chunk in ask_and_analyze_v2_stream(
                        analyzer=analyzer,
                        question=question,
                        create_session=True,
                        session_id=session_id
                    ):
                        # Map internal chunk types to frontend SSE event names
                        ctype = chunk.get('type') if isinstance(chunk, dict) else None
                        if ctype == 'analysis_token':
                            yield sse('analysis_token', {'text': chunk.get('content', '')})
                        elif ctype == 'analysis_complete':
                            yield sse('analysis_final', {'text': chunk.get('content', '')})
                        elif ctype == 'session_saved':
                            final_session_id = chunk.get('session_id')
                            yield sse('done', {'session_id': final_session_id})
                            done_sent = True
                        elif ctype == 'error':
                            yield sse('analysis_final', {'text': chunk.get('content', 'Streaming error')})
                            yield sse('done', {'error': 'stream_failed'})
                            done_sent = True
                        elif ctype == 'execution_complete':
                            # Optional progress event; basic payload to satisfy listeners
                            yield sse('execution_progress', {
                                'index': 1,
                                'name': 'Query 1',
                                'rows': 0,
                                'columns': []
                            })
                        elif ctype == 'complete':
                            if not done_sent:
                                yield sse('done', {'session_id': final_session_id or session_id})
                                done_sent = True
                        else:
                            # Fallback: forward token-like content
                            yield sse('analysis_token', {'text': json.dumps(chunk)})

                    if not done_sent:
                        yield sse('done', {'session_id': final_session_id or session_id})
                except Exception as e:
                    yield sse('analysis_final', {'text': f'Streaming failed: {str(e)}'})
                    yield sse('done', {'error': 'stream_failed'})
            else:
                # Fallback streaming response
                yield sse('analysis_token', {'text': f'Quick server received: {question}'})
                yield sse('analysis_final', {'text': f'Quick server received: {question}'})
                yield sse('done', {})
        
        # Return SSE response with proper headers
        response = Response(generate(), mimetype='text/event-stream')
        response.headers['Cache-Control'] = 'no-cache'
        response.headers['X-Accel-Buffering'] = 'no'
        response.headers['Connection'] = 'keep-alive'
        response.headers['Access-Control-Allow-Origin'] = '*'
        response.headers['Access-Control-Allow-Headers'] = 'Content-Type'
        return response
        
    except Exception as e:
        return jsonify({'error': f'Failed to process streaming question: {str(e)}'}), 500

@app.route('/api/followup', methods=['POST'])
def ask_followup():
    """Follow-up question endpoint"""
    try:
        data = request.get_json()
        question = data.get('question', '')
        conversation_id = data.get('conversation_id', '')
        
        if not question:
            return jsonify({'error': 'Question is required'}), 400
        
        if multi_sql_flow_available:
            try:
                from multi_sql_flow import ask_followup_v2
                result = ask_followup_v2(
                    question=question,
                    conversation_id=conversation_id,
                    user_id=data.get('user_id', 'test_user')
                )
                return jsonify(result)
            except Exception as e:
                return jsonify({
                    'error': f'Follow-up processing failed: {str(e)}',
                    'question': question
                }), 500
        else:
            return jsonify({
                'answer': f'Quick server follow-up: {question}',
                'conversation_id': conversation_id
            })
            
    except Exception as e:
        return jsonify({'error': f'Failed to process follow-up: {str(e)}'}), 500

@app.route('/api/followup/stream', methods=['GET'])
def followup_question_stream():
    """Streaming follow-up endpoint using SSE events: analysis_token, analysis_final, done"""
    try:
        question = request.args.get('question', '').strip()
        session_id = request.args.get('session_id', '').strip()

        if not question:
            return jsonify({'error': 'Question is required'}), 400
        if not session_id:
            return jsonify({'error': 'Session ID is required'}), 400

        def sse(event_name, data):
            return f"event: {event_name}\n" + f"data: {json.dumps(data)}\n\n"

        def generate():
            try:
                if not analyzer:
                    yield sse('analysis_final', {'text': 'Analyzer is not initialized'})
                    yield sse('done', {'error': 'no_analyzer'})
                    return

                # Reconstruct dataset from conversation context
                df = analyzer.conversation_manager.session_manager.reconstruct_query_result(session_id)
                datasets = [{
                    'name': 'Follow-up Dataset',
                    'purpose': 'Reconstructed from prior conversation',
                    'sql': '',
                    'df': df
                }]

                accumulated = ''
                for token in analyzer.analyze_multi_data(datasets, question, mode=None, session_id=session_id, stream=True):
                    if token:
                        accumulated += token
                        yield sse('analysis_token', {'text': token})

                # Persist follow-up into conversation history
                try:
                    context = analyzer.conversation_manager.get_conversation_details(session_id)
                    if context:
                        followup_data = {
                            'timestamp': datetime.now().isoformat(),
                            'question': question,
                            'analysis': accumulated,
                            'type': 'followup'
                        }
                        context.conversation_history.append(followup_data)
                        analyzer.conversation_manager.session_manager.update_session(session_id, context)
                except Exception:
                    pass

                yield sse('analysis_final', {'text': accumulated})
                yield sse('done', {'session_id': session_id})

            except Exception as e:
                yield sse('analysis_final', {'text': f'Follow-up streaming error: {str(e)}'})
                yield sse('done', {'error': 'stream_failed'})

        # Return SSE response with proper headers
        response = Response(generate(), mimetype='text/event-stream')
        response.headers['Cache-Control'] = 'no-cache'
        response.headers['X-Accel-Buffering'] = 'no'
        response.headers['Connection'] = 'keep-alive'
        response.headers['Access-Control-Allow-Origin'] = '*'
        response.headers['Access-Control-Allow-Headers'] = 'Content-Type'
        return response
    except Exception as e:
        return jsonify({'error': f'Failed to stream follow-up: {str(e)}'}), 500

if __name__ == '__main__':
    print("🚀 Starting Flask server on http://localhost:5000")
    print("📋 Available endpoints:")
    print("   GET  /api/health")
    print("   GET  /api/conversations") 
    print("   POST /api/ask")
    print("   GET  /api/ask/stream")
    print("   POST /api/followup")
    print("   GET  /api/followup/stream")
    
    app.run(debug=True, host='0.0.0.0', port=5000)