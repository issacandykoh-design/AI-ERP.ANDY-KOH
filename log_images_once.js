const { spawn } = require('child_process');
const fs = require('fs');

function startMcp() {
  const p = spawn('npx', ['-y', 'mcp-remote', 'https://rube.app/mcp'], {
    shell: true,
    env: process.env
  });
  return p;
}

function sendJson(proc, obj) {
  proc.stdin.write(JSON.stringify(obj) + '\n');
}

const proc = startMcp();
let buffer = '';
let sessionId = '';

function handleLine(line) {
  if (!line.trim()) return;
  let msg; try { msg = JSON.parse(line); } catch { return; }
  if (msg.id === 1 && msg.result) {
    const req = {
      jsonrpc: '2.0', id: 2, method: 'tools/call',
      params: { name: 'RUBE_SEARCH_TOOLS', arguments: { queries: ['gemini generate image'], use_case: 'generate image with ai' } }
    };
    sendJson(proc, req);
  } else if (msg.id === 2) {
    try {
      const text = msg.result?.content?.[0]?.text || '';
      const parsed = JSON.parse(text);
      sessionId = parsed?.data?.data?.session?.id || 'pony';
    } catch {}
    const req = {
      jsonrpc: '2.0', id: 100, method: 'tools/call',
      params: {
        name: 'RUBE_MULTI_EXECUTE_TOOL',
        arguments: {
          tools: [{ tool_slug: 'GEMINI_GENERATE_IMAGE', arguments: { prompt: 'Test social image, vibrant colors' } }],
          session_id: sessionId,
          sync_response_to_workbench: false
        }
      }
    };
    sendJson(proc, req);
  } else if (msg.id === 100) {
    const text = msg.result?.content?.[0]?.text || '';
    fs.writeFileSync('image_result.json', text);
    console.log('Saved image_result.json');
    process.exit(0);
  }
}

proc.stdout.on('data', d => {
  buffer += d.toString();
  const lines = buffer.split('\n');
  buffer = lines.pop();
  for (const line of lines) handleLine(line);
});
proc.stderr.on('data', () => {});

sendJson(proc, {
  jsonrpc: '2.0', id: 1, method: 'initialize',
  params: { protocolVersion: '2024-11-05', capabilities: {}, clientInfo: { name: 'logger', version: '1.0.0' } }
});

