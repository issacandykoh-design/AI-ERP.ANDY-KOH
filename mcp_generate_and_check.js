const { spawn } = require('child_process');
const puppeteer = require('puppeteer');

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

async function run() {
  const proc = startMcp();
  let buffer = '';
  let sessionId = '';
  let imageUrls = [];
  let awaiting = new Map();
  let imagesDone = false;

  function handleLine(line) {
    if (!line.trim()) return;
    let msg;
    try { msg = JSON.parse(line); } catch { return; }

    if (msg.id === 1 && msg.result) {
      const initDone = {
        jsonrpc: '2.0',
        id: 2,
        method: 'tools/call',
        params: {
          name: 'RUBE_SEARCH_TOOLS',
          arguments: {
            queries: ['gemini generate image'],
            use_case: 'generate image with ai'
          }
        }
      };
      sendJson(proc, initDone);
    } else if (msg.id === 2) {
      try {
        const text = msg.result?.content?.[0]?.text || '';
        const parsed = JSON.parse(text);
        sessionId = parsed?.data?.data?.session?.id || parsed?.data?.data?.session_id || 'pony';
      } catch {}
      const prompts = [
        'Vibrant abstract art for social media, high quality',
        'Futuristic neon cityscape, digital art',
        'Minimalist geometric poster, bold colors'
      ];
      const req = {
        jsonrpc: '2.0',
        id: 100,
        method: 'tools/call',
        params: {
          name: 'RUBE_MULTI_EXECUTE_TOOL',
          arguments: {
            tools: prompts.map(pr => ({
              tool_slug: 'GEMINI_GENERATE_IMAGE',
              arguments: { prompt: pr }
            })),
            session_id: sessionId,
            sync_response_to_workbench: false
          }
        }
      };
      awaiting.set(100, 'images');
      sendJson(proc, req);
    } else if (awaiting.get(msg.id) === 'images') {
      // Extract s3url(s)
      try {
        const text = msg.result?.content?.[0]?.text || '';
        const parsed = JSON.parse(text);
        const results = parsed?.data?.data?.results || [];
        for (const r of results) {
          const url = r?.response?.data?.image?.s3url;
          if (url) imageUrls.push(url);
        }
        imagesDone = true;
      } catch {}
    }
  }

  proc.stdout.on('data', d => {
    buffer += d.toString();
    const lines = buffer.split('\n');
    buffer = lines.pop();
    for (const line of lines) handleLine(line);
  });
  proc.stderr.on('data', () => {});

  // Initialize
  sendJson(proc, {
    jsonrpc: '2.0',
    id: 1,
    method: 'initialize',
    params: {
      protocolVersion: '2024-11-05',
      capabilities: {},
      clientInfo: { name: 'puppeteer-check', version: '1.0.0' }
    }
  });

  // Wait until imagesDone or timeout
  const start = Date.now();
  while (!imagesDone && Date.now() - start < 20000) {
    await new Promise(res => setTimeout(res, 300));
  }

  if (!imageUrls.length) {
    console.log('No image URLs found.');
    process.exit(1);
  }

  const browser = await puppeteer.launch({ headless: 'new' });
  const page = await browser.newPage();
  for (let i = 0; i < imageUrls.length; i++) {
    const url = imageUrls[i];
    const resp = await page.goto(url, { waitUntil: 'networkidle0' });
    const ok = !!resp && resp.ok();
    const status = resp?.status();
    console.log(`URL ${i + 1}: ${url} -> status ${status}, ok=${ok}`);
    await page.screenshot({ path: `puppeteer_image_${i + 1}.png` });
  }
  await browser.close();
  console.log('Puppeteer verification done.');
  process.exit(0);
}

run().catch(e => { console.error(e); process.exit(1); });
