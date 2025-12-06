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
        // Init done
        const req = {
            jsonrpc: '2.0',
            id: 2,
            method: 'tools/call',
            params: {
                name: 'RUBE_CREATE_PLAN',
                arguments: {
                    goal: "Generate an image of a cat and post it to my facebook page"
                }
            }
        };
        console.log('Asking Rube for a plan...');
        serverProcess.stdin.write(JSON.stringify(req) + '\n');
      } else if (msg.id === 2) {
        console.log('Plan Result:');
        console.log(JSON.stringify(msg.result, null, 2));
        process.exit(0);
      } else if (msg.error) {
          console.error("Error:", JSON.stringify(msg.error, null, 2));
          process.exit(1);
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
