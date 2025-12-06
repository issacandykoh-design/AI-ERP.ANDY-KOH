const { spawn } = require('child_process');

const serverProcess = spawn('npx', ['-y', 'mcp-remote', 'https://rube.app/mcp'], {
  shell: true,
  env: process.env
});

let buffer = '';
let step = 'INIT';
let sessionId = '';
let fbPageId = '122100093015127410';
let fbToolSlug = 'FACEBOOK_POST_PHOTOS'; // Trying this slug

serverProcess.stdout.on('data', (data) => {
  const chunk = data.toString();
  buffer += chunk;
  const lines = buffer.split('\n');
  buffer = lines.pop();

  for (const line of lines) {
    if (!line.trim()) continue;
    try {
      const msg = JSON.parse(line);
      handleMessage(msg);
    } catch (e) {}
  }
});

function sendJson(obj) {
    serverProcess.stdin.write(JSON.stringify(obj) + '\n');
}

function handleMessage(msg) {
    if (msg.id === 1 && msg.result) {
        console.log('Initialized.');
        searchTools();
    } else if (msg.id === 2) {
        if (msg.result && msg.result.session) {
            sessionId = msg.result.session.id;
            console.log('Session ID:', sessionId);
        }
        postToFacebook();
    } else if (msg.id === 300) {
        console.log('Facebook Post Result:', JSON.stringify(msg, null, 2));
        process.exit(0);
    } else if (msg.error) {
        console.error('Error:', JSON.stringify(msg.error, null, 2));
    }
}

function searchTools() {
    const req = {
        jsonrpc: '2.0',
        id: 2,
        method: 'tools/call',
        params: {
            name: 'RUBE_SEARCH_TOOLS',
            arguments: {
                queries: ["post photo to facebook page"],
                use_case: "Post a photo to facebook"
            }
        }
    };
    sendJson(req);
}

function postToFacebook() {
    const imageUrl = "https://upload.wikimedia.org/wikipedia/commons/thumb/b/b6/Image_created_with_a_mobile_phone.png/640px-Image_created_with_a_mobile_phone.png";
    
    const req = {
        jsonrpc: '2.0',
        id: 300,
        method: 'tools/call',
        params: {
            name: 'RUBE_MULTI_EXECUTE_TOOL',
            arguments: {
                tools: [{
                    tool_slug: fbToolSlug,
                    arguments: {
                        page_id: fbPageId,
                        photos: [imageUrl],
                        published: true
                    }
                }],
                session_id: sessionId,
                sync_response_to_workbench: false
            }
        }
    };
    console.log('Posting to Facebook...');
    sendJson(req);
}

const initRequest = {
  jsonrpc: '2.0',
  id: 1,
  method: 'initialize',
  params: {
    "protocolVersion": "2024-11-05",
    "capabilities": {},
    "clientInfo": { "name": "test-client", "version": "1.0.0" }
  }
};
sendJson(initRequest);
