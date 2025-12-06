const { spawn } = require('child_process');

const serverProcess = spawn('npx', ['-y', 'mcp-remote', 'https://rube.app/mcp'], {
  shell: true,
  env: process.env
});

let buffer = '';

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
        const req = {
            jsonrpc: '2.0',
            id: 2,
            method: 'tools/call',
            params: {
                name: 'GEMINI_GENERATE_IMAGES',
                arguments: {
                    prompt: "A futuristic city skyline",
                    model: "gemini-3-pro-image-preview"
                }
            }
        };
        console.log('Generating image...');
        serverProcess.stdin.write(JSON.stringify(req) + '\n');
      } else if (msg.id === 2) {
        console.log('Raw Response:', JSON.stringify(msg, null, 2));
        process.exit(0);
      } else if (msg.error) {
          console.log('Raw Error:', JSON.stringify(msg, null, 2));
      }
    } catch (e) {}
  }
});

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
serverProcess.stdin.write(JSON.stringify(initRequest) + '\n');
