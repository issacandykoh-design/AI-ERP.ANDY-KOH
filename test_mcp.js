const { spawn } = require('child_process');

// Path to the MCP server executable (using npx here)
const serverProcess = spawn('npx', ['-y', 'mcp-remote', 'https://rube.app/mcp'], {
  shell: true,
  env: process.env
});

let buffer = '';

serverProcess.stdout.on('data', (data) => {
  const chunk = data.toString();
  buffer += chunk;
  console.log('Received chunk:', chunk);

  // Try to parse JSON messages from the buffer
  const lines = buffer.split('\n');
  // Keep the last partial line in the buffer
  buffer = lines.pop();

  for (const line of lines) {
    if (!line.trim()) continue;
    try {
      const msg = JSON.parse(line);
      console.log('Received message:', JSON.stringify(msg, null, 2));

      if (msg.result && msg.result.protocolVersion) {
        console.log('Initialization successful!');
        // Send tools/list request
        const toolsRequest = {
          jsonrpc: '2.0',
          id: 2,
          method: 'tools/list'
        };
        console.log('Sending tools/list request...');
        serverProcess.stdin.write(JSON.stringify(toolsRequest) + '\n');
      } else if (msg.id === 2) {
          console.log('Tools list received!');
          const tools = msg.result.tools;
          console.log('Available tools:', tools.map(t => t.name).join(', '));
          process.exit(0);
      }
    } catch (e) {
      console.error('Error parsing JSON:', e);
    }
  }
});

serverProcess.stderr.on('data', (data) => {
  console.error('STDERR:', data.toString());
});

serverProcess.on('close', (code) => {
  console.log(`Child process exited with code ${code}`);
});

// Send initialize request
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

console.log('Sending initialize request...');
serverProcess.stdin.write(JSON.stringify(initRequest) + '\n');

// Timeout after 30 seconds
setTimeout(() => {
    console.log('Timeout reached, killing process');
    serverProcess.kill();
}, 30000);
