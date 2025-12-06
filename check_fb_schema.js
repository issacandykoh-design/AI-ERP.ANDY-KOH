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
                    queries: ["facebook create photo post"],
                    use_case: "check schema"
                }
            }
        };
        serverProcess.stdin.write(JSON.stringify(req) + '\n');
      } else if (msg.id === 2) {
        if (msg.result && msg.result.content && msg.result.content[0].text) {
            const toolsStr = msg.result.content[0].text;
            try {
                const response = JSON.parse(toolsStr);
                if (response.data && response.data.data && response.data.data.tool_schemas) {
                    const tool = response.data.data.tool_schemas.FACEBOOK_CREATE_PHOTO_POST;
                    console.log('Schema:', JSON.stringify(tool, null, 2));
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
