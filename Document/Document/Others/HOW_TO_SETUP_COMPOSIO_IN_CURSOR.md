# How to Setup Composio (Rube) MCP Server in Cursor

## Overview

Composio's Rube is an MCP (Model Context Protocol) server that connects Cursor to over 500+ business applications including Google Drive, Gmail, Slack, Notion, GitHub, and many more. This allows you to interact with these services directly from Cursor's AI chat.

## 🚀 Quick Setup Steps

### Step 1: Add Rube MCP Server to Cursor

1. **Open Cursor Settings**
   - Press `Ctrl + ,` (Windows/Linux) or `Cmd + ,` (Mac) to open settings
   - Or go to: **File → Preferences → Settings**

2. **Navigate to MCP Settings**
   - Search for "MCP" in the settings search bar
   - Or go to: **Settings → Features → MCP**

3. **Add New MCP Server**
   - Click on **"Add MCP Server"** or **"MCP Tools"** in the sidebar
   - In the "Install MCP server?" dialog, enter:
     - **Name:** `rube` (or `composio`)
     - **Type:** `streamableHttp`
     - **URL:** `https://rube.app/?agent=cursor`
   - Click **"Confirm"** or **"Add"**

### Step 2: Authenticate and Connect Applications

1. **Check Server Status**
   - After adding, you should see Rube in your MCP servers list
   - It will show **"Needs login"** status

2. **Start Authentication**
   - Click on the Rube server entry
   - Click **"Authenticate"** or **"Login"** button
   - This will open a browser window

