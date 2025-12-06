const { spawn } = require('child_process');

class McpClient {
    constructor() {
        this.process = spawn('npx', ['-y', 'mcp-remote', 'https://rube.app/mcp'], {
            shell: true,
            env: process.env
        });
        this.requestId = 0;
        this.pendingRequests = new Map();
        this.buffer = '';

        this.process.stdout.on('data', (data) => this.handleData(data));
        this.process.stderr.on('data', (data) => {
            // console.error('STDERR:', data.toString()); 
        });
    }

    handleData(data) {
        this.buffer += data.toString();
        const lines = this.buffer.split('\n');
        // If the last line is empty (ends with \n), buffer becomes empty
        // If not, we keep the partial line
        if (this.buffer.endsWith('\n')) {
             this.buffer = '';
        } else {
             this.buffer = lines.pop();
        }

        for (const line of lines) {
            if (!line.trim()) continue;
            try {
                const msg = JSON.parse(line);
                if (msg.id && this.pendingRequests.has(msg.id)) {
                    const { resolve, reject } = this.pendingRequests.get(msg.id);
                    this.pendingRequests.delete(msg.id);
                    if (msg.error) reject(msg.error);
                    else resolve(msg.result);
                }
            } catch (e) {
                // console.error('Error parsing JSON:', e);
            }
        }
    }

    call(method, params) {
        return new Promise((resolve, reject) => {
            const id = ++this.requestId;
            this.pendingRequests.set(id, { resolve, reject });
            const request = { jsonrpc: '2.0', id, method, params };
            this.process.stdin.write(JSON.stringify(request) + '\n');
        });
    }

    async initialize() {
        await this.call('initialize', {
            protocolVersion: "2024-11-05",
            capabilities: {},
            clientInfo: { name: "automator", version: "1.0.0" }
        });
        console.log('Initialized.');
    }

    async callTool(name, args) {
        console.log(`Calling tool: ${name}...`);
        const result = await this.call('tools/call', {
            name,
            arguments: args
        });
        return result;
    }

    close() {
        this.process.kill();
    }
}

async function run() {
    const client = new McpClient();
    try {
        await client.initialize();

        // Test RUBE_SEARCH_TOOLS
        // console.log('Testing RUBE_SEARCH_TOOLS...');
        // const searchResult = await client.callTool('RUBE_SEARCH_TOOLS', { use_case: "test" });
        // console.log('Search Result:', JSON.stringify(searchResult, null, 2));
        // return; // Stop here for test

        // 1. Get Facebook Page ID
        // const pagesResult = await client.callTool('FACEBOOK_GET_USER_PAGES', { 
        //     fields: "id,name,access_token,tasks", 
        //     user_id: "me" 
        // });
        // let pagesContent;
        // try {
        //     pagesContent = JSON.parse(pagesResult.content[0].text);
        // } catch (e) {
        //     console.error('Failed to parse pages result:', pagesResult);
        //     return;
        // }
        
        // console.log('Pages Result:', JSON.stringify(pagesContent, null, 2));
        
        // let pageId;
        // if (pagesContent.data && Array.isArray(pagesContent.data) && pagesContent.data.length > 0) {
        //      pageId = pagesContent.data[0].id;
        // } else if (Array.isArray(pagesContent) && pagesContent.length > 0) {
        //      pageId = pagesContent[0].id;
        // }
        
        // if (!pageId) {
        //     console.error('Could not find a Facebook Page ID. Response:', JSON.stringify(pagesContent, null, 2));
        //     return;
        // }
        
        // Try using the ID found in connection info
        const pageId = "122100093015127410"; 
        console.log(`Using Page ID: ${pageId}`);

        // 2. Create and post image
        // Generate Image
        const prompt = "A creative and artistic digital art piece for social media, high quality, vibrant colors";
        console.log('Generating image with prompt:', prompt);
        const imageResult = await client.callTool('GEMINI_GENERATE_IMAGES', { 
            prompt: prompt,
            model: "gemini-3-pro-image-preview"
        });
        
        let imageContent;
        try {
            imageContent = JSON.parse(imageResult.content[0].text);
        } catch (e) {
            console.error('Failed to parse image result:', imageResult);
            return;
        }
        
        console.log('Image Generation Result:', JSON.stringify(imageContent, null, 2));

        // Extract Image URL
        let imageUrl;
        // Check various possible locations
        if (imageContent.url) imageUrl = imageContent.url;
        else if (imageContent.images && imageContent.images[0] && imageContent.images[0].url) imageUrl = imageContent.images[0].url;
        else if (imageContent.data && imageContent.data.url) imageUrl = imageContent.data.url;
        
        if (!imageUrl) {
            console.error('Failed to extract image URL from response');
            return;
        }
        console.log(`Generated Image URL: ${imageUrl}`);

        // Post to Facebook
        const postResult = await client.callTool('FACEBOOK_POST_PHOTOS', {
            page_id: pageId,
            photo: imageUrl,
            caption: "Generated by AI #art"
        });
        console.log('Post Result:', JSON.stringify(postResult, null, 2));

    } catch (error) {
        console.error('Error:', error);
    } finally {
        client.close();
        // Force exit as process might hang
        process.exit(0);
    }
}

run();
