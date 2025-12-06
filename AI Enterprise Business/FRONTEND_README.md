# Craveva AI Enterprise Business - React Frontend

A ChatGPT-style web interface for Craveva AI Enterprise Business, built with React and Flask.

## Features

- 🎨 ChatGPT-like dark theme interface
- 💬 Real-time conversation with AI data analyst
- 📊 Interactive data visualization
- 📱 Responsive design for mobile and desktop
- 💾 Conversation history management
- 📄 Export conversations to PDF/CSV

## Quick Start

### Option 1: Use the Batch Script (Windows)
```bash
# Simply double-click or run:
start_app.bat
```

### Option 2: Manual Setup

#### Backend Setup
1. Install Python dependencies:
```bash
pip install -r requirements.txt
```

2. Start the Flask API server:
```bash
python web_api.py
```
The backend will be available at `http://localhost:5000`

#### Frontend Setup
1. Navigate to the frontend directory:
```bash
cd frontend
```

2. Install Node.js dependencies:
```bash
npm install
```

3. Start the React development server:
```bash
npm start
```
The frontend will be available at `http://localhost:3000`

## Project Structure

```
vanna/
├── frontend/                 # React frontend application
│   ├── public/              # Static files
│   ├── src/                 # React source code
│   │   ├── components/      # React components
│   │   │   ├── Sidebar.js   # Conversation history sidebar
│   │   │   └── ChatInterface.js # Main chat interface
│   │   ├── services/        # API services
│   │   │   └── api.js       # Backend API integration
│   │   ├── App.js           # Main App component
│   │   ├── App.css          # ChatGPT-style CSS
│   │   └── index.js         # React entry point
│   └── package.json         # Node.js dependencies
├── web_api.py               # Flask API server
├── main.py                  # Original analyzer (backend logic)
├── conversation_manager.py  # Conversation management
├── export_manager.py        # Export functionality
├── requirements.txt         # Python dependencies
└── start_app.bat           # Windows startup script
```

## API Endpoints

The Flask backend provides the following REST API endpoints:

- `GET /api/health` - Health check
- `GET /api/conversations` - List all conversations
- `GET /api/conversations/<id>` - Get conversation details
- `POST /api/ask` - Ask a new question
- `POST /api/followup` - Ask a followup question
- `POST /api/export` - Export conversation

## Environment Variables

Make sure you have a `.env` file with your OpenAI API key:

```
OPENAI_API_KEY=***REMOVED***
OPENAI_MODEL=gpt-4
# Optional: set a faster model for Flash mode to speed up responses
OPENAI_FAST_MODEL=gpt-4o-mini

# If using DeepSeek as provider
# API_PROVIDER=deepseek
# DEEPSEEK_API_KEY=sk-7ac7e441f74f443bac0302f69aca6535
# DEEPSEEK_MODEL=deepseek-chat
# Optional: set a faster DeepSeek model for Flash mode
# DEEPSEEK_FAST_MODEL=deepseek-chat
```

## Usage

1. Open your browser and go to `http://localhost:3000`
2. You'll see a ChatGPT-like interface with:
   - Left sidebar: Conversation history
   - Main area: Chat interface with example prompts
3. Click "New Chat" or select an existing conversation
4. Ask questions about your enterprise data
5. View AI-generated SQL queries and data analysis
6. Export conversations as needed

## Example Questions

- "What was the total sales revenue for March 2024?"
- "Which coffee products are the most popular?"
- "What's the distribution of payment methods used?"
- "Show me the daily sales trends for the last month"

## Troubleshooting

### Backend Issues
- Make sure Python dependencies are installed: `pip install -r requirements.txt`
- Check that your `.env` file contains a valid OpenAI API key
- Verify the database file `my_database.db` exists

### Frontend Issues
- Make sure Node.js is installed (version 14 or higher)
- Install dependencies: `cd frontend && npm install`
- Check that the backend is running on port 5000

### CORS Issues
- The Flask backend includes CORS headers for development
- Make sure both servers are running on their default ports

## Development

To modify the interface:

1. **Styling**: Edit `frontend/src/App.css` for ChatGPT-like themes
2. **Components**: Modify files in `frontend/src/components/`
3. **API**: Update `frontend/src/services/api.js` for new endpoints
4. **Backend**: Extend `web_api.py` for additional functionality

## Production Deployment

For production deployment:

1. Build the React app: `cd frontend && npm run build`
2. Serve the built files with a web server (nginx, Apache, etc.)
3. Configure the Flask app for production (use gunicorn, etc.)
4. Set appropriate environment variables and security settings