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
                name: 'RUBE_SEARCH_TOOLS',
                arguments: {
                    queries: ["facebook", "gemini image"],
                    use_case: "post image to facebook"
                }
            }
        };
        serverProcess.stdin.write(JSON.stringify(req) + '\n');
      } else if (msg.id === 2) {
        if (msg.result && msg.result.content && msg.result.content[0].text) {
            const toolsStr = msg.result.content[0].text;
            try {
                const response = JSON.parse(toolsStr);
                if (response.data) {
                    console.log('Successful:', response.data.successful);
                    console.log('Error:', response.data.error);
                    if (response.data.data) {
                        console.log('Inner Data Keys:', Object.keys(response.data.data));
                        // Check for tool keys in inner data
                        // Or maybe it's response.data.data itself?
                        
                        // Also log first 3 keys if it's an object with many keys
                        const keys = Object.keys(response.data.data);
                        console.log('First 3 keys:', keys.slice(0, 3));
                    }
                }
            } catch (e) {}
        }
        process.exit(0);
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
