from flask import Flask, jsonify

app = Flask(__name__)

# Route gốc
@app.route("/")
def index():
    return jsonify({"message": "API is running on port 5000"})

# Route test
@app.route("/hello")
def hello():
    return jsonify({"message": "Hello from Flask!"})

if __name__ == "__main__":
    # Chạy trên tất cả IP của server (0.0.0.0) và port 5000
    app.run(host="0.0.0.0", port=5000)

