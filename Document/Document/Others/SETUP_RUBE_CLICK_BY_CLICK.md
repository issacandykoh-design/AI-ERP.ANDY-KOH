# Setup Rube - Click by Click Guide (For Beginners)

## 🎯 I'll Walk You Through Every Single Click!

Don't worry - I'll tell you exactly where to click. Just follow these steps one by one.

---

## STEP 1: Open Cursor Settings

**What to do:**
1. Look at the **top left** of Cursor window
2. Click on **"File"** (the menu at the top)
3. In the dropdown menu, click **"Preferences"**
4. Then click **"Settings"**

**OR** (easier way):
- Press the keys: `Ctrl` and `,` (comma) at the same time
- This opens Settings directly!

**What you should see:**
- A settings window opens
- There's a search bar at the top that says "Search settings"

---

## STEP 2: Find MCP Settings

**What to do:**
1. In the search bar at the top, type exactly: `MCP`
2. You should see results appear below

**OR** if that doesn't work:
1. Look on the **left sidebar** of the settings window
2. Scroll down and look for **"Features"**
3. Click on **"Features"**
4. Look for **"MCP"** or **"Model Context Protocol"**
5. Click on it

**What you should see:**
- A section about MCP servers
- Maybe a list of servers (could be empty)
- A button that says **"Add MCP Server"** or **"+"** or **"Add"**

---

## STEP 3: Add Rube Server

**What to do:**
1. Click the button that says **"Add MCP Server"** or **"+"** or **"Add"**
2. A form or dialog box will appear
3. Fill in these EXACT values:

   **Field 1 - Name:**
   - Type: `rube`
   - (all lowercase, no spaces)

   **Field 2 - Type:**
   - Type: `streamableHttp`
   - (exactly like this, case-sensitive)

   **Field 3 - URL:**
   - Type: `https://rube.app/?agent=cursor`
   - (copy this exactly, including the https://)

4. Click **"Save"** or **"Add"** or **"OK"**

**What you should see:**
- A new entry appears in your MCP servers list
- It might say "rube" with status "Needs login" or "Not connected"

---

## STEP 4: Get Your API Key from Rube

**What to do:**
1. Open your web browser (Chrome, Edge, Firefox, etc.)
2. Go to this website: `https://rube.app/`
3. Look for a button that says **"Install Rube"** or **"Get Started"** or **"Sign Up"**
4. Click it
5. If you need to create an account:
   - Click **"Sign Up"** or **"Create Account"**
   - Enter your email and create a password
   - Verify your email if needed
6. After logging in, look for:
   - **"Install Rube"** button
   - Or **"Generate Token"** or **"API Key"**
7. Click on it
8. Select **"Cursor"** as your editor
9. Copy the API key/token that appears (it's a long string of letters and numbers)

**What you should see:**
- A token/API key (looks like: `abc123xyz789...` or similar)
- Copy this - you'll need it in the next step!

---

## STEP 5: Add API Key to Cursor

**What to do:**
1. Go back to Cursor
2. Find the "rube" server you added in Step 3
3. Click on it (or click an "Edit" button next to it)
4. Look for a field that says:
   - **"API Key"** or
   - **"Auth Token"** or
   - **"Token"** or
   - **"Authentication"**
5. Paste the API key you copied from Step 4
6. Click **"Save"** or **"Connect"**

**What you should see:**
- The status changes to "Connected" or "Ready"
- Or it might say "Authenticated"

---

## STEP 6: Connect Google Drive (or Other Tools)

**What to do:**
1. Go back to your browser (rube.app)
2. Look for a section called:
   - **"Connected Apps"** or
   - **"Integrations"** or
   - **"Tools"** or
   - **"Apps"**
3. Find **"Google Drive"** in the list
4. Click **"Connect"** next to Google Drive
5. A new window/tab will open asking you to sign in to Google
6. Sign in with your Google account
7. Click **"Allow"** or **"Authorize"** to give Rube permission
8. You'll be redirected back to Rube
9. Google Drive should now show as "Connected"

**Repeat for other tools:**
- Do the same for Gmail, Slack, Notion, etc. if you want them

---

## STEP 7: Test It Works!

**What to do:**
1. In Cursor, press `Ctrl + I` (or `Cmd + I` on Mac)
   - This opens the AI chat
2. Type this message:
   ```
   List my Google Drive files
   ```
3. Press Enter
4. The AI should respond and show your Google Drive files!

**If it works:**
- ✅ You're all set! Rube is connected!

**If it doesn't work:**
- See the troubleshooting section below

---

## 🆘 TROUBLESHOOTING

### Problem: "I can't find MCP settings"
**Solution:**
- Try pressing `Ctrl + Shift + P` (Command Palette)
- Type: `MCP`
- Look for "MCP: Configure" or similar
- Click it

### Problem: "I can't find the Add button"
**Solution:**
- The button might be at the top right of the MCP settings page
- Or it might be a "+" icon
- Or try right-clicking in the servers list area

### Problem: "The server won't connect"
**Solution:**
- Double-check the URL: `https://rube.app/?agent=cursor`
- Make sure you copied the API key correctly
- Try removing and re-adding the server

### Problem: "I can't find the API key on Rube"
**Solution:**
- Make sure you're logged into rube.app
- Look for "Settings" or "Account" menu
- Or try: https://rube.app/settings or https://rube.app/api-keys

### Problem: "Google Drive won't connect"
**Solution:**
- Make sure you clicked "Allow" when Google asked for permission
- Try disconnecting and reconnecting Google Drive
- Check that you're signed into the correct Google account

---

## 📞 Still Stuck?

If you're still having trouble, tell me:
1. Which step are you on? (Step 1, 2, 3, etc.)
2. What do you see on your screen?
3. What error message (if any) do you see?

I'll help you figure it out!

---

## ✅ Checklist

Go through this to make sure you did everything:

- [ ] Opened Cursor Settings (Ctrl + ,)
- [ ] Found MCP settings
- [ ] Added Rube server with URL: `https://rube.app/?agent=cursor`
- [ ] Got API key from rube.app
- [ ] Added API key to Cursor
- [ ] Connected Google Drive (or other tools)
- [ ] Tested it by asking "List my Google Drive files"

---

**You can do this!** Just follow each step slowly. Take your time! 🚀

