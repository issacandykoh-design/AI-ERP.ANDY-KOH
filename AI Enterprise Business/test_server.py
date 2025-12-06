#!/usr/bin/env python3
import requests
import time
import json

def test_server():
    base_url = "http://localhost:5000"
    
    print("Testing Flask server...")
    
    # Wait for server to start
    time.sleep(2)
    
    try:
        # Test 1: Basic health check
        print("\n1. Testing basic connection...")
        response = requests.get(f"{base_url}/api/conversations", timeout=10)
        print(f"   Conversations endpoint: {response.status_code}")
        
        if response.status_code != 200:
            print(f"   Error: {response.text}")
            return False
            
        # Test 2: Ask endpoint with v2 flow
        print("\n2. Testing /api/ask endpoint...")
        test_data = {
            "question": "Show me sales data for this month",
            "session_id": "test-session-v2"
        }
        
        response = requests.post(f"{base_url}/api/ask", 
                               json=test_data, 
                               timeout=30)
        print(f"   Ask endpoint status: {response.status_code}")
        
        if response.status_code == 200:
            result = response.json()
            print(f"   Success! Response keys: {list(result.keys())}")
            if 'response' in result:
                print(f"   Response preview: {result['response'][:100]}...")
            return True
        else:
            print(f"   Error: {response.text}")
            return False
            
    except requests.exceptions.ConnectionError as e:
        print(f"   Connection failed: {e}")
        return False
    except Exception as e:
        print(f"   Unexpected error: {e}")
        return False

if __name__ == "__main__":
    success = test_server()
    if success:
        print("\n✅ All tests passed!")
    else:
        print("\n❌ Tests failed!")