3. **Sign In to Composio**
   - Sign in with your Composio account (or create one at [composio.dev](https://composio.dev))
   - Authorize Cursor to access Composio

4. **Connect Your Tools**
   - After authentication, you'll see a list of available tools
   - Click **"Connect"** next to the tools you want to use:
     - ✅ **Google Drive** - Access and manage files
     - ✅ **Gmail** - Send emails, read messages
     - ✅ **Slack** - Send messages, manage channels
     - ✅ **Notion** - Create pages, read databases
     - ✅ **GitHub** - Manage repositories, issues
     - ✅ And 500+ more tools!

5. **OAuth Flow for Each Tool**
   - For each tool (like Google Drive), you'll be redirected to authorize access
   - Follow the OAuth flow in your browser
   - Grant the necessary permissions
   - Return to Cursor

### Step 3: Verify Setup

1. **Check MCP Tools**
   - Open Cursor's chat interface (`Ctrl + I` or `Cmd + I`)
   - You should see available MCP tools in the sidebar or tool list

2. **Test a Connection**
   - Try asking: `"List my recent Google Drive documents"`
   - Or: `"Show me my Gmail inbox"`
   - The AI should be able to interact with your connected tools

## 📋 Available Tools

Once connected, you can use these tools (and many more):

### Google Workspace
- **Google Drive** - List files, create folders, upload/download files
- **Gmail** - Send emails, read messages, manage labels
- **Google Calendar** - Create events, view calendar
- **Google Sheets** - Read/write spreadsheet data
- **Google Docs** - Create and edit documents

### Communication
- **Slack** - Send messages, manage channels, read messages
- **Microsoft Teams** - Similar to Slack
- **Discord** - Manage servers and channels

### Productivity
- **Notion** - Create pages, read databases, update content
- **Airtable** - Manage databases
- **Trello** - Manage boards and cards
- **Asana** - Manage tasks and projects

### Development
- **GitHub** - Manage repositories, issues, pull requests
- **GitLab** - Similar to GitHub
- **Jira** - Manage tickets and projects
- **Linear** - Issue tracking

### And 500+ More!
- CRM tools (Salesforce, HubSpot)
- Payment processors (Stripe, PayPal)
- Cloud storage (Dropbox, OneDrive)
- Social media (Twitter, LinkedIn)
- And many more!

## 💡 Usage Examples

### Example 1: Google Drive Operations

```
You: "List my recent Google Drive files"
AI: [Uses Google Drive MCP tool to fetch and display your files]

You: "Create a new folder in Google Drive called 'Project Files'"
AI: [Creates the folder using Google Drive API]

You: "Upload this file to Google Drive"
AI: [Uploads the file to your Drive]
```

### Example 2: Gmail Operations

```
You: "Send an email to john@example.com with subject 'Meeting'"
AI: [Uses Gmail MCP tool to send the email]

You: "Show me unread emails from today"
AI: [Fetches and displays your unread emails]

You: "Draft a reply to the latest email"
AI: [Creates a draft reply]
```

### Example 3: Slack Operations

```
You: "Send a message to #general channel saying 'Hello team!'"
AI: [Sends message via Slack MCP tool]

You: "What are the recent messages in #development channel?"
AI: [Fetches recent messages from the channel]
```

### Example 4: Notion Operations

```
You: "Create a new page in Notion with title 'Project Plan'"
AI: [Creates the page using Notion API]

You: "Add a task to my Notion database"
AI: [Adds the task to your database]
```

## 🔧 Troubleshooting

### Issue: "MCP Server Not Found"
**Solution:**
- Verify the URL is correct: `https://rube.app/?agent=cursor`
- Check your internet connection
- Restart Cursor

### Issue: "Needs Login" Status
**Solution:**
- Click on the server entry
- Click "Authenticate" or "Login"
- Complete the OAuth flow in your browser
- Make sure you're signed in to Composio

### Issue: "Tool Not Available"
**Solution:**
- Make sure you've connected the specific tool (e.g., Google Drive)
- Check that you've completed the OAuth flow for that tool
- Verify permissions were granted

### Issue: "Authentication Failed"
**Solution:**
- Try disconnecting and reconnecting the tool
- Check your Composio account status
- Verify API keys/credentials are valid

### Issue: "Too Many MCP Servers"
**Solution:**
- Cursor recommends not connecting more than 3 MCP servers simultaneously
- Disable unused MCP servers if needed
- Prioritize the tools you use most

## 🔐 Security & Privacy

### What Composio/Rube Can Access
- Only the tools you explicitly connect
- Permissions you grant during OAuth
- Data is handled according to Composio's privacy policy

### Best Practices
1. **Only connect tools you need** - Don't connect everything at once
2. **Review permissions** - Check what permissions each tool requests
3. **Disconnect unused tools** - Remove tools you no longer use
4. **Monitor access** - Check your Composio dashboard for active connections

## 📚 Additional Resources

- **Composio Website:** [composio.dev](https://composio.dev)
- **Rube MCP Documentation:** [playbooks.com/mcp/composiohq-rube](https://playbooks.com/mcp/composiohq-rube)
- **Composio API Docs:** [docs.composio.dev](https://docs.composio.dev)
- **Available Tools List:** Check Composio dashboard for full list

## 🎯 Quick Reference

### MCP Server Configuration
```
Name: rube
Type: streamableHttp
URL: https://rube.app/?agent=cursor
```

### Common Commands
- `"Connect to [Tool Name]"` - Connect a new tool
- `"List my [Tool] [items]"` - List items from a tool
- `"Create [item] in [Tool]"` - Create something in a tool
- `"Send [message] via [Tool]"` - Send messages
- `"Show me [data] from [Tool]"` - Display data

## ✅ Setup Checklist

- [ ] Added Rube MCP server to Cursor
- [ ] Authenticated with Composio account
- [ ] Connected Google Drive
- [ ] Connected Gmail (optional)
- [ ] Connected Slack (optional)
- [ ] Connected Notion (optional)
- [ ] Tested a command with connected tool
- [ ] Verified tools appear in Cursor's MCP tools list

---

**You're all set!** Now you can use Composio's 500+ tools directly from Cursor's AI chat. Just ask the AI to interact with your connected services! 🚀

