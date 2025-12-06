const { spawn } = require('child_process');

// Path to the MCP server executable
const serverProcess = spawn('npx', ['-y', 'mcp-remote', 'https://rube.app/mcp'], {
  shell: true,
  env: process.env
});

let buffer = '';
let isInitialized = false;
let step = 0; // 0: init, 1: search facebook, 2: search image, 3: done

serverProcess.stdout.on('data', (data) => {
  const chunk = data.toString();
  buffer += chunk;
  
  const lines = buffer.split('\n');
  buffer = lines.pop();

  for (const line of lines) {
    if (!line.trim()) continue;
    try {
      const msg = JSON.parse(line);
      
      if (msg.id === 1 && msg.result) {
        console.log('Initialized.');
        isInitialized = true;
        searchFacebook();
      } else if (msg.id === 2) {
        console.log('\n--- Facebook Tools Search Result ---');
        console.log(JSON.stringify(msg.result, null, 2));
        searchImage();
      } else if (msg.id === 3) {
        console.log('\n--- Image Generation Tools Search Result ---');
        console.log(JSON.stringify(msg.result, null, 2));
        console.log('\nDone.');
        process.exit(0);
      } else if (msg.error) {
          console.error("Error received:", JSON.stringify(msg.error, null, 2));
          process.exit(1);
      }
    } catch (e) {
      // Partial JSON or error
    }
  }
});

serverProcess.stderr.on('data', (data) => {
  // console.error('STDERR:', data.toString()); // validation logs might be noisy
});

function sendJson(obj) {
    serverProcess.stdin.write(JSON.stringify(obj) + '\n');
}

// 1. Initialize
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

function searchFacebook() {
    const req = {
        jsonrpc: '2.0',
        id: 2,
        method: 'tools/call',
        params: {
            name: 'RUBE_SEARCH_TOOLS',
            arguments: {
                queries: ["post to facebook page"],
                use_case: "I want to post an image to a facebook page"
            }
        }
    };
    console.log('Searching for Facebook tools...');
    sendJson(req);
}

function searchImage() {
    const req = {
        jsonrpc: '2.0',
        id: 3,
        method: 'tools/call',
        params: {
            name: 'RUBE_SEARCH_TOOLS',
            arguments: {
                queries: ["generate image"],
                use_case: "I want to generate an image using AI"
            }
        }
    };
    console.log('Searching for Image tools...');
    sendJson(req);
}

setTimeout(() => {
    console.log('Timeout.');
    process.exit(1);
}, 60000);
