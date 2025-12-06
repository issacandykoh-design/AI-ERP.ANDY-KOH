const { spawn } = require('child_process');

const serverProcess = spawn('npx', ['-y', 'mcp-remote', 'https://rube.app/mcp'], {
  shell: true,
  env: process.env
});

let buffer = '';
let step = 'INIT';
let sessionId = '';
let fbPageId = '122100093015127410';
let fbToolSlug = 'FACEBOOK_CREATE_PHOTO_POST'; 
let geminiToolSlug = 'GEMINI_GENERATE_IMAGE'; // Singular!

const IMAGES_TO_CREATE = 20; // Set to 20 as requested, but will stop if error loop
let imagesCreated = 0;

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
        // Search Result
        if (msg.result && msg.result.content && msg.result.content[0].text) {
             try {
                const response = JSON.parse(msg.result.content[0].text);
                if (response.data && response.data.data && response.data.data.session) {
                    sessionId = response.data.data.session.id;
                    console.log('Session ID:', sessionId);
                }
            } catch (e) {}
        }
        startImageLoop();
    } else if (msg.id >= 100 && msg.id < 200) {
        handleImageGenResult(msg);
    } else if (msg.id >= 200 && msg.id < 300) {
        handleFbPostResult(msg);
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
                queries: ["facebook", "gemini image"],
                use_case: "generate image and post to facebook"
            }
        }
    };
    sendJson(req);
}

async function startImageLoop() {
    if (imagesCreated >= IMAGES_TO_CREATE) {
        console.log('All images created and posted.');
        process.exit(0);
    }

    console.log(`\nStarting iteration ${imagesCreated + 1}/${IMAGES_TO_CREATE}`);
    
    const prompt = `A creative and artistic digital art piece for social media, high quality, vibrant colors, variation ${imagesCreated + 1}`;
    
    const req = {
        jsonrpc: '2.0',
        id: 100 + imagesCreated,
        method: 'tools/call',
        params: {
            name: 'RUBE_MULTI_EXECUTE_TOOL',
            arguments: {
                tools: [{
                    tool_slug: geminiToolSlug,
                    arguments: {
                        prompt: prompt,
                        // model: "gemini-3-pro-image-preview" // Optional, removing to rely on default if unsure
                    }
                }],
                session_id: sessionId,
                sync_response_to_workbench: false
            }
        }
    };
    console.log('Requesting Image Generation...');
    sendJson(req);
}

function handleImageGenResult(msg) {
    if (msg.error) {
        console.error(`Image Gen Error (Iter ${imagesCreated + 1}):`, msg.error);
        imagesCreated++;
        startImageLoop();
        return;
    }

    // Result is usually in msg.result.content[0].text if RUBE_MULTI_EXECUTE_TOOL returns standard MCP text
    // But RUBE_MULTI_EXECUTE_TOOL returns { execution_results: [...] }
    // Let's log the raw result to be sure and try to parse.
    
    // console.log('Raw Image Gen Result:', JSON.stringify(msg.result, null, 2));
    
    let imageUrl = null;
    
    // Heuristic to find URL
    const resultStr = JSON.stringify(msg.result);
    
    // Try to find "url": "..."
    const urlMatch = resultStr.match(/"url"\s*:\s*"([^"]+)"/);
    if (urlMatch) {
        imageUrl = urlMatch[1];
    } else {
        const httpMatch = resultStr.match(/https?:\/\/[^"\s]+\.(?:png|jpg|jpeg|webp)/);
        if (httpMatch) {
            imageUrl = httpMatch[0];
        }
    }

    if (imageUrl) {
        console.log('Found Image URL:', imageUrl);
        postToFacebook(imageUrl);
    } else {
        console.error('Could not extract Image URL.');
        console.log('Full Response:', resultStr.substring(0, 500) + '...');
        imagesCreated++;
        startImageLoop();
    }
}

function postToFacebook(imageUrl) {
    const req = {
        jsonrpc: '2.0',
        id: 200 + imagesCreated,
        method: 'tools/call',
        params: {
            name: 'RUBE_MULTI_EXECUTE_TOOL',
            arguments: {
                tools: [{
                    tool_slug: fbToolSlug,
                    arguments: {
                        page_id: fbPageId,
                        url: imageUrl, // Trying 'url'
                        message: `Generated by AI - Variation ${imagesCreated + 1}`
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

function handleFbPostResult(msg) {
    if (msg.error) {
        console.error(`Facebook Post Error (Iter ${imagesCreated + 1}):`, msg.error);
        // If error is about arguments, we might need to adjust
    } else {
        console.log(`Facebook Post Success (Iter ${imagesCreated + 1})!`);
    }
    
    imagesCreated++;
    startImageLoop();
}

const initRequest = {
  jsonrpc: '2.0',
  id: 1,
  method: 'initialize',
  params: {
    "protocolVersion": "2024-11-05",
    "capabilities": {},
    "clientInfo": { "name": "automator", "version": "1.0.0" }
  }
};
sendJson(initRequest);

setTimeout(() => {
    console.log('Timeout.');
    process.exit(1);
}, 600000);
